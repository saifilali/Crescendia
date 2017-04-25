"""
Project:     Crescendia Game
File:        db_actions.py
Descritpion: Holds methods to be called by the controller.py for making database calls relating to songs(actions).
"""

import configparser
import pymysql

config = configparser.ConfigParser()
config.read("/var/www/config.ini")

sqlhost = config.get("configuration", "sqlhost")
sqluser = config.get("configuration", "sqluser")
sqlpassword = config.get("configuration", "sqlpassword")
sqldatabase = config.get("configuration", "sqldatabase")
siteurl = config.get("configuration", "siteurl")


# Returns information related to an atack move when given a two character attack code.
def print_action(code, power):
    description = ""
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    try:
        with connection.cursor() as cursor:
            sql = "SELECT * FROM actions WHERE id=%s"
            cursor.execute(sql, (code))
            actions = cursor.fetchall()
        for action in actions:
            description = action['description'].replace(
                "POWERMOD", str(int(round(action['scale'] * power))))
            cost = str(action['cost'])
            name = action['name']
            code = action['id']
        connection.close()
        return dict([('name', name), ('description', description), ('cost', cost), ('id', code)])
    except:
        connection.close()
        return dict([('name', 'Unselected'), ('description', 'N/A'), ('cost', 'N/A'), ('code', 'N/A')])


def print_passive(code, power):
    description = ""
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM actions WHERE id=%s"
        cursor.execute(sql, (code))
        actions = cursor.fetchall()
    for action in actions:
        description = action['description'].replace(
            "POWERMOD", str(int(round(action['scale'] * power))))
        name = action['name']
        code = action['id']
    connection.close()
    return dict([('name', name), ('description', description), ('id', code)])
