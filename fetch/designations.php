<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not authenticated
    header('Location: ../index.php');
    exit();
}

// Include the database connection file
include '../db_conn.php'; 

// Fetch all designations
$designations = [];
$stmt = $conn->prepare("SELECT * FROM designations");
$stmt->execute();
$result = $stmt->get_result();

// Fetch all designations into the array
while ($row = $result->fetch_assoc()) {
    $designations[] = $row;
}

// Close the statement
$stmt->close();

// Handle form submission for adding new designation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $designation = $_POST['designation'];
    
    // Prepare and bind SQL statement
    $stmt = $conn->prepare("INSERT INTO designations (designation) VALUES (?)");
    $stmt->bind_param("s", $designation);
    
    // Execute the statement and check for success
    if ($stmt->execute()) {
        $success_message = "New designation added successfully";
        // Refresh designations array to include the new designation
        $designations[] = ["designation" => $designation];
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Designation List</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../sideBar.css">
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
            <div class="mx-auto bg-white p-10">
                <section class="flex justify-between mb-5">
                    <h1 class="text-2xl font-bold mb-4">Designation List</h1>
                    <div class="flex justify-end gap-5">
                        <button id="addDesignationButton" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Designation
                        </button>
                        <a href="desigChange.php" class="btn btn-info">Action</a>
                    </div>
                </section>

                <?php if (!empty($success_message)) : ?>
                    <p class="text-green-600 mb-4"><?php echo $success_message; ?></p>
                <?php elseif (!empty($error_message)) : ?>
                    <p class="text-red-600 mb-4"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <?php if (empty($designations)) : ?>
                    <p class="text-gray-600">No designations found.</p>
                <?php else : ?>
                    <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                        <thead class="bg-gray-400">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold">Designation Name</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($designations as $designation) : ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($designation['designation']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal for Adding Designation -->
    <div id="addDesignationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-lg font-bold mb-4">Add New Designation</h2>
            <form action="" method="post" class="space-y-4" id="addReportForm">
                <div>
                    <label for="designation" class="block text-lg font-semibold mb-2">Designation</label>
                    <input type="text" id="designation" name="designation" placeholder="Add new designation" required 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Add</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('addDesignationButton').onclick = function() {
            document.getElementById('addDesignationModal').classList.remove('hidden');
        };
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('addDesignationModal').classList.add('hidden');
        };
    </script>
</body>
</html>
