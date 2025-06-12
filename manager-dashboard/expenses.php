<?php
include '../includes/db.php';

$success = $error = "";
$type = $_GET['type'] ?? '';
$vehicle_id = $_GET['vehicle_id'] ?? null;
$driver_id = $_GET['driver_id'] ?? null;
$filter = $_GET['filter'] ?? 'all';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// Handle addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $type = $_POST['expense_type'];
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $vehicle_id = $_POST['vehicle_id'] ?? null;
    $driver_id = $_POST['driver_id'] ?? null;

    if ($description && $amount > 0) {
        $stmt = $conn->prepare("INSERT INTO expenses (type, description, amount, vehicle_id, driver_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdii", $type, $description, $amount, $vehicle_id, $driver_id);
        if ($stmt->execute()) {
            $success = "✅ Expense recorded.";
        } else {
            $error = "❌ Failed to add expense: " . $stmt->error;
        }
    } else {
        $error = "❌ All fields required.";
    }
}

// Build WHERE clause for filtering
$whereClause = "1";
if ($filter === 'today') {
    $whereClause = "DATE(created_at) = CURDATE()";
} elseif ($filter === 'week') {
    $whereClause = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $whereClause = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
} elseif ($filter === 'range' && $from && $to) {
    $whereClause = "DATE(created_at) BETWEEN '$from' AND '$to'";
}

$expenses = $conn->query("SELECT * FROM expenses WHERE $whereClause ORDER BY created_at DESC");
$totalResult = $conn->query("SELECT SUM(amount) as total FROM expenses WHERE $whereClause");
$totalRow = $totalResult->fetch_assoc();
$totalAmount = $totalRow['total'] ?? 0;

$vehicles = $conn->query("SELECT id, vehicle_reg FROM vehicles");
$drivers = $conn->query("SELECT id, name, national_id FROM users WHERE role='DRIVER'");
?>

<style>
    .expense-form {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .expense-form input, .expense-form select {
        padding: 8px;
        flex: 1;
        min-width: 200px;
    }
    .expense-form button {
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
        background-color: #f4f4f4;
        color: #333;
    }
    .success, .error {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        transition: opacity 0.5s ease-in-out;
    }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .filter-bar { margin-bottom: 20px; }
</style>

<div class="main-content">
    <div class="dashboard-header">
        <div class="user-info">💸 Expenses Management</div>
    </div>
    <div class="content-area">
        <h1>➕ Add Expense</h1>

        <?php if ($success): ?><p class="success" id="messageBox"><?php echo $success; ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error" id="messageBox"><?php echo $error; ?></p><?php endif; ?>

        <form method="POST" class="expense-form">
            <select name="expense_type" required onchange="toggleFields(this.value)">
                <option value="general" <?= $type === 'general' ? 'selected' : '' ?>>General</option>
                <option value="vehicle" <?= $type === 'vehicle' ? 'selected' : '' ?>>Vehicle</option>
                <option value="driver" <?= $type === 'driver' ? 'selected' : '' ?>>Driver (Millage)</option>
            </select>

            <select name="vehicle_id" id="vehicleField" style="display:<?= $type === 'vehicle' ? 'block' : 'none' ?>">
                <option value="">Select Vehicle</option>
                <?php while($v = $vehicles->fetch_assoc()): ?>
                    <option value="<?= $v['id'] ?>" <?= ($vehicle_id == $v['id']) ? 'selected' : '' ?>><?= $v['vehicle_reg'] ?></option>
                <?php endwhile; ?>
            </select>

            <select name="driver_id" id="driverField" style="display:<?= $type === 'driver' ? 'block' : 'none' ?>">
                <option value="">Select Driver</option>
                <?php while($d = $drivers->fetch_assoc()): ?>
                    <option value="<?= $d['id'] ?>" <?= ($driver_id == $d['id']) ? 'selected' : '' ?>><?= $d['name'] ?> (ID: <?= $d['national_id'] ?>)</option>
                <?php endwhile; ?>
            </select>

            <input type="text" name="description" placeholder="Description" required>
            <input type="number" name="amount" placeholder="Amount" step="0.01" required>
            <button type="submit" name="add_expense">Add Expense</button>
        </form>

        <h2>📋 Expense Records</h2>

        <form method="GET" class="filter-bar">
            <label>Filter:</label>
            <select name="filter" onchange="this.form.submit()">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
                <option value="today" <?= $filter === 'today' ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= $filter === 'week' ? 'selected' : '' ?>>This Week</option>
                <option value="month" <?= $filter === 'month' ? 'selected' : '' ?>>This Month</option>
                <option value="range" <?= $filter === 'range' ? 'selected' : '' ?>>Date Range</option>
            </select>
            <input type="date" name="from" value="<?= $from ?>" <?= $filter === 'range' ? '' : 'disabled' ?>>
            <input type="date" name="to" value="<?= $to ?>" <?= $filter === 'range' ? '' : 'disabled' ?>>
            <button type="submit">Apply</button>
        </form>

        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while ($exp = $expenses->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= ucfirst($exp['type']) ?></td>
                    <td><?= htmlspecialchars($exp['description']) ?></td>
                    <td>KES <?= number_format($exp['amount'], 2) ?></td>
                    <td><?= $exp['created_at'] ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if ($expenses->num_rows === 0): ?>
                <tr><td colspan="5">No expenses recorded.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align:right;">Total:</th>
                    <th colspan="2">KES <?= number_format($totalAmount, 2) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
function toggleFields(type) {
    document.getElementById('vehicleField').style.display = (type === 'vehicle') ? 'block' : 'none';
    document.getElementById('driverField').style.display = (type === 'driver') ? 'block' : 'none';
}
setTimeout(() => {
    const box = document.getElementById("messageBox");
    if (box) box.style.opacity = 0;
}, 3000);
</script>
