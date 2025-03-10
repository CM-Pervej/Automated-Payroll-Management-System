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
include('db_conn.php');

// // Fetch user details
// $user_id = $_SESSION['user_id'];
// $query = "SELECT name, employeeNo, email, userrole_id FROM user WHERE id = ?";
// if ($stmt = $conn->prepare($query)) {
//     $stmt->bind_param('i', $user_id);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     if ($result->num_rows > 0) {
//         $user = $result->fetch_assoc();
//     } else {
//         header('Location: index.php');
//         exit();
//     }
//     $stmt->close();
// }

// // Get the user role
$userrole_id = $_SESSION['userrole_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="sideBar.css">
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
            <!-- Cards for Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-green-100 p-4 rounded-lg shadow-md flex items-center space-x-4">
                    <i class="fas fa-users text-3xl text-green-600"></i>
                    <div>
                        <p class="text-lg font-semibold">Employees</p>
                        <p class="text-gray-600">120 Active</p>
                    </div>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg shadow-md flex items-center space-x-4">
                    <i class="fas fa-wallet text-3xl text-yellow-600"></i>
                    <div>
                        <p class="text-lg font-semibold">Payrolls</p>
                        <p class="text-gray-600">Pending: 5</p>
                    </div>
                </div>
                <div class="bg-blue-100 p-4 rounded-lg shadow-md flex items-center space-x-4">
                    <i class="fas fa-cogs text-3xl text-blue-600"></i>
                    <div>
                        <p class="text-lg font-semibold">Settings</p>
                        <p class="text-gray-600">Configure Payroll</p>
                    </div>
                </div>
            </div>

            <!-- Links to different pages (Visible to all but disabled based on role) -->
            <div class="flex gap-4 mb-8">
                <a href="users/admin.php" class="btn btn-primary" <?php echo ($userrole_id != 1) ? 'disabled' : ''; ?> title="Only Admin can access this page">
                    Admin Page
                </a>
                <a href="users/hr.php" class="btn btn-primary" <?php echo ($userrole_id != 1 && $userrole_id != 2) ? 'disabled' : ''; ?> title="Only Admin or HR can access this page">
                    HR Page
                </a>
                <a href="users/user1.php" class="btn btn-primary" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 3) ? 'disabled' : ''; ?> title="Only User1 can access this page">
                    User 1 Page
                </a>
                <a href="users/user2.php" class="btn btn-primary" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 4) ? 'disabled' : ''; ?> title="Only User2 can access this page">
                    User 2 Page
                </a>
            </div>

            <!-- Additional Dashboard Content -->
            <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
                <h2 class="text-2xl font-bold mb-4">Dashboard Insights</h2>
                <p class="text-gray-700">View comprehensive insights on payroll activities, employee statistics, and review records.</p>
            </div>
        </main>
    </div>
</body>
</html>
