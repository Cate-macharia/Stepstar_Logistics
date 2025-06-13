<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'DRIVER') {
    header("Location: ../login.php");
    exit();
}

$driver_name = $_SESSION['user']['name'];
$national_id = $_SESSION['user']['national_id'];
$page = $_GET['page'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <h2>🚛 Stepstar</h2>
        <nav>
            <ul>
                <li><a href="dashboard-driver.php?page=home">🏠 Dashboard</a></li>
                <li><a href="dashboard-driver.php?page=new-order">📦 New Orders</a></li>
                <li><a href="dashboard-driver.php?page=order-history">📜 Order History</a></li>
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
                <a href="logout.php">🚪 Logout</a>
            </div>
        </header>

        <section class="content-area">
            <?php
$page = $_GET['page'] ?? '';

if ($page === 'order-history') {
    include 'order-history.php';
} elseif ($page === 'new-order') {
    include 'new-order.php';
} else {
    echo "<h2>🚚 Welcome to your Driver Dashboard</h2>";
    echo "<p>Select an action from the sidebar to begin managing your deliveries.</p>";
}
?>

        </section>
    </main>
</div>

</body>
</html>
