<?php
session_start();
echo "<h2>Welcome, Manager " . $_SESSION['user']['name'] . "!</h2>";
?>
