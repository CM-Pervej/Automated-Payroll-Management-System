<?php 
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not authenticated
    header('Location: index.php');
    exit();
}

// Include the database connection
include 'db_conn.php';
$employees = [];

// Fetch employee data along with department name and designation
$result = $conn->query("SELECT e.id, e.employeeNo, e.name, e.empStatus, e.grade_id, e.image, d.department_name, des.designation, g.grade
    FROM employee e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN grade g ON e.grade_id = g.id 
    INNER JOIN checkEmployee ce ON e.id = ce.employee_id
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Handle the deletion of an employee
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Ensure it's an integer

    // Establish a new connection to delete the employee
    include 'db_conn.php';
    
    // Delete from checkEmployee table
    $deleteQuery = "DELETE FROM checkEmployee WHERE employee_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $delete_id); // Bind the employee ID
    $stmt->execute();

    // Redirect back to the employee list page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle the deletion of all selected employees
if (isset($_POST['delete_all'])) {
    if (!empty($_POST['delete_ids'])) {
        $ids = implode(",", $_POST['delete_ids']); // Get all selected employee IDs
        $deleteQuery = "DELETE FROM checkEmployee WHERE employee_id IN ($ids)";
        $conn->query($deleteQuery); // Execute the deletion

        // Redirect back to the employee list page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unchecked Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <!-- TailWind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="sideBar.css">
    <script>
        function toggleSelectAll(source, name) {
            checkboxes = document.getElementsByName(name);
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Header -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include 'sideBar.php'; ?>
    </header>
    
    <!-- Main Content Area -->
    <div class="flex flex-col flex-grow ml-64">
        <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
            <?php include 'topBar.php'; ?>
        </aside>
        
        <!-- Content Section -->
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div>
                <form method="POST" action="">
                    <section class="flex justify-between mb-5">
                        <h1 class="text-2xl font-bold mb-4">Employee List</h1>
                        <div class="mt-4">
                            <button type="submit" name="delete_all" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700">Delete Selected</button>
                        </div>
                    </section>
                    <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="px-4 py-2 text-left"><input type="checkbox" onclick="toggleSelectAll(this, 'delete_ids[]')"> All</th> <!-- Added Select Column -->
                                <th class="px-4 py-2 text-left">Image</th>
                                <th class="px-4 py-2 text-left">Employee No</th>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Grade</th>
                                <th class="px-4 py-2 text-left">Designation</th>
                                <th class="px-4 py-2 text-left">Department</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Actions</th> <!-- Added Actions Column -->
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($employees)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">No employees found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($employees as $employee): ?>
                                    <tr class="hover:bg-gray-100">
                                        <td class="px-4 py-2">
                                            <input type="checkbox" name="delete_ids[]" value="<?php echo $employee['id']; ?>" />
                                        </td>
                                        <td class="px-4 py-2">
                                            <p>
                                                <?php 
                                                    if (!empty($employee['image'])) {
                                                        $imagePath = 'uploads/' . basename($employee['image']);
                                                        
                                                        // Check if the file exists before displaying
                                                        if (file_exists($imagePath)): 
                                                ?>
                                                            <img src="<?php echo $imagePath; ?>" alt="Profile Image" class="w-14 h-14 object-cover rounded-full border border-gray-300" />
                                                <?php else: ?>
                                                    <p class="text-red-500">Image file does not exist at: <?php echo htmlspecialchars($imagePath); ?></p>
                                                <?php 
                                                        endif; 
                                                    } else {
                                                ?>
                                                    <p>No image available for this employee.</p>
                                                <?php } ?>
                                            </p>
                                        </td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($employee['employeeNo']); ?></td>
                                        <td class="px-4 py-2">
                                            <a href="profile.php?employee_id=<?php echo $employee['id']; ?>" class="text-blue-600 hover:underline">
                                                <?php echo htmlspecialchars($employee['name']); ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($employee['grade']); ?></td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($employee['designation']); ?></td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($employee['department_name']); ?></td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($employee['empStatus']); ?></td>
                                        <td class="px-4 py-2">
                                            <!-- Delete Button -->
                                            <a href="?delete_id=<?php echo $employee['id']; ?>" class="text-red-600 hover:underline">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
