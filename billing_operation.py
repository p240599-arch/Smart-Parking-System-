from db_connection import get_connection
from datetime import datetime


def generate_bill(ticket_id):

    conn = get_connection()
    cur = conn.cursor()

    # -----------------------------------
    # GET TICKET DETAILS
    # -----------------------------------
    cur.execute("""
        SELECT pt.Entry_Time, pt.Exit_Time, v.Vehicle_Type
        FROM Parking_Ticket pt
        JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID
        WHERE pt.Ticket_ID = ?
    """, (ticket_id,))

    data = cur.fetchone()

    if not data:
        print("Invalid Ticket ID")
        return

    entry_time = datetime.fromisoformat(data["Entry_Time"])
    exit_time = datetime.fromisoformat(data["Exit_Time"])

    # -----------------------------------
    # CALCULATE DURATION
    # -----------------------------------
    duration = exit_time - entry_time
    hours = duration.total_seconds() / 3600

    if hours < 1:
        hours = 1  # minimum 1 hour charge

    # -----------------------------------
    # GET RATE
    # -----------------------------------
    cur.execute("""
        SELECT Rate_ID, Rate_Per_Hour
        FROM Parking_Rate
        WHERE Vehicle_Type = ?
    """, (data["Vehicle_Type"],))

    rate = cur.fetchone()

    amount = hours * rate["Rate_Per_Hour"]

    bill_time = datetime.now()

    # -----------------------------------
    # INSERT BILL
    # -----------------------------------
    cur.execute("""
        INSERT INTO Billing (Ticket_ID, Rate_ID, Amount, Bill_Time)
        VALUES (?, ?, ?, ?)
    """, (
        ticket_id,
        rate["Rate_ID"],
        round(amount, 2),
        bill_time
    ))

    conn.commit()
    conn.close()

    print("Bill generated successfully!")
    print("Total Amount:", round(amount, 2))