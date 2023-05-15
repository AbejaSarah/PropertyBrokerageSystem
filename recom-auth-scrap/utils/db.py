import mysql.connector
from mysql.connector import errorcode

class Database:
    def __init__(self, host, user, password, database):
        try:
            self.connection = mysql.connector.connect(
                host=host,
                user=user,
                password=password,
                database=database
            )
            self.cursor = self.connection.cursor(buffered=True)
            print(f"Connected to database '{database}'")
        except mysql.connector.Error as error:
            if error.errno == errorcode.ER_ACCESS_DENIED_ERROR:
                print("Invalid credentials")
            elif error.errno == errorcode.ER_BAD_DB_ERROR:
                print("Database does not exist")
            else:
                print(error)

    def execute(self, query, values=None):
        try:
            self.cursor.execute(query, values)
            self.connection.commit()
            return self.cursor.lastrowid
        except mysql.connector.Error as error:
            print(error)

    def fetch_all(self, query, values=None):
        try:
            self.cursor.execute(query, values)
            return self.cursor.fetchall()
        except mysql.connector.Error as error:
            print(error)
