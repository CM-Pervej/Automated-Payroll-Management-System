<?php
    include '../db_conn.php';
    session_start();

    // Check if the user is logged in and has the correct role (1 = Admin, 2 = HR)
    if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2)) {
        header('Location: ../dashboard.php'); // Redirect to dashboard if not Admin or HR
        exit();
    }

    // Check if the user is HR (userrole_id 2)
    $isHR = ($_SESSION['userrole_id'] == 2);
    
    // Fetch all roles for selection
    $roleQuery = "SELECT * FROM userRole";
    $roleResult = $conn->query($roleQuery);
    $roles = [];

    if ($roleResult->num_rows > 0) {
        while ($roleRow = $roleResult->fetch_assoc()) {
            $roles[] = $roleRow;
        }
    }

    // Handle the update request
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $userrole_id = $_POST['userrole_id'];
        $password = isset($_POST['password']) ? $_POST['password'] : ''; // Check if password is set

        // Check if the status is set in the POST request, else default to null or current value
        $status = isset($_POST['status']) ? $_POST['status'] : null;

        // If the user is HR (userrole_id 2), they cannot update users with role 1 or 2
        if ($isHR && ($userrole_id == 1 || $userrole_id == 2)) {
            $_SESSION['error'] = 'You cannot assign role to Admin (userrole_id = 1) or HR (userrole_id = 2).';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit(); // Redirect back to the form
        }

        // If password is provided, hash and update, otherwise skip password update
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE user SET name = ?, email = ?, userrole_id = ?, password = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssisis", $name, $email, $userrole_id, $hashedPassword, $status, $id);
        } else {
            // Skip password update if not provided
            $query = "UPDATE user SET name = ?, email = ?, userrole_id = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssisi", $name, $email, $userrole_id, $status, $id);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = 'User updated successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit(); // Redirect to show success
        } else {
            $_SESSION['error'] = 'Error updating user';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit(); // Redirect back to the form in case of failure
        }
    }

    // Fetch active users (status = 1)
    $activeQuery = "SELECT u.id, u.name, u.email, u.userrole_id, u.employeeNo, u.employee_id, u.status, r.role 
                    FROM user u
                    JOIN userRole r ON u.userrole_id = r.id
                    WHERE u.status = 1
                    ORDER BY u.userrole_id ASC";
    $activeResult = $conn->query($activeQuery);
    $activeUsers = [];

    if ($activeResult->num_rows > 0) {
        while ($row = $activeResult->fetch_assoc()) {
            $activeUsers[] = $row;
        }
    }

    // Fetch inactive users (status = 0)
    $inactiveQuery = "SELECT u.id, u.name, u.email, u.userrole_id, u.employeeNo, u.employee_id, u.status, r.role 
                      FROM user u
                      JOIN userRole r ON u.userrole_id = r.id
                      WHERE u.status = 0
                      ORDER BY u.userrole_id ASC";
    $inactiveResult = $conn->query($inactiveQuery);
    $inactiveUsers = [];

    if ($inactiveResult->num_rows > 0) {
        while ($row = $inactiveResult->fetch_assoc()) {
            $inactiveUsers[] = $row;
        }
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar (fixed) -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include '../sideBar.php'; ?>
    </header>
    <div class="flex flex-col flex-grow ml-64">
        <!-- Top Bar (fixed) -->
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include '../topBar.php'; ?>
            </aside>
        </div>
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="container mx-auto p-5">
                <div class="relative flex items-center justify-center mb-5">
                    <h1 class="text-3xl font-bold">User Management</h1>
                    <a href="userSet.php" class="absolute right-0 px-4 py-2 bg-blue-500 text-white rounded">ADD Users</a>
                </div>

                <!-- Display Error or Success Messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 text-red-700 p-4 mb-4 rounded">
                        <?php echo $_SESSION['error']; ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 text-green-700 p-4 mb-4 rounded">
                        <?php echo $_SESSION['success']; ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- Active Users Section -->
                <div class="mb-8" div id="active-section">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-semibold mb-4">Active Users</h2>
                        <h2 class="text-2xl font-semibold mb-4"><button onclick="toggleForm()" class="text-blue-600">Inactive Users</button></h1>
                    </div>
                    <table class="table-auto w-full border-collapse mb-6">
                        <thead>
                            <tr class="bg-green-100">
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">Role</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeUsers as $user): ?>
                            <tr class="bg-green-50">
                                <td class="border px-4 py-2"><?php echo $user['employeeNo']; ?></td>
                                <td class="border px-4 py-2">
                                    <a href="../profile.php?employee_id=<?php echo $user['employee_id']; ?>" class="text-blue-600 hover:underline">
                                        <?php echo $user['name']; ?>
                                    </a>
                                </td>
                                <td class="border px-4 py-2"><?php echo $user['email']; ?></td>
                                <td class="border px-4 py-2"><?php echo $user['role']; ?></td>
                                <td class="border px-4 py-2"><?php echo ($user['status'] == 1) ? 'Active' : 'Inactive'; ?></td>
                                <td class="border px-4 py-2 text-center">
                                    <button class="bg-blue-500 text-white px-4 py-1 rounded" 
                                            onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Inactive Users Section -->
                <div id="inactive-section" class="hidden">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-semibold mb-4"><button onclick="toggleForm()" class="text-blue-600">Active Users</button></h1>
                        <h2 class="text-2xl font-semibold mb-4">Inactive Users</h2>
                    </div>
                    <table class="table-auto w-full border-collapse">
                        <thead>
                            <tr class="bg-red-100">
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">Role</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inactiveUsers as $user): ?>
                                <tr class="bg-red-50">
                                    <td class="border px-4 py-2"><?php echo $user['employeeNo']; ?></td>
                                    <td class="border px-4 py-2">
                                        <a href="../profile.php?employee_id=<?php echo $user['employee_id']; ?>" class="text-blue-600 hover:underline">
                                            <?php echo $user['name']; ?>
                                        </a>
                                    </td>
                                    <td class="border px-4 py-2"><?php echo $user['email']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $user['role']; ?></td>
                                    <td class="border px-4 py-2"><?php echo ($user['status'] == 1) ? 'Active' : 'Inactive'; ?></td>
                                    <td class="border px-4 py-2 text-center">
                                        <button class="bg-blue-500 text-white px-4 py-1 rounded" 
                                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal for Editing User -->
            <div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                    <h2 class="text-xl font-bold mb-4">Edit User</h2>
                    <form id="editForm" action="" method="POST">
                        <input type="hidden" name="id" id="editUserId">
                        
                        <label class="block">Name:</label>
                        <input type="text" name="name" id="editName" class="border p-2 w-full rounded mb-2">
                        
                        <label class="block">Email:</label>
                        <input type="email" name="email" id="editEmail" class="border p-2 w-full rounded mb-2">
                        
                        <!-- Only show password field if user role is not HR -->
                        <?php if (!$isHR): ?>
                        <label class="block">Password:</label>
                        <input type="password" name="password" id="editPassword" class="border p-2 w-full rounded mb-2" placeholder="Leave blank to keep current password">
                        <?php endif; ?>
                        
                        <label class="block">User Role:</label>
                        <select name="userrole_id" id="editUserRole" class="border p-2 w-full rounded mb-2">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo $role['role']; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label class="block">Status:</label>
                        <select name="status" id="editStatus" class="border p-2 w-full rounded mb-2">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>

                        <div class="flex justify-end mt-4">
                            <button type="button" class="bg-gray-400 text-white px-4 py-2 rounded mr-2" onclick="closeEditModal()">Cancel</button>
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openEditModal(user) {
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editName').value = user.name;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editUserRole').value = user.userrole_id;
            document.getElementById('editStatus').value = user.status;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function toggleForm() {
            document.getElementById('active-section').classList.toggle('hidden');
            document.getElementById('inactive-section').classList.toggle('hidden');
        }
    </script>
</body>
</html>
