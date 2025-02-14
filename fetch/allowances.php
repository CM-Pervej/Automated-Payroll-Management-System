<?php
include '../view.php';
include '../db_conn.php';

$allowances = [];

// Fetch allowances to display in the table
$stmt = $conn->prepare("SELECT * FROM allowanceList");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $allowances[] = $row;
}

$stmt->close();

// Initialize a variable to hold the success message
$successMessage = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize inputs
    $allwName = htmlspecialchars($_POST['allwName']);
    $allwPercentage = (float) htmlspecialchars($_POST['allwPercentage']);
    $allwValue = (float) htmlspecialchars($_POST['allwValue']);

    // Prepare an SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO allowanceList (allwName, allwPercentage, allwValue) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $allwName, $allwPercentage, $allwValue);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        $successMessage = "Allowance registered successfully!";
        // Refresh the page to see the new entry and clear POST data
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Allowance List</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../sideBar.css">
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar and Header -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include 'sideBar.php'; ?>
    </header>
    <div class="flex flex-col flex-grow ml-64">
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include 'topBar.php'; ?>
            </aside>
        </div>

        <!-- Main Content Area -->
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="mx-auto bg-white p-10">
                <!-- Success Message -->
                <?php if (!empty($successMessage)): ?>
                    <div class="text-green-700 bg-green-100 p-4 rounded mb-4 font-bold">
                        <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>

                <section class="flex justify-between mb-5">
                    <h1 class="text-2xl font-bold mb-4">Allowance List</h1>
                    <div class="flex gap-5">
                        <button id="addAllowanceButton" class="btn btn-success text-white px-4 py-2 rounded hover:bg-green-700 flex items-center" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'disabled' : ''; ?> title="Only Admin and HR can access this page">
                            <i class="fas fa-plus mr-2"></i> Add Allowance
                        </button>
                        <a href="allwChange.php" class="btn btn-info" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'disabled' : ''; ?> title="Only Admin and HR can access this page">Action</a>
                    </div>
                </section>

                <!-- Allowance List Table -->
                <?php if (empty($allowances)): ?>
                    <p class="text-gray-600">No allowances found.</p>
                <?php else: ?>
                    <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                        <thead class="bg-gray-200">
                            <tr class="w-full">
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold border-r border-gray-300 w-1/3">Allowance Name</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold border-r border-gray-300 w-1/3">Allowance Percentage</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold w-1/3">Allowance Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($allowances as $allowance): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-6 py-4 border-r border-gray-200"><?php echo htmlspecialchars($allowance['allwName']); ?></td>
                                    <td class="px-6 py-4 border-r border-gray-200"><?php echo htmlspecialchars($allowance['allwPercentage']); ?>%</td>
                                    <td class="px-6 py-4 border-r border-gray-200"><?php echo htmlspecialchars($allowance['allwValue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal for Adding Allowance -->
    <div id="addAllowanceModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="bg-blue-100 rounded-lg shadow-lg p-6 w-96 border">
            <h2 class="text-lg font-bold mb-4">Add New Allowance</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label for="allwName" class="block text-sm font-medium text-gray-700">Allowance Name</label>
                    <input type="text" name="allwName" id="allwName" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="allwPercentage" class="block text-sm font-medium text-gray-700">Allowance Percentage</label>
                    <input type="number" step="0.01" name="allwPercentage" id="allwPercentage" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="allwValue" class="block text-sm font-medium text-gray-700">Allowance Value</label>
                    <input type="number" step="0.01" name="allwValue" id="allwValue" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Add Allowance</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Modal -->
    <script>
        document.getElementById('addAllowanceButton').onclick = function() {
            document.getElementById('addAllowanceModal').classList.remove('hidden');
        };
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('addAllowanceModal').classList.add('hidden');
        };
    </script>
</body>
</html>
