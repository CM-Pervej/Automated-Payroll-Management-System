<?php
session_start();

// Check if the user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2 && $_SESSION['userrole_id'] != 3 && $_SESSION['userrole_id'] != 4)) {
    header('Location: ../dashboard.php'); // Redirect to dashboard if not Admin
    exit();
}

include '../db_conn.php';

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the real name, email, employee_id, and userrole_id of the user
$query = "SELECT name, email, employee_id, userrole_id FROM user WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($realName, $realEmail, $employee_id, $userrole_id);  // Add userrole_id here
$stmt->fetch();
$stmt->close();

// Handle the password change request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];

    // Fetch the user's current password from the database
    $query = "SELECT password FROM user WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Check if the current password matches the one in the database
    if (password_verify($currentPassword, $hashedPassword)) {
        // Check if the new password matches the confirm password
        if ($newPassword === $confirmNewPassword) {
            // Hash the new password for security
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password in the database
            $updateQuery = "UPDATE user SET password = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $hashedNewPassword, $user_id);
            if ($updateStmt->execute()) {
                $successMessage = "Password updated successfully!";
            } else {
                $errorMessage = "Failed to update password. Please try again.";
            }
            $updateStmt->close();
        } else {
            $errorMessage = "New password and confirm password do not match.";
        }
    } else {
        $errorMessage = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="sideBar.css">
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar (fixed) -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include '../sideBar.php'; ?>
    </header>

    <main class="flex flex-col flex-grow ml-64">
        <!-- Top Bar (fixed) -->
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include '../topBar.php'; ?>
            </aside>
        </div>

        <!-- Content Section -->
         <section class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="settings-page p-6 bg-gray-50 rounded-lg shadow-md">
                <!-- User Profile Section -->
                <div class="user-profile bg-white p-6 rounded-lg shadow-sm mb-6 flex gap-5 justify-between items-center">
                    <p class="text-3xl font-bold text-gray-600"><?php echo htmlspecialchars($realName); ?></p>
                    <p class="text-lg text-gray-600"><?php echo htmlspecialchars($realEmail); ?></p>
                    <!-- Show this section only if the userrole_id is 1 or 2 -->
                    <?php if ($userrole_id == 1 || $userrole_id == 2): ?>
                        <section class="flex justify-end items-center gap-4">
                            <a href="admin.php"  class="text-blue-600 hover:underline text-lg font-bold">Profile </a> //
                            <a href="userShow.php" class="text-blue-600 hover:underline text-lg font-bold">Users</a> //
                            <a href="empReg.php" class="text-blue-600 hover:underline text-lg font-bold">Employee</a>
                        </section>
                    <?php endif; ?>
                </div>
            
                <!-- Change Password Section -->
                <div class="change-password bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Change Password</h3>
                    
                    <!-- Display Error or Success Message -->
                    <?php if (isset($errorMessage)): ?>
                        <div class="text-red-600 mb-4"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <?php if (isset($successMessage)): ?>
                        <div class="text-green-600 mb-4"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-4">
                        <div class="mb-4">
                            <label for="currentPassword" class="block text-gray-600 font-medium">Current Password:</label>
                            <div class="relative">
                                <input type="password" id="currentPassword" name="currentPassword" required 
                                    class="input input-bordered w-full mt-2" placeholder="Enter your current password">
                                <!-- Eye Icon to Toggle Visibility -->
                                <button type="button" id="toggleCurrentPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="newPassword" class="block text-gray-600 font-medium">New Password:</label>
                            <div class="relative">
                                <input type="password" id="newPassword" name="newPassword" required 
                                    class="input input-bordered w-full mt-2" placeholder="Enter your new password">
                                <!-- Eye Icon to Toggle Visibility -->
                                <button type="button" id="toggleNewPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordStrengthMeter" class="text-gray-600 mb-4 mt-2">
                                <p>Password Strength: <span id="strengthLevel" class="font-semibold text-red-600">Weak</span></p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirmNewPassword" class="block text-gray-600 font-medium">Confirm New Password:</label>
                            <div class="relative">
                                <input type="password" id="confirmNewPassword" name="confirmNewPassword" required 
                                    class="input input-bordered w-full mt-2" placeholder="Confirm your new password">
                                <!-- Eye Icon to Toggle Visibility -->
                                <button type="button" id="toggleConfirmNewPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full" id="submitBtn" disabled>Save Changes</button>
                    </form>
                </div>
            </div>  
         </section>
    </main> 

    <script>
// Get the DOM elements for password inputs and toggle buttons
const currentPasswordInput = document.getElementById("currentPassword");
const newPasswordInput = document.getElementById("newPassword");
const confirmPasswordInput = document.getElementById("confirmNewPassword");

const toggleCurrentPassword = document.getElementById("toggleCurrentPassword");
const toggleNewPassword = document.getElementById("toggleNewPassword");
const toggleConfirmNewPassword = document.getElementById("toggleConfirmNewPassword");

const submitButton = document.getElementById("submitBtn");

// Function to toggle password visibility
function togglePasswordVisibility(inputField, toggleButton) {
    if (inputField.type === "password") {
        inputField.type = "text";  // Show the password
        toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';  // Change icon to "hide"
    } else {
        inputField.type = "password";  // Hide the password
        toggleButton.innerHTML = '<i class="fas fa-eye"></i>';  // Change icon to "show"
    }
}

// Add event listeners to toggle buttons for each password field
toggleCurrentPassword.addEventListener("click", function() {
    togglePasswordVisibility(currentPasswordInput, toggleCurrentPassword);
});
toggleNewPassword.addEventListener("click", function() {
    togglePasswordVisibility(newPasswordInput, toggleNewPassword);
});
toggleConfirmNewPassword.addEventListener("click", function() {
    togglePasswordVisibility(confirmPasswordInput, toggleConfirmNewPassword);
});

// Function to check password strength
function checkPasswordStrength(password) {
    const hasUpperCase = /[A-Z]/.test(password); // Check for uppercase letter
    const hasLowerCase = /[a-z]/.test(password); // Check for lowercase letter
    const hasNumber = /\d/.test(password);       // Check for number
    const hasSpecialChar = /[^\w\s]/.test(password); // Check for special characters

    const typesCount = [hasUpperCase, hasLowerCase, hasNumber, hasSpecialChar].filter(Boolean).length;

    if (typesCount === 1 || typesCount === 2) {
        strengthLevel.textContent = "Weak";
        strengthLevel.classList.remove("text-yellow-600", "text-green-600");
        strengthLevel.classList.add("text-red-600");
        submitButton.disabled = true;  // Disable submit button
    } else if (typesCount === 3) {
        strengthLevel.textContent = "Medium";
        strengthLevel.classList.remove("text-red-600", "text-green-600");
        strengthLevel.classList.add("text-yellow-600");
        submitButton.disabled = false;  // Enable submit button
    } else if (typesCount === 4) {
        strengthLevel.textContent = "Strong";
        strengthLevel.classList.remove("text-red-600", "text-yellow-600");
        strengthLevel.classList.add("text-green-600");
        submitButton.disabled = false;  // Enable submit button
    }
}

// Event listener for typing password
newPasswordInput.addEventListener("input", function() {
    checkPasswordStrength(newPasswordInput.value);
});

// Confirm password check
confirmPasswordInput.addEventListener("input", function() {
    if (confirmPasswordInput.value !== newPasswordInput.value) {
        confirmPasswordInput.setCustomValidity("Passwords do not match!");
    } else {
        confirmPasswordInput.setCustomValidity("");
    }
});
    </script>
</body>
</html>
