<?php
// Include the database connection
include 'db_conn.php';

// Get the current month and year
$current_month = date('m');
$current_year = date('Y');

// Prepare the SQL statement to fetch payroll data for all employees in the current month and year
$stmt = $conn->prepare("SELECT * FROM payroll WHERE `month` = ? AND `year` = ?");
$stmt->bind_param("ii", $current_month, $current_year);

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
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="bg-blue-50 flex">
    <main class="flex flex-col flex-grow bg-white px-8 pb-20 mb-20">
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
            <!-- Button to generate PDF -->
            <button id="generatePDF" class="btn btn-primary mt-5 ml-10">Generate PDF</button>

            <!-- Loop through all employees and display their data -->
            <?php foreach ($employees as $payroll): ?>
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
                                <p class="absolute bottom-0 right-0"><?php echo number_format($payroll['grossPay'], 2); ?></p>
                            </div>
                        </article>
                    </section>
                </aside>
                <aside class="h-full">
                    <p class="border border-black py-2 w-full text-center text-lg font-bold">Deductions</p>
                    <article class="flex justify-between px-10">
                        <div class="text-right">
                            <p>GPF:</p>
                            <p>GPF Installment:</p>
                            <p>House Rent Deduction:</p>
                            <p>Benevolent Fund:</p>
                            <p>Insurance:</p>
                            <p>Electricity:</p>
                            <p>HRD Fund:</p>
                            <p>Club Subscription:</p>
                            <p>Association Subscription:</p>
                            <p>Transport Bill:</p>
                            <p>Telephone Bill:</p>
                            <p>Pension Fund:</p>
                            <p>Fish Bill:</p>
                            <p>Income Tax:</p>
                            <p>Donation:</p>
                            <p>Guest House Rent:</p>
                            <p>House Loan 1:</p>
                            <p>House Loan 2:</p>
                            <p>House Loan 3:</p>
                            <p>Salary Adjustment:</p>
                            <p>Revenue:</p>
                            <p>Other Deduction:</p>
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
                        </div>
                    </article>
                </aside>
            </section>

            <section class="flex justify-between mx-8 mt-3 text-lg font-bold">
                <p>Net Pay: <?php echo number_format($payroll['netPay'], 2); ?></p>
            </section>

            <hr class="border border-black mx-8 mt-3">
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script>
        document.getElementById('generatePDF').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.text("Payroll Report", 20, 20);
            let yOffset = 30;

            <?php foreach ($employees as $payroll): ?>
                doc.text("Employee: <?php echo htmlspecialchars($payroll['name']); ?>", 20, yOffset);
                yOffset += 10;
                doc.text("Net Pay: <?php echo number_format($payroll['netPay'], 2); ?>", 20, yOffset);
                yOffset += 20;
            <?php endforeach; ?>

            doc.save('payroll_report.pdf');
        });
    </script>
</body>
</html>
