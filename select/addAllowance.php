<?php
include 'auth.php';
include '../db_conn.php';

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2 && $_SESSION['userrole_id'] != 4)) {
    header('Location: ../dashboard.php'); // Redirect to dashboard if not Admin
    exit();
}

$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

if ($employee_id <= 0) {
    echo "Invalid employee ID.";
    exit();
}

$successMessage = "";
$errorMessage = "";

// Fetch employee name and number
$employee_stmt = $conn->prepare("SELECT employeeNo, name, basic FROM employee WHERE id = ?");
$employee_stmt->bind_param("i", $employee_id);
$employee_stmt->execute();
$employee_stmt->bind_result($employeeNo, $employeeName, $basic);
$employee_stmt->fetch();
$employee_stmt->close();

if (!$employeeNo || !$employeeName) {
    echo "Employee not found.";
    exit();
}

// Handle removal of inserted allowances
if (isset($_POST['remove_allowances_btn'])) {
    if (isset($_POST['remove_allowances'])) {
        $remove_allowances = array_unique($_POST['remove_allowances']); // Remove duplicates

        foreach ($remove_allowances as $remove_allowance_id) {
            // Delete from allwConfirm
            $delete_confirm_stmt = $conn->prepare("DELETE FROM allwConfirm WHERE allowanceList_id = ? AND employee_id = ?");
            $delete_confirm_stmt->bind_param("ii", $remove_allowance_id, $employee_id);
            $delete_confirm_stmt->execute();
            $delete_confirm_stmt->close();

            // Delete from empAllowance
            $delete_stmt = $conn->prepare("DELETE FROM empAllowance WHERE employee_id = ? AND allowanceList_id = ?");
            $delete_stmt->bind_param("ii", $employee_id, $remove_allowance_id);
            if (!$delete_stmt->execute()) {
                $errorMessage = "Error removing allowance: " . $delete_stmt->error;
            }
            $delete_stmt->close();
        }
        $successMessage = "Allowances removed successfully!";
    } else {
        $errorMessage = "No allowances selected for removal.";
    }
}

// Handle insertion into allwConfirm
if (isset($_POST['confirm_allowances_btn']) && isset($_POST['allwTotal'])) {
    $allwTotalValues = $_POST['allwTotal'];

    // Prepare statements
    $delete_confirm_stmt = $conn->prepare("DELETE FROM allwConfirm WHERE empAllowance_id = ?");
    $insert_confirm_stmt = $conn->prepare("INSERT INTO allwConfirm (allwTotal, employee_id, empAllowance_id, allowanceList_id) 
                                           VALUES (?, ?, ?, ?) 
                                           ON DUPLICATE KEY UPDATE allwTotal = VALUES(allwTotal)");

    foreach (array_unique(array_keys($allwTotalValues)) as $allowanceListId) {
        $enteredTotal = $allwTotalValues[$allowanceListId];

        $allowance_stmt = $conn->prepare("SELECT em.id FROM empAllowance em WHERE em.employee_id = ? AND em.allowanceList_id = ?");
        $allowance_stmt->bind_param("ii", $employee_id, $allowanceListId);
        $allowance_stmt->execute();
        $allowance_stmt->store_result();

        if ($allowance_stmt->num_rows > 0) {
            $allowance_stmt->bind_result($empAllowanceId);
            $allowance_stmt->fetch();

             // Delete any existing entry in allwConfirm for this empAllowance_id
            $delete_confirm_stmt->bind_param("i", $empAllowanceId);
            $delete_confirm_stmt->execute();

            // Insert the new entry into allwConfirm table
            $insert_confirm_stmt->bind_param("diii", $enteredTotal, $employee_id, $empAllowanceId, $allowanceListId);
            if (!$insert_confirm_stmt->execute()) {
                $errorMessages[] = "Error confirming allowance: " . $insert_confirm_stmt->error;
            }
        }
        $allowance_stmt->close();
    }

     // Clean up statements
    $delete_confirm_stmt->close();
    $insert_confirm_stmt->close();

    if (empty($errorMessages)) {
        $successMessage = "Allowances confirmed successfully, with previous entries cleared!";

        // Redirect to profile.php with employee_id
        header("Location: ../profile.php?employee_id=$employee_id");
        exit();
    }
}

// Handle insertion of new or updated allowances
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['allowances'])) {
    $selected_allowances = array_unique($_POST['allowances']);
    $allowance_percentages = $_POST['allwPercentage'];
    $allowance_values = $_POST['allwValue'];

    if ($basic !== null) {
        // Prepare the statement for inserting/updating allowances
        $insert_allowance_stmt = $conn->prepare("INSERT INTO empAllowance (employee_id, allowanceList_id, allwPercentage, allwValue, allwTotal) 
                                                VALUES (?, ?, ?, ?, ?) 
                                                ON DUPLICATE KEY UPDATE allwPercentage = VALUES(allwPercentage), allwValue = VALUES(allwValue), allwTotal = VALUES(allwTotal)");

        foreach ($selected_allowances as $allowance_id) {
            // Fetch default values from the allowanceList table
            $allw_stmt = $conn->prepare("SELECT allwPercentage, allwValue FROM allowanceList WHERE id = ?");
            $allw_stmt->bind_param("i", $allowance_id);
            $allw_stmt->execute();
            $allw_stmt->bind_result($defaultPercentage, $defaultValue);
            $allw_stmt->fetch();
            $allw_stmt->close();

            // Determine values to insert
            $allwPercentage = isset($allowance_percentages[$allowance_id]) && $allowance_percentages[$allowance_id] != $defaultPercentage 
                            ? $allowance_percentages[$allowance_id] 
                            : $defaultPercentage;

            $allwValue = isset($allowance_values[$allowance_id]) && $allowance_values[$allowance_id] != $defaultValue 
                        ? $allowance_values[$allowance_id] 
                        : $defaultValue;

            // Calculate total
            $allwTotal = ceil($basic * ($allwPercentage / 100)) + $allwValue;

            // Execute the insert/update statement
            $insert_allowance_stmt->bind_param("iiddi", $employee_id, $allowance_id, $allwPercentage, $allwValue, $allwTotal);
            if (!$insert_allowance_stmt->execute()) {
                $errorMessage = "Error inserting allowance: " . $insert_allowance_stmt->error;
            }
        }
        $successMessage = "Allowances inserted/updated successfully!";
        $insert_allowance_stmt->close();
    }
}

// Fetch existing allowances for the employee
$existing_allowances_stmt = $conn->prepare("SELECT DISTINCT allowanceList_id FROM empAllowance WHERE employee_id = ?");
$existing_allowances_stmt->bind_param("i", $employee_id);
$existing_allowances_stmt->execute();
$existing_allowances_result = $existing_allowances_stmt->get_result();
$existing_allowances = [];
while ($row = $existing_allowances_result->fetch_assoc()) {
    $existing_allowances[] = $row['allowanceList_id'];
}
$existing_allowances_stmt->close();

// Fetch all allowances from the allowanceList table
$allowances = $conn->query("SELECT DISTINCT id, allwName, allwPercentage, allwValue FROM allowanceList");

// Calculate total allowance
$total_allowance_stmt = $conn->prepare("SELECT SUM(allwTotal) AS total FROM empAllowance WHERE employee_id = ?");
$total_allowance_stmt->bind_param("i", $employee_id);
$total_allowance_stmt->execute();
$total_allowance_stmt->bind_result($total_allowance);
$total_allowance_stmt->fetch();
$total_allowance_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Allowances</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="sideBar.css">
    <script>
        function toggleSelectAll(source, name) {
            checkboxes = document.getElementsByName(name);
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
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
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="w-full max-w-4xl mx-auto p-8 bg-white rounded-lg shadow-lg border">
                <h1 class="text-3xl font-semibold mb-4 text-gray-800">Manage Allowances for <?php echo htmlspecialchars($employeeName); ?> (<?php echo htmlspecialchars($employeeNo); ?>)</h1>

                <!-- Success/Error Messages -->
                <?php if ($successMessage): ?>
                    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg shadow">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>
                <?php if ($errorMessage): ?>
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg shadow">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <h2 class="text-2xl font-semibold mb-4 text-gray-700">Confirm Allowances</h2>
                    <div class="overflow-auto">
                        <table class="w-full table-auto border-collapse border border-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left border">
                                        <input type="checkbox" onclick="toggleSelectAll(this, 'remove_allowances[]')">
                                    </th>
                                    <th class="px-4 py-2 text-left border">Allowance Name</th>
                                    <th class="px-4 py-2 text-left border">Percentage</th>
                                    <th class="px-4 py-2 text-left border">Value</th>
                                    <th class="px-4 py-2 text-left border">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // Fetch confirmed allowances for the employee, ordered by allowanceList_id in ascending order
                                    $confirmed_allowances_stmt = $conn->prepare("SELECT a.allwName, em.allwPercentage, em.allwValue, em.allwTotal, em.allowanceList_id 
                                                                                FROM empAllowance em 
                                                                                JOIN allowanceList a ON em.allowanceList_id = a.id 
                                                                                WHERE em.employee_id = ? ORDER BY em.allowanceList_id ASC");
                                    $confirmed_allowances_stmt->bind_param("i", $employee_id);
                                    $confirmed_allowances_stmt->execute();
                                    $confirmed_allowances_result = $confirmed_allowances_stmt->get_result();

                                    while ($row = $confirmed_allowances_result->fetch_assoc()):
                                ?>
                                    <tr class="border-b hover:bg-indigo-50 transition duration-150 ease-in-out">
                                        <td class="px-4 py-2 border">
                                            <input type="checkbox" name="remove_allowances[]" value="<?php echo $row['allowanceList_id']; ?>" />
                                        </td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['allwName']); ?></td>
                                        <td class="px-4 py-2 text-gray-700 border"><?php echo htmlspecialchars($row['allwPercentage']); ?></td>
                                        <td class="px-4 py-2 text-gray-700 border"><?php echo number_format($row['allwValue'], 2); ?></td>
                                        <td class="px-4 py-2 border">
                                            <input type="text" name="allwTotal[<?php echo $row['allowanceList_id']; ?>]" 
                                                value="<?php echo $row['allwTotal']; ?>" 
                                                class="border p-2 w-full rounded-md" />
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" name="remove_allowances_btn" class="mt-4 p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-150">Remove Selected Allowances</button>
                        <button type="submit" name="confirm_allowances_btn" class="mt-4 p-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition duration-150">Confirm Selected Allowances</button>
                    </div>
                </form>

                <form action="addAllowance.php?employee_id=<?php echo $employee_id; ?>" method="POST" class="mt-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700">Available Allowances</h2>
                    <table class="min-w-full divide-y divide-gray-200 mb-4 border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left border">
                                    <input type="checkbox" onclick="toggleSelectAll(this, 'allowances[]')">
                                </th>
                                <th class="px-4 py-2 text-left border">Allowance Name</th>
                                <th class="px-4 py-2 text-left border">Percentage</th>
                                <th class="px-4 py-2 text-left border">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $allowances->fetch_assoc()): ?>
                                <?php if (!in_array($row['id'], $existing_allowances)): ?>
                                    <tr class="border-b hover:bg-indigo-50 transition duration-150 ease-in-out">
                                        <td class="px-4 py-2 border">
                                            <input type="checkbox" name="allowances[]" value="<?php echo $row['id']; ?>">
                                        </td>
                                        <td class="px-4 py-2 border text-gray-700"><?php echo htmlspecialchars($row['allwName']); ?></td>
                                        <td class="px-4 py-2 border">
                                            <input type="text" name="allwPercentage[<?php echo $row['id']; ?>]" 
                                                value="<?php echo $row['allwPercentage']; ?>" 
                                                class="border p-2 w-full rounded-md" />
                                        </td>
                                        <td class="px-4 py-2 border">
                                            <input type="text" name="allwValue[<?php echo $row['id']; ?>]" 
                                                value="<?php echo $row['allwValue']; ?>" 
                                                class="border p-2 w-full rounded-md" />
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <button type="submit" class="w-full bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 transition duration-200 ease-in-out">Select Allowances</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
