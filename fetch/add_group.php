<?php
include '../view.php';
include '../db_conn.php';

// Fetch the addDuty_id from the URL
$addDuty_id = isset($_GET['AdditionalDesignation_id']) ? (int) $_GET['AdditionalDesignation_id'] : 0;

$employees_by_department = [];
$additionalDesignation = '';

// Fetch the Additional Designation name based on the addDuty_id
if ($addDuty_id > 0) {
    // Query to fetch the Additional Designation for the given addDuty_id
    $stmt = $conn->prepare("SELECT designation FROM addDuty WHERE id = ?");
    $stmt->bind_param("i", $addDuty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $additionalDesignation = $row['designation'];
    }
    $stmt->close();

    // Query to fetch employees connected to the given addDuty_id from empAddSalary and empAddDesignation
    $stmt = $conn->prepare("SELECT e.id AS employee_id, e.name AS employee_name, e.contactNo, e.email, 
                                   e.department_id, d.department_name,
                                   ad.AdditionalDesignation, ea.chargeAllw, ea.telephoneAllwance
                            FROM employee e
                            LEFT JOIN empAddSalary ea ON e.id = ea.employee_id
                            LEFT JOIN empAddDesignation ad ON ea.id = ad.empAddSalary_id
                            LEFT JOIN departments d ON e.department_id = d.id
                            WHERE ad.addDuty_id = ?");
    $stmt->bind_param("i", $addDuty_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Store the results grouped by department
    while ($row = $result->fetch_assoc()) {
        $department_name = $row['department_name'];
        $employees_by_department[$department_name][] = $row;
    }
    $stmt->close();
} else {
    $employees_by_department = [];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees by Department</title>
</head>
<body>
    <h1><?= htmlspecialchars($additionalDesignation); ?></h1>

    <?php if (!empty($employees_by_department)): ?>
        <?php foreach ($employees_by_department as $department_name => $employees): ?>
            <h2>Department: <?= htmlspecialchars($department_name); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Contact No</th>
                        <th>Email</th>
                        <th>Charge Allowance</th>
                        <th>Telephone Allowance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['employee_name']); ?></td>
                            <td><?= htmlspecialchars($employee['contactNo']); ?></td>
                            <td><?= htmlspecialchars($employee['email']); ?></td>
                            <td><?= number_format($employee['chargeAllw'], 2); ?></td>
                            <td><?= number_format($employee['telephoneAllwance'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No employees found for the selected Additional Designation ID.</p>
    <?php endif; ?>
</body>
</html>
