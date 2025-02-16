<?php
include 'auth.php';
include '../db_conn.php';

// Initialize messages
$success_message = '';
$error_message = '';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Display messages from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Fetch all addDuty data with associated telephone allowances
$sql = "SELECT a.id AS duty_id, a.designation, a.addSalary, t.id AS telephone_id, t.telephoneAllw
        FROM addDuty a
        LEFT JOIN telephoneAllw t ON a.id = t.addDuty_id";
        // -- WHERE a.id != 1";
$result = $conn->query($sql);

$dutiesData = [];
while ($row = $result->fetch_assoc()) {
    if (!isset($dutiesData[$row['duty_id']])) {
        $dutiesData[$row['duty_id']] = [
            'id' => $row['duty_id'],
            'designation' => $row['designation'],
            'addSalary' => $row['addSalary'],
            'telephones' => []
        ];
    }
    if ($row['telephone_id']) {
        $dutiesData[$row['duty_id']]['telephones'][] = [
            'telephone_id' => $row['telephone_id'],
            'telephoneAllw' => $row['telephoneAllw']
        ];
    }
}

// Handle update, delete, add, and remove actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        // Update a single duty record and associated telephone allowances
        $dutyId = $_POST['duty_id'];
        $designation = $_POST['designation'];
        $addSalary = floatval($_POST['addSalary']);

        $updateDutySql = "UPDATE addDuty SET designation = ?, addSalary = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateDutySql);
        $updateStmt->bind_param("sdi", $designation, $addSalary, $dutyId);

        if ($updateStmt->execute()) {
            foreach ($_POST['telephones'] as $index => $telephoneAllw) {
                $telephoneId = $_POST['telephone_ids'][$index];
                if ($telephoneId) {
                    $updateTelephoneSql = "UPDATE telephoneAllw SET telephoneAllw = ? WHERE id = ?";
                    $telephoneStmt = $conn->prepare($updateTelephoneSql);
                    $telephoneStmt->bind_param("di", $telephoneAllw, $telephoneId);
                    $telephoneStmt->execute();
                    $telephoneStmt->close();
                } else {
                    $insertTelephoneSql = "INSERT INTO telephoneAllw (addDuty_id, telephoneAllw) VALUES (?, ?)";
                    $insertTelephoneStmt = $conn->prepare($insertTelephoneSql);
                    $insertTelephoneStmt->bind_param("id", $dutyId, $telephoneAllw);
                    $insertTelephoneStmt->execute();
                    $insertTelephoneStmt->close();
                }
            }
            $_SESSION['success_message'] = "Record updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating record: " . $updateStmt->error;
        }
        $updateStmt->close();
    } elseif (isset($_POST['delete'])) {
        $dutyId = $_POST['duty_id'];

        $deleteTelephoneSql = "DELETE FROM telephoneAllw WHERE addDuty_id = ?";
        $deleteTelephoneStmt = $conn->prepare($deleteTelephoneSql);
        $deleteTelephoneStmt->bind_param("i", $dutyId);
        $deleteTelephoneStmt->execute();
        $deleteTelephoneStmt->close();

        $deleteDutySql = "DELETE FROM addDuty WHERE id = ?";
        $deleteDutyStmt = $conn->prepare($deleteDutySql);
        $deleteDutyStmt->bind_param("i", $dutyId);
        $deleteDutyStmt->execute();
        $deleteDutyStmt->close();

        $_SESSION['success_message'] = "Record deleted successfully!";
    } elseif (isset($_POST['add_telephone'])) {
        $dutyId = $_POST['duty_id'];
        $telephoneAllw = floatval($_POST['telephoneAllw']);

        $insertTelephoneSql = "INSERT INTO telephoneAllw (addDuty_id, telephoneAllw) VALUES (?, ?)";
        $insertTelephoneStmt = $conn->prepare($insertTelephoneSql);
        $insertTelephoneStmt->bind_param("id", $dutyId, $telephoneAllw);

        if ($insertTelephoneStmt->execute()) {
            $_SESSION['success_message'] = "Telephone allowance added successfully!";
        } else {
            $_SESSION['error_message'] = "Error adding telephone allowance: " . $insertTelephoneStmt->error;
        }
        $insertTelephoneStmt->close();
    } elseif (isset($_POST['remove_telephone']) && isset($_POST['telephone_id'])) {
        $telephoneId = $_POST['telephone_id'];

        $deleteTelephoneSql = "DELETE FROM telephoneAllw WHERE id = ?";
        $deleteTelephoneStmt = $conn->prepare($deleteTelephoneSql);
        $deleteTelephoneStmt->bind_param("i", $telephoneId);

        if ($deleteTelephoneStmt->execute()) {
            $_SESSION['success_message'] = "Telephone allowance removed successfully!";
        } else {
            $_SESSION['error_message'] = "Error removing telephone allowance: " . $deleteTelephoneStmt->error;
        }
        $deleteTelephoneStmt->close();
    }

    header("Location: chargeChange.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Additional Duties</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 h-screen flex overflow-hidden">
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include '../sideBar.php'; ?>
    </header>
    <div class="flex flex-col flex-grow ml-64">
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include '../topBar.php'; ?>
            </aside>
        </div>
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="mb-4">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success shadow-lg"><?php echo $success_message; ?></div>
                <?php elseif (!empty($error_message)): ?>
                    <div class="alert alert-error shadow-lg"><?php echo $error_message; ?></div>
                <?php endif; ?>
            </div>
            <h1 class="text-3xl font-semibold mb-6 text-gray-800">Update Additional Duties</h1>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-6 py-4 text-gray-700 font-bold">ID</th>
                            <th class="px-6 py-4 text-gray-700 font-bold">Designation</th>
                            <th class="px-6 py-4 text-gray-700 font-bold w-max">Additional Salary</th>
                            <th class="px-6 py-4 text-gray-700 font-bold">Telephone Allowance</th>
                            <th class="px-6 py-4 text-gray-700 font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dutiesData as $dutyId => $duty): ?>
                            <form method="POST" class="bg-white border-b">
                                <input type="hidden" name="duty_id" value="<?php echo $duty['id']; ?>">
                                <tr class="border-b">
                                    <td class="border px-4 py-2 text-center"><?php echo $duty['id']; ?></td>
                                    <td class="border px-4 py-2">
                                        <input type="text" name="designation" value="<?php echo htmlspecialchars($duty['designation']); ?>" class="input input-bordered w-full max-w-xs">
                                    </td>
                                    <td class="border px-4 py-2">
                                        <input type="number" name="addSalary" value="<?php echo $duty['addSalary']; ?>" class="input input-bordered w-max">
                                    </td>
                                    <td class="border px-4 py-2">
                                        <?php foreach ($duty['telephones'] as $telephone): ?>
                                            <div class="flex items-center space-x-2 mb-2">
                                                <input type="hidden" name="telephone_ids[]" value="<?php echo $telephone['telephone_id']; ?>">
                                                <input type="number" name="telephones[]" value="<?php echo $telephone['telephoneAllw']; ?>" class="input input-bordered w-full max-w-xs">
                                                <button type="submit" name="remove_telephone" class="btn btn-error btn-sm" onclick="return confirm('Are you sure you want to remove this telephone allowance?')">Remove</button>
                                                <input type="hidden" name="telephone_id" value="<?php echo $telephone['telephone_id']; ?>">
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="flex items-center space-x-2 mb-4">
                                            <input type="number" name="telephoneAllw" class="input input-bordered w-full max-w-xs" placeholder="New Telephone Allowance">
                                            <button type="submit" name="add_telephone" value="add" class="btn btn-success btn-sm">Add Telephone</button>
                                        </div>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <div class="flex flex-col gap-2">
                                            <button type="submit" name="update" value="update" class="btn btn-primary btn-sm">Update</button>
                                            <button type="submit" name="delete" value="delete" class="btn btn-danger btn-sm">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </form>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>

