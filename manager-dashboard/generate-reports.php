<?php 
require_once __DIR__ . '/../vendor/autoload.php';
include '../includes/db.php';
session_start();

$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$tenant_id = $_SESSION['user']['tenant_id'] ?? null;

// Get tenant profile
$tenantRes = $conn->query("SELECT * FROM tenants WHERE id = $tenant_id");
$tenant = $tenantRes->fetch_assoc();
$logo = $tenant['logo_url'] ?? '../images/default-logo.png';
$business_name = $tenant['business_name'] ?? 'Your Business';
$phone = $tenant['phone_number'] ?? '';
$address = $tenant['address'] ?? '';
$footer_note = $tenant['invoice_footer'] ?? '';

$mpdf = new \Mpdf\Mpdf();
$mpdf->SetTitle("$business_name Report");

$html = "<div style='font-family:Arial,sans-serif;'>
    <div style='text-align:center;'>
        <img src='$logo' height='60'><br>
        <h2 style='color:#005baa;'>$business_name</h2>
        <p>$address<br>📞 $phone</p>
    </div>
    <hr>
";

$statusTypes = ['Pending', 'In Progress', 'Delivered', 'Paid', 'Unpaid', 'All'];

if ($type === 'orders' && in_array($status, $statusTypes)) {
    $filterSQL = "";

    if ($status === 'Paid') {
        $filterSQL = "WHERE s.paid = 1";
        $html .= "<h3>✅ Paid Orders Report</h3>";
    } elseif ($status === 'Unpaid') {
        $filterSQL = "WHERE s.paid = 0";
        $html .= "<h3>💰 Unpaid Orders Report</h3>";
    } elseif ($status === 'All') {
        $filterSQL = "";
        $html .= "<h3>📋 All Orders Report</h3>";
    } else {
        $filterSQL = "WHERE s.status = '" . $conn->real_escape_string($status) . "'";
        $html .= "<h3>📦 Orders Report - Status: " . $status . "</h3>";
    }

    $query = "SELECT s.*, u.name AS driver_name 
              FROM shipments s 
              LEFT JOIN users u ON s.driver_id = u.national_id 
              $filterSQL 
              ORDER BY s.pickup_date DESC";

    $result = $conn->query($query);

    $html .= "<table width='100%' border='1' cellspacing='0' cellpadding='5' style='border-collapse: collapse;'>
        <thead><tr style='background:#f2f2f2;'>
            <th>#</th><th>Shipment No</th><th>Date</th><th>Customer</th><th>Driver</th><th>Vehicle</th><th>From → To</th><th>Status</th><th>Amount</th>
        </tr></thead><tbody>";

    $i = 1; $total = 0;
    while ($row = $result->fetch_assoc()) {
        $html .= "<tr>
            <td>{$i}</td>
            <td>{$row['shipment_number']}</td>
            <td>{$row['pickup_date']}</td>
            <td>{$row['customer_source']}</td>
            <td>{$row['driver_name']}</td>
            <td>{$row['vehicle_reg']}</td>
            <td>{$row['from_location']} → {$row['to_location']}</td>
            <td>{$row['status']}</td>
            <td>KES " . number_format($row['amount'], 2) . "</td>
        </tr>";
        $total += $row['amount'];
        $i++;
    }

    $html .= "</tbody></table>
    <p><strong>Total Amount: KES " . number_format($total, 2) . "</strong></p>";
}
elseif ($type === 'expenses') {
    $html .= "<h3>Expenses Report</h3>";
    $query = "SELECT e.*, v.vehicle_reg, u.name AS driver_name
              FROM expenses e
              LEFT JOIN vehicles v ON e.vehicle_id = v.id
              LEFT JOIN users u ON e.driver_id = u.id
              ORDER BY e.created_at DESC";
    $result = $conn->query($query);

    $html .= "<table width='100%' border='1' cellspacing='0' cellpadding='5' style='border-collapse: collapse;'>
    <thead><tr style='background:#f2f2f2;'>
        <th>#</th><th>Type</th><th>Specific</th><th>Description</th><th>Driver</th><th>Vehicle</th><th>Amount</th><th>Date</th>
    </tr></thead><tbody>";

    $i = 1; $total = 0;
    while ($row = $result->fetch_assoc()) {
        $html .= "<tr>
            <td>{$i}</td>
            <td>{$row['type']}</td>
            <td>{$row['specific_type']}</td>
            <td>{$row['description']}</td>
            <td>" . ($row['driver_name'] ?? '-') . "</td>
            <td>" . ($row['vehicle_reg'] ?? '-') . "</td>
            <td>KES " . number_format($row['amount'], 2) . "</td>
            <td>{$row['created_at']}</td>
        </tr>";
        $total += $row['amount'];
        $i++;
    }
    $html .= "</tbody></table>
    <p><strong>Total Expenses: KES " . number_format($total, 2) . "</strong></p>";
}
elseif ($type === 'profit') {
    $incomeRes = $conn->query("SELECT SUM(amount) AS total_income FROM shipments");
    $income = $incomeRes->fetch_assoc()['total_income'] ?? 0;
    $expenseRes = $conn->query("SELECT SUM(amount) AS total_expense FROM expenses");
    $expenses = $expenseRes->fetch_assoc()['total_expense'] ?? 0;
    $profit = $income - $expenses;

    $html .= "<h3>Profit Report</h3>
    <p><strong>Total Income:</strong> KES " . number_format($income, 2) . "</p>
    <p><strong>Total Expenses:</strong> KES " . number_format($expenses, 2) . "</p>
    <p><strong>Profit:</strong> KES " . number_format($profit, 2) . "</p>";
} else {
    $html .= "<p style='color:red;'>❌ Invalid report type selected.</p>";
}

$html .= "<hr><div style='text-align:center; font-size:13px; margin-top:20px;'>"
      . nl2br($footer_note) . "</div></div>";

$mpdf->WriteHTML($html);
$mpdf->Output("{$business_name}_Report.pdf", \Mpdf\Output\Destination::INLINE);
