<?php
include '../view.php';
include '../db_conn.php';

// Retrieve all deductions from the database
$deductions = [];
$stmt = $conn->prepare("SELECT * FROM deductionList");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $deductions[] = $row;
}

$stmt->close();
$conn->close();

// Initialize success message for form submission
$successMessage = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include connection again in case form submission reloads the page
    include '../db_conn.php';

    // Prepare data for insertion
    $dedName = htmlspecialchars($_POST['dedName']);
    $dedPercentage = htmlspecialchars($_POST['dedPercentage']);
    $dedValue = htmlspecialchars($_POST['dedValue']);

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO deductionList (dedName, dedPercentage, dedValue) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $dedName, $dedPercentage, $dedValue);

    if ($stmt->execute()) {
        $successMessage = "Deduction added successfully!";
    } else {
        $successMessage = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deduction List</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../sideBar.css">
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include 'sideBar.php'; ?>
    </header>

    <!-- Main Content -->
    <div class="flex flex-col flex-grow ml-64">
        <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
            <?php include 'topBar.php'; ?>
        </aside>

        <!-- Deduction List Section -->
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="mx-auto bg-auto p-10">
                <!-- Success Message -->
                <?php if ($successMessage): ?>
                    <p class="text-green-600 mb-4"><?php echo $successMessage; ?></p>
                <?php endif; ?>

                <section class="flex justify-between mb-5">
                        <h1 class="text-2xl font-bold mb-4">Deduction List</h1>
                        <div class="flex gap-5">
                            <button id="addDeductionButton" class="btn btn-success text-white px-4 py-2 rounded hover:bg-green-700 flex items-center" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'disabled' : ''; ?> title="Only Admin and HR can access this page">
                                <i class="fas fa-plus mr-2"></i> Add Deduction
                            </button>
                            <a href="dedChange.php" class="btn btn-info"  <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'disabled' : ''; ?> title="Only Admin and HR can access this page">Action</a>
                        </div>
                    </section>

                <!-- Deduction Table -->
                <?php if (empty($deductions)): ?>
                    <p class="text-gray-600 mt-4">No deductions found.</p>
                <?php else: ?>
                    <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                        <thead class="bg-gray-200">
                            <tr class="w-full">
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold border-r border-gray-300 w-1/3">Deduction Name</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold border-r border-gray-300 w-1/3">Deduction Percentage</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold border-r border-gray-300 w-1/3">Deduction Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($deductions as $deduction): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-6 py-4 border-r border-gray-200"><?php echo htmlspecialchars($deduction['dedName']); ?></td>
                                    <td class="px-6 py-4 border-r border-gray-200"><?php echo htmlspecialchars($deduction['dedPercentage']); ?>%</td>
                                    <td class="px-6 py-4 border-r border-gray-200"><?php echo htmlspecialchars($deduction['dedValue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal for Adding Deduction -->
    <div id="addDeductionModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="bg-blue-100 rounded-lg shadow-lg p-6 w-96 border">
            <h2 class="text-lg font-bold mb-4">Add New Deduction</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label for="dedName" class="block text-sm font-medium">Deduction Name</label>
                    <input type="text" name="dedName" id="dedName" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="dedPercentage" class="block text-sm font-medium">Deduction Percentage</label>
                    <input type="number" name="dedPercentage" id="dedPercentage" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="dedValue" class="block text-sm font-medium">Deduction Value</label>
                    <input type="number" name="dedValue" id="dedValue" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Add Deduction</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Show/Hide Modal
        document.getElementById('addDeductionButton').onclick = function() {
            document.getElementById('addDeductionModal').classList.remove('hidden');
        };
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('addDeductionModal').classList.add('hidden');
        };
    </script>
</body>
</html>
