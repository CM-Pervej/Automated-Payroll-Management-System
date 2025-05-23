<?php
// Include the database connection
include '../db_conn.php';

$successMessage = "";
$errorMessages = []; // Array to store all error messages

// Fetch designations, departments, and grades for select options
$designations = $conn->query("SELECT id, designation FROM designations");
$departments = $conn->query("SELECT id, department_name FROM departments");
$grades = $conn->query("SELECT id, grade, scale FROM grade");

// Fetch the last employee's ID
$employee_id_result = $conn->query("SELECT id FROM employee ORDER BY id DESC LIMIT 1");
$employee_id_row = $employee_id_result->fetch_assoc();
$employee_id = $employee_id_row ? $employee_id_row['id'] + 1 : 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate form data
    $name = strtoupper(trim(htmlspecialchars($_POST['name'])));
    $gender = htmlspecialchars(trim($_POST['gender']));
    $date_of_birth = htmlspecialchars(trim($_POST['date_of_birth']));
    $contactNo = htmlspecialchars(trim($_POST['contactNo']));
    $email = htmlspecialchars(trim($_POST['email']));
    // $empStatus = htmlspecialchars(trim($_POST['empStatus']));
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

    // Image upload handling
    $image = $_FILES['image'];
    $imagePath = '';

    if ($image['error'] === 0) {
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
    } else {
        $errorMessages[] = "Profile image is required.";
    }

    if (empty($errorMessages)) {
        $gender_code = $gender === '1' ? 1 : ($gender === '2' ? 2 : ($gender === '0' ? 3 : null));
        $birth_year = date('y', strtotime($date_of_birth));
        $employee_count_result = $conn->query("SELECT COUNT(*) AS count FROM employee");
        $employee_count_row = $employee_count_result->fetch_assoc();
        $current_employee_count = (int)$employee_count_row['count'] + 1;
        $formatted_count = str_pad($current_employee_count, 5, '0', STR_PAD_LEFT);
        $employeeNo = $gender_code . $birth_year . $formatted_count;

        $grade_stmt = $conn->prepare("SELECT scale FROM grade WHERE id = ?");
        $grade_stmt->bind_param("i", $grade_id);
        $grade_stmt->execute();
        $grade_stmt->bind_result($scale);
        $grade_stmt->fetch();
        $grade_stmt->close();
        $basic = (float)$scale;

        $stmt = $conn->prepare("INSERT INTO employee 
            (employeeNo, name, date_of_birth, gender, contactNo, email, empStatus, designation_id, department_id, basic, account_number, grade_id, joining_date, e_tin, image, approve) 
            VALUES (?, ?, ?, ?, ?, ?, 2, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param(
            "ssssssiisdssss",
            $employeeNo, $name, $date_of_birth, $gender, $contactNo, $email, $designation_id,
            $department_id, $basic, $account_number, $grade_id, $joining_date, $e_tin, $imagePath
        );

        if ($stmt->execute()) {
            $employee_id = $conn->insert_id; // Get the last inserted employee ID
        
            // Now show the alert and redirect
            echo "<script>
                alert('Set your password now');
                window.location.href = 'passRequest.php?employee_id=$employee_id';
            </script>";
            exit;
        } else {
            $errorMessages[] = "Database error: " . $stmt->error;
        }         

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col flex-grow">
        <main class="flex-grow p-8 bg-blue-100 shadow-lg overflow-auto border">
            <!-- Form -->
            <div class="container mx-auto px-5 pb-5">
                <div class="bg-white rounded-lg shadow-lg px-8 pb-8 pt-2 border">
                    <!-- Sidebar (fixed) -->
                    <div class="mt-2">
                        <!-- Heading -->
                        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Create Your Account</h2>
                        <!-- Software Info -->
                        <div class="mb-6 text-center">
                        <p class="text-lg text-gray-700">Welcome to the <b>Web-based Automated Payroll Management System</b>, a powerful tool designed to help you efficiently manage payment information for your esteemed institution. Streamline payroll processes, ensure accurate calculations, and manage allowances and deductions with ease. <b class="text-fuchsia-400">Once registered, your account will be reviewed by our team, and you will receive access once approved</b>.</p>
                        </div>
                    </div>

                    <?php if (!empty($errorMessages)): ?>
                        <div class="text-red-600 text-center"><?php echo implode('<br>', $errorMessages); ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="mt-6" autocomplete="off">
                        <section class="flex justify-between gap-20">
                            <table class="w-full">
                                <tbody>
                                    <tr>
                                        <td class="py-2">
                                            <label for="image" class="text-gray-700">Profile Image</label>
                                        </td>
                                        <td class="py-2 tooltip tooltip-left tooltip-warning w-full" data-tip="use 'jpg', 'png' or  'jpeg' file only">
                                            <input type="file" name="image" id="image" required 
                                                class="mt-1 block w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="name" class="text-gray-700">Name</label>
                                        </td>
                                        <td class="py-2">
                                            <input type="text" name="name" id="name" placeholder="Enter employee's full name" required 
                                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="gender" class="text-gray-700">Gender</label>
                                        </td>
                                        <td class="py-2">
                                            <select name="gender" id="gender" required 
                                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="" disabled selected class="bg-gray-300">Select Gender</option>
                                                <option value="1">Male</option>
                                                <option value="2">Female</option>
                                                <option value="0">Other</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="date_of_birth" class="text-gray-700">Date of Birth</label>
                                        </td>
                                        <td class="py-2">
                                            <input type="date" name="date_of_birth" id="date_of_birth" required
                                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="contactNo" class="text-gray-700">Contact No</label>
                                        </td>
                                        <td class="py-2">
                                            <input type="text" name="contactNo" id="contactNo" placeholder="Enter contact number" required 
                                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="email" class="text-gray-700">Email</label>
                                        </td>
                                        <td class="py-2">
                                            <input type="email" name="email" id="email" placeholder="Enter email address" required 
                                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                    </tr>
                                </tbody>
                            </table class="w-full">
                            <table class="w-full">
                                <tbody>
                                    <tr>
                                        <td class="py-2">
                                            <label for="designation_id" class="text-gray-700">Designation</label>
                                        </td>
                                        <td class="py-2">
                                            <select name="designation_id" id="designation_id" required 
                                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="" disabled selected class="bg-gray-300">Select Designation</option>
                                                <?php while ($row = $designations->fetch_assoc()): ?>
                                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['designation']; ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="department_id" class="text-gray-700">Department</label>
                                        </td>
                                        <td class="py-2">
                                            <select name="department_id" id="department_id" required 
                                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="" disabled selected class="bg-gray-300">Select Department</option>
                                                <?php while ($row = $departments->fetch_assoc()): ?>
                                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['department_name']; ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="account_number" class="text-gray-700">Account Number</label>
                                        </td>
                                        <td class="py-2">
                                            <input type="text" name="account_number" id="account_number" placeholder="Enter account number" required 
                                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="grade_id" class="text-gray-700">Grade</label>
                                        </td>
                                        <td class="py-2">
                                            <select name="grade_id" id="grade_id" required 
                                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="" disabled selected class="bg-gray-300">Select Grade</option>
                                                <?php while ($row = $grades->fetch_assoc()): ?>
                                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['grade']; ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="joining_date" class="text-gray-700">Joining Date</label>
                                        </td>
                                        <td class="py-2">
                                            <input type="date" name="joining_date" id="joining_date" required 
                                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <label for="e_tin" class="text-gray-700">E-TIN</label>
                                        </td>
                                        <td class="py-2">
                                            <input type="text" name="e_tin" id="e_tin" placeholder="Enter E-TIN" required 
                                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        <!-- Terms and Conditions -->
                        <button type="submit" class="mt-6 w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700"> Submit Request </button>
                        <div class="flex items-center mt-4">
                            <input type="checkbox" id="terms" class="checkbox checkbox-primary" required>
                            <label for="terms" class="text-gray-600 ml-2">I agree to the <span class="text-blue-500">Terms & Conditions</span></label>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>    
</body>
</html>
