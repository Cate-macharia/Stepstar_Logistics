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
        $rate = $_POST['rate'][$i];
        $status = 'Pending';

        $sql = "INSERT INTO shipments (driver_id, shipment_number, customer_source, pickup_date, from_location, to_location, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $driver_id, $shipment_number, $customer_source, $shipment_date, $from_location, $to_location, $status);
        mysqli_stmt_execute($stmt);
    }

    $success = true;
}
?>

<h2>📦 Record New Deliveries for <?php echo htmlspecialchars($driver_name); ?> (ID: <?php echo htmlspecialchars($driver_id); ?>)</h2>

<?php if ($success): ?>
    <p style="color:green;">✅ Deliveries recorded successfully!</p>
<?php endif; ?>

<?php if (!$success): ?>
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
                Rate per Tonne: <input type="number" step="0.01" name="rate[]" required><br><br>
            </div>
        </div>

        <button type="button" onclick="addEntry()">➕ Add Another Delivery</button><br><br>
        <button type="submit">📥 Submit Deliveries</button>
    </form>
<?php endif; ?>

<!-- ⬅️ Back Button After Form -->
<div style="margin-top: 20px; text-align: right;">
    <a href="dashboard-driver.php" style="text-decoration:none; background:#6c757d; color:white; padding:8px 12px; border-radius:5px;">⬅️ Back to Dashboard</a>
</div>


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
        Rate per Tonne: <input type="number" step="0.01" name="rate[]" required><br><br>
    `;
    document.getElementById('deliveries').appendChild(div);
}
</script>
