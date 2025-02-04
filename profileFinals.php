<?php
// Include the database connection
include 'db_conn.php';

// Validate and fetch employee ID
$employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_SANITIZE_NUMBER_INT);

if ($employee_id) {
    // Get the current month and year
    $current_month = date('m');
    $current_year = date('Y');

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM payroll WHERE employee_id = ? AND `month` = ? AND `year` = ?");
    $stmt->bind_param("iii", $employee_id, $current_month, $current_year);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $payroll = $result->fetch_assoc();

            // Calculate grossPay
            $grossPay = array_sum([
                $payroll['basic'], $payroll['chargeAllw'], $payroll['telephoneAllwance'],
                $payroll['dearnessAllw'], $payroll['houseAllw'], $payroll['medicalAllw'],
                $payroll['educationAllw'], $payroll['festivalAllw'], $payroll['researchAllw'],
                $payroll['newBdYrAllw'], $payroll['recreationAllw'], $payroll['otherAllw']
            ]);

            // Calculate totalDeduction
            $totalDeduction = array_sum([
                $payroll['gpf'], $payroll['gpfInstallment'], $payroll['houseDed'], $payroll['benevolentFund'],
                $payroll['insurance'], $payroll['electricity'], $payroll['hrdExtra'],
                $payroll['clubSubscription'], $payroll['assoSubscription'], $payroll['transportBill'],
                $payroll['telephoneBill'], $payroll['pensionFund'], $payroll['fishBill'],
                $payroll['incomeTax'], $payroll['donation'], $payroll['guestHouseRent'],
                $payroll['houseLoanInstallment_1'], $payroll['houseLoanInstallment_2'],
                $payroll['houseLoanInstallment_3'], $payroll['salaryAdjustment'], $payroll['revenue'],
                $payroll['otherDed']
            ]);

            // Calculate netPay
            $netPay = $grossPay - $totalDeduction;

        } else {
            $error_message = "No payroll data found for the specified employee in the current month and year.";
        }
    } else {
        $error_message = "Failed to execute the query. Please try again.";
    }

    $stmt->close();
} else {
    $error_message = "Invalid or missing employee ID.";
}

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
    <!-- <link rel="stylesheet" href="sideBar.css"> -->
</head>
<body class="bg-blue-50 flex">
    <main class="flex flex-col flex-grow  bg-white px-8 pb-20 mb-20">
         <aside class="pt-8">
            <section class="flex items-center relative">
                <figure class="w-auto pl-32 absolute">
                    <img src="uploads/justt.png" alt="" class="w-28">
                </figure>
                <div class="flex-grow flex justify-center items-center text-2xl font-bold text-center">
                    <p>Jashore University of Science and Technology <br> Jashore - 7408 <br> Salary Bill</p>
                </div>
            </section>
            
            <section class="mt-6">
                <hr class="border border-black ml-8">
                <p class="ml-10 my-2 text-lg font-bold">Current Month: <?php echo date('F, Y'); ?></p>
                <hr class="border border-black">
            </section>
        </aside>

        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php else: ?>
        <section class="bg-white flex justify-between px-10 mt-5">
            <aside class="flex justify-center items-end gap-3 flex-1">
                <div class="text-right">
                    <p class="text-xl font-semibold">Name:</p>
                    <p class="text-lg">Designation:</p>
                    <p class="text-lg">Department:</p>
                    <p class="text-lg">SB. A/C No:</p>
                    <p class="text-lg">Grade & Scale:</p>
                </div>
                <div>
                    <p class="text-lg font-semibold"><?php echo htmlspecialchars($payroll['name']); ?></p>
                    <p class="text-lg"><?php echo htmlspecialchars($payroll['designation']); ?></p>
                    <p class="text-lg"><?php echo htmlspecialchars($payroll['departments']); ?></p>
                    <p class="text-lg"><?php echo htmlspecialchars($payroll['account_number']); ?></p>
                    <p class="text-lg"><?php echo htmlspecialchars($payroll['grade']); ?> (BDT <?php echo htmlspecialchars($payroll['scale']); ?>)</p>
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
                    <p class="text-lg">01-Jul-2024</p>
                    <p class="text-lg"><?php echo htmlspecialchars($payroll['e_tin']); ?></p>
                </div>
            </aside>
        </section>
        <section class="grid grid-cols-2 gap-56">
            <aside class="flex flex-col justify-between h-full">
                <section class="h-full flex flex-col">
                    <p class="border border-black py-2 w-full text-center text-lg font-bold">Pay & Allowance</p>
                    <article class="flex justify-between px-10 h-full">
                        <div class="text-right relative">
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
                            <p class="absolute bottom-0 right-0">Gross Pay:</p>
                        </div>
                        <div class="text-right relative">
                            <p><?php echo htmlspecialchars($payroll['increment']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['basic']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['dearnessAllw']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['houseAllw']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['medicalAllw']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['educationAllw']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['chargeAllw']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['telephoneAllwance']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['festivalAllw']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['researchAllw']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['newBdYrAllw']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['recreationAllw']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['otherAllw']); ?></p>
                            <p class="absolute bottom-0 right-0"><?php echo number_format($grossPay, 2); ?></p>
                        </div>
                    </article>
                </section>
                <article>
                    <aside class="flex justify-between gap-5 border-y border-black pt-2 pb-3">
                        <div class="text-right">
                            <p class="whitespace-nowrap">Net Pay (BDT):</p>
                            <p>In Word:</p>
                        </div>
                        <div>
                            <p id="net-pay"><?php echo number_format($netPay, 2); ?></p>
                            <p id="net-pay-words"></p>
                        </div>
                    </aside>
                    <div>
                        <p>Declaration: The amount claimed in this bill is correct and has not been drawn earlier</p>
                    </div>
                </article>
            </aside>
            <aside class="h-full">
                <p class="border border-black py-2 w-full text-center text-lg font-bold">Deductions</p>
                <article class="flex justify-between px-10">
                    <div class="text-right">
                        <p>GPF:</p>
                        <p>GPF Installment: 0 / 0:</p>
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
                        <p>Home Loan Installment1: 0 / 0:</p>
                        <p>Home Loan Installment2: 0 / 0:</p>
                        <p>Home Loan Installment3: 0 / 0:</p>
                        <p>Salary Adjustment:</p>
                        <p>Others:</p>
                        <p>Revenue:</p>
                        <p>Total Deduction:</p>
                    </div>
                    <div class="text-right">
                        <p><?php echo htmlspecialchars($payroll['gpf']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['gpfInstallment']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['houseDed']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['benevolentFund']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['insurance']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['electricity']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['hrdExtra']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['clubSubscription']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['assoSubscription']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['transportBill']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['telephoneBill']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['pensionFund']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['fishBill']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['incomeTax']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['donation']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['guestHouseRent']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['houseLoanInstallment_1']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['houseLoanInstallment_2']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['houseLoanInstallment_3']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['salaryAdjustment']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['revenue']); ?></p>
                        <p><?php echo htmlspecialchars($payroll['otherDed']); ?></p>
                        <p class="font-bold"><?php echo number_format($totalDeduction, 2); ?></p>
                    </div>
                </article>
            </aside>
        </section>
        <section class="flex justify-between items-end gap-20 mt-5">
            <aside class="flex-1 flex flex-col gap-4">
                <p class="flex justify-end w-full"> <span class="border border-black py-6 px-3 w-max tracking-widest">STAMP</span></p>
                <p class="text-center border-t border-black border-dashed">Incumbent's Signature</p>
            </aside>
            <aside class="flex-1">
                <p class="text-center border-t border-black border-dashed">Prepared By</p>
            </aside>
            <aside class="flex-1 flex flex-col gap-10">
                <p class="text-center">Remarks</p>
                <p class="text-center border-t border-black border-dashed">Assistant Director</p>
            </aside>
            <aside class="flex-1">
                <p class="text-center border-t border-black border-dashed">Deputy Director</p>
            </aside>
            <aside class="flex-1">
                <p class="text-center border-t border-black border-dashed">Director</p>
            </aside>
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

        // Update net pay in words dynamically
        document.addEventListener('DOMContentLoaded', () => {
            const netPayText = document.getElementById('net-pay').textContent;
            const netPayAmount = parseFloat(netPayText.replace(/,/g, '')); // Convert to float, handle commas
            const netPayWords = convertNumberToWords(netPayAmount);
            document.getElementById('net-pay-words').textContent = netPayWords;
        });
    </script>
</body>
</html>