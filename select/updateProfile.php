<?php
session_start();

// Check if the user is logged in and is User1
if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2 && $_SESSION['userrole_id'] != 3 && $_SESSION['userrole_id'] != 4)) {
    header('Location: ../dashboard.php'); // Redirect to dashboard if not User1
    exit();
}
// Fetch the user role from the session
$userrole_id = $_SESSION['userrole_id'];

include '../db_conn.php';

$employee_id = $_GET['employee_id'] ?? null;
$employee = null;

if ($employee_id) {
    $stmt = $conn->prepare("SELECT e.id, e.name, e.date_of_birth, e.gender, e.contactNo, e.email, e.empStatus, e.image, e.designation_id, e.department_id, e.no_of_increment, e.basic, e.account_number, e.grade_id, e.joining_date, e.e_tin, d.designation AS primary_designation, dept.department_name, g.grade 
                            FROM employee e 
                            JOIN designations d ON e.designation_id = d.id 
                            JOIN departments dept ON e.department_id = dept.id 
                            JOIN grade g ON e.grade_id = g.id 
                            WHERE e.id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();
}

// Fetch all designations
$designations = [];
$designation_stmt = $conn->prepare("SELECT id, designation FROM designations");
$designation_stmt->execute();
$designation_result = $designation_stmt->get_result();
while ($row = $designation_result->fetch_assoc()) {
    $designations[] = $row;
}
$designation_stmt->close();

// Fetch all departments
$departments = [];
$department_stmt = $conn->prepare("SELECT id, department_name FROM departments");
$department_stmt->execute();
$department_result = $department_stmt->get_result();
while ($row = $department_result->fetch_assoc()) {
    $departments[] = $row;
}
$department_stmt->close();

// Fetch all grades
$grades = [];
$grade_stmt = $conn->prepare("SELECT id, grade, scale, gradePercentage FROM grade");
$grade_stmt->execute();
$grade_result = $grade_stmt->get_result();
while ($row = $grade_result->fetch_assoc()) {
    $grades[] = $row;
}
$grade_stmt->close();

// Update employee details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $name = $_POST['name'];
    $designation_id = $_POST['designation_id'];
    $department_id = $_POST['department_id'];
    $no_of_increment = $_POST['no_of_increment'];
    $account_number = $_POST['account_number'];
    $grade_id = $_POST['grade_id'];
    $joining_date = $_POST['joining_date'];
    $e_tin = $_POST['e_tin'];
    $gender = $_POST['gender'];
    $contactNo = $_POST['contactNo'];
    $email = $_POST['email'];
    $empStatus = $_POST['empStatus'];
    $date_of_birth = $_POST['date_of_birth'];

    // Handle file upload
    $imagePath = $employee['image'];
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        $imagePath = $targetDir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
    }

    // Fetch the scale and grade percentage based on the selected grade
    $scale_stmt = $conn->prepare("SELECT scale, gradePercentage FROM grade WHERE id = ?");
    $scale_stmt->bind_param("i", $grade_id);
    $scale_stmt->execute();
    $scale_stmt->bind_result($scale, $gradePercentage);
    $scale_stmt->fetch();
    $scale_stmt->close();

    // Calculate basic salary based on grade and increments
    $basic_salary = $scale;

    if ($no_of_increment > 0) {
        // First increment
        $basic_salary += $scale * ($gradePercentage / 100);

        // Further increments
        for ($i = 1; $i < $no_of_increment; $i++) {
            $basic_salary += ceil($basic_salary * ($gradePercentage / 100) / 10) * 10;
        }
    }

    // Round up to the nearest ten
    $basic_salary = ceil($basic_salary / 10) * 10;

    // Update employee details with the new basic salary
    $update_employee_stmt = $conn->prepare("UPDATE employee SET name=?, date_of_birth=?, gender=?, contactNo=?, email=?, empStatus=?, designation_id=?, department_id=?, no_of_increment=?, basic=?, account_number=?, grade_id=?, joining_date=?, e_tin=?, image=? WHERE id=?");
    $update_employee_stmt->bind_param("ssssssiisssisssi", $name, $date_of_birth, $gender, $contactNo, $email, $empStatus, $designation_id, $department_id, $no_of_increment, $basic_salary, $account_number, $grade_id, $joining_date, $e_tin, $imagePath, $employee_id);
    $update_employee_stmt->execute();
    $update_employee_stmt->close();

    // Redirect to profile page after successful update
    header("Location: ../profile.php?employee_id=$employee_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Employee Profile</title>
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
            <div class="container mx-auto p-8 bg-white shadow-lg rounded-lg mt-8">
                <?php if ($employee): ?>
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Employee Basic Information -->
                        <section class="grid grid-cols-1 lg:grid-cols-2">
                            <article class="space-y-6 border-t border-r border-black p-5">
                                <!-- image  -->
                                <div class="flex items-center space-x-4">
                                    <label class="text-gray-700">Profile</label>
                                    <div class="py-2 flex flex-col gap-2">
                                        <input type="file" id="image" name="image" class="mt-1 block border border-gray-300 rounded-md" accept="image/*" onchange="previewImage(event)">
                                        <p class="flex gap-2 w-24 h-24 justify-between items-center">
                                            <?php if ($employee['image']): ?>
                                                <img src="<?php echo htmlspecialchars($employee['image']); ?>" alt="Current Image" class="mt-2 w-24 h-24 rounded whitespace-nowrap">
                                            <?php endif; ?>
                                            <img id="imagePreview" class="image-preview hidden" alt="Image Preview" class="mt-2 rounded whitespace-nowrap">
                                        </p>
                                    </div>
                                </div>
                                <!-- name -->
                                <div class="flex items-center space-x-4">
                                    <label for="name" class="w-1/3 text-lg font-medium text-gray-800">Name</label>
                                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required class="input input-bordered w-full rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                                </div>

                                <!-- birth date -->
                                <div class="flex items-center space-x-4">
                                    <label for="date_of_birth" class="w-1/3 text-lg font-medium text-gray-800">Date of Birth</label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo htmlspecialchars($employee['date_of_birth']); ?>" class="input input-bordered w-full rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                                </div>

                                <!-- Gender Select -->
                                <div class="flex items-center space-x-4">
                                    <label for="gender" class="w-1/3 text-lg font-medium text-gray-800">Gender</label>
                                    <select name="gender" id="gender" required 
                                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        <option class="text-black" value="Male" <?php echo ($employee['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($employee['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($employee['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <!-- Contact -->
                                <div class="flex items-center space-x-4">
                                    <label for="contactNo" class="w-1/3 text-lg font-medium text-gray-800">Contact</label>
                                    <input type="text" name="contactNo" id="contactNo" value="<?php echo htmlspecialchars($employee['contactNo']); ?>" required class="input input-bordered w-full mt-2" />
                                </div>

                                <!-- Email -->
                                <div class="flex items-center space-x-4">
                                    <label for="email" class="w-1/3 text-lg font-medium text-gray-800">Email</label>
                                    <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required class="input input-bordered w-full mt-2" />
                                </div>
                            </article>
                            <article class="space-y-6 border-b border-black p-5">
                                <!-- Employee Status Select -->
                                <div class="<?php echo ($userrole_id == 3) ? 'hidden' : 'flex items-center space-x-4'; ?>">
                                    <label for="empStatus" class="w-1/3 text-lg font-medium text-gray-800">Employee Status</label>
                                    <select name="empStatus" id="empStatus" required 
                                            class="select select-bordered w-2/3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="Active" <?php echo ($employee['empStatus'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="In Active" <?php echo ($employee['empStatus'] == 'In Active') ? 'selected' : ''; ?>>In Active</option>
                                    </select>
                                </div>
                                <div class="<?php echo ($userrole_id == 3) ? 'hidden' : 'flex items-center space-x-4'; ?>">
                                    <label for="designation_id" class="w-1/3 text-lg font-medium text-gray-800">Designation</label>
                                    <select name="designation_id" id="designation_id" required class="select select-bordered w-2/3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <?php foreach ($designations as $designation): ?>
                                            <option value="<?php echo $designation['id']; ?>" <?php echo ($designation['id'] == $employee['designation_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($designation['designation']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="<?php echo ($userrole_id == 3) ? 'hidden' : 'flex items-center space-x-4'; ?>">
                                    <label for="department_id" class="w-1/3 text-lg font-medium text-gray-800">Department</label>
                                    <select name="department_id" id="department_id" required class="select select-bordered w-2/3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <?php foreach ($departments as $department): ?>
                                            <option value="<?php echo $department['id']; ?>" <?php echo ($department['id'] == $employee['department_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($department['department_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="<?php echo ($userrole_id == 3) ? 'hidden' : 'flex items-center space-x-4'; ?>">
                                    <label for="grade_id" class="w-1/3 text-lg font-medium text-gray-800">Grade</label>
                                    <select name="grade_id" id="grade_id" required class="select select-bordered w-2/3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <?php foreach ($grades as $grade): ?>
                                            <option value="<?php echo $grade['id']; ?>" <?php echo ($grade['id'] == $employee['grade_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($grade['grade']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="<?php echo ($userrole_id == 3) ? 'hidden' : 'flex items-center space-x-4'; ?>">
                                    <label for="no_of_increment" class="w-1/3 text-lg font-medium text-gray-800">No. of Increments</label>
                                    <input type="number" name="no_of_increment" id="no_of_increment" value="<?php echo htmlspecialchars($employee['no_of_increment']); ?>" required class="input input-bordered w-2/3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                </div>
                                <div class="<?php echo ($userrole_id == 3) ? 'hidden' : 'flex items-center space-x-4'; ?>">
                                    <label for="account_number" class="w-1/3 text-lg font-medium text-gray-800">Account Number</label>
                                    <input type="text" name="account_number" id="account_number" value="<?php echo htmlspecialchars($employee['account_number']); ?>" class="input input-bordered w-2/3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                </div>
                                <div class="<?php echo ($userrole_id == 3) ? 'hidden' : 'flex items-center space-x-4'; ?>">
                                    <label for="joining_date" class="w-1/3 text-lg font-medium text-gray-800">Joining Date</label>
                                    <input type="date" name="joining_date" id="joining_date" value="<?php echo htmlspecialchars($employee['joining_date']); ?>" class="input input-bordered w-2/3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                </div>
                                <div class="<?php echo ($userrole_id == 3) ? 'hidden' : 'flex items-center space-x-4'; ?>">
                                    <label for="e_tin" class="w-1/3 text-lg font-medium text-gray-800">E-TIN</label>
                                    <input type="text" name="e_tin" id="e_tin" value="<?php echo htmlspecialchars($employee['e_tin']); ?>" class="input input-bordered w-2/3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                </div>
                            </article>
                        </section>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-full mt-8">Update Employee</button>
                    </form>
                <?php else: ?>
                    <p class="text-red-500 text-lg font-semibold mt-8">Employee not found!</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        function previewImage(event) {
            const imagePreview = document.getElementById('imagePreview');
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('hidden');
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
