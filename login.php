<?php
session_start();
include 'includes/db.php';

// ✅ Show logout success message if redirected from logout
if (isset($_GET['message']) && $_GET['message'] === 'loggedout') {
    echo "<p style='color: green;'>✅ You have been logged out successfully.</p>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;

        // Redirect by role
        if ($user['role'] === 'DRIVER') {
            header("Location: dashboard-driver.php");
        } elseif ($user['role'] === 'MANAGER') {
            header("Location: dashboard-manager.php");
        }
        exit();
    } else {
        echo "<p style='color: red;'>❌ Invalid email or password!</p>";
    }
}
?>

<h2>Login</h2>
<form method="POST" action="">
  <input type="email" name="email" placeholder="Email" required><br><br>
  <input type="password" name="password" placeholder="Password" required><br><br>
  <button type="submit">Login</button>
</form>
