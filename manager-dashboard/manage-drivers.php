<?php

$success = $error = "";

// Handle driver addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_driver'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nid = trim($_POST['national_id']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Check if NID exists
    $check = $conn->prepare("SELECT id FROM users WHERE national_id = ?");
    $check->bind_param("s", $nid);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "❌ A driver with this National ID already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, national_id) VALUES (?, ?, ?, 'DRIVER', ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $nid);
        if ($stmt->execute()) {
            $success = "✅ Driver added successfully.";
        } else {
            $error = "❌ Error adding driver: " . $stmt->error;
        }
    }
}

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $del_id AND role = 'DRIVER'");
    header("Location: manage-drivers.php");
    exit();
}

// Fetch all drivers
$drivers = $conn->query("SELECT * FROM users WHERE role = 'DRIVER' ORDER BY created_at DESC");
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
        th { background-color: #f4f4f4; }
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
<div class="wrapper">
    <div class="sidebar">
        <h2>🚚 Stepstar Logistics</h2>
        <ul>
            <li><a href="dashboard-manager.php">🏠 Dashboard</a></li>
            <li><a href="view_orders.php">📄 View Orders</a></li>
            <li><a href="manage-drivers.php">👥 Manage Drivers</a></li>
            <li><a href="manage-vehicles.php">🚛 Manage Vehicles</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="dashboard-header">
            <div class="user-info">Welcome, Manager</div>
        </div>
        <div class="content-area">
            <h1>👥 Manage Drivers</h1>

            <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
            <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

            <h3>➕ Add New Driver</h3>
            <form method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="national_id" placeholder="National ID" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="add_driver">Add Driver</button>
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
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($d['name']); ?></td>
                        <td><?php echo htmlspecialchars($d['email']); ?></td>
                        <td><?php echo htmlspecialchars($d['national_id']); ?></td>
                        <td><?php echo $d['created_at']; ?></td>
                        <td>
                            <a href="edit-driver.php?id=<?php echo $d['id']; ?>" class="btn">✏️ Edit</a>
                            <a href="manage-drivers.php?delete=<?php echo $d['id']; ?>" class="btn" onclick="return confirm('Delete this driver?')">🗑️ Delete</a>
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
</div>
</body>
</html>
