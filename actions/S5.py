'''
This one probably works and just needs testing
'''
import argparse
import pymysql
import configparser
import action_helper

config = configparser.ConfigParser()
config.read("/var/www/config.ini")
sqlhost = config.get("configuration", "sqlhost")
sqluser = config.get("configuration", "sqluser")
sqlpassword = config.get("configuration", "sqlpassword")
sqldatabase = config.get("configuration", "sqldatabase")

parser = argparse.ArgumentParser()
parser.add_argument("-battle_id",  type=str, dest="battle_id")
parser.add_argument("-team",  type=str, dest="team")
parser.add_argument("-turn",  type=str, dest="turn")
parser.add_argument("-unit",  type=str, dest="unit")
parser.add_argument("-unit_key",  type=str, dest="unit_key")
parser.add_argument("-unit_speed",  type=str, dest="unit_speed")
parser.add_argument("-action_target_team",  type=str,
                    dest="action_target_team")
parser.add_argument("-action_target_unit",  type=str,
                    dest="action_target_unit")


args = parser.parse_args()
battle_id = args.battle_id
team = args.team
turn = args.turn
unit = args.unit
unit_key = args.unit_key
unit_speed = args.unit_speed
action_target_team = args.action_target_team
action_target_unit = args.action_target_unit
balance = action_helper.get_balance("S5")
cost = balance["cost"]
scale = balance["scale"]
target_team = action_helper.get_ally(team)
target_team_enemy = action_helper.get_enemy(team)
heal_string = ""
energy_boost_string = ""
summary_code = ""
connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                             db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
with connection.cursor() as cursor:
    sql = "SELECT * FROM battle_unit_stats WHERE battle_id = %s AND team = %s AND slot = %s"
    params = (battle_id, team, unit)
    cursor.execute(sql, params)
    unit_stats = cursor.fetchone()

    if(unit_stats["health_current"] < 1):
        action_helper.dead_action_receipt(battle_id, team, unit, unit_stats["title"], turn)
    elif(unit_stats["energy_current"] < cost):
        action_helper.exhausted_action_receipt(battle_id, team, unit, unit_stats["title"], turn)
    else:
        action_helper.spend_energy(battle_id, team, unit, cost)
        sql = "SELECT * FROM battle_unit_stats WHERE health_current > 0 AND battle_id = %s AND team = %s"
        params = (battle_id, target_team)
        cursor.execute(sql, params)
        target_stats_all = cursor.fetchall()

        for target_stats in target_stats_all:
            sql = "UPDATE battle_unit_stats SET health_current = health_current*1.1, energy_current = energy_current*1.1, defense_current = defense_current*1.1, power_current=power_current*1.1, speed_current=speed_current*1.1, health_default=health_default*1.1, energy_default=energy_default*1.1 WHERE health_current > 0 AND battle_id = %s AND team = %s AND slot = %s"
            params = (battle_id, target_team, target_stats["slot"])
            summary_code += "t{}u{} +{} e +{} d +{} p +{} s+ {} dh +{} de +{}".format(target_team, target_stats["slot"], target_stats[health_current]*.1, target_stats[health_ energy]*.1,  target_stats[defense_current]*.1, target_stats[power_current]*.1, target_stats[speed_current]*.1, target_stats[health_current]*.1, target_stats[energy_default]*.1)
            cursor.execute(sql, params)

        source_animation = "status_action_cast"
        background_animation = "status_action_bg_animation"
        target_animation = "status_action_effect"

        summary_text = "{} casts HBFS on everyone, giving them massive boost!".format(unit_stats[
            "title"])

        sql = "UPDATE battle_action_queue SET source_animation=%s, target_animation=%s, background_animation=%s, summary_code=%s, summary_text=%s, processed=1 WHERE battle_id=%s AND unit=%s AND team=%s AND turn =%s"
        params = (source_animation, target_animation, background_animation,
                  summary_code, summary_text, battle_id, unit, team, turn)
        cursor.execute(sql, params)
connection.commit()
connection.close()
