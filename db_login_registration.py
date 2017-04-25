"""
Project:     Crescendia Game
File:        db_login_registration.py
Descritpion: Holds methods to be called by the controller.py for making database calls relating to user login info
"""

import configparser
import pymysql
from flask import Flask, request, jsonify, redirect

config = configparser.ConfigParser()
config.read("/var/www/config.ini")

sqlhost = config.get("configuration", "sqlhost")
sqluser = config.get("configuration", "sqluser")
sqlpassword = config.get("configuration", "sqlpassword")
sqldatabase = config.get("configuration", "sqldatabase")
siteurl = config.get("configuration", "siteurl")

#to register a new user for the first time
def user_register(username, email, password_hash, alphakey):
    if(username != '' and email != '' and password_hash != '' and alphakey != ''):
        if (alphakey == "gigem"):
            try:
                connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                             db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
                not_taken = True
                with connection.cursor() as cursor:
                    sql = "SELECT * FROM users WHERE username LIKE %s;"
                    cursor.execute(sql, (username))
                    data = cursor.fetchall()
                if(len(data) > 0):
                    not_taken = False
                with connection.cursor() as cursor:
                    sql = "SELECT * FROM users WHERE email LIKE %s;"
                    cursor.execute(sql, (email))
                    data = cursor.fetchall()
                if(len(data) > 0):
                    not_taken = False
                if(not_taken == True):
                    with connection.cursor() as cursor:
                        sql = "INSERT INTO users (username, email, password_hash) VALUES (%s, %s, %s);"
                        cursor.execute(sql, (username, email, password_hash))
                    connection.commit()
                    connection.close()
                    return "200"
                else:
                    connection.close()
                    return "Username or Email already taken!"
            except:
                return "Shit Request"
        else:
            return "Bad alpha key"
    else:
        return "One or more fields was missing"

#to log in a user     
def user_login(username, password_hash):
    if(username != '' and password_hash != ''):
        connection = pymysql.connect(host='localhost', user=sqluser, password=sqlpassword,
                                     db=sqldatabase, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
        user_exists = False
        with connection.cursor() as cursor:
            sql = "SELECT * FROM users WHERE username LIKE %s;"
            cursor.execute(sql, (username))
            data = cursor.fetchall()
        if(len(data) > 0):
            user_exists = True
        if(user_exists):
            if(password_hash == data[0]['password_hash']):
                return jsonify(data)

                connection.close()
            else:
                return "Bad Password"
                connection.close()
        else:
            return "Bad Username"
            connection.close()
    else:
        return "Missing username or password"
    return "Bad request"