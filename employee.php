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
    WHERE e.approve != 0 AND e.empStatus = $status;";

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
                        <a href="users/empReg.php" class="btn btn-primary" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'hidden' : ''; ?>>
                            Add Employee
                        </a>
                        <a href="registration/employee_action.php" class="btn btn-info" <?php echo ($userrole_id != 1 && $userrole_id != 2) ? 'hidden' : ''; ?>>
                            Action
                        </a>
                    </div>
                </section>

                <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2 text-left">Profile Image</th>
                            <th class="px-4 py-2 text-left">Employee No</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Grade</th>
                            <th class="px-4 py-2 text-left">Designation</th>
                            <th class="px-4 py-2 text-left">Department</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($employees)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No employees found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employees as $employee): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-4 py-2">
                                        <?php 
                                            if (!empty($employee['image'])) {
                                                $imagePath = 'uploads/' . basename($employee['image']);
                                                if (file_exists($imagePath)) {
                                                    echo '<img src="' . $imagePath . '" alt="Profile Image" class="w-14 h-14 object-cover rounded-full border border-gray-300" />';
                                                } else {
                                                    echo '<p class="text-red-500">Image not found.</p>';
                                                }
                                            } else {
                                                echo '<p>No image available.</p>';
                                            }
                                        ?>
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
                                    <td class="px-4 py-2">
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
</body>
</html>
