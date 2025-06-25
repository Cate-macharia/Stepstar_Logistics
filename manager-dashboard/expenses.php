<?php
include '../includes/db.php';

$success = $error = "";
$tenant_id = $_SESSION['user']['tenant_id']; // ✅ Ensure tenant context
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
    $vehicle_id = !empty($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : null;
    $driver_id  = !empty($_POST['driver_id'])  ? intval($_POST['driver_id']) : null;

    if ($amount > 0) {
        $stmt = $conn->prepare("INSERT INTO expenses (type, specific_type, description, amount, vehicle_id, driver_id, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddii", $type, $specific_type, $description, $amount, $vehicle_id, $driver_id, $tenant_id);
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

// Filtering
$whereParts = ["e.tenant_id = $tenant_id"]; // ✅ Always limit to tenant
if ($vehicle_id) {
    $whereParts[] = "vehicle_id = " . intval($vehicle_id);
}
if ($filter === 'today') {
    $whereParts[] = "DATE(e.created_at) = CURDATE()";
} elseif ($filter === 'week') {
    $whereParts[] = "YEARWEEK(e.created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $whereParts[] = "MONTH(e.created_at) = MONTH(CURDATE()) AND YEAR(e.created_at) = YEAR(CURDATE())";
} elseif ($filter === 'range' && $from && $to) {
    $whereParts[] = "DATE(e.created_at) BETWEEN '$from' AND '$to'";
}
$whereClause = implode(" AND ", $whereParts);

// Fetch expenses
$expenses = $conn->query("
    SELECT e.*, u.name AS driver_name, v.vehicle_reg
    FROM expenses e
    LEFT JOIN users u ON e.driver_id = u.national_id
    LEFT JOIN vehicles v ON e.vehicle_id = v.id
    WHERE $whereClause
    ORDER BY e.created_at DESC
");


// Total
$totalResult = $conn->query("SELECT SUM(amount) as total FROM expenses e WHERE $whereClause");
$totalRow = $totalResult->fetch_assoc();
$totalAmount = $totalRow['total'] ?? 0;

// Fetch tenant-specific vehicles and drivers
$vehicles = $conn->query("SELECT id, vehicle_reg FROM vehicles WHERE tenant_id = $tenant_id");
$drivers = $conn->query("SELECT id, name, national_id FROM users WHERE role='DRIVER' AND tenant_id = $tenant_id");
?>

<!-- FORM -->
<form method="POST" class="expense-form">
    <div class="form-grid">
        <div class="form-group">
            <label>Expense Type</label>
            <select name="expense_type" required onchange="toggleFields(this.value)">
                <option value="general" <?= $type === 'general' ? 'selected' : '' ?>>General</option>
                <option value="vehicle" <?= $type === 'vehicle' ? 'selected' : '' ?>>Vehicle</option>
                <option value="driver" <?= $type === 'driver' ? 'selected' : '' ?>>Driver</option>
            </select>
        </div>

        <div class="form-group" id="vehicleField" style="display:<?= $type === 'vehicle' ? 'block' : 'none' ?>">
            <label>Vehicle</label>
            <select name="vehicle_id">
                <option value="">Select Vehicle</option>
                <?php while($v = $vehicles->fetch_assoc()): ?>
                    <option value="<?= $v['id'] ?>" <?= ($vehicle_id == $v['id']) ? 'selected' : '' ?>><?= $v['vehicle_reg'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group" id="driverField" style="display:<?= $type === 'driver' ? 'block' : 'none' ?>">
            <label>Driver</label>
            <select name="driver_id">
                <option value="">Select Driver</option>
                <?php while($d = $drivers->fetch_assoc()): ?>
                    <option value="<?= $d['id'] ?>" <?= ($driver_id == $d['id']) ? 'selected' : '' ?>>
                        <?= $d['name'] ?> (ID: <?= $d['national_id'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Specific Type</label>
            <select name="specific_type" id="specificTypeField">
                <option value="">Select Specific Type</option>
            </select>
        </div>

        <div class="form-group">
            <label>Description (Optional)</label>
            <input type="text" name="description" placeholder="Narration or purpose">
        </div>

        <div class="form-group">
            <label>Amount</label>
            <input type="number" name="amount" step="0.01" required placeholder="KES">
        </div>

        <div class="form-group">
            <button type="submit" name="add_expense">➕ Add Expense</button>
        </div>
    </div>
</form>

<!-- TABLE -->
<h2>📋 Expense Records</h2>
<table class="styled-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Type</th>
            <th>Specific</th>
            <th>Description</th>
            <th>Vehicle</th>
            <th>Driver</th>
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
            <td><?= !empty($exp['vehicle_reg']) ? $exp['vehicle_reg'] : '-' ?></td>
            <td><?= htmlspecialchars($exp['driver_name'] ?? '-') ?></td>
            <td>KES <?= number_format($exp['amount'], 2) ?></td>
            <td><?= $exp['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if ($expenses->num_rows === 0): ?>
        <tr><td colspan="8" style="color:red; text-align:center;">⚠️ No expenses found.</td></tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" style="text-align:right;">Total:</th>
            <th colspan="2">KES <?= number_format($totalAmount, 2) ?></th>
        </tr>
    </tfoot>
</table>


<!-- Styling -->
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
        margin-top: 10px;#005f
    }

    .styled-table thead th {
        background-color: #0077cc;
        color: #ffffff;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid a3;
    }

    .styled-table tbody td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    .styled-table tfoot th {
        background-color:#0077cc;
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
    .expense-form {
        margin-bottom: 30px;
    }
    .form-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .form-group {
        flex: 1 1 200px;
        display: flex;
        flex-direction: column;
    }
    .form-group select,
    .form-group input {
        padding: 8px;
        margin-top: 4px;
        font-size: 14px;
    }
    .form-group button {
        padding: 10px 16px;
        background: #2980b9;
        color: white;
        border: none;
        margin-top: 28px;
        cursor: pointer;
        border-radius: 4px;
    }
    .form-group button:hover {
        background-color: #21689e;
    }
    .styled-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin-top: 20px;
    }
    .styled-table th, .styled-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #ccc;
        text-align: left;
    }
    .styled-table th {
        background-color: #f2f2f2;
    }
</style>

<!-- JavaScript -->
<script>
function toggleFields(type) {
    document.getElementById('vehicleField').style.display = (type === 'vehicle') ? 'block' : 'none';
    document.getElementById('driverField').style.display = (type === 'driver') ? 'block' : 'none';

    const specific = document.getElementById('specificTypeField');
    specific.innerHTML = '<option value="">Select Specific Type</option>';

    let options = [];
    if (type === 'vehicle') {
        options = ['Insurance', 'Sacco', 'Loan', 'Repairs', 'Tyre Exchange', 'Speed Governor', 'Inspection', 'KENHA', 'Fuel', 'Service', 'Brokerage Fee', 'Contingency Fee'];
    } else if (type === 'driver') {
        options = ['Other'];
    } else if (type === 'general') {
        options = ['Miscellaneous', 'Others'];
    }

    options.forEach(opt => {
        const el = document.createElement('option');
        el.value = opt;
        el.textContent = opt;
        specific.appendChild(el);
    });
}

// Initialize on load
window.addEventListener('DOMContentLoaded', () => {
    const initialType = document.querySelector("select[name='expense_type']").value;
    toggleFields(initialType);
});
</script>

