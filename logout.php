<?php
session_start();
session_unset();
session_destroy();

// Redirect with a logout success message
header("Location: login.php?message=loggedout");
exit();
?>
