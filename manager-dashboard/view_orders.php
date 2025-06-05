<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: ../login.php");
    exit();
}

// Optional: date filter
$filter = $_GET['filter'] ?? 'all';
$from_date = $_GET['from'] ?? '';
$to_date = $_GET['to'] ?? '';

$whereClause = "1"; // always true
if ($filter === 'today') {
    $whereClause = "DATE(pickup_date) = CURDATE()";
} elseif ($filter === 'week') {
    $whereClause = "YEARWEEK(pickup_date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $whereClause = "MONTH(pickup_date) = MONTH(CURDATE()) AND YEAR(pickup_date) = YEAR(CURDATE())";
} elseif ($filter === 'range' && $from_date && $to_date) {
    $whereClause = "pickup_date BETWEEN '$from_date' AND '$to_date'";
}

$sql = "SELECT * FROM shipments WHERE $whereClause ORDER BY pickup_date DESC";
$result = mysqli_query($conn, $sql);
?>

<h2>📋 All Orders</h2>

<form method="GET">
    <label>Filter: </label>
    <select name="filter" onchange="toggleRange(this.value)">
        <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All</option>
        <option value="today" <?= $filter == 'today' ? 'selected' : '' ?>>Today</option>
        <option value="week" <?= $filter == 'week' ? 'selected' : '' ?>>This Week</option>
        <option value="month" <?= $filter == 'month' ? 'selected' : '' ?>>This Month</option>
        <option value="range" <?= $filter == 'range' ? 'selected' : '' ?>>Date Range</option>
    </select>

    <div id="range" style="<?= $filter == 'range' ? '' : 'display:none;' ?>">
        From: <input type="date" name="from" value="<?= $from_date ?>">
        To: <input type="date" name="to" value="<?= $to_date ?>">
    </div>

    <button type="submit">🔍 Apply</button>
</form>

<table border="1" cellpadding="8" cellspacing="0" style="margin-top: 20px; width: 100%;">
    <tr>
        <th>Date</th>
        <th>Shipment #</th>
        <th>Driver ID</th>
        <th>From → To</th>
        <th>Vehicle</th>
        <th>Status</th>
        <th>✅ Verified</th>
        <th>🛠 Edit</th>
        <th>🧾 Invoice</th>
    </tr>

    <?php if (mysqli_num_rows($result) == 0): ?>
        <tr><td colspan="9" style="color:red;">No orders found.</td></tr>
    <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['pickup_date'] ?></td>
            <td><?= $row['shipment_number'] ?></td>
            <td><?= $row['driver_id'] ?></td>
            <td><?= $row['from_location'] ?> → <?= $row['to_location'] ?></td>
            <td><?= $row['vehicle_reg'] ?? 'N/A' ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <form method="POST" action="verify_order.php" style="display:inline;">
                    <input type="hidden" name="shipment_id" value="<?= $row['id'] ?>">
                    <input type="checkbox" name="verified" onchange="this.form.submit()" />
                </form>
            </td>
            <td>
                <a href="edit_order.php?id=<?= $row['id'] ?>">✏️</a>
            </td>
            <td>
                <a href="generate_invoice.php?id=<?= $row['id'] ?>" target="_blank">📄</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php endif; ?>
</table>

<script>
function toggleRange(value) {
    document.getElementById('range').style.display = value === 'range' ? 'inline' : 'none';
}
</script>
