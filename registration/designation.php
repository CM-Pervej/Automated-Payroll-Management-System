<?php
// Include the database connection file
include '../db_conn.php'; // Adjust the path based on your directory structure

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $designation = $_POST['designation'];

    // Prepare and bind SQL statement
    $stmt = $conn->prepare("INSERT INTO designations (designation) VALUES (?)");
    $stmt->bind_param("s", $designation);

    // Execute the statement
    if ($stmt->execute()) {
        $success_message = "New designation added successfully";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Designation Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">

    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-center mb-6">Add Designation</h2>
        
        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="text-green-600 mb-4 text-center"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="text-red-600 mb-4 text-center"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="post" class="space-y-4">
            <div>
                <label for="designation" class="block text-lg font-semibold mb-2">Designation</label>
                <input type="text" id="designation" name="designation" placeholder="Add new designation" required 
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <input type="submit" value="Submit" 
                    class="w-full bg-green-500 text-white py-3 rounded-lg hover:bg-green-600 transition duration-300 cursor-pointer">
            </div>
        </form>
    </div>

</body>
</html>
