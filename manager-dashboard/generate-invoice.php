<?php
include '../includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) die("❌ Invalid shipment ID.");

$query = $conn->query("SELECT * FROM shipments WHERE id = $id");
$order = $query->fetch_assoc();

if (!$order) die("❌ Shipment not found.");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= $order['shipment_number'] ?></title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .invoice-box { max-width: 800px; margin: auto; border: 1px solid #eee; padding: 30px; }
        h2 { text-align: center; }
        table { width: 100%; line-height: 1.6; border-collapse: collapse; }
        td, th { padding: 8px; border: 1px solid #ccc; }
        .total { font-weight: bold; }
        .print-btn { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>

<div class="invoice-box">
    <h2>📄 Shipment Invoice</h2>
    <table>
        <tr><th>Shipment #</th><td><?= $order['shipment_number'] ?></td></tr>
        <tr><th>Date</th><td><?= $order['pickup_date'] ?></td></tr>
        <tr><th>Driver ID</th><td><?= $order['driver_id'] ?></td></tr>
        <tr><th>Customer</th><td><?= $order['customer_source'] ?></td></tr>
        <tr><th>Route</th><td><?= $order['from_location'] ?> → <?= $order['to_location'] ?></td></tr>
        <tr><th>Vehicle</th><td><?= $order['vehicle_reg'] ?></td></tr>
        <tr><th>Distance (KM)</th><td><?= $order['distance_km'] ?></td></tr>
        <tr><th>Weight (tonnes)</th><td><?= $order['net_weight'] ?></td></tr>
        <tr><th>Rate/Tonne</th><td><?= number_format($order['rate_per_tonne'], 2) ?></td></tr>
        <tr><th>VAT (%)</th><td><?= $order['vat_percent'] ?></td></tr>
        <tr class="total"><th>Total Amount</th><td>KES <?= number_format($order['amount'], 2) ?></td></tr>
        <tr><th>Status</th><td><?= $order['status'] ?></td></tr>
    </table>

    <div class="print-btn">
        <button onclick="window.print()">🖨️ Print Invoice</button>
    </div>
</div>

</body>
</html>
