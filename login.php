<?php
session_start();
include 'includes/db.php';

$message = '';



if (isset($_GET['message']) && $_GET['message'] === 'loggedout') {
    $message = "<p style='color: lightgreen; text-align:center;'>✅ You have been logged out successfully.</p>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE name = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && $password === $user['password']) {
        $_SESSION['user']['tenant_id'] = $user['tenant_id'];
        
        if ($user['role'] === 'DRIVER') {
            header("Location: dashboard-driver.php");
        } elseif ($user['role'] === 'MANAGER') {
            header("Location: manager-dashboard/dashboard-manager.php");
        } elseif ($user['role'] === 'SUPER_ADMIN') {
            header("Location: super_admin_dashboard.php");
        }
        exit();
    } else {
        $message = "<p style='color: red; text-align:center;'>❌ Invalid name or password!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Stepstar Logistics</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body, html {
      height: 100%;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background: url('images/lorry-background.jpg') no-repeat center center/cover;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      color: white;
    }

    .overlay {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: 0;
    }

    .container {
      position: relative;
      z-index: 1;
      padding: 40px;
      border-radius: 12px;
      max-width: 400px;
      width: 90%;
      text-align: center;
      background: transparent;
    }

    h2 {
      margin-bottom: 20px;
      color: #fff;
    }

    input[type="text"],
    input[type="password"] {
      width: 90%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 8px;
      border: none;
      font-size: 16px;
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
      outline: none;
    }

    input::placeholder {
      color: #e0e0e0;
    }

    button {
      padding: 12px 24px;
      font-size: 16px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 10px;
    }

    button:hover {
      background-color: #0056b3;
    }

    .title {
      position: absolute;
      top: 30px;
      left: 0;
      right: 0;
      text-align: center;
      color: white;
      font-size: 28px;
      font-weight: bold;
      z-index: 1;
      text-shadow: 2px 2px 8px #000;
    }

    @media (max-width: 600px) {
      .container {
        padding: 20px;
      }

      .title {
        font-size: 22px;
      }
    }
  </style>
</head>
<body>
  <div class="overlay"></div>
  <div class="title">STEPSTAR LOGISTICS LIMITED</div>

  <div class="container">
    <h2>🔐 Login</h2>
    <?= $message ?>
    <form method="POST" action="">
      <input type="text" name="name" placeholder="Name" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
