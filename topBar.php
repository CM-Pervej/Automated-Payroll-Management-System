<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('db_conn.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT user.name, user.userrole_id, user.employee_id  FROM user WHERE user.id = ?";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Determine profile page
// $settings = "#";
// if ($user['userrole_id'] == 1) {
//     $settings = "users/settings.php";
// } elseif ($user['userrole_id'] == 2) {
//     $settings = "users/settings.php";
// } elseif ($user['userrole_id'] == 3 || $user['userrole_id'] == 4) {
//     $settings = "users/user_settings.php";
// }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .suggestion-box {
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
            z-index: 1000;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
        }
        .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
        }
        .suggestion-item:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <header class="p-2 text-white bg-blue-50">
        <div class="container mx-auto flex items-center justify-between">
            <!-- Search Bar with Clear Icon -->
            <div class="relative w-80">
                <input type="text" name="searchKeyword" id="searchKeyword" placeholder="Search Employee"
                       class="w-full px-4 py-2 text-gray-700 bg-white border border-blue-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       autocomplete="off">
                <div id="suggestionBox" class="suggestion-box hidden text-black"></div> 
                <!-- Clear Icon -->
                <button id="clearSearch" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden"> &times; </button>
            </div>

            <!-- User Profile Dropdown -->
            <div class="relative">
                <button id="profileMenuButton" class="flex items-center space-x-2 focus:outline-none py-3 px-6 rounded-full shadow-inner bg-blue-300">
                    <span class="text-black font-semibold"><?php echo htmlspecialchars($user['name']); ?></span>
                </button>
                <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 text-gray-800 z-20">
                    <a href="/payroll/profile.php?employee_id=<?php echo $user['employee_id']; ?>" class="block px-4 py-2 hover:bg-gray-200">Profiles</a>
                    <a href="/payroll/users/setting.php" class="block px-4 py-2 hover:bg-gray-200">Settings</a>
                    <a href="/payroll/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-200">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Profile dropdown toggle
        document.getElementById('profileMenuButton').onclick = function() {
            const profileMenu = document.getElementById('profileMenu');
            profileMenu.classList.toggle('hidden');
        };

        // Search Suggestions and Clear Icon Functionality
        const searchInput = document.getElementById("searchKeyword");
        const clearButton = document.getElementById("clearSearch");
        
        // Show/Hide Clear Icon based on input
        searchInput.addEventListener("input", function() {
            const query = this.value;
            clearButton.classList.toggle("hidden", query.length === 0);
            
            if (query.length > 0) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", "/payroll/get_suggestions.php?query=" + encodeURIComponent(query), true);
                xhr.onload = function() {
                    if (this.status === 200) {
                        const suggestions = JSON.parse(this.responseText);
                        showSuggestions(suggestions);
                    }
                };
                xhr.send();
            } else {
                clearSuggestions();
            }
        });

        // Show suggestions
        function showSuggestions(suggestions) {
            const suggestionBox = document.getElementById('suggestionBox');
            suggestionBox.innerHTML = '';
            suggestions.forEach(suggestion => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.textContent = suggestion.value;
                item.onclick = function() {
                    window.location.href = "search.php?keyword=" + encodeURIComponent(suggestion.value);
                };
                suggestionBox.appendChild(item);
            });
            suggestionBox.classList.remove('hidden');
        }

        // Clear search and suggestions
        function clearSuggestions() {
            searchInput.value = '';
            const suggestionBox = document.getElementById('suggestionBox');
            suggestionBox.innerHTML = '';
            suggestionBox.classList.add('hidden');
            clearButton.classList.add("hidden");
        }

        // Clear button click handler
        clearButton.onclick = clearSuggestions;
    </script>
</body>
</html>
