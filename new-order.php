<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'DRIVER') {
    header("Location: login.php");
    exit();
}

$driver_name = $_SESSION['user']['name'];
$driver_id = $_SESSION['user']['national_id'];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < count($_POST['shipment_number']); $i++) {
        $shipment_number = $_POST['shipment_number'][$i];
        $customer_source = $_POST['customer_source'][$i];
        $shipment_date = $_POST['shipment_date'][$i];
        $from_location = $_POST['from_location'][$i];
        $to_location = $_POST['to_location'][$i];
        $vehicle_reg = $_POST['vehicle_reg'][$i];
        $net_weight = $_POST['net_weight'][$i];
        $distance_km = isset($_POST['distance_km'][$i]) ? $_POST['distance_km'][$i] : null;

        // Initialize base rate and VAT
        $base_rate = 0;
        $vat_percent = 16.00;

        // Check if rate exists in the database
        $rate_sql = "SELECT base_rate, vat_percent FROM rates WHERE from_location = ? AND to_location = ? LIMIT 1";
        $rate_stmt = mysqli_prepare($conn, $rate_sql);
        mysqli_stmt_bind_param($rate_stmt, "ss", $from_location, $to_location);
        mysqli_stmt_execute($rate_stmt);
        $rate_result = mysqli_stmt_get_result($rate_stmt);
        $rate_data = mysqli_fetch_assoc($rate_result);

        if ($rate_data) {
            // Use existing rate from the database
            $base_rate = $rate_data['base_rate'];
            $vat_percent = $rate_data['vat_percent'];
            $rate_with_vat = $base_rate * (1 + $vat_percent / 100);
        } else {
            // If rate not found, ensure distance is provided
            if (empty($distance_km)) {
                die("Distance is required when rate is not found in the database.");
            }

            // Determine base rate per tonne based on distance
            if ($distance_km <= 15) {
                $base_rate = 728.75;
            } elseif ($distance_km <= 29) {
                $base_rate = 726.43;
            } elseif ($distance_km <= 49) {
                $base_rate = 725.00;
            } elseif ($distance_km <= 69) {
                $base_rate = 723.50;
            } elseif ($distance_km <= 89) {
                $base_rate = 722.00;
            } elseif ($distance_km <= 109) {
                $base_rate = 720.50;
            } elseif ($distance_km <= 129) {
                $base_rate = 719.00;
            } elseif ($distance_km <= 149) {
                $base_rate = 717.50;
            } elseif ($distance_km <= 169) {
                $base_rate = 716.00;
            } elseif ($distance_km <= 189) {
                $base_rate = 714.50;
            } else {
                $base_rate = 713.00;
            }

            $rate_with_vat = $base_rate * (1 + $vat_percent / 100);
        }

        $amount = $net_weight * $rate_with_vat;
        $status = 'Pending';

        $sql = "INSERT INTO shipments (driver_id, shipment_number, customer_source, pickup_date, from_location, to_location, vehicle_reg, net_weight, distance_km, rate_per_tonne, vat_percent, amount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssddddds", $driver_id, $shipment_number, $customer_source, $shipment_date, $from_location, $to_location, $vehicle_reg, $net_weight, $distance_km, $base_rate, $vat_percent, $amount, $status);
        mysqli_stmt_execute($stmt);
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
                From: <input type="text" name="from_location[]" required>
                To: <input type="text" name="to_location[]" required><br>
                Vehicle Reg: <input type="text" name="vehicle_reg[]" required><br>
                Net Weight (tonnes): <input type="number" step="0.01" name="net_weight[]" required><br>
                Distance (KM): <input type="number" step="0.1" name="distance_km[]" placeholder="Enter if rate not found" ><br><br>
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
        From: <input type="text" name="from_location[]" required>
        To: <input type="text" name="to_location[]" required><br>
        Vehicle Reg: <input type="text" name="vehicle_reg[]" required><br>
        Net Weight (tonnes): <input type="number" step="0.01" name="net_weight[]" required><br>
        Distance (KM): <input type="number" step="0.1" name="distance_km[]" placeholder="Enter if rate not found" ><br><br>
    `;
    document.getElementById('deliveries').appendChild(div);
}
</script>