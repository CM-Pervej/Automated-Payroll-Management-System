<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Include the database connection
include '../db_conn.php';

// Get the current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Fetch payroll records for the current month and year
$payrollData = [];
$payroll_stmt = $conn->prepare("SELECT * FROM payroll WHERE month = ? AND year = ?");
$payroll_stmt->bind_param("ss", $currentMonth, $currentYear);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../sideBar.css">
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
                    <div class="card bg-white shadow-md p-6 rounded-lg border border-gray-200 w-full">
                        <h4 class="text-lg font-medium text-gray-600">Total Employees</h4>
                        <p id="totalEmployees" class="text-2xl font-bold text-blue-600"><?php echo $totalEmployees; ?></p>
                    </div>
                    <div class="card bg-white shadow-md p-6 rounded-lg border border-gray-200 w-full">
                        <h4 class="text-lg font-medium text-gray-600">Total Allowances</h4>
                        <p id="totalAllowances" class="text-2xl font-bold text-green-600"><?php echo number_format($totalAllowancesSum, 2); ?></p>
                    </div>
                    <div class="card bg-white shadow-md p-6 rounded-lg border border-gray-200 w-full">
                        <h4 class="text-lg font-medium text-gray-600">Total Deductions</h4>
                        <p id="totalDeductions" class="text-2xl font-bold text-red-600"><?php echo number_format($totalDeductionsSum, 2); ?></p>
                    </div>
                    <div class="card bg-white shadow-md p-6 rounded-lg border border-gray-200 w-full">
                        <h4 class="text-lg font-medium text-gray-600">Total Gross Pay</h4>
                        <p id="totalGross" class="text-2xl font-bold text-teal-600"><?php echo number_format($totalGrossSum, 2); ?></p>
                    </div>
                    <div class="card bg-white shadow-md p-6 rounded-lg border border-gray-200 w-full">
                        <h4 class="text-lg font-medium text-gray-600">Total Net Pay</h4>
                        <p id="totalNet" class="text-2xl font-bold text-purple-600"><?php echo number_format($totalNetPaySum, 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex gap-4 my-6">
                <select id="gradeFilter" class="p-3 border rounded-md">
                    <option value="" class="bg-gray-600 text-white">Select Grade</option>
                    <?php foreach ($grades as $grade): ?>
                        <option value="<?php echo htmlspecialchars($grade); ?>"><?php echo htmlspecialchars($grade); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="designationFilter" class="p-3 border rounded-md">
                    <option option value="" class="bg-gray-600 text-white">Select Designation</option>
                    <?php foreach ($designations as $designation): ?>
                        <option value="<?php echo htmlspecialchars(strtolower($designation)); ?>">
                            <?php echo htmlspecialchars($designation); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select id="departmentFilter" class="p-3 border rounded-md">
                    <option option value="" class="bg-gray-600 text-white">Select Department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo htmlspecialchars($department); ?>"><?php echo htmlspecialchars($department); ?></option>
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
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Account_Number</th>
                                <th>E-TIN</th>
                                <th>Charge Allw</th>
                                <th>Telephone Allw</th>
                                <th>Additional Designation</th>
                                <th>Dearness Allw</th>
                                <th>House Allw</th>
                                <th>Medical Allw </th>
                                <th>Education Allw </th>
                                <th>Festival Allw </th>
                                <th>Research Allw </th>
                                <th>New Bangla Year </th>
                                <th>Recreation Allw </th>
                                <th>Other Allw </th>
                                <th>GPF </th>
                                <th>GPF Installment </th>
                                <th>HouseDed </th>
                                <th>Benevolent Fund </th>
                                <th>Insurance </th>
                                <th>Electricity </th>
                                <th>HRD Extra </th>
                                <th>Club Subscription </th>
                                <th>Association Subscription </th>
                                <th>Transport Bill </th>
                                <th>Telephone Bill</th>
                                <th>Pension Fund</th>
                                <th>Fish Bill</th>
                                <th>Income Tax</th>
                                <th>Donation</th>
                                <th>Guest House Rent</th>
                                <th>House Loan Installment_1</th>
                                <th>House Loan Installment_2</th>
                                <th>House Loan Installment_3</th>
                                <th>Salary Adjustment</th>
                                <th>Other Ded</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTable">
                            <?php foreach ($payrollData as $payroll): ?>
                            <tr>
                                <td><?php echo $payroll['employeeNo']; ?></td>
                                <td><?php echo $payroll['name']; ?></td>
                                <td class="designation"><?php echo $payroll['designation']; ?></td>
                                <td class="department whitespace-nowrap"><?php echo $payroll['departments']; ?></td>
                                <td class="grade"><?php echo $payroll['grade']; ?></td>
                                <td><?php echo $payroll['increment']; ?></td>
                                <td><?php echo $payroll['basic']; ?></td>
                                <td class="allowance"><?php echo $payroll['totalAllowance']; ?></td>
                                <td class="gross"><?php echo $payroll['gross']; ?></td>
                                <td class="deduction"><?php echo $payroll['totalDeduction']; ?></td>
                                <td class="net"><?php echo $payroll['netPay']; ?></td>
                                <td><?php echo $payroll['contactNo']; ?></td>
                                <td><?php echo $payroll['email']; ?></td>
                                <td><?php echo $payroll['empStatus']; ?></td>
                                <td><?php echo $payroll['account_number']; ?></td>
                                <td><?php echo $payroll['e_tin']; ?></td>
                                <td><?php echo $payroll['chargeAllw']; ?></td>
                                <td><?php echo $payroll['telephoneAllwance']; ?></td>
                                <td><?php echo $payroll['AdditionalDesignation']; ?></td>
                                <td><?php echo $payroll['dearnessAllw'];?></td>
                                <td><?php echo $payroll['houseAllw'];?></td>
                                <td><?php echo $payroll['medicalAllw'];?></td>
                                <td><?php echo $payroll['educationAllw'];?></td>
                                <td><?php echo $payroll['festivalAllw'];?></td>
                                <td><?php echo $payroll['researchAllw'];?></td>
                                <td><?php echo $payroll['newBdYrAllw'];?></td>
                                <td><?php echo $payroll['recreationAllw'];?></td>
                                <td><?php echo $payroll['otherAllw'];?></td>
                                <td><?php echo $payroll['gpf'];?></td>
                                <td><?php echo $payroll['gpfInstallment'];?></td>
                                <td><?php echo $payroll['houseDed'];?></td>
                                <td><?php echo $payroll['benevolentFund'];?></td>
                                <td><?php echo $payroll['insurance'];?></td>
                                <td><?php echo $payroll['electricity'];?></td>
                                <td><?php echo $payroll['hrdExtra'];?></td>
                                <td><?php echo $payroll['clubSubscription'];?></td>
                                <td><?php echo $payroll['assoSubscription'];?></td>
                                <td><?php echo $payroll['transportBill'];?></td>
                                <td><?php echo $payroll['telephoneBill'];?></td>
                                
                                <td><?php echo $payroll['pensionFund'];?></td>
                                <td><?php echo $payroll['fishBill'];?></td>
                                <td><?php echo $payroll['incomeTax'];?></td>
                                <td><?php echo $payroll['donation'];?></td>
                                <td><?php echo $payroll['guestHouseRent'];?></td>
                                <td><?php echo $payroll['houseLoanInstallment_1'];?></td>
                                <td><?php echo $payroll['houseLoanInstallment_2'];?></td>
                                <td><?php echo $payroll['houseLoanInstallment_3'];?></td>
                                <td><?php echo $payroll['salaryAdjustment'];?></td>
                                <td><?php echo $payroll['revenue'];?></td>
                                <td><?php echo $payroll['otherDed'];?></td>
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
    const employeeTable = document.getElementById('employeeTable');
    const totalEmployeesElem = document.getElementById('totalEmployees');
    const totalAllowancesElem = document.getElementById('totalAllowances');
    const totalDeductionsElem = document.getElementById('totalDeductions');
    const totalGrossElem = document.getElementById('totalGross');
    const totalNetElem = document.getElementById('totalNet');

    const calculateTotals = () => {
        let totalEmployees = 0;
        let totalAllowances = 0;
        let totalDeductions = 0;
        let totalGross = 0;
        let totalNet = 0;

        const rows = employeeTable.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                totalEmployees++;
                totalAllowances += parseFloat(row.querySelector('.allowance').innerText) || 0;
                totalDeductions += parseFloat(row.querySelector('.deduction').innerText) || 0;
                totalGross += parseFloat(row.querySelector('.gross').innerText) || 0;
                totalNet += parseFloat(row.querySelector('.net').innerText) || 0;
            }
        });

        totalEmployeesElem.innerText = totalEmployees;
        totalAllowancesElem.innerText = totalAllowances.toFixed(2);
        totalDeductionsElem.innerText = totalDeductions.toFixed(2);
        totalGrossElem.innerText = totalGross.toFixed(2);
        totalNetElem.innerText = totalNet.toFixed(2);
    };

    const filterEmployees = () => {
        const gradeValue = gradeFilter.value.toLowerCase();
        const designationValue = designationFilter.value.toLowerCase();
        const departmentValue = departmentFilter.value.toLowerCase();

        const rows = employeeTable.querySelectorAll('tr');
        rows.forEach(row => {
            const grade = row.querySelector('.grade').innerText.toLowerCase();
            const designation = row.querySelector('.designation').innerText.toLowerCase();
            const department = row.querySelector('.department').innerText.toLowerCase();

            // Apply exact match for designation, while allowing partial matches for other filters
            if (
                (gradeValue === '' || grade === gradeValue) &&
                (designationValue === '' || designation === designationValue) &&
                (departmentValue === '' || department.includes(departmentValue))
            ) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        calculateTotals();
    };

    gradeFilter.addEventListener('change', filterEmployees);
    designationFilter.addEventListener('change', filterEmployees);
    departmentFilter.addEventListener('input', filterEmployees);
});
</script>
</body>
</html>
