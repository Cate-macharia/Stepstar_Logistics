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

// Fetch registered vehicles for dropdown
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

        // Generate PDF Invoice using mPDF
        $mpdf = new \Mpdf\Mpdf();
        $invoiceHTML = "<h2 style='color:#2e6c80;'>Stepstar Logistics Invoice</h2>
            <strong>Driver:</strong> $driver_name (ID: $driver_id)<br>
            <strong>Shipment No:</strong> $shipment_number<br>
            <strong>Customer:</strong> $customer_source<br>
            <strong>Date:</strong> $shipment_date<br>
            <strong>From:</strong> $from_location<br>
            <strong>To:</strong> $to_location<br>
            <strong>Vehicle:</strong> $vehicle_reg<br>
            <strong>Net Weight:</strong> $net_weight tonnes<br>
            <strong>Rate/Tonne:</strong> KES $base_rate<br>
            <strong>VAT (16%):</strong> Included<br>
            <strong>Total Amount:</strong> KES " . number_format($amount, 2) . "<br><hr>
            <small>Generated on " . date('Y-m-d H:i') . "</small>";

        $mpdf->WriteHTML($invoiceHTML);
        $pdfPath = __DIR__ . "/invoices/invoice_{$shipment_number}.pdf";
        $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);
    }

    $success = true;
}
?>

<h2>📦 Record New Deliveries for <?php echo htmlspecialchars($driver_name); ?> (ID: <?php echo htmlspecialchars($driver_id); ?>)</h2>

<?php if ($success): ?>
    <p style="color:green;">✅ Deliveries recorded successfully!</p>
    <a href="dashboard-driver.php" style="text-decoration:none; background:#6c757d; color:white; padding:8px 12px; border-radius:5px; display:inline-block;">⬅️ Back to Dashboard</a>
<?php else: ?>
    <form method="POST" id="deliveryForm">
        <div id="deliveries">
            <div class="delivery-entry">
                <h4>Delivery #1</h4>
                Customer Source: <input type="text" name="customer_source[]" required><br>
                Date: <input type="date" name="shipment_date[]" required><br>
                Shipment Number: <input type="text" name="shipment_number[]" required><br>
                From: <input type="text" name="from_location[]" class="from" required>
                To: <input type="text" name="to_location[]" class="to" required>
                <button type="button" onclick="searchRate(this)">🔍 Search Rate</button><br>
                Vehicle Reg:
                <select name="vehicle_reg[]" required>
                    <option value="">-- Select Vehicle --</option>
                    <?= $vehicleOptions ?>
                </select><br>
                Net Weight (tonnes): <input type="number" step="0.01" name="net_weight[]" required><br>
                Distance (KM): <input type="text" name="distance_km[]" class="distance" placeholder="Enter if rate not found"><br><br>
            </div>
        </div>
        <button type="button" onclick="addEntry()">➕ Add Another Delivery</button><br><br>
        <button type="submit">📥 Submit Deliveries</button>
    </form>
<?php endif; ?>

<script>
function addEntry() {
    const div = document.createElement('div');
    div.classList.add('delivery-entry');
    div.innerHTML = `
        <hr><h4>New Delivery</h4>
        Customer Source: <input type="text" name="customer_source[]" required><br>
        Date: <input type="date" name="shipment_date[]" required><br>
        Shipment Number: <input type="text" name="shipment_number[]" required><br>
        From: <input type="text" name="from_location[]" class="from" required>
        To: <input type="text" name="to_location[]" class="to" required>
        <button type="button" onclick="searchRate(this)">🔍 Search Rate</button><br>
        Vehicle Reg:
        <select name="vehicle_reg[]" required>
            <option value="">-- Select Vehicle --</option>
            <?= $vehicleOptions ?>
        </select><br>
        Net Weight (tonnes): <input type="number" step="0.01" name="net_weight[]" required><br>
        Distance (KM): <input type="text" name="distance_km[]" class="distance" placeholder="Enter if rate not found"><br><br>
    `;
    document.getElementById('deliveries').appendChild(div);
}

function searchRate(button) {
    const parent = button.closest('.delivery-entry');
    const from = parent.querySelector('.from').value.trim().toUpperCase();
    const to = parent.querySelector('.to').value.trim().toUpperCase();
    const distanceInput = parent.querySelector('.distance');

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
