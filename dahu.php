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

    // Get the user role
    $userrole_id = $_SESSION['userrole_id'];

    // Fetch employee statistics based on empStatus and approve
    $active_employee_query = "SELECT COUNT(*) AS total_active FROM employee WHERE empStatus = 1 AND approve = 1";
    $inactive_employee_query = "SELECT COUNT(*) AS total_inactive FROM employee WHERE empStatus = 2 AND approve = 1";
    $pending_employee_query = "SELECT COUNT(*) AS total_pending FROM employee WHERE approve = 0";

    // Fetch user statistics based on status
    $active_user_query = "SELECT COUNT(*) AS total_active_users FROM user WHERE status = 1";
    $inactive_user_query = "SELECT COUNT(*) AS total_inactive_users FROM user WHERE status = 0";
    $pending_user_query = "SELECT COUNT(*) AS total_pending_users FROM user WHERE status = 2";

    // Fetch the total number of departments
    $department_count_query = "SELECT COUNT(*) AS total_departments FROM departments";
    
    // Execute queries with error handling
    $active_employee_result = mysqli_query($conn, $active_employee_query);
    if (!$active_employee_result) {
        die("Error executing active employee query: " . mysqli_error($conn));
    }

    $inactive_employee_result = mysqli_query($conn, $inactive_employee_query);
    if (!$inactive_employee_result) {
        die("Error executing inactive employee query: " . mysqli_error($conn));
    }

    $pending_employee_result = mysqli_query($conn, $pending_employee_query);
    if (!$pending_employee_result) {
        die("Error executing pending employee query: " . mysqli_error($conn));
    }

    $active_user_result = mysqli_query($conn, $active_user_query);
    if (!$active_user_result) {
        die("Error executing active user query: " . mysqli_error($conn));
    }

    $inactive_user_result = mysqli_query($conn, $inactive_user_query);
    if (!$inactive_user_result) {
        die("Error executing inactive user query: " . mysqli_error($conn));
    }

    $pending_user_result = mysqli_query($conn, $pending_user_query);
    if (!$pending_user_result) {
        die("Error executing pending user query: " . mysqli_error($conn));
    }

    // Get data from queries
    $active_employee_count = mysqli_fetch_assoc($active_employee_result)['total_active'];
    $inactive_employee_count = mysqli_fetch_assoc($inactive_employee_result)['total_inactive'];
    $pending_employee_count = mysqli_fetch_assoc($pending_employee_result)['total_pending'];

    $active_user_count = mysqli_fetch_assoc($active_user_result)['total_active_users'];
    $inactive_user_count = mysqli_fetch_assoc($inactive_user_result)['total_inactive_users'];
    $pending_user_count = mysqli_fetch_assoc($pending_user_result)['total_pending_users'];

    // Fetch department count
    $department_count_result = mysqli_query($conn, $department_count_query);
    if (!$department_count_result) {
        die("Error executing department count query: " . mysqli_error($conn));
    }
    $department_count = mysqli_fetch_assoc($department_count_result)['total_departments'];

    // Fetch the last inserted month and year from the payroll table
    $last_inserted_query = "SELECT `month`, `year` FROM payroll ORDER BY `year` DESC, `month` DESC LIMIT 1";
    $last_inserted_result = mysqli_query($conn, $last_inserted_query);

    if ($last_inserted_result) {
        // Get the latest month and year
        $last_inserted_data = mysqli_fetch_assoc($last_inserted_result);
        $passed_month = $last_inserted_data['month'];
        $passed_year = $last_inserted_data['year'];
    } else {
        // If no payroll records exist, default to current month and year
        $passed_month = date('m');
        $passed_year = date('Y');
    }

    // Prepare the SQL statement to fetch payroll data for the last inserted month and year
    $stmt = $conn->prepare("SELECT * FROM payroll WHERE `month` = ? AND `year` = ?");
    $stmt->bind_param("ii", $passed_month, $passed_year);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $payrolls = [];
            $totalBasic = 0;
            $totalAllowances = 0;
            $totalDeductions = 0;
            $totalSalary = 0;

            while ($row = $result->fetch_assoc()) {
                // Calculate individual allowances
                $totalAllowance = array_sum([
                    $row['chargeAllw'], $row['telephoneAllwance'],
                    $row['dearnessAllw'], $row['houseAllw'], $row['medicalAllw'],
                    $row['educationAllw'], $row['festivalAllw'], $row['researchAllw'],
                    $row['newBdYrAllw'], $row['recreationAllw'], $row['otherAllw']
                ]);
            
                // Calculate individual deductions
                $totalDeduction = array_sum([
                    $row['gpf'], $row['gpfInstallment'], $row['houseDed'], $row['benevolentFund'],
                    $row['insurance'], $row['electricity'], $row['hrdExtra'],
                    $row['clubSubscription'], $row['assoSubscription'], $row['transportBill'],
                    $row['telephoneBill'], $row['pensionFund'], $row['fishBill'],
                    $row['incomeTax'], $row['donation'], $row['guestHouseRent'],
                    $row['houseLoanInstallment_1'], $row['houseLoanInstallment_2'],
                    $row['houseLoanInstallment_3'], $row['salaryAdjustment'], $row['revenue'],
                    $row['otherDed']
                ]);
            
                // Calculate net pay
                $basic = $row['basic'];
                $netPay = $basic + $totalAllowance - $totalDeduction;
            
                // Store individual row data
                $row['totalAllowance'] = $totalAllowance; // Storing allowance for current employee
                $row['totalDeduction'] = $totalDeduction; // Storing deduction for current employee
                $row['netPay'] = $netPay; // Storing net pay for current employee
            
                // Update totals for reporting purposes
                $totalBasic += $basic;
                $totalAllowances += $totalAllowance;
                $totalDeductions += $totalDeduction;
                $totalSalary += $netPay;
                            
                $payrolls[] = $row;
            }
        } else {
            $error_message = "No payroll data found for the current month and year.";
        }
    } else {
        $error_message = "Failed to execute the query. Please try again.";
    }

    $stmt->close();
    $conn->close();
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
            <!-- Employees Summary Section -->
            <div class="bg-white">
                <h2 class="text-2xl font-bold mb-4">Overview of the System</h2>
                <div class="flex gap-6">
                    <!-- Number of Employees Card -->
                    <div class="bg-green-100 p-4 rounded-lg shadow-md flex items-center space-x-4">
                        <i class="fas fa-users text-3xl text-green-600"></i>
                        <div>
                            <p class="text-lg font-semibold">Number of Employees</p>
                            <div class="flex gap-2">
                                <p class="text-blue-700 font-semibold"><?php echo $active_employee_count; ?> Active </p>/ 
                                <p class="text-violet-600 font-semibold"><?php echo $inactive_employee_count; ?> Inactive </p>/ 
                                <p class="text-gray-600 font-semibold"><?php echo $pending_employee_count; ?> Pending</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Number of Users Card -->
                    <div class="bg-yellow-100 p-4 rounded-lg shadow-md flex items-center space-x-4">
                        <i class="fas fa-user-check text-3xl text-yellow-600"></i>
                        <div>
                            <p class="text-lg font-semibold">Number of Users</p>
                            <div class="flex gap-2">
                                <p class="text-blue-700 font-semibold"><?php echo $active_user_count; ?> Active</p>/ 
                                <p class="text-violet-600 font-semibold"><?php echo $inactive_user_count; ?> Inactive</p>/ 
                                <p class="text-gray-600 font-semibold"><?php echo $pending_user_count; ?> Pending</p>
                            </div>
                        </div>
                    </div>

                    <!-- Number of Departments Card -->
                    <div class="bg-purple-100 p-4 rounded-lg shadow-md flex items-center space-x-4">
                        <i class="fas fa-building text-3xl text-purple-600"></i>
                        <div>
                            <p class="text-lg font-semibold">Number of Departments</p>
                            <p class="text-gray-600 font-semibold"><?php echo $department_count; ?> Departments</p>
                        </div>
                    </div>
                </div>
            </div>

        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php else: ?>
            <section class="pt-16 flex flex-col gap-10">
                <section class="flex flex-col justify-center items-stretch gap-10 w-full">
                    <!-- Second Table Section -->
                    <div class="flex-1 w-full">
                        <div class="flex items-center gap-5 border-b border-gray-300 mb-5 pb-3 text-xl">
                            <p class="font-semibold">Financial Summary</p>/
                            <p class="text-gray-600">Payroll Period: <span class="font-semibold"><?php echo date('F, Y', strtotime("{$passed_year}-{$passed_month}-01")); ?></span></p>/
                            <p class="text-gray-600">Date: <span class="font-semibold"><?php echo date('F d, Y'); ?></span></p>/
                            <p>Current Time: <span id="current-time" class="font-semibold"></span></p>
                        </div>
                        <table class="border border-collapse w-full table-auto table-fixed">
                            <tr>
                                <th class="text-xl text-left p-3 border border-gray-400">Total Basic</th>
                                <th class="text-xl text-left p-3 border border-gray-400">Total Allowances</th>
                                <th class="text-xl text-left p-3 border border-gray-400">Total Deductions</th>
                                <th class="text-xl text-left p-3 border border-gray-400">Total Salary</th>
                            </tr>
                            <tr>
                                <td class="text-left p-3 border border-gray-400"><?php echo number_format($totalBasic, 2); ?></td>
                                <td class="text-left p-3 border border-gray-400"><?php echo number_format($totalAllowances, 2); ?></td>
                                <td class="text-left p-3 border border-gray-400"><?php echo number_format($totalDeductions, 2); ?></td>
                                <td class="text-left p-3 border border-gray-400"><?php echo number_format($totalSalary, 2); ?></td>
                            </tr>
                        </table>
                        <p class="mt-5">In Words: <span class="net-pay-words text-lg font-semibold"></span></p> 
                    </div>
                </section>
            </section>
        <?php endif; ?>
    </main>

    <script>
        /**
         * Convert number to words for amounts in Taka
         */
        function convertNumberToWords(number) {
            const dictionary = {
                0: 'Zero', 1: 'One', 2: 'Two', 3: 'Three', 4: 'Four', 5: 'Five',
                6: 'Six', 7: 'Seven', 8: 'Eight', 9: 'Nine', 10: 'Ten',
                11: 'Eleven', 12: 'Twelve', 13: 'Thirteen', 14: 'Fourteen', 15: 'Fifteen',
                16: 'Sixteen', 17: 'Seventeen', 18: 'Eighteen', 19: 'Nineteen',
                20: 'Twenty', 30: 'Thirty', 40: 'Forty', 50: 'Fifty', 60: 'Sixty',
                70: 'Seventy', 80: 'Eighty', 90: 'Ninety', 100: 'Hundred',
                1000: 'Thousand', 100000: 'Lac', 10000000: 'Crore'
            };

            if (typeof number !== 'number' || isNaN(number)) return 'Invalid number';

            let words = 'Taka ';
            const conjunction = ' and ';
            const decimal = ' Paisa ';
            const integerPart = Math.floor(number);
            const fractionalPart = Math.round((number % 1) * 100);

            const convert = (num) => {
                if (num < 21) return dictionary[num];
                if (num < 100) {
                    const tens = Math.floor(num / 10) * 10;
                    const units = num % 10;
                    return dictionary[tens] + (units ? ` ${dictionary[units]}` : '');
                }
                if (num < 1000) {
                    const hundreds = Math.floor(num / 100);
                    const remainder = num % 100;
                    return dictionary[hundreds] + ' ' + dictionary[100] + (remainder ? ` ${convert(remainder)}` : '');
                }
                for (let base of [10000000, 100000, 1000, 100]) {
                    if (num >= base) {
                        const quotient = Math.floor(num / base);
                        const remainder = num % base;
                        return convert(quotient) + ' ' + dictionary[base] + (remainder ? ` ${convert(remainder)}` : '');
                    }
                }
            };

            words += convert(integerPart);
            if (fractionalPart > 0) {
                words += conjunction + fractionalPart + decimal;
            } else {
                words += ' and Paisa Zero ';
            }

            return words + 'Only';
        }

        document.addEventListener("DOMContentLoaded", function() {
            var totalSalary = <?php echo json_encode($totalSalary ?? 0); ?>;
            document.querySelector('.net-pay-words').textContent = convertNumberToWords(totalSalary);
        });
    </script>
        <script>
        // Function to format time
        function formatTime(date) {
            let hours = date.getHours();
            let minutes = date.getMinutes();
            let seconds = date.getSeconds();
            let period = hours >= 12 ? 'PM' : 'AM';

            // Convert 24-hour time to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12; // Hour '0' should be '12'
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            return hours + ':' + minutes + ':' + seconds + ' ' + period;
        }

        // Function to update time every second
        function updateTime() {
            const now = new Date();
            const timeString = formatTime(now);
            document.getElementById('current-time').innerText = timeString;
        }

        // Update time every second
        setInterval(updateTime, 1000);

        // Initial call to display time immediately when page loads
        window.onload = updateTime;
    </script>
    </div>
</body>
</html>
