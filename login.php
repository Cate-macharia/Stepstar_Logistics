<?php
session_start();
include 'includes/db.php';

if (isset($_GET['message']) && $_GET['message'] === 'loggedout') {
    echo "<p style='color: green;'>✅ You have been logged out successfully.</p>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE name = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && $password === $user['password']) {
        $_SESSION['user'] = $user;

        if ($user['role'] === 'DRIVER') {
            header("Location: dashboard-driver.php");
        } elseif ($user['role'] === 'MANAGER') {
            header("Location: manager-dashboard/dashboard-manager.php");
        } elseif ($user['role'] === 'ADMIN') {
            header("Location: admin-dashboard.php");
        }
        exit();
    } else {
        echo "<p style='color: red;'>❌ Invalid name or password!</p>";
    }
}
?>

<h2>🔐 Login</h2>
<form method="POST" action="">
    <input type="text" name="name" placeholder="Name" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>
