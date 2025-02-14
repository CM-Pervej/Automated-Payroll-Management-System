<?php
include 'auth.php';
include '../db_conn.php';

// Initialize an array to hold the allowances
$allowances = [];

// Fetch all allowances
$stmt = $conn->prepare("SELECT * FROM allowanceList");
$stmt->execute();
$result = $stmt->get_result();

// Fetch all allowances into the array
while ($row = $result->fetch_assoc()) {
    $allowances[] = $row;
}

// Handle update and delete actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        // Update allowance
        $id = $_POST['id'];
        $allwName = htmlspecialchars($_POST['allwName']);
        $allwPercentage = htmlspecialchars($_POST['allwPercentage']);
        $allwValue = htmlspecialchars($_POST['allwValue']);

        $stmt = $conn->prepare("UPDATE allowanceList SET allwName = ?, allwPercentage = ?, allwValue = ? WHERE id = ?");
        $stmt->bind_param("sddi", $allwName, $allwPercentage, $allwValue, $id);

        if ($stmt->execute()) {
            $message = "<p class='text-green-600'>Allowance updated successfully!</p>";
        } else {
            $message = "<p class='text-red-600'>Error updating allowance: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        // Delete allowance
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM allowanceList WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $message = "<p class='text-red-600'>Allowance deleted successfully!</p>";
        } else {
            $message = "<p class='text-red-600'>Error deleting allowance: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management - Allowances</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/8e69038194.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../sideBar.css">
</head>
<body class="bg-blue-50 h-screen flex overflow-hidden">
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include 'sideBar.php'; ?>
    </header>
    <div class="flex flex-col flex-grow ml-64">
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include 'topBar.php'; ?>
            </aside>
        </div>

        <!-- Main Content -->
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="mx-auto bg-white p-10">
                <header class="mb-8 text-left">
                    <h1 class="text-3xl font-semibold text-gray-700">Manage Allowances</h1>
                    <p class="text-gray-500">Update and manage additional allowances within the payroll system</p>
                </header>

                <?php if (isset($message)) echo $message; ?>

                <div class="overflow-x-auto">
                    <table class="border min-w-full divide-y divide-gray-200 rounded-lg shadow-lg">
                        <thead class="bg-gray-200">
                            <tr class="w-full">
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold border-r border-gray-300">Allowance Name</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold border-r border-gray-300">Percentage (%)</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold border-r border-gray-300">Value (৳)</th>
                                <th class="px-6 py-4 text-left text-sm text-gray-700 font-bold border-r border-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($allowances as $allowance): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?php echo $allowance['id']; ?>">
                                        <td class="px-6 py-4 border-r border-gray-200">
                                            <input type="text" name="allwName" value="<?php echo htmlspecialchars($allowance['allwName']); ?>" required
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                                maxlength="50" placeholder="Allowance Name">
                                        </td>
                                        <td class="px-6 py-4 border-r border-gray-200">
                                            <input type="number" name="allwPercentage" value="<?php echo htmlspecialchars($allowance['allwPercentage']); ?>" required
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                                placeholder="Percentage">
                                        </td>
                                        <td class="px-6 py-4 border-r border-gray-200">
                                            <input type="number" name="allwValue" value="<?php echo htmlspecialchars($allowance['allwValue']); ?>" required
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                                placeholder="Value">
                                        </td>
                                        <td class="px-6 py-4 border-r border-gray-200">
                                            <div class="flex justify-center space-x-2">
                                                <button type="submit" name="update" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition">Update</button>
                                                <button type="submit" name="delete" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition" onclick="return confirm('Are you sure you want to delete this allowance?');">Remove</button>
                                            </div>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
