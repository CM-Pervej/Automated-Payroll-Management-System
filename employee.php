<?php 
include 'view.php'; 
include 'db_conn.php';
$employees = [];

// Determine current view: show active or inactive users
$status = (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 2 : 1;

// Fetch employee data along with department name and designation
$query = "SELECT e.id, e.employeeNo, e.name, e.empStatus, e.grade_id, e.image, d.department_name, des.designation, g.grade
    FROM employee e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN grade g ON e.grade_id = g.id 
    WHERE e.approve != 0 AND e.empStatus = $status
    ORDER BY d.department_name ASC, g.grade ASC;";

$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include 'sideBar.php'; ?>
    </header>

    <!-- Main Content -->
    <div class="flex flex-col flex-grow ml-64">
        <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
            <?php include 'topBar.php'; ?>
        </aside>

        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div>
                <section class="flex justify-between items-center mb-5">
                    <h1 class="text-2xl font-bold">
                        <!-- Toggle Button -->
                        <?php
                            $toggleStatus = ($status == 1) ? 'inactive' : 'active';
                            $toggleLabel = ($status == 1) ? 'Active Employees' : 'Inactive Employees';
                        ?>
                        <a href="?status=<?php echo $toggleStatus; ?>" class="hover:border-b-2 hover:border-black">
                            <?php echo $toggleLabel; ?>
                        </a>
                    </h1>
                    <div class="flex justify-end gap-5">
                        <input type="text" id="search" class="p-3 w-full rounded-md border border-gray-300" placeholder="Search for employees...">
                        <a href="users/empReg.php" class="btn btn-primary" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'hidden' : ''; ?>>
                            Add Employee
                        </a>
                        <a href="registration/employee_action.php" class="btn btn-info" <?php echo ($userrole_id != 1 && $userrole_id != 2) ? 'hidden' : ''; ?>>
                            Action
                        </a>
                    </div>
                </section>

                <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg text-center">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="w-20">Profile</th>
                            <th class="px-4">Employee No</th>
                            <th class="text-left px-4">Name</th>
                            <th class="px-4">Grade</th>
                            <th class="text-left px-4">Designation</th>
                            <th class="text-left px-4">Department</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTable" class="bg-white divide-y divide-gray-200">
                        <?php if (empty($employees)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No employees found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employees as $employee): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="flex justify-center w-20 p-1">
                                        <?php 
                                            if (!empty($employee['image'])) {
                                                $imagePath = 'uploads/' . basename($employee['image']);
                                                if (file_exists($imagePath)) {
                                                    echo '<img src="' . $imagePath . '" alt="Profile Image" class="w-12 h-12 object-cover rounded-full border border-gray-300" />';
                                                } else {
                                                    echo '<p class="text-red-500">Image not found.</p>';
                                                }
                                            } else {
                                                echo '<p>No image available.</p>';
                                            }
                                        ?>
                                    </td>
                                    <td class="px-4"><?php echo htmlspecialchars($employee['employeeNo']); ?></td>
                                    <td class="text-left w-60 px-4">
                                        <a href="profile.php?employee_id=<?php echo $employee['id']; ?>" class="text-blue-600 hover:underline block w-60 text-ellipsis overflow-hidden whitespace-nowrap">
                                            <?php echo htmlspecialchars($employee['name']); ?>
                                        </a>
                                    </td>
                                    <td class="px-4"><?php echo htmlspecialchars($employee['grade']); ?></td>
                                    <td class="text-left px-4"><?php echo htmlspecialchars($employee['designation']); ?></td>
                                    <td class="text-left px-4"><p class="block w-72 text-ellipsis overflow-hidden whitespace-nowrap"><?php echo htmlspecialchars($employee['department_name']); ?></p></td>
                                    <td class="px-4">
                                        <?php 
                                            echo ($employee['empStatus'] == 1) ? 'Active' : 
                                                 (($employee['empStatus'] == 2) ? 'Inactive' : 'Unknown');
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
    // Simple search function
    document.getElementById('search').addEventListener('input', function(e) {
        const searchQuery = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#employeeTable tr');
        
        rows.forEach(row => {
            const columns = row.querySelectorAll('td');
            let match = false;

            columns.forEach(column => {
                if (column.innerText.toLowerCase().includes(searchQuery)) {
                    match = true;
                }
            });

            if (match) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>
