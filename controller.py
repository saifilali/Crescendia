"""
Project:     Crescendia Game
File:        controller.py
Descritpion: Interface layer controller for lower level functions.
             Holds app routes for incoming requests and processes them.

(In the future, I would like to make it so that no DB accesses are done directly by this python script)
"""

import configparser
import os
import pymysql
from flask import Flask, request, jsonify, redirect
from subprocess import Popen
import logic_battle_system
import db_actions
import db_login_registration
import db_units
import db_user

app = Flask(__name__)

config = configparser.ConfigParser()
config.read("/var/www/config.ini")
match_threshold = 5
sqlhost = config.get("configuration", "sqlhost")
sqluser = config.get("configuration", "sqluser")
sqlpassword = config.get("configuration", "sqlpassword")
sqldatabase = config.get("configuration", "sqldatabase")
siteurl = config.get("configuration", "siteurl")
match_threshold = config.get("configuration", "match_threshold")

# Balance variables, should move to config eventually
power_level_scale = [0, 1, 1.25, 1.5, 2, 2.5]
health_level_scale = [0, 1, 1.25, 1.5, 2, 2.5]
defense_level_scale = [0, 1, 1.25, 1.5, 2, 2.5]
energy_level_scale = [0, 1, 1.1, 1.2, 1.3, 1.4]
speed_level_scale = [0, 1, 1.1, 1.2, 1.3, 1.4]


def closeness(key):
    try:
        keydiffs = []
        keydiffs.append(abs((key[0] - key[1])))
        keydiffs.append(abs((key[0] - key[2])))
        keydiffs.append(abs((key[0] - key[3])))
        keydiffs.append(abs((key[1] - key[2])))
        keydiffs.append(abs((key[1] - key[3])))
        keydiffs.append(abs((key[2] - key[3])))
        keysum = 0
        for keydiff in keydiffs:
            if keydiff > 6:
                keydiff = 12 - keydiff
            keysum = keydiff + keysum
        print(key)
        index = (36 - keysum) / 24
        print(index)
        return(index)
    except:
        return(0.5)


def squad_average_level(squad_id):
    try:
        connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                     db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
        average_level = 0
        with connection.cursor() as cursor:
            sql = "SELECT AVG(user_units.level) FROM user_units INNER JOIN user_squads ON (user_units.song_id = user_squads.song_1_id OR user_units.song_id = user_squads.song_2_id OR user_units.song_id = user_squads.song_3_id OR user_units.song_id = user_squads.song_0_id) WHERE user_squads.squad_id = " + squad_id
            cursor.execute(sql)
            data = cursor.fetchall()
            average_level = float(data[0]['AVG(user_units.level)'])
        connection.close()
        return (average_level)
    except:
        return(1)


def squad_harmony_index(squad_id):
    try:
        connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                     db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
        closeness_index = 0
        with connection.cursor() as cursor:
            sql = "SELECT songs_master.song_key FROM songs_master INNER JOIN user_squads ON (songs_master.id = user_squads.song_1_id OR songs_master.id = user_squads.song_2_id OR songs_master.id = user_squads.song_3_id OR songs_master.id = user_squads.song_0_id) WHERE user_squads.squad_id = " + squad_id
            cursor.execute(sql)
            data = cursor.fetchall()
            keys = []
            for key in data:
                keys.append(int(key["song_key"][:-1]))
            closeness_index = closeness(keys)
        connection.close()
        return (closeness_index)
    except:
        return (0.5)


def squad_strength(squad_id):
    closeness_index = squad_harmony_index(squad_id)
    average_level = squad_average_level(squad_id)
    strength_index = closeness_index * average_level
    return (strength_index)

# Gets data for a single unit


@app.route('/units/show_unit')
def units_show_unit():
    song_id = request.args.get('song_id', '')
    return jsonify(db_units.get_unit_data(song_id))

# Gets data for all units


@app.route('/units/show_all')
def units_show_all():
    return jsonify(db_units.get_all_unit_data())

# gets unit list given search criteria


@app.route('/units/search')
def units_search():
    query = request.args.get('query', '')
    return jsonify(db_units.get_search_unit(query))


@app.route('/user/add_friend')
def user_add_friend():
    user_id = request.args.get('user_id', '')
    friend_id = request.args.get('friend_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        sql = "INSERT IGNORE INTO friends (user_1_id, user_2_id, request_status) VALUES(" + \
            user_id + "," + friend_id + ",'pending')"
        with connection.cursor() as cursor:
            cursor.execute(sql)
            print("1")
            connection.commit()
            print("2")
            connection.close()
            print("x")
        return redirect(siteurl + "/user/home.php")
    else:
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/friend_action')
def user_friend_action():
    user_id = request.args.get('user_id', '')
    friend_id = request.args.get('friend_id', '')
    action = request.args.get('action', '')
    print("smol bean")
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + friend_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        print(data)
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            print("NYANPASU")
            sql = "UPDATE friends SET request_status = '" + action + "', updated_time =NOW() WHERE user_1_id = " + \
                str(user_id) + " AND user_2_id =" + str(friend_id)
            print(sql)
            with connection.cursor() as cursor:
                cursor.execute(sql)
                connection.commit()
                connection.close()
                print(siteurl + "/user/home.php")
            return redirect(siteurl + "/user/home.php")
        else:
            print("Auth failed lol")
            connection.close()
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/show_friends')
def user_show_friends():
    user_id = request.args.get('user_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        sql = "SELECT * FROM friends WHERE user_1_id = " + user_id + " OR user_2_id =" + user_id
        with connection.cursor() as cursor:
            cursor.execute(sql)
            data = cursor.fetchall()
        connection.close()
        return jsonify(data)
    else:
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/units/get_soundcloud')
def units_get_soundcloud():
    user_id = request.args.get('user_id', '')
    sc_id = request.args.get('sc_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, (user_id))
        data = cursor.fetchall()
    # check if user auth is valid, else redirect them
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        print("Trigger the script to analyze a soundcloud song i guess nyaaa")
        Popen(['python3', '/var/crescendia/songanalyzer/scdownloader.py',
               '-i', str(sc_id)], cwd="/var/crescendia/songanalyzer")
        return redirect(siteurl + "/user/home.php?comment=processing")
    else:
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/search')
def user_search():
    query = request.args.get('query', '')
    return jsonify(db_user.get_user_data(query))


@app.route('/guild/search')
def guild_search():
    query = request.args.get('query', '')
    return jsonify(db_user.get_guild_data(query))
# gets data for all user squads, given a user and correct authentication hash


@app.route('/user/show_all_squads')
def user_show_all_squads():
    user_id = request.args.get('user_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, (user_id))
        data = cursor.fetchall()
    # check if user auth is valid, else redirect them
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        with connection.cursor() as cursor:
            sql = "SELECT * FROM user_squads WHERE user_id = %s ORDER BY last_time DESC"
            cursor.execute(sql, (user_id))
            data = cursor.fetchall()
        for squad in data:
            squad_id = str(squad["squad_id"])
            h_index = squad_harmony_index(squad_id)
            squad["harmony_index"] = str(h_index)
            squad["average_level"] = str(squad_average_level(squad_id))
            squad["strength_index"] = str(squad_strength(squad_id))

            if(h_index == 1.5):
                squad["harmony_index_string"] = "Perfect Harmony!"
            elif(h_index > 1.3):
                squad["harmony_index_string"] = "Excellent Harmony!"
            elif(h_index > 1.1):
                squad["harmony_index_string"] = "Good Harmony!"
            elif(h_index > 0.9):
                squad["harmony_index_string"] = "Average Harmony!"
            elif(h_index > 0.7):
                squad["harmony_index_string"] = "Mediocre Harmony!"
            elif(h_index > 0.7):
                squad["harmony_index_string"] = "Terrible Harmony!"
        connection.close()
        return jsonify(data)
    else:
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")
# gets data for a single squad given the correct authentication hash


@app.route('/user/show_squad')
def user_show_squad():
    user_id = request.args.get('user_id', '')
    squad_id = request.args.get('squad_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, (user_id))
        data = cursor.fetchall()
    # check if user auth is valid, else redirect them
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        with connection.cursor() as cursor:
            sql = "SELECT * FROM user_squads WHERE squad_id = %s"
            cursor.execute(sql, (squad_id))
            data = cursor.fetchall()
        connection.close()
        for squad in data:
            h_index = squad_harmony_index(squad_id)
            squad["harmony_index"] = str(h_index)
            squad["average_level"] = str(squad_average_level(squad_id))
            squad["strength_index"] = str(squad_strength(squad_id))

            if(h_index == 1.5):
                squad["harmony_index_string"] = "Perfect Harmony!"
            elif(h_index > 1.3):
                squad["harmony_index_string"] = "Excellent Harmony!"
            elif(h_index > 1.1):
                squad["harmony_index_string"] = "Good Harmony!"
            elif(h_index > 0.9):
                squad["harmony_index_string"] = "Average Harmony!"
            elif(h_index > 0.7):
                squad["harmony_index_string"] = "Mediocre Harmony!"
            elif(h_index > 0.7):
                squad["harmony_index_string"] = "Terrible Harmony!"
        return jsonify(data)
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/show_squad_strength')
def user_show_squad_strength():
    user_id = request.args.get('user_id', '')
    squad_id = request.args.get('squad_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, (user_id))
        data = cursor.fetchall()
    # check if user auth is valid, else redirect them
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        return str(squad_strength(squad_id))
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/squad_choose_headliner')
def user_squad_choose_headliner():
    user_id = request.args.get('user_id', '')
    squad_id = request.args.get('squad_id', '')
    headliner = request.args.get('headliner', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, (user_id))
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "UPDATE user_squads SET headliner =%s WHERE squad_id = %s AND user_id = %s"
            cursor.execute(sql, (headliner, squad_id, user_id))
            data = cursor.fetchall()
        connection.commit()
        connection.close()
        return redirect(siteurl + "/user/squad_config.php?squad_id=" + str(squad_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/squad_set_unit')
def user_squad_set_unit():
    user_id = request.args.get('user_id', '')
    squad_id = request.args.get('squad_id', '')
    song_id = request.args.get('song_id', '')
    slot = request.args.get('slot', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, (user_id))
        data = cursor.fetchall()
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        with connection.cursor() as cursor:
            sql = "SELECT * FROM user_squads WHERE user_id = %s AND squad_id = %s"
            cursor.execute(sql, (user_id, squad_id))
            data = cursor.fetchall()
        song_0_id = str(data[0]['song_0_id'])
        song_1_id = str(data[0]['song_1_id'])
        song_2_id = str(data[0]['song_2_id'])
        song_3_id = str(data[0]['song_3_id'])
        print(song_0_id)
        print(song_1_id)
        print(song_2_id)
        print(song_3_id)
        print(song_id)
        print(type(song_0_id))
        print(type(song_id))
        isunique = False
        # print("slot is" +slot)
        if(slot == '0'):
            if(song_1_id != song_id and song_2_id != song_id and song_3_id != song_id):
                isunique = True
        elif(slot == '1'):
            if(song_0_id != song_id and song_2_id != song_id and song_3_id != song_id):
                isunique = True
        elif(slot == '2'):
            if(song_1_id != song_id and song_0_id != song_id and song_3_id != song_id):
                isunique = True
        elif(slot == '3'):
            if(song_1_id != song_id and song_2_id != song_id and song_0_id != song_id):
                isunique = True
        if(isunique == True):
            with connection.cursor() as cursor:
                sql = "UPDATE user_squads SET song_" + slot + "_id =%s WHERE squad_id = %s AND user_id = %s"
                cursor.execute(sql, (str(song_id), squad_id, user_id))
            connection.commit()
            connection.close()
            return redirect(siteurl + "/user/squad_config.php?squad_id=" + str(squad_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
        else:
            connection.close()
            return redirect(siteurl + "/user/squad_config.php?comment=duplicate_unit&squad_id=" + str(squad_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/squad_set_name')
def user_squad_set_name():
    user_id = request.args.get('user_id', '')
    squad_id = request.args.get('squad_id', '')
    squad_name = request.args.get('squad_name', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, (user_id))
        data = cursor.fetchall()
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        with connection.cursor() as cursor:
            sql = "UPDATE user_squads SET name =%s WHERE squad_id = %s AND user_id = %s"
            cursor.execute(sql, (str(squad_name), squad_id, user_id))
            data = cursor.fetchall()
        connection.commit()
        connection.close()
        return redirect(siteurl + "/user/squad_config.php?squad_id=" + str(squad_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/delete_squad')
def user_delete_squad():
    user_id = request.args.get('user_id', '')
    squad_id = request.args.get('squad_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, (user_id))
        data = cursor.fetchall()
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        with connection.cursor() as cursor:
            sql = "DELETE FROM user_squads WHERE squad_id = %s AND user_id = %s"
            cursor.execute(sql, (squad_id, user_id))
            data = cursor.fetchall()
        connection.commit()
        connection.close()
        return redirect(siteurl + "/user/home.php?comment=" + "Deleted that squad&" + "user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/add_squad')
def user_add_squad():
    user_id = request.args.get('user_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, (user_id))
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "SELECT* FROM user_squads WHERE user_id =%s"
            cursor.execute(sql, (user_id))
            squads = cursor.fetchall()
        if(len(squads) >= data[0]['squads_max']):
            # too many squads, can't make new!
            connection.close()
            return redirect(siteurl + "/user/home.php?comment=max_squads&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
        else:
            with connection.cursor() as cursor:
                sql = "INSERT INTO user_squads (user_id) VALUES (%s)"
                cursor.execute(sql, (user_id))
                squad_id = cursor.lastrowid
            connection.commit()
            connection.close()
            return redirect(siteurl + "/user/squad_config.php?squad_id=" + str(squad_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/show_unit')
def user_show_unit():
    user_id = request.args.get('user_id', '')
    song_id = request.args.get('song_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "SELECT * FROM user_units INNER JOIN songs_master ON user_units.song_id=songs_master.id WHERE user_id = " + \
                user_id + " AND song_id = " + song_id + " ORDER BY last_time DESC"
            cursor.execute(sql)
            data = cursor.fetchall()
        connection.close()
        for song in data:
            song['power'] = int(song['power'] * power_level_scale[song['level']])
            song['health'] = int(song['health'] * health_level_scale[song['level']])
            song['defense'] = int(song['defense'] * defense_level_scale[song['level']])
            song['energy'] = int(song['energy'] * energy_level_scale[song['level']])
            song['speed'] = int(song['speed'] * speed_level_scale[song['level']])
        return jsonify(data)
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/show_all_units')
def user_show_all_units():
    user_id = request.args.get('user_id', '')
    user_query_id = request.args.get('user_query_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            if(request.args.get('user_query_id') != None):
                sql = "SELECT * FROM user_units INNER JOIN songs_master ON user_units.song_id=songs_master.id WHERE user_id = " + \
                    user_query_id + " ORDER BY last_time DESC"
            else:
                sql = "SELECT * FROM user_units INNER JOIN songs_master ON user_units.song_id=songs_master.id WHERE user_id = " + \
                    user_id + " ORDER BY last_time DESC"
            cursor.execute(sql)
            data = cursor.fetchall()
        connection.close()
        for song in data:
            song['power'] = int(song['power'] * power_level_scale[song['level']])
            song['health'] = int(song['health'] * health_level_scale[song['level']])
            song['defense'] = int(song['defense'] * defense_level_scale[song['level']])
            song['energy'] = int(song['energy'] * energy_level_scale[song['level']])
            song['speed'] = int(song['speed'] * speed_level_scale[song['level']])

        return jsonify(data)
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/set_actions_unit')
def user_set_actions_unit():
    user_id = request.args.get('user_id', '')
    song_id = request.args.get('song_id', '')
    go_home = request.args.get('go_home', '')
    actions_ids = request.args.getlist('actions_selected')
    # print(actions_ids)
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM users WHERE user_id = %s;"
        cursor.execute(sql, user_id)
        data = cursor.fetchall()
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        if(len(actions_ids) == 2):
            with connection.cursor() as cursor:
                sql = "UPDATE user_units SET last_time=NOW(), action_A = %s, action_B = %s WHERE user_id= %s AND song_id= %s"
                cursor.execute(sql, (str(actions_ids[0]), str(
                    actions_ids[1]), str(user_id), str(song_id)))
            connection.commit()
            connection.close()
            if(go_home):
                return redirect(siteurl + "/user/home.php?user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
            else:
                return redirect(siteurl + "/user/unit_config.php?song_id=" + str(song_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
        else:
            connection.close()
            return redirect(siteurl + "/user/unit_config.php?comment=not_enough_actions&song_id=" + str(song_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/display_unit_moves')
def user_display_unit_moves():
    user_id = request.args.get('user_id', '')
    song_id = request.args.get('song_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "SELECT * FROM user_units INNER JOIN songs_master ON user_units.song_id=songs_master.id WHERE user_id = " + \
                user_id + " AND song_id = " + song_id + " ORDER BY last_time DESC"
            cursor.execute(sql)
            songs = cursor.fetchall()
        for song in songs:
            song['power'] = int(song['power'] * power_level_scale[song['level']])
            song['health'] = int(song['health'] * health_level_scale[song['level']])
            song['defense'] = int(song['defense'] * defense_level_scale[song['level']])
            song['energy'] = int(song['energy'] * energy_level_scale[song['level']])
            song['speed'] = int(song['speed'] * speed_level_scale[song['level']])
            return jsonify([db_actions.print_passive(song['passive'], (song['power'])), db_actions.print_action(song['action_A'], song['power']), db_actions.print_action(song['action_B'], song['power'])])
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/display_unit_moves_all')
def user_display_unit_moves_all():
    user_id = request.args.get('user_id', '')
    song_id = request.args.get('song_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "SELECT * FROM user_units INNER JOIN songs_master ON user_units.song_id=songs_master.id WHERE user_id = " + \
                user_id + " AND song_id = " + song_id + " ORDER BY last_time DESC"
            cursor.execute(sql)
            songs = cursor.fetchall()
        for song in songs:
            song['power'] = int(song['power'] * power_level_scale[song['level']])
            song['health'] = int(song['health'] * health_level_scale[song['level']])
            song['defense'] = int(song['defense'] * defense_level_scale[song['level']])
            song['energy'] = int(song['energy'] * energy_level_scale[song['level']])
            song['speed'] = int(song['speed'] * speed_level_scale[song['level']])
            return jsonify([db_actions.print_passive(song['passive'], song['power']), db_actions.print_action(song['action_0'], song['power']), db_actions.print_action(song['action_1'], song['power']), db_actions.print_action(song['action_2'], song['power']), db_actions.print_action(song['action_3'], song['power']), db_actions.print_action(song['action_4'], song['power'])])
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/add_unit')
def user_add_unit():
    user_id = request.args.get('user_id', '')
    song_id = request.args.get('song_id', '')
    do_configure = request.args.get('configure', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        firstowner = False
        with connection.cursor() as cursor:
            sql = "SELECT * FROM user_units WHERE song_id LIKE '" + song_id + "';"
            cursor.execute(sql)
            data = cursor.fetchall()
        if(len(data) == 0):
            firstowner = True
        with connection.cursor() as cursor:
            sql = "SELECT * FROM user_units WHERE user_id LIKE '" + user_id + "';"
            cursor.execute(sql)
            allunits = cursor.fetchall()
            sql = "SELECT * FROM users WHERE user_id LIKE '" + user_id + "';"
            cursor.execute(sql)
            userdata = cursor.fetchall()
        # print(len(allunits))
        if(len(allunits) >= userdata[0]['units_max']):
            connection.close()
            return "You have have reached your maximum unit capacity!"
        with connection.cursor() as cursor:
            if(firstowner == True):
                sql = "INSERT INTO user_units (user_id, song_id, firstowner) VALUES(" + \
                    user_id + "," + song_id + ", 1)"
            else:
                sql = "INSERT INTO user_units (user_id, song_id) VALUES(" + \
                    user_id + "," + song_id + ")"
            try:
                cursor.execute(sql)
            except:
                connection.commit()
                connection.close()
                return redirect(siteurl + "/user/home.php?comment=" + "You already own this unit!&" + "user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
        connection.commit()
        connection.close()
        if (do_configure):
            return redirect(siteurl + "/user/unit_config.php?configure=true&" + "song_id=" + str(song_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
        else:
            return redirect(siteurl + "/user/home.php?comment=" + "Added that unit&" + "user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/delete_unit')
def user_delete_unit():
    user_id = request.args.get('user_id', '')
    song_id = request.args.get('song_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "DELETE FROM user_units WHERE song_id = '" + song_id + "'AND user_id = '" + user_id + "';"
            cursor.execute(sql)
            sql = "UPDATE user_squads SET song_0_id = NULL WHERE  user_id = " + \
                user_id + " AND song_0_id = " + song_id
            cursor.execute(sql)
            sql = "UPDATE user_squads SET song_1_id = NULL WHERE  user_id = " + \
                user_id + " AND song_1_id = " + song_id
            cursor.execute(sql)
            sql = "UPDATE user_squads SET song_2_id = NULL WHERE  user_id = " + \
                user_id + " AND song_2_id = " + song_id
            cursor.execute(sql)
            sql = "UPDATE user_squads SET song_3_id = NULL WHERE user_id = " + user_id + " AND song_3_id = " + song_id
            cursor.execute(sql)
            data = cursor.fetchall()
        connection.commit()
        connection.close()
        return redirect(siteurl + "/user/home.php?comment=" + "Deleted that unit&" + "user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/level_unit')
def user_level_unit():
    user_id = request.args.get('user_id', '')
    song_id = request.args.get('song_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        if(10 <= int(data[0]['money_bought'])):
            with connection.cursor() as cursor:
                sql = "UPDATE user_units SET instant_level_up = 1 WHERE song_id = '" + \
                    song_id + "'AND user_id = '" + user_id + "';"
                cursor.execute(sql)
                sql = "UPDATE users SET money_bought = money_bought-10 WHERE user_id = '" + user_id + "';"
                cursor.execute(sql)
                data = cursor.fetchall()
            connection.commit()
            connection.close()
            return redirect(siteurl + "/user/unit_config.php?comment=leveled_up&configure=true&" + "song_id=" + str(song_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
        else:
            connection.close()
            return redirect(siteurl + "/user/unit_config.php?comment=not_enough_platinum&configure=true&" + "song_id=" + str(song_id) + "&user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/buy_slot')
def user_buy_slot():
    user_id = request.args.get('user_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        if(10 <= int(data[0]['money_bought'])):
            with connection.cursor() as cursor:
                sql = "UPDATE users SET units_max = units_max+5 WHERE user_id = '" + user_id + "';"
                cursor.execute(sql)
                sql = "UPDATE users SET money_bought = money_bought-10 WHERE user_id = '" + user_id + "';"
                cursor.execute(sql)
                data = cursor.fetchall()
            connection.commit()
            connection.close()
            return redirect(siteurl + "/user/home.php?user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
        else:
            connection.close()
            return redirect(siteurl + "/user/home.php?user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        # print("auth is invalid")
        connection.close()
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/register')
def user_register():
    username = request.args.get('username', '')
    email = request.args.get('email', '')
    password_hash = request.args.get('auth', '')
    alphakey = request.args.get('alphakey', '')
    return db_login_registration.user_register(username, email, password_hash, alphakey)


@app.route('/user/login')
def user_login():
    username = request.args.get('username', '')
    password_hash = request.args.get('auth', '')
    return db_login_registration.user_login(username, password_hash)


@app.route('/user/get_info')
def user_get_info():
    user_id = request.args.get('user_id', '')
    auth = request.args.get('auth', '')
    if(user_id != '' and auth != ''):
        # print("All the fields are in")
        connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                     db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
        user_exists = False
        with connection.cursor() as cursor:
            sql = "SELECT * FROM users WHERE user_id LIKE '" + user_id + "';"
            cursor.execute(sql)
            data = cursor.fetchall()
        if(len(data) > 0):
            user_exists = True
        if(user_exists):
            if(auth == data[0]['password_hash']):
                return jsonify(data)
                connection.close()
            else:
                return "Bad auth"
                connection.close()
        else:
            # print("User doesn't exist")
            return "Bad Username"
            connection.close()
    else:
        return "Missing username or password"
    return "Bad request"


@app.route('/user/get_waifu_greeting')
def user_get_waifu_greeting():
    user_id = request.args.get('user_id', '')
    auth = request.args.get('auth', '')
    if(user_id != '' and auth != ''):
        # print("All the fields are in")
        connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                     db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
        user_exists = False
        with connection.cursor() as cursor:
            sql = "SELECT * FROM users WHERE user_id LIKE '" + user_id + "';"
            cursor.execute(sql)
            data = cursor.fetchall()
            if(len(data) > 0):
                user_exists = True
            if(user_exists):
                if(auth == data[0]['password_hash']):
                    sql = "SELECT face_index FROM songs_master WHERE id = '" + \
                        str(data[0]['waifu']) + "';"
                    cursor.execute(sql)
                    waifudata = cursor.fetchone()
                    sql = "SELECT text, file FROM dialogue WHERE personality = " + \
                        str(waifudata['face_index'] % 10) + \
                        " AND type = 'greeting' ORDER BY RAND() LIMIT 1"
                    print(sql)
                    cursor.execute(sql)
                    quote = cursor.fetchone()
                    return jsonify(quote)
                    connection.close()
                else:
                    return "Bad auth"
                    connection.close()
            else:
                # print("User doesn't exist")
                return "Bad Username"
                connection.close()
    else:
        return "Missing username or password"
    return "Bad request"


@app.route('/user/get_info_public')
def user_get_info_public():
    user_id = request.args.get('user_id', '')
    query_user_id = request.args.get('query_user_id', '')
    auth = request.args.get('auth', '')
    if(user_id != '' and auth != ''):
        # print("All the fields are in")
        connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                     db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
        user_exists = False
        with connection.cursor() as cursor:
            sql = "SELECT user_id, username, created_time, last_login_time, battles_total, battles_won, battles_lost, battles_tied, battles_quit FROM users WHERE user_id =" + query_user_id + ";"
            cursor.execute(sql)
            data = cursor.fetchall()
        if(len(data) > 0):
            user_exists = True
        if(user_exists):
            return jsonify(data)
        else:
            # print("User doesn't exist")
            return "Bad Username"
            connection.close()
    else:
        return "Missing username or password"
    return "Bad request"


@app.route('/user/join_guild')
def user_join_guild():
    user_id = request.args.get('user_id', '')
    guild_id = request.args.get('guild_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "UPDATE users SET guild = " + guild_id + " WHERE user_id = '" + user_id + "';"
            cursor.execute(sql)
            data = cursor.fetchall()
        connection.commit()
        connection.close()
        return redirect(siteurl + "/user/home.php")
    else:
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/guild/make_guild')
def guild_make_guild():
    user_id = request.args.get('user_id', '')
    name = request.args.get('guild_id', '')
    description = request.args.get('guild_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "INSERT INTO guild (name, founder, owner, description) VALUES (%s, %s, %s, %s);"
            cursor.execute(sql, (name, user_id, user_id, description))
            data = cursor.fetchall()
        connection.commit()
        connection.close()
        return redirect(siteurl + "/user/home.php")
    else:
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/add_gold')
def user_add_gold():
    user_id = request.args.get('user_id', '')
    gold = request.args.get('gold', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "UPDATE users SET money_earned = money_earned+" + gold + " WHERE user_id = '" + user_id + "';"
            cursor.execute(sql)
            data = cursor.fetchall()
        connection.commit()
        connection.close()
        return redirect(siteurl + "/user/home.php?user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/user/add_platinum')
def user_add_platinum():
    user_id = request.args.get('user_id', '')
    platinum = request.args.get('platinum', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        # print(data)
    if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
        # print("auth is valid")
        with connection.cursor() as cursor:
            sql = "UPDATE users SET money_bought = money_bought+" + \
                platinum + " WHERE user_id = '" + user_id + "';"
            cursor.execute(sql)
            data = cursor.fetchall()
        connection.commit()
        connection.close()
        return redirect(siteurl + "/user/home.php?user_id=" + str(request.args.get('user_id', '')) + "&auth=" + str(request.args.get('auth', '') + ""))
    else:
        return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/request_add')
def battle_request_add():
    user_id = request.args.get('user_id', '')
    squad_id = request.args.get('squad_id', '')
    friend_id = request.args.get('friend_id', '')
    print("poop")
    # battle_id = request.args.get('battle_id','')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        print("poop")
        # print(data)
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:

            sql = "SELECT * FROM battle_requests WHERE user_1_id = " + user_id + " AND status = 'open'"
            cursor.execute(sql)
            data = cursor.fetchall()
            if(len(data) > 0):
                return redirect(siteurl + "/user/home.php?comment=already in battle queue")
            else:
                print("yay")
                squad_strength_index = str(squad_strength(squad_id))
                if(request.args.get('friend') != None):
                    sql = "INSERT INTO battle_requests (user_1_id, user_1_squad, user_1_strength, user_2_id) VALUES (" + \
                        user_id + "," + squad_id + "," + squad_strength_index + "," + friend_id + ")"
                    print(sql)
                if(request.args.get('random') != None):
                    # Look up if any random battles exist. Get the one with the closest
                    # average level
                    sql = "SELECT * FROM battle_requests WHERE user_2_id is NULL AND status ='open' AND ABS(user_1_strength - " + \
                        squad_strength_index + ") < " + match_threshold + \
                        " ORDER BY ABS(user_1_strength - " + squad_strength_index + ")"
                    cursor.execute(sql)
                    data = cursor.fetchall()
                    if(len(data) > 0):
                        sql = "UPDATE battle_requests SET user_2_id = " + user_id + ", user_2_squad = " + squad_id + ", user_2_strength = " + \
                            squad_strength_index + ",status = \"accepted\" WHERE battle_id =" + \
                            str(data[0]['battle_id'])
                        cursor.execute(sql)
                        connection.commit()
                        connection.close()
                        return redirect(siteurl + "/user/battle.php")
                    else:
                        sql = "INSERT INTO battle_requests (user_1_id, user_1_squad, user_1_strength) VALUES (" + \
                            user_id + "," + squad_id + "," + squad_strength_index + ")"
                if(request.args.get('bots') != None):
                    sql = "INSERT INTO battle_requests (user_1_id, user_1_squad, user_1_strength, user_2_id) VALUES (" + \
                        user_id + "," + squad_id + "," + squad_strength_index + ",0)"
                    print(sql)
                cursor.execute(sql)
                connection.commit()
                connection.close()
                return redirect(siteurl + "/user/home.php")
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/request_accept')
def battle_request_accept():
    user_id = request.args.get('user_id', '')
    squad_id = request.args.get('squad_id', '')
    # battle_id = request.args.get('battle_id','')
    battle_id = request.args.get('battle_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        print("poop")
        # print(data)
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            sql = "UPDATE battle_requests SET status = 'expired' WHERE user_1_id=" + user_id + " AND status = 'open'"
            cursor.execute(sql)
            if(request.args.get('accept') != None):
                sql = "UPDATE battle_requests SET user_2_id = " + user_id + ", user_2_strength =" + \
                    str(squad_strength(squad_id)) + ", user_2_squad = " + squad_id + \
                    ", status = \"accepted\" WHERE battle_id =" + battle_id
                Popen(['python3', 'regulartasks.py'], cwd="/var/www/")
            else:
                sql = "UPDATE battle_requests SET user_2_id = " + user_id + \
                    ", status = \"expired\" WHERE battle_id =" + battle_id
            print(sql)
            cursor.execute(sql)
            connection.commit()
            connection.close()
            return redirect(siteurl + "/user/battle.php")
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/request_check')
def battle_request_check():
    user_id = request.args.get('user_id', '')
    status = request.args.get('status', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            sql = "SELECT * FROM battle_requests WHERE (user_1_id = " + user_id + \
                " OR user_2_id = " + user_id + ") AND status = '" + status + "'"
            if(status == "accepted"):
                sql = "SELECT * FROM battle_requests WHERE (user_1_id = " + user_id + \
                    " OR user_2_id = " + user_id + ") AND status = '" + status + "'"
            cursor.execute(sql)
            data = cursor.fetchall()
            connection.commit()
            connection.close()
            return jsonify(data)
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/request_challenge')
def battle_request_challenge_random():
    user_id = request.args.get('user_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            sql = "SELECT * FROM battle_requests WHERE user_2_id = NULL AND status = 'open'"
            cursor.execute(sql)
            data = cursor.fetchall()
            connection.commit()
            connection.close()
            return jsonify(data)
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/request_challenge_friend')
def battle_request_challenge_friend():
    user_id = request.args.get('user_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            sql = "SELECT * FROM battle_requests WHERE user_2_id = " + user_id + " AND status ='open'"
            cursor.execute(sql)
            data = cursor.fetchall()
            connection.commit()
            connection.close()
            return jsonify(data)
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/active_get_info')
def battle_active_get_info():
    user_id = request.args.get('user_id', '')

    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            sql = "SELECT * FROM battle_requests WHERE (user_2_id = " + user_id + " OR user_1_id = " + \
                user_id + ") AND (status = 'accepted' OR status='active') LIMIT 1"
            cursor.execute(sql)
            data = cursor.fetchone()
            if(not data):
                sql = "SELECT * FROM battle_requests WHERE (user_2_id = " + user_id + " OR user_1_id = " + \
                    user_id + ") AND status = 'finished' ORDER BY updated_at DESC LIMIT 1"
                cursor.execute(sql)
                data = cursor.fetchone()
            connection.commit()
            connection.close()
            return jsonify(data)
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/get_unit_info')
def battle_get_unit_info():
    user_id = request.args.get('user_id', '')
    battle_id = request.args.get('battle_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            sql = "SELECT * FROM battle_unit_stats WHERE battle_id =" + battle_id
            cursor.execute(sql)
            data = cursor.fetchall()
            connection.commit()
            connection.close()
            return jsonify(data)
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/get_effect_info')
def battle_get_effect_info():
    user_id = request.args.get('user_id', '')
    battle_id = request.args.get('battle_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            sql = "SELECT * FROM battle_effect_queue WHERE battle_id =" + battle_id
            cursor.execute(sql)
            data = cursor.fetchall()
            connection.commit()
            connection.close()
            return jsonify(data)
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/get_queue_info')
def battle_get_queue_info():
    user_id = request.args.get('user_id', '')
    battle_id = request.args.get('battle_id', '')
    turn = request.args.get('turn', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            if(request.args.get('unprocessed') != None):
                sql = "SELECT * FROM battle_action_queue WHERE processed = 0 AND battle_id = %s"
                cursor.execute(sql, (battle_id))
            elif(request.args.get('receipt') != None):
                sql = "SELECT * FROM battle_action_queue WHERE battle_id = %s AND turn = %s ORDER BY `order` DESC"
                cursor.execute(sql, (battle_id, str(int(turn) - 1)))
            else:
                sql = "SELECT * FROM battle_action_queue WHERE battle_id = %s"
                cursor.execute(sql, (battle_id))
            data = cursor.fetchall()
            connection.commit()
            connection.close()
            return jsonify(data)
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/battle/add_action_to_queue')
def battle_add_action_to_queue():
    user_id = request.args.get('user_id', '')
    battle_id = request.args.get('battle_id', '')
    slot = request.args.get('slot', '')
    turn = request.args.get('turn', '')
    team = request.args.get('team', '')
    target_team = request.args.get('target_team', '')
    target_unit = request.args.get('target_unit', '')
    action_slot = request.args.get('action_slot', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            sql = "SELECT * FROM battle_unit_stats WHERE battle_id =" + \
                battle_id + " AND team = " + team + " AND slot =" + slot
            print(sql)
            cursor.execute(sql)
            song_info = cursor.fetchone()
            if(action_slot == "A"):
                action_code = song_info["action_A"]
                action_target_team = "enemy"
            if(action_slot == "B"):
                action_code = song_info["action_B"]
                action_target_team = "enemy"
            if(action_slot == "X"):
                action_code = "B0"
                action_target_team = "enemy"
            if(action_slot == "Z"):
                action_code = "Z0"
                action_target_team = "enemy"
            sql = "INSERT IGNORE INTO battle_action_queue (battle_id, turn, team, unit, unit_key, unit_speed, action_code, action_target_team, action_target_unit, processed) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s);"
            params = (battle_id, turn, team, slot, song_info["song_key"], song_info[
                "speed_current"], action_code, target_team, target_unit, "0")
            cursor.execute(sql, params)
    connection.commit()
    connection.close()
    Popen(['python3', 'battle_manager.py'], cwd="/var/www/").wait()
    return redirect(siteurl + "/user/battle.php")


@app.route('/battle/get_battle_info')
def battle_get_battle_info():
    user_id = request.args.get('user_id', '')
    battle_id = request.args.get('battle_id', '')
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        # print(user_id)
        sql = "SELECT * FROM users WHERE user_id = '" + user_id + "';"
        cursor.execute(sql)
        data = cursor.fetchall()
        if(len(data) == 1 and request.args.get('auth', '') == data[0]['password_hash']):
            # print("auth is valid")        with connection.cursor() as cursor:
            sql = "SELECT * FROM battle_requests WHERE battle_id =" + battle_id
            cursor.execute(sql)
            data = cursor.fetchall()
            connection.commit()
            connection.close()
            return jsonify(data)
        else:
            return redirect(siteurl + "?comment=bad_auth")


@app.route('/gitpull', methods=['POST'])
def gitpull():
    print("do a git pull and reboot!")
    os.system("sh /var/www/pull.sh")
    # comment 3
    return "200"


@app.route('/gitpull')
def process_all_songs():
    os.system("python3")
    # comment 3
    return "200"
if __name__ == '__main__':
    port = int(os.environ.get('PORT', 8080))
    app.run(host='0.0.0.0', port=port, debug=True)
