<?php
include 'view.php'; 
include 'db_conn.php';
$employeesData = [];

// Fetch all allowances from allowanceList
$allowanceList = [];
$allowanceList_stmt = $conn->prepare("SELECT * FROM allowanceList");
$allowanceList_stmt->execute();
$allowanceList_result = $allowanceList_stmt->get_result();
while ($row = $allowanceList_result->fetch_assoc()) {
    $allowanceList[] = $row;
}
$allowanceList_stmt->close();

// Fetch all deductions from deductionList
$deductionList = [];
$deductionList_stmt = $conn->prepare("SELECT * FROM deductionList");
$deductionList_stmt->execute();
$deductionList_result = $deductionList_stmt->get_result();
while ($row = $deductionList_result->fetch_assoc()) {
    $deductionList[] = $row;
}
$deductionList_stmt->close();

// Fetch all employees
$employees_stmt = $conn->prepare(" SELECT e.id AS employee_id, e.employeeNo, e.name, e.gender, e.contactNo, e.email, e.empStatus, e.no_of_increment, e.basic, e.account_number, e.e_tin, e.joining_date, e.image, e.approve, d.designation AS primary_designation, dept.department_name, g.grade, g.scale
                                                FROM employee e
                                                JOIN designations d ON e.designation_id = d.id
                                                JOIN departments dept ON e.department_id = dept.id
                                                JOIN grade g ON e.grade_id = g.id
                                                WHERE e.approve != 0 AND e.empStatus = 1
                                                ORDER BY 
                                                dept.department_name ASC,
                                                g.grade ASC,
                                                no_of_increment DESC, 
                                                e.joining_date ASC
                                            ");
$employees_stmt->execute();
$employees_result = $employees_stmt->get_result();

while ($employee = $employees_result->fetch_assoc()) {
    // Initialize employee data
    $employee_id = $employee['employee_id'];
    $totalAllowance = 0;
    $totalDeduction = 0;
    $grossPay = 0;
    $netPay = 0;
    $allowances = [];
    $deductions = [];
    $additionalDesignations = [];

    // Fetch allowances
    $allowance_stmt = $conn->prepare("SELECT ac.allwTotal, allw.allwName, ac.allowanceList_id
                                                                FROM allwConfirm ac
                                                                JOIN allowanceList allw ON ac.allowanceList_id = allw.id
                                                                WHERE ac.employee_id = ?");
    $allowance_stmt->bind_param("i", $employee_id);
    $allowance_stmt->execute();
    $allowance_result = $allowance_stmt->get_result();
    while ($row = $allowance_result->fetch_assoc()) {
        $allowances[] = $row;
        $totalAllowance += $row['allwTotal'];
    }
    $allowance_stmt->close();

    // Fetch deductions
    $deduction_stmt = $conn->prepare("SELECT dc.dedTotal, ded.dedName, dc.deductionList_id 
                                                                FROM dedConfirm dc
                                                                JOIN deductionList ded ON dc.deductionList_id = ded.id
                                                                WHERE dc.employee_id = ?");
    $deduction_stmt->bind_param("i", $employee_id);
    $deduction_stmt->execute();
    $deduction_result = $deduction_stmt->get_result();
    while ($row = $deduction_result->fetch_assoc()) {
        $deductions[] = $row;
        $totalDeduction += $row['dedTotal'];
    }
    $deduction_stmt->close();

    // Fetch empAddSalary data
    $empAddSalary_stmt = $conn->prepare("SELECT chargeAllw, telephoneAllwance FROM empAddSalary WHERE employee_id = ?");
    $empAddSalary_stmt->bind_param("i", $employee_id);
    $empAddSalary_stmt->execute();
    $empAddSalary_result = $empAddSalary_stmt->get_result();
    $empAddSalary = $empAddSalary_result->fetch_assoc();
    $empAddSalary_stmt->close();

    // Fetch additional designations
    $addDesignation_stmt = $conn->prepare(" SELECT ad.designation AS additional_designation
                                                            FROM empAddDesignation ead
                                                            JOIN addDuty ad ON ead.addDuty_id = ad.id
                                                            JOIN empAddSalary eas ON eas.id = ead.empAddSalary_id
                                                            WHERE eas.employee_id = ?
                                                        ");
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

    // Store employee data
    $employeesData[] = [
        'employee' => $employee,
        'allowances' => $allowances,
        'deductions' => $deductions,
        'totalAllowance' => $totalAllowance,
        'totalDeduction' => $totalDeduction,
        'grossPay' => $grossPay,
        'netPay' => $netPay,
        'additionalDesignations' => $additionalDesignations,
        'empAddSalary' => $empAddSalary,
    ];
}
    // Payroll insertion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitPayroll'])) {
        // Get current month and year
        $month = date('m');
        $year = date('Y');

        // Prepare SQL to check if records for the same month and year already exist
        $checkSql = "SELECT COUNT(*) FROM payroll WHERE month = ? AND year = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $month, $year);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            // If records already exist for the same month and year, delete them
            $deleteSql = "DELETE FROM payroll WHERE month = ? AND year = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("ii", $month, $year);
            $deleteStmt->execute();
            $deleteStmt->close();
        }

        // Now, proceed with the insertion of new payroll data
        foreach ($employeesData as $data) {
            $employee = $data['employee'];
            // Generate a string for Additional Designations using implode and store it in a variable
            $additionalDesignationsString = implode(", ", $data['additionalDesignations']);

            // Prepare individual variables to store values that are passed into bind_param
            $chargeAllw = $data['empAddSalary']['chargeAllw'] ?? 0;
            $telephoneAllwance = $data['empAddSalary']['telephoneAllwance'] ?? 0;
            $additionalDesignationsString = implode(", ", $data['additionalDesignations']); // This is already handled properly.

            // Handle allowances
            $dearnessAllw = $data['allowances'][0]['allwTotal'] ?? 0;
            $houseAllw = $data['allowances'][1]['allwTotal'] ?? 0;
            $medicalAllw = $data['allowances'][2]['allwTotal'] ?? 0;
            $educationAllw = $data['allowances'][3]['allwTotal'] ?? 0;
            $festivalAllw = $data['allowances'][4]['allwTotal'] ?? 0;
            $researchAllw = $data['allowances'][5]['allwTotal'] ?? 0;
            $newBdYrAllw = $data['allowances'][6]['allwTotal'] ?? 0;
            $recreationAllw = $data['allowances'][7]['allwTotal'] ?? 0;
            $otherAllw = $data['allowances'][8]['allwTotal'] ?? 0;

            // Sum allowances from index 9 onward
            $additionalAllowances = array_slice($data['allowances'], 9); // Get all allowances from index 9
            $otherAllw += array_sum(array_column($additionalAllowances, 'allwTotal')); // Add to $otherAllw

            // Handle deductions
            $gpf = $data['deductions'][0]['dedTotal'] ?? 0;
            $gpfInstallment = $data['deductions'][1]['dedTotal'] ?? 0;
            $houseDed = $data['deductions'][2]['dedTotal'] ?? 0;
            $benevolentFund = $data['deductions'][3]['dedTotal'] ?? 0;
            $insurance = $data['deductions'][4]['dedTotal'] ?? 0;
            $electricity = $data['deductions'][5]['dedTotal'] ?? 0;
            $hrdExtra = $data['deductions'][6]['dedTotal'] ?? 0;
            $clubSubscription = $data['deductions'][7]['dedTotal'] ?? 0;
            $assoSubscription = $data['deductions'][8]['dedTotal'] ?? 0;
            $transportBill = $data['deductions'][9]['dedTotal'] ?? 0;
            $telephoneBill = $data['deductions'][10]['dedTotal'] ?? 0;
            $pensionFund = $data['deductions'][11]['dedTotal'] ?? 0;
            $fishBill = $data['deductions'][12]['dedTotal'] ?? 0;
            $incomeTax = $data['deductions'][13]['dedTotal'] ?? 0;
            $donation = $data['deductions'][14]['dedTotal'] ?? 0;
            $guestHouseRent = $data['deductions'][15]['dedTotal'] ?? 0;
            $houseLoanInstallment_1 = $data['deductions'][16]['dedTotal'] ?? 0;
            $houseLoanInstallment_2 = $data['deductions'][17]['dedTotal'] ?? 0;
            $houseLoanInstallment_3 = $data['deductions'][18]['dedTotal'] ?? 0;
            $salaryAdjustment = $data['deductions'][19]['dedTotal'] ?? 0;
            $otherDed = $data['deductions'][20]['dedTotal'] ?? 0;
            $revenue = $data['deductions'][21]['dedTotal'] ?? 0;

            // Sum deductions from index 22 onward
            $additionalDeductions = array_slice($data['deductions'], 22); // Get all deductions from index 22
            $otherDed += array_sum(array_column($additionalDeductions, 'dedTotal')); // Add to $otherDed

            // Now prepare the SQL statement for insertion
            $sql = "INSERT INTO payroll (
                employee_id, employeeNo, name, gender, contactNo, email, empStatus, designation, departments, grade, 
                increment, scale, basic, account_number, e_tin, chargeAllw, telephoneAllwance, AdditionalDesignation,
                dearnessAllw, houseAllw, medicalAllw, educationAllw, festivalAllw, researchAllw, newBdYrAllw, recreationAllw, otherAllw, 
                gpf, gpfInstallment, houseDed, benevolentFund, insurance, electricity, hrdExtra, clubSubscription, 
                assoSubscription, transportBill, telephoneBill, pensionFund, fishBill, incomeTax, donation, 
                guestHouseRent, houseLoanInstallment_1, houseLoanInstallment_2, houseLoanInstallment_3, salaryAdjustment, 
                revenue, otherDed, month, year
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";

            // Now bind the parameters, ensuring everything is passed as variables
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ississsssssddddddsddddsssssssssssssssssssiiiiiiiiii",
                $employee['employee_id'], $employee['employeeNo'], $employee['name'], $employee['gender'], $employee['contactNo'], 
                $employee['email'], $employee['empStatus'], $employee['primary_designation'], $employee['department_name'], 
                $employee['grade'], $employee['no_of_increment'], $employee['scale'], $employee['basic'], 
                $employee['account_number'], $employee['e_tin'], $chargeAllw, $telephoneAllwance, $additionalDesignationsString, 
                $dearnessAllw, $houseAllw, $medicalAllw, $educationAllw, $festivalAllw, $researchAllw, $newBdYrAllw, $recreationAllw, $otherAllw, 
                $gpf, $gpfInstallment, $houseDed, $benevolentFund, $insurance, $electricity, $hrdExtra, $clubSubscription, 
                $assoSubscription, $transportBill, $telephoneBill, $pensionFund, $fishBill, $incomeTax, $donation, 
                $guestHouseRent, $houseLoanInstallment_1, $houseLoanInstallment_2, $houseLoanInstallment_3, $salaryAdjustment, 
                $revenue, $otherDed, $month, $year
            );

            // Execute the query
            if (!$stmt->execute()) {
                error_log("Payroll insert failed for employee_id {$employee['employee_id']}: " . $stmt->error);
            }
            $stmt->close();
        }
        $_SESSION['success'] = "Payroll data inserted successfully!";
        header('Location: payroll.php');
        exit();
    }

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Payroll</title>
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
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-20">
                <?php include 'topBar.php'; ?>
            </aside>
         </div>
        <!-- Content Section -->
        <main class="flex-grow mt-20 bg-white shadow-lg overflow-auto">
            <form method="post" action="payroll.php">
                <div class="mb-4 pt-10 pr-64 pb-2 pl-5 w-full flex fixed left-64 top-16 right-0 z-10 bg-white">
                    <h2 class="text-2xl font-semibold text-left">Employee Payroll Information</h2>
                    <div class="flex-1 px-4">
                        <input type="text" id="search" class="p-3 w-full rounded-md border border-gray-300" placeholder="Search for employees...">
                    </div>
                    <div>
                        <!-- Include a hidden input with employee ID and submit button -->
                        <input type="hidden" name="employee_id[]" value="<?php echo $data['employee']['employee_id']; ?>">
                        <button type="submit" name="submitPayroll" class="btn btn-primary text-white px-4 py-2 rounded-md" <?php echo ($userrole_id != 1 && $userrole_id != 2) ? 'hidden' : ''; ?> title="Only Admin and HR can access this page">Submit</button>
                    </div>
                </div>
                <section class="container mx-auto px-4 mt-24">
                    <div class="overflow-auto w-fit mx-auto pr-72 mr-72">
                        <!-- <table class="table w-full border-collapse border border-gray-200 rounded-lg shadow-md bg-white mb-10"> -->
                        <table class="table w-full border-collapse border border-gray-200 rounded-lg shadow-md bg-white mb-10" role="table" aria-label="Employee Payroll Information">
                            <thead class="bg-gray-200 text-sm">
                                <tr>
                                    <th id="th1" scope="col" class="p-3 text-left border border-gray-300">Image</th>
                                    <th id="th2" scope="col" class="p-3 text-left border border-gray-300">Employee No</th>
                                    <th id="th3" scope="col" class="p-3 text-left border border-gray-300">Name</th>
                                    <th id="th3" scope="col" class="p-3 text-left border border-gray-300">Gender</th>
                                    <th id="th4" scope="col" class="p-3 text-left border border-gray-300">Primary Designation</th>
                                    <th id="th5" scope="col" class="p-3 text-left border border-gray-300">Department</th>
                                    <th id="th6" scope="col" class="p-3 text-left border border-gray-300">Grade</th>
                                    <th id="th7" scope="col" class="p-3 text-left border border-gray-300">Scale</th>
                                    <th id="th8" scope="col" class="p-3 text-left border border-gray-300">Increment</th>
                                    <th id="th9" scope="col" class="p-3 text-left border border-gray-300">Basic Salary</th>

                                    <?php foreach ($allowanceList as $index => $allowance) : ?>
                                        <th id="th-allowance-<?php echo $index; ?>" class="p-3 text-left border border-gray-300">
                                            <?php echo $allowance['allwName']; ?>
                                        </th>
                                    <?php endforeach; ?>

                                    <th id="th-total-allowance" class="p-3 text-left border border-gray-300">Total Allowance</th>

                                    <?php foreach ($deductionList as $index => $deduction) : ?>
                                        <th id="th-deduction-<?php echo $index; ?>" class="p-3 text-left border border-gray-300">
                                            <?php echo $deduction['dedName']; ?>
                                        </th>
                                    <?php endforeach; ?>

                                    <th id="th-total-deduction" scope="col" class="p-3 text-left border border-gray-300">Total Deduction</th>
                                    <th id="th-charge-allw" scope="col" class="p-3 text-left border border-gray-300">Charge Allw</th>
                                    <th id="th-telephone-allw" scope="col" class="p-3 text-left border border-gray-300">Telephone Allw</th>
                                    <th id="th-gross-pay" scope="col" class="p-3 text-left border border-gray-300">Gross Pay</th>
                                    <th id="th-net-pay" scope="col" class="p-3 text-left border border-gray-300">Net Pay</th>
                                    <th id="th-additional-designations" scope="col" class="p-3 text-left border border-gray-300">Additional Designations</th>
                                </tr>
                            </thead>
                            <tbody id="employeeTable">
                                <?php foreach ($employeesData as $data) : ?>
                                    <tr class="border-b hover:bg-gray-100">
                                        <td headers="th1" class="w-max">
                                            <p class="w-max">
                                                <?php 
                                                    if (!empty($data['employee']['image'])) {
                                                        $imagePath = 'uploads/' . basename($data['employee']['image']);
                                                        
                                                        // Check if the file exists before displaying
                                                        if (file_exists($imagePath)): 
                                                ?>
                                                <img src="<?php echo $imagePath; ?>" alt="Profile Image" class="size-14 object-cover rounded-full border border-gray-300" />
                                                <?php else: ?>
                                                    <p class="text-red-500">Image file does not exist at: <?php echo htmlspecialchars($imagePath); ?></p>
                                                <?php 
                                                        endif; 
                                                    } else {
                                                ?>
                                                    <p>No image available for this employee.</p>
                                                <?php } ?>
                                            </p>
                                        </td>
                                        <td headers="th2" class="p-3 text-left border"><?php echo $data['employee']['employeeNo']; ?></td>
                                        <td headers="th3" class="p-3 text-left border">
                                            <a href="profile.php?employee_id=<?php echo $data['employee']['employee_id']; ?>" class="text-blue-600 font-semibold whitespace-nowrap hover:underline">
                                                <?php echo $data['employee']['name']; ?>
                                            </a>
                                        </td>
                                        <td headers="th6" class="p-3 text-left border">
                                            <?php 
                                                $gender = $data['employee']['gender'];
                                                if ($gender == 1) {
                                                    echo "Male";
                                                } elseif ($gender == 2) {
                                                    echo "Female";
                                                } elseif ($gender == 0) {
                                                    echo "Other";
                                                } else {
                                                    echo "Not specified"; 
                                                }
                                            ?>
                                        </td>
                                        <td headers="th4" class="p-3 text-left border whitespace-nowrap"><?php echo $data['employee']['primary_designation']; ?></td>
                                        <td headers="th5" class="p-3 text-left border whitespace-nowrap"><?php echo $data['employee']['department_name']; ?></td>
                                        <td headers="th6" class="p-3 text-left border"><?php echo $data['employee']['grade']; ?></td>
                                        <td headers="th7" class="p-3 text-left border"><?php echo $data['employee']['scale']; ?>.00</td>
                                        <td headers="th8" class="p-3 text-left border"><?php echo $data['employee']['no_of_increment']; ?></td>
                                        <td headers="th9" class="p-3 text-left border"><?php echo $data['employee']['basic']; ?></td>

                                        <?php foreach ($allowanceList as $index => $allowance): ?>
                                            <?php 
                                            // Find the corresponding allowance value by name
                                            $allowanceValue = null;
                                            foreach ($data['allowances'] as $empAllowance) {
                                                if ($empAllowance['allwName'] === $allowance['allwName']) {
                                                    $allowanceValue = $empAllowance['allwTotal'];
                                                    break;
                                                }
                                            }
                                            ?>
                                            <td headers="th-allowance-<?php echo $index; ?>" class="p-3 text-left border">
                                                <?php echo $allowanceValue !== null ? $allowanceValue : '0.00'; // Use '0' or 'N/A' for missing values ?>
                                            </td>
                                        <?php endforeach; ?>

                                        <td headers="th-total-allowance" class="p-3 text-left border"><?php echo $data['totalAllowance']; ?></td>

                                        <?php foreach ($deductionList as $index => $deduction): ?>
                                            <?php 
                                            // Find the corresponding deduction value by name
                                            $deductionValue = null;
                                            foreach ($data['deductions'] as $empDeduction) {
                                                if ($empDeduction['dedName'] === $deduction['dedName']) {
                                                    $deductionValue = $empDeduction['dedTotal'];
                                                    break;
                                                }
                                            }
                                            ?>
                                            <td headers="th-deduction-<?php echo $index; ?>" class="p-3 text-left border">
                                                <?php echo $deductionValue !== null ? $deductionValue : '0.00'; // Use '0' or 'N/A' for missing values ?>
                                            </td>
                                        <?php endforeach; ?>

                                        <td headers="th-total-deduction" class="p-3 text-left border"><?php echo $data['totalDeduction']; ?></td>
                                        <td headers="th-charge-allw" class="p-3 text-left border"><?php echo $data['empAddSalary']['chargeAllw']; ?></td>
                                        <td headers="th-telephone-allw" class="p-3 text-left border"><?php echo $data['empAddSalary']['telephoneAllwance']; ?></td>
                                        <td headers="th-gross-pay" class="p-3 text-left border"><?php echo $data['grossPay']; ?></td>
                                        <td headers="th-net-pay" class="p-3 text-left border"><?php echo $data['netPay']; ?></td>
                                        <td headers="th-additional-designations" class="p-3 text-left border whitespace-nowrap">
                                            <?php echo !empty($data['additionalDesignations']) ? implode(", ", $data['additionalDesignations']) : 'N/A'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </form>
        </main>
    </div>

<script>
    // Simple search function
    document.getElementById('search').addEventListener('input', function(e) {
        const searchQuery = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#employeeTable tr');
        
        rows.forEach(row => {
            const columns = row.querySelectorAll('td');
            let match = false;

            columns.forEach(column => {
                if (column.innerText.toLowerCase().includes(searchQuery)) {
                    match = true;
                }
            });

            if (match) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>
