<?php
include '../view.php';
include '../db_conn.php';

// Initialize message variable
$message = '';

// Handle add new department_name
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $department_name = htmlspecialchars($_POST['department_name']);

    // Insert the new department into the database
    $stmt = $conn->prepare("INSERT INTO departments (department_name) VALUES (?)");
    $stmt->bind_param("s", $department_name);

    if ($stmt->execute()) {
        $message = "New department added successfully!";
    } else {
        $message = "Error adding department: " . $stmt->error;
    }

    $stmt->close();
}

// Handle update department_name
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $department_name = htmlspecialchars($_POST['department_name']);

    $stmt = $conn->prepare("UPDATE departments SET department_name = ? WHERE id = ?");
    $stmt->bind_param("si", $department_name, $id);

    if ($stmt->execute()) {
        $message = "Department updated successfully!";
    } else {
        $message = "Error updating department: " . $stmt->error;
    }

    $stmt->close();
}

// Handle delete department_name
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Department deleted successfully!";
    } else {
        $message = "Error deleting department: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all departments
$departments = [];
$stmt = $conn->prepare("SELECT * FROM departments");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Department Management</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
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
                    <h1 class="text-2xl font-bold mb-4">Department Management</h1>
                    <!-- Add New Department Form -->
                    <button id="addDepartmentButton" class="btn btn-success text-white px-4 py-2 rounded hover:bg-green-700 mb-4" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">
                        <i class="fas fa-plus mr-2"></i> Add Department
                    </button>
                </div>

                <!-- Departments List -->
                <div id="departmentList" class="mb-8">
                    <?php if (empty($departments)) : ?>
                        <p class="text-gray-600">No departments found.</p>
                    <?php else : ?>
                        <table class="w-full rounded-lg shadow-lg text-center">
                            <thead class="bg-gray-400">
                                <tr>
                                    <th class="text-gray-700 w-16">S/N</th>
                                    <th class="px-6 py-4 text-gray-700 border-l">Department Name</th>
                                    <th class="w-32 text-gray-700 border-l" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $serial = 1; ?>
                                <?php foreach ($departments as $department) : ?>
                                    <tr class="hover:bg-gray-100">
                                        <td class="text-center w-16"><?php echo $serial++; ?></td>
                                        <td class="px-6 border-l w-max overflow-hidden">
                                            <a href="group.php?department_id=<?= htmlspecialchars($department['id']); ?>" class="text-blue-600 w-full block py-4 transform transition-all duration-150 hover:scale-125">
                                                <?= htmlspecialchars($department['department_name']); ?>
                                            </a>
                                        </td>
                                        <td class="flex gap-4 py-4 px-6 border-l" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">
                                            <button data-id="<?= $department['id']; ?>" data-name="<?= $department['department_name']; ?>" class="updateBtn text-green-600 hover:text-green-700">Update</button>
                                            <button data-id="<?= $department['id']; ?>" data-name="<?= $department['department_name']; ?>" class="deleteBtn text-red-600 hover:text-red-700">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Add/Update Department Modal -->
                <div id="modal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <h2 class="text-lg font-bold mb-4" id="modalTitle">Add New Department</h2>
                        <form method="POST">
                            <input type="hidden" name="id" id="departmentId">
                            <div>
                                <input type="text" id="department_name" name="department_name" required 
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
                        <h2 class="text-lg font-bold mb-4">Are you sure you want to delete this department?</h2>
                        <form method="POST">
                            <input type="hidden" name="id" id="deleteDepartmentId">
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
        // Open modal for adding new department
        document.getElementById('addDepartmentButton').onclick = function() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add New Department';
            document.getElementById('submitBtn').classList.remove('hidden');
            document.getElementById('updateBtn').classList.add('hidden');
        };

        // Close modal
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('modal').classList.add('hidden');
        };

        // Open modal for updating a department
        const updateBtns = document.querySelectorAll('.updateBtn');
        updateBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                document.getElementById('modal').classList.remove('hidden');
                document.getElementById('modalTitle').textContent = 'Update Department';
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('updateBtn').classList.remove('hidden');
                
                document.getElementById('departmentId').value = id;
                document.getElementById('department_name').value = name;
            };
        });

        // Open delete confirmation modal
        const deleteBtns = document.querySelectorAll('.deleteBtn');
        deleteBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteDepartmentId').value = id;
            };
        });

        // Close delete confirmation modal
        document.getElementById('closeDeleteModal').onclick = function() {
            document.getElementById('deleteModal').classList.add('hidden');
        };
    </script>
</body>
</html>
