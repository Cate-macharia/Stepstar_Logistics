<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'DRIVER') {
    header("Location: login.php");
    exit();
}

$driver_id = $_SESSION['user']['id'];
$success_message = "";
$error_message = "";

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current hashed password from DB
    $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $driver_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $hashed_password);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Validate current password
    if (!password_verify($current_password, $hashed_password)) {
        $error_message = "❌ Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "❌ New passwords do not match.";
    } else {
        // Update password
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "si", $new_hashed, $driver_id);
        if (mysqli_stmt_execute($update_stmt)) {
            $success_message = "✅ Password updated successfully!";
        } else {
            $error_message = "❌ Something went wrong. Try again.";
        }
        mysqli_stmt_close($update_stmt);
    }
}
?>

<h2>⚙️ Account Settings</h2>
<a href="dashboard-driver.php" style="text-decoration:none; background:#6c757d; color:white; padding:8px 12px; border-radius:5px; display:inline-block; margin-bottom:20px;">⬅️ Back to Dashboard</a>

<?php if ($success_message): ?>
    <p style="color:green;"><?php echo $success_message; ?></p>
<?php elseif ($error_message): ?>
    <p style="color:red;"><?php echo $error_message; ?></p>
<?php endif; ?>

<form method="POST">
    <label>Current Password:</label><br>
    <input type="password" name="current_password" required><br><br>

    <label>New Password:</label><br>
    <input type="password" name="new_password" required><br><br>

    <label>Confirm New Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit">🔄 Update Password</button>
</form>
