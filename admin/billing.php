<?php
// ============================================================
// admin/billing.php - Manage Billing (Full CRUD)
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message  = '';
$edit_bill = null;

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Billing WHERE Bill_ID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Bill deleted.</div>';
    } else {
        $message = '<div class="alert alert-error">Cannot delete bill. Payment may exist.</div>';
    }
    $stmt->close();
}

// Handle EDIT load
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Billing WHERE Bill_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_bill = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = (int)$_POST['ticket_id'];
    $rate_id   = (int)$_POST['rate_id'];
    $amount    = (float)$_POST['amount'];
    $bill_time = trim($_POST['bill_time']);
    $bill_id   = isset($_POST['bill_id']) ? (int)$_POST['bill_id'] : 0;

    if ($ticket_id <= 0 || $rate_id <= 0 || $amount <= 0 || empty($bill_time)) {
        $message = '<div class="alert alert-error">Please fill in all fields correctly.</div>';
    } else {
        if ($bill_id > 0) {
            $stmt = $conn->prepare("UPDATE Billing SET Ticket_ID=?, Rate_ID=?, Amount=?, Bill_Time=? WHERE Bill_ID=?");
            $stmt->bind_param("iidsi", $ticket_id, $rate_id, $amount, $bill_time, $bill_id);
            $action = "updated";
        } else {
            $stmt = $conn->prepare("INSERT INTO Billing (Ticket_ID, Rate_ID, Amount, Bill_Time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iids", $ticket_id, $rate_id, $amount, $bill_time);
            $action = "added";
        }
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Bill ' . $action . ' successfully.</div>';
            $edit_bill = null;
        } else {
            $message = '<div class="alert alert-error">Error: Ticket may already have a bill.</div>';
        }
        $stmt->close();
    }
}

// Dropdowns
$tickets = $conn->query("SELECT pt.Ticket_ID, v.Vehicle_Number FROM Parking_Ticket pt JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID ORDER BY pt.Ticket_ID DESC");
$rates   = $conn->query("SELECT Rate_ID, Vehicle_Type, Rate_Per_Hour FROM Parking_Rate ORDER BY Vehicle_Type");

// Fetch all bills
$bills = $conn->query("
    SELECT b.*, pt.Ticket_ID, v.Vehicle_Number, pr.Vehicle_Type, pr.Rate_Per_Hour
    FROM Billing b
    JOIN Parking_Ticket pt ON b.Ticket_ID = pt.Ticket_ID
    JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID
    JOIN Parking_Rate pr ON b.Rate_ID = pr.Rate_ID
    ORDER BY b.Bill_ID DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing</title>
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
        <a href="billing.php" class="active">Billing</a>
        <a href="payments.php">Payments</a>
        <a href="drivers.php">Drivers</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">Manage Billing</div>

    <?php echo $message; ?>

    <!-- Form -->
    <div class="form-box mb-20">
        <h3 style="margin-bottom:18px;font-size:15px;color:#333;">
            <?php echo $edit_bill ? 'Edit Bill' : 'Create Bill'; ?>
        </h3>
        <form method="POST" action="billing.php">

            <?php if ($edit_bill): ?>
                <input type="hidden" name="bill_id" value="<?php echo $edit_bill['Bill_ID']; ?>">
            <?php endif; ?>

            <label>Parking Ticket</label>
            <select name="ticket_id" required>
                <option value="">-- Select Ticket --</option>
                <?php while ($t = $tickets->fetch_assoc()): ?>
                    <option value="<?php echo $t['Ticket_ID']; ?>"
                        <?php echo ($edit_bill && $edit_bill['Ticket_ID'] == $t['Ticket_ID']) ? 'selected' : ''; ?>>
                        Ticket #<?php echo $t['Ticket_ID']; ?> - <?php echo htmlspecialchars($t['Vehicle_Number']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Parking Rate</label>
            <select name="rate_id" required>
                <option value="">-- Select Rate --</option>
                <?php while ($r = $rates->fetch_assoc()): ?>
                    <option value="<?php echo $r['Rate_ID']; ?>"
                        <?php echo ($edit_bill && $edit_bill['Rate_ID'] == $r['Rate_ID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($r['Vehicle_Type']); ?> - Rs <?php echo $r['Rate_Per_Hour']; ?>/hr
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Amount (Rs)</label>
            <input type="number" name="amount" placeholder="e.g. 150.00" step="0.01" min="0"
                   value="<?php echo $edit_bill ? $edit_bill['Amount'] : ''; ?>" required>

            <label>Bill Time</label>
            <input type="datetime-local" name="bill_time"
                   value="<?php echo $edit_bill ? date('Y-m-d\TH:i', strtotime($edit_bill['Bill_Time'])) : ''; ?>" required>

            <div class="form-actions">
                <button type="submit" class="btn btn-green">
                    <?php echo $edit_bill ? 'Update Bill' : 'Create Bill'; ?>
                </button>
                <?php if ($edit_bill): ?>
                    <a href="billing.php" class="btn btn-gray">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Bills Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Bill ID</th>
                    <th>Ticket</th>
                    <th>Vehicle</th>
                    <th>Rate Type</th>
                    <th>Amount (Rs)</th>
                    <th>Bill Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $bills->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['Bill_ID']; ?></td>
                    <td>#<?php echo $row['Ticket_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Number']); ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Type']); ?></td>
                    <td>Rs <?php echo number_format($row['Amount'], 2); ?></td>
                    <td><?php echo $row['Bill_Time']; ?></td>
                    <td>
                        <a href="billing.php?edit=<?php echo $row['Bill_ID']; ?>" class="btn btn-blue" style="padding:5px 12px;">Edit</a>
                        <a href="billing.php?delete=<?php echo $row['Bill_ID']; ?>"
                           class="btn btn-red" style="padding:5px 12px;"
                           onclick="return confirmDelete('bill')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($bills->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center" style="padding:20px;color:#999;">No bills found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
