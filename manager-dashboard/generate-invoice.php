<?php
require_once 'vendor/autoload.php';
require_once 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid shipment ID.");
}

$id = (int) $_GET['id'];
$sql = "SELECT s.*, u.name AS driver_name 
        FROM shipments s 
        LEFT JOIN users u ON s.driver_id = u.national_id 
        WHERE s.id = ? LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("❌ Shipment not found.");
}

$data = $result->fetch_assoc();

// Prepare values
$invoice_no = $data['shipment_number'];
$customer = $data['customer_source'];
$driver = $data['driver_name'];
$vehicle = $data['vehicle_reg'];
$weight = $data['net_weight'];
$rate = $data['rate_per_tonne'];
$distance = $data['distance_km'];
$amount = $data['amount'];
$vat = $data['vat_percent'];
$date = $data['pickup_date'];
$route = $data['from_location'] . ' → ' . $data['to_location'];

// === PDF BEGIN ===
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Stepstar Logistics');
$pdf->SetTitle("Invoice #$invoice_no");
$pdf->SetHeaderData('', 0, 'Stepstar Logistics Limited', "Invoice #: $invoice_no\nDate: $date");
$pdf->setHeaderFont(['helvetica', '', 12]);
$pdf->setFooterFont(['helvetica', '', 10]);
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

// === INVOICE BODY ===
$html = <<<EOD
<style>
    h2 { color: #2c3e50; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #ccc; padding: 8px; }
    th { background-color: #f0f0f0; }
</style>

<h2>📄 Delivery Invoice</h2>
<p><strong>Customer:</strong> $customer<br>
<strong>Driver:</strong> $driver<br>
<strong>Vehicle:</strong> $vehicle<br>
<strong>Route:</strong> $route<br>
<strong>Pickup Date:</strong> $date</p>

<table>
    <thead>
        <tr>
            <th>Description</th>
            <th>Rate per Tonne (KES)</th>
            <th>Weight (T)</th>
            <th>Distance (KM)</th>
            <th>VAT (%)</th>
            <th>Total (KES)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Transport Charge</td>
            <td align="right">{$rate}</td>
            <td align="right">{$weight}</td>
            <td align="right">{$distance}</td>
            <td align="right">{$vat}</td>
            <td align="right">" . number_format($amount, 2) . "</td>
        </tr>
    </tbody>
</table>

<p style="margin-top: 40px;"><strong>Approved by (Supervisor Signature): ____________________________</strong></p>
EOD;

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("invoice_$invoice_no.pdf", 'I'); // I = Inline view
