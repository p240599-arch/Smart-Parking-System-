<?php
// ============================================================
// admin/drivers.php - View All Drivers
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

// Fetch all drivers with their info and vehicle count
$drivers = $conn->query("
    SELECT u.User_ID, u.Name, u.Email, u.Phone_Number,
           COUNT(v.Vehicle_ID) AS Vehicle_Count
    FROM Driver d
    JOIN User u ON d.User_ID = u.User_ID
    LEFT JOIN Vehicle v ON v.User_ID = u.User_ID
    GROUP BY u.User_ID
    ORDER BY u.Name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Drivers</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">Parking Management System | Imtiaz Super Mart</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="parking_lots.php">Lots</a>
        <a href="parking_slots.php">Slots</a>
        <a href="parking_tickets.php">Tickets</a>
        <a href="parking_rates.php">Rates</a>
        <a href="billing.php">Billing</a>
        <a href="payments.php">Payments</a>
        <a href="drivers.php" class="active">Drivers</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">All Drivers</div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Vehicles</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $drivers->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['User_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                    <td><?php echo htmlspecialchars($row['Phone_Number'] ?? 'N/A'); ?></td>
                    <td><?php echo $row['Vehicle_Count']; ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($drivers->num_rows === 0): ?>
                <tr><td colspan="6" class="text-center" style="padding:20px;color:#999;">No drivers found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
