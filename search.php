<?php
// Include the database connection
include 'db_conn.php';

$searchResults = [];
$errorMessage = "";

// Get the keyword from the query string
$keyword = $_GET['keyword'] ?? '';

if (!empty($keyword)) {
    // Search query across key fields
    $query = "
        SELECT e.*, d.designation, dep.department_name, g.grade 
        FROM employee e 
        LEFT JOIN designations d ON e.designation_id = d.id 
        LEFT JOIN departments dep ON e.department_id = dep.id 
        LEFT JOIN grade g ON e.grade_id = g.id 
        WHERE e.employeeNo LIKE ? 
           OR e.name LIKE ? 
           OR e.email LIKE ? 
           OR e.contactNo LIKE ? 
           OR e.gender = ? 
           OR e.empStatus = ? 
           OR g.grade = ? 
           OR dep.department_name = ? 
           OR d.designation = ?
    ";

    $param = '%' . $keyword . '%';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssssssss', $param, $param, $param, $param, $keyword, $keyword, $keyword, $keyword, $keyword);
    $stmt->execute();
    $searchResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($searchResults)) {
        $errorMessage = "No matching employees found.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-5">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-semibold text-center text-gray-800">Search Results for "<?php echo htmlspecialchars($keyword); ?>"</h1>

            <?php if (!empty($errorMessage)): ?>
                <div class="text-red-600 text-center mb-4"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (!empty($searchResults)): ?>
                <table class="mt-4 w-full border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border">Employee No</th>
                            <th class="px-4 py-2 border">Name</th>
                            <th class="px-4 py-2 border">Gender</th>
                            <th class="px-4 py-2 border">Email</th>
                            <th class="px-4 py-2 border">Contact No</th>
                            <th class="px-4 py-2 border">Designation</th>
                            <th class="px-4 py-2 border">Department</th>
                            <th class="px-4 py-2 border">Grade</th>
                            <th class="px-4 py-2 border">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchResults as $employee): ?>
                            <tr>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['employeeNo']); ?></td>
                                <td class="px-4 py-2">
                                    <a href="profile.php?employee_id=<?php echo $employee['id']; ?>" class="text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars($employee['name']); ?>
                                    </a>
                                </td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['gender']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['contactNo']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['designation']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['department_name']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['grade']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($employee['empStatus']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
