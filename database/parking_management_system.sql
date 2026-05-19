-- ============================================================
-- University Parking Management System
-- Imtiaz Super Mart Peshawar
-- Database: parking_management_system
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS parking_management_system;
USE parking_management_system;

-- ============================================================
-- TABLE: User
-- Central identity table for all system users
-- ============================================================
CREATE TABLE IF NOT EXISTS User (
    User_ID    INT AUTO_INCREMENT PRIMARY KEY,
    Name       VARCHAR(100) NOT NULL,
    Email      VARCHAR(100) NOT NULL UNIQUE,
    Password   VARCHAR(255) NOT NULL,
    Phone_Number VARCHAR(20)
);

-- ============================================================
-- TABLE: Admin
-- Subtype of User (ISA relationship)
-- ============================================================
CREATE TABLE IF NOT EXISTS Admin (
    User_ID INT PRIMARY KEY,
    FOREIGN KEY (User_ID) REFERENCES User(User_ID) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: Driver
-- Subtype of User (ISA relationship)
-- ============================================================
CREATE TABLE IF NOT EXISTS Driver (
    User_ID INT PRIMARY KEY,
    FOREIGN KEY (User_ID) REFERENCES User(User_ID) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: Parking_Lot
-- Physical parking facilities managed by admins
-- ============================================================
CREATE TABLE IF NOT EXISTS Parking_Lot (
    Lot_ID      INT AUTO_INCREMENT PRIMARY KEY,
    Location    VARCHAR(200) NOT NULL,
    Total_Slots INT NOT NULL DEFAULT 0,
    User_ID     INT,
    FOREIGN KEY (User_ID) REFERENCES Admin(User_ID) ON DELETE SET NULL
);

-- ============================================================
-- TABLE: Parking_Slot
-- Individual parking spaces within a lot
-- ============================================================
CREATE TABLE IF NOT EXISTS Parking_Slot (
    Slot_ID     INT AUTO_INCREMENT PRIMARY KEY,
    Slot_Number VARCHAR(20) NOT NULL,
    Slot_Status ENUM('Available', 'Occupied', 'Reserved') NOT NULL DEFAULT 'Available',
    Lot_ID      INT NOT NULL,
    FOREIGN KEY (Lot_ID) REFERENCES Parking_Lot(Lot_ID) ON DELETE CASCADE,
    UNIQUE KEY unique_slot_in_lot (Lot_ID, Slot_Number)
);

-- ============================================================
-- TABLE: Vehicle
-- Vehicles registered by drivers
-- ============================================================
CREATE TABLE IF NOT EXISTS Vehicle (
    Vehicle_ID     INT AUTO_INCREMENT PRIMARY KEY,
    Vehicle_Number VARCHAR(50) NOT NULL UNIQUE,
    Vehicle_Type   VARCHAR(50) NOT NULL,
    User_ID        INT NOT NULL,
    FOREIGN KEY (User_ID) REFERENCES Driver(User_ID) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: Parking_Rate
-- Hourly rates based on vehicle type
-- ============================================================
CREATE TABLE IF NOT EXISTS Parking_Rate (
    Rate_ID      INT AUTO_INCREMENT PRIMARY KEY,
    Vehicle_Type VARCHAR(50) NOT NULL UNIQUE,
    Rate_Per_Hour DECIMAL(10,2) NOT NULL
);

-- ============================================================
-- TABLE: Parking_Ticket
-- Core transactional record for each parking event
-- ============================================================
CREATE TABLE IF NOT EXISTS Parking_Ticket (
    Ticket_ID  INT AUTO_INCREMENT PRIMARY KEY,
    Entry_Time DATETIME NOT NULL,
    Exit_Time  DATETIME DEFAULT NULL,
    Vehicle_ID INT NOT NULL,
    Slot_ID    INT NOT NULL,
    FOREIGN KEY (Vehicle_ID) REFERENCES Vehicle(Vehicle_ID) ON DELETE CASCADE,
    FOREIGN KEY (Slot_ID) REFERENCES Parking_Slot(Slot_ID) ON DELETE CASCADE,
    CONSTRAINT chk_exit_after_entry CHECK (Exit_Time IS NULL OR Exit_Time > Entry_Time)
);

-- ============================================================
-- TABLE: Billing
-- Bill generated after a parking session ends
-- ============================================================
CREATE TABLE IF NOT EXISTS Billing (
    Bill_ID   INT AUTO_INCREMENT PRIMARY KEY,
    Ticket_ID INT NOT NULL UNIQUE,
    Rate_ID   INT NOT NULL,
    Amount    DECIMAL(10,2) NOT NULL,
    Bill_Time DATETIME NOT NULL,
    FOREIGN KEY (Ticket_ID) REFERENCES Parking_Ticket(Ticket_ID) ON DELETE CASCADE,
    FOREIGN KEY (Rate_ID) REFERENCES Parking_Rate(Rate_ID) ON DELETE RESTRICT
);

-- ============================================================
-- TABLE: Payment
-- Payment record for a bill
-- ============================================================
CREATE TABLE IF NOT EXISTS Payment (
    Payment_ID     INT AUTO_INCREMENT PRIMARY KEY,
    Bill_ID        INT NOT NULL UNIQUE,
    Payment_Method ENUM('Cash', 'Card', 'Online', 'Wallet') NOT NULL,
    Payment_Time   DATETIME NOT NULL,
    Payment_Status ENUM('Pending', 'Completed', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (Bill_ID) REFERENCES Billing(Bill_ID) ON DELETE CASCADE
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin User
INSERT INTO User (Name, Email, Password, Phone_Number) VALUES
('Super Admin', 'admin@parking.com', MD5('admin123'), '0311-1234567'),
('Ali Ahmed',   'ali@parking.com',   MD5('driver123'), '0321-9876543'),
('Sara Khan',   'sara@parking.com',  MD5('driver123'), '0333-5556677');

-- Assign roles
INSERT INTO Admin  (User_ID) VALUES (1);
INSERT INTO Driver (User_ID) VALUES (2);
INSERT INTO Driver (User_ID) VALUES (3);

-- Parking Rates
INSERT INTO Parking_Rate (Vehicle_Type, Rate_Per_Hour) VALUES
('Car',        50.00),
('Bike',       20.00),
('Truck',     100.00),
('Van',        70.00),
('Rickshaw',   30.00);

-- Parking Lot
INSERT INTO Parking_Lot (Location, Total_Slots, User_ID) VALUES
('Imtiaz Super Mart - Main Entrance, Peshawar', 50, 1),
('Imtiaz Super Mart - Side Gate, Peshawar',     30, 1);

-- Parking Slots
INSERT INTO Parking_Slot (Slot_Number, Slot_Status, Lot_ID) VALUES
('A1', 'Available', 1),
('A2', 'Available', 1),
('A3', 'Occupied',  1),
('B1', 'Available', 1),
('B2', 'Reserved',  1),
('C1', 'Available', 2),
('C2', 'Available', 2),
('C3', 'Occupied',  2);

-- Vehicles
INSERT INTO Vehicle (Vehicle_Number, Vehicle_Type, User_ID) VALUES
('ABC-123', 'Car',  2),
('XYZ-789', 'Bike', 2),
('KHI-456', 'Van',  3);

-- Parking Tickets
INSERT INTO Parking_Ticket (Entry_Time, Exit_Time, Vehicle_ID, Slot_ID) VALUES
('2025-05-10 09:00:00', '2025-05-10 11:00:00', 1, 3),
('2025-05-11 08:30:00', '2025-05-11 10:00:00', 2, 8),
('2025-05-12 14:00:00', NULL,                  3, 5);

-- Billing
INSERT INTO Billing (Ticket_ID, Rate_ID, Amount, Bill_Time) VALUES
(1, 1, 100.00, '2025-05-10 11:05:00'),
(2, 2,  30.00, '2025-05-11 10:05:00');

-- Payments
INSERT INTO Payment (Bill_ID, Payment_Method, Payment_Time, Payment_Status) VALUES
(1, 'Cash',   '2025-05-10 11:10:00', 'Completed'),
(2, 'Online', '2025-05-11 10:10:00', 'Completed');
