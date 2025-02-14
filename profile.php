<?php
include 'view.php'; 
include 'db_conn.php';

// Initialize variables
$employee_id = $_GET['employee_id'] ?? null; // Get employee_id from URL
$employee = null;
$allowances = [];
$deductions = [];
$empAddSalary = null;
$additionalDesignations = [];
$totalDeduction = 0;
$totalAllowance = 0;
$grossPay = 0;
$netPay = 0;

// Fetch employee details
if ($employee_id) {
    // Fetch employee basic details with primary designation
    $stmt = $conn->prepare("SELECT e.id, e.employeeNo, e.name, e.date_of_birth, e.gender, e.contactNo, e.email, e.empStatus, e.image, e.designation_id, e.department_id, e.basic, e.no_of_increment, e.account_number, e.grade_id, e.joining_date, e.e_tin, d.designation AS primary_designation, dept.department_name, g.grade, g.scale 
                            FROM employee e 
                            JOIN designations d ON e.designation_id = d.id 
                            JOIN departments dept ON e.department_id = dept.id 
                            JOIN grade g ON e.grade_id = g.id 
                            WHERE e.id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();

    // Fetch allowances for the employee
    $allowance_stmt = $conn->prepare("SELECT ac.allwTotal, allw.allwName, ac.allowanceList_id 
                                                FROM allwConfirm ac
                                                JOIN allowanceList allw ON ac.allowanceList_id = allw.id
                                                WHERE ac.employee_id = ?");
    $allowance_stmt->bind_param("i", $employee_id);
    $allowance_stmt->execute();
    $allowance_result = $allowance_stmt->get_result();
    while ($row = $allowance_result->fetch_assoc()) {
        $allowances[] = $row;
        $totalAllowance += $row['allwTotal']; // Sum of allowances
    }
    $allowance_stmt->close();

    // Fetch deductions for the employee
    $deduction_stmt = $conn->prepare("SELECT dc.dedTotal, ded.dedName, dc.deductionList_id 
                                    FROM dedConfirm dc
                                    JOIN deductionList ded ON dc.deductionList_id = ded.id
                                    WHERE dc.employee_id = ?");
    $deduction_stmt->bind_param("i", $employee_id);
    $deduction_stmt->execute();
    $deduction_result = $deduction_stmt->get_result();
    while ($row = $deduction_result->fetch_assoc()) {
        $deductions[] = $row;
        $totalDeduction += $row['dedTotal']; // Sum of deductions
    }
    $deduction_stmt->close();

    // Fetch empAddSalary data for the employee
    $empAddSalary_stmt = $conn->prepare("SELECT chargeAllw, telephoneAllwance FROM empAddSalary WHERE employee_id = ?");
    $empAddSalary_stmt->bind_param("i", $employee_id);
    $empAddSalary_stmt->execute();
    $empAddSalary_result = $empAddSalary_stmt->get_result();
    $empAddSalary = $empAddSalary_result->fetch_assoc();
    $empAddSalary_stmt->close();

    // Fetch additional designations
    $addDesignation_stmt = $conn->prepare("SELECT ad.designation AS additional_designation
                                           FROM empAddDesignation ead
                                           JOIN addDuty ad ON ead.addDuty_id = ad.id
                                           WHERE ead.empAddSalary_id IN (SELECT id FROM empAddSalary WHERE employee_id = ?)");
    $addDesignation_stmt->bind_param("i", $employee_id);
    $addDesignation_stmt->execute();
    $addDesignation_result = $addDesignation_stmt->get_result();
    while ($row = $addDesignation_result->fetch_assoc()) {
        $additionalDesignations[] = $row['additional_designation'];
    }
    $addDesignation_stmt->close();

    // Calculate gross pay and net pay
    $grossPay = $employee['basic'] + $totalAllowance + ($empAddSalary['chargeAllw'] ?? 0) + ($empAddSalary['telephoneAllwance'] ?? 0);
    $netPay = $grossPay - $totalDeduction;

    // Check if the confirm button is clicked
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Step 1: Delete existing records for the employee in the checkEmployee table
        $delete_stmt = $conn->prepare("DELETE FROM checkEmployee WHERE employee_id = ?");
        $delete_stmt->bind_param("i", $employee_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        // Step 2: Insert the new values into the checkEmployee table
        $insert_stmt = $conn->prepare("INSERT INTO checkEmployee (employee_id, employeeNo, name, empStatus, grade, designation, department_name)
                                       VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("issssss", $employee_id, $employee['employeeNo'], $employee['name'], $employee['empStatus'], $employee['grade'], $employee['primary_designation'], $employee['department_name']);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
}

$conn->close();

// Format the dates
$dateOfBirth = new DateTime($employee['date_of_birth']);
$formattedDateOfBirth = $dateOfBirth->format('d-M-Y');

$joiningDate = new DateTime($employee['joining_date']);
$formattedJoiningDate = $joiningDate->format('d-M-Y');
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="sideBar.css">
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
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Header -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include 'sideBar.php'; ?>
    </header>
    <!-- Main Content Area -->
    <div class="flex flex-col flex-grow ml-64">
        <!-- Top Bar -->
        <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
            <div class="container mx-auto flex items-center justify-between py-4 px-6">
                <!-- Search Bar with Clear Icon -->
                <div class="relative w-80">
                    <input type="text" name="searchKeyword" id="searchKeyword" placeholder="Search Employee"
                        class="w-full px-4 py-2 text-gray-700 bg-white border border-blue-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        autocomplete="off">
                    <div id="suggestionBox" class="suggestion-box hidden text-black"></div>
                    <!-- Clear Icon -->
                    <button id="clearSearch" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden"
                        aria-label="Clear search">&times;</button>
                </div>

                <!-- Navigation Links -->
                <div class="flex justify-center gap-4">
                    <a href="select/addAllowance.php?employee_id=<?php echo $employee_id; ?>" class="btn btn-primary" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 4) ? 'disabled' : ''; ?> title="Only User1 can access this page">Allowance</a>
                    <a href="select/addDeduction.php?employee_id=<?php echo $employee_id; ?>" class="btn btn-primary" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 4) ? 'disabled' : ''; ?> title="Only User1 can access this page">Deduction</a>
                    <a href="select/empCharge.php?employee_id=<?php echo $employee_id; ?>" class="btn btn-primary" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 4) ? 'disabled' : ''; ?> title="Only User1 can access this page">Additional Duty</a>
                    <a href="select/updateProfile.php?employee_id=<?php echo $employee_id; ?>" class="btn btn-primary" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 4) ? 'disabled' : ''; ?> title="Only User1 can access this page">Update</a>
                    <form method="POST">
                        <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>" />
                        <button type="submit" class="btn btn-success" <?php echo ($userrole_id != 1 && $userrole_id != 2 && $userrole_id != 4) ? 'disabled' : ''; ?> title="Only User1 can access this page">Confirm</button>
                    </form>
                </div>

                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button id="profileMenuButton" class="flex items-center space-x-2 focus:outline-none">
                        <img src="profile.png" alt="User" class="w-8 h-8 rounded-full">
                        <span class="text-black">User</span>
                    </button>
                    <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 text-gray-800 z-20">
                        <a href="#" class="block px-4 py-2 hover:bg-gray-200">Profile</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-200">Settings</a>
                        <a href="#" class="block px-4 py-2 text-red-600 hover:bg-gray-200">Logout</a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Content Section -->
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <section class="flex-grow bg-white rounded-lg shadow-md p-6 mb-10 border border-gray-200">
                <div class="flex items-center mb-8">
                    <!-- Employee Image -->
                    <div class="flex-shrink-0">
                        <?php if (!empty($employee['image'])): ?>
                            <?php 
                                $imagePath = 'uploads/' . basename($employee['image']);
                                if (file_exists($imagePath)): 
                            ?>
                                <img src="<?php echo $imagePath; ?>" alt="Profile image of <?php echo htmlspecialchars($employee['name']); ?>" class="w-36 h-36 object-cover rounded-lg border-4 border-gray-200 shadow-lg" />
                            <?php else: ?>
                                <p class="text-red-500">Image file does not exist at: <?php echo htmlspecialchars($imagePath); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-gray-400">No image available for this employee.</p>
                        <?php endif; ?>
                    </div>
                    <!-- Employee Information -->
                    <div class="ml-8">
                        <h1 class="text-4xl font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($employee['name']); ?></h1>
                        <p class="text-xl font-semibold text-gray-500 mb-3"><?php echo htmlspecialchars($employee['primary_designation']); ?> || <?php echo htmlspecialchars($employee['department_name']); ?></p>
                        <p class="text-gray-600 mb-1"><i class="fas fa-envelope text-gray-500 mr-2"></i> <a href="mailto:<?php echo htmlspecialchars($employee['email']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($employee['email']); ?></a></p>
                        <p class="text-gray-600"><i class="fas fa-phone-alt text-gray-500 mr-2"></i> <?php echo htmlspecialchars($employee['contactNo']); ?></p>
                    </div>

                    <!-- University Logo -->
                    <div class="ml-auto transform skew-x-12 py-2 shadow-lg flex items-center justify-center px-16 bg-gradient-to-r from-purple-500 to-blue-500 rounded-lg">
                        <figure class="transform -skew-x-12 w-24 h-24">
                            <img src="uploads/JUSTt.png" alt="University Logo" class="w-full h-full object-cover rounded-md shadow-md">
                        </figure>
                    </div>
                </div>

                <article class="flex gap-5 justify-between items-stretch rounded-lg w-full">
                    <!-- Personal Information -->
                    <aside class="w-1/2 p-4 rounded-lg shadow-sm relative bg-gray-100"  style="clip-path: polygon(90% 0%, 100% 50%, 90% 100%, 0% 100%, 0% 0%);">
                        <p class="text-2xl font-semibold border-b border-gray-400 pb-2 mb-2 mr-10">Personal Information</p>
                        <div>
                            <p><strong class="text-gray-500">Employee ID:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['employeeNo']); ?></span></p>
                            <p><strong class="text-gray-500">Gender:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['gender']); ?></span></p>
                            <p><strong class="text-gray-500">Date of Birth:</strong> <span class="font-semibold"><?php echo htmlspecialchars($formattedDateOfBirth); ?></span></p>
                            <p><strong class="text-gray-500">Status:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['empStatus']); ?></span></p>
                            <p><strong class="text-gray-500">Contact:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['contactNo']); ?></span></p>
                            <p><strong class="text-gray-500">Email:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['email']); ?></span></p>
                        </div>
                    </aside>

                    <!-- Job Details -->
                    <aside class="w-1/2 p-4 rounded-lg shadow-sm relative bg-gray-100"  style="clip-path: polygon(90% 0%, 100% 50%, 90% 100%, 0% 100%, 0% 0%);">
                        <p class="text-2xl font-semibold border-b border-gray-400 pb-2 mb-2 mr-10">Job Details</p>
                        <div>
                            <p class="text-base"><strong class="text-gray-500">Department:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['department_name']); ?></span></p>
                            <p class="text-base"><strong class="text-gray-500">Designation:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['primary_designation']); ?></span></p>
                            <p class="text-base"><strong class="text-gray-500">Grade:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['grade']); ?></span></p>
                            <p class="text-base"><strong class="text-gray-500">Scale:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['scale']); ?>.00</span></p>
                            <p class="text-base"><strong class="text-gray-500">Date of Joining:</strong> <span class="font-semibold"><?php echo htmlspecialchars($formattedJoiningDate); ?></span></p>
                            <p class="text-base"><strong class="text-gray-500">SB. A/C No:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['account_number']); ?></span></p>
                            <p class="text-base"><strong class="text-gray-500">E-TIN:</strong> <span class="font-semibold"><?php echo htmlspecialchars($employee['e_tin']); ?></span></p>
                        </div>
                    </aside>
                    <!-- <aside class="w-1/2 py-4 pr-4 pl-14 rounded-lg shadow-sm relative bg-gray-200"  style="clip-path: polygon(10% 0%, 100% 0%, 100% 100%, 10% 100%, 0% 50%);"> </aside> -->
                </article>

                <!-- Main Content -->
                <section class="mt-8">
                    <?php if ($employee): ?>
                        <!-- Allowances & Deductions -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-blue-50 p-4 rounded-lg shadow-md">
                                <h2 class="text-2xl font-semibold border-b border-gray-400 pb-2 mb-2">Allowances</h2>
                                <div>
                                    <p class="font-semibold text-gray-700">No of Increments: <span class="text-black"> <?php echo htmlspecialchars($employee['no_of_increment']); ?> </span></p>
                                    <p class="font-semibold text-gray-700">Basic Salary: <span class="text-black"> <?php echo htmlspecialchars($employee['basic']); ?></span></p>
                                    <?php if (count($allowances) > 0): ?>
                                        <?php foreach ($allowances as $allowance): ?>
                                            <p class="font-semibold text-gray-700"><?php echo htmlspecialchars($allowance['allwName']); ?>: <span class="text-black"><?php echo htmlspecialchars($allowance['allwTotal']);  ?></span></p>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No allowances found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-lg shadow-md">
                                <h2 class="text-2xl font-semibold border-b border-gray-400 pb-2 mb-2">Deductions</h2>
                                <div>
                                    <?php if (count($deductions) > 0): ?>
                                        <?php foreach ($deductions as $deduction): ?>
                                            <p class="font-semibold text-gray-700"><?php echo htmlspecialchars($deduction['dedName']); ?>: <span class="text-black"><?php echo htmlspecialchars($deduction['dedTotal']);  ?></span></p>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No deductions found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Salary & Designations -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                            <div class="bg-red-50 p-4 rounded-lg shadow-md">
                                <h2 class="text-2xl font-semibold border-b border-gray-400 pb-2 mb-2">Additional Designations</h2>
                                <?php if (count($additionalDesignations) > 0): ?>
                                    <ul class="list-disc pl-5">
                                        <?php foreach ($additionalDesignations as $designation): ?>
                                            <li class="font-semibold"><?php echo htmlspecialchars($designation); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>No additional designations found.</p>
                                <?php endif; ?>
                            </div>

                            <div class="bg-red-50 p-4 rounded-lg shadow-md">
                                <h2 class="text-2xl font-semibold border-b border-gray-400 pb-2 mb-2">Additional Salary</h2>
                                <?php if ($empAddSalary): ?>
                                    <p class="font-semibold">Charge Allowance: <?php echo htmlspecialchars($empAddSalary['chargeAllw']); ?></p>
                                    <p class="font-semibold">Telephone Allowance: <?php echo htmlspecialchars($empAddSalary['telephoneAllwance']); ?></p>
                                <?php else: ?>
                                    <p>No additional salary details found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-red-500">Employee not found.</p>
                    <?php endif; ?>
                </section>
                <section class="mt-8">
                    <?php if ($employee): ?>
                        <!-- Financial Information -->
                        <div class="flex gap-8 w-full mt-6">
                            <div class="bg-green-50 p-4 rounded-lg shadow-md w-full">
                                <h2 class="text-2xl font-semibold border-b border-gray-400 pb-2 mb-2">Financial Summary</h2>
                                <div>
                                    <p class="font-semibold">Total Deduction: <?php echo htmlspecialchars($totalDeduction); ?></p>
                                    <p class="font-semibold">Gross Pay: <?php echo htmlspecialchars($grossPay); ?></p>
                                    <p class="font-semibold">Net Pay: <?php echo htmlspecialchars($netPay); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-red-500">Employee not found.</p>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>

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
                xhr.open("GET", "get_suggestions.php?query=" + encodeURIComponent(query), true);
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
