<?php
include 'auth.php';
include '../db_conn.php';

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

// Handle removal of inserted deductions
if (isset($_POST['remove_deductions_btn'])) {
    if (isset($_POST['remove_deductions'])) {
        $remove_deductions = $_POST['remove_deductions'];

        foreach ($remove_deductions as $remove_deduction_id) {
            // First, delete from allwConfirm
            $delete_confirm_stmt = $conn->prepare("DELETE FROM dedConfirm WHERE empDeduction_id = (SELECT id FROM empDeduction WHERE employee_id = ? AND DeductionList_id = ?)");
            $delete_confirm_stmt->bind_param("ii", $employee_id, $remove_deduction_id);
            $delete_confirm_stmt->execute();
            $delete_confirm_stmt->close();

            // Then, delete from empAllowance
            $delete_stmt = $conn->prepare("DELETE FROM empDeduction WHERE employee_id = ? AND deductionList_id = ?");
            $delete_stmt->bind_param("ii", $employee_id, $remove_deduction_id);
            if (!$delete_stmt->execute()) {
                $errorMessage = "Error removing deduction: " . $delete_stmt->error;
            }
            $delete_stmt->close();
        }
        $successMessage = "Allowances removed successfully!";
    } else {
        $errorMessage = "No allowances selected for removal.";
    }
}

// Handle insertion into dedConfirm
if (isset($_POST['confirm_deductions_btn']) && isset($_POST['dedTotal'])) {
    $dedTotalValues = $_POST['dedTotal']; // Get manually entered allwTotal values

    // Prepare statements for deletion and insertion in dedConfirm table
    $delete_confirm_stmt = $conn->prepare("DELETE FROM dedConfirm WHERE empdeduction_id = ?");
    $insert_confirm_stmt = $conn->prepare("INSERT INTO dedConfirm (dedTotal, employee_id, empDeduction_id, deductionList_id) 
                                           VALUES (?, ?, ?, ?)
                                           ON DUPLICATE KEY UPDATE dedTotal = VALUES(dedTotal)");

    foreach (array_unique(array_keys($dedTotalValues)) as $deductionListId) {
        $enteredTotal = $dedTotalValues[$deductionListId];

        $deduction_stmt = $conn->prepare("SELECT em.id FROM empDeduction em WHERE em.employee_id = ? AND em.deductionList_id = ?");
        $deduction_stmt->bind_param("ii", $employee_id, $deductionListId);
        $deduction_stmt->execute();
        $deduction_stmt->store_result();
        
        if ($deduction_stmt->num_rows > 0) {
            $deduction_stmt->bind_result($empDeductionId);
            $deduction_stmt->fetch();

            // Delete any existing entry in dedConfirm for this empDeduction_id
            $delete_confirm_stmt->bind_param("i", $empDeductionId);
            $delete_confirm_stmt->execute();

            // Insert the new entry into dedConfirm table
            $insert_confirm_stmt->bind_param("diii", $enteredTotal, $employee_id, $empDeductionId, $deductionListId);
            if (!$insert_confirm_stmt->execute()) {
                $errorMessages[] = "Error confirming deduction: " . $insert_confirm_stmt->error;
            }
        }
        $deduction_stmt->close();
    }

    // Clean up statements
    $delete_confirm_stmt->close();
    $insert_confirm_stmt->close();

    if (empty($errorMessages)) {
        $successMessage = "Deductions confirmed successfully, with previous entries cleared!";
    }
}

// Handle insertion of new or updated deductions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deductions'])) {
    $selected_deductions = $_POST['deductions'];
    $deduction_percentages = $_POST['dedPercentage'];
    $deduction_values = $_POST['dedValue'];

    if ($basic !== null) {
        // Prepare the statement for inserting/updating deductions
        $insert_deduction_stmt = $conn->prepare("INSERT INTO empDeduction (employee_id, deductionList_id, dedPercentage, dedValue, dedTotal) 
                                                VALUES (?, ?, ?, ?, ?) 
                                                ON DUPLICATE KEY UPDATE dedPercentage = VALUES(dedPercentage), dedValue = VALUES(dedValue), dedTotal = VALUES(dedTotal)");

        foreach ($selected_deductions as $deduction_id) {
            // Fetch default values from the deductionList table
            $ded_stmt = $conn->prepare("SELECT dedPercentage, dedValue FROM deductionList WHERE id = ?");
            $ded_stmt->bind_param("i", $deduction_id);
            $ded_stmt->execute();
            $ded_stmt->bind_result($defaultPercentage, $defaultValue);
            $ded_stmt->fetch();
            $ded_stmt->close();

            // Determine values to insert
            $dedPercentage = isset($deduction_percentages[$deduction_id]) && $deduction_percentages[$deduction_id] != $defaultPercentage 
                            ? $deduction_percentages[$deduction_id] 
                            : $defaultPercentage;

            $dedValue = isset($deduction_values[$deduction_id]) && $deduction_values[$deduction_id] != $defaultValue 
                        ? $deduction_values[$deduction_id] 
                        : $defaultValue;

            // Calculate total
            $dedTotal = ceil($basic * ($dedPercentage / 100)) + $dedValue;

            // Execute the insert/update statement
            $insert_deduction_stmt->bind_param("iiddd", $employee_id, $deduction_id, $dedPercentage, $dedValue, $dedTotal);
            if (!$insert_deduction_stmt->execute()) {
                $errorMessage = "Error inserting deduction: " . $insert_deduction_stmt->error;
            }
        }
        $successMessage = "Deductions inserted/updated successfully!";
        $insert_deduction_stmt->close();
    }
}

// Fetch existing deductions for the employee
$existing_deductions_stmt = $conn->prepare("SELECT DISTINCT deductionList_id FROM empDeduction WHERE employee_id = ?");
$existing_deductions_stmt->bind_param("i", $employee_id);
$existing_deductions_stmt->execute();
$existing_deductions_result = $existing_deductions_stmt->get_result();
$existing_deductions = [];
while ($row = $existing_deductions_result->fetch_assoc()) {
    $existing_deductions[] = $row['deductionList_id'];
}
$existing_deductions_stmt->close();

// Fetch all deductions from the deductionList table
$deductions = $conn->query("SELECT DISTINCT id, dedName, dedPercentage, dedValue FROM deductionList");

// Calculate total deduction
$total_deduction_stmt = $conn->prepare("SELECT SUM(dedTotal) AS total FROM empDeduction WHERE employee_id = ?");
$total_deduction_stmt->bind_param("i", $employee_id);
$total_deduction_stmt->execute();
$total_deduction_stmt->bind_result($total_deduction);
$total_deduction_stmt->fetch();
$total_deduction_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Deductions</title>
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
        <?php include 'sideBar.php'; ?>
    </header>

    <!-- Main Content Area -->
    <div class="flex flex-col flex-grow ml-64">
        <!-- Top Bar (fixed) -->
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include 'topBar.php'; ?>
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
                    <h2 class="text-2xl font-semibold mb-4 text-gray-700">Current Assigned Deductions</h2>
                    <div class="overflow-auto mb-6">
                        <table class="w-full table-auto border-collapse border border-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left border">
                                        <input type="checkbox" onclick="toggleSelectAll(this, 'remove_deductions[]')">
                                    </th>
                                    <th class="px-4 py-2 text-left border">Deduction Name</th>
                                    <th class="px-4 py-2 text-left border">Percentage</th>
                                    <th class="px-4 py-2 text-left border">Value</th>
                                    <th class="px-4 py-2 text-left border">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // Fetch confirmed deductions for the employee, ordered by deductionList_id in ascending order
                                    $confirmed_deductions_stmt = $conn->prepare("SELECT d.dedName, em.dedPercentage, em.dedValue, em.dedTotal, em.deductionList_id 
                                                                                FROM empDeduction em 
                                                                                JOIN deductionList d ON em.deductionList_id = d.id 
                                                                                WHERE em.employee_id = ? ORDER BY em.deductionList_id ASC");
                                    $confirmed_deductions_stmt->bind_param("i", $employee_id);
                                    $confirmed_deductions_stmt->execute();
                                    $confirmed_deductions_result = $confirmed_deductions_stmt->get_result();

                                    while ($row = $confirmed_deductions_result->fetch_assoc()):
                                ?>
                                    <tr class="border-b hover:bg-indigo-50 transition duration-150 ease-in-out">
                                        <td class="px-4 py-2 border">
                                            <input type="checkbox" name="remove_deductions[]" value="<?php echo $row['deductionList_id']; ?>" />
                                        </td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['dedName']); ?></td>
                                        <td class="px-4 py-2 text-gray-700 border"><?php echo htmlspecialchars($row['dedPercentage']); ?></td>
                                        <td class="px-4 py-2 text-gray-700 border"><?php echo number_format($row['dedValue'], 2); ?></td>
                                        <td class="px-4 py-2 border">
                                            <input type="text" name="dedTotal[<?php echo $row['deductionList_id']; ?>]" 
                                                value="<?php echo $row['dedTotal']; ?>" 
                                                class="border p-2 w-full rounded-md" />
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" name="remove_deductions_btn" class="mt-4 p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-150">Remove Selected Deductions</button>
                        <button type="submit" name="confirm_deductions_btn" class="mt-4 p-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition duration-150">Confirm Selected Deductions</button>
                    </div>
                </form>

                <form action="addDeduction.php?employee_id=<?php echo $employee_id; ?>" method="POST" class="mt-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700">Available Deductions</h2>
                    <table class="min-w-full divide-y divide-gray-200 mb-4 border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left border">
                                    <input type="checkbox" onclick="toggleSelectAll(this, 'deductions[]')">
                                </th>
                                <th class="px-4 py-2 text-left border">Allowance Name</th>
                                <th class="px-4 py-2 text-left border">Percentage</th>
                                <th class="px-4 py-2 text-left border">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $deductions->fetch_assoc()): ?>
                                <?php if (!in_array($row['id'], $existing_deductions)): ?>
                                    <tr class="border-b hover:bg-indigo-50 transition duration-150 ease-in-out">
                                        <td class="px-4 py-2 border">
                                            <input type="checkbox" name="deductions[]" value="<?php echo $row['id']; ?>">
                                        </td>
                                        <td class="px-4 py-2 border text-gray-700"><?php echo htmlspecialchars($row['dedName']); ?></td>
                                        <td class="px-4 py-2 border">
                                            <input type="text" name="dedPercentage[<?php echo $row['id']; ?>]" 
                                                value="<?php echo $row['dedPercentage']; ?>" 
                                                class="border p-2 w-full rounded-md" />
                                        </td>
                                        <td class="px-4 py-2 border">
                                            <input type="text" name="dedValue[<?php echo $row['id']; ?>]" 
                                                value="<?php echo $row['dedValue']; ?>" 
                                                class="border p-2 w-full rounded-md" />
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <button type="submit" class="w-full bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 transition duration-200 ease-in-out">Select Deductions</button>
                </form>

                <h2 class="text-2xl font-semibold mt-8 mb-4 text-gray-800">Total Deduction: <?php echo htmlspecialchars($total_deduction); ?></h2>
            </div>
         </main>
    </div>
</body>
</html>
