<?php
include 'auth.php';
include '../db_conn.php';

// Get employee_id from URL parameters
$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

if ($employee_id == 0) {
    die("Invalid employee ID");
}

// Initialize variables
$additionalSalaries = [];
$selectedDesignations = [];
$existingTelephoneAllw_id = null; // Initialize to capture existing telephone allowance ID

// Fetch data from addDuty and telephoneAllw tables, excluding the first id
$sql = "SELECT a.id AS addDuty_id, a.designation, a.addSalary, t.id AS telephoneAllw_id, t.telephoneAllw
        FROM addDuty a
        LEFT JOIN telephoneAllw t ON a.id = t.addDuty_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['addDuty_id'];

        if (!isset($additionalSalaries[$id])) {
            $additionalSalaries[$id] = [
                'designation' => $row['designation'],
                'addSalary' => $row['addSalary'],
                'telephones' => [],
            ];
        }

        if ($row['telephoneAllw']) {
            $additionalSalaries[$id]['telephones'][] = [
                'id' => $row['telephoneAllw_id'],
                'value' => $row['telephoneAllw']
            ];
        }
    }
} else {
    echo "No records found.";
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $selectedDesignations = $_POST['designation'] ?? []; // This will be an array
    $selectedAddSalaryIds = $_POST['addDuty_id'] ?? []; // This will map to the designations
    $selectedTelephoneAllw = $_POST['telephoneAllw'] ?? 0; // Selected telephone allowance

    // Check if there is existing data for this employee
    $existingDataCheckSql = "SELECT id FROM empAddSalary WHERE employee_id = ?";
    $stmt = $conn->prepare($existingDataCheckSql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $existingDataResult = $stmt->get_result();

    // If there is existing data, delete it
    if ($existingDataResult->num_rows > 0) {
        // Delete existing entries in empAddSalary
        $deleteSalarySql = "DELETE FROM empAddSalary WHERE employee_id = ?";
        $stmt = $conn->prepare($deleteSalarySql);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();

        // Optionally delete related entries in empAddDesignation
        $deleteDesignationSql = "DELETE FROM empAddDesignation WHERE empAddSalary_id IN (SELECT id FROM empAddSalary WHERE employee_id = ?)";
        $stmt = $conn->prepare($deleteDesignationSql);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
    }

    // Get the corresponding addSalary values for selected designations
    $additionalSalariesSelected = [];
    foreach ($selectedDesignations as $id => $designation) {
        // Only fetch the addSalary for the selected addDuty_id (matching the checked designations)
        $addDuty_id = $selectedAddSalaryIds[$id]; // Use the selected addDuty_id
        $salarySql = "SELECT addSalary FROM addDuty WHERE id = ?";
        $stmt = $conn->prepare($salarySql);
        $stmt->bind_param("i", $addDuty_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $additionalSalariesSelected[] = $row['addSalary'];
        }
    }

    // Sort salaries in descending order
    rsort($additionalSalariesSelected);

    // Calculate chargeAllw
    if (count($additionalSalariesSelected) > 0) {
        $maxSalary = $additionalSalariesSelected[0];
        $secondMaxSalary = (count($additionalSalariesSelected) > 1) ? $additionalSalariesSelected[1] : 0;

        // chargeAllw = max + half of the second max
        $chargeAllw = $maxSalary + ($secondMaxSalary / 2);
    } else {
        $chargeAllw = 0; // No designations selected
    }

    // Get the telephone allowance amount based on the selected telephoneAllw_id
    $telephoneAllowanceValue = 0;
    if ($selectedTelephoneAllw) {
        $telephoneSql = "SELECT telephoneAllw FROM telephoneAllw WHERE id = ?";
        $stmt = $conn->prepare($telephoneSql);
        $stmt->bind_param("i", $selectedTelephoneAllw);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $telephoneAllowanceValue = $row['telephoneAllw'];
        }
    }

    // Insert into empAddSalary table with correct values
    $insertSalarySql = "INSERT INTO empAddSalary (employee_id, chargeAllw, telephoneAllwance, telephoneAllw_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSalarySql);
    $stmt->bind_param("iddi", $employee_id, $chargeAllw, $telephoneAllowanceValue, $selectedTelephoneAllw);
    if ($stmt->execute()) {
        $empAddSalary_id = $stmt->insert_id; // Get the last inserted id

        // Insert selected designations
        foreach ($selectedDesignations as $id => $designation) {
            $addDuty_id = $selectedAddSalaryIds[$id];

            // Insert into empAddDesignation with the corresponding addDuty_id
            $insertDesignationSql = "INSERT INTO empAddDesignation (empAddSalary_id, addDuty_id, AdditionalDesignation) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertDesignationSql);
            $stmt->bind_param("iis", $empAddSalary_id, $addDuty_id, $designation);
            $stmt->execute();
        }

        // Redirect to profile.php with employee_id as a URL parameter
        header("Location: ../profile.php?employee_id=" . $employee_id);
        exit();
    } else {
        echo "Error inserting into empAddSalary: " . $stmt->error;
    }
}

// Fetch existing designations for the employee
$existingDesignationsSql = "SELECT addDuty_id, AdditionalDesignation FROM empAddDesignation WHERE empAddSalary_id IN (SELECT id FROM empAddSalary WHERE employee_id = ?)";
$stmt = $conn->prepare($existingDesignationsSql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$existingDesignationsResult = $stmt->get_result();

while ($row = $existingDesignationsResult->fetch_assoc()) {
    $selectedDesignations[$row['addDuty_id']] = $row['AdditionalDesignation'];
}

// Fetch existing telephone allowance if any
$existingTelephoneSql = "SELECT telephoneAllw_id FROM empAddSalary WHERE employee_id = ?";
$stmt = $conn->prepare($existingTelephoneSql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$existingTelephoneResult = $stmt->get_result();
if ($existingTelephoneResult->num_rows > 0) {
    $existingTelephoneRow = $existingTelephoneResult->fetch_assoc();
    $existingTelephoneAllw_id = $existingTelephoneRow['telephoneAllw_id']; // Get the existing telephone allowance ID
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Designation and Allowances</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="sideBar.css">
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
            <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-lg border">
                <h2 class="text-2xl font-bold text-center mb-6">Select Designation and Allowances</h2>

                <form action="" method="POST" class="space-y-4">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border px-4 py-2">Designation</th>
                                <th class="border px-4 py-2">Additional Salary</th>
                                <th class="border px-4 py-2">Telephone Allowance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($additionalSalaries as $id => $data): ?>
                                <tr class="bg-white">
                                    <td class="border px-4 py-2">
                                        <input type="checkbox" name="designation[<?php echo $id; ?>]" value="<?php echo $data['designation']; ?>" id="designation<?php echo $id; ?>"
                                        <?php echo (isset($selectedDesignations[$id]) ? 'checked' : ''); ?>>
                                        <input type="hidden" name="addDuty_id[<?php echo $id; ?>]" value="<?php echo $id; ?>">
                                        <label for="designation<?php echo $id; ?>"><?php echo $data['designation']; ?></label>
                                    </td>
                                    <td class="border px-4 py-2"><?php echo $data['addSalary']; ?></td>
                                    <td class="border px-4 py-2">
                                        <div class="border px-4 py-2 flex justify-between">
                                            <?php foreach ($data['telephones'] as $telephone): ?>
                                                <div>
                                                    <input type="radio" name="telephoneAllw" value="<?php echo $telephone['id']; ?>" id="telephoneAllw<?php echo $telephone['id']; ?>" 
                                                    <?php echo ($existingTelephoneAllw_id == $telephone['id'] ? 'checked' : ''); ?>>
                                                    <label for="telephoneAllw<?php echo $telephone['id']; ?>"><?php echo number_format($telephone['value'], 2); ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="text-center">
                        <button type="submit" class="bg-blue-500 text-white font-semibold px-4 py-2 rounded hover:bg-blue-700">Submit</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
