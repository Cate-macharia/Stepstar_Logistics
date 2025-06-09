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
    echo json_encode(['found' => false]);
    exit;
}

// Combine: check if a zone row matches exactly from–to route
$sql = "SELECT zone, distance, rate, total_rate 
        FROM zone_rates 
        WHERE zone = CONCAT(SUBSTRING(?,1,1), '-') 
           OR CONCAT(?, ' - ', ?) = CONCAT(?, ' - ', ?)
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssss", $from, $from, $to, $from, $to);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if ($row) {
    echo json_encode([
        'found' => true,
        'distance' => $row['distance'],
        'rate' => $row['rate'],
        'total_rate' => $row['total_rate'],
        'zone' => $row['zone']
    ]);
} else {
    echo json_encode(['found' => false]);
}
