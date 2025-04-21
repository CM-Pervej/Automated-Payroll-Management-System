<?php
include '../view.php';
include '../db_conn.php';

// Initialize message variable
$message = '';

// Handle add new designation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $designation = htmlspecialchars($_POST['designation']);

    // Insert the new designation into the database
    $stmt = $conn->prepare("INSERT INTO designations (designation) VALUES (?)");
    $stmt->bind_param("s", $designation);

    if ($stmt->execute()) {
        $message = "New designation added successfully!";
    } else {
        $message = "Error adding designation: " . $stmt->error;
    }

    $stmt->close();
}

// Handle update designation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $designation = htmlspecialchars($_POST['designation']);

    $stmt = $conn->prepare("UPDATE designations SET designation = ? WHERE id = ?");
    $stmt->bind_param("si", $designation, $id);

    if ($stmt->execute()) {
        $message = "Designation updated successfully!";
    } else {
        $message = "Error updating designation: " . $stmt->error;
    }

    $stmt->close();
}

// Handle delete designation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM designations WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Designation deleted successfully!";
    } else {
        $message = "Error deleting designation: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all designations
$designations = [];
$stmt = $conn->prepare("SELECT * FROM designations");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $designations[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Designation Management</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../sideBar.css">
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar (fixed) -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include '../sideBar.php'; ?>
    </header>

    <!-- Main Content Area -->
    <div class="flex flex-col flex-grow ml-64">
        <!-- Top Bar (fixed) -->
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include '../topBar.php'; ?>
            </aside>
        </div>
        
        <!-- Content Section -->
        <main class="flex-grow p-8 mt-5 bg-white shadow-lg overflow-auto">
            <div class="mx-auto bg-white p-10">
                <!-- Display message -->
                <?php if ($message) : ?>
                    <p class="text-green-600 mb-4"><?= $message; ?></p>
                <?php endif; ?>

                <div class="flex justify-between">
                    <h1 class="text-2xl font-bold mb-4">Designation Management</h1>
                    <!-- Add New Designation Form -->
                    <button id="addDesignationButton" class="btn btn-success text-white px-4 py-2 rounded hover:bg-green-700 mb-4" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">
                        <i class="fas fa-plus mr-2"></i> Add Designation
                    </button>
                </div>

                <!-- Designations List -->
                <div id="designationList" class="mb-8">
                    <?php if (empty($designations)) : ?>
                        <p class="text-gray-600">No designations found.</p>
                    <?php else : ?>
                        <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                            <thead class="bg-gray-400">
                                <tr>
                                    <th class="px-6 py-4 text-left text-gray-700">S/N</th>
                                    <th class="px-6 py-4 text-left text-gray-700 border-l">Designation Name</th>
                                    <th class="px-6 py-4 text-left text-gray-700 border-l" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $serial = 1; ?>
                                <?php foreach ($designations as $designation) : ?>
                                    <tr class="hover:bg-gray-100">
                                        <td class="px-6 py-4"><?php echo $serial++; ?></td>
                                        <td class="px-6 py-4 border-l">
                                            <a href="group.php?designation_id=<?= htmlspecialchars($designation['id']); ?>" class="text-blue-600 hover:underline">
                                                <?= htmlspecialchars($designation['designation']); ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 flex gap-4 border-l" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">
                                            <!-- Update Button -->
                                            <button data-id="<?= $designation['id']; ?>" data-name="<?= $designation['designation']; ?>" class="updateBtn text-green-600 hover:text-green-700">Update</button>
                                            <!-- Delete Button -->
                                            <button data-id="<?= $designation['id']; ?>" data-name="<?= $designation['designation']; ?>" class="deleteBtn text-red-600 hover:text-red-700">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Add/Update Designation Modal -->
                <div id="modal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <h2 class="text-lg font-bold mb-4" id="modalTitle">Add New Designation</h2>
                        <form method="POST">
                            <input type="hidden" name="id" id="designationId">
                            <div>
                                <input type="text" id="designation" name="designation" required 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div class="flex justify-end mt-4">
                                <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                                <button type="submit" name="add" class="bg-green-600 text-white px-4 py-2 rounded" id="submitBtn">Add</button>
                                <button type="submit" name="update" class="bg-blue-600 text-white px-4 py-2 rounded hidden" id="updateBtn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="deleteModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <h2 class="text-lg font-bold mb-4">Are you sure you want to delete this designation?</h2>
                        <form method="POST">
                            <input type="hidden" name="id" id="deleteDesignationId">
                            <div class="flex justify-end mt-4">
                                <button type="button" id="closeDeleteModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                                <button type="submit" name="delete" class="bg-red-600 text-white px-4 py-2 rounded">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Open modal for adding new designation
        document.getElementById('addDesignationButton').onclick = function() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add New Designation';
            document.getElementById('submitBtn').classList.remove('hidden');
            document.getElementById('updateBtn').classList.add('hidden');
            document.getElementById('modal').classList.remove('hidden');
        };

        // Close modal
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('modal').classList.add('hidden');
        };

        // Open modal for updating a designation
        const updateBtns = document.querySelectorAll('.updateBtn');
        updateBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                document.getElementById('modal').classList.remove('hidden');
                document.getElementById('modalTitle').textContent = 'Update Designation';
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('updateBtn').classList.remove('hidden');
                
                document.getElementById('designationId').value = id;
                document.getElementById('designation').value = name;
            };
        });

        // Open delete confirmation modal
        const deleteBtns = document.querySelectorAll('.deleteBtn');
        deleteBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteDesignationId').value = id;
            };
        });

        // Close delete confirmation modal
        document.getElementById('closeDeleteModal').onclick = function() {
            document.getElementById('deleteModal').classList.add('hidden');
        };
    </script>
</body>
</html>
