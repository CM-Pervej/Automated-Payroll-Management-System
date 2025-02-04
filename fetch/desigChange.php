<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not authenticated
    header('Location: ../index.php');
    exit();
}

// Include the database connection
include '../db_conn.php';

// Initialize an array to hold the designations
$designations = [];

// Fetch all designations
$stmt = $conn->prepare("SELECT * FROM designations");
$stmt->execute();
$result = $stmt->get_result();

// Fetch all designations into the array
while ($row = $result->fetch_assoc()) {
    $designations[] = $row;
}

// Handle update and delete actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        // Update designation
        $id = $_POST['id'];
        $designation = htmlspecialchars($_POST['designation']);

        $stmt = $conn->prepare("UPDATE designations SET designation = ? WHERE id = ?");
        $stmt->bind_param("si", $designation, $id);

        if ($stmt->execute()) {
            $message = "<p class='text-green-600'>Designation updated successfully!</p>";
        } else {
            $message = "<p class='text-red-600'>Error updating designation: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        // Delete designation
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM designations WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $message = "<p class='text-red-600'>Designation deleted successfully!</p>";
        } else {
            $message = "<p class='text-red-600'>Error deleting designation: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Designations</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.14.0/dist/full.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="w-full mx-auto mt-10 p-8 bg-white rounded-lg shadow-lg border">
        <h1 class="text-3xl font-semibold text-center text-gray-800 mb-6">Manage Designations</h1>
        
        <?php if (isset($message)) echo $message; ?>

        <div class="overflow-x-auto px-20 mx-32">
            <table class="w-full text-left bg-gray-50 rounded-md shadow-lg">
                <thead>
                    <tr class="bg-indigo-600 text-white">
                        <th class="px-6 py-3 text-sm font-medium tracking-wide">Designation</th>
                        <th class="px-6 py-3 text-sm font-medium tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($designations as $designation): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                                <td class="px-4 py-2">
                                    <input type="text" name="designation" value="<?php echo htmlspecialchars($designation['designation']); ?>" required 
                                           class="border rounded-lg p-2 w-full focus:outline-none focus:ring focus:ring-indigo-300"
                                           style="min-width: 150px;" maxlength="50">
                                </td>
                                <td class="px-4 py-2 flex space-x-2 justify-center">
                                    <input type="hidden" name="id" value="<?php echo $designation['id']; ?>">
                                    <button type="submit" name="update" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg focus:ring-2 focus:ring-green-300 transition duration-150">Update</button>
                                    <button type="submit" name="delete" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg focus:ring-2 focus:ring-red-300 transition duration-150" onclick="return confirm('Are you sure you want to delete this designation?');">Remove</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
