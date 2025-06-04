<?php
session_start();
include 'includes/db.php';

$driver_name = $_SESSION['user']['name'];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < count($_POST['shipment_number']); $i++) {
        $driver_id = $_POST['driver_id'][$i];
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

<h2>🚚 Welcome, Driver <?php echo htmlspecialchars($driver_name); ?>!</h2>

<?php if ($success): ?>
    <p style="color:green; font-size:18px;">✅ Deliveries recorded successfully!</p>
<?php else: ?>
    <p>Please enter your delivery details below. Your National ID will be recorded for each delivery.</p>
    <hr>

    <form method="POST" id="deliveryForm">
        <div id="deliveries">
            <div class="delivery-entry">
                <h4>Delivery #1</h4>
                Driver National ID: <input type="text" name="driver_id[]" required><br>
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

    <script>
    function addEntry() {
        const div = document.createElement('div');
        div.classList.add('delivery-entry');
        div.innerHTML = `
            <hr><h4>New Delivery</h4>
            Driver National ID: <input type="text" name="driver_id[]" required><br>
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
<?php endif; ?>
