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
        $payrolls = [];
        $totalEmployees = 0;
        $totalDepartments = 0;
        $totalDesignations = 0;
        $totalGrades = 0;
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
            $totalEmployees++;
            $totalBasic += $basic;
            $totalAllowances += $totalAllowance;
            $totalDeductions += $totalDeduction;
            $totalSalary += $netPay;
                        
            $payrolls[] = $row;
        
            // Grouping by department
            $departments[$row['departments']]['details'][] = $row;
            $departments[$row['departments']]['count'] = isset($departments[$row['departments']]['count']) 
                ? $departments[$row['departments']]['count'] + 1 
                : 1;
        
            // Grouping by designation
            $designation[$row['designation']]['details'][] = $row;
            $designation[$row['designation']]['count'] = isset($designation[$row['designation']]['count']) 
                ? $designation[$row['designation']]['count'] + 1 
                : 1;
        
            // Grouping by grade
            $grade[$row['grade']]['details'][] = $row;
            $grade[$row['grade']]['count'] = isset($grade[$row['grade']]['count']) 
                ? $grade[$row['grade']]['count'] + 1 
                : 1;
        }

        // Count unique departments
        $totalDepartments = count($departments);
        $totalDesignations = count($designation);
        $totalGrades = count($grade);
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
<body>
    <main class="flex flex-col flex-grow bg-white text-black px-8 pb-20 mb-20">
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
                    উপর্যুক্ত বিষয়ের আলোকে আপনার বিভাগ/দপ্তরের <?php echo date('F, Y', strtotime("{$passed_year}-{$passed_month}-01")); ?> মাসের বেতন ও ভাতার বিল টি অন্ত্যন্ত সহজভাবে প্রস্তুত করা হলো। 
                    বিল গুলিতে স্বাক্ষর দিয়ে অনুমোদন হিসাবে দপ্তরে প্রেরণের জন্য এবং কোন প্রকার ভুল পরিলক্ষিত হলে সেটিও সংশোধন করে জমা দেওয়ার জন্য অনুরোধ করা হলো। 
                    যে সকল শিক্ষক ও কর্মকর্তাগণ তাদের টি.আই.এন হিসাব দপ্তরে প্রদান করেননি, তাদেরকে চলতি মাসের বেতন ও ভাতার বিলের সাথে টি.আই.এন এর কপি জমা দেওয়ার জন্য অনুরোধ করা হলো।
                </p>
            </article>
        </section>

        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php else: ?>
            <section class="page-container pt-16 relative h-screen flex flex-col gap-10">
                <div class="flex justify-between items-center border-b pb-6 mb-6">
                    <div class="flex flex-col gap-2">
                        <h1 class="text-2xl font-extrabold text-blue-900">Jashore University of Science and Technology, <span class="font-semibold text-black">Jashore - 7408</span></h1>
                        <p class="text-gray-600 text-lg">Payroll Period: <span class="font-semibold"><?php echo date('F, Y', strtotime("{$passed_year}-{$passed_month}-01")); ?></span></p>
                        <p class="text-gray-600 text-lg">Date: <span class="font-semibold"><?php echo date('F d, Y'); ?></span></p>
                    </div>
                    <div>
                        <img src="../uploads/JUSTt.png" alt="University Logo" class="h-20">
                    </div>
                </div>
                <section class="flex flex-col justify-center items-stretch gap-10 w-full">
                    <!-- First Table Section -->
                    <div class="flex-1 w-full">
                        <h1 class="text-2xl mb-5 pb-3 font-semibold border-b border-gray-300">University Overview</h1>
                        <table class="border border-collapse w-full table-auto table-fixed">
                            <tr>
                                <th class="text-xl text-left p-3 border border-gray-400">Total Employees</th>
                                <th class="text-xl text-left p-3 border border-gray-400">Total Departments</th>
                                <th class="text-xl text-left p-3 border border-gray-400">Total Designations</th>
                                <th class="text-xl text-left p-3 border border-gray-400">Total Grades</th>
                            </tr>
                            <tr>
                                <td class="text-left p-3 border border-gray-400"><?php echo $totalEmployees; ?></td>
                                <td class="text-left p-3 border border-gray-400"><?php echo $totalDepartments; ?></td>
                                <td class="text-left p-3 border border-gray-400"><?php echo $totalDesignations; ?></td>
                                <td class="text-left p-3 border border-gray-400"><?php echo $totalGrades; ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Second Table Section -->
                    <div class="flex-1 w-full">
                        <h1 class="text-2xl mb-5 pb-3 font-semibold border-b border-gray-300">Financial Summary</h1>
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
                <section class="w-full">
                    <article class="flex justify-between items-end gap-5 mt-10">
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
                    <article class="mt-24 text-xl border-t border-black py-5">
                        <p>Website developed by the Department of <span class="font-bold">Computer Science and Engineering</span>, JUST</p>
                        <p>NB. If there is any error in your bill, immediately inform it to the Accounts Office of JUST</p>
                    </article>
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
</body>
</html>
