PRAGMA foreign_keys = ON;

------------------------------------------------------------
-- USER TABLE
------------------------------------------------------------
CREATE TABLE User (
    User_ID INTEGER PRIMARY KEY AUTOINCREMENT,
    Name TEXT NOT NULL,
    Email TEXT UNIQUE NOT NULL,
    Password TEXT NOT NULL,
    Phone_Number TEXT UNIQUE
);

------------------------------------------------------------
-- DRIVER TABLE
------------------------------------------------------------
CREATE TABLE Driver (
    User_ID INTEGER PRIMARY KEY,

    FOREIGN KEY (User_ID)
    REFERENCES User(User_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

------------------------------------------------------------
-- ADMIN TABLE
------------------------------------------------------------
CREATE TABLE Admin (
    User_ID INTEGER PRIMARY KEY,

    FOREIGN KEY (User_ID)
    REFERENCES User(User_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

------------------------------------------------------------
-- VEHICLE TABLE
------------------------------------------------------------
CREATE TABLE Vehicle (
    Vehicle_ID INTEGER PRIMARY KEY AUTOINCREMENT,

    Vehicle_Number TEXT UNIQUE NOT NULL,

    Vehicle_Type TEXT NOT NULL,

    User_ID INTEGER NOT NULL,

    FOREIGN KEY (User_ID)
    REFERENCES Driver(User_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

------------------------------------------------------------
-- PARKING LOT TABLE
------------------------------------------------------------
CREATE TABLE Parking_Lot (
    Lot_ID INTEGER PRIMARY KEY AUTOINCREMENT,

    Location TEXT NOT NULL,

    Total_Slots INTEGER NOT NULL
    CHECK (Total_Slots > 0),

    User_ID INTEGER NOT NULL,

    FOREIGN KEY (User_ID)
    REFERENCES Admin(User_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

------------------------------------------------------------
-- PARKING SLOT TABLE
------------------------------------------------------------
CREATE TABLE Parking_Slot (
    Slot_ID INTEGER PRIMARY KEY AUTOINCREMENT,

    Slot_Number TEXT NOT NULL,

    Slot_Status TEXT NOT NULL
    CHECK (
        Slot_Status IN
        ('Available', 'Occupied', 'Reserved')
    ),

    Lot_ID INTEGER NOT NULL,

    UNIQUE (Lot_ID, Slot_Number),

    FOREIGN KEY (Lot_ID)
    REFERENCES Parking_Lot(Lot_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

------------------------------------------------------------
-- PARKING RATE TABLE
------------------------------------------------------------
CREATE TABLE Parking_Rate (
    Rate_ID INTEGER PRIMARY KEY AUTOINCREMENT,

    Vehicle_Type TEXT UNIQUE NOT NULL,

    Rate_Per_Hour REAL NOT NULL
    CHECK (Rate_Per_Hour > 0)
);

------------------------------------------------------------
-- PARKING TICKET TABLE
------------------------------------------------------------
CREATE TABLE Parking_Ticket (
    Ticket_ID INTEGER PRIMARY KEY AUTOINCREMENT,

    Entry_Time DATETIME NOT NULL,

    Exit_Time DATETIME,

    Vehicle_ID INTEGER NOT NULL,

    Slot_ID INTEGER NOT NULL,

    FOREIGN KEY (Vehicle_ID)
    REFERENCES Vehicle(Vehicle_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

    FOREIGN KEY (Slot_ID)
    REFERENCES Parking_Slot(Slot_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

    CHECK (
        Exit_Time IS NULL
        OR Exit_Time > Entry_Time
    )
);

------------------------------------------------------------
-- BILLING TABLE
------------------------------------------------------------
CREATE TABLE Billing (
    Bill_ID INTEGER PRIMARY KEY AUTOINCREMENT,

    Ticket_ID INTEGER UNIQUE NOT NULL,

    Rate_ID INTEGER NOT NULL,

    Amount REAL NOT NULL
    CHECK (Amount >= 0),

    Bill_Time DATETIME NOT NULL,

    FOREIGN KEY (Ticket_ID)
    REFERENCES Parking_Ticket(Ticket_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

    FOREIGN KEY (Rate_ID)
    REFERENCES Parking_Rate(Rate_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

------------------------------------------------------------
-- PAYMENT TABLE
------------------------------------------------------------
CREATE TABLE Payment (
    Payment_ID INTEGER PRIMARY KEY AUTOINCREMENT,

    Bill_ID INTEGER UNIQUE NOT NULL,

    Payment_Method TEXT NOT NULL
    CHECK (
        Payment_Method IN
        ('Cash', 'Card', 'Online', 'Wallet')
    ),

    Payment_Time DATETIME NOT NULL,

    Payment_Status TEXT NOT NULL
    CHECK (
        Payment_Status IN
        ('Pending', 'Completed', 'Failed', 'Refunded')
    ),

    FOREIGN KEY (Bill_ID)
    REFERENCES Billing(Bill_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);