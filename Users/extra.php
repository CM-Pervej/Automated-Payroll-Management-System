<?php
session_start();
include('db_conn.php'); // Database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT user.name, user.userrole_id FROM user WHERE user.id = ?";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Determine the profile page based on userrole_id
$profile_page = "#"; // Default
if ($user['userrole_id'] == 1) {
    $profile_page = "admin.php";  // Redirect Admins
} elseif ($user['userrole_id'] == 2) {
    $profile_page = "hr.php";  // Redirect HR
} elseif ($user['userrole_id'] == 3 || $user['userrole_id'] == 4) {
    $profile_page = "user.php";  // Redirect Normal Users
}

?>

<header class="p-4 text-white bg-blue-50">
    <div class="container mx-auto flex items-center justify-between">
        <!-- Search Bar -->
        <div class="relative w-80">
            <input type="text" name="searchKeyword" id="searchKeyword" placeholder="Search Employee"
                   class="w-full px-4 py-2 text-gray-700 bg-white border border-blue-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   autocomplete="off">
            <div id="suggestionBox" class="suggestion-box hidden text-black"></div> 
        </div>

        <!-- User Profile Dropdown -->
        <div class="relative">
            <button id="profileMenuButton" class="flex items-center space-x-2 focus:outline-none">
                <img src="profile.png" alt="User" class="w-8 h-8 rounded-full">
                <span class="text-black"><?php echo htmlspecialchars($user['name']); ?></span>
            </button>
            <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 text-gray-800 z-20">
                <a href="<?php echo $profile_page; ?>" class="block px-4 py-2 hover:bg-gray-200">Profile</a>
                <a href="#" class="block px-4 py-2 hover:bg-gray-200">Settings</a>
                <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-200">Logout</a>
            </div>
        </div>
    </div>
</header>

<script>
    // Profile dropdown toggle
    document.getElementById('profileMenuButton').onclick = function() {
        document.getElementById('profileMenu').classList.toggle('hidden');
    };
</script>
