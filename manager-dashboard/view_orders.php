<?php
include '../includes/db.php';

$filter = $_GET['filter'] ?? 'all';
$from_date = $_GET['from'] ?? '';
$to_date = $_GET['to'] ?? '';
$paidFilter = isset($_GET['paid']) ? " AND paid = " . intval($_GET['paid']) : "";
$archived = isset($_GET['archived']) && $_GET['archived'] == '1';

$whereClause = "1";
if ($filter === 'today') {
    $whereClause = "DATE(pickup_date) = CURDATE()";
} elseif ($filter === 'week') {
    $whereClause = "YEARWEEK(pickup_date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $whereClause = "MONTH(pickup_date) = MONTH(CURDATE()) AND YEAR(pickup_date) = YEAR(CURDATE())";
} elseif ($filter === 'range' && $from_date && $to_date) {
    $whereClause = "pickup_date BETWEEN '$from_date' AND '$to_date'";
}

// Compose query now that filters are defined
$query = "SELECT * FROM shipments WHERE $whereClause $paidFilter";
$query .= $archived ? " AND archived = 1" : " AND (archived IS NULL OR archived = 0)";
$query .= " ORDER BY pickup_date DESC";


$result = mysqli_query($conn, $query);
?>

<h2>📋 <?= $archived ? 'Archived' : 'All' ?> Orders</h2>

<form method="GET" action="dashboard-manager.php" style="margin-bottom: 20px;">
    <input type="hidden" name="page" value="view_orders">
    <label>Filter: </label>
    <select name="filter" onchange="toggleRange(this.value)">
        <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All</option>
        <option value="today" <?= $filter == 'today' ? 'selected' : '' ?>>Today</option>
        <option value="week" <?= $filter == 'week' ? 'selected' : '' ?>>This Week</option>
        <option value="month" <?= $filter == 'month' ? 'selected' : '' ?>>This Month</option>
        <option value="range" <?= $filter == 'range' ? 'selected' : '' ?>>Date Range</option>
    </select>

    <span id="range" style="<?= $filter == 'range' ? '' : 'display:none;' ?>">
        From: <input type="date" name="from" value="<?= $from_date ?>">
        To: <input type="date" name="to" value="<?= $to_date ?>">
    </span>

    <button type="submit">🔍 Apply</button>
</form>

<a href="dashboard-manager.php?page=view_orders&archived=<?= $archived ? '0' : '1' ?>" class="btn" style="background: #6c757d; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; float: right;">
    <?= $archived ? '⬅ Back to Active Orders' : '🗃 View Archived Orders' ?>
</a>
<div style="clear: both;"></div>

<table border="1" cellpadding="10" cellspacing="0" width="100%">
    <tr>
        <th>Date</th>
        <th>Shipment #</th>
        <th>Driver ID</th>
        <th>Customer</th>
        <th>Route</th>
        <th>Vehicle</th>
        <th>Status</th>
        <th>💰 Paid</th>
        <th>✏️ Edit</th>
        <th>📄 Invoice</th>
    </tr>

    <?php if (mysqli_num_rows($result) == 0): ?>
        <tr><td colspan="10" style="color:red;">No orders found.</td></tr>
    <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['pickup_date']) ?></td>
            <td><?= htmlspecialchars($row['shipment_number']) ?></td>
            <td><?= htmlspecialchars($row['driver_id']) ?></td>
            <td><?= htmlspecialchars($row['customer_source']) ?></td>
            <td><?= htmlspecialchars($row['from_location']) ?> → <?= htmlspecialchars($row['to_location']) ?></td>
            <td><?= htmlspecialchars($row['vehicle_reg']) ?: 'N/A' ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <button 
                    onclick="markAsPaid(<?= $row['id'] ?>, this)" 
                    style="background:<?= $row['paid'] ? '#5cb85c' : '#f0ad4e' ?>;color:white;border:none;padding:5px 10px;border-radius:4px;"
                    <?= $row['paid'] ? 'disabled' : '' ?>>
                    <?= $row['paid'] ? '✔ Paid' : '💰 Mark Paid' ?>
                </button>
            </td>
            <td><a href="edit-order.php?id=<?= $row['id'] ?>">✏️</a></td>
            <td>
                <?php 
                $invoicePath = "../invoices/invoice_{$row['shipment_number']}.pdf";
                if (file_exists($invoicePath)) {
                    echo "<a href='$invoicePath' target='_blank'>📄 View</a>";
                } else {
                    echo "<span style='color:gray;'>Not Generated</span>";
                }
                ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php endif; ?>
</table>

<script>
function toggleRange(value) {
    document.getElementById('range').style.display = value === 'range' ? 'inline' : 'none';
}

function markAsPaid(id, button) {
    if (!confirm("Confirm marking as paid?")) return;

    fetch('mark-paid.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'shipment_id=' + encodeURIComponent(id)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            button.textContent = '✔ Paid';
            button.style.background = '#5cb85c';
            button.disabled = true;
        } else {
            alert("Failed to update: " + (data.error || 'Unknown error'));
        }
    })
    .catch(() => alert("❌ Network or server error."));
}
</script>
