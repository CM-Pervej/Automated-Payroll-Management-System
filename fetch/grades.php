<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not authenticated
    header('Location: ../index.php');
    exit();
}

include '../db_conn.php';

// Fetch all grades
$grades = [];
$stmt = $conn->prepare("SELECT * FROM grade");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}
$stmt->close();

// Handle form submission
$successMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $grade = (int) htmlspecialchars($_POST['grade']);
    $increment = htmlspecialchars($_POST['increment']);
    $scale = htmlspecialchars($_POST['scale']);
    $gradePercentage = htmlspecialchars($_POST['gradePercentage']);

    // Check if grade already exists
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM grade WHERE grade = ?");
    $check_stmt->bind_param("i", $grade);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo "<p class='text-red-600'>Error: Grade already exists!</p>";
    } else {
        // Insert the data into the grade table
        $stmt = $conn->prepare("INSERT INTO grade (grade, increment, scale, gradePercentage) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            // If preparing the statement failed, output the error
            echo "Error preparing the statement: " . $conn->error;
        } else {
            $stmt->bind_param("iisd", $grade, $increment, $scale, $gradePercentage);

            // Check if the statement executes successfully
            if ($stmt->execute()) {
                // $successMessage = "Grade registered successfully!";
                $success_message = "New Grade  added successfully";
            } else {
                // echo "Error executing query: " . $stmt->error;
                $error_message = "Error executing query: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../sideBar.css">
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include 'sideBar.php'; ?>
    </header>

    <div class="flex flex-col flex-grow ml-64">
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include 'topBar.php'; ?>
            </aside>
        </div>
        
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="mx-auto bg-white p-10">
                <section class="flex justify-between mb-5">
                    <h1 class="text-2xl font-bold mb-4">Grade List</h1>
                    <div class="flex justify-end gap-5">
                        <button id="addGradeButton" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Grade
                        </button>
                        <a href="gradeChange.php" class="btn btn-info">Action</a>
                    </div>
                </section>

                <?php if (!empty($success_message)) : ?>
                    <p class="text-green-600 mb-4"><?php echo $success_message; ?></p>
                <?php elseif (!empty($error_message)) : ?>
                    <p class="text-red-600 mb-4"><?php echo $error_message; ?></p>
                <?php endif; ?>
                
                <?php if (empty($grades)): ?>
                    <p class="text-gray-600">No grades found.</p>
                <?php else: ?>
                    <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                        <thead class="bg-gray-400">
                            <tr class="w-full">
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold w-1/4">Grade</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold w-1/4">Increment</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold w-1/4">Scale</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold w-1/4">Grade Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($grades as $grade): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-6 py-4"><?= htmlspecialchars($grade['grade']); ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($grade['increment']); ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($grade['scale']); ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($grade['gradePercentage']); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div id="addGradeModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-lg font-bold mb-4">Add New Grade</h2>
            <form action="grades.php" method="POST" class="space-y-4" id="addReportForm">
                <div>
                    <label for="grade" class="block text-sm font-medium text-gray-700">Grade</label>
                    <input type="number" name="grade" id="grade" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="increment" class="block text-sm font-medium text-gray-700">Increment</label>
                    <input type="number" step="0.01" name="increment" id="increment" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="scale" class="block text-sm font-medium text-gray-700">Scale</label>
                    <input type="text" name="scale" id="scale" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="gradePercentage" class="block text-sm font-medium text-gray-700">Grade Percentage</label>
                    <input type="number" step="0.01" name="gradePercentage" id="gradePercentage" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeModal" class="text-gray-500 hover:text-gray-800 mr-2">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Add Grade</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        document.getElementById('addGradeButton').onclick = function() {
            document.getElementById('addGradeModal').classList.remove('hidden');
        };
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('addGradeModal').classList.add('hidden');
        };
    </script>
</body>
</html>
