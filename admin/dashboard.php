<?php
// ============================================================
// admin/dashboard.php - Admin Dashboard
// ============================================================

session_start();

// Only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

// --- Fetch summary counts for dashboard cards ---

// Count vehicles
$r = $conn->query("SELECT COUNT(*) AS total FROM Vehicle");
$total_vehicles = $r->fetch_assoc()['total'];

// Count parking lots
$r = $conn->query("SELECT COUNT(*) AS total FROM Parking_Lot");
$total_lots = $r->fetch_assoc()['total'];

// Count available slots
$r = $conn->query("SELECT COUNT(*) AS total FROM Parking_Slot WHERE Slot_Status = 'Available'");
$available_slots = $r->fetch_assoc()['total'];

// Count active tickets (no exit time)
$r = $conn->query("SELECT COUNT(*) AS total FROM Parking_Ticket WHERE Exit_Time IS NULL");
$active_tickets = $r->fetch_assoc()['total'];

// Count drivers
$r = $conn->query("SELECT COUNT(*) AS total FROM Driver");
$total_drivers = $r->fetch_assoc()['total'];

// Total revenue from completed payments
$r = $conn->query("SELECT SUM(b.Amount) AS revenue FROM Billing b JOIN Payment p ON b.Bill_ID = p.Bill_ID WHERE p.Payment_Status = 'Completed'");
$revenue = $r->fetch_assoc()['revenue'];
$revenue = $revenue ? number_format($revenue, 2) : '0.00';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Parking System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="brand">Parking Management System | Imtiaz Super Mart</div>
    <div class="nav-links">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="parking_lots.php">Lots</a>
        <a href="parking_slots.php">Slots</a>
        <a href="parking_tickets.php">Tickets</a>
        <a href="parking_rates.php">Rates</a>
        <a href="billing.php">Billing</a>
        <a href="payments.php">Payments</a>
        <a href="drivers.php">Drivers</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">Admin Dashboard</div>
    <p style="color:#666;margin-bottom:20px;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></p>

    <!-- Summary Cards -->
    <div class="dashboard-cards">
        <div class="card">
            <h3><?php echo $total_vehicles; ?></h3>
            <p>Total Vehicles</p>
        </div>
        <div class="card">
            <h3><?php echo $total_lots; ?></h3>
            <p>Parking Lots</p>
        </div>
        <div class="card">
            <h3><?php echo $available_slots; ?></h3>
            <p>Available Slots</p>
        </div>
        <div class="card">
            <h3><?php echo $active_tickets; ?></h3>
            <p>Active Tickets</p>
        </div>
        <div class="card">
            <h3><?php echo $total_drivers; ?></h3>
            <p>Drivers</p>
        </div>
        <div class="card">
            <h3>Rs <?php echo $revenue; ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="page-title" style="font-size:17px;margin-top:10px;">Quick Actions</div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:14px;">
        <a href="add_vehicle.php"         class="btn btn-green">+ Add Vehicle</a>
        <a href="parking_lots.php"        class="btn btn-green">+ Add Parking Lot</a>
        <a href="parking_slots.php"       class="btn btn-green">+ Add Slot</a>
        <a href="parking_tickets.php"     class="btn btn-blue">View Tickets</a>
        <a href="parking_rates.php"       class="btn btn-blue">Manage Rates</a>
        <a href="billing.php"             class="btn btn-blue">Billing</a>
        <a href="payments.php"            class="btn btn-blue">Payments</a>
    </div>
</div>

<script src="../script.js"></script>
</body>
</html>
