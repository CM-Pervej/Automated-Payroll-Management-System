<?php
// Include the database connection
include '../db_conn.php';

// Initialize a variable to hold the success message
$successMessage = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize inputs
    $grade = (int) htmlspecialchars($_POST['grade']);
    $increment = htmlspecialchars($_POST['increment']);
    $scale = htmlspecialchars($_POST['scale']);
    $gradePercentage = htmlspecialchars($_POST['gradePercentage']);

    // Check if the grade already exists
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM grade WHERE grade = ?");
    $check_stmt->bind_param("i", $grade);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo "Error: Grade already exists!";
    } else {
        // Prepare an SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO grade (grade, increment, scale, gradePercentage) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idsd", $grade, $increment, $scale, $gradePercentage);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            $successMessage = "Grade registered successfully!"; // Set success message
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-lg mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-4">Register New Grade</h1>
        <?php if ($successMessage): // Check if there's a success message ?>
            <div class="mb-4 text-green-600">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        <form action="grade.php" method="POST" class="space-y-4">
            <!-- Grade -->
            <div>
                <label for="grade" class="block text-sm font-medium text-gray-700">Grade</label>
                <input type="number" name="grade" id="grade" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Increment -->
            <div>
                <label for="increment" class="block text-sm font-medium text-gray-700">Increment</label>
                <input type="number" step="0.01" name="increment" id="increment" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Scale -->
            <div>
                <label for="scale" class="block text-sm font-medium text-gray-700">Scale</label>
                <input type="number" step="0.01" name="scale" id="scale" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Grade Percentage -->
            <div>
                <label for="gradePercentage" class="block text-sm font-medium text-gray-700">Grade Percentage</label>
                <input type="number" step="0.01" name="gradePercentage" id="gradePercentage" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <button type="submit" class="w-full bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600">Register Grade</button>
            </div>
        </form>
    </div>
</body>
</html>
