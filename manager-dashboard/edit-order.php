<?php

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    die("❌ No order ID provided.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipment_number = $_POST['shipment_number'];
    $customer_source = $_POST['customer_source'];
    $pickup_date = $_POST['pickup_date'];
    $from = $_POST['from_location'];
    $to = $_POST['to_location'];
    $vehicle = $_POST['vehicle_reg'];
    $weight = floatval($_POST['net_weight']);
    $distance = floatval($_POST['distance_km']);
    $rate = floatval($_POST['rate_per_tonne']);
    $vat = floatval($_POST['vat_percent']);
    $status = $_POST['status'];
    $amount = $weight * ($rate * (1 + $vat / 100));

    $stmt = $conn->prepare("UPDATE shipments SET shipment_number=?, customer_source=?, pickup_date=?, from_location=?, to_location=?, vehicle_reg=?, net_weight=?, distance_km=?, rate_per_tonne=?, vat_percent=?, amount=?, status=? WHERE id=?");
    $stmt->bind_param("ssssssdddddsi", $shipment_number, $customer_source, $pickup_date, $from, $to, $vehicle, $weight, $distance, $rate, $vat, $amount, $status, $order_id);
    $stmt->execute();

    echo "<p style='color:green;'>✅ Order updated successfully.</p>";
    echo "<a href='dashboard-manager.php?page=view_orders'>⬅️ Back to Orders</a>";
    exit;
}

$query = $conn->query("SELECT * FROM shipments WHERE id = $order_id");
$data = $query->fetch_assoc();
?>

<h2>✏️ Edit Order</h2>
<form method="POST">
    Shipment #: <input type="text" name="shipment_number" value="<?= $data['shipment_number'] ?>" required><br>
    Customer Source: <input type="text" name="customer_source" value="<?= $data['customer_source'] ?>" required><br>
    Pickup Date: <input type="date" name="pickup_date" value="<?= $data['pickup_date'] ?>" required><br>
    From: <input type="text" name="from_location" value="<?= $data['from_location'] ?>" required><br>
    To: <input type="text" name="to_location" value="<?= $data['to_location'] ?>" required><br>
    Vehicle: <input type="text" name="vehicle_reg" value="<?= $data['vehicle_reg'] ?>"><br>
    Net Weight: <input type="number" step="0.01" name="net_weight" value="<?= $data['net_weight'] ?>" required><br>
    Distance KM: <input type="text" name="distance_km" value="<?= $data['distance_km'] ?>" required><br>
    Rate/Tonne: <input type="number" step="0.01" name="rate_per_tonne" value="<?= $data['rate_per_tonne'] ?>" required><br>
    VAT (%): <input type="number" step="0.01" name="vat_percent" value="<?= $data['vat_percent'] ?>" required><br>
    Status:
    <select name="status">
        <option value="Pending" <?= $data['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
        <option value="Delivered" <?= $data['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
        <option value="Verified" <?= $data['status'] === 'Verified' ? 'selected' : '' ?>>Verified</option>
    </select><br><br>
    <button type="submit">💾 Save Changes</button>
</form>
