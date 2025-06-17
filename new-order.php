<?php
include 'includes/db.php';
require_once __DIR__ . '/vendor/autoload.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'DRIVER') {
    header("Location: login.php");
    exit();
}

$driver_name = $_SESSION['user']['name'];
$driver_id = $_SESSION['user']['national_id'];
$success = false;

$vehicles = $conn->query("SELECT vehicle_reg FROM vehicles ORDER BY vehicle_reg ASC");
$vehicleOptions = "";
while ($v = $vehicles->fetch_assoc()) {
    $vehicleOptions .= "<option value=\"{$v['vehicle_reg']}\">{$v['vehicle_reg']}</option>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < count($_POST['shipment_number']); $i++) {
        $shipment_number = $_POST['shipment_number'][$i];
        $customer_source = $_POST['customer_source'][$i];
        $shipment_date = $_POST['shipment_date'][$i];
        $from_location = strtoupper(trim($_POST['from_location'][$i]));
        $to_location = strtoupper(trim($_POST['to_location'][$i]));
        $vehicle_reg = $_POST['vehicle_reg'][$i];
        $net_weight = (float) $_POST['net_weight'][$i];
        $distance_km = trim($_POST['distance_km'][$i]);

        $base_rate = 0;
        $vat_percent = 16.00;

        $route = "$from_location - $to_location";
        $rate_sql = "SELECT base_rate FROM manual_rates WHERE CONCAT(from_location, ' - ', to_location) = ? LIMIT 1";
        $rate_stmt = $conn->prepare($rate_sql);

        if (!$rate_stmt) {
            die("❌ Query prepare failed: " . $conn->error);
        }

        $rate_stmt->bind_param("s", $route);
        $rate_stmt->execute();
        $rate_result = $rate_stmt->get_result();
        $rate_data = $rate_result->fetch_assoc();

        if ($rate_data) {
            $base_rate = $rate_data['base_rate'];
        } else {
            if (empty($distance_km)) {
                die("❌ Distance is required when rate is not found.");
            }

            $distance_value = (float) $distance_km;
            if ($distance_value <= 15) $base_rate = 728.75;
            elseif ($distance_value <= 29) $base_rate = 726.43;
            elseif ($distance_value <= 49) $base_rate = 725.00;
            elseif ($distance_value <= 69) $base_rate = 723.50;
            elseif ($distance_value <= 89) $base_rate = 722.00;
            elseif ($distance_value <= 109) $base_rate = 720.50;
            elseif ($distance_value <= 129) $base_rate = 719.00;
            elseif ($distance_value <= 149) $base_rate = 717.50;
            elseif ($distance_value <= 169) $base_rate = 716.00;
            elseif ($distance_value <= 189) $base_rate = 714.50;
            else $base_rate = 713.00;
        }

        $rate_with_vat = $base_rate * (1 + $vat_percent / 100);
        $amount = $net_weight * $rate_with_vat;
        $status = 'Pending';

        $sql = "INSERT INTO shipments (
                    driver_id, shipment_number, customer_source, pickup_date,
                    from_location, to_location, vehicle_reg, net_weight,
                    distance_km, rate_per_tonne, vat_percent, amount, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("❌ Insert failed: " . $conn->error);
        }

        $stmt->bind_param("sssssssddddds",
            $driver_id, $shipment_number, $customer_source, $shipment_date,
            $from_location, $to_location, $vehicle_reg, $net_weight,
            $distance_km, $base_rate, $vat_percent, $amount, $status
        );

        $stmt->execute();

       $mpdf = new \Mpdf\Mpdf(['default_font' => 'sans-serif']);
$mpdf->SetTitle("Invoice #$shipment_number");
$mpdf->SetMargins(10, 10, 10);

// VAT and Total calculations
$vat_amount = $amount - ($amount / 1.16);
$amount_ex_vat = $amount - $vat_amount;
$total = $amount;

// HTML Invoice
$invoiceHTML = "
<div style='font-family: Arial, sans-serif; font-size:13px;'>
    <table width='100%' style='border-bottom:2px solid #005baa;'>
        <tr>
            <td><img src='" . __DIR__ . "/images/LOGISTICS LOGO-1.png' height='80'></td>
            <td align='right'>
                <h2 style='margin:0;color:#005baa;'>STEPSTAR LOGISTICS LTD</h2>
                <p style='margin:2px;'>P.O BOX 515-20106, NAKURU - KENYA</p>
                <p style='margin:2px;'>+254 710 987 658 | smart360movers@gmail.com</p>
            </td>
        </tr>
    </table>

    <h3 style='text-align:center; margin-top:15px;'>INVOICE</h3>

    <table width='100%' style='margin-top:20px;' cellpadding='5'>
        <tr>
            <td><strong>Invoice No:</strong> {$shipment_number}</td>
            <td><strong>Date:</strong> " . date('Y-m-d') . "</td>
        </tr>
        <tr>
            <td><strong>Customer:</strong> " . htmlspecialchars($customer_source) . "</td>
            <td><strong>Driver:</strong> " . htmlspecialchars($driver_name) . " (ID: {$driver_id})</td>
        </tr>
        <tr>
            <td><strong>From:</strong> {$from_location}</td>
            <td><strong>To:</strong> {$to_location}</td>
        </tr>
        <tr>
            <td><strong>Vehicle Reg:</strong> {$vehicle_reg}</td>
            <td><strong>Distance:</strong> {$distance_km} KM</td>
        </tr>
    </table>

    <table width='100%' border='1' cellspacing='0' cellpadding='6' style='margin-top:20px; border-collapse: collapse; font-size:13px;'>
        <thead style='background:#f2f2f2;'>
            <tr>
                <th>Description</th>
                <th>Net Weight (Tonnes)</th>
                <th>Rate/Tonne (KES)</th>
                <th>Amount (KES)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{$from_location} → {$to_location}</td>
                <td>{$net_weight}</td>
                <td>" . number_format($base_rate, 2) . "</td>
                <td>" . number_format($amount_ex_vat, 2) . "</td>
            </tr>
        </tbody>
    </table>

    <table width='100%' style='margin-top:15px; font-size:14px;'>
        <tr>
            <td width='75%' align='right'><strong>Amount:</strong></td>
            <td align='right'>KES " . number_format($amount_ex_vat, 2) . "</td>
        </tr>
        <tr>
            <td align='right'><strong>VAT (16%):</strong></td>
            <td align='right'>KES " . number_format($vat_amount, 2) . "</td>
        </tr>
        <tr>
            <td align='right'><strong>Total:</strong></td>
            <td align='right'><strong>KES " . number_format($total, 2) . "</strong></td>
        </tr>
    </table>

    <p style='text-align:center; margin-top:40px;'>Thank you for doing business with Stepstar Logistics Ltd.</p>
</div>";

// Ensure invoices directory exists
$invoiceDir = __DIR__ . "/invoices";
if (!is_dir($invoiceDir)) {
    mkdir($invoiceDir, 0777, true);
}

// Save the PDF
$pdfPath = $invoiceDir . "/invoice_{$shipment_number}.pdf";
$mpdf->WriteHTML($invoiceHTML);
$mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

    }

    $success = true;
}
?>

<h2>📦 Record New Deliveries for <?= htmlspecialchars($driver_name) ?> (ID: <?= htmlspecialchars($driver_id) ?>)</h2>

<?php if ($success): ?>
    <p style="color:green;">✅ Deliveries recorded and invoice generated successfully!</p>
    <a href="dashboard-driver.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
<?php else: ?>
    <form method="POST" id="deliveryForm">
        <div id="deliveries">
            <div class="delivery-entry" style="border:1px solid #ccc; padding:20px; border-radius:10px; margin-bottom:20px;">
                <h4 style="margin-top:0;">Delivery #1</h4>
                <label>Customer Source:</label> <input type="text" name="customer_source[]" required><br>
                <label>Shipment Date:</label> <input type="date" name="shipment_date[]" required><br>
                <label>Shipment Number:</label> <input type="text" name="shipment_number[]" required><br>
                <label>From:</label> <input type="text" name="from_location[]" class="from" required>
                <label>To:</label> <input type="text" name="to_location[]" class="to" required>
                <button type="button" onclick="searchRate(this)">🔍 Search Rate</button><br>
                <label>Vehicle Reg:</label>
                <select name="vehicle_reg[]" required>
                    <option value="">-- Select Vehicle --</option>
                    <?= $vehicleOptions ?>
                </select><br>
                <label>Net Weight (tonnes):</label> <input type="number" step="0.01" name="net_weight[]" required><br>
                <label>Distance (KM):</label> <input type="text" name="distance_km[]" class="distance" placeholder="Optional if rate found"><br>
            </div>
        </div>
        <button type="button" onclick="addEntry()">➕ Add Another Delivery</button><br><br>
        <button type="submit">📥 Submit Deliveries</button>
    </form>
<?php endif; ?>

<script>
function addEntry() {
    const base = document.querySelector('.delivery-entry');
    const clone = base.cloneNode(true);
    document.getElementById('deliveries').appendChild(clone);
}
function searchRate(button) {
    const entry = button.closest('.delivery-entry');
    const from = entry.querySelector('.from').value.trim().toUpperCase();
    const to = entry.querySelector('.to').value.trim().toUpperCase();
    const distanceInput = entry.querySelector('.distance');

    fetch('search-rate.php?from=' + from + '&to=' + to)
        .then(res => res.json())
        .then(data => {
            if (data.found) {
                distanceInput.value = data.distance;
                alert('✅ Special route found and distance filled.');
            } else {
                alert('❌ No rate found. Please enter distance manually.');
            }
        })
        .catch(() => alert('❌ Error occurred while searching.'));
}
</script>
