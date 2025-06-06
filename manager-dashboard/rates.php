<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: ../login.php");
    exit();
}

// Fetch current rates
$rates = mysqli_query($conn, "SELECT * FROM rates ORDER BY from_location, to_location");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rates Management</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <h2>🚛 Stepstar</h2>
        <nav>
            <ul>
                <li><a href="view_orders.php">📋 View Orders</a></li>
                <li><a href="drivers.php">👷 Drivers Management</a></li>
                <li><a href="vehicles.php">🚚 Vehicle Management</a></li>
                <li><a href="rates.php" class="active">💰 Rates Management</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
                <li><a href="expenses.php">💸 Expenses</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="user-info">
                <strong>👤 <?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong> | Role: MANAGER
            </div>
            <div class="top-actions">
                <a href="settings.php">⚙️ Settings</a>
                <a href="../logout.php">🚪 Logout</a>
            </div>
        </header>

        <section class="content-area">
            <h1>💰 Rates Management</h1>

            <form action="upload-rates.php" method="POST" enctype="multipart/form-data" style="margin-bottom: 30px;">
                <label><strong>Upload Rates File:</strong></label><br>
                <select name="file_type" required>
                    <option value="">-- Choose File Type --</option>
                    <option value="pdf">📄 PDF</option>
                    <option value="csv">📊 CSV</option>
                    <option value="excel">📈 Excel</option>
                </select><br><br>

                <input type="file" name="rates_file" required><br><br>
                <button type="submit">📁 Upload & Import</button>
            </form>

            <h2>📃 Existing Rates</h2>
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>Distance (km)</th>
                    <th>Rate (KES/ton)</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($rates)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['from_location']) ?></td>
                        <td><?= htmlspecialchars($row['to_location']) ?></td>
                        <td><?= htmlspecialchars($row['distance_km']) ?></td>
                        <td><?= number_format($row['rate_per_ton'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </section>
    </main>
</div>

</body>
</html>
