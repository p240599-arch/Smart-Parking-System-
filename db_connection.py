import sqlite3

DATABASE_NAME = '../database/smart_parking.db'


def get_connection():

    conn = sqlite3.connect(DATABASE_NAME)

    conn.row_factory = sqlite3.Row

    conn.execute('PRAGMA foreign_keys = ON')

    return conn