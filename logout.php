<?php
session_start();
// Remove all session variables
$_SESSION = [];
// Destroy the session
session_destroy();
// Redirect to the login page
header("Location: index.php");
exit;
?>