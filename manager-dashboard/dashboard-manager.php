<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: ../login.php");
    exit();
}

$manager_name = $_SESSION['user']['name'];
$role = $_SESSION['user']['role'];
$page = $_GET['page'] ?? 'home';
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
                <li><a href="dashboard-manager.php?page=view_orders">📋 View Orders</a></li>
                <li><a href="dashboard-manager.php?page=manage-drivers">👷 Drivers Management</a></li>
                <li><a href="dashboard-manager.php?page=vehicles">🚚 Vehicle Management</a></li>
                <li><a href="dashboard-manager.php?page=rates">💰 Rates Management</a></li>
                <li><a href="dashboard-manager.php?page=reports">📈 Reports</a></li>
                <li><a href="dashboard-manager.php?page=expenses">💸 Expenses</a></li>
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
            <?php
            switch ($page) {
                case 'view_orders':
                    include 'view_orders.php';
                    break;
                case 'manage-drivers':
                    include 'manage-drivers.php';
                    break;
                case 'vehicles':
                    include 'vehicles.php';
                    break;
                case 'rates':
                    include 'rates.php';
                    break;
                case 'reports':
                    include 'reports.php';
                    break;
                case 'expenses':
                    include 'expenses.php';
                    break;
                    case 'view_expenses':
                    include 'expenses.php';
                    break;

                default:
                    echo "<h1>📊 Welcome to your Manager Dashboard</h1>
                          <p>Select an option from the left sidebar to begin.</p>";
            }
            ?>
        </section>
    </main>
</div>

</body>
</html>
