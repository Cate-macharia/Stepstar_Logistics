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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Dashboard</title>
    <link rel="stylesheet" href="style.css"> <!-- update this path to your CSS -->
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <h2>🚛 Stepstar</h2>
        <nav>
            <ul>
                <li><a href="/drivers/dashboard-driver.php">🏠 Dashboard</a></li>
                <li><a href="drivers/new-order.php">📦 New Orders</a></li>
                <li><a href="drivers/order-history.php">📜 Order History</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="user-info">
                <strong>👤 <?php echo htmlspecialchars($driver_name); ?></strong> | ID: <?php echo htmlspecialchars($national_id); ?>
            </div>
            <div class="top-actions">
                <a href="settings.php">⚙️ Settings</a>
                <a href="../logout.php">🚪 Logout</a>
            </div>
        </header>

        <section class="content-area">
            <h1>🚚 Welcome to your Driver Dashboard</h1>
            <p>Select an action from the sidebar to begin managing your deliveries.</p>
        </section>
    </main>
</div>

</body>
</html>
