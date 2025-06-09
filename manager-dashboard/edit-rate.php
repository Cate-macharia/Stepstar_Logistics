<?php
session_start();
include '../includes/db.php';

if (!isset($_GET['id'])) {
    die("❌ Rate ID is required.");
}

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM manual_rates WHERE id = $id");
$rate = mysqli_fetch_assoc($result);

if (!$rate) {
    die("❌ Rate not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zone = $_POST['zone'];
    $from = $_POST['from_location'];
    $to = $_POST['to_location'];
    $distance = $_POST['distance_km'];
    $base_rate = $_POST['base_rate'];
    $vat = $_POST['vat'];
    $total_rate = $_POST['total_rate'];

    $stmt = mysqli_prepare($conn, "
        UPDATE manual_rates SET zone=?, from_location=?, to_location=?, distance_km=?, base_rate=?, vat=?, total_rate=?
        WHERE id=?
    ");
    mysqli_stmt_bind_param($stmt, "sssddddi", $zone, $from, $to, $distance, $base_rate, $vat, $total_rate, $id);
    mysqli_stmt_execute($stmt);

    echo "✅ Rate updated successfully!";
    echo "<br><a href='manage-rates.php'>⬅️ Back to Rate List</a>";
    exit();
}
?>

<h2>✏️ Edit Rate</h2>
<form method="POST">
    Zone: <input type="text" name="zone" value="<?= $rate['zone'] ?>" required><br>
    From: <input type="text" name="from_location" value="<?= $rate['from_location'] ?>" required><br>
    To: <input type="text" name="to_location" value="<?= $rate['to_location'] ?>" required><br>
    Distance (KM): <input type="number" step="0.1" name="distance_km" value="<?= $rate['distance_km'] ?>" required><br>
    Base Rate: <input type="number" step="0.01" name="base_rate" value="<?= $rate['base_rate'] ?>" required><br>
    VAT: <input type="number" step="0.01" name="vat" value="<?= $rate['vat'] ?>" required><br>
    Total Rate: <input type="number" step="0.01" name="total_rate" value="<?= $rate['total_rate'] ?>" required><br>
    <button type="submit">💾 Update Rate</button>
</form>
