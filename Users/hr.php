<?php
session_start();

// Check if the user is logged in and has the HR role
if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2)) {
    header('Location: ../dashboard.php'); // Redirect to dashboard if not HR or Admin
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Page</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold">Welcome, HR</h1>
        <p>You have access to all pages except Admin pages.</p>
    </div>
</body>
</html>
