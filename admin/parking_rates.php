<?php
// ============================================================
// admin/parking_rates.php - Manage Parking Rates (Full CRUD)
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message   = '';
$edit_rate = null;

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Parking_Rate WHERE Rate_ID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Rate deleted.</div>';
    } else {
        $message = '<div class="alert alert-error">Cannot delete rate. Billing records reference it.</div>';
    }
    $stmt->close();
}

// Handle EDIT load
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Parking_Rate WHERE Rate_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_rate = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_type  = trim($_POST['vehicle_type']);
    $rate_per_hour = (float)$_POST['rate_per_hour'];
    $rate_id       = isset($_POST['rate_id']) ? (int)$_POST['rate_id'] : 0;

    if (empty($vehicle_type) || $rate_per_hour <= 0) {
        $message = '<div class="alert alert-error">Please fill in all fields correctly.</div>';
    } else {
        if ($rate_id > 0) {
            $stmt = $conn->prepare("UPDATE Parking_Rate SET Vehicle_Type=?, Rate_Per_Hour=? WHERE Rate_ID=?");
            $stmt->bind_param("sdi", $vehicle_type, $rate_per_hour, $rate_id);
            $action = "updated";
        } else {
            $stmt = $conn->prepare("INSERT INTO Parking_Rate (Vehicle_Type, Rate_Per_Hour) VALUES (?, ?)");
            $stmt->bind_param("sd", $vehicle_type, $rate_per_hour);
            $action = "added";
        }
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Rate ' . $action . ' successfully.</div>';
            $edit_rate = null;
        } else {
            $message = '<div class="alert alert-error">Error: Vehicle type may already have a rate.</div>';
        }
        $stmt->close();
    }
}

// Fetch all rates
$rates = $conn->query("SELECT * FROM Parking_Rate ORDER BY Vehicle_Type");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parking Rates</title>
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
        <a href="parking_rates.php" class="active">Rates</a>
        <a href="billing.php">Billing</a>
        <a href="payments.php">Payments</a>
        <a href="drivers.php">Drivers</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">Manage Parking Rates</div>

    <?php echo $message; ?>

    <!-- Form -->
    <div class="form-box mb-20">
        <h3 style="margin-bottom:18px;font-size:15px;color:#333;">
            <?php echo $edit_rate ? 'Edit Rate' : 'Add New Rate'; ?>
        </h3>
        <form method="POST" action="parking_rates.php">

            <?php if ($edit_rate): ?>
                <input type="hidden" name="rate_id" value="<?php echo $edit_rate['Rate_ID']; ?>">
            <?php endif; ?>

            <label>Vehicle Type</label>
            <input type="text" name="vehicle_type" placeholder="e.g. Car, Bike, Truck"
                   value="<?php echo $edit_rate ? htmlspecialchars($edit_rate['Vehicle_Type']) : ''; ?>" required>

            <label>Rate Per Hour (Rs)</label>
            <input type="number" name="rate_per_hour" placeholder="e.g. 50" min="1" step="0.01"
                   value="<?php echo $edit_rate ? $edit_rate['Rate_Per_Hour'] : ''; ?>" required>

            <div class="form-actions">
                <button type="submit" class="btn btn-green">
                    <?php echo $edit_rate ? 'Update Rate' : 'Add Rate'; ?>
                </button>
                <?php if ($edit_rate): ?>
                    <a href="parking_rates.php" class="btn btn-gray">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Rates Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Rate ID</th>
                    <th>Vehicle Type</th>
                    <th>Rate Per Hour (Rs)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $rates->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['Rate_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Type']); ?></td>
                    <td>Rs <?php echo number_format($row['Rate_Per_Hour'], 2); ?></td>
                    <td>
                        <a href="parking_rates.php?edit=<?php echo $row['Rate_ID']; ?>" class="btn btn-blue" style="padding:5px 12px;">Edit</a>
                        <a href="parking_rates.php?delete=<?php echo $row['Rate_ID']; ?>"
                           class="btn btn-red" style="padding:5px 12px;"
                           onclick="return confirmDelete('rate')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($rates->num_rows === 0): ?>
                <tr><td colspan="5" class="text-center" style="padding:20px;color:#999;">No rates found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
