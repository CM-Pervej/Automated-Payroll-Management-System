<?php
include '../view.php';
include '../db_conn.php';

// Initialize message variable
$message = '';

// Handle add new grade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $grade = (int) htmlspecialchars($_POST['grade']);
    $increment = htmlspecialchars($_POST['increment']);
    $scale = htmlspecialchars($_POST['scale']);
    $gradePercentage = htmlspecialchars($_POST['gradePercentage']);

    // Check if grade already exists
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM grade WHERE grade = ?");
    $check_stmt->bind_param("i", $grade);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        $message = "Error: Grade already exists!";
    } else {
        // Insert the data into the grade table
        $stmt = $conn->prepare("INSERT INTO grade (grade, increment, scale, gradePercentage) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisd", $grade, $increment, $scale, $gradePercentage);

        if ($stmt->execute()) {
            $message = "New Grade added successfully!";
        } else {
            $message = "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle update grade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $grade = (int) htmlspecialchars($_POST['grade']);
    $increment = htmlspecialchars($_POST['increment']);
    $scale = htmlspecialchars($_POST['scale']);
    $gradePercentage = htmlspecialchars($_POST['gradePercentage']);

    $stmt = $conn->prepare("UPDATE grade SET grade = ?, increment = ?, scale = ?, gradePercentage = ? WHERE id = ?");
    $stmt->bind_param("iisdi", $grade, $increment, $scale, $gradePercentage, $id);

    if ($stmt->execute()) {
        $message = "Grade updated successfully!";
    } else {
        $message = "Error updating grade: " . $stmt->error;
    }
    $stmt->close();
}

// Handle delete grade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM grade WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Grade deleted successfully!";
    } else {
        $message = "Error deleting grade: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all grades
$grades = [];
$stmt = $conn->prepare("SELECT * FROM grade");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar (fixed) -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include '../sideBar.php'; ?>
    </header>

    <div class="flex flex-col flex-grow ml-64">
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include '../topBar.php'; ?>
            </aside>
        </div>

        <main class="flex-grow p-8 mt-5 bg-white shadow-lg overflow-auto">
            <div class="mx-auto bg-white p-10">
                <section class="flex justify-between mb-5">
                    <h1 class="text-2xl font-bold mb-4">Grade Management</h1>
                    <div class="flex justify-end gap-5" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">
                        <button id="addGradeButton" class="btn btn-success text-white px-4 py-2 rounded hover:bg-green-700 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Grade
                        </button>
                    </div>
                </section>

                <!-- Display message -->
                <?php if ($message) : ?>
                    <p class="text-green-600 mb-4"><?= $message; ?></p>
                <?php endif; ?>

                <?php if (empty($grades)) : ?>
                    <p class="text-gray-600">No grades found.</p>
                <?php else : ?>
                    <table class="w-full rounded-lg shadow-lg">
                        <thead class="bg-gray-400">
                            <tr>
                                <th class="text-gray-700 w-16">S/N</th>
                                <th class="px-6 py-4 text-gray-700 border-l">Grade</th>
                                <th class="px-6 py-4 text-gray-700 border-l">Increment</th>
                                <th class="px-6 py-4 text-gray-700 border-l">Scale</th>
                                <th class="px-6 py-4 text-gray-700 border-l">Grade Percentage</th>
                                <th class="w-32 text-gray-700 border-l" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $serial = 1; ?>
                            <?php foreach ($grades as $grade) : ?>
                                <tr class="hover:bg-gray-100 text-center">
                                    <td class="text-center w-16"><?= $serial++; ?></td>
                                    <td class="border-l w-max">
                                        <a href="group.php?grade_id=<?= htmlspecialchars($grade['id']); ?>" class="text-blue-600 w-full block py-4 hover:scale-150 transform transition-all duration-150">
                                            <?= htmlspecialchars($grade['grade']); ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 border-l"><?= htmlspecialchars($grade['increment']); ?></td>
                                    <td class="px-6 py-4 border-l"><?= number_format($grade['scale'], 2, '.', ''); ?></td>
                                    <td class="px-6 py-4 border-l"><?= htmlspecialchars($grade['gradePercentage']); ?>%</td>
                                    <td class="flex gap-4 py-4 px-6 border-l" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">
                                        <!-- Update Button -->
                                        <button data-id="<?= $grade['id']; ?>" data-grade="<?= $grade['grade']; ?>" data-increment="<?= $grade['increment']; ?>" data-scale="<?= $grade['scale']; ?>" data-percentage="<?= $grade['gradePercentage']; ?>" class="updateBtn text-green-600 hover:text-green-700">Update</button>
                                        <!-- Delete Button -->
                                        <button data-id="<?= $grade['id']; ?>" class="deleteBtn text-red-600 hover:text-red-700">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add/Update Grade Modal -->
    <div id="modal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-lg font-bold mb-4" id="modalTitle">Add New Grade</h2>
            <form method="POST">
                <input type="hidden" name="id" id="gradeId">
                <div>
                    <label for="grade" class="block text-sm font-medium text-gray-700">Grade</label>
                    <input type="number" name="grade" id="grade" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="increment" class="block text-sm font-medium text-gray-700">Increment</label>
                    <input type="number" step="0.01" name="increment" id="increment" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="scale" class="block text-sm font-medium text-gray-700">Scale</label>
                    <input type="text" name="scale" id="scale" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="gradePercentage" class="block text-sm font-medium text-gray-700">Grade Percentage</label>
                    <input type="number" step="0.01" name="gradePercentage" id="gradePercentage" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
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
            <h2 class="text-lg font-bold mb-4">Are you sure you want to delete this grade?</h2>
            <form method="POST">
                <input type="hidden" name="id" id="deleteGradeId">
                <div class="flex justify-end mt-4">
                    <button type="button" id="closeDeleteModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                    <button type="submit" name="delete" class="bg-red-600 text-white px-4 py-2 rounded">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open modal for adding new grade
        document.getElementById('addGradeButton').onclick = function() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add New Grade';
            document.getElementById('submitBtn').classList.remove('hidden');
            document.getElementById('updateBtn').classList.add('hidden');
        };

        // Close modal
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('modal').classList.add('hidden');
        };

        // Open modal for updating a grade
        const updateBtns = document.querySelectorAll('.updateBtn');
        updateBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const grade = this.getAttribute('data-grade');
                const increment = this.getAttribute('data-increment');
                const scale = this.getAttribute('data-scale');
                const percentage = this.getAttribute('data-percentage');

                document.getElementById('modal').classList.remove('hidden');
                document.getElementById('modalTitle').textContent = 'Update Grade';
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('updateBtn').classList.remove('hidden');

                document.getElementById('gradeId').value = id;
                document.getElementById('grade').value = grade;
                document.getElementById('increment').value = increment;
                document.getElementById('scale').value = scale;
                document.getElementById('gradePercentage').value = percentage;
            };
        });

        // Open delete confirmation modal
        const deleteBtns = document.querySelectorAll('.deleteBtn');
        deleteBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteGradeId').value = id;
            };
        });

        // Close delete confirmation modal
        document.getElementById('closeDeleteModal').onclick = function() {
            document.getElementById('deleteModal').classList.add('hidden');
        };
    </script>
</body>
</html>
