<?php
session_start();

// Destroy session to log out the user
session_destroy();

// Redirect to the login page after logging out
header('Location: index.php');
exit();
?>
