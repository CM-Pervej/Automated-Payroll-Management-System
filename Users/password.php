<?php
// Include the database connection
include '../db_conn.php';

// Retrieve the employee ID from the query parameters
$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

if ($employee_id <= 0) {
    echo "Invalid employee ID.";
    exit();
}

$successMessage = "";
$errorMessage = "";

// Fetch user role information
$userrole = $conn->query("SELECT id, role FROM userrole where id != 1");

// Fetch employee details
$employee_stmt = $conn->prepare("SELECT id, employeeNo, name, email FROM employee WHERE id = ?");
$employee_stmt->bind_param("i", $employee_id);
$employee_stmt->execute();
$employee_stmt->bind_result($employeeId, $employeeNo, $employeeName, $employeeEmail);
$employee_stmt->fetch();
$employee_stmt->close();

if (!$employeeId || !$employeeNo || !$employeeName) {
    echo "Employee not found.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate form data
    $name = strtoupper(trim(htmlspecialchars($_POST['name'])));
    $userrole_id = (int) htmlspecialchars(trim($_POST['userrole_id']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Check if the employee_id already exists in the user table
    $check_stmt = $conn->prepare("SELECT id FROM user WHERE employee_id = ?");
    $check_stmt->bind_param("i", $employee_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $errorMessage = "This user is already registered.";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into the user table
        $stmt = $conn->prepare("INSERT INTO user (employee_id, employeeNo, name, userrole_id, email, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississ", $employeeId, $employeeNo, $name, $userrole_id, $email, $hashed_password);

        if ($stmt->execute()) {
            // Redirect to profile page after successful registration
            echo "<script>
            alert('Registration done successfully, wait for the approval process to finish');
            window.location.href = '../index.php';
        </script>";
        exit;
        } else {
            $errorMessage = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }

        // Display error message if user is already registered
        <?php if ($errorMessage != ""): ?>
            alert("<?php echo $errorMessage; ?>");
        <?php endif; ?>
    </script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <?php if (!empty($errorMessages)): ?>
            <div class="text-red-600 text-center"><?php echo implode('<br>', $errorMessages); ?></div>
        <?php endif; ?>

        <h2 class="text-2xl font-bold text-gray-800 mb-4">Register User</h2>
        <form method="POST" action="" onsubmit="return validateForm()">
            <!-- Employee ID (Read-Only) -->
            <div class="mb-4">
                <label for="employeeId" class="block text-gray-700 font-medium mb-2">Employee ID</label>
                <input type="text" id="employeeId" value="<?php echo $employeeId; ?>" readonly
                       class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100">
            </div>
            <!-- Employee Number (Read-Only) -->
            <div class="mb-4">
                <label for="employeeNo" class="block text-gray-700 font-medium mb-2">Employee Number</label>
                <input type="text" id="employeeNo" value="<?php echo $employeeNo; ?>" readonly
                       class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100">
            </div>
            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                <input type="text" name="name" id="name" value="<?php echo $employeeName; ?>" readonly
                       class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100">
            </div>
            <div>
                <label for="userrole_id" class="text-gray-700">Role</label>
                <p class="py-2">
                    <select name="userrole_id" id="userrole_id" required 
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="" disabled selected class="bg-gray-300">Select Role</option>
                        <?php while ($row = $userrole->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['role']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </p>
            </div>
            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" name="email" id="email" value="<?php echo $employeeEmail; ?>" readonly
                       class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100">
            </div>
            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" name="password" id="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>
            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                <input type="password" id="confirm_password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>
            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700">
                Register
            </button>
        </form>
    </div>
</body>
</html>
