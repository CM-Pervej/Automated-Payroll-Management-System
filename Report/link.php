<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Include the database connection
include '../db_conn.php';

// Fetch payroll records for the current month and year
$payrollData = [];
$start_month = $_GET['start_month'] ?? '';
$start_year = $_GET['start_year'] ?? '';
$end_month = $_GET['end_month'] ?? '';
$end_year = $_GET['end_year'] ?? '';

// Fetch distinct values for dropdown filters
$months = [];
$years = [];

// Fetch distinct departments
$month_query = "SELECT DISTINCT month FROM payroll ORDER BY month DESC";
$result = $conn->query($month_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $months[] = $row['month'];
    }
}

// Fetch distinct departments
$year_query = "SELECT DISTINCT year FROM payroll ORDER BY year DESC";
$result = $conn->query($year_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $years[] = $row['year'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="sideBar.css">
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <!-- Sidebar -->
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include 'sideBar.php'; ?>
    </header>

    <!-- Main Content -->
    <div class="flex flex-col flex-grow ml-64">
        <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
            <?php include 'topBar.php'; ?>
        </aside>
        <main class="flex-grow p-4 mt-16 bg-white shadow-lg overflow-auto">
            <article class="py-5">
                <h2 class="text-3xl font-semibold text-gray-800 mb-3">Download Salary Reports</h2>
                <p class="text-lg text-gray-600">Please select the report you wish to download. Be mindful that this data is highly sensitive.</p>
            </article>
            <section class="flex-grow bg-white">
                <div class="container mx-auto">
                    <!-- Download Options -->
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Option 1: Monthly Salary Report for Every Employee -->
                        <div class="bg-blue-100 rounded-lg shadow-md p-6 hover:bg-blue-200 transition duration-200">
                            <aside class="flex justify-between">
                                <div>
                                    <h3 class="text-xl font-semibold text-blue-600 mb-2">Monthly Salary Report</h3>
                                    <p class="text-gray-700 mb-4">This will download the salary details for all employees and departments. Make sure to handle the data with care.</p>
                                </div>
                                <!-- Filters -->
                                <section>
                                    <span>Select:</span>
                                    <select id="monthFilter" class="p-3 border rounded-md">
                                        <option value="" class="bg-gray-600 text-white">Month</option>
                                        <?php foreach ($months as $month): ?>
                                            <option value="<?php echo htmlspecialchars($month); ?>"><?php echo htmlspecialchars($month); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select id="yearFilter" class="p-3 border rounded-md">
                                        <option value="" class="bg-gray-600 text-white">Year</option>
                                        <?php foreach ($years as $year): ?>
                                            <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </section>
                            </aside>
                            <p class="flex gap-5 w-max">
                                <a id="monthlyEmployeeReport" href="#" class="w-full bg-blue-600 text-white font-semibold p-3 rounded-md hover:bg-blue-700 transition duration-200"> Employee </a>
                                <a id="monthlyDepartmentReport" href="#" class="w-full bg-green-600 text-white font-semibold p-3 rounded-md hover:bg-green-700 transition duration-200"> Department </a>
                                <a id="monthlyDepartmentReport2" href="#" class="w-full bg-green-600 text-white font-semibold p-3 rounded-md hover:bg-green-700 transition duration-200"> Department </a>
                                <a id="monthlyDesignationReport" href="#" class="w-full bg-violet-600 text-white font-semibold p-3 rounded-md hover:bg-violet-700 transition duration-200"> Designation </a>
                            </p>
                        </div>
                        <!-- Option 1: Monthly Salary Report for Every Employee -->
                        <div class="bg-blue-100 rounded-lg shadow-md p-6 hover:bg-blue-200 transition duration-200">
                            <aside class="flex justify-between">
                                <div>
                                    <h3 class="text-xl font-semibold text-blue-600 mb-2">Yearly Salary Report</h3>
                                    <p class="text-gray-700 mb-4">This will download the salary details for all employees and departments. Make sure to handle the data with care.</p>
                                    <!-- Multiple Buttons with Different Links -->
                                    <div class="space-x-4">
                                        <!-- Buttons: All use the same function, but pass different URLs -->
                                        <button class="p-3 bg-blue-500 text-white rounded-md" onclick="generateReport('dept.php')">Generate Year Report</button>
                                        <button class="p-3 bg-green-500 text-white rounded-md" onclick="generateReport('monthly_report.php')">Generate Monthly Report</button>
                                        <button class="p-3 bg-yellow-500 text-white rounded-md" onclick="generateReport('custom_report.php')">Generate Custom Report</button>
                                    </div>
                                </div>
                                <!-- Filters -->
                                <section class="flex flex-col items-end gap-4 mb-8">
                                    <aside>
                                        <span>From:</span>
                                        <select id="startMonthFilter" class="p-3 border rounded-md">
                                            <option value="" class="bg-gray-600 text-white">Month</option>
                                            <?php foreach ($months as $month): ?>
                                                <option value="<?php echo htmlspecialchars($month); ?>"><?php echo htmlspecialchars($month); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select id="startYearFilter" class="p-3 border rounded-md">
                                            <option value="" class="bg-gray-600 text-white">Year</option>
                                            <?php foreach ($years as $year): ?>
                                                <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </aside>
                                    <aside>
                                        <span>To:</span>
                                        <select id="endMonthFilter" class="p-3 border rounded-md">
                                            <option value="" class="bg-gray-600 text-white">Month</option>
                                            <?php foreach ($months as $month): ?>
                                                <option value="<?php echo htmlspecialchars($month); ?>"><?php echo htmlspecialchars($month); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select id="endYearFilter" class="p-3 border rounded-md">
                                            <option value="" class="bg-gray-600 text-white">Year</option>
                                            <?php foreach ($years as $year): ?>
                                                <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </aside>
                                </section>
                            </aside>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // JavaScript to dynamically update download links based on selected filters
        const monthFilter = document.getElementById('monthFilter');
        const yearFilter = document.getElementById('yearFilter');

        // Update links based on selected month and year
        function updateLinks() {
            const month = monthFilter.value;
            const year = yearFilter.value;

            // Update the URLs for the reports dynamically
            const monthlyEmployeeUrl = month && year ? `empReport.php?month=${month}&year=${year}` : '#';
            const monthlyDepartmentUrl = month && year ? `deptReport.php?month=${month}&year=${year}` : '#';
            const monthlyDepartmentUrl2 = month && year ? `deptReport2.php?month=${month}&year=${year}` : '#';
            const monthlyDesignationUrl = month && year ? `designReport.php?month=${month}&year=${year}` : '#';

            document.getElementById('monthlyEmployeeReport').setAttribute('href', monthlyEmployeeUrl);
            document.getElementById('monthlyDepartmentReport').setAttribute('href', monthlyDepartmentUrl);
            document.getElementById('monthlyDepartmentReport2').setAttribute('href', monthlyDepartmentUrl2);
            document.getElementById('monthlyDesignationReport').setAttribute('href', monthlyDesignationUrl);
        }

        // Event listeners to update the download links when filters are changed
        monthFilter.addEventListener('change', updateLinks);
        yearFilter.addEventListener('change', updateLinks);

        // Initial link update when page loads
        updateLinks();
    </script>

    <script>
        function generateReport(page) {
            const startMonth = document.getElementById('startMonthFilter').value;
            const startYear = document.getElementById('startYearFilter').value;
            const endMonth = document.getElementById('endMonthFilter').value;
            const endYear = document.getElementById('endYearFilter').value;

            if (startMonth && startYear && endMonth && endYear) {
                window.location.href = `${page}?start_month=${startMonth}&start_year=${startYear}&end_month=${endMonth}&end_year=${endYear}`;
            } else {
                alert("Please select all fields before generating the report.");
            }
        }
    </script>
</body>
</html>
