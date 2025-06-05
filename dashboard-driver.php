<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'DRIVER') {
    header("Location: login.php");
    exit();
}

$driver_name = $_SESSION['user']['name'];
$national_id = $_SESSION['user']['national_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Driver Dashboard</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f2f5; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .header h2 { margin: 0; }
        .top-links a {
            margin-left: 15px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
            padding: 8px 12px;
            border-radius: 5px;
        }
        .top-links a:hover { background-color: #0056b3; }
        .nav {
            margin-top: 40px;
        }
        .nav a {
            display: inline-block;
            margin: 10px 20px 10px 0;
            padding: 15px 25px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .nav a:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>👋 Welcome back, Driver <?php echo htmlspecialchars($driver_name); ?> (ID: <?php echo htmlspecialchars($national_id); ?>)</h2>
    <div class="top-links">
        <a href="settings.php">⚙️ Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>

<div class="nav">
    <a href="new-order.php">📦 New Orders</a>
    <a href="order-history.php">📜 Order History</a>
</div>

<p>Use the tabs above to manage your deliveries efficiently.</p>

</body>
</html>