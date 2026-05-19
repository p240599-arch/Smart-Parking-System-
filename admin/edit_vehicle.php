<?php
// ============================================================
// admin/edit_vehicle.php - Edit Vehicle
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch vehicle data
$stmt = $conn->prepare("SELECT * FROM Vehicle WHERE Vehicle_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$vehicle = $result->fetch_assoc();
$stmt->close();

if (!$vehicle) {
    echo "Vehicle not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_number = trim($_POST['vehicle_number']);
    $vehicle_type   = trim($_POST['vehicle_type']);
    $user_id        = (int)$_POST['user_id'];

    $stmt = $conn->prepare("UPDATE Vehicle SET Vehicle_Number=?, Vehicle_Type=?, User_ID=? WHERE Vehicle_ID=?");
    $stmt->bind_param("ssii", $vehicle_number, $vehicle_type, $user_id, $id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Vehicle updated successfully.</div>';
        // Refresh vehicle data
        $stmt2 = $conn->prepare("SELECT * FROM Vehicle WHERE Vehicle_ID = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $vehicle = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
    } else {
        $message = '<div class="alert alert-error">Error updating vehicle. Number may already exist.</div>';
    }
    $stmt->close();
}

// Get all drivers for dropdown
$drivers = $conn->query("SELECT d.User_ID, u.Name FROM Driver d JOIN User u ON d.User_ID = u.User_ID ORDER BY u.Name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Vehicle</title>
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
    <div class="page-title">Edit Vehicle</div>

    <?php echo $message; ?>

    <div class="form-box">
        <form method="POST" action="edit_vehicle.php?id=<?php echo $id; ?>">

            <label for="vehicle_number">Vehicle Number (License Plate)</label>
            <input type="text" id="vehicle_number" name="vehicle_number"
                   value="<?php echo htmlspecialchars($vehicle['Vehicle_Number']); ?>" required>

            <label for="vehicle_type">Vehicle Type</label>
            <select id="vehicle_type" name="vehicle_type" required>
                <?php
                $types = ['Car', 'Bike', 'Truck', 'Van', 'Rickshaw'];
                foreach ($types as $type):
                    $sel = ($vehicle['Vehicle_Type'] === $type) ? 'selected' : '';
                ?>
                    <option value="<?php echo $type; ?>" <?php echo $sel; ?>><?php echo $type; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="user_id">Assign to Driver</label>
            <select id="user_id" name="user_id" required>
                <?php while ($d = $drivers->fetch_assoc()): ?>
                    <option value="<?php echo $d['User_ID']; ?>"
                        <?php echo ($vehicle['User_ID'] == $d['User_ID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($d['Name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="form-actions">
                <button type="submit" class="btn btn-green">Update Vehicle</button>
                <a href="vehicles.php" class="btn btn-gray">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
