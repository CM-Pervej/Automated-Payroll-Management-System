<?php 
include '../view.php'; 
include '../db_conn.php';

// Fetch payroll records for the current month and year
$payrollData = [];
$payroll_stmt = $conn->prepare("SELECT * FROM payroll");
$payroll_stmt->execute();
$payroll_result = $payroll_stmt->get_result();

// Initialize totals
$totalEmployees = 0;
$totalAllowancesSum = 0;
$totalDeductionsSum = 0;
$totalGrossSum = 0;
$totalNetPaySum = 0;

while ($row = $payroll_result->fetch_assoc()) {
    // Calculate the sum of allowances and deductions
    $totalAllowance = $row['dearnessAllw'] + $row['houseAllw'] + $row['medicalAllw'] + $row['educationAllw'] +
                      $row['festivalAllw'] + $row['researchAllw'] + $row['newBdYrAllw'] + $row['recreationAllw'] +
                      $row['otherAllw'] + $row['chargeAllw'] + $row['telephoneAllwance'];
    
    $totalDeduction = $row['gpf'] + $row['gpfInstallment'] + $row['houseDed'] + $row['benevolentFund'] +
                      $row['insurance'] + $row['electricity'] + $row['hrdExtra'] + $row['clubSubscription'] +
                      $row['assoSubscription'] + $row['transportBill'] + $row['telephoneBill'] + $row['pensionFund'] +
                      $row['fishBill'] + $row['incomeTax'] + $row['donation'] + $row['guestHouseRent'] +
                      $row['houseLoanInstallment_1'] + $row['houseLoanInstallment_2'] + $row['houseLoanInstallment_3'] +
                      $row['salaryAdjustment'] + $row['revenue'] + $row['otherDed'];

    // Calculate gross and net pay
    $gross = $totalAllowance + $row['basic'];
    $netPay = $gross - $totalDeduction;

    // Update totals
    $totalEmployees++;
    $totalAllowancesSum += $totalAllowance;
    $totalDeductionsSum += $totalDeduction;
    $totalGrossSum += $gross;
    $totalNetPaySum += $netPay;

    // Add calculated values to the payroll data
    $row['totalAllowance'] = $totalAllowance;
    $row['totalDeduction'] = $totalDeduction;
    $row['gross'] = $gross;
    $row['netPay'] = $netPay;
    $payrollData[] = $row;
}
$payroll_stmt->close();

// Fetch distinct values for dropdown filters
$grades = [];
$designations = [];
$departments = [];
$months = [];
$years = [];

// Fetch distinct grades
$grade_query = "SELECT DISTINCT grade FROM payroll ORDER BY grade";
$result = $conn->query($grade_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row['grade'];
    }
}

// Fetch distinct designations
$designation_query = "SELECT DISTINCT designation FROM payroll ORDER BY grade";
$result = $conn->query($designation_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $designations[] = $row['designation'];
    }
}

// Fetch distinct departments
$department_query = "SELECT DISTINCT departments FROM payroll ORDER BY departments";
$result = $conn->query($department_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['departments'];
    }
}

// Fetch distinct months
$month_query = "SELECT DISTINCT month FROM payroll ORDER BY month DESC";
$result = $conn->query($month_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $months[] = $row['month'];
    }
}

// Fetch distinct years
$year_query = "SELECT DISTINCT year FROM payroll ORDER BY year";
$result = $conn->query($year_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $years[] = $row['year'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="sideBar.css">
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include '../sideBar.php'; ?>
    </header>

    <!-- Main Content -->
    <div class="flex flex-col flex-grow ml-64">
        <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
            <?php include '../topBar.php'; ?>
        </aside>
        <main class="flex-grow p-4 mt-16 bg-white shadow-lg overflow-auto">
            <!-- Summary Section -->
            <div class="w-full pr-72">
                <h3 class="text-2xl font-semibold text-gray-800">Payroll Summary</h3>
                <div class="flex justify-between gap-4 mt-4 w-full">
                    <!-- Summary Cards Here -->
                </div>
            </div>

            <!-- Filters -->
            <div class="flex gap-4 my-6">
                <select id="gradeFilter" class="p-3 border rounded-md">
                    <option value="">Select Grade</option>
                    <?php foreach ($grades as $grade): ?>
                        <option value="<?php echo htmlspecialchars($grade); ?>"><?php echo htmlspecialchars($grade); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="designationFilter" class="p-3 border rounded-md">
                    <option value="">Select Designation</option>
                    <?php foreach ($designations as $designation): ?>
                        <option value="<?php echo htmlspecialchars(strtolower($designation)); ?>"><?php echo htmlspecialchars($designation); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="departmentFilter" class="p-3 border rounded-md">
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo htmlspecialchars($department); ?>"><?php echo htmlspecialchars($department); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="monthFilter" class="p-3 border rounded-md">
                    <option value="">Select Month</option>
                    <?php foreach ($months as $month): ?>
                        <option value="<?php echo htmlspecialchars($month); ?>"><?php echo htmlspecialchars($month); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="yearFilter" class="p-3 border rounded-md">
                    <option value="">Select Year</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Payroll Table -->
            <section class="container mx-auto">
                <div class="overflow-auto w-fit mx-auto pr-72 mr-72">
                    <table class="table min-w-min border-collapse border border-gray-200 rounded-lg shadow-md bg-white">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th>Employee No</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Grade</th>
                                <th>Increment</th>
                                <th>Basic Salary</th>
                                <th>+ Allowance</th>
                                <th>GrossPay</th>
                                <th>+ Deduction</th>
                                <th>Net Pay</th>
                                <th class="hidden">Month</th>
                                <th class="hidden">Year</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTable" style="display: none;">
                            <?php foreach ($payrollData as $payroll): ?>
                            <tr>
                                <td><?php echo $payroll['employeeNo']; ?></td>
                                <td class="whitespace-nowrap"><?php echo $payroll['name']; ?></td>
                                <td class="designation whitespace-nowrap"><?php echo $payroll['designation']; ?></td>
                                <td class="department whitespace-nowrap"><?php echo $payroll['departments']; ?></td>
                                <td class="grade"><?php echo $payroll['grade']; ?></td>
                                <td><?php echo $payroll['increment']; ?></td>
                                <td><?php echo $payroll['basic']; ?></td>
                                <td class="allowance"><?php echo $payroll['totalAllowance']; ?></td>
                                <td class="gross"><?php echo $payroll['gross']; ?></td>
                                <td class="deduction"><?php echo $payroll['totalDeduction']; ?></td>
                                <td class="net"><?php echo $payroll['netPay']; ?></td>
                                <td class="month hidden"><?php echo $payroll['month']; ?></td>
                                <td class="year hidden"><?php echo $payroll['year']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gradeFilter = document.getElementById('gradeFilter');
            const designationFilter = document.getElementById('designationFilter');
            const departmentFilter = document.getElementById('departmentFilter');
            const monthFilter = document.getElementById('monthFilter');
            const yearFilter = document.getElementById('yearFilter');
            const employeeTable = document.getElementById('employeeTable');

            const checkFiltersSelected = () => {
                // Check if all filters have selected values
                const allFiltersSelected = gradeFilter.value && 
                                           designationFilter.value && 
                                           departmentFilter.value && 
                                           monthFilter.value && 
                                           yearFilter.value;

                // Show table if all filters are selected
                if (allFiltersSelected) {
                    employeeTable.style.display = ''; // Show the table
                } else {
                    employeeTable.style.display = 'none'; // Hide the table
                }
            };

            // Trigger the table visibility when any filter is changed
            gradeFilter.addEventListener('change', checkFiltersSelected);
            designationFilter.addEventListener('change', checkFiltersSelected);
            departmentFilter.addEventListener('change', checkFiltersSelected);
            monthFilter.addEventListener('change', checkFiltersSelected);
            yearFilter.addEventListener('change', checkFiltersSelected);
        });
    </script>
</body>
</html>
