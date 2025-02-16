from database import *

def get_data_from_database():
    conn = connect_db()

    data = get_data(conn)

    close_db(conn)

    return data

