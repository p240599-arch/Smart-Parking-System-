<?php
// ============================================================
// driver/dashboard.php - Driver Dashboard
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$user_id = $_SESSION['user_id'];

// Count driver's vehicles
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM Vehicle WHERE User_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_vehicles = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Count driver's tickets
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total FROM Parking_Ticket pt
    JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID
    WHERE v.User_ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_tickets = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Count active tickets
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total FROM Parking_Ticket pt
    JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID
    WHERE v.User_ID = ? AND pt.Exit_Time IS NULL
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_tickets = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Total amount paid
$stmt = $conn->prepare("
    SELECT SUM(b.Amount) AS total
    FROM Billing b
    JOIN Parking_Ticket pt ON b.Ticket_ID = pt.Ticket_ID
    JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID
    JOIN Payment p ON b.Bill_ID = p.Bill_ID
    WHERE v.User_ID = ? AND p.Payment_Status = 'Completed'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_paid = $stmt->get_result()->fetch_assoc()['total'];
$total_paid = $total_paid ? number_format($total_paid, 2) : '0.00';
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">Parking Management System | Imtiaz Super Mart</div>
    <div class="nav-links">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="my_vehicle.php">My Vehicles</a>
        <a href="my_tickets.php">My Tickets</a>
        <a href="my_payments.php">My Payments</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">Driver Dashboard</div>
    <p style="color:#666;margin-bottom:20px;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></p>

    <div class="dashboard-cards">
        <div class="card">
            <h3><?php echo $my_vehicles; ?></h3>
            <p>My Vehicles</p>
        </div>
        <div class="card">
            <h3><?php echo $my_tickets; ?></h3>
            <p>Total Tickets</p>
        </div>
        <div class="card">
            <h3><?php echo $active_tickets; ?></h3>
            <p>Active (Parked Now)</p>
        </div>
        <div class="card">
            <h3>Rs <?php echo $total_paid; ?></h3>
            <p>Total Paid</p>
        </div>
    </div>

    <div class="page-title" style="font-size:17px;margin-top:10px;">Quick Links</div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:14px;">
        <a href="my_vehicle.php"   class="btn btn-green">View My Vehicles</a>
        <a href="my_tickets.php"   class="btn btn-blue">View My Tickets</a>
        <a href="my_payments.php"  class="btn btn-blue">View My Payments</a>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
