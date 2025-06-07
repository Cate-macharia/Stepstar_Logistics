<?php
session_start();
include '../includes/db.php';

require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'MANAGER') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['rates_file']) && isset($_POST['file_type'])) {
    $fileType = $_POST['file_type'];
    $file = $_FILES['rates_file']['tmp_name'];

    if ($fileType === 'csv') {
        if (($handle = fopen($file, "r")) !== false) {
            fgetcsv($handle, 1000, ","); // Skip header
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $from = trim($data[0]);
                $to = trim($data[1]);
                $distance = trim($data[2]); // VARCHAR
                $rate = (float) preg_replace('/[^\d.]/', '', $data[3]);

                $stmt = mysqli_prepare($conn, "
                    INSERT INTO rates (from_location, to_location, distance_km, rate_per_ton)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        distance_km = VALUES(distance_km), 
                        rate_per_ton = VALUES(rate_per_ton)
                ");
                mysqli_stmt_bind_param($stmt, "sssd", $from, $to, $distance, $rate);
                mysqli_stmt_execute($stmt);
            }
            fclose($handle);
            header("Location: rates.php?success=1");
            exit();
        } else {
            echo "❌ Failed to open CSV file.";
        }

    } elseif ($fileType === 'excel') {
        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            unset($rows[0]); // remove header

            foreach ($rows as $row) {
                $from = trim($row[0]);
                $to = trim($row[1]);
                $distance = trim($row[2]);
                $rate = (float) preg_replace('/[^\d.]/', '', $row[3]);

                $stmt = mysqli_prepare($conn, "
                    INSERT INTO rates (from_location, to_location, distance_km, rate_per_ton)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        distance_km = VALUES(distance_km), 
                        rate_per_ton = VALUES(rate_per_ton)
                ");
                mysqli_stmt_bind_param($stmt, "sssd", $from, $to, $distance, $rate);
                mysqli_stmt_execute($stmt);
            }
            header("Location: rates.php?success=1");
            exit();
        } catch (Exception $e) {
            echo "❌ Error reading Excel file: " . $e->getMessage();
        }

    } elseif ($fileType === 'pdf') {
        $parser = new Parser();
        $pdf = $parser->parseFile($file);
        $text = $pdf->getText();
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            if (preg_match('/^([A-Z])\s+([\d>]+)\s+([\d,]+)\s+([\d,]+)\s+([\d,]+)/', $line, $matches)) {
                $zone = $matches[1];
                $distance = $matches[2];
                $rate = floatval(str_replace(',', '', $matches[3]));
                $vat = floatval(str_replace(',', '', $matches[4]));
                $total_rate = floatval(str_replace(',', '', $matches[5]));

                $stmt = mysqli_prepare($conn, "
                    INSERT INTO zone_rates (zone, distance, rate, vat, total_rate)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        distance = VALUES(distance), 
                        rate = VALUES(rate),
                        vat = VALUES(vat),
                        total_rate = VALUES(total_rate)
                ");
                mysqli_stmt_bind_param($stmt, "ssddd", $zone, $distance, $rate, $vat, $total_rate);
                mysqli_stmt_execute($stmt);
            }
        }

        header("Location: rates.php?success=1");
        exit();
    } else {
        echo "❌ Unsupported file type.";
    }
} else {
    echo "❌ Invalid request or no file uploaded.";
}
?>
