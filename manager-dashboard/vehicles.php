<?php
include '../includes/db.php';

$tenant_id = $_SESSION['user']['tenant_id']; // ✅ Add this
$success = $error = "";

// Handle addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {
    $reg = strtoupper(trim($_POST['vehicle_reg']));
    $type = trim($_POST['vehicle_type']);
    $capacity = floatval($_POST['capacity']);

    if ($reg && $type && $capacity) {
        $check = $conn->prepare("SELECT id FROM vehicles WHERE vehicle_reg = ?");
        $check->bind_param("s", $reg);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "duplicate";
        } else {
           $stmt = $conn->prepare("INSERT INTO vehicles (vehicle_reg, vehicle_type, capacity, tenant_id) VALUES (?, ?, ?, ?)");
           $stmt->bind_param("ssdi", $reg, $type, $capacity, $tenant_id);

            if ($stmt->execute()) {
                $success = "✅ Vehicle added successfully.";
            } else {
                $error = "❌ Error adding vehicle: " . $stmt->error;
            }
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM vehicles WHERE id = $del_id");
    header("Location: dashboard-manager.php?page=vehicles");
    exit();
}

$vehicles = $conn->query("SELECT * FROM vehicles WHERE tenant_id = $tenant_id ORDER BY created_at DESC");
?>

<style>
    .vehicle-form {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .vehicle-form input {
        padding: 8px;
        flex: 1;
        min-width: 200px;
    }
    .vehicle-form button {
        padding: 8px 16px;
    }
    .styled-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .styled-table th, .styled-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #ccc;
        text-align: left;
    }
    .styled-table th {
        background-color: #0077cc;
        color: #333;
    }
    .styled-table tr:hover {
        background-color: #f9f9f9;
    }
    .btn {
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        background-color: #e74c3c;
        color: white;
        margin-right: 5px;
    }
    .btn:hover {
        background-color: #c0392b;
    }
    .btn-view {
        background-color: #2980b9;
    }
    .btn-view:hover {
        background-color: #1f5e91;
    }
    .success, .error {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        transition: opacity 0.5s ease-in-out;
    }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
</style>

<div class="main-content">
    <div class="dashboard-header">
        <div class="user-info">🚚 Vehicle Management</div>
    </div>
    <div class="content-area">
        <h1>➕ Add Vehicle</h1>

        <?php if ($success): ?>
            <p class="success" id="messageBox"><?php echo $success; ?></p>
        <?php elseif ($error && $error !== 'duplicate'): ?>
            <p class="error" id="messageBox"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" class="vehicle-form">
            <input type="text" name="vehicle_reg" placeholder="Vehicle Registration" required>
            <input type="text" name="vehicle_type" placeholder="Type (e.g., Lorry, Trailer)" required>
            <input type="number" name="capacity" placeholder="Capacity (tons)" step="0.1" required>
            <button type="submit" name="add_vehicle">Add Vehicle</button>
        </form>

        <h2>📋 Registered Vehicles</h2>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Registration</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while($v = $vehicles->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($v['vehicle_reg']); ?></td>
                    <td><?php echo htmlspecialchars($v['vehicle_type']); ?></td>
                    <td><?php echo $v['capacity']; ?> tons</td>
                    <td><?php echo $v['created_at']; ?></td>
                    <td>
                        <a href="dashboard-manager.php?page=vehicles&delete=<?php echo $v['id']; ?>" class="btn" onclick="return confirm('Delete this vehicle?')">🗑️ Delete</a>
                        <a href="dashboard-manager.php?page=view_expenses&vehicle_id=<?= $v['id'] ?>" class="btn btn-view">📊 View Expenses</a>

                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($vehicles->num_rows === 0): ?>
                <tr><td colspan="6">No vehicles registered yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Auto-hide message after 3 seconds
    setTimeout(() => {
        const box = document.getElementById("messageBox");
        if (box) box.style.opacity = 0;
    }, 3000);
</script>
