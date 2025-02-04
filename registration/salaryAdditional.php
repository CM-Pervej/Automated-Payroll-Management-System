<?php
// Include the database connection file
include '../db_conn.php'; // Adjust the path based on your directory structure

// Initialize success and error messages
$success_message = '';
$error_message = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $designation = $_POST['designation'];
    $addSalary = floatval($_POST['addSalary']);
    $telephoneAllw = isset($_POST['telephone']) ? $_POST['telephone'] : [];

    // Prepare and bind SQL statement for adding salary
    $stmt = $conn->prepare("INSERT INTO addDuty (designation, addSalary) VALUES (?, ?)");
    $stmt->bind_param("sd", $designation, $addSalary);

    // Execute the statement
    if ($stmt->execute()) {
        $last_id = $stmt->insert_id; // Get the last inserted ID for the telephone numbers

        // Insert telephone numbers into the database
        $telephone_stmt = $conn->prepare("INSERT INTO telephoneAllw (addDuty_id, telephoneAllw) VALUES (?, ?)");
        foreach ($telephoneAllw as $telephone) {
            $telephone_stmt->bind_param("is", $last_id, $telephone);
            $telephone_stmt->execute();
        }
        $telephone_stmt->close();

        $success_message = "Additional salary registered successfully!";
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
    <title>Additional Salary Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function addTelephoneField() {
            const container = document.getElementById('telephone-fields');
            const inputCount = container.children.length + 1;
            const newField = document.createElement('div');
            newField.className = 'mb-4';
            newField.innerHTML = `
                <label for="telephone${inputCount}" class="block text-lg font-semibold mb-2">Telephone ${inputCount}</label>
                <input type="number" step="0.01" id="telephone${inputCount}" name="telephone[]" 
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
            `;
            container.appendChild(newField);
        }

        function removeTelephoneField() {
            const container = document.getElementById('telephone-fields');
            if (container.children.length > 1) {
                container.removeChild(container.lastChild);
            }
        }
    </script>
</head>
<body class="bg-gray-100 py-10">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-center mb-6">Register Additional Salary</h2>

        <?php if ($success_message): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4"><?php echo $success_message; ?></div>
        <?php elseif ($error_message): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="post" class="space-y-6">
            <!-- Additional Designation -->
            <div>
                <label for="designation" class="block text-lg font-semibold mb-2">Additional Designation</label>
                <input type="text" id="designation" name="designation" required 
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <!-- Additional Salary -->
            <div>
                <label for="addSalary" class="block text-lg font-semibold mb-2">Additional Salary</label>
                <input type="number" step="0.01" id="addSalary" name="addSalary" required 
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <!-- Telephone Fields Container -->
            <div id="telephone-fields" class="space-y-4">
                <div class="mb-4">
                    <label for="telephone1" class="block text-lg font-semibold mb-2">Telephone 1</label>
                    <input type="number" step="0.01" id="telephone1" name="telephone[]" required 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>

            <!-- Buttons to add/remove telephone fields -->
            <div class="flex space-x-4">
                <button type="button" onclick="addTelephoneField()" class="bg-blue-500 text-white py-2 px-4 rounded">Add Telephone</button>
                <button type="button" onclick="removeTelephoneField()" class="bg-red-500 text-white py-2 px-4 rounded">Remove Telephone</button>
            </div>

            <!-- Submit Button -->
            <div>
                <input type="submit" value="Submit" 
                    class="w-full bg-green-500 text-white py-3 rounded-lg hover:bg-green-600 transition duration-300 cursor-pointer">
            </div>
        </form>
    </div>

</body>
</html>
