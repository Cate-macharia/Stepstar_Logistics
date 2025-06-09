<?php
include '../includes/db.php';
$result = mysqli_query($conn, "SELECT * FROM manual_rates ORDER BY from_location, to_location");
?>

<h2>📃 Current Rates</h2>
<table border="1" cellpadding="8">
<tr>
  <th>Zone</th><th>From</th><th>To</th><th>Route/Distance Range</th><th>Base Rate</th><th>VAT</th><th>Total</th><th>Actions</th>
</tr>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
  <td><?= $row['zone'] ?></td>
  <td><?= $row['from_location'] ?></td>
  <td><?= $row['to_location'] ?></td>
  <td><?= $row['route_range'] ?></td>
  <td><?= $row['base_rate'] ?></td>
  <td><?= $row['vat'] ?></td>
  <td><?= $row['total_rate'] ?></td>
  <td><a href="edit-rate.php?id=<?= $row['id'] ?>">✏️ Edit</a></td>
</tr>
<?php endwhile; ?>
</table>
