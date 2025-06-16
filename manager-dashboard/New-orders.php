<?php
include '../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: ../login.php");
    exit();
}

$manager_name = $_SESSION['user']['name'];
$success = false;

$vehicles = $conn->query("SELECT vehicle_reg FROM vehicles ORDER BY vehicle_reg ASC");
$vehicleOptions = "";
while ($v = $vehicles->fetch_assoc()) {
    $vehicleOptions .= "<option value=\"{$v['vehicle_reg']}\">{$v['vehicle_reg']}</option>";
}

$drivers = $conn->query("SELECT name, national_id FROM users WHERE role = 'DRIVER' ORDER BY name ASC");
$driverOptions = "";
while ($d = $drivers->fetch_assoc()) {
    $driverOptions .= "<option value=\"{$d['national_id']}\">{$d['name']}</option>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < count($_POST['shipment_number']); $i++) {
        $driver_id = $_POST['driver_id'][$i];
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

        $stmt = $conn->prepare("INSERT INTO shipments (
            driver_id, shipment_number, customer_source, pickup_date,
            from_location, to_location, vehicle_reg, net_weight,
            distance_km, rate_per_tonne, vat_percent, amount, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssssssddddds",
            $driver_id, $shipment_number, $customer_source, $shipment_date,
            $from_location, $to_location, $vehicle_reg, $net_weight,
            $distance_km, $base_rate, $vat_percent, $amount, $status
        );

        $stmt->execute();

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->SetTitle("Invoice #$shipment_number");
        $mpdf->SetMargins(10, 10, 10);

        $invoiceHTML = "<div style='padding: 30px; font-family:sans-serif;'>
            <h2 style='color:#2c3e50;'>INVOICE</h2>
            <p><strong>Shipment No:</strong> $shipment_number</p>
            <p><strong>Customer:</strong> $customer_source</p>
            <p><strong>Date:</strong> $shipment_date</p>
            <p><strong>From:</strong> $from_location → $to_location</p>
            <p><strong>Vehicle:</strong> $vehicle_reg</p>
            <p><strong>Weight:</strong> $net_weight tonnes</p>
            <p><strong>Rate:</strong> KES $base_rate</p>
            <p><strong>Total:</strong> KES " . number_format($amount, 2) . "</p>
        </div>";

        if (!is_dir(__DIR__ . '/../invoices')) {
            mkdir(__DIR__ . '/../invoices', 0777, true);
        }

        $pdfPath = __DIR__ . "/../invoices/invoice_{$shipment_number}.pdf";
        $mpdf->WriteHTML($invoiceHTML);
        $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);
    }
    $success = true;
}
?>

<h2>📝 Create Delivery Orders - Manager</h2>

<?php if ($success): ?>
    <p style="color:green;">✅ Orders placed and invoices generated!</p>
    <a href="dashboard-manager.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
<?php else: ?>
<form method="POST" id="deliveryForm">
    <div id="deliveries">
        <div class="delivery-entry">
            <label>Driver:</label>
            <select name="driver_id[]" required onchange="fillDriverID(this)">
                <option value="">-- Select Driver --</option>
                <?= $driverOptions ?>
            </select><br>
            <label>Customer:</label> <input type="text" name="customer_source[]" required><br>
            <label>Date:</label> <input type="date" name="shipment_date[]" required><br>
            <label>Shipment No:</label> <input type="text" name="shipment_number[]" required><br>
            <label>From:</label> <input type="text" name="from_location[]" class="from" required>
            <label>To:</label> <input type="text" name="to_location[]" class="to" required>
            <button type="button" onclick="searchRate(this)">🔍</button><br>
            <label>Vehicle:</label>
            <select name="vehicle_reg[]" required>
                <option value="">-- Select Vehicle --</option>
                <?= $vehicleOptions ?>
            </select><br>
            <label>Weight (T):</label> <input type="number" step="0.01" name="net_weight[]" required><br>
            <label>Distance (KM):</label> <input type="text" name="distance_km[]" class="distance"><br>
        </div>
    </div>
    <br>
    <button type="button" onclick="addEntry()">➕ Add Delivery</button><br><br>
    <button type="submit">📥 Submit</button>
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
    fetch('../search-rate.php?from=' + from + '&to=' + to)
        .then(res => res.json())
        .then(data => {
            if (data.found) {
                distanceInput.value = data.distance;
                alert('✅ Rate found and distance filled.');
            } else {
                alert('❌ Rate not found. Enter distance manually.');
            }
        })
        .catch(() => alert('❌ Error searching.'));
}
</script>
