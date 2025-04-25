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

            // Group by department and designation, count designations
            $employees[$row['departments']][$row['designation']]['details'][] = $row;
            $employees[$row['departments']][$row['designation']]['count'] = 
                isset($employees[$row['departments']][$row['designation']]['count']) 
                    ? $employees[$row['departments']][$row['designation']]['count'] + 1 
                    : 1;
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
<body>
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
                    উপর্যুক্ত বিষয়ের আলোকে আপনার বিভাগ/দপ্তরের <?php echo date('F, Y', strtotime("{$passed_year}-{$passed_month}-01")); ?> মাসের বেতন ও ভাতার বিল টি অন্ত্যন্ত সহজভাবে প্রস্তুত করা হলো। 
                    বিল গুলিতে স্বাক্ষর দিয়ে অনুমোদন হিসাবে দপ্তরে প্রেরণের জন্য এবং কোন প্রকার ভুল পরিলক্ষিত হলে সেটিও সংশোধন করে জমা দেওয়ার জন্য অনুরোধ করা হলো। 
                    যে সকল শিক্ষক ও কর্মকর্তাগণ তাদের টি.আই.এন হিসাব দপ্তরে প্রদান করেননি, তাদেরকে চলতি মাসের বেতন ও ভাতার বিলের সাথে টি.আই.এন এর কপি জমা দেওয়ার জন্য অনুরোধ করা হলো।
                </p>
            </article>
        </section>

        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php else: ?>
        <!-- Loop through each department and display payroll for employees in that department -->
        <?php foreach ($employees as $department => $designations): ?>
    <section class="page-container pt-16">
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
            <p class="ml-10 my-2 text-lg font-bold">Current Month: <?php echo date('F, Y', strtotime("{$passed_year}-{$passed_month}-01")); ?></p>
            <hr class="border border-black">
        </section>

        <!-- <h1>General Details</h1> -->
        <section class="grid grid-cols-2 w-full gap-5">
            <article class="flex justify-center items-end gap-3 flex-1">
                <div class="text-right">
                    <p>Name:</p> <p>Chairman:</p> <p>Email:</p> <p>Phone:</p>
                </div>
                <div>
                    <p><?php echo htmlspecialchars($department); ?></p> <p>Pervej Chowkider</p> <p>pervejbd2029@gmail.com</p> <p>01982525570</p>
                </div>
            </article>
            <article class="flex justify-center items-end gap-3 flex-1">
                <div class="text-right">
                    <p>Established Year:</p> <p>Location:</p> <p>Total Employee:</p>
                </div>
                <div>
                    <p>2007</p> <p>2nd Floor, Academic Building</p> <p>20</p>
                </div>
            </article>
        </section>
        <section class="mt-5">
            <p class="text-xl">Salary Details of <span class="font-bold"><?php echo htmlspecialchars($department); ?></span> </p>
            <article class="mt-5">
                <table class="w-full border-collapse border border-black">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border border-gray-300 px-4 py-2 text-center whitespace-nowrap">SL</th> 
                            <th class="border border-gray-300 px-4 py-2 text-left whitespace-nowrap">Designation</th> 
                            <th class="border border-gray-300 px-4 py-2 text-center whitespace-nowrap">Count</th> 
                            <th class="border border-gray-300 px-4 py-2 text-right whitespace-nowrap">Basic</th> 
                            <th class="border border-gray-300 px-4 py-2 text-right whitespace-nowrap">Allowances</th> 
                            <th class="border border-gray-300 px-4 py-2 text-right whitespace-nowrap">Deductions</th> 
                            <th class="border border-gray-300 px-4 py-2 text-right whitespace-nowrap">Net Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php 
                            $totalDepartmentPay = 0; 
                            $sl = 1;
                            foreach ($designations as $designation => $data): 
                                $totalDesignationPay = array_sum(array_column($data['details'], 'netPay'));
                                $totalDepartmentPay += $totalDesignationPay;
                            ?>
                                <tr class="hover:bg-gray-100 text-black">
                                    <td class="border border-gray-300 px-4 py-2 text-center whitespace-nowrap"><?php echo $sl++; ?></td>
                                    <td class="border border-gray-300 px-4 py-2 text-left whitespace-nowrap"><?php echo htmlspecialchars($designation); ?></td>
                                    <td class="border border-gray-300 px-4 py-2 text-center whitespace-nowrap"><?php echo $data['count']; ?></td>
                                    <td class="border border-gray-300 px-4 py-2 text-right whitespace-nowrap"><?php echo number_format(array_sum(array_column($data['details'], 'basic')), 2); ?></td>
                                    <td class="border border-gray-300 px-4 py-2 text-right whitespace-nowrap"><?php echo number_format(array_sum(array_column($data['details'], 'grossPay')), 2); ?></td>
                                    <td class="border border-gray-300 px-4 py-2 text-right whitespace-nowrap"><?php echo number_format(array_sum(array_column($data['details'], 'totalDeduction')), 2); ?></td>
                                    <td class="border border-gray-300 px-4 py-2 text-right whitespace-nowrap"><?php echo number_format($totalDesignationPay, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                </table>
            </article>
            <aside class="flex gap-5 py-5">
                <div class="text-right flex flex-col justify-center text-lg font-semibold">
                    <p>Total Pay (BDT):</p>
                    <p>In Words:</p>
                </div>
                <div class=" flex flex-col justify-center text-lg font-semibold">
                    <p><?php echo number_format($totalDepartmentPay, 2); ?></p>
                    <p class="total-pay-words"></p>
                </div>
            </aside>
            <p class="font-semibold text-lg">Declaration: The amount claimed in this bill is correct and has not been drawn earlier</p>
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

    const convert = (num) => {
        if (num < 21) return dictionary[num]; // Handle numbers 0-20
        if (num < 100) {
            const tens = Math.floor(num / 10) * 10; // Tens place
            const units = num % 10; // Ones place
            return dictionary[tens] + (units ? `-${dictionary[units]}` : ''); // Combine tens and ones
        }
        if (num < 1000) {
            const hundreds = Math.floor(num / 100); // Hundreds place
            const remainder = num % 100; // Remainder after hundreds
            return dictionary[hundreds] + ' Hundred' + (remainder ? ` ${convert(remainder)}` : ''); // Combine hundreds and remainder
        }
        for (let base of [10000000, 100000, 1000, 100]) {
            if (num >= base) {
                const quotient = Math.floor(num / base); // Quotient for large units
                const remainder = num % base; // Remainder after the large unit
                return convert(quotient) + ' ' + dictionary[base] + (remainder ? ` ${convert(remainder)}` : ''); // Combine large units and remainder
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

// Loop through each department and apply the number to words function
document.querySelectorAll('.total-pay-words').forEach(function(element) {
    const totalPayElement = element.previousElementSibling; // The total pay element
    const totalPay = parseFloat(totalPayElement.textContent.replace(/,/g, '')); // Get the numeric value of total pay
    if (!isNaN(totalPay)) {
        element.textContent = convertNumberToWords(totalPay); // Set the text content with the result
    }
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
                html2canvas: { scale: 2 },  // Increase the scale for better quality
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },  // PDF format
                pagebreak: { mode: ['css', 'legacy'] }  // Ensure proper page breaks
            };
            
            // Generate the PDF
            html2pdf().from(element).set(options).save();
        });
    </script>
</body>
</html>