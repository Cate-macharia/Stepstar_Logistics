<?php
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $national_id = $_POST['national_id'];
   $hashed_password = $_POST['password'];

    $sql = "INSERT INTO users (name, email, password, role, national_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $role, $national_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Registration successful!";
    } else {
        echo "❌ Error: " . mysqli_error($conn);
    }
}
?>

<h2>Register</h2>
<form method="POST" action="">
  <input type="text" name="name" placeholder="Name" required><br><br>
  <input type="email" name="email" placeholder="Email" required><br><br>
  <input type="text" name="national_id" placeholder="National ID" required><br><br>
  <input type="password" name="password" placeholder="Password" required><br><br>
  <select name="role" required>
    <option value="DRIVER">Driver</option>
    <option value="MANAGER">Manager</option>
    <option value="ADMIN">ADMIN</option>
  </select><br><br>
  <button type="submit">Register</button>
</form>
