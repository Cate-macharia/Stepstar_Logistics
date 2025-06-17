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
    $specific_type = $_POST['specific_type'] ?? '';
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $vehicle_id = ($type === 'vehicle') ? intval($_POST['vehicle_id']) : null;
    $driver_id = ($type === 'driver') ? intval($_POST['driver_id']) : null;

    if ($amount > 0) {
        $stmt = $conn->prepare("INSERT INTO expenses (type, specific_type, description, amount, vehicle_id, driver_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddi", $type, $specific_type, $description, $amount, $vehicle_id, $driver_id);
        if ($stmt->execute()) {
            header("Location: dashboard-manager.php?page=expenses&success=1&filter=$filter&from=$from&to=$to");
            exit();
        } else {
            $error = "❌ Failed to add expense: " . $stmt->error;
        }
    } else {
        $error = "❌ Amount is required.";
    }
}

if (isset($_GET['success'])) {
    $success = "✅ Expense recorded.";
}

$whereParts = [];
if ($vehicle_id) {
    $whereParts[] = "vehicle_id = " . intval($vehicle_id);
}
if ($filter === 'today') {
    $whereParts[] = "DATE(created_at) = CURDATE()";
} elseif ($filter === 'week') {
    $whereParts[] = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $whereParts[] = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
} elseif ($filter === 'range' && $from && $to) {
    $whereParts[] = "DATE(created_at) BETWEEN '$from' AND '$to'";
}
$whereClause = count($whereParts) ? implode(" AND ", $whereParts) : "1";

$expenses = $conn->query("SELECT * FROM expenses WHERE $whereClause ORDER BY created_at DESC");
$totalResult = $conn->query("SELECT SUM(amount) as total FROM expenses WHERE $whereClause");
$totalRow = $totalResult->fetch_assoc();
$totalAmount = $totalRow['total'] ?? 0;

$vehicles = $conn->query("SELECT id, vehicle_reg FROM vehicles");
$drivers = $conn->query("SELECT id, name, national_id FROM users WHERE role='DRIVER'");
?>

<style>
    .expense-form {
        background: #f9f9f9;
        border: 1px solid #ddd;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
    }

    .expense-form label {
        font-weight: bold;
        margin-bottom: 4px;
        display: block;
        color: #333;
    }

    .expense-form select,
    .expense-form input[type="text"],
    .expense-form input[type="number"] {
        padding: 8px;
        width: 100%;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .expense-form button {
        padding: 10px 20px;
        background: #0077cc;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }

    .expense-form button:hover {
        background: #005fa3;
    }

    .styled-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin-top: 10px;
    }

    .styled-table thead th {
        background-color: #0077cc;
        color: #ffffff;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #005fa3;
    }

    .styled-table tbody td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    .styled-table tfoot th {
        background-color:rgb(91, 15, 105);
        padding: 14px;
        text-align: center;
        font-weight: bold;
    }

    .styled-table tbody tr:nth-child(even) {
        background-color:rgb(68, 70, 137);
    }

    .styled-table tbody tr:hover {
        background-color:rgb(55, 77, 96);
    }

    .success, .error {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        font-weight: bold;
    }

    .success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>


<form method="POST" class="expense-form">
    <select name="expense_type" required onchange="toggleFields(this.value)">
        <option value="general" <?= $type === 'general' ? 'selected' : '' ?>>General</option>
        <option value="vehicle" <?= $type === 'vehicle' ? 'selected' : '' ?>>Vehicle</option>
        <option value="driver" <?= $type === 'driver' ? 'selected' : '' ?>>Driver</option>
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

    <select name="specific_type" id="specificTypeField">
        <option value="">Select Specific Expense</option>
    </select>

    <input type="text" name="description" placeholder="Narration or purpose (optional)">
    <input type="number" name="amount" placeholder="Amount" step="0.01" required>
    <button type="submit" name="add_expense">Add Expense</button>
</form>

<h2>📋 Expense Records</h2>
<table class="styled-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Type</th>
            <th>Specific</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; while ($exp = $expenses->fetch_assoc()): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($exp['type']) ?></td>
            <td><?= htmlspecialchars($exp['specific_type']) ?></td>
            <td><?= htmlspecialchars($exp['description']) ?></td>
            <td>KES <?= number_format($exp['amount'], 2) ?></td>
            <td><?= $exp['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if ($expenses->num_rows === 0): ?>
        <tr><td colspan="6" style="color:red;">⚠️ No expenses found for this filter.</td></tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" style="text-align:right;">Total:</th>
            <th colspan="2">KES <?= number_format($totalAmount, 2) ?></th>
        </tr>
    </tfoot>
</table>

<script>
function toggleFields(type) {
    document.getElementById('vehicleField').style.display = (type === 'vehicle') ? 'block' : 'none';
    document.getElementById('driverField').style.display = (type === 'driver') ? 'block' : 'none';

    const specific = document.getElementById('specificTypeField');
    specific.innerHTML = '';
    let options = [];
    if (type === 'vehicle') {
        options = ['Insurance', 'Sacco', 'Loan', 'Repairs', 'Tyre Exchange', 'Speed Governor', 'Inspection', 'KENHA', 'Fuel', 'Service', 'Brokerage Fee', 'Contingency Fee'];
    } else if (type === 'driver') {
        options = ['Others'];
    } else {
        options = ['Miscellaneous', 'others'];
    }
    options.forEach(opt => {
        const el = document.createElement('option');
        el.value = opt;
        el.textContent = opt;
        specific.appendChild(el);
    });
}
</script>
