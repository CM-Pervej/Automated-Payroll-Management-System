<?php
session_start();

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['userrole_id'] != 1) {
    header('Location: ../dashboard.php'); // Redirect to dashboard if not Admin
    exit();
}

include '../db_conn.php';

$employeesWithoutUser = [];
$employeesWithUser = [];

// Check if the approve_employee form is submitted
if (isset($_POST['approve_employee'])) {
    $employee_id = $_POST['employee_id'];
    $approval_status = $_POST['approval_status'];

    if ($approval_status === 'delete') {
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Delete from the user table first to avoid foreign key constraint error
        $deleteUserQuery = "DELETE FROM user WHERE employee_id = ?";
        $stmt = $conn->prepare($deleteUserQuery);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();

        // Now delete from the employee table
        $deleteEmployeeQuery = "DELETE FROM employee WHERE id = ?";
        $stmt = $conn->prepare($deleteEmployeeQuery);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();

        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    } else {
        // Update the employee's approve status in the employee table
        $updateApproveStatusQuery = "UPDATE employee SET approve = ? WHERE id = ?";
        $stmt = $conn->prepare($updateApproveStatusQuery);
        $stmt->bind_param("ii", $approval_status, $employee_id);
        $stmt->execute();
    }

    // Refresh the page after the form submission (staying on the same page)
    header("Refresh:0");
    exit();
}

// Fetch employees without user account
$result = $conn->query("SELECT e.id, e.employeeNo, e.name, e.email, e.grade_id, e.image, e.approve, d.department_name, des.designation, g.grade FROM employee e LEFT JOIN departments d ON e.department_id = d.id LEFT JOIN designations des ON e.designation_id = des.id LEFT JOIN grade g ON e.grade_id = g.id WHERE e.approve = 0 AND e.id NOT IN (SELECT employee_id FROM user)");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $employeesWithoutUser[] = $row;
    }
}

// Fetch employees with a user account and their roles
$result = $conn->query("SELECT e.id, e.employeeNo, e.name, e.email, e.grade_id, e.image, e.approve, d.department_name, des.designation, g.grade, ur.role FROM employee e LEFT JOIN departments d ON e.department_id = d.id LEFT JOIN designations des ON e.designation_id = des.id LEFT JOIN grade g ON e.grade_id = g.id LEFT JOIN user u ON e.id = u.employee_id LEFT JOIN userRole ur ON u.userrole_id = ur.id WHERE e.approve = 0 AND e.id IN (SELECT employee_id FROM user)");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $employeesWithUser[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll allowanceList</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <div class="flex flex-col flex-grow ml-64">
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <!-- Employees without User Account Table -->
            <section>
                <h1 class="text-2xl font-bold mb-4">Employee</h1>
                <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2 text-left">Employee</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Grade</th>
                            <th class="px-4 py-2 text-left">Designation</th>
                            <th class="px-4 py-2 text-left">Department</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employeesWithoutUser as $employee): ?>
                            <tr class="hover:bg-gray-100">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($employee['employeeNo']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($employee['name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($employee['grade']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($employee['designation']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($employee['department_name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td class="px-4 py-2">
                                    <form method="post">
                                        <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
                                        <select name="approval_status" class="border p-2 rounded">
                                            <option value="1">Approve</option>
                                            <option value="0">Pending</option>
                                            <option value="delete">Reject (Delete)</option>
                                        </select>
                                        <button type="submit" name="approve_employee" class="bg-blue-500 text-white px-4 py-1 rounded">
                                            Submit
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- Employees with User Account Table -->
            <section class="mt-14">
                <h1 class="text-2xl font-bold mb-4 px-2 pt-2">Users</h1>
                <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2 text-left">User</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Grade</th>
                            <th class="px-4 py-2 text-left">Designation</th>
                            <th class="px-4 py-2 text-left">Department</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Role</th>
                            <th class="px-4 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employeesWithUser as $employee) : ?>
                            <tr>
                                <td class="px-4 py-2"><?= htmlspecialchars($employee['employeeNo']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($employee['name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($employee['grade']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($employee['department_name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($employee['designation']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($employee['email']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($employee['role']) ?></td>
                                <td class="px-4 py-2">
                                    <form method="post" class="flex">
                                        <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
                                        <select name="approval_status" class="border p-2 rounded">
                                            <option value="1">Approve</option>
                                            <option value="0">Pending</option>
                                            <option value="delete">Reject (Delete)</option>
                                        </select>
                                        <button type="submit" name="approve_employee" class="bg-blue-500 text-white px-4 py-1 rounded">
                                            Submit
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
