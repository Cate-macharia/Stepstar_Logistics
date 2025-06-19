<?php
include '../includes/db.php';


$message = "";

// Handle single delete
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM manual_rates WHERE id = $id");
    $message = "✅ Rate ID $id deleted.";
}

// Handle delete all
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_rates'])) {
    $conn->query("DELETE FROM manual_rates");
    $message = "✅ All rates deleted successfully.";
}

// Handle manual add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_manual_rate'])) {
    $zone = strtoupper(trim($_POST['zone']));
    $from = trim($_POST['from_location']) ?: '-';
    $to = trim($_POST['to_location']) ?: '-';
    $route = trim($_POST['route_range']);
    $base = floatval($_POST['base_rate']);
    $vat = round($base * 0.16, 2);
    $total = $base + $vat;

    $stmt = $conn->prepare("INSERT INTO manual_rates (zone, from_location, to_location, route_range, base_rate, vat, total_rate)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssddd", $zone, $from, $to, $route, $base, $vat, $total);
    $stmt->execute();
    $message = "✅ Manual rate added.";
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rate'])) {
    $id = $_POST['rate_id'];
    $zone = strtoupper(trim($_POST['zone']));
    $from = trim($_POST['from_location']) ?: '-';
    $to = trim($_POST['to_location']) ?: '-';
    $route = trim($_POST['route_range']);
    $base = floatval($_POST['base_rate']);
    $vat = round($base * 0.16, 2);
    $total = $base + $vat;

    $stmt = $conn->prepare("UPDATE manual_rates SET zone=?, from_location=?, to_location=?, route_range=?, base_rate=?, vat=?, total_rate=? WHERE id=?");
    $stmt->bind_param("ssssdddi", $zone, $from, $to, $route, $base, $vat, $total, $id);
    $stmt->execute();
    $message = "✅ Rate ID $id updated.";
}

// Handle CSV Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $row = 0;

    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($row === 0) { $row++; continue; }

            $zone = strtoupper(trim($data[0]));
            $from = strtoupper(trim($data[1]));
            $to = strtoupper(trim($data[2]));
            $route = trim($data[3]);
            $base = floatval($data[4]);
            if (($from === '' || $from === '-') && ($to === '' || $to === '-') && str_contains($route, '-')) {
                $parts = explode('-', $route);
                $from = strtoupper(trim($parts[0]));
                $to = strtoupper(trim($parts[1]));
            }

            if (!str_contains($route, '-') && (empty($from) || empty($to))) {
                $from = '-';
                $to = '-';
            }
$vat = floatval($data[5]);
$total = floatval($data[6]);

            $stmt = $conn->prepare("INSERT INTO manual_rates (zone, from_location, to_location, route_range, base_rate, vat, total_rate)
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) die("❌ SQL prepare failed: " . $conn->error);

            $stmt->bind_param("ssssddd", $zone, $from, $to, $route, $base, $vat, $total);
            $stmt->execute();
            $row++;
        }
        fclose($handle);
        $message = "✅ CSV rates uploaded.";
    } else {
        $message = "❌ Could not open uploaded file.";
    }
}

// Fetch rates
$rates = $conn->query("SELECT * FROM manual_rates ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rates</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border-bottom: 1px solid #ccc; }
        th { background-color: #0077cc; }
        .success { color: green; }
        .btn-del { color: red; text-decoration: none; font-weight: bold; }
        .btn-edit { color: blue; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="user-info">📈 Rate Management</div>
        </div>
        <div class="content-area">
            <?php if ($message): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>

            <h2>💰 Add or Update Rate</h2>
            <form method="POST">
                <input type="hidden" name="rate_id" id="rate_id">
                Zone: <input type="text" name="zone" id="zone" required>
                From: <input type="text" name="from_location" id="from_location">
                To: <input type="text" name="to_location" id="to_location">
                Distance/Route: <input type="text" name="route_range" id="route_range" required>
                Base Rate: <input type="number" step="0.01" name="base_rate" id="base_rate" required>
                <button type="submit" name="add_manual_rate" id="add_btn">➕ Add Rate</button>
                <button type="submit" name="update_rate" id="update_btn" style="display:none; background:#27ae60; color:white;">💾 Update Rate</button>
            </form>

            <hr>

            <h2>📤 Upload CSV File</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit">📥 Upload CSV</button>
            </form>

            <hr>

            
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete ALL rates?');">
                <button type="submit" name="delete_all_rates" style="background:#c0392b;color:white;padding:10px;border:none;border-radius:5px;">
                    🗑️ Delete All Rates
                </button>
            </form>

            <hr>

            <h2>📋 Existing Rates</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Zone</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Route/Distance</th>
                        <th>Rate</th>
                        <th>VAT</th>
                        <th>Total</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = $rates->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $r['id']; ?></td>
                        <td><?php echo $r['zone']; ?></td>
                        <td><?php echo $r['from_location'] ?: '-'; ?></td>
                        <td><?php echo $r['to_location'] ?: '-'; ?></td>
                        <td><?php echo $r['route_range']; ?></td>
                        <td><?php echo number_format($r['base_rate'], 2); ?></td>
                        <td><?php echo number_format($r['vat'], 2); ?></td>
                        <td><strong><?php echo number_format($r['total_rate'], 2); ?></strong></td>
                        <td><?php echo $r['created_at']; ?></td>
                        <td>
                            <a href="?delete_id=<?php echo $r['id']; ?>" class="btn-del" onclick="return confirm('Delete this rate?');">🗑️</a>
                            <a href="#" class="btn-edit" onclick="populateEditForm(<?php echo htmlspecialchars(json_encode($r)); ?>)">✏️</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function populateEditForm(data) {
    document.getElementById('rate_id').value = data.id;
    document.getElementById('zone').value = data.zone;
    document.getElementById('from_location').value = data.from_location;
    document.getElementById('to_location').value = data.to_location;
    document.getElementById('route_range').value = data.route_range;
    document.getElementById('base_rate').value = data.base_rate;
    document.getElementById('add_btn').style.display = 'none';
    document.getElementById('update_btn').style.display = 'inline-block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</body>
</html>
