<?php
include '../includes/db.php';

// Fetch tenants to link users to
$tenantOptions = $conn->query("SELECT id, business_name FROM tenants ORDER BY business_name ASC");

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

// Handle new or updated user submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $national_id = $_POST['national_id'];
    $role = $_POST['role'];
    $tenant_id = $_POST['tenant_id'];

    if (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
        // Update user
        $user_id = (int)$_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, national_id=?, role=?, tenant_id=? WHERE id=?");
        $stmt->bind_param("sssssii", $name, $email, $password, $national_id, $role, $tenant_id, $user_id);
        if ($stmt->execute()) {
            echo "<p style='color:green;text-align:center;'>✅ User updated successfully!</p>";
        } else {
            echo "<p style='color:red;text-align:center;'>❌ Error updating: " . $stmt->error . "</p>";
        }
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, national_id, role, tenant_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $email, $password, $national_id, $role, $tenant_id);
        if ($stmt->execute()) {
            echo "<p style='color:green;text-align:center;'>✅ User added successfully!</p>";
        } else {
            echo "<p style='color:red;text-align:center;'>❌ Error: " . $stmt->error . "</p>";
        }
    }
}

// Fetch all users
$users = $conn->query("SELECT u.*, t.business_name FROM users u LEFT JOIN tenants t ON u.tenant_id = t.id ORDER BY u.role, u.tenant_id");
?>

<div class="manage-users-container">
    <h2>👥 Manage Users</h2>

    <form method="POST" style="margin-bottom: 30px;">
        <h3 style="margin-bottom: 15px;">➕ Add New User</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
            <input type="text" name="name" placeholder="Full Name" required style="flex: 1 1 48%;">
            <input type="email" name="email" placeholder="Email Address" required style="flex: 1 1 48%;">
            <input type="text" name="national_id" placeholder="National ID" required style="flex: 1 1 48%;">
            <input type="password" name="password" placeholder="Password" required style="flex: 1 1 48%;">
            <select name="role" required style="flex: 1 1 48%;">
                <option value="">-- Select Role --</option>
                <option value="MANAGER">Manager</option>
                <option value="DRIVER">Driver</option>
            </select>
            <select name="tenant_id" required style="flex: 1 1 48%;">
                <option value="">-- Assign to Tenant --</option>
                <?php while ($t = $tenantOptions->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['business_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit">👤 Add User</button>
    </form>

    <h3>📋 Existing Users</h3>
    <div style="overflow-x:auto;">
        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <thead>
                <tr style="background:#eee;">
                      <th>ID</th>
                      <th>Name</th>
                     <th>Email</th>
                     <th>National ID</th>
                     <th>Role</th>
                     <th>Tenant</th>
                     <th>Actions</th>
                </tr>

            </thead>
            <tbody>
            <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                     <td><?= $row['id'] ?></td>
                     <td><?= htmlspecialchars($row['name']) ?></td>
                     <td><?= htmlspecialchars($row['email']) ?></td>
                     <td><?= htmlspecialchars($row['national_id']) ?></td>
                     <td><?= $row['role'] ?></td>
                     <td><?= $row['business_name'] ?? '❌ None' ?></td>
                    <td style="white-space: nowrap;">
        <a href="?page=manage_users&edit=<?= $row['id'] ?>" style="margin-right: 10px; color: #007bff; text-decoration: none;">✏️ Edit</a>
        <a href="?page=manage_users&delete=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')" style="color: #dc3545; text-decoration: none;">🗑️ Delete</a>
    </td>
</tr>

            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.container {
    padding: 20px;
    background: #fff;
    color: #000;
    border-radius: 8px;
}
.container h2, h3 {
    margin-bottom: 10px;
}
input, select, button {
    padding: 8px;
    margin: 6px 0;
    width: 100%;
    border-radius: 6px;
    border: 1px solid #ccc;
}
button {
    background: teal;
    color: white;
    font-weight: bold;
    border: none;
    cursor: pointer;
}
.manage-users-container {
    max-width: 100%;
    width: 95%;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    color: #000;
}

.manage-users-container input,
.manage-users-container select {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 16px;
}

.manage-users-container button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    background: teal;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.manage-users-container table {
    width: 100%;
    margin-top: 30px;
    border-collapse: collapse;
    text-align: left;
}

.manage-users-container th, .manage-users-container td {
    border: 1px solid #ddd;
    padding: 10px;
}

.manage-users-container th {
    background: #f2f2f2;
}
</style>
