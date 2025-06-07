<?php
include 'includes/db.php';

$from = strtoupper(trim($_GET['from'] ?? ''));
$to = strtoupper(trim($_GET['to'] ?? ''));

$response = ['found' => false];

if ($from && $to) {
    $sql = "SELECT distance FROM zone_rates WHERE zone = ? AND distance LIKE CONCAT('%', ?, '%') LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $from, $to);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        $response['found'] = true;
        $response['distance'] = $data['distance'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
