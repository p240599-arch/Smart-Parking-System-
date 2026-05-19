<?php
// ============================================================
// admin/parking_lots.php - Manage Parking Lots (Full CRUD)
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message = '';
$edit_lot = null;

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Parking_Lot WHERE Lot_ID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Parking lot deleted.</div>';
    } else {
        $message = '<div class="alert alert-error">Cannot delete lot. Slots may still exist.</div>';
    }
    $stmt->close();
}

// Handle EDIT load
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Parking_Lot WHERE Lot_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_lot = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location    = trim($_POST['location']);
    $total_slots = (int)$_POST['total_slots'];
    $user_id     = (int)$_POST['user_id'];
    $lot_id      = isset($_POST['lot_id']) ? (int)$_POST['lot_id'] : 0;

    if (empty($location) || $total_slots <= 0 || $user_id <= 0) {
        $message = '<div class="alert alert-error">Please fill in all fields correctly.</div>';
    } else {
        if ($lot_id > 0) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE Parking_Lot SET Location=?, Total_Slots=?, User_ID=? WHERE Lot_ID=?");
            $stmt->bind_param("siii", $location, $total_slots, $user_id, $lot_id);
            $action = "updated";
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO Parking_Lot (Location, Total_Slots, User_ID) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $location, $total_slots, $user_id);
            $action = "added";
        }
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Parking lot ' . $action . ' successfully.</div>';
            $edit_lot = null;
        } else {
            $message = '<div class="alert alert-error">Error saving parking lot.</div>';
        }
        $stmt->close();
    }
}

// Fetch all admins for dropdown
$admins = $conn->query("SELECT a.User_ID, u.Name FROM Admin a JOIN User u ON a.User_ID = u.User_ID ORDER BY u.Name");

// Fetch all lots
$lots = $conn->query("
    SELECT pl.*, u.Name AS Admin_Name
    FROM Parking_Lot pl
    LEFT JOIN User u ON pl.User_ID = u.User_ID
    ORDER BY pl.Lot_ID DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parking Lots</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">Parking Management System | Imtiaz Super Mart</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="parking_lots.php" class="active">Lots</a>
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
    <div class="page-title">Manage Parking Lots</div>

    <?php echo $message; ?>

    <!-- Add / Edit Form -->
    <div class="form-box mb-20">
        <h3 style="margin-bottom:18px;font-size:15px;color:#333;">
            <?php echo $edit_lot ? 'Edit Parking Lot' : 'Add New Parking Lot'; ?>
        </h3>
        <form method="POST" action="parking_lots.php">

            <?php if ($edit_lot): ?>
                <input type="hidden" name="lot_id" value="<?php echo $edit_lot['Lot_ID']; ?>">
            <?php endif; ?>

            <label>Location</label>
            <input type="text" name="location" placeholder="e.g. Main Entrance, Peshawar"
                   value="<?php echo $edit_lot ? htmlspecialchars($edit_lot['Location']) : ''; ?>" required>

            <label>Total Slots</label>
            <input type="number" name="total_slots" placeholder="e.g. 50" min="1"
                   value="<?php echo $edit_lot ? $edit_lot['Total_Slots'] : ''; ?>" required>

            <label>Managed by Admin</label>
            <select name="user_id" required>
                <option value="">-- Select Admin --</option>
                <?php while ($a = $admins->fetch_assoc()): ?>
                    <option value="<?php echo $a['User_ID']; ?>"
                        <?php echo ($edit_lot && $edit_lot['User_ID'] == $a['User_ID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($a['Name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="form-actions">
                <button type="submit" class="btn btn-green">
                    <?php echo $edit_lot ? 'Update Lot' : 'Add Lot'; ?>
                </button>
                <?php if ($edit_lot): ?>
                    <a href="parking_lots.php" class="btn btn-gray">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Lots Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Lot ID</th>
                    <th>Location</th>
                    <th>Total Slots</th>
                    <th>Managed By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $lots->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['Lot_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Location']); ?></td>
                    <td><?php echo $row['Total_Slots']; ?></td>
                    <td><?php echo htmlspecialchars($row['Admin_Name'] ?? 'N/A'); ?></td>
                    <td>
                        <a href="parking_lots.php?edit=<?php echo $row['Lot_ID']; ?>" class="btn btn-blue" style="padding:5px 12px;">Edit</a>
                        <a href="parking_lots.php?delete=<?php echo $row['Lot_ID']; ?>"
                           class="btn btn-red" style="padding:5px 12px;"
                           onclick="return confirmDelete('parking lot')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($lots->num_rows === 0): ?>
                <tr><td colspan="6" class="text-center" style="padding:20px;color:#999;">No lots found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
