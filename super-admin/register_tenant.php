<?php
include '../includes/db.php';
$message = '';

$logoPath = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $address = trim($_POST['address'] ?? '');
    $vat_number = trim($_POST['vat_number'] ?? '');
    $currency = trim($_POST['currency'] ?? 'KES');
    $invoice_footer = trim($_POST['invoice_footer'] ?? '');
    $domain = strtolower(preg_replace('/\s+/', '', $business_name));

    $name = trim($_POST['name'] ?? 'Manager'); // placeholder manager name
    $national_id = trim($_POST['national_id'] ?? '12345678');
    $password = trim($_POST['password'] ?? '123456'); // default/fixed password
    $role = 'MANAGER';

    // Upload logo if provided
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $uploadDir = '../storage/logos/';
        $logoName = time() . '_' . basename($_FILES['logo']['name']);
        $logoPath = $uploadDir . $logoName;
        move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath);
    }

    // Insert into tenants table
    $stmt = $conn->prepare("INSERT INTO tenants 
        (business_name, email, phone, domain, logo_url, address, vat_number, currency, invoice_footer) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssss", $business_name, $email, $phone, $domain, $logoPath, $address, $vat_number, $currency, $invoice_footer);

    if ($stmt->execute()) {
        $tenant_id = $conn->insert_id;

        // Insert manager into users table
        $userStmt = $conn->prepare("INSERT INTO users (name, email, password, role, national_id, tenant_id) VALUES (?, ?, ?, ?, ?, ?)");
        $userStmt->bind_param("sssssi", $name, $email, $password, $role, $national_id, $tenant_id);

        if ($userStmt->execute()) {
            $message = "<p style='color:green;text-align:center;'>✅ Tenant and manager registered successfully.</p>";
        } else {
            $message = "<p style='color:red;text-align:center;'>❌ Error adding manager: " . $userStmt->error . "</p>";
        }
        $userStmt->close();
    } else {
        $message = "<p style='color:red;text-align:center;'>❌ Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Tenant - Flexbyte</title>
    <link rel="stylesheet" href="../manager-dashboard/dashboard.css">
    <style>
        body {
            background: url('../images/lorry-background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 40px auto;
            padding: 30px;
            background:#66b2b2;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            text-align: center;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
        }
        button:hover {
            background: #0056b3;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🏢 Register New Tenant</h2>
    <?= $message ?>
    <form method="POST" enctype="multipart/form-data">
             <input type="text" name="business_name" placeholder="Business Name" required>
             <input type="email" name="email" placeholder="Business Email" required>
             <input type="text" name="phone_number" placeholder="Phone Number" required>
             <input type="text" name="address" placeholder="Business Address">
             <input type="text" name="vat_number" placeholder="VAT Number">
             <input type="text" name="currency" placeholder="Currency (e.g., KES)" value="KES">
             <textarea name="invoice_footer" placeholder="Custom Invoice Footer"></textarea>
             <label>Upload Logo: <input type="file" name="logo"></label>
             <button type="submit">Register Tenant</button>
    </form>
</div>
</body>
</html>
