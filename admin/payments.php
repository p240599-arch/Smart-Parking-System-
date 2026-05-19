<?php
// ============================================================
// admin/payments.php - Manage Payments (Full CRUD)
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message     = '';
$edit_payment = null;

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Payment WHERE Payment_ID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Payment deleted.</div>';
    } else {
        $message = '<div class="alert alert-error">Error deleting payment.</div>';
    }
    $stmt->close();
}

// Handle EDIT load
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM Payment WHERE Payment_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_id        = (int)$_POST['bill_id'];
    $payment_method = $_POST['payment_method'];
    $payment_time   = trim($_POST['payment_time']);
    $payment_status = $_POST['payment_status'];
    $payment_id     = isset($_POST['payment_id']) ? (int)$_POST['payment_id'] : 0;

    if ($bill_id <= 0 || empty($payment_method) || empty($payment_time) || empty($payment_status)) {
        $message = '<div class="alert alert-error">Please fill in all fields.</div>';
    } else {
        if ($payment_id > 0) {
            $stmt = $conn->prepare("UPDATE Payment SET Bill_ID=?, Payment_Method=?, Payment_Time=?, Payment_Status=? WHERE Payment_ID=?");
            $stmt->bind_param("isssi", $bill_id, $payment_method, $payment_time, $payment_status, $payment_id);
            $action = "updated";
        } else {
            $stmt = $conn->prepare("INSERT INTO Payment (Bill_ID, Payment_Method, Payment_Time, Payment_Status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $bill_id, $payment_method, $payment_time, $payment_status);
            $action = "added";
        }
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Payment ' . $action . ' successfully.</div>';
            $edit_payment = null;
        } else {
            $message = '<div class="alert alert-error">Error: Bill may already have a payment record.</div>';
        }
        $stmt->close();
    }
}

// Fetch bills for dropdown
$bills = $conn->query("SELECT b.Bill_ID, b.Amount, v.Vehicle_Number FROM Billing b JOIN Parking_Ticket pt ON b.Ticket_ID = pt.Ticket_ID JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID ORDER BY b.Bill_ID DESC");

// Fetch all payments
$payments = $conn->query("
    SELECT p.*, b.Amount, v.Vehicle_Number
    FROM Payment p
    JOIN Billing b ON p.Bill_ID = b.Bill_ID
    JOIN Parking_Ticket pt ON b.Ticket_ID = pt.Ticket_ID
    JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID
    ORDER BY p.Payment_ID DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payments</title>
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
        <a href="payments.php" class="active">Payments</a>
        <a href="drivers.php">Drivers</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">Manage Payments</div>

    <?php echo $message; ?>

    <!-- Form -->
    <div class="form-box mb-20">
        <h3 style="margin-bottom:18px;font-size:15px;color:#333;">
            <?php echo $edit_payment ? 'Edit Payment' : 'Add Payment'; ?>
        </h3>
        <form method="POST" action="payments.php">

            <?php if ($edit_payment): ?>
                <input type="hidden" name="payment_id" value="<?php echo $edit_payment['Payment_ID']; ?>">
            <?php endif; ?>

            <label>Bill</label>
            <select name="bill_id" required>
                <option value="">-- Select Bill --</option>
                <?php while ($b = $bills->fetch_assoc()): ?>
                    <option value="<?php echo $b['Bill_ID']; ?>"
                        <?php echo ($edit_payment && $edit_payment['Bill_ID'] == $b['Bill_ID']) ? 'selected' : ''; ?>>
                        Bill #<?php echo $b['Bill_ID']; ?> - <?php echo htmlspecialchars($b['Vehicle_Number']); ?> - Rs <?php echo number_format($b['Amount'], 2); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Payment Method</label>
            <select name="payment_method" required>
                <?php
                $methods = ['Cash', 'Card', 'Online', 'Wallet'];
                foreach ($methods as $m):
                    $sel = ($edit_payment && $edit_payment['Payment_Method'] === $m) ? 'selected' : '';
                ?>
                    <option value="<?php echo $m; ?>" <?php echo $sel; ?>><?php echo $m; ?></option>
                <?php endforeach; ?>
            </select>

            <label>Payment Time</label>
            <input type="datetime-local" name="payment_time"
                   value="<?php echo $edit_payment ? date('Y-m-d\TH:i', strtotime($edit_payment['Payment_Time'])) : ''; ?>" required>

            <label>Payment Status</label>
            <select name="payment_status" required>
                <?php
                $statuses = ['Pending', 'Completed', 'Failed', 'Refunded'];
                foreach ($statuses as $s):
                    $sel = ($edit_payment && $edit_payment['Payment_Status'] === $s) ? 'selected' : '';
                ?>
                    <option value="<?php echo $s; ?>" <?php echo $sel; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>

            <div class="form-actions">
                <button type="submit" class="btn btn-green">
                    <?php echo $edit_payment ? 'Update Payment' : 'Add Payment'; ?>
                </button>
                <?php if ($edit_payment): ?>
                    <a href="payments.php" class="btn btn-gray">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Payment ID</th>
                    <th>Bill ID</th>
                    <th>Vehicle</th>
                    <th>Amount (Rs)</th>
                    <th>Method</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $payments->fetch_assoc()):
                // Status badge
                $badge = 'badge-gray';
                if ($row['Payment_Status'] === 'Completed') $badge = 'badge-green';
                elseif ($row['Payment_Status'] === 'Failed')    $badge = 'badge-red';
                elseif ($row['Payment_Status'] === 'Pending')   $badge = 'badge-yellow';
                elseif ($row['Payment_Status'] === 'Refunded')  $badge = 'badge-blue';
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['Payment_ID']; ?></td>
                    <td>#<?php echo $row['Bill_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Number']); ?></td>
                    <td>Rs <?php echo number_format($row['Amount'], 2); ?></td>
                    <td><?php echo $row['Payment_Method']; ?></td>
                    <td><?php echo $row['Payment_Time']; ?></td>
                    <td><span class="badge <?php echo $badge; ?>"><?php echo $row['Payment_Status']; ?></span></td>
                    <td>
                        <a href="payments.php?edit=<?php echo $row['Payment_ID']; ?>" class="btn btn-blue" style="padding:5px 12px;">Edit</a>
                        <a href="payments.php?delete=<?php echo $row['Payment_ID']; ?>"
                           class="btn btn-red" style="padding:5px 12px;"
                           onclick="return confirmDelete('payment')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($payments->num_rows === 0): ?>
                <tr><td colspan="9" class="text-center" style="padding:20px;color:#999;">No payments found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
