<?php
// ============================================================
// driver/my_payments.php - Driver's Billing and Payment Details
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$user_id = $_SESSION['user_id'];

// Fetch billing and payment records for this driver
$stmt = $conn->prepare("
    SELECT b.Bill_ID, b.Amount, b.Bill_Time,
           pt.Ticket_ID, pt.Entry_Time, pt.Exit_Time,
           v.Vehicle_Number, v.Vehicle_Type,
           pr.Rate_Per_Hour,
           p.Payment_ID, p.Payment_Method, p.Payment_Time, p.Payment_Status
    FROM Billing b
    JOIN Parking_Ticket pt ON b.Ticket_ID = pt.Ticket_ID
    JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID
    JOIN Parking_Rate pr ON b.Rate_ID = pr.Rate_ID
    LEFT JOIN Payment p ON b.Bill_ID = p.Bill_ID
    WHERE v.User_ID = ?
    ORDER BY b.Bill_ID DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$records = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Payments</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">Parking Management System | Imtiaz Super Mart</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="my_vehicle.php">My Vehicles</a>
        <a href="my_tickets.php">My Tickets</a>
        <a href="my_payments.php" class="active">My Payments</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">My Billing and Payments</div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Bill ID</th>
                    <th>Ticket</th>
                    <th>Vehicle</th>
                    <th>Amount (Rs)</th>
                    <th>Bill Time</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $records->fetch_assoc()):
                // Payment status badge
                $badge = 'badge-gray';
                $status = $row['Payment_Status'] ?? 'No Payment';
                if ($status === 'Completed') $badge = 'badge-green';
                elseif ($status === 'Failed')    $badge = 'badge-red';
                elseif ($status === 'Pending')   $badge = 'badge-yellow';
                elseif ($status === 'Refunded')  $badge = 'badge-blue';
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td>#<?php echo $row['Bill_ID']; ?></td>
                    <td>#<?php echo $row['Ticket_ID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Vehicle_Number']); ?> (<?php echo $row['Vehicle_Type']; ?>)</td>
                    <td>Rs <?php echo number_format($row['Amount'], 2); ?></td>
                    <td><?php echo $row['Bill_Time']; ?></td>
                    <td><?php echo $row['Payment_Method'] ?? '-'; ?></td>
                    <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($records->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center" style="padding:20px;color:#999;">No billing records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
