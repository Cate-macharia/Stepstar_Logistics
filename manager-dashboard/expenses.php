<?php
include '../includes/db.php';

$success = $error = "";
$vehicle_id = $_GET['vehicle_id'] ?? null;

// Handle addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);

    if ($vehicle_id && $description && $amount > 0) {
        $stmt = $conn->prepare("INSERT INTO expenses (vehicle_id, description, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $vehicle_id, $description, $amount);
        if ($stmt->execute()) {
            $success = "✅ Expense recorded.";
        } else {
            $error = "❌ Failed to add expense: " . $stmt->error;
        }
    } else {
        $error = "❌ All fields required.";
    }
}

// Fetch expenses for the vehicle
$expenses = null;
if ($vehicle_id) {
    $query = "SELECT * FROM expenses WHERE vehicle_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("SQL Prepare Failed: " . $conn->error . " | Query: $query");
    }
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $expenses = $stmt->get_result();
}
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
</style>

<div class="main-content">
    <div class="dashboard-header">
        <div class="user-info">💸 Expenses Management</div>
    </div>
    <div class="content-area">
        <h1>➕ Add Vehicle Expense</h1>

        <?php if ($success): ?><p class="success" id="messageBox"><?php echo $success; ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error" id="messageBox"><?php echo $error; ?></p><?php endif; ?>

        <form method="POST" class="expense-form">
            <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($vehicle_id); ?>">
            <input type="text" name="description" placeholder="Description" required>
            <input type="number" name="amount" placeholder="Amount (KES)" step="0.01" required>
            <button type="submit" name="add_expense">Add Expense</button>
        </form>

        <h2>📋 Expense Records</h2>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($expenses && $expenses->num_rows > 0): ?>
                    <?php $i = 1; while ($exp = $expenses->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($exp['description']); ?></td>
                            <td>KES <?php echo number_format($exp['amount'], 2); ?></td>
                            <td><?php echo $exp['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No expenses recorded for this vehicle.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
setTimeout(() => {
    const box = document.getElementById("messageBox");
    if (box) box.style.opacity = 0;
}, 3000);
</script>
