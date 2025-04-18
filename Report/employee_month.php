<?php
session_start();

// Check if the user is logged in and has the HR role
if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2)) {
    header('Location: ../dashboard.php'); // Redirect to dashboard if not HR or Admin
    exit();
}

// Include the database connection
include '../db_conn.php';

// Get the passed month and year from the previous page
$passed_month = isset($_GET['month']) ? $_GET['month'] : date('m'); // Default to current month if not passed
$passed_year = isset($_GET['year']) ? $_GET['year'] : date('Y'); // Default to current year if not passed

// Prepare the SQL statement to fetch payroll data for all employees in the passed month and year
$stmt = $conn->prepare("SELECT * FROM payroll WHERE `month` = ? AND `year` = ?");
$stmt->bind_param("ii", $passed_month, $passed_year);

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            // Calculate grossPay
            $grossPay = array_sum([
                $row['basic'], $row['chargeAllw'], $row['telephoneAllwance'],
                $row['dearnessAllw'], $row['houseAllw'], $row['medicalAllw'],
                $row['educationAllw'], $row['festivalAllw'], $row['researchAllw'],
                $row['newBdYrAllw'], $row['recreationAllw'], $row['otherAllw']
            ]);

            // Calculate totalDeduction
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

            // Calculate netPay
            $netPay = $grossPay - $totalDeduction;

            // Store employee payroll data
            $row['grossPay'] = $grossPay;
            $row['totalDeduction'] = $totalDeduction;
            $row['netPay'] = $netPay;

            $employees[] = $row;
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
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
         <section class="mt-5 mb-36">
            <article class="flex flex-col justify-between items-center py-3 border-b-4 border-black">
                <figure>
                    <img src="../uploads/justt.png" alt="" id="generatePDF" class="w-28">
                </figure>
                <p class="text-2xl font-bold text-center">Office of the Director of Accounts</p>
                <div class="flex flex-col items-center">
                    <p class="text-2xl font-semibold tracking-tighter text-center">Jashore University of Science and Technology</p>
                    <p class="text-xl font-bold">Jashore - 7408</p>
                    <p class="text-lg font-bold">Phone: 01982-525570</p>
                </div>
            </article>
            <div class="flex justify-between mt-5 text-lg font-semibold">
                <p>Memo No. JUST/Accounts/</p>
                <p class="mr-10">Date .............................................</p>
            </div>
            <div class="px-16 py-20 mt-10">
                <p class="border-t-2 border-dashed border-black w-max p-2 font-semibold">যশোর বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয়</p>
            </div>
            <article class="px-14 text-xl">
                <p class="font-bold mb-20">বিষয়ঃ <?php echo date('F, Y', strtotime("{$passed_year}-{$passed_month}-01")); ?> মাসের বেতন ও ভাতার বিল প্রস্তুত প্রসঙ্গে।</p>
                <p class="leading-loose text-justify">
                    উপর্যুক্ত বিষয়ের আলোকে আপনার বিভাগ/দপ্তরের শিক্ষক/কর্মকর্তাদের <?php echo date('F, Y', strtotime("{$passed_year}-{$passed_month}-01")); ?> মাসের বেতন ও ভাতার বিল (............) টি অন্ত্যন্ত সহজভাবে প্রস্তুত করা হলো। 
                    বিল গুলিতে স্বাক্ষর দিয়ে অনুমোদন হিসাবে দপ্তরে প্রেরণের জন্য এবং কোন প্রকার ভুল পরিলক্ষিত হলে সেটিও সংশোধন করে জমা দেওয়ার জন্য অনুরোধ করা হলো। 
                    যে সকল শিক্ষক ও কর্মকর্তাগণ তাদের টি.আই.এন হিসাব দপ্তরে প্রদান করেননি, তাদেরকে চলতি মাসের বেতন ও ভাতার বিলের সাথে টি.আই.এন এর কপি জমা দেওয়ার জন্য অনুরোধ করা হলো।
                </p>
            </article>
         </section>

      
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php else: ?>
            <!-- Loop through all employees and display their data -->
            <?php foreach ($employees as $payroll): ?>
        <section class="page-container">
            <aside class="pt-16">
                <section class="flex items-center relative">
                    <figure class="w-auto pl-10 absolute">
                        <img src="../uploads/justt.png" alt="" class="w-24">
                    </figure>
                    <div class="flex-grow flex justify-center items-center text-3xl font-bold text-center">
                        <p>Jashore University of Science and Technology <br> Jashore - 7408 <br> Salary Bill</p>
                    </div>
                </section>
                
                <section class="mt-6">
                    <hr class="border border-black ml-8">
                    <p class="ml-10 my-2 text-xl font-bold">Current Month: <?php echo date('F, Y', strtotime("{$passed_year}-{$passed_month}-01")); ?></p>
                    <hr class="border border-black">
                </section>
            </aside>
                <section class="bg-white flex justify-between my-3">
                    <aside class="flex justify-center items-end gap-3 flex-1">
                        <div class="text-right text-xl">
                            <p class="text-2xl font-semibold">Name:</p>
                            <p>Designation:</p>
                            <p>Department:</p>
                            <p>SB. A/C No:</p>
                            <p>Grade & Scale:</p>
                        </div>
                        <div class="text-xl">
                            <p class="text-2xl font-semibold"><?php echo htmlspecialchars($payroll['name']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['designation']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['departments']); ?></p>
                            <p><?php echo htmlspecialchars($payroll['account_number']); ?></p>
                            <p><?php echo number_format($payroll['grade'], 2); ?> (BDT <?php echo number_format($payroll['scale'], 2); ?>)</p>
                        </div>
                    </aside>
                    <aside class="flex justify-center items-end gap-3 flex-1">
                        <div class="text-right text-xl">
                            <p>Date of Joining:</p>
                            <p>Date of Increment:</p>
                            <p>E-TIN:</p>
                        </div>
                        <div class="text-xl">
                            <p>02-Jul-2018</p>
                            <p>..</p>
                            <p><?php echo htmlspecialchars($payroll['e_tin']); ?></p>
                        </div>
                    </aside>
                </section>
                <section class="grid grid-cols-2 gap-20">
                    <aside class="flex flex-col justify-between h-full">
                        <section class="h-full flex flex-col">
                            <p class="border border-black py-2 w-full text-center text-2xl font-bold">Pay & Allowance</p>
                            <article class="flex justify-between px-10 h-full mt-2">
                                <div class="text-right relative text-xl">
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
                                <div class="text-right relative text-xl">
                                    <p><?php echo number_format($payroll['increment'], 2); ?></p>
                                    <p><?php echo number_format($payroll['basic'], 2); ?></p>
                                    <p><?php echo number_format($payroll['dearnessAllw'], 2); ?></p>
                                    <p><?php echo number_format($payroll['houseAllw'], 2); ?></p>
                                    <p><?php echo number_format($payroll['medicalAllw'], 2); ?></p>
                                    <p><?php echo number_format($payroll['educationAllw'], 2); ?></p>
                                    <p><?php echo number_format($payroll['chargeAllw'], 2); ?></p>
                                    <p><?php echo number_format($payroll['telephoneAllwance'], 2); ?></p>
                                    <p><?php echo number_format($payroll['festivalAllw'], 2); ?></p>
                                    <p><?php echo number_format($payroll['researchAllw'], 2); ?></p>
                                    <p><?php echo number_format($payroll['newBdYrAllw'], 2); ?></p>
                                    <p><?php echo number_format($payroll['recreationAllw'], 2); ?></p>
                                    <p><?php echo number_format($payroll['otherAllw'], 2); ?></p>
                                    <p class="absolute bottom-0 right-0 font-semibold"><?php echo number_format($payroll['grossPay'], 2); ?></p>
                                </div>
                            </article>
                        </section>
                        <article>
                            <aside class="flex justify-between gap-5 border-y border-black pt-2 pb-3">
                                <div class="text-right text-xl">
                                    <p class="whitespace-nowrap font-semibold">Net Pay (BDT):</p>
                                    <p class="font-semibold">In Word:</p>
                                </div>
                                <div>
                                    <p id="net-pay" class="font-semibold text-xl"><?php echo number_format($payroll['netPay'], 2); ?></p>
                                    <p class="net-pay-words text-lg font-semibold"></p> <!-- Change id to class -->
                                </div>
                            </aside>
                            <div>
                                <p class="font-semibold text-xl">Declaration: The amount claimed in this bill is correct and has not been drawn earlier</p>
                            </div>
                        </article>
                    </aside>
                    <aside class="h-full">
                        <p class="border border-black py-2 w-full text-center text-2xl font-bold">Deductions</p>
                        <article class="flex justify-between mt-2 px-10">
                            <div class="text-right text-xl">
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
                            <div class="text-right text-xl">
                                <p><?php echo number_format($payroll['gpf'], 2); ?></p>
                                <p><?php echo number_format($payroll['gpfInstallment'], 2); ?></p>
                                <p><?php echo number_format($payroll['houseDed'], 2); ?></p>
                                <p><?php echo number_format($payroll['benevolentFund'], 2); ?></p>
                                <p><?php echo number_format($payroll['insurance'], 2); ?></p>
                                <p><?php echo number_format($payroll['electricity'], 2); ?></p>
                                <p><?php echo number_format($payroll['hrdExtra'], 2); ?></p>
                                <p><?php echo number_format($payroll['clubSubscription'], 2); ?></p>
                                <p><?php echo number_format($payroll['assoSubscription'], 2); ?></p>
                                <p><?php echo number_format($payroll['transportBill'], 2); ?></p>
                                <p><?php echo number_format($payroll['telephoneBill'], 2); ?></p>
                                <p><?php echo number_format($payroll['pensionFund'], 2); ?></p>
                                <p><?php echo number_format($payroll['fishBill'], 2); ?></p>
                                <p><?php echo number_format($payroll['incomeTax'], 2); ?></p>
                                <p><?php echo number_format($payroll['donation'], 2); ?></p>
                                <p><?php echo number_format($payroll['guestHouseRent'], 2); ?></p>
                                <p><?php echo number_format($payroll['houseLoanInstallment_1'], 2); ?></p>
                                <p><?php echo number_format($payroll['houseLoanInstallment_2'], 2); ?></p>
                                <p><?php echo number_format($payroll['houseLoanInstallment_3'], 2); ?></p>
                                <p><?php echo number_format($payroll['salaryAdjustment'], 2); ?></p>
                                <p><?php echo number_format($payroll['otherDed'], 2); ?></p>
                                <p><?php echo number_format($payroll['revenue'], 2); ?></p>
                                <p class="font-semibold"><?php echo number_format($payroll['totalDeduction'], 2); ?></p>
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
                <section class="mt-24 text-xl border-t border-black py-5">
                    <p>Website developed by the Department of <span class="font-bold">Computer Science and Engineering</span>, JUST</p>
                    <p>NB. If there is any error in your bill, immediately inform it to the Accounts Office of JUST</p>
                </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
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
            const netPay = <?php echo json_encode(array_column($employees, 'netPay')); ?>[index];
            
            // Convert net pay to words and update the element
            element.textContent = convertNumberToWords(netPay);
        });
    });
    </script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
    <script>
        document.getElementById("generatePDF").addEventListener("click", function() {
            // Capture the entire page content for PDF generation
            const element = document.body;  // Capture the body of the page
            
            // Options for the PDF generation
            const options = {
                filename: 'employee_payroll.pdf',  // Filename of the PDF
                image: { type: 'jpeg', quality: 0.98 },  // Set image type and quality
                html2canvas: { scale: 0.6 },  // Increase the scale for better quality
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },  // PDF format
                pagebreak: { mode: ['css', 'legacy'] }  // Ensure proper page breaks
            };
            
            // Generate the PDF
            html2pdf().from(element).set(options).save();
        });
    </script>
</body>
</html>