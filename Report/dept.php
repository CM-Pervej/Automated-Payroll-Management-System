<?php
// Include the database connection
include '../db_conn.php';

// Get the passed month and year from the previous page
$start_month = isset($_GET['start_month']) ? $_GET['start_month'] : date('m'); // Default to current month if not passed
$start_year = isset($_GET['start_year']) ? $_GET['start_year'] : date('Y'); // Default to current year if not passed
$end_month = isset($_GET['end_month']) ? $_GET['end_month'] : date('m'); // Default to current month if not passed
$end_year = isset($_GET['end_year']) ? $_GET['end_year'] : date('Y'); // Default to current year if not passed

// Prepare the SQL statement to fetch payroll data for all employees within the selected date range
$employees = [];
$current_year = $start_year;
$current_month = $start_month;

// Loop through each month in the range from start to end
while (($current_year < $end_year) || ($current_year == $end_year && $current_month <= $end_month)) {
    // Prepare the SQL statement to fetch payroll data for the current month
    $query = "SELECT * FROM payroll WHERE 
                (year = ? AND month = ?)";

    // Prepare the statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $current_year, $current_month);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Check if the employee already exists in the employees array
                $employee_id = $row['employee_id'];
                if (!isset($employees[$employee_id])) {
                    $employees[$employee_id] = [
                        'name' => $row['name'],
                        'designation' => $row['designation'],
                        'departments' => $row['departments'],
                        'grade' => $row['grade'],
                        'increment' => $row['increment'],
                        'scale' => $row['scale'],
                        'account_number' => $row['account_number'],
                        'e_tin' => $row['e_tin'],

                        'totalGrossPay' => 0,
                        'totalDeduction' => 0,
                        'totalNetPay' => 0,

                        'basic' => 0, 'chargeAllw' => 0, 'telephoneAllwance' => 0, 'dearnessAllw' => 0, 'houseAllw' => 0, 'medicalAllw' => 0, 'educationAllw' => 0, 'festivalAllw' => 0, 'researchAllw' => 0, 'newBdYrAllw' => 0, 'recreationAllw' => 0, 'otherAllw' => 0,
                        'gpf' => 0, 'gpfInstallment' => 0, 'houseDed' => 0, 'benevolentFund' => 0, 'insurance' => 0, 'electricity' => 0, 'hrdExtra' => 0, 'clubSubscription' => 0, 'assoSubscription' => 0, 'transportBill' => 0, 'telephoneBill' => 0, 'pensionFund' => 0, 'fishBill' => 0, 'incomeTax' => 0, 'donation' => 0, 'guestHouseRent' => 0, 'houseLoanInstallment_1' => 0, 'houseLoanInstallment_2' => 0, 'houseLoanInstallment_3' => 0, 'salaryAdjustment' => 0, 'revenue' => 0, 'otherDed' => 0
                    ];
                }

                // Fetch and accumulate the individual column values
                $employees[$employee_id]['basic'] += $row['basic'];
                $employees[$employee_id]['chargeAllw'] += $row['chargeAllw'];
                $employees[$employee_id]['telephoneAllwance'] += $row['telephoneAllwance'];
                $employees[$employee_id]['dearnessAllw'] += $row['dearnessAllw'];
                $employees[$employee_id]['houseAllw'] += $row['houseAllw'];
                $employees[$employee_id]['medicalAllw'] += $row['medicalAllw'];
                $employees[$employee_id]['educationAllw'] += $row['educationAllw'];
                $employees[$employee_id]['festivalAllw'] += $row['festivalAllw'];
                $employees[$employee_id]['researchAllw'] += $row['researchAllw'];
                $employees[$employee_id]['newBdYrAllw'] += $row['newBdYrAllw'];
                $employees[$employee_id]['recreationAllw'] += $row['recreationAllw'];
                $employees[$employee_id]['otherAllw'] += $row['otherAllw'];

                $employees[$employee_id]['gpf'] += $row['gpf'];

                // Calculate grossPay
                $grossPay = array_sum([
                    $row['basic'], $row['chargeAllw'], $row['telephoneAllwance'],
                    $row['dearnessAllw'], $row['houseAllw'], $row['medicalAllw'],
                    $row['educationAllw'], $row['festivalAllw'], $row['researchAllw'],
                    $row['newBdYrAllw'], $row['recreationAllw'], $row['otherAllw']
                ]);

                // Calculate totalDeduction
                $totalDeductionValue = array_sum([
                    $row['gpf'], $row['gpfInstallment'], $row['houseDed'], $row['benevolentFund'],
                    $row['insurance'], $row['electricity'], $row['hrdExtra'],
                    $row['clubSubscription'], $row['assoSubscription'], $row['transportBill'],
                    $row['telephoneBill'], $row['pensionFund'], $row['fishBill'],
                    $row['incomeTax'], $row['donation'], $row['guestHouseRent'],
                    $row['houseLoanInstallment_1'], $row['houseLoanInstallment_2'],
                    $row['houseLoanInstallment_3'], $row['salaryAdjustment'], $row['revenue'],
                    $row['otherDed']
                ]);

                // Calculate netPay
                $netPay = $grossPay - $totalDeductionValue;

                // Accumulate the values for this employee
                $employees[$employee_id]['totalGrossPay'] += $grossPay;
                $employees[$employee_id]['totalDeduction'] += $totalDeductionValue;
                $employees[$employee_id]['totalNetPay'] += $netPay;
            }
        }
    } else {
        $error_message = "Failed to execute the query for month: {$current_month}/{$current_year}.";
        break;
    }

    // Move to the next month
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

<!-- HTML Section -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
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
<body class="bg-blue-50 flex">
    <main class="flex flex-col flex-grow bg-white px-8 pb-20 mb-20">
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php else: ?>
        <!-- Display summary of the total payroll data -->
        <?php foreach ($employees as $employee_id => $data): ?>
        <section class="page-container">
        <aside class="pt-16">
                <section class="flex items-center relative">
                    <figure class="w-auto pl-10 absolute">
                        <img src="../uploads/justt.png" alt="" class="w-24">
                    </figure>
                    <div class="flex-grow flex justify-center items-center text-2xl font-bold text-center">
                        <p>Jashore University of Science and Technology <br> Jashore - 7408 <br> Salary Bill</p>
                    </div>
                </section>
                <section class="mt-6">
                    <hr class="border border-black ml-8">
                    <p class="ml-10 my-2 text-lg">Payroll Summary from <span class="font-bold"> <?php echo date('F, Y', strtotime("{$start_year}-{$start_month}-01")); ?> </span> to <span class="font-bold"> <?php echo date('F, Y', strtotime("{$end_year}-{$end_month}-01")); ?><span></span></p>
                    <hr class="border border-black">
                </section>
            </aside>
            <section class="bg-white flex justify-between my-3">
                <aside class="flex justify-center items-end gap-3 flex-1">
                    <div class="text-right">
                        <p class="text-xl font-semibold">Name:</p>
                        <p class="text-lg">Designation:</p>
                        <p class="text-lg">Department:</p>
                        <p class="text-lg">SB. A/C No:</p>
                        <p class="text-lg">Grade & Scale:</p>
                    </div>
                    <div>
                        <p class="text-xl font-semibold"><?php echo $data['name']; ?></p>
                        <p class="text-lg"><?php echo $data['designation']; ?></p>
                        <p class="text-lg"><?php echo $data['departments']; ?></p>
                        <p class="text-lg"><?php echo $data['account_number']; ?></p>
                        <p class="text-lg"><?php echo number_format($data['grade'], 2); ?> (BDT <?php echo number_format($data['scale'], 2); ?>)</p>
                    </div>
                </aside>
                <aside class="flex justify-center items-end gap-3 flex-1">
                    <div class="text-right">
                        <p class="text-lg">Date of Joining:</p>
                        <p class="text-lg">Date of Increment:</p>
                        <p class="text-lg">E-TIN:</p>
                    </div>
                    <div>
                        <p class="text-lg">02-Jul-2018</p>
                        <p class="text-lg">..</p>
                        <p class="text-lg"><?php echo $data['e_tin']; ?></p>
                    </div>
                </aside>
            </section>

            <section class="grid grid-cols-2 gap-20">
                <aside class="flex flex-col justify-between h-full">
                    <section class="h-full flex flex-col">
                        <p class="border border-black py-2 w-full text-center text-2xl font-bold">Pay & Allowance</p>
                        <article class="flex justify-between px-10 h-full mt-2">
                            <div class="text-right relative text-lg">
                                <p>No. of Increments:</p>
                                <p>Basic Pay:</p>
                                <p>Dearness/Special Allw:</p>
                                <p>House Rent Allowance:</p>
                                <p>Medical Allowance:</p>
                                <p>Education Allowance:</p>
                                <p>Charge Allowance:</p>
                                <p>Telephone Allowance:</p>
                                <p>Festival Bonus:</p>
                                <p>Research Allowance:</p>
                                <p>New Bangla Yr. Bonus:</p>
                                <p>Recreation Allowance:</p>
                                <p>Others:</p>
                                <p class="absolute bottom-0 right-0 font-semibold">Gross Pay:</p>
                            </div>
                            <div class="text-right relative text-lg">
                                <p><?php echo number_format($data['increment'], 2); ?></p>
                                <p><?php echo number_format($data['basic'], 2); ?></p>
                                <p><?php echo number_format($data['dearnessAllw'], 2); ?></p>
                                <p><?php echo number_format($data['houseAllw'], 2); ?></p>
                                <p><?php echo number_format($data['medicalAllw'], 2); ?></p>
                                <p><?php echo number_format($data['educationAllw'], 2); ?></p>
                                <p><?php echo number_format($data['chargeAllw'], 2); ?></p>
                                <p><?php echo number_format($data['telephoneAllwance'], 2); ?></p>
                                <p><?php echo number_format($data['festivalAllw'], 2); ?></p>
                                <p><?php echo number_format($data['researchAllw'], 2); ?></p>
                                <p><?php echo number_format($data['newBdYrAllw'], 2); ?></p>
                                <p><?php echo number_format($data['recreationAllw'], 2); ?></p>
                                <p><?php echo number_format($data['otherAllw'], 2); ?></p>
                                <p class="absolute bottom-0 right-0 font-semibold"><?php echo number_format($data['totalGrossPay'], 2); ?></p>
                            </div>
                        </article>
                    </section>
                    <article>
                        <aside class="flex justify-between gap-5 border-y border-black pt-2 pb-3">
                            <div class="text-right text-lg">
                                <p class="whitespace-nowrap font-semibold">Net Pay (BDT):</p>
                                <p class="font-semibold">In Word:</p>
                            </div>
                            <div>
                                <p id="net-pay" class="font-semibold text-lg"><?php echo number_format($data['totalNetPay'], 2); ?></p>
                                <p class="net-pay-words text-lg font-semibold"></p> <!-- Change id to class -->
                            </div>
                        </aside>
                        <div>
                            <p class="font-semibold text-lg">Declaration: The amount claimed in this bill is correct and has not been drawn earlier</p>
                        </div>
                    </article>
                </aside>
                <aside class="h-full">
                    <p class="border border-black py-2 w-full text-center text-2xl font-bold">Deductions</p>
                    <article class="flex justify-between mt-2 px-10">
                        <div class="text-right text-lg">
                            <p>GPF:</p>
                            <p>GPF Installment:</p>
                            <p>House Rent Deduction:</p>
                            <p>Benevolent Fund:</p>
                            <p>Insurance Premium:</p>
                            <p>Electricity Bill:</p>
                            <p>HRD Extra:</p>
                            <p>Club Subscription:</p>
                            <p>Association Subscription:</p>
                            <p>Transport Bill:</p>
                            <p>Telephone Bill:</p>
                            <p>Pension Fund:</p>
                            <p>Fish Bill:</p>
                            <p>Income Tax:</p>
                            <p>Donation:</p>
                            <p>Guest House Rent:</p>
                            <p>House Loan Installment 1:</p>
                            <p>House Loan Installment 2:</p>
                            <p>House Loan Installment 3:</p>
                            <p>Salary Adjustment:</p>
                            <p>Others:</p>
                            <p>Revenue:</p>
                            <p class="font-semibold">Total Deductions:</p>
                        </div>
                        <div class="text-right text-lg">
                            <p><?php echo number_format($data['gpf'], 2); ?></p>
                            <p><?php echo number_format($data['gpfInstallment'], 2); ?></p>
                            <p><?php echo number_format($data['houseDed'], 2); ?></p>
                            <p><?php echo number_format($data['benevolentFund'], 2); ?></p>
                            <p><?php echo number_format($data['insurance'], 2); ?></p>
                            <p><?php echo number_format($data['electricity'], 2); ?></p>
                            <p><?php echo number_format($data['hrdExtra'], 2); ?></p>
                            <p><?php echo number_format($data['clubSubscription'], 2); ?></p>
                            <p><?php echo number_format($data['assoSubscription'], 2); ?></p>
                            <p><?php echo number_format($data['transportBill'], 2); ?></p>
                            <p><?php echo number_format($data['telephoneBill'], 2); ?></p>
                            <p><?php echo number_format($data['pensionFund'], 2); ?></p>
                            <p><?php echo number_format($data['fishBill'], 2); ?></p>
                            <p><?php echo number_format($data['incomeTax'], 2); ?></p>
                            <p><?php echo number_format($data['donation'], 2); ?></p>
                            <p><?php echo number_format($data['guestHouseRent'], 2); ?></p>
                            <p><?php echo number_format($data['houseLoanInstallment_1'], 2); ?></p>
                            <p><?php echo number_format($data['houseLoanInstallment_2'], 2); ?></p>
                            <p><?php echo number_format($data['houseLoanInstallment_3'], 2); ?></p>
                            <p><?php echo number_format($data['salaryAdjustment'], 2); ?></p>
                            <p><?php echo number_format($data['otherDed'], 2); ?></p>
                            <p><?php echo number_format($data['revenue'], 2); ?></p>
                            <p class="font-semibold"><?php echo number_format($data['totalDeduction'], 2); ?></p>
                        </div>
                    </article>
                </aside>
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
            <section class="mt-24 text-lg border-t border-black py-5">
                <p>Web developed by: department of <span class="font-bold">Computer Science and Engineering, JUST</span> </p>
                <p>NB. If there is any error in your bill, immediately inform it to the Accounts Office of JUST</p>
            </section>
        </section>
        <?php endforeach; ?>
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
        // Get all elements with the class 'net-pay-words'
        const netPayWordsElements = document.querySelectorAll('.net-pay-words');
        
        netPayWordsElements.forEach(function(element, index) {
            // Get the net pay for the current employee (from the corresponding row)
            const totalNetPay = <?php echo json_encode(array_column($employees, 'totalNetPay')); ?>[index];
            
            // Convert net pay to words and update the element
            element.textContent = convertNumberToWords(totalNetPay);
        });
    });
    </script>
</body>
</html>
