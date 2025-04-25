<?php
// Include the database connection
include '../db_conn.php';

// Initialize variables for messages
$success_message = '';
$error_message = '';

// Handle form submission for adding a new additional duty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_duty'])) {
    $designation = htmlspecialchars($_POST['designation']);
    $addSalary = (float) htmlspecialchars($_POST['addSalary']);

    // Insert into addDuty table
    $stmt = $conn->prepare("INSERT INTO addDuty (designation, addSalary) VALUES (?, ?)");
    $stmt->bind_param("sd", $designation, $addSalary);

    if ($stmt->execute()) {
        $success_message = "Additional duty added successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle update request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_duty'])) {
    $id = (int) $_POST['id'];
    $designation = htmlspecialchars($_POST['designation']);
    $addSalary = (float) htmlspecialchars($_POST['addSalary']);

    // Update query for the selected entry
    $stmt = $conn->prepare("UPDATE addDuty SET designation = ?, addSalary = ? WHERE id = ?");
    $stmt->bind_param("sdi", $designation, $addSalary, $id);

    if ($stmt->execute()) {
        $success_message = "Additional duty updated successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];

    // Delete query
    $stmt = $conn->prepare("DELETE FROM addDuty WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success_message = "Additional duty deleted successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch data for displaying
$result = $conn->query("SELECT * FROM addDuty");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Additional Duty Management</title>

    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- DaisyUI CDN -->
    <script src="https://cdn.jsdelivr.net/npm/daisyui@1.9.3/dist/full.js"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-center text-gray-700 mb-6">Additional Duty Management</h1>

        <!-- Success or error messages -->
        <?php if ($success_message) { echo "<div class='alert alert-success mb-4'>$success_message</div>"; } ?>
        <?php if ($error_message) { echo "<div class='alert alert-error mb-4'>$error_message</div>"; } ?>

        <!-- Form to add a new additional duty -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Add Additional Duty</h2>
        <form action="" method="post" class="space-y-4">
            <div class="form-control">
                <label for="designation" class="label">Designation:</label>
                <input type="text" name="designation" required class="input input-bordered w-full">
            </div>
            <div class="form-control">
                <label for="addSalary" class="label">Additional Salary:</label>
                <input type="number" step="0.01" name="addSalary" required class="input input-bordered w-full">
            </div>
            <button type="submit" name="add_duty" class="btn btn-primary w-full">Add Duty</button>
        </form>

        <!-- Additional Duty List -->
        <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-4">Additional Duty List</h2>
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Designation</th>
                    <th>Additional Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td id="designation-<?= $row['id'] ?>"><?= $row['designation'] ?></td>
                        <td id="salary-<?= $row['id'] ?>"><?= $row['addSalary'] ?></td>
                        <td class="flex space-x-2">
                            <!-- Update button that triggers the modal -->
                            <button type="button" class="btn btn-info btn-sm" onclick="showUpdateForm(<?= $row['id'] ?>, '<?= $row['designation'] ?>', <?= $row['addSalary'] ?>)">Update</button>
                            
                            <!-- Delete button that triggers the delete confirmation modal -->
                            <button type="button" class="btn btn-error btn-sm" onclick="showDeleteModal(<?= $row['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Update Form -->
    <div id="update-modal" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Update Additional Duty</h2>
            <form action="" method="post" id="update-form" class="space-y-4">
                <input type="hidden" name="id" id="update-id">
                <div class="form-control">
                    <label for="designation" class="label">Designation:</label>
                    <input type="text" name="designation" id="update-designation" required class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label for="addSalary" class="label">Additional Salary:</label>
                    <input type="number" name="addSalary" id="update-addSalary" step="0.01" required class="input input-bordered w-full">
                </div>
                <div class="flex justify-between">
                    <button type="submit" name="update_duty" class="btn btn-primary w-1/2">Save</button>
                    <button type="button" onclick="cancelUpdate()" class="btn btn-secondary w-1/2">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Are you sure you want to delete this record?</h2>
            <form action="" method="get" id="delete-form" class="flex justify-between space-x-4">
                <input type="hidden" name="delete_id" id="delete-id">
                <button type="submit" class="btn btn-error w-1/2">Yes, Delete</button>
                <button type="button" onclick="cancelDelete()" class="btn btn-secondary w-1/2">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        // Function to show the update form modal
        function showUpdateForm(id, designation, addSalary) {
            document.getElementById('update-id').value = id;
            document.getElementById('update-designation').value = designation;
            document.getElementById('update-addSalary').value = addSalary;
            document.getElementById('update-modal').classList.remove('hidden'); // Show modal
        }

        // Function to hide the update form modal
        function cancelUpdate() {
            document.getElementById('update-modal').classList.add('hidden'); // Hide modal
        }

        // Function to show the delete confirmation modal
        function showDeleteModal(id) {
            document.getElementById('delete-id').value = id;
            document.getElementById('delete-modal').classList.remove('hidden'); // Show modal
        }

        // Function to hide the delete confirmation modal
        function cancelDelete() {
            document.getElementById('delete-modal').classList.add('hidden'); // Hide modal
        }
    </script>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
