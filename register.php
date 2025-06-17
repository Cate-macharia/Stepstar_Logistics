<?php
include 'includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $national_id = $_POST['national_id'];
    $hashed_password = $_POST['password']; // you may later hash this if needed

    $sql = "INSERT INTO users (name, email, password, role, national_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $role, $national_id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "<p style='color: lightgreen; text-align:center;'>✅ Registration successful!</p>";
    } else {
        $message = "<p style='color: red; text-align:center;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Stepstar Logistics</title>
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
      max-width: 450px;
      width: 90%;
      text-align: center;
      background: transparent;
    }

    h2 {
      margin-bottom: 20px;
      color: #fff;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
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

    select option {
      color: black;
    }

    input::placeholder {
      color: #e0e0e0;
    }

    button {
      padding: 12px 24px;
      font-size: 16px;
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 10px;
    }

    button:hover {
      background-color: #1e7e34;
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
  pointer-events: none;
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
  <div class="page-wrapper">

  <div class="overlay"></div>
  <div class="title">STEPSTAR LOGISTICS LIMITED</div>

  <div class="container">
    <h2 style="margin-top: 40px;">📝 Register</h2>
    <?= $message ?>
    <form method="POST" action="">
      <input type="text" name="name" placeholder="Name" required><br>
      <input type="email" name="email" placeholder="Email" required><br>
      <input type="text" name="national_id" placeholder="National ID" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <select name="role" required>
        <option value="">-- Select Role --</option>
        <option value="DRIVER">Driver</option>
        <option value="MANAGER">Manager</option>
        <option value="ADMIN">Admin</option>
      </select><br>
      <button type="submit">Register</button>
    </form>
  </div>
  <?php include 'includes/footer.php'; ?>
</div> <!-- end of page-wrapper -->
</body>

</body>
</html>


