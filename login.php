<?php
// ============================================================
// login.php - Login Page
// University Parking Management System
// ============================================================

session_start();

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: driver/dashboard.php");
    }
    exit();
}

require_once 'db.php';

$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role     = $_POST['role'];

    // Basic validation
    if (empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all fields.";
    } else {
        // Check user exists with matching email and password (MD5)
        // Using prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT User_ID, Name, Email FROM User WHERE Email = ? AND Password = MD5(?)");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the role exists for this user
            if ($role === 'admin') {
                $role_stmt = $conn->prepare("SELECT User_ID FROM Admin WHERE User_ID = ?");
            } else {
                $role_stmt = $conn->prepare("SELECT User_ID FROM Driver WHERE User_ID = ?");
            }
            $role_stmt->bind_param("i", $user['User_ID']);
            $role_stmt->execute();
            $role_result = $role_stmt->get_result();

            if ($role_result->num_rows === 1) {
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['User_ID'];
                $_SESSION['name']    = $user['Name'];
                $_SESSION['email']   = $user['Email'];
                $_SESSION['role']    = $role;

                // Redirect based on role
                if ($role === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: driver/dashboard.php");
                }
                exit();
            } else {
                $error = "You do not have " . ucfirst($role) . " access.";
            }
            $role_stmt->close();
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Parking Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">
        <h2>Parking Management System</h2>
        <p class="subtitle">Imtiaz Super Mart, Peshawar</p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   placeholder="Enter your email"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                   required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Enter your password" required>

            <label for="role">Login As</label>
            <select id="role" name="role" required>
                <option value="">-- Select Role --</option>
                <option value="admin"  <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin')  ? 'selected' : ''; ?>>Admin</option>
                <option value="driver" <?php echo (isset($_POST['role']) && $_POST['role'] === 'driver') ? 'selected' : ''; ?>>Driver</option>
            </select>

            <button type="submit" class="btn btn-green btn-full">Login</button>
        </form>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>
