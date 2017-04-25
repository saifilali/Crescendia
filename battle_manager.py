import pymysql
from subprocess import Popen
import configparser


config = configparser.ConfigParser()
config.read("/var/www/config.ini")
sqlhost = config.get("configuration", "sqlhost")
sqluser = config.get("configuration", "sqluser")
sqlpassword = config.get("configuration", "sqlpassword")
sqldatabase = config.get("configuration", "sqldatabase")

connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                             db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)


def run_battle(battle_id):
    with connection.cursor() as cursor:
        sql = "SELECT * FROM battle_action_queue WHERE battle_id=%s AND processed=0 ORDER BY unit_speed DESC"
        cursor.execute(sql, (battle_id))
        actions = cursor.fetchall()
        current_turn = actions[0]["turn"]
        sql = "SELECT * FROM battle_effect_queue WHERE battle_id=%s AND processed=0 AND turn_expire > %s ORDER BY unit_speed DESC"
        cursor.execute(sql, (battle_id, current_turn))
        effects = cursor.fetchall()

        action_turn_order = 0
        effect_turn_order = 0
        battle_turn = 0
        for action in actions:
            print("Action target unit is: " + action["action_target_unit"])
            Popen(['python3', action["action_code"] + '.py', '-battle_id', str(action["battle_id"]), '-team', str(action["team"]), '-turn', str(action["turn"]), '-unit', str(action["unit"]), '-unit_key', str(
                action["unit_key"]), '-unit_speed', str(action["unit_speed"]), '-action_target_team', str(action["action_target_team"]), '-action_target_unit', str(action["action_target_unit"])], cwd="/var/www/actions").wait()
            sql = "UPDATE battle_action_queue SET `order` = %s WHERE battle_id=%s AND unit = %s AND team = %s"
            params = (action_turn_order, battle_id, action["unit"], action["team"])
            battle_turn = action["turn"]
            cursor.execute(sql, params)
            connection.commit()
            action_turn_order = action_turn_order + 1
        for effect in effects:
            Popen(['python3', effect["action_code"] + '.py', '-battle_id', str(effect["battle_id"]), '-team', str(effect["team"]), '-turn', str(effect["turn"]), '-unit', str(effect["unit"]), '-unit_key', str(
                effect["unit_key"]), '-unit_speed', str(effect["unit_speed"]), '-action_target_team', str(effect["action_target_team"]), '-action_target_unit', str(effect["action_target_unit"]), '-turn_expire', str(effect["turn_expire"])], cwd="/var/www/actions").wait()
            sql = "UPDATE battle_effect_queue SET `order` = %s WHERE battle_id=%s AND unit = %s AND team = %s"
            params = (effect_turn_order, battle_id, effect["unit"], effect["team"])
            battle_turn = effect["turn"]
            cursor.execute(sql, params)
            connection.commit()
            effect_turn_order = effect_turn_order + 1
        sql = "UPDATE battle_requests SET current_turn = current_turn+1 WHERE battle_id=%s"
        cursor.execute(sql, (battle_id))
        connection.commit()
        sql = "UPDATE battle_unit_stats SET energy_current = energy_current*1.1 WHERE battle_id=%s"
        cursor.execute(sql, (battle_id))
        connection.commit()
        sql = "UPDATE battle_unit_stats SET energy_current = energy_default WHERE energy_current > energy_default AND battle_id=%s"
        cursor.execute(sql, (battle_id))
        connection.commit()
        sql = "SELECT * FROM battle_unit_stats WHERE battle_id=%s"
        cursor.execute(sql, (battle_id))
        connection.commit()
        battle_units = cursor.fetchall()
        for unit in battle_units:
            if(unit["health_current"] < 1):
                print("unit is dead so give it a dead placeholder thing")
                sql = "REPLACE INTO battle_action_queue (battle_id, turn, team, unit, unit_key, unit_speed, action_code, action_target_team, action_target_unit, processed) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s);"
                params = (battle_id, str(battle_turn + 1), unit["team"], unit["slot"], unit[
                          "song_key"], unit["speed_current"], "Z0", -1, -1, 0)
                cursor.execute(sql, params)
                connection.commit()

        Popen(['python3', 'regulartasks.py'], cwd="/var/www/").wait()
        connection.close()


def run_battles_all():
    with connection.cursor() as cursor:
        sql = "SELECT battle_id, COUNT(battle_id) FROM battle_action_queue WHERE processed = 0 GROUP BY battle_id;"
        cursor.execute(sql)
        battles = cursor.fetchall()
        for battle in battles:
            if (battle["COUNT(battle_id)"] == 8):
                battle_id = battle["battle_id"]
                run_battle(battle_id)


def run_battle_one(battle_id):
    with connection.cursor() as cursor:
        sql = "SELECT battle_id, COUNT(battle_id) FROM battle_action_queue WHERE battle_id = %s AND processed = 0 GROUP BY battle_id;"
        cursor.execute(sql, (battle_id))
        battle = cursor.fetchone()
        if (battle["COUNT(battle_id)"] == 8):
            battle_id = battle["battle_id"]
            run_battle(battle_id)
    connection.close()

run_battles_all()
