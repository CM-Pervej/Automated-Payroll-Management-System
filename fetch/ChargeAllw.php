<?php
include '../view.php';
include '../db_conn.php';

// Fetch data from the addDuty and telephoneAllw tables
$sql = "SELECT a.id, a.designation, a.addSalary, t.telephoneAllw
        FROM addDuty a
        LEFT JOIN telephoneAllw t ON a.id = t.addDuty_id
        WHERE a.id != 1";
$result = $conn->query($sql);

// Organize data into associative array
$additionalSalaries = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        if (!isset($additionalSalaries[$id])) {
            $additionalSalaries[$id] = [
                'designation' => $row['designation'],
                'addSalary' => $row['addSalary'],
                'telephones' => []
            ];
        }
        if ($row['telephoneAllw']) {
            $additionalSalaries[$id]['telephones'][] = $row['telephoneAllw'];
        }
    }
}

// Initialize messages
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../db_conn.php'; // Reopen connection for insertion

    $designation = $_POST['designation'];
    $addSalary = floatval($_POST['addSalary']);
    $telephoneAllw = isset($_POST['telephone']) ? $_POST['telephone'] : [];

    // Prepare insertion for addDuty
    $stmt = $conn->prepare("INSERT INTO addDuty (designation, addSalary) VALUES (?, ?)");
    $stmt->bind_param("sd", $designation, $addSalary);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;

        // Insert into telephoneAllw for each telephone allowance
        $telephone_stmt = $conn->prepare("INSERT INTO telephoneAllw (addDuty_id, telephoneAllw) VALUES (?, ?)");
        foreach ($telephoneAllw as $telephone) {
            $telephone_stmt->bind_param("is", $last_id, $telephone);
            $telephone_stmt->execute();
        }
        $telephone_stmt->close();
        $success_message = "Additional salary registered successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Additional Salary Data</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
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
                <?php if (!empty($success_message)): ?>
                    <div class="text-green-700 bg-green-100 p-4 rounded mb-4 font-bold">
                        <?php echo $success_message; ?>
                    </div>
                <?php elseif (!empty($error_message)): ?>
                    <div class="text-red-700 bg-red-100 p-4 rounded mb-4 font-bold">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <section class="flex justify-between mb-5">
                    <h1 class="text-2xl font-bold mb-4">Allowance List</h1>
                    <div class="flex gap-5">
                        <button id="addReportButton" class="btn btn-success text-white px-4 py-2 rounded hover:bg-green-700 flex items-center" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'disabled' : ''; ?> title="Only Admin and HR can access this page">
                            <i class="fas fa-plus mr-2"></i> Add Duty
                        </button>
                        <a href="chargeChange.php" class="btn btn-info" class="btn btn-info" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'disabled' : ''; ?> title="Only Admin and HR can access this page">Action</a>
                    </div>
                </section>

                <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg text-center">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-6 py-4 text-gray-700 font-bold">ID</th>
                            <th class="px-6 py-4 text-gray-700 font-bold">Additional Designation</th>
                            <th class="px-6 py-4 text-gray-700 font-bold">Additional Salary</th>
                            <th class="px-6 py-4 text-gray-700 font-bold">Telephone Allowance(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($additionalSalaries)): ?>
                            <?php foreach ($additionalSalaries as $id => $data): ?>
                                <tr class="bg-white">
                                    <td class="border px-4 py-2"><?php echo $id; ?></td>
                                    <td>
                                        <a href="add_group.php?AdditionalDesignation_id=<?= htmlspecialchars($id); ?>" class="text-blue-600 w-full block py-4 transform transition-all duration-150 hover:scale-125">
                                            <?= htmlspecialchars($data['designation']); ?>
                                        </a>
                                    </td>
                                    <td class="border px-4 py-2"><?php echo number_format($data['addSalary'], 2); ?></td>
                                    <td class="border px-4 py-2">
                                        <ul class="flex gap-5">
                                            <?php foreach ($data['telephones'] as $telephone): ?>
                                                <li class="border p-2"><?php echo number_format($telephone, 2); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="border px-4 py-2 text-center">No data found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal for Adding Report -->
    <div id="addReportModal" class="fixed inset-0 flex items-center justify-center z-50 hidden overflow-auto">
        <div class="bg-blue-100 rounded-lg shadow-lg p-6 w-96 border">
            <h2 class="text-lg font-bold mb-4">Add New Designation</h2>
            <form id="addReportForm" method="post" class="space-y-6">
                <div>
                    <label for="designation" class="block text-lg font-semibold mb-2">Additional Designation</label>
                    <input type="text" id="designation" name="designation" required class="w-full p-3 border">
                </div>
                <div>
                    <label for="addSalary" class="block text-lg font-semibold mb-2">Additional Salary</label>
                    <input type="number" step="0.01" id="addSalary" name="addSalary" required class="w-full p-3 border">
                </div>
                <div id="telephone-fields" class="space-y-4">
                    <div class="mb-4">
                        <label for="telephone1" class="block text-lg font-semibold mb-2">Telephone 1</label>
                        <input type="number" step="0.01" id="telephone1" name="telephone[]" required class="w-full p-3 border">
                    </div>
                </div>
                <div class="flex space-x-4">
                    <button type="button" onclick="addTelephoneField()" class="bg-blue-500 text-white py-2 px-4 rounded">Add Telephone</button>
                    <button type="button" onclick="removeTelephoneField()" class="bg-red-500 text-white py-2 px-4 rounded">Remove Telephone</button>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeModal" class="text-gray-500 mr-2">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('addReportButton').onclick = function() {
            document.getElementById('addReportModal').classList.remove('hidden');
        };
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('addReportModal').classList.add('hidden');
        };
        
        let telephoneCount = 1;
        
        function addTelephoneField() {
            telephoneCount++;
            const fieldDiv = document.createElement('div');
            fieldDiv.classList.add('mb-4');
            fieldDiv.innerHTML = `
                <label for="telephone${telephoneCount}" class="block text-lg font-semibold mb-2">Telephone ${telephoneCount}</label>
                <input type="number" step="0.01" id="telephone${telephoneCount}" name="telephone[]" required class="w-full p-3 border">
            `;
            document.getElementById('telephone-fields').appendChild(fieldDiv);
        }
        
        function removeTelephoneField() {
            const fieldsDiv = document.getElementById('telephone-fields');
            if (fieldsDiv.children.length > 1) {
                fieldsDiv.removeChild(fieldsDiv.lastChild);
                telephoneCount--;
            }
        }
    </script>
</body>
</html>
