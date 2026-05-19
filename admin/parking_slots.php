<?php
// ============================================================
// admin/parking_slots.php - Manage Parking Slots (Full CRUD)
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message  = '';
$edit_slot = null;

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Parking_Slot WHERE Slot_ID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Parking slot deleted.</div>';
    } else {
        $message = '<div class="alert alert-error">Cannot delete slot. Active tickets may exist.</div>';
    }
    $stmt->close();
}

// Handle EDIT load
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Parking_Slot WHERE Slot_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_slot = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slot_number = trim($_POST['slot_number']);
    $slot_status = $_POST['slot_status'];
    $lot_id      = (int)$_POST['lot_id'];
    $slot_id     = isset($_POST['slot_id']) ? (int)$_POST['slot_id'] : 0;

    if (empty($slot_number) || empty($slot_status) || $lot_id <= 0) {
        $message = '<div class="alert alert-error">Please fill in all fields.</div>';
    } else {
        if ($slot_id > 0) {
            $stmt = $conn->prepare("UPDATE Parking_Slot SET Slot_Number=?, Slot_Status=?, Lot_ID=? WHERE Slot_ID=?");
            $stmt->bind_param("ssii", $slot_number, $slot_status, $lot_id, $slot_id);
            $action = "updated";
        } else {
            $stmt = $conn->prepare("INSERT INTO Parking_Slot (Slot_Number, Slot_Status, Lot_ID) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $slot_number, $slot_status, $lot_id);
            $action = "added";
        }
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Parking slot ' . $action . ' successfully.</div>';
            $edit_slot = null;
        } else {
            $message = '<div class="alert alert-error">Error: Slot number may already exist in this lot.</div>';
        }
        $stmt->close();
    }
}

// Fetch parking lots for dropdown
$lots = $conn->query("SELECT Lot_ID, Location FROM Parking_Lot ORDER BY Location");

// Fetch all slots
$slots = $conn->query("
    SELECT ps.*, pl.Location AS Lot_Location
    FROM Parking_Slot ps
    JOIN Parking_Lot pl ON ps.Lot_ID = pl.Lot_ID
    ORDER BY ps.Lot_ID, ps.Slot_Number
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parking Slots</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">Parking Management System | Imtiaz Super Mart</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="parking_lots.php">Lots</a>
        <a href="parking_slots.php" class="active">Slots</a>
        <a href="parking_tickets.php">Tickets</a>
        <a href="parking_rates.php">Rates</a>
        <a href="billing.php">Billing</a>
        <a href="payments.php">Payments</a>
        <a href="drivers.php">Drivers</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">Manage Parking Slots</div>

    <?php echo $message; ?>

    <!-- Add / Edit Form -->
    <div class="form-box mb-20">
        <h3 style="margin-bottom:18px;font-size:15px;color:#333;">
            <?php echo $edit_slot ? 'Edit Parking Slot' : 'Add New Slot'; ?>
        </h3>
        <form method="POST" action="parking_slots.php">

            <?php if ($edit_slot): ?>
                <input type="hidden" name="slot_id" value="<?php echo $edit_slot['Slot_ID']; ?>">
            <?php endif; ?>

            <label>Slot Number</label>
            <input type="text" name="slot_number" placeholder="e.g. A1, B3"
                   value="<?php echo $edit_slot ? htmlspecialchars($edit_slot['Slot_Number']) : ''; ?>" required>

            <label>Slot Status</label>
            <select name="slot_status" required>
                <?php
                $statuses = ['Available', 'Occupied', 'Reserved'];
                foreach ($statuses as $s):
                    $sel = ($edit_slot && $edit_slot['Slot_Status'] === $s) ? 'selected' : '';
                ?>
                    <option value="<?php echo $s; ?>" <?php echo $sel; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>

            <label>Parking Lot</label>
            <select name="lot_id" required>
                <option value="">-- Select Lot --</option>
                <?php while ($l = $lots->fetch_assoc()): ?>
                    <option value="<?php echo $l['Lot_ID']; ?>"
                        <?php echo ($edit_slot && $edit_slot['Lot_ID'] == $l['Lot_ID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($l['Location']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="form-actions">
                <button type="submit" class="btn btn-green">
                    <?php echo $edit_slot ? 'Update Slot' : 'Add Slot'; ?>
                </button>
                <?php if ($edit_slot): ?>
                    <a href="parking_slots.php" class="btn btn-gray">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Slots Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Slot ID</th>
                    <th>Slot Number</th>
                    <th>Status</th>
                    <th>Parking Lot</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $slots->fetch_assoc()):
                // Status badge color
                $badge = 'badge-gray';
                if ($row['Slot_Status'] === 'Available') $badge = 'badge-green';
                elseif ($row['Slot_Status'] === 'Occupied') $badge = 'badge-red';
                elseif ($row['Slot_Status'] === 'Reserved') $badge = 'badge-yellow';
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['Slot_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Slot_Number']); ?></td>
                    <td><span class="badge <?php echo $badge; ?>"><?php echo $row['Slot_Status']; ?></span></td>
                    <td><?php echo htmlspecialchars($row['Lot_Location']); ?></td>
                    <td>
                        <a href="parking_slots.php?edit=<?php echo $row['Slot_ID']; ?>" class="btn btn-blue" style="padding:5px 12px;">Edit</a>
                        <a href="parking_slots.php?delete=<?php echo $row['Slot_ID']; ?>"
                           class="btn btn-red" style="padding:5px 12px;"
                           onclick="return confirmDelete('parking slot')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($slots->num_rows === 0): ?>
                <tr><td colspan="6" class="text-center" style="padding:20px;color:#999;">No slots found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
