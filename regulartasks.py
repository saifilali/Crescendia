import pymysql
import configparser
import random

config = configparser.ConfigParser()
config.read("/var/www/config.ini")

sqlhost = config.get("configuration", "sqlhost")
sqluser = config.get("configuration", "sqluser")
sqlpassword = config.get("configuration", "sqlpassword")
sqldatabase = config.get("configuration", "sqldatabase")
siteurl = config.get("configuration", "siteurl")

level_2_limit = config.get("configuration", "level_2_limit")
level_3_limit = config.get("configuration", "level_3_limit")
level_4_limit = config.get("configuration", "level_4_limit")
level_5_limit = config.get("configuration", "level_5_limit")

power_level_scale = [0, 1, 1.25, 1.5, 2, 2.5]
health_level_scale = [0, 1, 1.25, 1.5, 2, 2.5]
defense_level_scale = [0, 1, 1.25, 1.5, 2, 2.5]
energy_level_scale = [0, 1, 1.1, 1.2, 1.3, 1.4]
speed_level_scale = [0, 1, 1.1, 1.2, 1.3, 1.4]

# update all stats: update win/loss records for users


def add_to_battle():
    print("hiii!!!")
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM battle_requests WHERE status = 'accepted'"
        cursor.execute(sql)
        data = cursor.fetchall()
        for battle in data:
            if(True):
                print(battle)
                print(battle["user_1_squad"])
                battle_id = str(battle["battle_id"])
                sql = "UPDATE battle_requests SET status = 'active', current_turn = 0 WHERE battle_id = " + \
                    str(battle_id)
                cursor.execute(sql)
                connection.commit()
                sql = "SELECT * FROM battle_requests WHERE battle_id = " + str(battle_id)
                cursor.execute(sql)
                battle_info = cursor.fetchall()
                print(battle_info)
                user_1_id = str(battle_info[0]["user_1_id"])
                user_2_id = str(battle_info[0]["user_2_id"])
                connection.commit()
                sql = "SELECT user_units.user_id, user_units.song_id FROM user_units INNER JOIN user_squads ON (user_units.song_id = user_squads.song_0_id OR user_units.song_id = user_squads.song_1_id OR user_units.song_id = user_squads.song_2_id OR user_units.song_id = user_squads.song_3_id) WHERE (user_squads.squad_id = ANY (SELECT user_1_squad FROM battle_requests WHERE battle_id = " + \
                    battle_id + ")) AND user_units.user_id = " + user_1_id + " ORDER BY user_id"
                cursor.execute(sql)
                squad_1_units = cursor.fetchall()

                sql = "SELECT headliner, name FROM user_squads WHERE squad_id =" + \
                    str(battle["user_1_squad"])
                cursor.execute(sql)
                squad_1_data = cursor.fetchone()
                squad_1_headliner = squad_1_data["headliner"]
                squad_1_name = squad_1_data["name"]
                position = 0
                for song in squad_1_units:
                    sql = "SELECT * FROM user_units INNER JOIN songs_master ON user_units.song_id=songs_master.id WHERE user_id = " + \
                        str(song["user_id"]) + " AND song_id =" + str(song["song_id"])
                    cursor.execute(sql)
                    song = cursor.fetchone()
                    isheadliner = 0
                    if(squad_1_headliner == position):
                        isheadliner = 1
                        sql = "UPDATE users SET waifu = " + \
                            str(song["song_id"]) + " WHERE user_id = " + str(song["user_id"])
                        cursor.execute(sql)
                    print(str(song["user_id"]))
                    print(str(song["user_id"]))
                    print("Headliner: " + str(isheadliner))
                    song['power'] = int(song['power'] * power_level_scale[song['level']])
                    song['health'] = int(song['health'] * health_level_scale[song['level']])
                    song['defense'] = int(song['defense'] * defense_level_scale[song['level']])
                    song['energy'] = int(song['energy'] * energy_level_scale[song['level']])
                    song['speed'] = int(song['speed'] * speed_level_scale[song['level']])

                    sql = "SELECT * FROM actions WHERE id='" + str(song['action_A']) + "'"
                    cursor.execute(sql)
                    action_A = cursor.fetchone()
                    sql = "SELECT * FROM actions WHERE id='" + str(song['action_B']) + "'"
                    cursor.execute(sql)
                    action_B = cursor.fetchone()

                    ins_battle_id = str(battle_id)
                    ins_user_id = str(song["user_id"]).replace("'", "''")
                    ins_song_id = str(song["song_id"]).replace("'", "''")
                    ins_team = str("1")
                    ins_slot = str(position)
                    ins_health_current = str(song['health']).replace("'", "''")
                    ins_health_default = str(song['health']).replace("'", "''")
                    ins_energy_current = str(song['energy']).replace("'", "''")
                    ins_energy_default = str(song['energy']).replace("'", "''")
                    ins_defense_current = str(song['defense']).replace("'", "''")
                    ins_defense_default = str(song['defense']).replace("'", "''")
                    ins_power_current = str(song['power']).replace("'", "''")
                    ins_power_default = str(song['power']).replace("'", "''")
                    ins_speed_current = str(song['speed']).replace("'", "''")
                    ins_speed_default = str(song['speed']).replace("'", "''")

                    action_A_description = action_A['description'].replace("POWERMOD", str(
                        int(round(action_A['scale'] * song['power'])))).replace("'", "''")
                    action_B_description = action_B['description'].replace("POWERMOD", str(
                        int(round(action_B['scale'] * song['power'])))).replace("'", "''")

                    ins_class = str(song['class']).replace("'", "''")
                    ins_class_name = str(song['class_name']).replace("'", "''")
                    ins_song_key = str(song['song_key'][:-1]).replace("'", "''")
                    ins_action_A = str(action_A['id']).replace("'", "''")
                    ins_action_A_name = str(action_A['name']).replace("'", "''")
                    ins_action_A_target = str(action_A['target']).replace("'", "''")
                    ins_action_A_cost = str(action_A['cost']).replace("'", "''")
                    ins_action_A_description = action_A_description
                    ins_action_B = str(action_B['id']).replace("'", "''")
                    ins_action_B_name = str(action_B['name']).replace("'", "''")
                    ins_action_B_target = str(action_B['target']).replace("'", "''")
                    ins_action_B_cost = str(action_B['cost']).replace("'", "''")
                    ins_action_B_description = action_B_description
                    ins_passive = str(song['passive']).replace("'", "''")
                    ins_headliner = str(isheadliner)
                    ins_artist = str(song['song_artist']).replace("'", "''")
                    ins_title = str(song['song_title']).replace("'", "''")
                    ins_song_file = str(song['filename']).replace("'", "''")
                    ins_length = str(song['length']).replace("'", "''")
                    ins_tempo = str(song['tempo']).replace("'", "''")
                    ins_face = str(song['face_index']).replace("'", "''")
                    ins_hair = str(song['hair_index']).replace("'", "''")
                    ins_color_0 = str(song['color_0']).replace("'", "''")
                    ins_color_1 = str(song['color_1']).replace("'", "''")
                    ins_color_2 = str(song['color_2']).replace("'", "''")

                    sql = "REPLACE INTO battle_unit_stats (battle_id , user_id , song_id , team , slot , health_current , health_default , energy_current , energy_default , defense_current , defense_default , power_current , power_default , speed_current , speed_default , class, class_name, song_key , action_A, action_A_target, action_B, action_B_target, passive, headliner, artist, title, song_file, length, tempo, face, hair, color_0, color_1, color_2, action_A_cost, action_A_description, action_B_cost, action_B_description, action_A_name, action_B_name ) VALUES (" + ins_battle_id + "," + ins_user_id + "," + ins_song_id + "," + ins_team + "," + ins_slot + "," + ins_health_current + "," + ins_health_default + "," + ins_energy_current + "," + ins_energy_default + "," + \
                        ins_defense_current + "," + ins_defense_default + "," + ins_power_current + "," + ins_power_default + "," + ins_speed_current + "," + ins_speed_default + ",'" + ins_class + "','" + ins_class_name + "','" + ins_song_key + "','" + ins_action_A + "','" + ins_action_A_target + "','" + ins_action_B + "','" + ins_action_B_target + "','" + ins_passive + "'," + ins_headliner + ",'" + \
                        ins_artist + "','" + ins_title + "','" + ins_song_file + "'," + ins_length + "," + ins_tempo + "," + ins_face + "," + ins_hair + ",'" + ins_color_0 + "','" + ins_color_1 + "','" + ins_color_2 + \
                        "'," + ins_action_A_cost + ",'" + ins_action_A_description + "'," + ins_action_B_cost + ",'" + \
                        ins_action_B_description + "','" + ins_action_A_name + "','" + ins_action_B_name + "' )"
                    print(sql)
                    cursor.execute(sql)
                    position = position + 1
                    connection.commit()
                sql = "SELECT user_units.user_id, user_units.song_id FROM user_units INNER JOIN user_squads ON (user_units.song_id = user_squads.song_0_id OR user_units.song_id = user_squads.song_1_id OR user_units.song_id = user_squads.song_2_id OR user_units.song_id = user_squads.song_3_id) WHERE (user_squads.squad_id = ANY (SELECT user_2_squad FROM battle_requests WHERE battle_id = " + \
                    battle_id + ")) AND user_units.user_id = " + user_2_id + " ORDER BY user_id"
                cursor.execute(sql)
                squad_2_units = cursor.fetchall()
                sql = "SELECT headliner, name FROM user_squads WHERE squad_id =" + \
                    str(battle["user_2_squad"])
                cursor.execute(sql)
                squad_2_data = cursor.fetchone()
                squad_2_headliner = squad_2_data["headliner"]
                squad_2_name = squad_2_data["name"]
                position = 0
                for song in squad_2_units:
                    sql = "SELECT * FROM user_units INNER JOIN songs_master ON user_units.song_id=songs_master.id WHERE user_id = " + \
                        str(song["user_id"]) + " AND song_id =" + str(song["song_id"])
                    print
                    cursor.execute(sql)
                    song = cursor.fetchone()
                    isheadliner = 0
                    if(squad_2_headliner == position):
                        isheadliner = 1
                        sql = "UPDATE users SET waifu = " + \
                            str(song["song_id"]) + " WHERE user_id = " + str(song["user_id"])
                        cursor.execute(sql)
                    print(str(song["user_id"]))
                    print(str(song["user_id"]))
                    print("Headliner: " + str(isheadliner))
                    song['power'] = int(song['power'] * power_level_scale[song['level']])
                    song['health'] = int(song['health'] * health_level_scale[song['level']])
                    song['defense'] = int(song['defense'] * defense_level_scale[song['level']])
                    song['energy'] = int(song['energy'] * energy_level_scale[song['level']])
                    song['speed'] = int(song['speed'] * speed_level_scale[song['level']])
                    print(song)
                    sql = "SELECT * FROM actions WHERE id='" + str(song['action_A']) + "'"
                    cursor.execute(sql)
                    action_A = cursor.fetchone()
                    sql = "SELECT * FROM actions WHERE id='" + str(song['action_B']) + "'"
                    cursor.execute(sql)
                    action_B = cursor.fetchone()

                    ins_battle_id = str(battle_id)
                    ins_user_id = str(song["user_id"]).replace("'", "''")
                    ins_song_id = str(song["song_id"]).replace("'", "''")
                    ins_team = str("2")
                    ins_slot = str(position)
                    ins_health_current = str(song['health']).replace("'", "''")
                    ins_health_default = str(song['health']).replace("'", "''")
                    ins_energy_current = str(song['energy']).replace("'", "''")
                    ins_energy_default = str(song['energy']).replace("'", "''")
                    ins_defense_current = str(song['defense']).replace("'", "''")
                    ins_defense_default = str(song['defense']).replace("'", "''")
                    ins_power_current = str(song['power']).replace("'", "''")
                    ins_power_default = str(song['power']).replace("'", "''")
                    ins_speed_current = str(song['speed']).replace("'", "''")
                    ins_speed_default = str(song['speed']).replace("'", "''")
                    print(action_A['description'])
                    print(action_B['description'])
                    action_A_description = action_A['description'].replace("POWERMOD", str(
                        int(round(action_A['scale'] * song['power'])))).replace("'", "''")
                    action_B_description = action_B['description'].replace("POWERMOD", str(
                        int(round(action_B['scale'] * song['power'])))).replace("'", "''")

                    ins_class = str(song['class']).replace("'", "''")
                    ins_class_name = str(song['class_name']).replace("'", "''")
                    ins_song_key = str(song['song_key'][:-1]).replace("'", "''")
                    ins_action_A = str(action_A['id']).replace("'", "''")
                    ins_action_A_name = str(action_A['name']).replace("'", "''")
                    ins_action_A_target = str(action_A['target']).replace("'", "''")
                    ins_action_A_cost = str(action_A['cost']).replace("'", "''")
                    ins_action_A_description = action_A_description
                    ins_action_B = str(action_B['id']).replace("'", "''")
                    ins_action_B_name = str(action_B['name']).replace("'", "''")
                    ins_action_B_target = str(action_B['target']).replace("'", "''")
                    ins_action_B_cost = str(action_B['cost']).replace("'", "''")
                    ins_action_B_description = action_B_description
                    ins_passive = str(song['passive']).replace("'", "''")
                    ins_headliner = str(isheadliner)
                    ins_artist = str(song['song_artist']).replace("'", "''")
                    ins_title = str(song['song_title']).replace("'", "''")
                    ins_song_file = str(song['filename']).replace("'", "''")
                    ins_length = str(song['length']).replace("'", "''")
                    ins_tempo = str(song['tempo']).replace("'", "''")
                    ins_face = str(song['face_index']).replace("'", "''")
                    ins_hair = str(song['hair_index']).replace("'", "''")
                    ins_color_0 = str(song['color_0']).replace("'", "''")
                    ins_color_1 = str(song['color_1']).replace("'", "''")
                    ins_color_2 = str(song['color_2']).replace("'", "''")

                    sql = "REPLACE INTO battle_unit_stats (battle_id , user_id , song_id , team , slot , health_current , health_default , energy_current , energy_default , defense_current , defense_default , power_current , power_default , speed_current , speed_default , class, class_name, song_key , action_A, action_A_target, action_B, action_B_target, passive, headliner, artist, title, song_file, length, tempo, face, hair, color_0, color_1, color_2, action_A_cost, action_A_description, action_B_cost, action_B_description, action_A_name, action_B_name ) VALUES (" + ins_battle_id + "," + ins_user_id + "," + ins_song_id + "," + ins_team + "," + ins_slot + "," + ins_health_current + "," + ins_health_default + "," + ins_energy_current + "," + ins_energy_default + "," + \
                        ins_defense_current + "," + ins_defense_default + "," + ins_power_current + "," + ins_power_default + "," + ins_speed_current + "," + ins_speed_default + ",'" + ins_class + "','" + ins_class_name + "','" + ins_song_key + "','" + ins_action_A + "','" + ins_action_A_target + "','" + ins_action_B + "','" + ins_action_B_target + "','" + ins_passive + "'," + ins_headliner + ",'" + \
                        ins_artist + "','" + ins_title + "','" + ins_song_file + "'," + ins_length + "," + ins_tempo + "," + ins_face + "," + ins_hair + ",'" + ins_color_0 + "','" + ins_color_1 + "','" + ins_color_2 + \
                        "'," + ins_action_A_cost + ",'" + ins_action_A_description + "'," + ins_action_B_cost + ",'" + \
                        ins_action_B_description + "','" + ins_action_A_name + "','" + ins_action_B_name + "' )"
                    print(sql)
                    cursor.execute(sql)
                    position = position + 1
                    connection.commit()
        connection.close()


def pick_winner_by_chance(user_1_strength, user_2_strength, user_1_id, user_2_id):
    outcome = random.uniform(0, (user_1_strength + user_2_strength))
    if(outcome < user_1_strength):
        return user_1_id
    else:
        return user_2_id


def expire_old_requests():
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "UPDATE battle_requests SET status = 'expired' WHERE created_at < (NOW() - INTERVAL 10 MINUTE) AND status = 'open'"
        cursor.execute(sql)
    connection.commit()
    connection.close()


def autogen_battle_outcome():
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT * FROM battle_requests WHERE status = 'accepted'"
        cursor.execute(sql)
        data = cursor.fetchall()
        for battle in data:
            try:
                sql = "UPDATE battle_requests SET status = 'finished', winner = " + str(pick_winner_by_chance(battle['user_1_strength'], battle[
                                                                                        'user_2_strength'], battle['user_1_id'], battle['user_2_id'])) + " WHERE battle_id = " + str(battle['battle_id'])
                cursor.execute(sql)
            except:
                pass
    connection.commit()
    connection.close()


def determine_battle_outcome():
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT battle_id, team, COUNT(team) FROM battle_unit_stats WHERE health_current < 1 GROUP BY team, battle_id;"
        cursor.execute(sql)
        data = cursor.fetchall()
        for battle in data:
            if(battle["COUNT(team)"] == 4):
                try:
                    if(battle["team"] == 1):
                        sql = "UPDATE battle_requests SET status = 'finished', winner = user_2_id WHERE battle_id = " + \
                            str(battle['battle_id'])
                    else:
                        sql = "UPDATE battle_requests SET status = 'finished', winner = user_1_id WHERE battle_id = " + \
                            str(battle['battle_id'])
                    cursor.execute(sql)
                except:
                    pass
    connection.commit()
    connection.close()


def update_winloss_records():
    connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                 db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
    with connection.cursor() as cursor:
        sql = "SELECT user_id FROM users"
        cursor.execute(sql)
        data = cursor.fetchall()

        for user in data:
            try:
                sql = "SELECT COUNT(*) FROM battle_requests WHERE status='finished' AND (user_1_id = " + \
                    str(user["user_id"]) + " OR user_2_id =" + str(user["user_id"]) + " )"
                cursor.execute(sql)
                user_total_battles_object = cursor.fetchone()
                user_total_battles = user_total_battles_object["COUNT(*)"]
                sql = "SELECT COUNT(*) FROM battle_requests WHERE status='finished' AND winner=" + \
                    str(user["user_id"]) + ""
                cursor.execute(sql)
                user_won_battles_object = cursor.fetchone()
                user_won_battles = user_won_battles_object["COUNT(*)"]
                sql = "UPDATE users SET battles_total = " + \
                    str(user_total_battles) + ", battles_won = " + \
                    str(user_won_battles) + " WHERE user_id =" + str(user["user_id"])
                cursor.execute(sql)
            except:
                pass

        sql = "SELECT user_2_squad FROM battle_requests WHERE status = 'finished' AND processed = 0 AND winner = user_2_id;"
        cursor.execute(sql)
        data = cursor.fetchall()
        for squad in data:
            try:
                sql = "SELECT user_units.user_id, user_units.song_id, user_squads.squad_id FROM user_units INNER JOIN user_squads ON (user_units.song_id = user_squads.song_0_id OR user_units.song_id = user_squads.song_1_id OR user_units.song_id = user_squads.song_2_id OR user_units.song_id = user_squads.song_3_id) WHERE (user_squads.squad_id = " + str(squad[
                    "user_2_squad"]) + " AND user_units.user_id = user_squads.user_id);"
                cursor.execute(sql)
                songs = cursor.fetchall()
                for song in songs:
                    sql = "UPDATE user_units SET battles_won = battles_won + 1 WHERE user_id =" + \
                        str(song["user_id"]) + " AND song_id =" + str(song["song_id"])
                    cursor.execute(sql)
            except:
                pass
        sql = "SELECT user_1_squad FROM battle_requests WHERE status = 'finished' AND processed = 0 AND winner = user_1_id;"
        cursor.execute(sql)
        data = cursor.fetchall()
        for squad in data:
            try:
                sql = "SELECT user_units.user_id, user_units.song_id, user_squads.squad_id FROM user_units INNER JOIN user_squads ON (user_units.song_id = user_squads.song_0_id OR user_units.song_id = user_squads.song_1_id OR user_units.song_id = user_squads.song_2_id OR user_units.song_id = user_squads.song_3_id) WHERE (user_squads.squad_id = " + str(squad[
                    "user_1_squad"]) + " AND user_units.user_id = user_squads.user_id);"
                cursor.execute(sql)
                songs = cursor.fetchall()
                for song in songs:
                    sql = "UPDATE user_units SET battles_won = battles_won + 1 WHERE user_id =" + \
                        str(song["user_id"]) + " AND song_id =" + str(song["song_id"])
                    cursor.execute(sql)
            except:
                pass

        sql = "SELECT user_2_squad FROM battle_requests WHERE status = 'finished' AND processed = 0;"
        cursor.execute(sql)
        data = cursor.fetchall()
        for squad in data:
            try:
                sql = "SELECT user_units.user_id, user_units.song_id, user_squads.squad_id FROM user_units INNER JOIN user_squads ON (user_units.song_id = user_squads.song_0_id OR user_units.song_id = user_squads.song_1_id OR user_units.song_id = user_squads.song_2_id OR user_units.song_id = user_squads.song_3_id) WHERE (user_squads.squad_id = " + str(squad[
                    "user_2_squad"]) + " AND user_units.user_id = user_squads.user_id);"
                cursor.execute(sql)
                songs = cursor.fetchall()
                for song in songs:
                    sql = "UPDATE user_units SET battles_total = battles_total + 1 WHERE user_id =" + \
                        str(song["user_id"]) + " AND song_id =" + str(song["song_id"])
                    cursor.execute(sql)
            except:
                pass
        sql = "SELECT user_1_squad FROM battle_requests WHERE status = 'finished' AND processed = 0;"
        cursor.execute(sql)
        data = cursor.fetchall()
        for squad in data:
            try:
                sql = "SELECT user_units.user_id, user_units.song_id, user_squads.squad_id FROM user_units INNER JOIN user_squads ON (user_units.song_id = user_squads.song_0_id OR user_units.song_id = user_squads.song_1_id OR user_units.song_id = user_squads.song_2_id OR user_units.song_id = user_squads.song_3_id) WHERE (user_squads.squad_id = " + str(squad[
                    "user_1_squad"]) + " AND user_units.user_id = user_squads.user_id);"
                cursor.execute(sql)
                songs = cursor.fetchall()
                for song in songs:
                    sql = "UPDATE user_units SET battles_total = battles_total + 1 WHERE user_id =" + \
                        str(song["user_id"]) + " AND song_id =" + str(song["song_id"])
                    cursor.execute(sql)
            except:
                pass
        sql = "UPDATE battle_requests SET processed = 1 WHERE processed = 0;"
        sql = "UPDATE user_units SET level = 2 WHERE experience > " + level_2_limit + " AND level < 2;"
        cursor.execute(sql)
        connection.commit()
        sql = "UPDATE user_units SET level = 3 WHERE experience > " + level_3_limit + " AND level < 3;"
        cursor.execute(sql)
        connection.commit()
        sql = "UPDATE user_units SET level = 4 WHERE experience > " + level_4_limit + " AND level < 4;"
        cursor.execute(sql)
        connection.commit()
        sql = "UPDATE user_units SET level = 5 WHERE (experience > " + \
            level_5_limit + " OR instant_level_up = 1) AND level < 5;"
        cursor.execute(sql)
    connection.commit()
    connection.close()

# This runs minutely

add_to_battle()

determine_battle_outcome()

try:
    expire_old_requests()
except:
    pass
try:
    update_winloss_records()
except:
    pass
