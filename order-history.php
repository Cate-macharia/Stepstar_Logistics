<?php
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'DRIVER') {
    header("Location: login.php");
    exit();
}

$driver_name = $_SESSION['user']['name'];
$driver_id = $_SESSION['user']['national_id'];
$success = false;
$filter = $_GET['filter'] ?? 'all';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$vehicles = $conn->query("SELECT vehicle_reg FROM vehicles ORDER BY vehicle_reg ASC");
$vehicleOptions = "";
while ($v = $vehicles->fetch_assoc()) {
    $vehicleOptions .= "<option value=\"{$v['vehicle_reg']}\">{$v['vehicle_reg']}</option>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $shipment_id = $_POST['shipment_id'];
    $new_status = $_POST['status'];

    $update_sql = "UPDATE shipments SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $shipment_id);
    mysqli_stmt_execute($stmt);
    echo "<p style='color:green;'>✅ Status updated to <strong>$new_status</strong>!</p>";
}

$whereClause = "WHERE driver_id = ?";
$params = [$driver_id];
$types = "s";

if ($filter === 'today') {
    $whereClause .= " AND DATE(pickup_date) = CURDATE()";
} elseif ($filter === 'week') {
    $whereClause .= " AND YEARWEEK(pickup_date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $whereClause .= " AND MONTH(pickup_date) = MONTH(CURDATE()) AND YEAR(pickup_date) = YEAR(CURDATE())";
} elseif ($filter === 'range' && $from_date && $to_date) {
    $whereClause .= " AND pickup_date BETWEEN ? AND ?";
    $params[] = $from_date;
    $params[] = $to_date;
    $types .= "ss";
}

$sql = "SELECT * FROM shipments $whereClause ORDER BY pickup_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<h2>📋 Order History - <?php echo htmlspecialchars($driver_name); ?></h2>

<form method="GET" action="dashboard-driver.php">
    <input type="hidden" name="page" value="order-history">

    <label>Filter by:</label>
    <select name="filter" onchange="toggleDateRange(this.value)">
        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
        <option value="today" <?= $filter === 'today' ? 'selected' : '' ?>>Today</option>
        <option value="week" <?= $filter === 'week' ? 'selected' : '' ?>>This Week</option>
        <option value="month" <?= $filter === 'month' ? 'selected' : '' ?>>This Month</option>
        <option value="range" <?= $filter === 'range' ? 'selected' : '' ?>>Date Range</option>
    </select>

    <div id="rangeInputs" style="<?= $filter === 'range' ? '' : 'display:none;' ?>">
        From: <input type="date" name="from_date" value="<?= $from_date ?>">
        To: <input type="date" name="to_date" value="<?= $to_date ?>">
    </div>

    <button type="submit">🔍 Apply Filter</button>
</form>

<hr>

<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background:rgb(32, 48, 113);">
            <th>Date</th>
            <th>Shipment No</th>
            <th>Route</th>
            <th>Vehicle</th>
            <th>Status</th>
            <th>Invoice</th>
            <th>Update Status</th>
        </tr>
    </thead>
    <tbody>
    <?php if (mysqli_num_rows($result) === 0): ?>
        <tr><td colspan="7" style="color:red; text-align:center;">⚠️ No deliveries found for selected filter.</td></tr>
    <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['pickup_date'] ?></td>
            <td><?= $row['shipment_number'] ?></td>
            <td><?= $row['from_location'] ?> → <?= $row['to_location'] ?></td>
            <td><?= $row['vehicle_reg'] ?? 'N/A' ?></td>
            <td><strong><?= $row['status'] ?></strong></td>
            <td>
                <?php 
                $invoicePath = "invoices/invoice_{$row['shipment_number']}.pdf";
                if (file_exists($invoicePath)) {
                    echo "<a href='$invoicePath' target='_blank'>📄 View</a>";
                } else {
                    echo "<span style='color:gray;'>Not Generated</span>";
                }
                ?>
            </td>
            <td>
                <form method="POST" onsubmit="return confirmStatusChange('<?= $row['status'] ?>', this.status.value);">
                    <input type="hidden" name="shipment_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="update_status" value="Pending" style="background-color:green;color:white;" onclick="this.form.status.value='Pending'">🟢</button>
                    <button type="submit" name="update_status" value="In Progress" style="background-color:gold;color:black;" onclick="this.form.status.value='In Progress'">🟡</button>
                    <button type="submit" name="update_status" value="Delivered" style="background-color:pink;color:black;" onclick="this.form.status.value='Delivered'">🌸</button>
                    <input type="hidden" name="status" value="">
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php endif; ?>
    </tbody>
</table>

<script>
function toggleDateRange(value) {
    document.getElementById('rangeInputs').style.display = value === 'range' ? 'inline-block' : 'none';
}

function confirmStatusChange(currentStatus, newStatus) {
    if (currentStatus === newStatus) {
        alert("🚫 This shipment is already marked as '" + newStatus + "'.");
        return false;
    }
    return confirm("Are you sure you want to mark this shipment as '" + newStatus + "'?");
}
</script>
