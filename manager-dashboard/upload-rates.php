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

    function insertZoneRate($conn, $zone, $distance, $rate, $vat, $total_rate) {
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

    if ($fileType === 'csv') {
        if (($handle = fopen($file, "r")) !== false) {
            fgetcsv($handle); // Skip header
            while (($data = fgetcsv($handle)) !== false) {
                $zone = trim($data[0]);
                $distance = trim($data[1]);
                $rate = floatval(str_replace(',', '', $data[2]));
                $vat = floatval(str_replace(',', '', $data[3]));
                $total_rate = floatval(str_replace(',', '', $data[4]));
                insertZoneRate($conn, $zone, $distance, $rate, $vat, $total_rate);
            }
            fclose($handle);
        } else {
            echo "❌ Failed to open CSV file.";
            exit();
        }

    } elseif ($fileType === 'excel') {
        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            unset($rows[0]); // Remove header

            foreach ($rows as $row) {
                $zone = trim($row[0]);
                $distance = trim($row[1]);
                $rate = floatval(str_replace(',', '', $row[2]));
                $vat = floatval(str_replace(',', '', $row[3]));
                $total_rate = floatval(str_replace(',', '', $row[4]));
                insertZoneRate($conn, $zone, $distance, $rate, $vat, $total_rate);
            }
        } catch (Exception $e) {
            echo "❌ Error reading Excel file: " . $e->getMessage();
            exit();
        }

    } elseif ($fileType === 'pdf') {
        $parser = new Parser();
        $pdf = $parser->parseFile($file);
        $text = $pdf->getText();
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            if (preg_match('/^([A-Z])\s+([\d>]+)\s+([\d,]+)\s+([\d,]+)\s+([\d,]+)/', trim($line), $matches)) {
                $zone = $matches[1];
                $distance = $matches[2];
                $rate = floatval(str_replace(',', '', $matches[3]));
                $vat = floatval(str_replace(',', '', $matches[4]));
                $total_rate = floatval(str_replace(',', '', $matches[5]));
                insertZoneRate($conn, $zone, $distance, $rate, $vat, $total_rate);
            }
        }
    } else {
        echo "❌ Unsupported file type.";
        exit();
    }

    header("Location: rates.php?success=1");
    exit();
} else {
    echo "❌ Invalid request or no file uploaded.";
}
?>
