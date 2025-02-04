<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <style>
        .sidebar-active-link {
            background-color: white;
            color: black;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .sidebar-container {
            width: 16rem;
            padding: 0 1.5rem 1.5rem 1.5rem;
        }
        .sidebar-heading {
            font-weight: bold;
            color: #4a5568;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: #1a202c;
            font-weight: 600;
            border-radius: 0.375rem;
            transition: background-color 0.3s ease;
            font-size: 1rem;
        }
        .sidebar-link i,
        .sidebar-link span {
            font-size: inherit;
        }
        .sidebar-link:hover {
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="flex flex-col bg-blue-50">
  <!-- side bar -->
    <div class="flex flex-grow w-max">
      <aside class="sidebar-container">
        <div class="flex items-center border-b border-black">
            <span class="text-xl font-bold text-black py-5 whitespace-nowrap">Payroll Management</span>
        </div>
        <div class="my-5">
                <p class="text-sm sidebar-heading">MAIN MENU</p>
                <nav class="flex flex-col mt-3 space-y-2">
                    <a href="../dashboard.php" class="sidebar-link <?php echo ($currentPage == 'index.php') ? 'sidebar-active-link' : ''; ?>"><i class="fas fa-home mr-2"></i><span>Dashboard</span></a>
                    <a href="../employee.php" class="sidebar-link <?php echo ($currentPage == 'employee.php') ? 'sidebar-active-link' : ''; ?>"><i class="fas fa-users mr-2"></i><span>Employee</span></a>  
                    <a href="../payroll.php" class="sidebar-link <?php echo ($currentPage == 'payroll.php') ? 'sidebar-active-link' : ''; ?>"><i class="fas fa-file-invoice-dollar mr-2"></i><span>Payroll</span></a>
                    <a href="../report.php" class="sidebar-link <?php echo ($currentPage == 'report.php') ? 'sidebar-active-link' : ''; ?>"><i class="fas fa-file-invoice-dollar mr-2"></i><span>Report</span></a>
                </nav>
            </div>
            <nav>
                <ul class="menu menu-xs rounded-lg max-w-xs w-full -ml-2">
                    <li>
                      <details open>
                        <summary class="text-base sidebar-heading cursor-pointer">REVIEW</summary>
                        <ul>
                          <li><a href="../empChecked.php" class="sidebar-link <?php echo ($currentPage == 'empChecked.php') ? 'sidebar-active-link' : ''; ?>">Checked</a></li>
                          <li><a href="../empUnChecked.php" class="sidebar-link <?php echo ($currentPage == 'empUnChecked.php') ? 'sidebar-active-link' : ''; ?>">Unchecked</a></li>
                        </ul>
                      </details>
                    </li>
                  </ul>
            </nav>                    
            <nav>
                <ul class="menu menu-xs rounded-lg max-w-xs w-full -ml-2">
                    <li>
                      <details open>
                        <summary class="text-base sidebar-heading cursor-pointer">ACTION</summary>
                        <ul>
                          <li><a href="allowances.php" class="sidebar-link <?php echo ($currentPage == 'allowances.php') ? 'sidebar-active-link' : ''; ?>">Allowances</a></li>
                          <li><a href="deductions.php" class="sidebar-link <?php echo ($currentPage == 'deductions.php') ? 'sidebar-active-link' : ''; ?>">Deductions</a></li>
                          <li><a href="grades.php" class="sidebar-link <?php echo ($currentPage == 'grades.php') ? 'sidebar-active-link' : ''; ?>">Grade</a></li>
                          <li><a href="designations.php" class="sidebar-link <?php echo ($currentPage == 'designations.php') ? 'sidebar-active-link' : ''; ?>">Designation</a></li>
                          <li><a href="departments.php" class="sidebar-link <?php echo ($currentPage == 'departments.php') ? 'sidebar-active-link' : ''; ?>">Department</a></li>
                          <li><a href="chargeAllw.php" class="sidebar-link <?php echo ($currentPage == 'chargeAllw.php') ? 'sidebar-active-link' : ''; ?>">Additional Duty</a></li>
                        </ul>
                      </details>
                    </li>
                  </ul>
            </nav>                
        </aside>
    </div>
</body>
</html>
