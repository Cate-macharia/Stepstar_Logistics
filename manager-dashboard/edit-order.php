<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid shipment ID.");
}

$id = (int) $_GET['id'];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipment_number = $_POST['shipment_number'];
    $customer_source = $_POST['customer_source'];
    $pickup_date = $_POST['pickup_date'];
    $from_location = strtoupper(trim($_POST['from_location']));
    $to_location = strtoupper(trim($_POST['to_location']));
    $vehicle_reg = $_POST['vehicle_reg'];
    $net_weight = $_POST['net_weight'];
    $distance_km = $_POST['distance_km'];
    $rate_per_tonne = $_POST['rate_per_tonne'];
    $vat_percent = $_POST['vat_percent'];
    $status = $_POST['status'];

    $amount = $net_weight * $rate_per_tonne * (1 + $vat_percent / 100);

    $sql = "UPDATE shipments SET shipment_number=?, customer_source=?, pickup_date=?, from_location=?, to_location=?, vehicle_reg=?, net_weight=?, distance_km=?, rate_per_tonne=?, vat_percent=?, amount=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssddddssi", $shipment_number, $customer_source, $pickup_date, $from_location, $to_location, $vehicle_reg, $net_weight, $distance_km, $rate_per_tonne, $vat_percent, $amount, $status, $id);
    
    if ($stmt->execute()) {
        $success = true;
    }
}

// Fetch current shipment details
$stmt = $conn->prepare("SELECT * FROM shipments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$shipment = $stmt->get_result()->fetch_assoc();

if (!$shipment) {
    die("❌ Shipment not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Shipment</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>🚚 Stepstar Logistics</h2>
        <ul>
            <li><a href="dashboard-manager.php">🏠 Dashboard</a></li>
            <li><a href="view_orders.php">📄 View Orders</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="dashboard-header">
            <div class="user-info">
                Editing Shipment #<?php echo htmlspecialchars($shipment['shipment_number']); ?>
            </div>
        </div>

        <div class="content-area">
            <h2>✏️ Edit Shipment Details</h2>
            <?php if ($success): ?>
                <p style="color:green;">✅ Shipment updated successfully.</p>
                <a href="view_orders.php" class="btn">⬅️ Back to Orders</a>
            <?php endif; ?>

            <form method="POST">
                Shipment No: <input type="text" name="shipment_number" value="<?php echo $shipment['shipment_number']; ?>" required><br><br>
                Customer: <input type="text" name="customer_source" value="<?php echo $shipment['customer_source']; ?>" required><br><br>
                Pickup Date: <input type="date" name="pickup_date" value="<?php echo $shipment['pickup_date']; ?>" required><br><br>
                From: <input type="text" name="from_location" value="<?php echo $shipment['from_location']; ?>" required>
                To: <input type="text" name="to_location" value="<?php echo $shipment['to_location']; ?>" required><br><br>
                Vehicle Reg: <input type="text" name="vehicle_reg" value="<?php echo $shipment['vehicle_reg']; ?>"><br><br>
                Weight (T): <input type="number" name="net_weight" step="0.01" value="<?php echo $shipment['net_weight']; ?>" required><br><br>
                Distance (KM): <input type="number" name="distance_km" step="0.01" value="<?php echo $shipment['distance_km']; ?>"><br><br>
                Rate per Tonne: <input type="number" name="rate_per_tonne" step="0.01" value="<?php echo $shipment['rate_per_tonne']; ?>"><br><br>
                VAT (%): <input type="number" name="vat_percent" step="0.01" value="<?php echo $shipment['vat_percent']; ?>"><br><br>
                Status:
                <select name="status">
                    <option <?php if ($shipment['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option <?php if ($shipment['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                    <option <?php if ($shipment['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                </select><br><br>
                <button type="submit">💾 Save Changes</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
