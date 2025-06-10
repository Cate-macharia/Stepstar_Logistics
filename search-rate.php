<?php
session_start();
header('Content-Type: application/json');
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'DRIVER') {
    echo json_encode(['found' => false, 'error' => 'Not authorized']);
    exit;
}

$from = trim(strtoupper($_GET['from'] ?? ''));
$to   = trim(strtoupper($_GET['to'] ?? ''));

if (!$from || !$to) {
    echo json_encode(['found' => false, 'error' => 'Missing from/to']);
    exit;
}

$route = "$from - $to";

// 1) Try exact from-to in manual_rates
$sql = "SELECT route_range, base_rate, vat, total_rate, zone 
        FROM manual_rates 
        WHERE UPPER(CONCAT(from_location, ' - ', to_location)) = ?
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $route);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if ($row) {
    echo json_encode([
        'found' => true,
        'distance' => $row['route_range'],
        'rate'     => $row['base_rate'],
        'total_rate'=> $row['total_rate'],
        'zone'     => $row['zone']
    ]);
    exit;
}

// 2) Otherwise try to match a distance-based rate (like '<15', '30-59')
$sql2 = "SELECT route_range, base_rate, vat, total_rate, zone 
         FROM manual_rates
         WHERE route_range = ?
         LIMIT 1";
$stmt2 = mysqli_prepare($conn, $sql2);
mysqli_stmt_bind_param($stmt2, "s", $from); // assuming driver types range in "from" field
mysqli_stmt_execute($stmt2);
$res2 = mysqli_stmt_get_result($stmt2);
$row2 = mysqli_fetch_assoc($res2);

if ($row2) {
    echo json_encode([
        'found' => true,
        'distance'  => $row2['route_range'],
        'rate'      => $row2['base_rate'],
        'total_rate'=> $row2['total_rate'],
        'zone'      => $row2['zone']
    ]);
    exit;
}

// fallback not found
echo json_encode(['found' => false]);
