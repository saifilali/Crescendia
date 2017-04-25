"""
Project:     Crescendia Game
File:        db_user.py
Descritpion: Holds methods to be called by the controller.py for making database calls relating to user info, squads, etc.
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


def get_user_data(query):
    query_split = query.split()
    data = []
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        wherelike = ""
        params = ()
        # NOTE: '%' must be escaped via '%%' lest the cursor.execute() will expect a parameter
        for word in query_split:
            word = "%%" + word + "%%"
            params += (word, word)
            wherelike += "(username LIKE %s OR email LIKE %s) AND "
        sql = "SELECT user_id, username FROM users WHERE " + wherelike[:-4] + " LIMIT 10"
        cursor.execute(sql, params)
        data += cursor.fetchall()
    connection.close()
    return data


def get_guild_data(query):
    query_split = query.split()
    data = []
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        wherelike = ""
        params = ()
        # NOTE: '%' must be escaped via '%%' lest the cursor.execute() will expect a parameter
        for word in query_split:
            word = "%%" + word + "%%"
            params += (word, word)
            wherelike += "(name LIKE %s OR description LIKE %s) AND "
        sql = "SELECT * FROM guild WHERE " + wherelike[:-4] + " LIMIT 10"
        cursor.execute(sql, params)
        data += cursor.fetchall()
    connection.close()
    return data
