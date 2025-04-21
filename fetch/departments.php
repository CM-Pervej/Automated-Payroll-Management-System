<?php
include '../view.php';
include '../db_conn.php';

// Initialize messages
$success_message = '';
$error_message = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['department_name'])) {
        // Add Department
        $department_name = trim($_POST['department_name']);
        $stmt = $conn->prepare("INSERT INTO departments (department_name) VALUES (?)");
        $stmt->bind_param("s", $department_name);
        if ($stmt->execute()) {
            $success_message = "New department added successfully";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all departments for display
$departments = [];
$stmt = $conn->prepare("SELECT id, department_name FROM departments");
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
    <title>Department List</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../sideBar.css">
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include '../sideBar.php'; ?>
    </header>

    <div class="flex flex-col flex-grow ml-64">
        <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
            <?php include '../topBar.php'; ?>
        </aside>

        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="mx-auto bg-white p-10">
                <section class="flex justify-between mb-5">
                    <h1 class="text-2xl font-bold mb-4">Departments List</h1>
                    <div class="flex justify-end gap-5">
                        <button id="addDepartmentButton" class="btn btn-success text-white px-4 py-2 rounded hover:bg-green-700 flex items-center" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">
                            <i class="fas fa-plus mr-2"></i> Add Department
                        </button>
                        <a href="depChange.php" class="btn btn-info" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">Action</a>
                    </div>
                </section>

                <?php if ($success_message): ?>
                    <p class="text-green-500"><?php echo $success_message; ?></p>
                <?php elseif ($error_message): ?>
                    <p class="text-red-500"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <?php if (empty($departments)): ?>
                    <p class="text-gray-600">No departments found.</p>
                <?php else: ?>
                    <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                        <thead class="bg-gray-400">
                            <tr>
                                <th class="px-6 py-4 text-left text-gray-700">S/N</th>
                                <th class="px-6 py-4 text-left text-gray-700">Department Name</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $serial = 1; ?>
                            <?php foreach ($departments as $department): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-6 py-4"><?php echo $serial++; ?></td>
                                    <td class="px-6 py-4 border-r">
                                        <a href="group.php?department_id=<?= htmlspecialchars($department['id']); ?>" class="text-blue-600 hover:underline">
                                            <?= htmlspecialchars($department['department_name']); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Department Modal -->
    <div id="addDepartmentModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-lg font-bold mb-4">Add New Department</h2>
            <form action="" method="post" class="space-y-4">
                <input type="text" id="department_name" name="department_name" placeholder="Enter department name" required 
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal('addDepartmentModal')" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('addDepartmentButton').onclick = function() {
            document.getElementById('addDepartmentModal').classList.remove('hidden');
        };
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</body>
</html>
