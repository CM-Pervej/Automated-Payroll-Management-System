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

// Initialize an empty array for storing payroll data per designation
$designationData = [];

// Loop through each month in the range
$current_year = $start_year;
$current_month = $start_month;

while (($current_year < $end_year) || ($current_year == $end_year && $current_month <= $end_month)) {
    // Prepare SQL query to get data per designation
    $stmt = $conn->prepare("SELECT * FROM payroll WHERE `month` = ? AND `year` = ? GROUP BY designation, employee_id");
    $stmt->bind_param("ii", $current_month, $current_year);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize totals for each designation
    $designationTotals = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Group payroll data by designation
            $designation = $row['designation'];

            // Initialize designation if not already done
            if (!isset($designationTotals[$designation])) {
                $designationTotals[$designation] = [
                    'totalEmployees' => 0,
                    'totalBasic' => 0,
                    'totalAllowances' => 0,
                    'totalDeductions' => 0,
                    'totalSalary' => 0
                ];
            }

            // Calculate allowances
            $totalAllowance = array_sum([ 
                $row['chargeAllw'], $row['telephoneAllwance'], $row['dearnessAllw'], $row['houseAllw'],
                $row['medicalAllw'], $row['educationAllw'], $row['festivalAllw'], $row['researchAllw'],
                $row['newBdYrAllw'], $row['recreationAllw'], $row['otherAllw']
            ]);

            // Calculate deductions
            $totalDeduction = array_sum([ 
                $row['gpf'], $row['gpfInstallment'], $row['houseDed'], $row['benevolentFund'],
                $row['insurance'], $row['electricity'], $row['hrdExtra'], $row['clubSubscription'],
                $row['assoSubscription'], $row['transportBill'], $row['telephoneBill'], $row['pensionFund'],
                $row['fishBill'], $row['incomeTax'], $row['donation'], $row['guestHouseRent'],
                $row['houseLoanInstallment_1'], $row['houseLoanInstallment_2'], $row['houseLoanInstallment_3'],
                $row['salaryAdjustment'], $row['revenue'], $row['otherDed']
            ]);

            // Net pay
            $netPay = $row['basic'] + $totalAllowance - $totalDeduction;

            // Update designation totals
            $designationTotals[$designation]['totalEmployees']++;
            $designationTotals[$designation]['totalBasic'] += $row['basic'];
            $designationTotals[$designation]['totalAllowances'] += $totalAllowance;
            $designationTotals[$designation]['totalDeductions'] += $totalDeduction;
            $designationTotals[$designation]['totalSalary'] += $netPay;
        }
    }

    // Store the designation totals for the current month
    foreach ($designationTotals as $designation => $totals) {
        if (!isset($designationData[$designation])) {
            $designationData[$designation] = [];
        }

        $designationData[$designation][] = [
            'month' => $current_month,
            'year' => $current_year,
            'employees' => $totals['totalEmployees'],
            'basic' => $totals['totalBasic'],
            'allowances' => $totals['totalAllowances'],
            'deductions' => $totals['totalDeductions'],
            'totalSalary' => $totals['totalSalary']
        ];
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
    <title>Payroll Report by Designation</title>
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

            const convert = (num) => {
                if (num < 20) return dictionary[num];
                if (num < 100) {
                    const tens = Math.floor(num / 10) * 10;
                    const units = num % 10;
                    return dictionary[tens] + (units ? `-${dictionary[units]}` : '');
                }
                if (num < 1000) {
                    const hundreds = Math.floor(num / 100);
                    const remainder = num % 100;
                    return dictionary[hundreds] + ' Hundred' + (remainder ? ` ${convert(remainder)}` : '');
                }
                for (let base of [10000000, 100000, 1000, 100]) {
                    if (num >= base) {
                        const quotient = Math.floor(num / base);
                        const remainder = num % base;
                        return convert(quotient) + ' ' + dictionary[base] + (remainder ? ` ${convert(remainder)}` : '');
                    }
                }
            };

            // Split the number into integer and fractional parts
            const [integerPart, fractionalPart] = number.toString().split('.').map(Number);
            
            let words = 'Taka ' + convert(integerPart); // Convert integer part

            // Handle the fractional part (Paisa)
            if (fractionalPart > 0) {
                words += ` and Paisa ${fractionalPart} Only`;
            } else {
                words += ' and Paisa Zero Only'; // If no fractional part, set Paisa to Zero
            }

            return words;
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Loop through each designation and apply the number to words function
            document.querySelectorAll('.net-pay-words').forEach(function(element) {
                const designationSum = element.getAttribute('data-total'); // Get the total salary from a custom data attribute
                const sum = parseFloat(designationSum.replace(/,/g, '')); // Convert to a float and check for commas
                console.log('Sum for designation: ', sum); // Check the sum in the console

                if (!isNaN(sum)) {
                    // Convert the total salary to words
                    element.textContent = convertNumberToWords(sum); // Set the text content with the result
                }
            });
        });
    </script>
</head>
<body class="bg-gray-100 px-8 text-black">
    <?php foreach ($designationData as $designation => $data): ?>
    <section class="page-container pt-5 relative h-screen flex flex-col gap-10">
        <section class="designation-section">
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
            <p class="text-gray-600 text-lg font-bold">Designation: <span class="font-normal"><?php echo $designation; ?></span></p>

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
                    <?php $sum = 0; ?>
                    <?php foreach ($data as $index => $row): ?>
                        <?php $sum += $row['totalSalary']; ?>
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
                </tbody>
            </table>

            <!-- Show Converted Words outside of the table -->
            <div class="mt-5">
                <p>In Words: <span class="net-pay-words font-bold" data-total="<?php echo number_format($sum, 2); ?>"></span></p>
            </div>
        </section>
        <section class="flex justify-between items-end gap-5 mt-10">
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
        </section>
        <section class="mt-10 text-lg border-t border-black py-5">
            <p>Web developed by: department of <span class="font-bold">Computer Science and Engineering, JUST</span> </p>
            <p>NB. If there is any error in your bill, immediately inform it to the Accounts Office of JUST</p>
        </section>
    </section>
    <?php endforeach; ?>
</body>
</html>
