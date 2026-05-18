<?php
// ============================================================
// driver/my_vehicle.php - Driver's Own Vehicles
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$user_id = $_SESSION['user_id'];

// Fetch vehicles belonging to this driver
$stmt = $conn->prepare("SELECT * FROM Vehicle WHERE User_ID = ? ORDER BY Vehicle_ID DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$vehicles = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Vehicles</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">Parking Management System | Imtiaz Super Mart</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="my_vehicle.php" class="active">My Vehicles</a>
        <a href="my_tickets.php">My Tickets</a>
        <a href="my_payments.php">My Payments</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">My Vehicles</div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vehicle ID</th>
                    <th>Vehicle Number</th>
                    <th>Vehicle Type</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $vehicles->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['Vehicle_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Number']); ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Type']); ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($vehicles->num_rows === 0): ?>
                <tr><td colspan="4" class="text-center" style="padding:20px;color:#999;">No vehicles registered.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
