<?php
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }

    if (!isset($_SESSION['userrole_id'])) {
        header('Location: index.php');  // Or handle the error as needed
        exit();
    }

// Include the database connection
include 'db_conn.php';

$searchResults = [];
$errorMessage = "";

// Get the keyword from the query string
$keyword = $_GET['keyword'] ?? '';

if (!empty($keyword)) {
    // Search query across key fields
    $query = "
        SELECT e.*, d.designation, dep.department_name, g.grade 
        FROM employee e 
        LEFT JOIN designations d ON e.designation_id = d.id 
        LEFT JOIN departments dep ON e.department_id = dep.id 
        LEFT JOIN grade g ON e.grade_id = g.id 
        WHERE e.employeeNo LIKE ? 
           OR e.name LIKE ? 
           OR e.email LIKE ? 
           OR e.contactNo LIKE ? 
           OR e.gender = ? 
           OR e.empStatus = ? 
           OR g.grade = ? 
           OR dep.department_name = ? 
           OR d.designation = ?
    ";

    $param = '%' . $keyword . '%';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssssssss', $param, $param, $param, $param, $keyword, $keyword, $keyword, $keyword, $keyword);
    $stmt->execute();
    $searchResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($searchResults)) {
        $errorMessage = "No matching employees found.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar (fixed) -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include 'sideBar.php'; ?>
    </header>

    <!-- Main Content Area -->
    <div class="flex flex-col flex-grow ml-64">
        <!-- Top Bar (fixed) -->
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include 'topBar.php'; ?>
            </aside>
        </div>

        <!-- Content Section -->
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="container mx-auto">
                <div class="bg-white rounded-lg">
                    <h1 class="text-3xl font-semibold text-center text-gray-800">Search Results for "<?php echo htmlspecialchars($keyword); ?>"</h1>

                    <?php if (!empty($errorMessage)): ?>
                        <div class="text-red-600 text-center mb-4"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($searchResults)): ?>
                        <table class="w-full border border-gray-300 mt-5">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border">Employee No</th>
                                    <th class="px-4 py-2 border">Name</th>
                                    <th class="px-4 py-2 border">Email</th>
                                    <th class="px-4 py-2 border">Contact No</th>
                                    <th class="px-4 py-2 border">Designation</th>
                                    <th class="px-4 py-2 border">Department</th>
                                    <th class="px-4 py-2 border">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($searchResults as $employee): ?>
                                    <tr>
                                        <td class="px-4 py-2 border text-center"><?php echo htmlspecialchars($employee['employeeNo']); ?></td>
                                        <td class="px-4 py-2">
                                            <a href="profile.php?employee_id=<?php echo $employee['id']; ?>" class="text-blue-600 hover:underline">
                                                <?php echo htmlspecialchars($employee['name']); ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td class="px-4 py-2 border text-center"><?php echo htmlspecialchars($employee['contactNo']); ?></td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['designation']); ?></td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['department_name']); ?></td>
                                        <td class="px-4 py-2 border text-center"><?php echo htmlspecialchars($employee['grade']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
