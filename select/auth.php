<?php 
session_start();

// Check if the user is logged in and is User1
if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2 && $_SESSION['userrole_id'] != 4)) {
    header('Location: ../dashboard.php'); // Redirect to dashboard if not User1
    exit();
}
// Fetch the user role from the session
$userrole_id = $_SESSION['userrole_id'];
?>