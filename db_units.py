"""
Project:     Crescendia Game
File:        db_units.py
Descritpion: Holds methods to be called by the controller.py for making database calls relating to unit info, creation, recall, etc.
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

# gets stored data pertaining to one unit(song)


def get_unit_data(song_id):
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM songs_master WHERE id=%s"
        cursor.execute(sql, (song_id))
        data = cursor.fetchall()
    connection.close()
    return data


def get_all_unit_data():
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM songs_master ORDER BY song_artist ASC"
        cursor.execute(sql)
        data = cursor.fetchall()
    connection.close()
    return data


#'query' is just the input the user gives for the search
def get_search_unit(query):
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
            params += (word, word, word)
            wherelike += "(song_title LIKE %s OR song_artist LIKE %s OR song_album LIKE %s) AND "
        sql = "SELECT * FROM songs_master WHERE " + wherelike[:-4] + " LIMIT 10"
        cursor.execute(sql, params)
        data += cursor.fetchall()
    connection.close()
    return data
