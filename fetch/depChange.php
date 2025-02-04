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

// Initialize an array to hold the departments and faculties
$departments = [];
$faculties = [];

// Fetch all faculties for the dropdown
$faculty_stmt = $conn->prepare("SELECT id, faculty FROM faculty");
$faculty_stmt->execute();
$faculty_result = $faculty_stmt->get_result();

// Fetch all faculties into the array
while ($faculty_row = $faculty_result->fetch_assoc()) {
    $faculties[] = $faculty_row;
}

// Fetch all departments
$stmt = $conn->prepare("SELECT d.id, d.department_name, d.faculty_id, f.faculty FROM departments d JOIN faculty f ON d.faculty_id = f.id");
$stmt->execute();
$result = $stmt->get_result();

// Fetch all departments into the array
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

// Handle update and delete actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        // Update department
        $id = $_POST['id'];
        $department_name = htmlspecialchars($_POST['department_name']);
        $faculty_id = intval($_POST['faculty_id']);

        $stmt = $conn->prepare("UPDATE departments SET department_name = ?, faculty_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $department_name, $faculty_id, $id);

        if ($stmt->execute()) {
            $message = "<p class='text-green-600'>Department updated successfully!</p>";
        } else {
            $message = "<p class='text-red-600'>Error updating department: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        // Delete department
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $message = "<p class='text-red-600'>Department deleted successfully!</p>";
        } else {
            $message = "<p class='text-red-600'>Error deleting department: " . $stmt->error . "</p>";
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
    <title>Manage Departments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.14.0/dist/full.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="w-full mx-auto mt-10 p-8 bg-white rounded-lg shadow-lg border">
        <h1 class="text-3xl font-semibold text-center text-gray-800 mb-6">Manage Departments</h1>
        
        <?php if (isset($message)) echo $message; ?>

        <div class="overflow-x-auto px-20 mx-32">
            <table class="w-full text-left bg-gray-50 rounded-md shadow-lg">
                <thead>
                    <tr class="bg-indigo-600 text-white">
                        <th class="px-6 py-3 text-sm font-medium tracking-wide">Department Name</th>
                        <th class="px-6 py-3 text-sm font-medium tracking-wide">Faculty</th>
                        <th class="px-6 py-3 text-sm font-medium tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($departments as $department): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
                                <td class="px-4 py-2">
                                    <input type="text" name="department_name" value="<?php echo htmlspecialchars($department['department_name']); ?>" required 
                                           class="border rounded-lg p-2 w-full focus:outline-none focus:ring focus:ring-indigo-300"
                                           style="min-width: 150px;" maxlength="50">
                                </td>
                                <td class="px-4 py-2">
                                    <select name="faculty_id" required class="border rounded-lg p-2 w-full focus:outline-none focus:ring focus:ring-indigo-300">
                                        <?php foreach ($faculties as $faculty): ?>
                                            <option value="<?php echo $faculty['id']; ?>" <?php echo ($faculty['id'] == $department['faculty_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($faculty['faculty']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="px-4 py-2 flex space-x-2 justify-center">
                                    <input type="hidden" name="id" value="<?php echo $department['id']; ?>">
                                    <button type="submit" name="update" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg focus:ring-2 focus:ring-green-300 transition duration-150">Update</button>
                                    <button type="submit" name="delete" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg focus:ring-2 focus:ring-red-300 transition duration-150" onclick="return confirm('Are you sure you want to delete this department?');">Remove</button>
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
