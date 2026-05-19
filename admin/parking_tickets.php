<?php
// ============================================================
// admin/parking_tickets.php - Manage Parking Tickets (Full CRUD)
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message    = '';
$edit_ticket = null;

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Parking_Ticket WHERE Ticket_ID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Ticket deleted.</div>';
    } else {
        $message = '<div class="alert alert-error">Cannot delete ticket. Billing may exist.</div>';
    }
    $stmt->close();
}

// Handle EDIT load
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Parking_Ticket WHERE Ticket_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_ticket = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entry_time = trim($_POST['entry_time']);
    $exit_time  = !empty($_POST['exit_time']) ? trim($_POST['exit_time']) : null;
    $vehicle_id = (int)$_POST['vehicle_id'];
    $slot_id    = (int)$_POST['slot_id'];
    $ticket_id  = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;

    if (empty($entry_time) || $vehicle_id <= 0 || $slot_id <= 0) {
        $message = '<div class="alert alert-error">Please fill in all required fields.</div>';
    } else {
        if ($ticket_id > 0) {
            $stmt = $conn->prepare("UPDATE Parking_Ticket SET Entry_Time=?, Exit_Time=?, Vehicle_ID=?, Slot_ID=? WHERE Ticket_ID=?");
            $stmt->bind_param("ssiii", $entry_time, $exit_time, $vehicle_id, $slot_id, $ticket_id);
            $action = "updated";
        } else {
            $stmt = $conn->prepare("INSERT INTO Parking_Ticket (Entry_Time, Exit_Time, Vehicle_ID, Slot_ID) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $entry_time, $exit_time, $vehicle_id, $slot_id);
            $action = "added";
        }
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Ticket ' . $action . ' successfully.</div>';
            $edit_ticket = null;
        } else {
            $message = '<div class="alert alert-error">Error saving ticket. Exit time must be after entry time.</div>';
        }
        $stmt->close();
    }
}

// Fetch vehicles for dropdown
$vehicles = $conn->query("SELECT Vehicle_ID, Vehicle_Number, Vehicle_Type FROM Vehicle ORDER BY Vehicle_Number");

// Fetch available slots for dropdown
$slots = $conn->query("SELECT s.Slot_ID, s.Slot_Number, pl.Location FROM Parking_Slot s JOIN Parking_Lot pl ON s.Lot_ID = pl.Lot_ID ORDER BY pl.Location, s.Slot_Number");

// Fetch all tickets
$tickets = $conn->query("
    SELECT pt.*, v.Vehicle_Number, v.Vehicle_Type, ps.Slot_Number, pl.Location AS Lot_Location
    FROM Parking_Ticket pt
    JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID
    JOIN Parking_Slot ps ON pt.Slot_ID = ps.Slot_ID
    JOIN Parking_Lot pl ON ps.Lot_ID = pl.Lot_ID
    ORDER BY pt.Ticket_ID DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parking Tickets</title>
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
        <a href="parking_tickets.php" class="active">Tickets</a>
        <a href="parking_rates.php">Rates</a>
        <a href="billing.php">Billing</a>
        <a href="payments.php">Payments</a>
        <a href="drivers.php">Drivers</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">Manage Parking Tickets</div>

    <?php echo $message; ?>

    <!-- Form -->
    <div class="form-box mb-20">
        <h3 style="margin-bottom:18px;font-size:15px;color:#333;">
            <?php echo $edit_ticket ? 'Edit Ticket' : 'Create New Ticket'; ?>
        </h3>
        <form method="POST" action="parking_tickets.php">

            <?php if ($edit_ticket): ?>
                <input type="hidden" name="ticket_id" value="<?php echo $edit_ticket['Ticket_ID']; ?>">
            <?php endif; ?>

            <label>Vehicle</label>
            <select name="vehicle_id" required>
                <option value="">-- Select Vehicle --</option>
                <?php while ($v = $vehicles->fetch_assoc()): ?>
                    <option value="<?php echo $v['Vehicle_ID']; ?>"
                        <?php echo ($edit_ticket && $edit_ticket['Vehicle_ID'] == $v['Vehicle_ID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['Vehicle_Number'] . ' (' . $v['Vehicle_Type'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Parking Slot</label>
            <select name="slot_id" required>
                <option value="">-- Select Slot --</option>
                <?php while ($s = $slots->fetch_assoc()): ?>
                    <option value="<?php echo $s['Slot_ID']; ?>"
                        <?php echo ($edit_ticket && $edit_ticket['Slot_ID'] == $s['Slot_ID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['Slot_Number'] . ' - ' . $s['Location']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Entry Time</label>
            <input type="datetime-local" name="entry_time"
                   value="<?php echo $edit_ticket ? date('Y-m-d\TH:i', strtotime($edit_ticket['Entry_Time'])) : ''; ?>" required>

            <label>Exit Time (leave blank if still parked)</label>
            <input type="datetime-local" name="exit_time"
                   value="<?php echo ($edit_ticket && $edit_ticket['Exit_Time']) ? date('Y-m-d\TH:i', strtotime($edit_ticket['Exit_Time'])) : ''; ?>">

            <div class="form-actions">
                <button type="submit" class="btn btn-green">
                    <?php echo $edit_ticket ? 'Update Ticket' : 'Create Ticket'; ?>
                </button>
                <?php if ($edit_ticket): ?>
                    <a href="parking_tickets.php" class="btn btn-gray">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ticket ID</th>
                    <th>Vehicle</th>
                    <th>Slot</th>
                    <th>Entry Time</th>
                    <th>Exit Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $tickets->fetch_assoc()):
                $is_active = ($row['Exit_Time'] === null);
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['Ticket_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Number']); ?> (<?php echo $row['Vehicle_Type']; ?>)</td>
                    <td><?php echo htmlspecialchars($row['Slot_Number']); ?></td>
                    <td><?php echo $row['Entry_Time']; ?></td>
                    <td><?php echo $row['Exit_Time'] ?? '-'; ?></td>
                    <td>
                        <?php if ($is_active): ?>
                            <span class="badge badge-green">Active</span>
                        <?php else: ?>
                            <span class="badge badge-gray">Closed</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="parking_tickets.php?edit=<?php echo $row['Ticket_ID']; ?>" class="btn btn-blue" style="padding:5px 12px;">Edit</a>
                        <a href="parking_tickets.php?delete=<?php echo $row['Ticket_ID']; ?>"
                           class="btn btn-red" style="padding:5px 12px;"
                           onclick="return confirmDelete('ticket')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($tickets->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center" style="padding:20px;color:#999;">No tickets found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
