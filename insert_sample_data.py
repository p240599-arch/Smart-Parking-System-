from db_connection import get_connection


def insert_data():

    conn = get_connection()
    cur = conn.cursor()

    # ---------------------------
    # USERS
    # ---------------------------
    cur.execute("""
        INSERT INTO User (Name, Email, Password, Phone_Number)
        VALUES ('Ali Khan', 'ali@gmail.com', '123', '03001112222')
    """)

    cur.execute("""
        INSERT INTO User (Name, Email, Password, Phone_Number)
        VALUES ('Admin Ahmed', 'admin@gmail.com', 'admin123', '03003334444')
    """)

    # ---------------------------
    # DRIVER
    # ---------------------------
    cur.execute("""
        INSERT INTO Driver (User_ID)
        VALUES (1)
    """)

    # ---------------------------
    # ADMIN
    # ---------------------------
    cur.execute("""
        INSERT INTO Admin (User_ID)
        VALUES (2)
    """)

    # ---------------------------
    # VEHICLE
    # ---------------------------
    cur.execute("""
        INSERT INTO Vehicle (Vehicle_Number, Vehicle_Type, User_ID)
        VALUES ('ABC-123', 'Car', 1)
    """)

    # ---------------------------
    # PARKING LOT
    # ---------------------------
    cur.execute("""
        INSERT INTO Parking_Lot (Location, Total_Slots, User_ID)
        VALUES ('Mall Road', 50, 2)
    """)

    # ---------------------------
    # PARKING SLOTS
    # ---------------------------
    cur.execute("""
        INSERT INTO Parking_Slot (Slot_Number, Slot_Status, Lot_ID)
        VALUES
        ('A1', 'Available', 1),
        ('A2', 'Available', 1),
        ('A3', 'Available', 1)
    """)

    # ---------------------------
    # PARKING RATE
    # ---------------------------
    cur.execute("""
        INSERT INTO Parking_Rate (Vehicle_Type, Rate_Per_Hour)
        VALUES
        ('Car', 100),
        ('Bike', 50),
        ('Bus', 200)
    """)

    conn.commit()
    conn.close()

    print("Sample data inserted successfully!")


insert_data()