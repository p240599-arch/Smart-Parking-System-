# Smart Parking System
**Imtiaz Super Mart, Peshawar**

---

## Group Information

| | |
|---|---|
| **Group Number** | 5 |
| **Course** | Database Management System (DBMS) |

### Group Members

| Name | Roll Number |
|------|-------------|
| Ayesha Zulfiqar | 24P-0663 |
| Sherbano Naveed | 24P-0599 |
| Ayesha Anees | 24P-0595 |

### Project Title

**Smart Parking System**

---

## Project Description

This is a web-based Smart Parking System developed for **Imtiaz Super Mart, Peshawar**. The idea behind this project is to manage the parking area of the supermart in a proper and organized way instead of doing everything manually.

The system has two types of users — **Admin** and **Driver**. The admin can manage everything in the system including parking lots, slots, vehicles, tickets, billing, and payments. Drivers can log in to check their own registered vehicles, their parking tickets, and payment details. The whole system is built on PHP and MySQL and runs on a local server using XAMPP.

---

## GitHub Repository

🔗 [hhttps://github.com/p240599-arch/Smart-Parking-System-](https://github.com/p240599-arch/Smart-Parking-System-)

---

## Technologies Used

| Technology | What it's used for |
|------------|-------------------|
| HTML5 | Building the structure of all web pages |
| CSS3 | Styling and layout of the entire interface |
| JavaScript | Confirm dialogs and auto-hide alert messages |
| PHP | All server-side logic and database operations |
| MySQL | Storing and managing all data |
| XAMPP | Running Apache and MySQL on a local machine |

> No external frameworks like Bootstrap, Laravel, or React were used. Everything is written from scratch.

---

## How to Install and Run

Follow the steps below to set up and run this project on your computer.

### Step 1 — Install XAMPP

If you don't have XAMPP installed, download it from the official site:
👉 [https://www.apachefriends.org/](https://www.apachefriends.org/)

After installing, open the **XAMPP Control Panel** and click **Start** next to both **Apache** and **MySQL**.

### Step 2 — Copy the Project

Copy the `parking-management-system` folder and paste it inside the following directory:

```
C:\xampp\htdocs\
```

So the final path should look like:

```
C:\xampp\htdocs\parking-management-system\
```

### Step 3 — Set Up the Database

1. Open your browser and go to: [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Click **"New"** on the left sidebar to create a new database
3. Name the database exactly as: `parking_management_system` and click **Create**
4. Click on that database from the left sidebar, then go to the **Import** tab
5. Click **"Choose File"** and select this file from inside the project folder:
   ```
   parking-management-system/database/parking_management_system.sql
   ```
6. Scroll down and click **"Go"** — all tables and sample data will be imported automatically

### Step 4 — Open the Project in Browser

Once the database is set up, open your browser and go to:

```
http://localhost/parking-management-system/
```

The login page will appear and the system is ready to use.

---

## Login Credentials

### Admin Account
| | |
|---|---|
| **Email** | admin@parking.com |
| **Password** | admin123 |
| **Role** | Admin |

### Driver Account 1
| | |
|---|---|
| **Email** | ali@parking.com |
| **Password** | driver123 |
| **Role** | Driver |

### Driver Account 2
| | |
|---|---|
| **Email** | sara@parking.com |
| **Password** | driver123 |
| **Role** | Driver |

---

## Features

**Admin can:**
- View a dashboard with live stats like total vehicles, available slots, active tickets, and total revenue collected
- Manage parking lots and individual parking slots (add, edit, delete)
- Register and manage vehicles
- Issue and manage parking tickets with entry and exit times
- Set parking rates based on vehicle type (Car, Bike, Van, Truck, Rickshaw)
- Handle billing and payment records
- View all registered drivers

**Driver can:**
- Log in and view their own dashboard
- See their registered vehicles
- Check their parking ticket history
- View their billing and payment details

---

## Database Tables

The database has the following tables:

| Table | Purpose |
|-------|---------|
| User | Stores all users (admins and drivers) |
| Admin | Marks which users are admins |
| Driver | Marks which users are drivers |
| Parking_Lot | Stores parking lot details and locations |
| Parking_Slot | Individual slots inside each lot (Available / Occupied / Reserved) |
| Vehicle | Vehicles registered by drivers |
| Parking_Rate | Hourly rates per vehicle type |
| Parking_Ticket | Records entry and exit time for each parking session |
| Billing | Bill generated after a parking session ends |
| Payment | Payment method and status for each bill |

---

## Project Folder Structure

```
parking-management-system/
│
├── index.php               ← Redirects to the login page
├── login.php               ← Login page for both admin and driver
├── logout.php              ← Ends the session and logs out
├── db.php                  ← Database connection file
├── style.css               ← Main stylesheet for the whole project
├── script.js               ← JavaScript for alerts and confirm dialogs
│
├── /admin/
│   ├── dashboard.php       ← Admin dashboard with stats and quick actions
│   ├── vehicles.php        ← View and delete vehicles
│   ├── add_vehicle.php     ← Add a new vehicle
│   ├── edit_vehicle.php    ← Edit an existing vehicle
│   ├── parking_lots.php    ← Manage parking lots (full CRUD)
│   ├── parking_slots.php   ← Manage parking slots (full CRUD)
│   ├── parking_tickets.php ← Manage parking tickets (full CRUD)
│   ├── parking_rates.php   ← Manage hourly rates (full CRUD)
│   ├── billing.php         ← Manage billing records (full CRUD)
│   ├── payments.php        ← Manage payments (full CRUD)
│   └── drivers.php         ← View all registered drivers
│
├── /driver/
│   ├── dashboard.php       ← Driver dashboard with personal stats
│   ├── my_vehicle.php      ← View own vehicles
│   ├── my_tickets.php      ← View own parking tickets
│   └── my_payments.php     ← View own billing and payment history
│
└── /database/
    └── parking_management_system.sql   ← Full SQL file with tables and sample data
```

---

## CRUD Operations

CRUD stands for the four basic operations that this system performs on the database:

| Letter | Operation | SQL Command |
|--------|-----------|-------------|
| C | Create — adding new records | INSERT |
| R | Read — viewing existing records | SELECT |
| U | Update — editing existing records | UPDATE |
| D | Delete — removing records | DELETE |

This project implements full CRUD for the following modules:

1. Vehicle
2. Parking Lot
3. Parking Slot
4. Parking Ticket
5. Parking Rate
6. Billing
7. Payment

All CRUD operations are done using PHP and MySQL with prepared statements to keep the system secure.

---

## Security

All database queries in this project use **prepared statements** with `$conn->prepare()` and `$stmt->bind_param()`, which means user input is never directly placed into SQL queries. This protects the system from SQL Injection attacks. Output displayed on pages also uses `htmlspecialchars()` to prevent XSS (Cross-Site Scripting).

---
