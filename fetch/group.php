<?php 
include '../view.php';
include '../db_conn.php';

// Fetch the parameters from the URL
$grade_id = isset($_GET['grade_id']) ? (int) $_GET['grade_id'] : 0;
$designation_id = isset($_GET['designation_id']) ? (int) $_GET['designation_id'] : 0;
$department_id = isset($_GET['department_id']) ? (int) $_GET['department_id'] : 0;
$addDuty_id = isset($_GET['AdditionalDesignation_id']) ? (int) $_GET['AdditionalDesignation_id'] : 0;
$empStatus = isset($_GET['empStatus']) ? (int) $_GET['empStatus'] : 1; // Default to active (1)

// Initialize variables to store names
$grade_name = '';
$designation_name = '';
$department_name = '';
$additional_designation_name = '';

// Fetch the grade name if grade_id is provided
if ($grade_id > 0) {
    $stmt = $conn->prepare("SELECT grade FROM grade WHERE id = ?");
    $stmt->bind_param("i", $grade_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $grade_name = $row['grade'];
    }
    $stmt->close();
}

// Fetch the designation name if designation_id is provided
if ($designation_id > 0) {
    $stmt = $conn->prepare("SELECT designation FROM designations WHERE id = ?");
    $stmt->bind_param("i", $designation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $designation_name = $row['designation'];
    }
    $stmt->close();
}

// Fetch the department name if department_id is provided
if ($department_id > 0) {
    $stmt = $conn->prepare("SELECT department_name FROM departments WHERE id = ?");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $department_name = $row['department_name'];
    }
    $stmt->close();
}

// Fetch the additional designation name if addDuty_id is provided
if ($addDuty_id > 0) {
    $stmt = $conn->prepare("SELECT designation FROM addDuty WHERE id = ?");
    $stmt->bind_param("i", $addDuty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $additional_designation_name = $row['designation'];
    }
    $stmt->close();
}

// Build SQL query based on the provided filters
$whereClauses = [];
$params = [];

if ($grade_id > 0) {
    $whereClauses[] = "e.grade_id = ?";
    $params[] = $grade_id;
}

if ($designation_id > 0) {
    $whereClauses[] = "e.designation_id = ?";
    $params[] = $designation_id;
}

if ($department_id > 0) {
    $whereClauses[] = "e.department_id = ?";
    $params[] = $department_id;
}

if ($addDuty_id > 0) {
    $whereClauses[] = "ad.addDuty_id = ?";
    $params[] = $addDuty_id;
}

// Default query with empStatus filter for active (1) or inactive (2) employees
$query = "SELECT e.*, d.department_name, des.designation, ad.AdditionalDesignation
          FROM employee e 
          JOIN departments d ON e.department_id = d.id 
          JOIN designations des ON e.designation_id = des.id
          LEFT JOIN empAddSalary ea ON e.id = ea.employee_id
          LEFT JOIN empAddDesignation ad ON ea.id = ad.empAddSalary_id
          WHERE e.empStatus = ? AND e.approve = 1";

if (count($whereClauses) > 0) {
    $query .= " AND " . implode(" AND ", $whereClauses);
}

// Grouping by employee ID to avoid duplicates
$query .= " GROUP BY e.id ORDER BY d.department_name, e.grade_id, e.name";

// Prepare the SQL query and bind parameters
$stmt = $conn->prepare($query);
$params = array_merge([$empStatus], $params);

$types = str_repeat('i', count($params)); // 'i' for integers
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Store the results grouped by department
$employees_by_department = [];
while ($row = $result->fetch_assoc()) {
    $employees_by_department[$row['department_name']][] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
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
                <h1 class="text-2xl text-center font-bold mb-4">
                    <?php if ($grade_name): ?>
                        Currently Viewing Employees in Grade: <?= htmlspecialchars($grade_name); ?>
                    <?php endif; ?>
                    <?php if ($designation_name): ?>
                        Designation: <?= htmlspecialchars($designation_name); ?>
                    <?php endif; ?>
                    <?php if ($department_name): ?>
                        Department: <?= htmlspecialchars($department_name); ?> 
                    <?php endif; ?>
                    <?php if ($additional_designation_name): ?>
                        Additional Designation: <?= htmlspecialchars($additional_designation_name); ?>
                    <?php endif; ?>
                </h1>

                <!-- Buttons to toggle between active and inactive employees -->
                <div class="flex justify-end mb-5">
                    <a href="?empStatus=1&grade_id=<?= $grade_id ?>&designation_id=<?= $designation_id ?>&department_id=<?= $department_id ?>&AdditionalDesignation_id=<?= $addDuty_id ?>" class="<?= $empStatus == 1 ? 'hidden' : 'text-blue-600 text-xl font-bold hover:underline' ?>">Inactive Employees</a>
                    <a href="?empStatus=2&grade_id=<?= $grade_id ?>&designation_id=<?= $designation_id ?>&department_id=<?= $department_id ?>&AdditionalDesignation_id=<?= $addDuty_id ?>" class="<?= $empStatus == 2 ? 'hidden' : 'text-blue-600 text-xl font-bold hover:underline' ?>">Active Employees</a>
                </div>

                <?php if (empty($employees_by_department)): ?>
                    <p class="text-gray-600">No employees found for the selected filters.</p>
                <?php else: ?>
                    <?php foreach ($employees_by_department as $department_name => $employees): ?>
                        <section class="mb-8">
                            <h2 class="text-2xl font-extrabold mb-4 text-fuchsia-800"><?= htmlspecialchars($department_name); ?> </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                <?php foreach ($employees as $employee): ?>
                                    <a href="/payroll/profile.php?employee_id=<?php echo $employee['id']; ?>" class="block bg-blue-50 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 ease-in-out hover:scale-105 hover:bg-blue-100 hover:border-2 hover:border-blue-300">
                                        <div class="flex items-center justify-center mb-4">
                                            <?php 
                                                if (!empty($employee['image'])) {
                                                    $imagePath = '../uploads/' . basename($employee['image']);
                                                    if (file_exists($imagePath)) {
                                                        echo '<img src="' . $imagePath . '" alt="Profile Image" class="h-72 w-full object-cover" />';
                                                    } else {
                                                        echo '<img src="uploads/default-avatar.png" alt="Profile Image" class="h-72 w-full object-cover" />';
                                                    }
                                                } else {
                                                    echo '<img src="uploads/default-avatar.png" alt="Profile Image" class="h-72 w-full object-cover" />';
                                                }
                                            ?>
                                        </div>
                                        <div class="px-6 pb-6">
                                            <h2 class="text-lg text-blue-600 font-semibold mb-2 overflow-hidden whitespace-nowrap text-ellipsis">
                                                <?= htmlspecialchars($employee['name']); ?>
                                            </h2>

                                            <p class="text-gray-600 font-semibold"><i class="fas fa-briefcase mr-2"></i><?= htmlspecialchars($employee['designation']); ?></p>
                                            <p class="text-sm text-gray-600 my-1 overflow-hidden whitespace-nowrap text-ellipsis"><i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($employee['email']); ?></p>
                                            <p class="text-sm text-gray-600"><i class="fas fa-phone mr-2"></i><?= htmlspecialchars($employee['contactNo']); ?></p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
