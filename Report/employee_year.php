<?php
session_start();

// Check if the user is logged in and has HR or Admin role
if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2)) {
    header('Location: ../dashboard.php');
    exit();
}

// Include the database connection
include '../db_conn.php';

// Get the user-selected start/end month/year (or default to current)
$start_month = isset($_GET['start_month']) ? (int)$_GET['start_month'] : date('m');
$start_year  = isset($_GET['start_year'])  ? (int)$_GET['start_year']  : date('Y');
$end_month   = isset($_GET['end_month'])   ? (int)$_GET['end_month']   : date('m');
$end_year    = isset($_GET['end_year'])    ? (int)$_GET['end_year']    : date('Y');

$employees = [];

// Function to format month and year
function formatMonthYear($month, $year) {
    return date('F, Y', strtotime("$year-$month-01"));
}

// Loop through the months and fetch payroll data
$current_year  = $start_year;
$current_month = $start_month;

while (($current_year < $end_year) || ($current_year == $end_year && $current_month <= $end_month)) {
    // Fetch payroll data for the current month and year
    $sql = "SELECT * FROM payroll WHERE month = ? AND year = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $current_month, $current_year);
    $stmt->execute();
    $result = $stmt->get_result();

    // Process each row of the result
    while ($row = $result->fetch_assoc()) {
        $employeeId = $row['employee_id'];

        // Initialize employee if not already present in the array
        if (!isset($employees[$employeeId])) {
            $employees[$employeeId] = [
                'name'        => $row['name'],
                'departments' => $row['departments'],
                'payroll'     => [],
                'totalSum'    => 0 // Initialize the total sum for the employee
            ];
        }

        // Calculate total allowances and deductions for this row
        $allowances = array_sum([ 
            $row['chargeAllw'], $row['telephoneAllwance'], $row['dearnessAllw'],
            $row['houseAllw'], $row['medicalAllw'], $row['educationAllw'],
            $row['festivalAllw'], $row['researchAllw'], $row['newBdYrAllw'],
            $row['recreationAllw'], $row['otherAllw']
        ]);

        $deductions = array_sum([ 
            $row['gpf'], $row['gpfInstallment'], $row['houseDed'], $row['benevolentFund'],
            $row['insurance'], $row['electricity'], $row['hrdExtra'], $row['clubSubscription'],
            $row['assoSubscription'], $row['transportBill'], $row['telephoneBill'],
            $row['pensionFund'], $row['fishBill'], $row['incomeTax'], $row['donation'],
            $row['guestHouseRent'], $row['houseLoanInstallment_1'], $row['houseLoanInstallment_2'],
            $row['houseLoanInstallment_3'], $row['salaryAdjustment'], $row['revenue'],
            $row['otherDed']
        ]);

        $netSalary = $row['basic'] + $allowances - $deductions;

        // Add to the total sum of net salary for the employee
        $employees[$employeeId]['totalSum'] += $netSalary;

        // Store this data under the corresponding employee for this month
        $employees[$employeeId]['payroll'][] = [
            'month'        => $current_month,
            'year'         => $current_year,
            'grade'        => $row['grade'],
            'designation'  => $row['designation'],
            'basic'        => $row['basic'],
            'allowances'   => $allowances,
            'deductions'   => $deductions,
            'netSalary'    => $netSalary
        ];
    }

    $stmt->close();

    // Move to the next month
    if ($current_month == 12) {
        $current_month = 1;
        $current_year++;
    } else {
        $current_month++;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
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
<body class="bg-blue-50 px-8 text-black">
<?php if (empty($employees)): ?>
  <p class="text-red-500">No payroll data found for the specified date range.</p>
<?php else: ?>
  <?php foreach ($employees as $employeeId => $empData): ?>
    <section class="page-container pt-5 relative h-screen flex flex-col gap-10">
      <aside>
        <div class="flex justify-between items-center border-b pb-6 mb-6">
          <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-extrabold text-blue-900">
              Jashore University of Science and Technology, 
              <span class="font-semibold text-black">Jashore - 7408</span>
            </h1>
            <p class="text-gray-600 text-lg">
              Payroll Period:
              <span class="font-semibold">
                <?php echo formatMonthYear($start_month, $start_year); ?>
                to
                <?php echo formatMonthYear($end_month, $end_year); ?>
              </span>
            </p>
            <p class="text-gray-600 text-lg">
              Date: <span class="font-semibold"><?php echo date('F d, Y'); ?></span>
            </p>
          </div>
          <div>
            <img src="../uploads/JUSTt.png" alt="University Logo" class="h-20">
          </div>
        </div>

        <!-- Employee Info -->
        <div class="mb-4">
          <p><strong>Name:</strong> <?php echo htmlspecialchars($empData['name']); ?></p>
          <p><strong>Department:</strong> <?php echo htmlspecialchars($empData['departments']); ?></p>
        </div>

        <!-- Table: Payroll Data -->
        <table class="table-auto w-full border border-gray-300">
          <thead class="bg-gray-200">
            <tr>
              <th class="p-2 border">SL</th>
              <th class="p-2 border">Month</th>
              <th class="p-2 border">Year</th>
              <th class="p-2 border">Grade</th>
              <th class="p-2 border">Designation</th>
              <th class="p-2 border">Basic</th>
              <th class="p-2 border">Allowances</th>
              <th class="p-2 border">Deductions</th>
              <th class="p-2 border">Net Salary</th>
            </tr>
          </thead>
          <tbody>
            <?php $sl = 1; foreach ($empData['payroll'] as $payroll): ?>
              <tr>
                <td class="p-2 border text-center"><?php echo $sl++; ?></td>
                <td class="p-2 border text-center"><?php echo date('F', mktime(0, 0, 0, $payroll['month'], 1)); ?></td>
                <td class="p-2 border text-center"><?php echo $payroll['year']; ?></td>
                <td class="p-2 border text-center"><?php echo $payroll['grade']; ?></td>
                <td class="p-2 border text-center"><?php echo $payroll['designation']; ?></td>
                <td class="p-2 border text-right"><?php echo number_format($payroll['basic'], 2); ?></td>
                <td class="p-2 border text-right"><?php echo number_format($payroll['allowances'], 2); ?></td>
                <td class="p-2 border text-right"><?php echo number_format($payroll['deductions'], 2); ?></td>
                <td class="p-2 border text-right font-bold"><?php echo number_format($payroll['netSalary'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
            <tr class="bg-gray-200 font-bold">
                  <td colspan="8" class="py-3 text-right">Total Salary from <span class="font-bold"><?php echo date('F, Y', strtotime("{$start_year}-{$start_month}-01")); ?></span> to <span class="font-bold"><?php echo date('F, Y', strtotime("{$end_year}-{$end_month}-01")); ?></span>:</td>
                  <td class="p-3 text-right"><?php echo number_format($empData['totalSum'], 2); ?></td>
              </tr>
          </tbody>
        </table>
        <p class="mt-5">In Words: <span class="net-pay-words text-lg font-semibold"></span></p>
      </aside>

      <section class="w-full">
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
  <?php endforeach; ?>
<?php endif; ?>

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
        // Loop through each employee and convert their total salary to words
        const netPayWordsElements = document.querySelectorAll('.net-pay-words');
        
        netPayWordsElements.forEach(function(element, index) {
            // Get the total sum for the current employee from PHP
            const totalSum = <?php echo json_encode(array_column($employees, 'totalSum')); ?>[index];
            
            // Convert net pay to words and update the element
            element.textContent = convertNumberToWords(totalSum);
        });
    });
    </script>
</body>
</html>
