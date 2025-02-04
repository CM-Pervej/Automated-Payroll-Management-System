<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not authenticated
    header('Location: ../index.php');
    exit();
}

// Include the database connection
include '../db_conn.php';

// Retrieve the employee ID from the query parameters
$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

if ($employee_id <= 0) {
    echo "Invalid employee ID.";
    exit();
}

$successMessage = "";
$errorMessage = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the selected allowances and deductions
    $selected_allowances = isset($_POST['allowances']) ? $_POST['allowances'] : [];
    $selected_deductions = isset($_POST['deductions']) ? $_POST['deductions'] : [];

    // Fetch the employee's basic salary
    $stmt = $conn->prepare("SELECT basic FROM employee WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($basic);
    $stmt->fetch();
    $stmt->close();

    if ($basic !== null) {
        // Prepare the SQL insert statement for empAllowance
        $insert_allowance_stmt = $conn->prepare("INSERT INTO empAllowance (employee_id, allowanceList_id, allwName, allwPercentage, allwValue, allwTotal) 
                                                VALUES (?, ?, ?, ?, ?, ?) 
                                                ON DUPLICATE KEY UPDATE allwName = VALUES(allwName), allwPercentage = VALUES(allwPercentage), allwValue = VALUES(allwValue), allwTotal = VALUES(allwTotal)");

        // Insert selected allowances
        foreach ($selected_allowances as $allowance_id) {
            // Fetch the allowance details from the allowanceList table
            $allw_stmt = $conn->prepare("SELECT allwName, allwPercentage, allwValue FROM allowanceList WHERE id = ?");
            $allw_stmt->bind_param("i", $allowance_id);
            $allw_stmt->execute();
            $allw_stmt->bind_result($allwName, $allwPercentage, $allwValue);
            $allw_stmt->fetch();
            $allw_stmt->close();

            // Calculate allwTotal
            $allwTotal = ($basic * ($allwPercentage / 100)) + $allwValue;

            // Insert the selected allowance into empAllowance or update if it exists
            $insert_allowance_stmt->bind_param("iisddd", $employee_id, $allowance_id, $allwName, $allwPercentage, $allwValue, $allwTotal);
            if (!$insert_allowance_stmt->execute()) {
                $errorMessage = "Error inserting allowance: " . $insert_allowance_stmt->error;
            }
        }

        // Prepare the SQL insert statement for empDeduction
        $insert_deduction_stmt = $conn->prepare("INSERT INTO empDeduction (employee_id, deductionList_id, dedName, dedPercentage, dedValue, dedTotal) 
                                                VALUES (?, ?, ?, ?, ?, ?) 
                                                ON DUPLICATE KEY UPDATE dedName = VALUES(dedName), dedPercentage = VALUES(dedPercentage), dedValue = VALUES(dedValue), dedTotal = VALUES(dedTotal)");

        // Insert selected deductions
        foreach ($selected_deductions as $deduction_id) {
            // Fetch the deduction details from the deductionList table
            $ded_stmt = $conn->prepare("SELECT dedName, dedPercentage, dedValue FROM deductionList WHERE id = ?");
            $ded_stmt->bind_param("i", $deduction_id);
            $ded_stmt->execute();
            $ded_stmt->bind_result($dedName, $dedPercentage, $dedValue);
            $ded_stmt->fetch();
            $ded_stmt->close();

            // Calculate dedTotal
            $dedTotal = ($basic * ($dedPercentage / 100)) + $dedValue;

            // Insert the selected deduction into empDeduction or update if it exists
            $insert_deduction_stmt->bind_param("iisddd", $employee_id, $deduction_id, $dedName, $dedPercentage, $dedValue, $dedTotal);
            if (!$insert_deduction_stmt->execute()) {
                $errorMessage = "Error inserting deduction: " . $insert_deduction_stmt->error;
            }
        }

        $successMessage = "Allowances and deductions inserted/updated successfully!";
        // Close the insert statements
        $insert_allowance_stmt->close();
        $insert_deduction_stmt->close();
    }
}

// Fetch existing allowances for the employee
$existing_allowances_stmt = $conn->prepare("SELECT allowanceList_id FROM empAllowance WHERE employee_id = ?");
$existing_allowances_stmt->bind_param("i", $employee_id);
$existing_allowances_stmt->execute();
$existing_allowances_result = $existing_allowances_stmt->get_result();
$existing_allowances = [];
while ($row = $existing_allowances_result->fetch_assoc()) {
    $existing_allowances[] = $row['allowanceList_id'];
}
$existing_allowances_stmt->close();

// Fetch existing deductions for the employee
$existing_deductions_stmt = $conn->prepare("SELECT deductionList_id FROM empDeduction WHERE employee_id = ?");
$existing_deductions_stmt->bind_param("i", $employee_id);
$existing_deductions_stmt->execute();
$existing_deductions_result = $existing_deductions_stmt->get_result();
$existing_deductions = [];
while ($row = $existing_deductions_result->fetch_assoc()) {
    $existing_deductions[] = $row['deductionList_id'];
}
$existing_deductions_stmt->close();

// Fetch allowances from the allowanceList table for display
$allowances = $conn->query("SELECT id, allwName, allwPercentage, allwValue FROM allowanceList");

// Fetch deductions from the deductionList table for display
$deductions = $conn->query("SELECT id, dedName, dedPercentage, dedValue FROM deductionList");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Allowances and Deductions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
</head>
<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-4">Select Allowances and Deductions for Employee ID: <?php echo $employee_id; ?></h1>

        <?php if ($successMessage): ?>
            <div class="mb-4 text-green-600">
                <?php echo $successMessage; ?>
            </div>
        <?php elseif ($errorMessage): ?>
            <div class="mb-4 text-red-600">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <form action="setAllwDed.php?employee_id=<?php echo $employee_id; ?>" method="POST" class="space-y-4">
            <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">

            <!-- <section class="flex justify-between"> -->
            <section>
                <div class="border-t border-r border-b border-black pr-1">
                    <h2 class="text-xl font-semibold mb-2">Allowances</h2>
                    <table class="min-w-full divide-y divide-gray-200 mb-4">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Select</th>
                                <th class="px-4 py-2 text-left">Allowance Name</th>
                                <th class="px-4 py-2 text-left">Percentage</th>
                                <th class="px-4 py-2 text-left">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $allowances->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2">
                                    <input type="checkbox" name="allowances[]" value="<?php echo $row['id']; ?>" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" 
                                        <?php echo in_array($row['id'], $existing_allowances) ? 'checked' : ''; ?>>
                                </td>
                                <td class="px-4 py-2"><?php echo $row['allwName']; ?></td>
                                <td class="px-4 py-2"><?php echo $row['allwPercentage']; ?>%</td>
                                <td class="px-4 py-2"><?php echo number_format($row['allwValue'], 2); ?> BDT</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-l border-b border-black pl-1">
                    <h2 class="text-xl font-semibold mb-2">Deductions</h2>
                    <table class="min-w-full divide-y divide-gray-200 mb-4">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Select</th>
                                <th class="px-4 py-2 text-left">Deduction Name</th>
                                <th class="px-4 py-2 text-left">Percentage</th>
                                <th class="px-4 py-2 text-left">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $deductions->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2">
                                    <input type="checkbox" name="deductions[]" value="<?php echo $row['id']; ?>" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" 
                                        <?php echo in_array($row['id'], $existing_deductions) ? 'checked' : ''; ?>>
                                </td>
                                <td class="px-4 py-2"><?php echo $row['dedName']; ?></td>
                                <td class="px-4 py-2"><?php echo $row['dedPercentage']; ?>%</td>
                                <td class="px-4 py-2"><?php echo number_format($row['dedValue'], 2); ?> BDT</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <div>
                <button type="submit" class="w-full bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600">Submit Allowances and Deductions</button>
            </div>
        </form>
    </div>
</body>
</html>
