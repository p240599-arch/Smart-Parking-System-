<?php
// ============================================================
// admin/add_vehicle.php - Add New Vehicle
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_number = trim($_POST['vehicle_number']);
    $vehicle_type   = trim($_POST['vehicle_type']);
    $user_id        = (int)$_POST['user_id'];

    if (empty($vehicle_number) || empty($vehicle_type) || $user_id <= 0) {
        $message = '<div class="alert alert-error">Please fill in all fields.</div>';
    } else {
        // Insert using prepared statement
        $stmt = $conn->prepare("INSERT INTO Vehicle (Vehicle_Number, Vehicle_Type, User_ID) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $vehicle_number, $vehicle_type, $user_id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Vehicle added successfully.</div>';
        } else {
            $message = '<div class="alert alert-error">Error: Vehicle number may already exist.</div>';
        }
        $stmt->close();
    }
}

// Get all drivers for dropdown
$drivers = $conn->query("SELECT d.User_ID, u.Name FROM Driver d JOIN User u ON d.User_ID = u.User_ID ORDER BY u.Name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Vehicle</title>
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
    <div class="page-title">Add New Vehicle</div>

    <?php echo $message; ?>

    <div class="form-box">
        <form method="POST" action="add_vehicle.php">

            <label for="vehicle_number">Vehicle Number (License Plate)</label>
            <input type="text" id="vehicle_number" name="vehicle_number"
                   placeholder="e.g. ABC-123" required>

            <label for="vehicle_type">Vehicle Type</label>
            <select id="vehicle_type" name="vehicle_type" required>
                <option value="">-- Select Type --</option>
                <option value="Car">Car</option>
                <option value="Bike">Bike</option>
                <option value="Truck">Truck</option>
                <option value="Van">Van</option>
                <option value="Rickshaw">Rickshaw</option>
            </select>

            <label for="user_id">Assign to Driver</label>
            <select id="user_id" name="user_id" required>
                <option value="">-- Select Driver --</option>
                <?php while ($d = $drivers->fetch_assoc()): ?>
                    <option value="<?php echo $d['User_ID']; ?>">
                        <?php echo htmlspecialchars($d['Name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="form-actions">
                <button type="submit" class="btn btn-green">Add Vehicle</button>
                <a href="vehicles.php" class="btn btn-gray">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
