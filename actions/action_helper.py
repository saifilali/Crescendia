import pymysql
import configparser
config = configparser.ConfigParser()
config.read("/var/www/config.ini")
sqlhost = config.get("configuration", "sqlhost")
sqluser = config.get("configuration", "sqluser")
sqlpassword = config.get("configuration", "sqlpassword")
sqldatabase = config.get("configuration", "sqldatabase")
import random


def key_difference(key_1, key_2):
    keydiff = abs(key_1 - key_2)
    if keydiff > 6:
        keydiff = 12 - keydiff
    return keydiff


def key_bonus_ally(keydiff):
    if(keydiff == 0):
        return 1.5
    if(keydiff == 1):
        return 1.25
    if(keydiff == 2):
        return 1.125
    return 1


def key_bonus_enemy(keydiff):
    if(keydiff == 6):
        return 1.5
    if(keydiff == 5):
        return 1.25
    if(keydiff == 4):
        return 1.125
    return 1


def get_enemy(team):
    if(team == "1"):
        return 2
    else:
        return 1


def get_ally(team):
    if(team == "1"):
        return 1
    else:
        return 2


def spend_energy(battle_id, team, unit, cost):
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "UPDATE battle_unit_stats SET energy_current = energy_current - %s WHERE battle_id = %s AND team = %s AND slot = %s"
        cursor.execute(sql, (cost, battle_id, team, unit))
        connection.commit()
    connection.close()


def is_immune(immune, battle_id, turn, target_team, target_slot):
    if(immune == 0):
        return 0
    else:
        connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                     db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
        with connection.cursor() as cursor:
            sql = "SELECT * FROM battle_effect_queue WHERE processed = 0 AND battle_id = %s AND team = %s AND slot = %s AND turn_expire > %s"
            cursor.execute(sql, (battle_id, target_team, target_slot, turn))
            active_effects = cursor.fetchall()
            is_immune = 0
            for effect in active_effects:
                if(effect["action_code"] == "HOE"):
                    is_immune = 1
                    sql = "UPDATE battle_effect_queue SET processed = 1 WHERE battle_id = %s AND team = %s AND slot = %s AND action_code = HOE"
                    cursor.execute(sql, (battle_id, target_team, target_slot))
                    connection.commit()
                if(effect["action_code"] == "D4E"):
                    if (random.random() < 0.3):
                        is_immune = 1
            connection.commit()
        connection.close()
        return is_immune


def get_balance(action_code):
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT cost, scale FROM actions WHERE id=%s"
        cursor.execute(sql, (action_code))
        cost_fetch = cursor.fetchone()
    connection.close()
    return {"scale": cost_fetch["scale"], "cost": cost_fetch["cost"]}


def exhausted_action_receipt(battle_id, team, unit, title, turn):
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        source_animation = "none"
        background_animation = "none"
        target_animation = "none"
        summary_code = "pass"
        summary_text = "{} is exhausted, so she did nothing. Very low energy!!!".format(title)
        sql = "UPDATE battle_action_queue SET source_animation=%s, target_animation=%s, background_animation=%s, summary_code=%s, summary_text=%s, processed=1 WHERE battle_id=%s AND unit=%s AND team=%s AND turn =%s"
        params = (source_animation, target_animation, background_animation,
                  summary_code, summary_text, battle_id, unit, team, turn)
        cursor.execute(sql, params)
        connection.commit()
    connection.close()


def dead_placeholder_action_receipt(battle_id, team, unit, title, turn):
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        source_animation = "none"
        background_animation = "none"
        target_animation = "none"
        summary_code = "pass"
        summary_text = "{} is already dead, so she did nothing. Nasty!".format(title)
        sql = "UPDATE battle_action_queue SET source_animation=%s, target_animation=%s, background_animation=%s, summary_code=%s, summary_text=%s, processed=1 WHERE battle_id=%s AND unit=%s AND team=%s AND turn =%s"
        params = (source_animation, target_animation, background_animation,
                  summary_code, summary_text, battle_id, unit, team, turn)
        cursor.execute(sql, params)
        connection.commit()
    connection.close()


def dead_action_receipt(battle_id, team, unit, title, turn):
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        source_animation = "none"
        background_animation = "none"
        target_animation = "none"
        summary_code = "pass2"
        summary_text = "{} died before executing action, so she did nothing. Very sad!!!".format(
            title)
        sql = "UPDATE battle_action_queue SET source_animation=%s, target_animation=%s, background_animation=%s, summary_code=%s, summary_text=%s, processed=1 WHERE battle_id=%s AND unit=%s AND team=%s AND turn =%s"
        params = (source_animation, target_animation, background_animation,
                  summary_code, summary_text, battle_id, unit, team, turn)
        cursor.execute(sql, params)
        connection.commit()
    connection.close()
