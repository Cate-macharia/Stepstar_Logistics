<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'SUPER_ADMIN') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
$admin_name = $_SESSION['user']['name'];
$page = $_GET['page'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Dashboard</title>
     <link rel="stylesheet" href="../manager-dashboard/dashboard.css">
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div style="text-align: center; padding: 0; background: transparent; margin: 20px auto; width: 130px;">
  <img src="../images/flex-logo.jpg" alt="Flexbyte Logo" style="width: 120px; height: auto; display: block; margin: 0 auto; border-radius: 0; box-shadow: none;">
</div>


        <p>Welcome, <?= htmlspecialchars($admin_name) ?></p>
        <nav>
            <ul>
                <li><a href="super_admin_dashboard.php?page=home">🏠 Home</a></li>
                <li><a href="super_admin_dashboard.php?page=register_tenant">🏢 Register Tenant</a></li>
               <li><a href="super_admin_dashboard.php?page=tenant_list">📄 Tenant List</a></li>
                <li><a href="super_admin_dashboard.php?page=manage_users">👥 Manage Users</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </nav>
    </aside>

        <?php
        if ($page === 'register_tenant') {
            include 'register_tenant.php';
        } elseif ($page === 'tenant_list') {
    include 'tenant_list.php';
    }
        elseif ($page === 'manage_users') {
            include 'manage_users.php';
        } else {
            echo "<h1>📊 Super Admin Dashboard</h1><p>Use the side menu to manage tenants and users.</p>";
        }
        ?>
    </main>
</div>

</body>
</html>
