<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['rates_file']) && $_POST['file_type'] === 'csv') {
    $file = $_FILES['rates_file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== false) {
        // Skip header row
        fgetcsv($handle, 1000, ",");

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $from = trim($data[0]);
            $to = trim($data[1]);
            $distance = (float) $data[2];
            $rate = (float) $data[3];

            $stmt = mysqli_prepare($conn, "INSERT INTO rates (from_location, to_location, distance_km, rate_per_ton) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE distance_km = VALUES(distance_km), rate_per_ton = VALUES(rate_per_ton)");
            mysqli_stmt_bind_param($stmt, "ssdd", $from, $to, $distance, $rate);
            mysqli_stmt_execute($stmt);
        }

        fclose($handle);
        header("Location: rates.php?success=1");
        exit();
    } else {
        echo "❌ Error opening file.";
    }
} else {
    echo "❌ Invalid file type or no file uploaded.";
}
