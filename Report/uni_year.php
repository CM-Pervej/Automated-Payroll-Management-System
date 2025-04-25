<?php
session_start();

// Check if the user is logged in and has the HR role
if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2)) {
    header('Location: ../dashboard.php');
    exit();
}

// Include the database connection
include '../db_conn.php';

// Get user-selected start and end month/year
$start_month = isset($_GET['start_month']) ? (int)$_GET['start_month'] : date('m');
$start_year = isset($_GET['start_year']) ? (int)$_GET['start_year'] : date('Y');
$end_month = isset($_GET['end_month']) ? (int)$_GET['end_month'] : date('m');
$end_year = isset($_GET['end_year']) ? (int)$_GET['end_year'] : date('Y');

// Initialize totals
$totalEmployees = 0;
$totalBasic = 0;
$totalAllowances = 0;
$totalDeductions = 0;
$totalSalary = 0;
$payrollData = [];

// Loop through each month in the range
$current_year = $start_year;
$current_month = $start_month;

while (($current_year < $end_year) || ($current_year == $end_year && $current_month <= $end_month)) {
    // Prepare SQL query for the current month and year
    $stmt = $conn->prepare("SELECT * FROM payroll WHERE `month` = ? AND `year` = ?");
    $stmt->bind_param("ii", $current_month, $current_year);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize totals for this month
    $monthTotalBasic = 0;
    $monthTotalAllowances = 0;
    $monthTotalDeductions = 0;
    $monthTotalSalary = 0;
    $employeeCount = 0;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Calculate allowances
            $totalAllowance = array_sum([
                $row['chargeAllw'], $row['telephoneAllwance'],
                $row['dearnessAllw'], $row['houseAllw'], $row['medicalAllw'],
                $row['educationAllw'], $row['festivalAllw'], $row['researchAllw'],
                $row['newBdYrAllw'], $row['recreationAllw'], $row['otherAllw']
            ]);

            // Calculate deductions
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

            // Net pay
            $netPay = $row['basic'] + $totalAllowance - $totalDeduction;

            // Sum monthly totals
            $monthTotalBasic += $row['basic'];
            $monthTotalAllowances += $totalAllowance;
            $monthTotalDeductions += $totalDeduction;
            $monthTotalSalary += $netPay;
            $employeeCount++;
        }

        // Store monthly totals in an array
        $payrollData[] = [
            'month' => $current_month,
            'year' => $current_year,
            'employees' => $employeeCount,
            'basic' => $monthTotalBasic,
            'allowances' => $monthTotalAllowances,
            'deductions' => $monthTotalDeductions,
            'totalSalary' => $monthTotalSalary
        ];

        // Add to overall totals
        $totalEmployees += $employeeCount;
        $totalBasic += $monthTotalBasic;
        $totalAllowances += $monthTotalAllowances;
        $totalDeductions += $monthTotalDeductions;
        $totalSalary += $monthTotalSalary;
    }

    // Move to next month
    if ($current_month == 12) {
        $current_month = 1;
        $current_year++;
    } else {
        $current_month++;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        /* Page break styles */
        .page-container {
            page-break-before: always; /* Start a new page before this container */
            page-break-after: always; /* End the page after this container */
        }
    </style>
        <script>
        // Automatically trigger the print dialog when the page loads
        window.onload = function () {
            window.print();
        };
    </script>
</head>
<body class="bg-gray-100 px-8 text-black">
    <section class="page-container pt-5 relative h-screen flex flex-col gap-10">
        <aside>
            <div class="flex justify-between items-center border-b pb-6 mb-6">
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-extrabold text-blue-900">Jashore University of Science and Technology, <span class="font-semibold text-black">Jashore - 7408</span></h1>
                    <p class="text-gray-600 text-lg">Payroll Period: <span class="font-semibold"><?php echo date('F, Y', strtotime("{$start_year}-{$start_month}-01")); ?> to <?php echo date('F, Y', strtotime("{$end_year}-{$end_month}-01")); ?></span></p>
                    <p class="text-gray-600 text-lg">Date: <span class="font-semibold"><?php echo date('F d, Y'); ?></span></p>
                </div>
                <div>
                    <img src="../uploads/JUSTt.png" alt="University Logo" class="h-20">
                </div>
            </div>

            <table class="table-auto w-full mt-6 border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-1 border">SL</th>
                        <th class="p-1 border">Month</th>
                        <th class="p-1 border">Year</th>
                        <th class="p-1 border">Employees</th>
                        <th class="p-1 border">Basic Salary</th>
                        <th class="p-1 border">Allowances</th>
                        <th class="p-1 border">Deductions</th>
                        <th class="p-1 border">Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payrollData)): ?>
                        <?php $sum = 0; // Initialize sum variable ?>
                        <?php foreach ($payrollData as $index => $row): ?>
                            <?php $sum += $row['totalSalary']; // Add each row's total salary to sum ?>
                            <tr class="text-right">
                                <td class="p-1 border w-max text-center"><?php echo $index + 1; ?></td>
                                <td class="p-1 border w-max text-center"><?php echo date('F', mktime(0, 0, 0, $row['month'], 1)); ?></td>
                                <td class="p-1 border w-max text-center"><?php echo $row['year']; ?></td>
                                <td class="p-1 border w-max text-center"><?php echo $row['employees']; ?></td>
                                <td class="p-1 border w-max"><?php echo number_format($row['basic'], 2); ?></td>
                                <td class="p-1 border w-max"><?php echo number_format($row['allowances'], 2); ?></td>
                                <td class="p-1 border w-max"><?php echo number_format($row['deductions'], 2); ?></td>
                                <td class="p-1 border w-max font-bold"><?php echo number_format($row['totalSalary'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Display Total Sum Row -->
                        <tr class="bg-gray-200 font-bold">
                            <td colspan="7" class="py-3 text-right">Total Salary from <span class="font-bold"><?php echo date('F, Y', strtotime("{$start_year}-{$start_month}-01")); ?></span> to <span class="font-bold"><?php echo date('F, Y', strtotime("{$end_year}-{$end_month}-01")); ?></span>:</td>
                            <td class="p-3 text-right"><?php echo number_format($sum, 2); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center p-4 text-red-500">No payroll data found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p class="mt-5">In Words: <span class="net-pay-words text-lg font-semibold"></span></p>
        </aside>

        <section class=" w-full">
            <article class="flex justify-between items-end gap-5 mt-5">
                <aside class="flex-1 flex flex-col gap-4">
                    <p class="flex justify-end w-full"> <span class="border border-black py-6 px-3 w-max tracking-widest">STAMP</span></p>
                    <p class="text-lg text-center border-t border-black border-dashed whitespace-nowrap">Incumbent's Signature</p>
                </aside>
                <aside class="flex-1">
                    <p class="text-lg text-center border-t border-black border-dashed whitespace-nowrap">Prepared By</p>
                </aside>
                <aside class="flex-1 flex flex-col gap-10">
                    <p class="text-center">Remarks</p>
                    <p class="text-lg text-center border-t border-black border-dashed whitespace-nowrap">Assistant Director</p>
                </aside>
                <aside class="flex-1">
                    <p class="text-lg text-center border-t border-black border-dashed whitespace-nowrap">Deputy Director</p>
                </aside>
                <aside class="flex-1">
                    <p class="text-lg text-center border-t border-black border-dashed whitespace-nowrap">Director</p>
                </aside>
            </article>
            <article class="mt-10 text-xl border-t border-black py-5">
                <p>Website developed by the Department of <span class="font-bold">Computer Science and Engineering</span>, JUST</p>
                <p>NB. If there is any error in your bill, immediately inform it to the <span class="font-bold">Accounts Office</span>  of JUST</p>
            </article>
        </section>
    </section>

    
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
            var sum = <?php echo json_encode($sum ?? 0); ?>;
            document.querySelector('.net-pay-words').textContent = convertNumberToWords(sum);
        });
    </script>
</body>
</html>
