<?php
// Include the database connection
include '../db_conn.php';

// Initialize a variable to hold the success message
$successMessage = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize inputs
    $allwName = htmlspecialchars($_POST['allwName']);
    $allwPercentage = htmlspecialchars($_POST['allwPercentage']);
    $allwValue = htmlspecialchars($_POST['allwValue']);

    // Prepare an SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO allowanceList (allwName, allwPercentage, allwValue) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $allwName, $allwPercentage, $allwValue);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        $successMessage = "Allowance registered successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
    
    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allowance Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-lg mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-4">Register New Allowance</h1>
        <?php if ($successMessage): // Check if there's a success message ?>
            <div class="mb-4 text-green-600">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        <form action="allowanceList.php" method="POST" class="space-y-4">
            <!-- Allowance Name -->
            <div>
                <label for="allwName" class="block text-sm font-medium text-gray-700">Allowance Name</label>
                <input type="text" name="allwName" id="allwName" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Allowance Percentage -->
            <div>
                <label for="allwPercentage" class="block text-sm font-medium text-gray-700">Allowance Percentage</label>
                <input type="number" step="0.01" name="allwPercentage" id="allwPercentage" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Allowance Value -->
            <div>
                <label for="allwValue" class="block text-sm font-medium text-gray-700">Allowance Value</label>
                <input type="number" step="0.01" name="allwValue" id="allwValue" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <button type="submit" class="w-full bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600">Register Allowance</button>
            </div>
        </form>
    </div>
</body>
</html>
