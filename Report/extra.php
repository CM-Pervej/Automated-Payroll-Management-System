<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Include the database connection
include '../db_conn.php';

// Fetch payroll records for the selected date range
$payrollData = [];
$start_month = $_GET['start_month'] ?? '';
$start_year = $_GET['start_year'] ?? '';
$end_month = $_GET['end_month'] ?? '';
$end_year = $_GET['end_year'] ?? '';

// Fetch distinct values for dropdown filters
$months = [];
$years = [];

// Fetch distinct months
$month_query = "SELECT DISTINCT month FROM payroll ORDER BY month DESC";
$result = $conn->query($month_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $months[] = $row['month'];
    }
}

// Fetch distinct years
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
             
            <!-- Filters -->
            <section class="flex gap-4 mb-8">
                <select id="startMonthFilter" class="p-3 border rounded-md">
                    <option value="" class="bg-gray-600 text-white">From Month</option>
                    <?php foreach ($months as $month): ?>
                        <option value="<?php echo htmlspecialchars($month); ?>"><?php echo htmlspecialchars($month); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="startYearFilter" class="p-3 border rounded-md">
                    <option value="" class="bg-gray-600 text-white">From Year</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="endMonthFilter" class="p-3 border rounded-md">
                    <option value="" class="bg-gray-600 text-white">To Month</option>
                    <?php foreach ($months as $month): ?>
                        <option value="<?php echo htmlspecialchars($month); ?>"><?php echo htmlspecialchars($month); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="endYearFilter" class="p-3 border rounded-md">
                    <option value="" class="bg-gray-600 text-white">To Year</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                    <?php endforeach; ?>
                </select>
            </section>

            <!-- Multiple Buttons with Different Links -->
            <div class="space-x-4">
                <!-- Buttons: All use the same function, but pass different URLs -->
                <button 
                    class="p-3 bg-blue-500 text-white rounded-md"
                    onclick="generateReport('year.php')">Generate Year Report</button>

                <button 
                    class="p-3 bg-green-500 text-white rounded-md"
                    onclick="generateReport('monthly_report.php')">Generate Monthly Report</button>

                <button 
                    class="p-3 bg-yellow-500 text-white rounded-md"
                    onclick="generateReport('custom_report.php')">Generate Custom Report</button>
            </div>
        </main>
    </div>

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
