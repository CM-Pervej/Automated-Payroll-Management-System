<?php
// Include the database connection
include '../db_conn.php';

$successMessage = "";
$errorMessages = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = htmlspecialchars(trim($_POST['action']));
    $employee_id = (int) htmlspecialchars(trim($_POST['employee_id']));

    if ($action === "update") {
        // Collect and validate form data for update
        $name = strtoupper(trim(htmlspecialchars($_POST['name'])));
        $gender = htmlspecialchars(trim($_POST['gender']));
        $date_of_birth = htmlspecialchars(trim($_POST['date_of_birth']));
        $contactNo = htmlspecialchars(trim($_POST['contactNo']));
        $email = htmlspecialchars(trim($_POST['email']));
        $empStatus = htmlspecialchars(trim($_POST['empStatus']));
        $designation_id = (int) htmlspecialchars(trim($_POST['designation_id']));
        $department_id = (int) htmlspecialchars(trim($_POST['department_id']));
        $account_number = htmlspecialchars(trim($_POST['account_number']));
        $grade_id = (int) htmlspecialchars(trim($_POST['grade_id']));
        $joining_date = htmlspecialchars(trim($_POST['joining_date']));
        $e_tin = htmlspecialchars(trim($_POST['e_tin']));

        // Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessages[] = "Invalid email format.";
        }
        if (!preg_match('/^[0-9]+$/', $contactNo)) {
            $errorMessages[] = "Invalid contact number format.";
        }

        // Handle optional image upload
        $image = $_FILES['image'];
        $imagePath = '';

        if ($image && $image['error'] === 0) {
            $targetDir = "../uploads/";
            $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'png', 'jpeg'];

            if (in_array($imageFileType, $allowedTypes) && $image["size"] <= 2000000) {
                $imagePath = $targetDir . uniqid() . '.' . $imageFileType;
                if (!move_uploaded_file($image["tmp_name"], $imagePath)) {
                    $errorMessages[] = "Failed to upload image.";
                }
            } else {
                $errorMessages[] = "Invalid image format or file too large.";
            }
        }

        if (empty($errorMessages)) {
            $stmt = $conn->prepare("UPDATE employee 
                SET name = ?, date_of_birth = ?, gender = ?, contactNo = ?, email = ?, empStatus = ?, 
                    designation_id = ?, department_id = ?, account_number = ?, grade_id = ?, joining_date = ?, e_tin = ?, image = ?
                WHERE id = ?");
            $stmt->bind_param(
                "sssssssiissssi",
                $name, $date_of_birth, $gender, $contactNo, $email, $empStatus, $designation_id,
                $department_id, $account_number, $grade_id, $joining_date, $e_tin, $imagePath, $employee_id
            );

            if ($stmt->execute()) {
                $successMessage = "Employee updated successfully!";
            } else {
                $errorMessages[] = "Database error: " . $stmt->error;
            }

            $stmt->close();
        }
    } elseif ($action === "remove") {
        // Handle employee deletion
        $stmt = $conn->prepare("DELETE FROM employee WHERE id = ?");
        $stmt->bind_param("i", $employee_id);

        if ($stmt->execute()) {
            $successMessage = "Employee removed successfully!";
        } else {
            $errorMessages[] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $errorMessages[] = "Invalid action.";
    }

    $conn->close();
}

// Fetch all employees for display
$employees = $conn->query("SELECT * FROM employee");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Action</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        form {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Employee Action Page</h1>

    <!-- Success and Error Messages -->
    <?php if (!empty($successMessage)): ?>
        <p style="color: green;"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>
    <?php if (!empty($errorMessages)): ?>
        <ul style="color: red;">
            <?php foreach ($errorMessages as $message): ?>
                <li><?= htmlspecialchars($message) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Display Employee List -->
    <h2>Employee List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $employees->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['employeeNo'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td>
                        <form method="POST" action="employee_action.php" style="display: inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="employee_id" value="<?= $row['id'] ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to remove this employee?')">Remove</button>
                        </form>
                        <form method="POST" enctype="multipart/form-data" action="employee_action.php" style="display: inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="employee_id" value="<?= $row['id'] ?>">
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Update Form -->
    <h2>Update Employee</h2>
    <form method="POST" enctype="multipart/form-data" action="employee_action.php">
        <input type="hidden" name="action" value="update">
        <label for="employee_id">Employee ID:</label>
        <input type="number" name="employee_id" required>
        <label for="name">Name:</label>
        <input type="text" name="name" required>
        <label for="gender">Gender:</label>
        <select name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        <label for="date_of_birth">Date of Birth:</label>
        <input type="date" name="date_of_birth" required>
        <label for="contactNo">Contact No:</label>
        <input type="text" name="contactNo" required>
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <label for="empStatus">Status:</label>
        <input type="text" name="empStatus" required>
        <label for="designation_id">Designation ID:</label>
        <input type="number" name="designation_id" required>
        <label for="department_id">Department ID:</label>
        <input type="number" name="department_id" required>
        <label for="account_number">Account Number:</label>
        <input type="text" name="account_number" required>
        <label for="grade_id">Grade ID:</label>
        <input type="number" name="grade_id" required>
        <label for="joining_date">Joining Date:</label>
        <input type="date" name="joining_date" required>
        <label for="e_tin">E-TIN:</label>
        <input type="text" name="e_tin" required>
        <label for="image">Profile Image:</label>
        <input type="file" name="image">
        <button type="submit">Update Employee</button>
    </form>
</body>
</html>
