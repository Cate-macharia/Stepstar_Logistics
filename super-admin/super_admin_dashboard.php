<?php
// super_admin_dashboard.php
session_start();
include '../includes/db.php';

// Check if user is super admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'SUPER_ADMIN') {
    header("Location: ../login.php");
    exit();
}

// Fetch tenants
$tenants = $conn->query("SELECT * FROM tenants ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Super Admin Dashboard</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        h2 { color: #005baa; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #f2f2f2; }
        .btn { padding: 6px 12px; background: #005baa; color: white; border: none; border-radius: 4px; text-decoration: none; }
        .btn:hover { background: #003f7f; }
    </style>
</head>
<body>
    <h2>👑 Super Admin - Manage Tenants</h2>

    <a href="register_tenant.php" class="btn">➕ Register New Tenant</a>
    <br><br>

    <table>
        <tr>
            <th>#</th>
            <th>Business Name</th>
            <th>Owner</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Date Registered</th>
            <th>Actions</th>
        </tr>
        <?php $i = 1; while ($tenant = $tenants->fetch_assoc()): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($tenant['business_name']) ?></td>
            <td><?= htmlspecialchars($tenant['owner_name']) ?></td>
            <td><?= htmlspecialchars($tenant['email']) ?></td>
            <td><?= htmlspecialchars($tenant['phone']) ?></td>
            <td><?= htmlspecialchars($tenant['created_at']) ?></td>
            <td>
                <a class="btn" href="view_tenant_data.php?id=<?= $tenant['id'] ?>">🔍 View</a>
                <a class="btn" style="background:#d9534f" href="delete_tenant.php?id=<?= $tenant['id'] ?>" onclick="return confirm('Delete this tenant?')">🗑 Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
