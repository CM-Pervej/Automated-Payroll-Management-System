<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['userrole_id'])) {
    // Redirect to the login page if not authenticated or user role is not set
    header('Location: index.php');
    exit();
}

// Fetch the user role from the session
$userrole_id = $_SESSION['userrole_id'];
?>