<?php
// ============================================================
// admin/vehicles.php - View and Delete Vehicles
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Vehicle WHERE Vehicle_ID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Vehicle deleted successfully.</div>';
    } else {
        $message = '<div class="alert alert-error">Error deleting vehicle.</div>';
    }
    $stmt->close();
}

// Fetch all vehicles with driver name
$result = $conn->query("
    SELECT v.Vehicle_ID, v.Vehicle_Number, v.Vehicle_Type, u.Name AS Driver_Name
    FROM Vehicle v
    JOIN User u ON v.User_ID = u.User_ID
    ORDER BY v.Vehicle_ID DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Vehicles</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">Parking Management System | Imtiaz Super Mart</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="vehicles.php" class="active">Vehicles</a>
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
    <div class="page-title">Manage Vehicles</div>

    <?php echo $message; ?>

    <div class="toolbar">
        <span style="font-size:13px;color:#666;"><?php echo $result->num_rows; ?> vehicle(s) found</span>
        <a href="add_vehicle.php" class="btn btn-green">+ Add Vehicle</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vehicle ID</th>
                    <th>Vehicle Number</th>
                    <th>Vehicle Type</th>
                    <th>Driver Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['Vehicle_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Number']); ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Type']); ?></td>
                    <td><?php echo htmlspecialchars($row['Driver_Name']); ?></td>
                    <td>
                        <a href="edit_vehicle.php?id=<?php echo $row['Vehicle_ID']; ?>" class="btn btn-blue" style="padding:5px 12px;">Edit</a>
                        <a href="vehicles.php?delete=<?php echo $row['Vehicle_ID']; ?>"
                           class="btn btn-red" style="padding:5px 12px;"
                           onclick="return confirmDelete('vehicle')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows === 0): ?>
                <tr><td colspan="6" class="text-center" style="padding:20px;color:#999;">No vehicles found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
