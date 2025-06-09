<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: ../login.php");
    exit();
}

$rates = mysqli_query($conn, "SELECT * FROM manual_rates ORDER BY zone");
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
                <strong>👤 <?= htmlspecialchars($_SESSION['user']['name']) ?></strong> | Role: MANAGER
            </div>
            <div class="top-actions">
                <a href="settings.php">⚙️ Settings</a>
                <a href="../logout.php">🚪 Logout</a>
            </div>
        </header>

        <section class="content-area">
            <h1>💰 Manual Rate Management</h1>

          <form action="add-rate.php" method="POST" style="margin-bottom: 30px;">
    <h3>➕ Add New Rate</h3>
    Zone: <input type="text" name="zone" required><br><br>
    From: <input type="text" name="from_location" placeholder="Optional"><br><br>
To: <input type="text" name="to_location" placeholder="Optional"><br><br>
    Route/Distance Range: <input type="text" name="route_range" required><br><br>
    Base Rate (KES/ton): <input type="number" step="0.01" name="base_rate" required><br><br>
    <button type="submit">💾 Save</button>
</form>


            <h2>📃 Existing Rates</h2>
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">
                <tr>
                    <th>Zone</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Route/Distance</th>
                    <th>Base Rate</th>
                    <th>VAT (16%)</th>
                    <th>Total Rate</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($rates)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['zone']) ?></td>
                        <td><?= htmlspecialchars($row['from_location']) ?></td>
                        <td><?= htmlspecialchars($row['to_location']) ?></td>
                        <td><?= htmlspecialchars($row['route_range']) ?></td>
                        <td><?= number_format($row['base_rate'], 2) ?></td>
                        <td><?= number_format($row['vat'], 2) ?></td>
                        <td><?= number_format($row['total_rate'], 2) ?></td>
                        <td>
                            <form action="delete-rate.php" method="POST" onsubmit="return confirm('Are you sure to delete this rate?');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit">🗑️ Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </section>
    </main>
</div>

</body>
</html>
