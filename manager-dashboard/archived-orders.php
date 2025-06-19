<?php
include '../includes/db.php';

$sql = "SELECT * FROM shipments WHERE archived = 1 ORDER BY pickup_date DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Query error: " . $conn->error); // <-- This helps catch SQL errors
}
?>


<h2>🗃 Archived Orders</h2>
<a href="dashboard-manager.php?page=view_orders" style="text-decoration:none;background:#007bff;color:#fff;padding:6px 12px;border-radius:4px;">⬅ Back to Orders</a>
<br><br>

<table border="1" cellpadding="10" cellspacing="0" width="100%">
    <tr>
        <th>Date</th>
        <th>Shipment #</th>
        <th>Driver ID</th>
        <th>Customer</th>
        <th>Route</th>
        <th>Vehicle</th>
        <th>Status</th>
        <th>📄 Invoice</th>
    </tr>

    <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="8" style="color:red;">⚠️ No archived orders found.</td></tr>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['pickup_date']) ?></td>
            <td><?= htmlspecialchars($row['shipment_number']) ?></td>
            <td><?= htmlspecialchars($row['driver_id']) ?></td>
            <td><?= htmlspecialchars($row['customer_source']) ?></td>
            <td><?= htmlspecialchars($row['from_location']) ?> → <?= htmlspecialchars($row['to_location']) ?></td>
            <td><?= htmlspecialchars($row['vehicle_reg']) ?: 'N/A' ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
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
