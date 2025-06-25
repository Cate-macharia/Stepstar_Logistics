<?php 
require_once __DIR__ . '/../vendor/autoload.php';
include '../includes/db.php';
session_start();

$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$tenant_id = $_SESSION['user']['tenant_id'] ?? null;

if (!$tenant_id) die("❌ Tenant ID missing.");

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
    <img src='$logo' height='60'><br>
    <h2 style='color:#005baa;'>$business_name - Report</h2>
    <p>$address<br>📞 $phone</p>
    <hr>
";

$statusTypes = ['Pending', 'In Progress', 'Delivered', 'Paid', 'Unpaid', 'All'];

if ($type === 'orders' && in_array($status, $statusTypes)) {
    $html .= "<h3>📦 Orders Report - Status: " . ($status === 'All' ? "All" : $status) . "</h3>";

    $query = "SELECT s.*, u.name AS driver_name 
              FROM shipments s 
              LEFT JOIN users u ON s.driver_id = u.national_id 
              WHERE s.tenant_id = ?
              " . ($status === 'Paid' ? " AND s.paid = 1" : ($status === 'Unpaid' ? " AND s.paid = 0" : ($status !== 'All' ? " AND s.status = ?" : ""))) . "
              ORDER BY s.pickup_date DESC";

    $stmt = $conn->prepare($query);
    if ($status === 'All') {
        $stmt->bind_param("i", $tenant_id);
    } elseif ($status === 'Paid' || $status === 'Unpaid') {
        $stmt->bind_param("i", $tenant_id);
    } else {
        $stmt->bind_param("is", $tenant_id, $status);
    }

    $stmt->execute();
    $result = $stmt->get_result();

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
    $html .= "<h3>💸 Expenses Report</h3>";
    $query = "SELECT e.*, v.vehicle_reg, u.name AS driver_name
              FROM expenses e
              LEFT JOIN vehicles v ON e.vehicle_id = v.id
              LEFT JOIN users u ON e.driver_id = u.id
              WHERE e.tenant_id = ?
              ORDER BY e.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();

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
    $stmt1 = $conn->prepare("SELECT SUM(amount) AS total_income FROM shipments WHERE tenant_id = ?");
    $stmt2 = $conn->prepare("SELECT SUM(amount) AS total_expense FROM expenses WHERE tenant_id = ?");
    $stmt1->bind_param("i", $tenant_id);
    $stmt2->bind_param("i", $tenant_id);
    $stmt1->execute(); $stmt2->execute();
    $income = $stmt1->get_result()->fetch_assoc()['total_income'] ?? 0;
    $expenses = $stmt2->get_result()->fetch_assoc()['total_expense'] ?? 0;
    $profit = $income - $expenses;

    $html .= "<h3>📊 Profit Report</h3>
    <p><strong>Total Income:</strong> KES " . number_format($income, 2) . "</p>
    <p><strong>Total Expenses:</strong> KES " . number_format($expenses, 2) . "</p>
    <p><strong>Profit:</strong> KES " . number_format($profit, 2) . "</p>";
}
else {
    $html .= "<p style='color:red;'>❌ Invalid report type selected.</p>";
}

$html .= "</div>";
$mpdf->WriteHTML($html);
$mpdf->Output("{$business_name}_Report.pdf", \Mpdf\Output\Destination::INLINE);
