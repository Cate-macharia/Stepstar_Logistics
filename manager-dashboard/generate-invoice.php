<?php
session_start();
include '../includes/db.php';

$shipment_id = $_GET['id'] ?? null;
$tenant_id = $_SESSION['user']['tenant_id'] ?? null;

// Get tenant info
$tenantRes = $conn->query("SELECT * FROM tenants WHERE id = $tenant_id");
$tenant = $tenantRes->fetch_assoc();
$business_name = $tenant['business_name'] ?? 'Your Business';
$logo = $tenant['logo_url'] ?? '../images/default-logo.png';
$phone = $tenant['phone_number'] ?? '';
$address = $tenant['address'] ?? '';
$vat_number = $tenant['vat_number'] ?? '';
$footer_note = $tenant['invoice_footer'] ?? '';

if (!$shipment_id) die("❌ Invalid shipment ID.");

$query = $conn->query("SELECT * FROM shipments WHERE id = $shipment_id");
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
        h2, h3 { text-align: center; }
        table { width: 100%; line-height: 1.6; border-collapse: collapse; margin-top: 20px; }
        td, th { padding: 8px; border: 1px solid #ccc; }
        .total { font-weight: bold; }
        .print-btn { margin-top: 20px; text-align: center; }
        .branding { text-align: center; margin-bottom: 20px; }
        .branding img { max-height: 80px; margin-bottom: 10px; }
        .footer { text-align: center; font-size: 13px; color: #555; margin-top: 30px; border-top: 1px solid #ccc; padding-top: 15px; }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="branding">
        <img src="<?= $logo ?>" alt="Logo"><br>
        <h2><?= $business_name ?></h2>
        <p><?= $address ?><br>📞 <?= $phone ?><br>VAT No: <?= $vat_number ?></p>
    </div>

    <h3>📄 Shipment Invoice</h3>
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

    <div class="footer">
        <?= nl2br($footer_note) ?>
    </div>

    <div class="print-btn">
        <button onclick="window.print()">🖨️ Print Invoice</button>
    </div>
</div>

</body>
</html>
