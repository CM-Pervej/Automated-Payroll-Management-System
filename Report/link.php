<?php
session_start();

// Check if the user is logged in and has the HR role
if (!isset($_SESSION['user_id']) || ($_SESSION['userrole_id'] != 1 && $_SESSION['userrole_id'] != 2)) {
    header('Location: ../dashboard.php'); // Redirect if not HR/Admin
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

// Fetch distinct months and years from the payroll table
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
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
  <!-- Sidebar -->
  <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
      <?php include '../sideBar.php'; ?>
  </header>
  
  <!-- Main Content -->
  <div class="flex flex-col flex-grow ml-64">
    <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
        <?php include '../topBar.php'; ?>
    </aside>
    <main class="flex-grow p-4 mt-16 bg-white shadow-lg overflow-auto">
      <article class="py-5">
        <h2 class="text-3xl font-semibold text-gray-800 mb-3">Download Salary Reports</h2>
        <p class="text-lg text-gray-600">Please select the report you wish to download. This data is highly sensitive; handle it with care.</p>
      </article>
      <section class="flex-grow bg-white">
        <div class="container mx-auto">
          <!-- Download Options -->
          <div class="grid grid-cols-1 gap-6">
            <!-- Monthly Salary Report Option -->
            <div class="bg-blue-100 rounded-lg shadow-md p-6 hover:bg-blue-200 transition duration-200">
              <aside class="flex justify-between">
                <div>
                  <h3 class="text-xl font-semibold text-blue-600 mb-2">Monthly Salary Report</h3>
                  <p class="text-gray-700 mb-4">Download detailed salary reports based on selected criteria.</p>
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
                <a id="monthlyUniversityReport" href="#" class="w-full bg-yellow-500 text-white font-semibold p-3 rounded-md hover:bg-yellow-700 transition duration-200"> University </a>
                <a id="monthlyEmployeeReport" href="#" class="w-full bg-blue-500 text-white font-semibold p-3 rounded-md hover:bg-blue-700 transition duration-200"> Employee </a>
                <a id="monthlyDepartmentReport" href="#" class="w-full bg-green-500 text-white font-semibold p-3 rounded-md hover:bg-green-700 transition duration-200"> Department </a>
                <!-- <a id="monthlyDepartmentReport2" href="#" class="w-full bg-green-500 text-white font-semibold p-3 rounded-md hover:bg-green-700 transition duration-200"> Department </a> -->
                <a id="monthlyDesignationReport" href="#" class="w-full bg-violet-500 text-white font-semibold p-3 rounded-md hover:bg-violet-700 transition duration-200"> Designation </a>
                <a id="monthlyGradeReport" href="#" class="w-full bg-cyan-500 text-white font-semibold p-3 rounded-md hover:bg-cyan-700 transition duration-200"> Grade </a>
                <a id="monthlyGenderReport" href="#" class="w-full bg-fuchsia-500 text-white font-semibold p-3 rounded-md hover:bg-fuchsia-700 transition duration-200"> Gender </a>
              </p>
            </div>

            <div class="bg-blue-100 rounded-lg shadow-md p-6 hover:bg-blue-200 transition duration-200">
              <aside class="flex justify-between">
                  <div>
                      <h3 class="text-xl font-semibold text-blue-600 mb-2">Yearly Salary Report</h3>
                      <p class="text-gray-700 mb-4">Generate a comprehensive salary report for the selected year. Make sure to handle the data with care.</p>
                      <!-- Multiple Buttons with Different Links -->
                      <div class="flex gap-5 w-max">
                          <!-- Buttons: All use the same function, but pass different URLs -->
                          <button onclick="generateReport('uni_year.php')" class="w-full bg-yellow-500 text-white font-semibold p-3 rounded-md hover:bg-yellow-700 transition duration-200">University</button>
                          <button onclick="generateReport('employee_year.php')"class="w-full bg-blue-500 text-white font-semibold p-3 rounded-md hover:bg-blue-700 transition duration-200">Employee</button>
                          <button  onclick="generateReport('dept_year.php')" class="w-full bg-green-500 text-white font-semibold p-3 rounded-md hover:bg-green-700 transition duration-200">Department</button>
                          <button  onclick="generateReport('desi_year.php')" class="w-full bg-violet-500 text-white font-semibold p-3 rounded-md hover:bg-violet-700 transition duration-200">Designation</button>
                          <button  onclick="generateReport('grade_year.php')" class="w-full bg-cyan-500 text-white font-semibold p-3 rounded-md hover:bg-cyan-700 transition duration-200">Grade</button>
                          <button  onclick="generateReport('gender_year.php')" class="w-full bg-fuchsia-500 text-white font-semibold p-3 rounded-md hover:bg-fuchsia-700 transition duration-200">Gender</button>
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
  
  <!-- Hidden iframe to trigger PDF download from empreport.php -->
  <iframe id="downloadFrame" style="display:none;"></iframe>
  
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const monthFilter = document.getElementById("monthFilter");
      const yearFilter = document.getElementById("yearFilter");
      const startMonthFilter = document.getElementById("startMonthFilter");
      const startYearFilter = document.getElementById("startYearFilter");
      const endMonthFilter = document.getElementById("endMonthFilter");
      const endYearFilter = document.getElementById("endYearFilter");
      const downloadFrame = document.getElementById("downloadFrame");

      const reportLinks = {
        monthlyUniversityReport: "uni_month.php",
        monthlyEmployeeReport: "employee_month.php",
        monthlyDepartmentReport: "dept_month.php",
        // monthlyDepartmentReport2: "dept_month2.php",
        monthlyDesignationReport: "desi_month.php",
        monthlyGradeReport: "grade_month.php",
        monthlyGenderReport: "gender_month.php",
      };

      function updateLinks() {
        const month = monthFilter.value;
        const year = yearFilter.value;

        Object.keys(reportLinks).forEach((id) => {
          const link = document.getElementById(id);
          if (link) link.href = month && year ? `${reportLinks[id]}?month=${month}&year=${year}` : "#";
        });
      }

      function downloadReport(event) {
        event.preventDefault();
        const { id } = event.target;
        const month = monthFilter.value;
        const year = yearFilter.value;
        if (month && year) {
          downloadFrame.src = `${reportLinks[id]}?month=${month}&year=${year}&download=1`;
        } else {
          alert("Please select both month and year.");
        }
      }

      function generateReport(page) {
        const startMonth = startMonthFilter.value;
        const startYear = startYearFilter.value;
        const endMonth = endMonthFilter.value;
        const endYear = endYearFilter.value;

        if (startMonth && startYear && endMonth && endYear) {
          window.location.href = `${page}?start_month=${startMonth}&start_year=${startYear}&end_month=${endMonth}&end_year=${endYear}`;
        } else {
          alert("Please select all fields before generating the report.");
        }
      }

      monthFilter.addEventListener("change", updateLinks);
      yearFilter.addEventListener("change", updateLinks);
      updateLinks();

      Object.keys(reportLinks).forEach((id) => {
        document.getElementById(id)?.addEventListener("click", downloadReport);
      });

      window.generateReport = generateReport;
    });
</script>
</body>
</html>
