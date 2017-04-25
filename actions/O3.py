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
balance = action_helper.get_balance("O3")
cost = balance["cost"]
scale = balance["scale"]
target_team = action_helper.get_enemy(team)
damage_string = ""
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
        sql = "SELECT * FROM battle_unit_stats WHERE battle_id = %s AND team = %s"
        params = (battle_id, target_team)
        cursor.execute(sql, params)
        target_stats_all = cursor.fetchall()
        for target_stats in target_stats_all:
            if(action_helper.is_immune(target_stats["immune"], battle_id, turn, target_stats["team"], target_stats["slot"]) == 0):
                power_multiplied = action_helper.key_bonus_enemy(
                    action_helper.key_difference(target_stats["song_key"], unit_stats["song_key"])) * unit_stats["power_current"] * scale
                damage_percent = (power_multiplied * 0.01 + 0.05)
                print(damage_percent)
                damage = damage_percent * target_stats["health_current"]
                newhealth = target_stats["health_current"] - damage
                damage_string = damage_string + str(damage) + " "
                if(newhealth < 0):
                    newhealth = 0
                sql = "UPDATE battle_unit_stats SET health_current = %s WHERE battle_id = %s AND team = %s AND slot = %s"
                params = (newhealth, battle_id, target_team, target_stats["slot"])
                cursor.execute(sql, params)
                '''
                This part adds the effect....
                '''
                sql = "REPLACE INTO battle_effect_queue (battle_id, turn, team, unit, unit_key, unit_speed, action_code, action_target_team, action_target_unit, processed, turn_expire, type) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s, %s,'debuff');"
                params = (battle_id, turn, unit_stats["team"], unit, unit_stats[
                          "song_key"], unit_stats["speed_current"], "O3E", "enemy", target_stats["slot"], 0, str(int(turn) + 5))
                cursor.execute(sql, params)
                summary_code += "t{}u{} -{}".format(target_team, target_stats["slot"], damage)
                connection.commit()

        source_animation = "offensive_action_cast"
        background_animation = "offensive_action_bg_animation"
        target_animation = "offensive_action_effect"
        
        summary_text = "{} has hit every enemy with Everybody Hurts for {} damage".format(
            unit_stats["title"], damage_string)

        sql = "UPDATE battle_action_queue SET source_animation=%s, target_animation=%s, background_animation=%s, summary_code=%s, summary_text=%s, processed=1 WHERE battle_id=%s AND unit=%s AND team=%s AND turn =%s"
        params = (source_animation, target_animation, background_animation,
                  summary_code, summary_text, battle_id, unit, team, turn)
        cursor.execute(sql, params)
connection.commit()
connection.close()
