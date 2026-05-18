<?php
// ============================================================
// driver/my_tickets.php - Driver's Own Parking Tickets
// ============================================================

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$user_id = $_SESSION['user_id'];

// Fetch tickets for this driver's vehicles
$stmt = $conn->prepare("
    SELECT pt.Ticket_ID, pt.Entry_Time, pt.Exit_Time,
           v.Vehicle_Number, v.Vehicle_Type,
           ps.Slot_Number, pl.Location AS Lot_Location
    FROM Parking_Ticket pt
    JOIN Vehicle v ON pt.Vehicle_ID = v.Vehicle_ID
    JOIN Parking_Slot ps ON pt.Slot_ID = ps.Slot_ID
    JOIN Parking_Lot pl ON ps.Lot_ID = pl.Lot_ID
    WHERE v.User_ID = ?
    ORDER BY pt.Ticket_ID DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tickets = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tickets</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar">
    <div class="brand">Parking Management System | Imtiaz Super Mart</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="my_vehicle.php">My Vehicles</a>
        <a href="my_tickets.php" class="active">My Tickets</a>
        <a href="my_payments.php">My Payments</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page-container">
    <div class="page-title">My Parking Tickets</div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ticket ID</th>
                    <th>Vehicle</th>
                    <th>Slot</th>
                    <th>Location</th>
                    <th>Entry Time</th>
                    <th>Exit Time</th>
                    <th>Status</th>
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
                    <td><?php echo htmlspecialchars($row['Lot_Location']); ?></td>
                    <td><?php echo $row['Entry_Time']; ?></td>
                    <td><?php echo $row['Exit_Time'] ?? '-'; ?></td>
                    <td>
                        <?php if ($is_active): ?>
                            <span class="badge badge-green">Active</span>
                        <?php else: ?>
                            <span class="badge badge-gray">Closed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($tickets->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center" style="padding:20px;color:#999;">No parking tickets found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>
