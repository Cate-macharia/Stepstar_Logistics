<?php

include '../includes/db.php';

$success = $error = "";
$tenant_id = $_SESSION['user']['tenant_id']; // ✅ Identify tenant

// Edit logic
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$edit_driver = null;

if ($edit_id) {
    $res = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'DRIVER' AND tenant_id = ?");
    $res->bind_param("ii", $edit_id, $tenant_id);
    $res->execute();
    $edit_driver = $res->get_result()->fetch_assoc();
}

// Update driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_driver'])) {
    $id = $_POST['driver_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nid = trim($_POST['national_id']);
    $fixed_salary = floatval($_POST['fixed_salary']);

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, national_id=?, fixed_salary=? WHERE id=? AND role='DRIVER' AND tenant_id=?");
    $stmt->bind_param("sssdii", $name, $email, $nid, $fixed_salary, $id, $tenant_id);

    if ($stmt->execute()) {
        $success = "✅ Driver updated successfully.";
    } else {
        $error = "❌ Failed to update: " . $stmt->error;
    }

    $edit_id = null;
    $edit_driver = null;
}

// Add driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_driver'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nid = trim($_POST['national_id']);
    $role = 'DRIVER';
    $password = trim($_POST['password']);
    $fixed_salary = floatval($_POST['fixed_salary']);

    $check = $conn->prepare("SELECT id FROM users WHERE national_id = ?");
    $check->bind_param("s", $nid);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "❌ A driver with this National ID already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, national_id, fixed_salary, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) die("❌ SQL Prepare failed: " . $conn->error);

        $stmt->bind_param("sssssdi", $name, $email, $password, $role, $nid, $fixed_salary, $tenant_id);

        if ($stmt->execute()) {
            $success = "✅ Driver added successfully.";
        } else {
            $error = "❌ Error adding driver: " . $stmt->error;
        }
    }
}

// Delete driver
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $del_id AND role = 'DRIVER' AND tenant_id = $tenant_id");
    header("Location: manage-drivers.php");
    exit();
}

// ✅ Fetch only tenant's drivers
$drivers = $conn->query("SELECT * FROM users WHERE role = 'DRIVER' AND tenant_id = $tenant_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Drivers</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
        }
        th, td {
            padding: 10px; border-bottom: 1px solid #ccc; text-align: left;
        }
        th { background-color:#0077cc; color:white; }
        .btn {
            padding: 6px 10px; border-radius: 4px; text-decoration: none;
            background-color: #2980b9; color: white;
        }
        .btn:hover { background-color: #1f5e91; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
<div class="main-content">
    <div class="dashboard-header">
        <div class="user-info">Welcome, Manager</div>
    </div>
    <div class="content-area">
        <h1>👥 Manage Drivers</h1>

        <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

        <h3><?= $edit_driver ? '✏️ Edit Driver' : '➕ Add New Driver' ?></h3>
        <form method="POST">
            <?php if ($edit_driver): ?>
                <input type="hidden" name="driver_id" value="<?= $edit_driver['id'] ?>">
            <?php endif; ?>

            <input type="text" name="name" placeholder="Full Name" required value="<?= $edit_driver['name'] ?? '' ?>">
            <input type="email" name="email" placeholder="Email" required value="<?= $edit_driver['email'] ?? '' ?>">
            <input type="text" name="national_id" placeholder="National ID" required value="<?= $edit_driver['national_id'] ?? '' ?>">
            <input type="number" step="0.01" name="fixed_salary" placeholder="fixed_salary (KES)" required value="<?= $edit_driver['salary'] ?? '' ?>">

            <?php if ($edit_driver): ?>
                <button type="submit" name="update_driver">💾 Update Driver</button>
                <a href="manage-drivers.php" class="btn" style="background:#777;margin-left:10px;">Cancel</a>
            <?php else: ?>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="add_driver">Add Driver</button>
            <?php endif; ?>
        </form>

        <h3 style="margin-top:30px;">📋 Registered Drivers</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>National ID</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; while($d = $drivers->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($d['name']) ?></td>
                    <td><?= htmlspecialchars($d['email']) ?></td>
                    <td><?= htmlspecialchars($d['national_id']) ?></td>
                    <td><?= $d['created_at'] ?></td>
                    <td>
                        <a href="dashboard-manager.php?page=manage-drivers&edit=<?= $d['id'] ?>" class="btn">✏️ Edit</a>
                        <a href="manage-drivers.php?delete=<?= $d['id'] ?>" class="btn" onclick="return confirm('Delete this driver?')">🗑️ Delete</a>
                        <a href="dashboard-manager.php?page=driver-salary&driver_id=<?= $d['id'] ?>" class="btn" style="background:#16a085;">💰 Salary</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($drivers->num_rows == 0): ?>
                <tr><td colspan="6">No drivers registered yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
