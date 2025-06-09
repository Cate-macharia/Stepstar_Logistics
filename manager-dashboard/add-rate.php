<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zone = strtoupper(trim($_POST['zone']));
    $from = isset($_POST['from_location']) && trim($_POST['from_location']) !== '' ? strtoupper(trim($_POST['from_location'])) : null;
$to = isset($_POST['to_location']) && trim($_POST['to_location']) !== '' ? strtoupper(trim($_POST['to_location'])) : null;
    $route_range = trim($_POST['route_range']); // accepts <15, 16-29, NRB-KISII etc
    $base_rate = floatval($_POST['base_rate']);

    $vat = round($base_rate * 0.16, 2);
    $total = $base_rate + $vat;

    $stmt = mysqli_prepare($conn, "
        INSERT INTO manual_rates (zone, from_location, to_location, route_range, base_rate, vat, total_rate)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "ssssddd", $zone, $from, $to, $route_range, $base_rate, $vat, $total);
    mysqli_stmt_execute($stmt);

    header("Location: rates.php?added=1");
    exit();
} else {
    header("Location: rates.php");
    exit();
}
?>
