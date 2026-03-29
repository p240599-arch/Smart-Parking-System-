
-- USER (Superclass)

CREATE TABLE User (
    User_ID INT PRIMARY KEY,
    Name VARCHAR(100),
    Email VARCHAR(100) UNIQUE,
    Password VARCHAR(100),
    Phone_Number VARCHAR(15)
);


-- DRIVER (Subclass)

CREATE TABLE Driver (
    User_ID INT PRIMARY KEY,
    FOREIGN KEY (User_ID) REFERENCES User(User_ID)
);


-- ADMIN (Subclass)

CREATE TABLE Admin (
    User_ID INT PRIMARY KEY,
    FOREIGN KEY (User_ID) REFERENCES User(User_ID)
);


-- VEHICLE

CREATE TABLE Vehicle (
    Vehicle_ID INT PRIMARY KEY,
    Vehicle_Number VARCHAR(20),
    Vehicle_Type VARCHAR(50),
    User_ID INT,
    FOREIGN KEY (User_ID) REFERENCES Driver(User_ID)
);


-- PARKING LOT

CREATE TABLE Parking_Lot (
    Lot_ID INT PRIMARY KEY,
    Location VARCHAR(100),
    Total_Slots INT,
    User_ID INT,
    FOREIGN KEY (User_ID) REFERENCES Admin(User_ID)
);

-- PARKING SLOT

CREATE TABLE Parking_Slot (
    Slot_ID INT PRIMARY KEY,
    Slot_Number VARCHAR(10),
    Slot_Status VARCHAR(20),
    Lot_ID INT,
    FOREIGN KEY (Lot_ID) REFERENCES Parking_Lot(Lot_ID)
);


-- PARKING TICKET

CREATE TABLE Parking_Ticket (
    Ticket_ID INT PRIMARY KEY,
    Entry_Time DATETIME,
    Exit_Time DATETIME,
    Vehicle_ID INT,
    Slot_ID INT,
    FOREIGN KEY (Vehicle_ID) REFERENCES Vehicle(Vehicle_ID),
    FOREIGN KEY (Slot_ID) REFERENCES Parking_Slot(Slot_ID)
);


-- PARKING RATE

CREATE TABLE Parking_Rate (
    Rate_ID INT PRIMARY KEY,
    Vehicle_Type VARCHAR(50),
    Rate_Per_Hour DECIMAL(10,2)
);


-- BILLING

CREATE TABLE Billing (
    Bill_ID INT PRIMARY KEY,
    Ticket_ID INT UNIQUE,
    Rate_ID INT,
    Amount DECIMAL(10,2),
    Bill_Time DATETIME,
    FOREIGN KEY (Ticket_ID) REFERENCES Parking_Ticket(Ticket_ID),
    FOREIGN KEY (Rate_ID) REFERENCES Parking_Rate(Rate_ID)
);


-- PAYMENT

CREATE TABLE Payment (
    Payment_ID INT PRIMARY KEY,
    Bill_ID INT UNIQUE,
    Payment_Method VARCHAR(50),
    Payment_Time DATETIME,
    Payment_Status VARCHAR(20),
    FOREIGN KEY (Bill_ID) REFERENCES Billing(Bill_ID)
);