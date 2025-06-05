<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: ../login.php");
    exit();
}

$manager_name = $_SESSION['user']['name'];
$role = $_SESSION['user']['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <h2>🚛 Stepstar</h2>
        <nav>
            <ul>
                <li><a href="shipments.php">📋 View Orders</a></li>
                <li><a href="drivers.php">👷 Drivers Management</a></li>
                <li><a href="vehicles.php">🚚 Vehicle Management</a></li>
                <li><a href="rates.php">💰 Rates Management</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
                <li><a href="expenses.php">💸 Expenses</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="user-info">
                <strong>👤 <?php echo htmlspecialchars($manager_name); ?></strong> | Role: <?php echo htmlspecialchars($role); ?>
            </div>
            <div class="top-actions">
                <a href="settings.php">⚙️ Settings</a>
                <a href="../logout.php">🚪 Logout</a>
            </div>
        </header>

        <section class="content-area">
            <h1>📊 Welcome to your Manager Dashboard</h1>
            <p>Select an option from the left sidebar to begin.</p>
        </section>
    </main>
</div>

</body>
</html>
