<?php
include '../includes/db.php';

if (!isset($_GET['driver_id']) || !is_numeric($_GET['driver_id'])) {
    echo "<p style='color:red;'>⚠️ Driver ID missing or invalid.</p>";
    return;
}

$driver_id = (int)$_GET['driver_id'];

// Fetch driver info
$driver_stmt = $conn->prepare("SELECT name, email, national_id, fixed_salary FROM users WHERE id = ? AND role = 'DRIVER'");
$driver_stmt->bind_param("i", $driver_id);
$driver_stmt->execute();
$driver_result = $driver_stmt->get_result();
$driver = $driver_result->fetch_assoc();

if (!$driver) {
    echo "<p style='color:red;'>❌ Driver not found.</p>";
    return;
}

// Count deliveries
$count_stmt = $conn->prepare("SELECT COUNT(*) AS trips FROM shipments WHERE driver_id = ?");
$count_stmt->bind_param("s", $driver['national_id']);
$count_stmt->execute();
$trip_result = $count_stmt->get_result();
$trip_data = $trip_result->fetch_assoc();
$trip_count = (int)$trip_data['trips'];

$millage_fee = 1000; // fixed per trip
$total_millage = $trip_count * $millage_fee;
$fixed_salary = $driver['fixed_salary'] ?? 0;
$total_pay = $fixed_salary + $total_millage;
?>

<h2>💰 Driver Salary Summary</h2>

<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <tr><th>Name</th><td><?= htmlspecialchars($driver['name']) ?></td></tr>
    <tr><th>Email</th><td><?= htmlspecialchars($driver['email']) ?></td></tr>
    <tr><th>National ID</th><td><?= htmlspecialchars($driver['national_id']) ?></td></tr>
    <tr><th>Fixed Salary (KES)</th><td><?= number_format($fixed_salary, 2) ?></td></tr>
    <tr><th>Total Trips</th><td><?= $trip_count ?></td></tr>
    <tr><th>Millage Fee (KES 1,000 × Trips)</th><td><?= number_format($total_millage, 2) ?></td></tr>
    <tr style="background:rgb(6, 44, 89);"><th>Total Pay</th><td><strong>KES <?= number_format($total_pay, 2) ?></strong></td></tr>
</table>

<br>
<a href="dashboard-manager.php?page=manage-drivers" class="btn btn-secondary">⬅️ Back to Drivers</a>
