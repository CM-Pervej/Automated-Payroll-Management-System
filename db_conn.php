<?php
// Database configuration
$servername = "localhost"; // or use "127.0.0.1"
$username = "root";        // your MySQL username
$password = "";            // your MySQL password
$database = "payroll";      // the name of the database

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // echo "Connected successfully to the 'salary' database";
}
?>
