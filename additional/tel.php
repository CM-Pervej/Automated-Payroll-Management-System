<?php
// Include the database connection
include '../db_conn.php';

// Initialize variables for messages
$success_message = '';
$error_message = '';

// Handle form submission for adding a new telephone allowance
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_telephone'])) {
    $telephoneAllw = (float) htmlspecialchars($_POST['telephoneAllw']);

    // Insert into add_telephone_list table
    $stmt = $conn->prepare("INSERT INTO add_telephone_list (telephoneAllw) VALUES (?)");
    $stmt->bind_param("d", $telephoneAllw);

    if ($stmt->execute()) {
        $success_message = "Telephone allowance added successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle update request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_telephone'])) {
    $id = (int) $_POST['id'];
    $telephoneAllw = (float) htmlspecialchars($_POST['telephoneAllw']);

    // Update query for the selected entry
    $stmt = $conn->prepare("UPDATE add_telephone_list SET telephoneAllw = ? WHERE id = ?");
    $stmt->bind_param("di", $telephoneAllw, $id);

    if ($stmt->execute()) {
        $success_message = "Telephone allowance updated successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];

    // Delete query
    $stmt = $conn->prepare("DELETE FROM add_telephone_list WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success_message = "Telephone allowance deleted successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch data for displaying
$result = $conn->query("SELECT * FROM add_telephone_list");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telephone Allowance Management</title>

    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- DaisyUI CDN -->
    <script src="https://cdn.jsdelivr.net/npm/daisyui@1.9.3/dist/full.js"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-center text-gray-700 mb-6">Telephone Allowance Management</h1>

        <!-- Success or error messages -->
        <?php if ($success_message) { echo "<div class='alert alert-success mb-4'>$success_message</div>"; } ?>
        <?php if ($error_message) { echo "<div class='alert alert-error mb-4'>$error_message</div>"; } ?>

        <!-- Form to add a new telephone allowance -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Add Telephone Allowance</h2>
        <form action="" method="post" class="space-y-4">
            <div class="form-control">
                <label for="telephoneAllw" class="label">Telephone Allowance:</label>
                <input type="number" step="0.01" name="telephoneAllw" required class="input input-bordered w-full">
            </div>
            <button type="submit" name="add_telephone" class="btn btn-primary w-full">Add Allowance</button>
        </form>

        <!-- Telephone Allowance List -->
        <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-4">Telephone Allowance List</h2>
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Telephone Allowance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td id="allowance-<?= $row['id'] ?>"><?= $row['telephoneAllw'] ?></td>
                        <td class="flex space-x-2">
                            <!-- Update button that triggers the modal -->
                            <button type="button" class="btn btn-info btn-sm" onclick="showUpdateForm(<?= $row['id'] ?>, <?= $row['telephoneAllw'] ?>)">Update</button>
                            
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
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Update Telephone Allowance</h2>
            <form action="" method="post" id="update-form" class="space-y-4">
                <input type="hidden" name="id" id="update-id">
                <div class="form-control">
                    <label for="telephoneAllw" class="label">Telephone Allowance:</label>
                    <input type="number" name="telephoneAllw" id="update-telephoneAllw" step="0.01" required class="input input-bordered w-full">
                </div>
                <div class="flex justify-between">
                    <button type="submit" name="update_telephone" class="btn btn-primary w-1/2">Save</button>
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
        function showUpdateForm(id, value) {
            document.getElementById('update-id').value = id;
            document.getElementById('update-telephoneAllw').value = value;
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
