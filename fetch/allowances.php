<?php
include '../view.php';
include '../db_conn.php';

// Initialize message variable
$message = '';

// Handle add new allowance
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $allwName = htmlspecialchars($_POST['allwName']);
    $allwPercentage = htmlspecialchars($_POST['allwPercentage']);
    $allwValue = htmlspecialchars($_POST['allwValue']);

    // Insert the new allowance into the database
    $stmt = $conn->prepare("INSERT INTO allowanceList (allwName, allwPercentage, allwValue) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $allwName, $allwPercentage, $allwValue);

    if ($stmt->execute()) {
        $message = "New allowance added successfully!";
    } else {
        $message = "Error adding allowance: " . $stmt->error;
    }

    $stmt->close();
}

// Handle update allowance
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $allwName = htmlspecialchars($_POST['allwName']);
    $allwPercentage = htmlspecialchars($_POST['allwPercentage']);
    $allwValue = htmlspecialchars($_POST['allwValue']);

    $stmt = $conn->prepare("UPDATE allowanceList SET allwName = ?, allwPercentage = ?, allwValue = ? WHERE id = ?");
    $stmt->bind_param("sddi", $allwName, $allwPercentage, $allwValue, $id);

    if ($stmt->execute()) {
        $message = "Allowance updated successfully!";
    } else {
        $message = "Error updating allowance: " . $stmt->error;
    }

    $stmt->close();
}

// Handle delete allowance
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM allowanceList WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Allowance deleted successfully!";
    } else {
        $message = "Error deleting allowance: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all allowances
$allowances = [];
$stmt = $conn->prepare("SELECT * FROM allowanceList");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $allowances[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Allowance Management</title>
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
                    <h1 class="text-2xl font-bold mb-4">Allowance Management</h1>
                    <!-- Add New Allowance Form -->
                    <button id="addAllowanceButton" class="btn btn-success text-white px-4 py-2 rounded hover:bg-green-700 mb-4" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">
                        <i class="fas fa-plus mr-2"></i> Add Allowance
                    </button>
                </div>

                <!-- Allowance List -->
                <div id="allowanceList" class="mb-8">
                    <?php if (empty($allowances)) : ?>
                        <p class="text-gray-600">No allowances found.</p>
                    <?php else : ?>
                        <table class="w-full rounded-lg shadow-lg text-center">
                            <thead class="bg-gray-400">
                                <tr>
                                    <th class="text-gray-700 w-16">S/N</th>
                                    <th class="px-6 py-4 text-gray-700 border-l text-left">Allowance Name</th>
                                    <th class="w-32 text-gray-700 border-l px-6 whitespace-nowrap">Allowance Percentage</th>
                                    <th class="w-32 text-gray-700 border-l px-6 whitespace-nowrap">Allowance Value</th>
                                    <th class="w-32 text-gray-700 border-l" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $serial = 1; ?>
                                <?php foreach ($allowances as $allowance) : ?>
                                    <tr class="hover:bg-gray-100">
                                        <td class="text-center w-16"><?php echo $serial++; ?></td>
                                        <td class="px-6 border-l w-max text-left"><?= htmlspecialchars($allowance['allwName']); ?></td>
                                        <td class="px-6 py-4 border-l"><?= htmlspecialchars($allowance['allwPercentage']); ?>%</td>
                                        <td class="px-6 py-4 border-l"><?= htmlspecialchars($allowance['allwValue']); ?></td>
                                        <td class="flex gap-4 py-4 px-6 border-l" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'style="display:none;"' : ''; ?> title="Only Admin and HR can access this page">
                                            <button data-id="<?= $allowance['id']; ?>" data-name="<?= $allowance['allwName']; ?>" data-percentage="<?= $allowance['allwPercentage']; ?>" data-value="<?= $allowance['allwValue']; ?>" class="updateBtn text-green-600 hover:text-green-700">Update</button>
                                            <button data-id="<?= $allowance['id']; ?>" data-name="<?= $allowance['allwName']; ?>" class="deleteBtn text-red-600 hover:text-red-700">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Add/Update Allowance Modal -->
                <div id="modal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <h2 class="text-lg font-bold mb-4" id="modalTitle">Add New Allowance</h2>
                        <form method="POST">
                            <input type="hidden" name="id" id="allowanceId">
                            <div>
                                <label for="allwName">Name</label>
                                <input type="text" id="allowanceName" name="allwName" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">

                                <label for="allwPercentage">Percentage</label>
                                <input type="number" id="allowancePercentage" name="allwPercentage" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">

                                <label for="allwValue">Flat Value</label>
                                <input type="number" id="allowanceValue" name="allwValue" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div class="flex justify-end mt-4">
                                <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                                <button type="submit" name="add" class="bg-green-600 text-white px-4 py-2 rounded" id="submitBtn">Add Allowance</button>
                                <button type="submit" name="update" class="bg-blue-600 text-white px-4 py-2 rounded hidden" id="updateBtn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="deleteModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-gray-500 bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <h2 class="text-lg font-bold mb-4">Are you sure you want to delete this allowance?</h2>
                        <form method="POST">
                            <input type="hidden" name="id" id="deleteAllowanceId">
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
        // Open modal for adding new allowance
        document.getElementById('addAllowanceButton').onclick = function() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add New Allowance';
            document.getElementById('submitBtn').classList.remove('hidden');
            document.getElementById('updateBtn').classList.add('hidden');
            document.getElementById('modal').classList.remove('hidden');
        };

        // Close modal
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('modal').classList.add('hidden');
        };

        // Open modal for updating an allowance
        const updateBtns = document.querySelectorAll('.updateBtn');
        updateBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const percentage = this.getAttribute('data-percentage');
                const value = this.getAttribute('data-value');

                document.getElementById('modal').classList.remove('hidden');
                document.getElementById('modalTitle').textContent = 'Update Allowance';
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('updateBtn').classList.remove('hidden');
                
                document.getElementById('allowanceId').value = id;
                document.getElementById('allowanceName').value = name;
                document.getElementById('allowancePercentage').value = percentage;
                document.getElementById('allowanceValue').value = value;
            };
        });

        // Open delete confirmation modal
        const deleteBtns = document.querySelectorAll('.deleteBtn');
        deleteBtns.forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteAllowanceId').value = id;
            };
        });

        // Close delete confirmation modal
        document.getElementById('closeDeleteModal').onclick = function() {
            document.getElementById('deleteModal').classList.add('hidden');
        };
    </script>
</body>
</html>
