<?php
include '../includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $name = trim($_POST['name']);
    $national_id = trim($_POST['national_id']);
    $password = trim($_POST['password']);
    $role = 'MANAGER'; // default role for tenant registration
    $domain = strtolower(preg_replace('/\s+/', '', $business_name));

    // Insert into tenants table
    $stmt = $conn->prepare("INSERT INTO tenants (business_name, email, phone, domain) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $business_name, $email, $phone, $domain);

    if ($stmt->execute()) {
        $tenant_id = $conn->insert_id; // get ID of newly created tenant

        // Now insert into users table
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
            background: rgba(255,255,255,0.95);
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
    <form method="POST">
        <input type="text" name="business_name" placeholder="Business Name" required>
        <input type="email" name="email" placeholder="Contact Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="text" name="name" placeholder="Manager Name" required>
        <input type="text" name="national_id" placeholder="Manager National ID" required>
        <input type="password" name="password" placeholder="Manager Password" required>
        <button type="submit">Register Tenant</button>
    </form>
</div>
</body>
</html>
