<?php
include '../includes/db.php';

// Handle edit request
$editUser = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editRes = $conn->query("SELECT * FROM users WHERE id = $editId");
    $editUser = $editRes->fetch_assoc();
}
// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $deleteId");
    echo "<p style='color:red;text-align:center;'>🗑️ User deleted successfully.</p>";
}

// Handle deletion (soft delete by setting archived = 1)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tenantId = (int)$_GET['delete'];
    $conn->query("UPDATE tenants SET archived = 1 WHERE id = $tenantId");
    echo "<p style='color:green;text-align:center;'>✅ Tenant archived successfully.</p>";
}

// Retrieve tenants (excluding archived)
$tenants = $conn->query("SELECT * FROM tenants ORDER BY created_at DESC");

?>

<div class="tenant-list-container">
    <h2>🏢 Tenant List</h2>

    <div style="overflow-x:auto;">
        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <thead>
                <tr style="background:#0d5c63; color: white;">
                    <th>ID</th>
                    <th>Business Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Domain</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $tenants->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['business_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['domain']) ?: '<span style="color:gray;">(None)</span>' ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="?page=tenant_list&delete=<?= $row['id'] ?>"
                           onclick="return confirm('Archive this tenant?')"
                           style="color: crimson; text-decoration: none;">🗃 Archive</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.tenant-list-container {
    width: 95%;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    color: #000;
}

.tenant-list-container h2 {
    margin-bottom: 20px;
    font-size: 24px;
}

.tenant-list-container table {
    width: 100%;
    border-collapse: collapse;
    background: #f9f9f9;
}

.tenant-list-container th, .tenant-list-container td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

.tenant-list-container th {
    background-color: #0d5c63;
    color: white;
}
</style>
