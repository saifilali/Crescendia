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

target_team = action_helper.get_enemy(team)

connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                             db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
with connection.cursor() as cursor:
    sql = "SELECT * FROM battle_unit_stats WHERE battle_id = %s AND team = %s AND slot = %s"
    params = (battle_id, team, unit)
    cursor.execute(sql, params)
    unit_stats = cursor.fetchone()

    if(unit_stats["health_current"] < 1):
        action_helper.dead_action_receipt(battle_id, team, unit, unit_stats["title"], turn)
    else:
        sql = "SELECT * FROM battle_unit_stats WHERE battle_id = %s AND team = %s AND slot = %s"
        params = (battle_id, target_team, action_target_unit)
        cursor.execute(sql, params)
        target_stats = cursor.fetchone()
        damage = unit_stats["power_current"] * action_helper.key_bonus_enemy(
            action_helper.key_difference(target_stats["song_key"], unit_stats["song_key"]))
        if(target_stats["defense_current"] > damage):
            damage = 1
        newhealth = target_stats["health_current"] - damage
        if(newhealth < 0):
            newhealth = 0

        '''
        This is the part where it updates some stuff in the receipt
        '''
        if(damage > target_stats["health_default"] * 0.2):
            effective_text = "It was extremely strong!"
            target_animation = "basic_attack_effect_strong"
        elif(damage > target_stats["health_default"] * 0.1):
            effective_text = "It was strong!"
            target_animation = "basic_attack_effect_moderate"
        elif(damage > target_stats["health_default"] * 0.05):
            effective_text = "It was weak!"
            target_animation = "basic_attack_effect_weak"
        elif(damage > 0):
            effective_text = "It did barely anything!"
            target_animation = "basic_attack_effect_nothing"
        if(newhealth < 0):
            effective_text = target_stats["title"] + " died!"
            target_animation = target_animation + " basic_attack_effect_nothing"
        sql = "UPDATE battle_unit_stats SET health_current = %s WHERE battle_id = %s AND team = %s AND slot = %s"
        params = (newhealth, battle_id, target_team, action_target_unit)
        cursor.execute(sql, params)
        source_animation = "basic_attack_cast"
        background_animation = ""
        summary_code = "t{}u{} -{}".format(target_team, action_target_unit, damage)
        summary_text = "{} has hit {} for {} damage with a basic attack! ".format(
            unit_stats["title"], target_stats["title"], damage) + effective_text

        sql = "UPDATE battle_action_queue SET source_animation=%s, target_animation=%s, background_animation=%s, summary_code=%s, summary_text=%s, processed=1 WHERE battle_id=%s AND unit=%s AND team=%s AND turn =%s"
        params = (source_animation, target_animation, background_animation,
                  summary_code, summary_text, battle_id, unit, team, turn)
        cursor.execute(sql, params)
connection.commit()
connection.close()
