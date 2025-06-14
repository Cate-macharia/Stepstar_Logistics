<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
   $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML('<h1>Test Invoice Works</h1>');
    $mpdf->Output();
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
