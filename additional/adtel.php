<?php
// Include database connection
include '../db_conn.php';
include 'auth.php';

// Initialize message variables
$success_message = '';
$error_message = '';

// Handle form submission for adding a new duty and telephone allowances
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve selected addDuty and selected telephone allowances
    if (isset($_POST['addDuty_id']) && isset($_POST['telephoneAllw_ids'])) {
        $addDutyId = $_POST['addDuty_id'];
        $telephoneAllwIds = $_POST['telephoneAllw_ids'];  // Array of selected telephone allowance IDs

        // Sort the telephone allowance IDs in ascending order
        sort($telephoneAllwIds); // This will sort the array in ascending order

        // Insert into add_duty_telephone for each selected telephone allowance
        foreach ($telephoneAllwIds as $telephoneAllwId) {
            $insertSql = "INSERT INTO add_duty_telephone (addDuty_id, telephoneAllw_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("ii", $addDutyId, $telephoneAllwId); // 'ii' for two integer parameters

            if (!$stmt->execute()) {
                $error_message = "Error inserting data: " . $stmt->error;
                break;  // Stop inserting if there's an error
            }
        }

        if (empty($error_message)) {
            $success_message = "Telephone allowances added successfully to the duty!";
        }
        $stmt->close();
    } else {
        $error_message = "Please select a duty and at least one telephone allowance!";
    }
}

// Handle delete request for removing a specific duty-telephone allowance link
if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];

    // Delete the specific duty-telephone allowance links
    $stmt = $conn->prepare("DELETE FROM add_duty_telephone WHERE addDuty_id = ?");
    $stmt->bind_param("i", $deleteId);

    if ($stmt->execute()) {
        $success_message = "Duty and all associated telephone allowances deleted successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all duties from addDuty table (including addSalary)
$addDuties = $conn->query("SELECT id, designation, addSalary FROM addDuty WHERE id != 1 AND id NOT IN (SELECT DISTINCT addDuty_id FROM add_duty_telephone) ORDER BY id ASC");

// Fetch all telephone allowances from add_telephone_list table
$telephoneAllowances = $conn->query("SELECT id, telephoneAllw FROM add_telephone_list");

// Fetch existing duty-telephone links from add_duty_telephone table
$dutyTelephoneLinks = [];
$query = "SELECT dt.id, dt.addDuty_id, dt.telephoneAllw_id, ad.designation, ad.addSalary, at.telephoneAllw 
          FROM add_duty_telephone dt 
          JOIN addDuty ad ON dt.addDuty_id = ad.id
          JOIN add_telephone_list at ON dt.telephoneAllw_id = at.id
          WHERE dt.addDuty_id != 1
          ORDER BY dt.addDuty_id ASC";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $dutyTelephoneLinks[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Telephone Allowances to Duty</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 h-screen flex overflow-hidden">
    <header class="w-64 bg-blue-50 text-white fixed h-full sidebar-scrollable">
        <?php include '../sideBar.php'; ?>
    </header>
    <div class="flex flex-col flex-grow ml-64">
        <div class="w-full">
            <aside class="fixed left-64 top-0 right-0 bg-blue-50 shadow-md z-10">
                <?php include '../topBar.php'; ?>
            </aside>
        </div>
        <main class="flex-grow p-8 mt-16 bg-white shadow-lg overflow-auto">
            <div class="mb-4">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success shadow-lg"><?php echo $success_message; ?></div>
                <?php elseif (!empty($error_message)): ?>
                    <div class="alert alert-error shadow-lg"><?php echo $error_message; ?></div>
                <?php endif; ?>
            </div>
            
            <h1 class="text-3xl font-semibold mb-6 text-gray-800">Assign Telephone Allowances to Duty</h1>

            <!-- Form to assign telephone allowances to a duty -->
            <form method="POST" class="mx-auto flex justify-between">
                <!-- Duty Selection -->
                <div>
                    <label for="addDuty_id" class="block text-sm font-medium text-gray-700">Select additional Duty</label>
                    <select id="addDuty_id" name="addDuty_id" class="input input-bordered w-full max-w-xs" required>
                        <option value="" disabled selected>Select additional Duty</option>
                        <?php
                        // Fetch all duties from the addDuty table
                        while ($duty = $addDuties->fetch_assoc()) {
                            echo "<option value=\"" . $duty['id'] . "\">" . htmlspecialchars($duty['designation']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Multiple Telephone Allowance Selection -->
                <div>
                    <label for="telephoneAllw_ids" class="block text-sm font-medium text-gray-700">Select Telephone Allowances</label>
                    <div class="relative w-full max-w-xs">
                        <button type="button" class="input input-bordered w-full text-left" onclick="toggleDropdown()">
                            Select Allowances
                        </button>
                        <div id="dropdown" class="absolute z-10 hidden bg-white border rounded shadow-md w-full max-h-60 overflow-y-auto mt-1">
                            <?php
                            // Fetch all telephone allowances from the add_telephone_list table
                            while ($telephone = $telephoneAllowances->fetch_assoc()) {
                                ?>
                                <div class="px-4 py-2 hover:bg-gray-100">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="telephoneAllw_ids[]" value="<?php echo $telephone['id']; ?>" class="mr-2">
                                        <?php echo htmlspecialchars($telephone['telephoneAllw']); ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center space-x-4">
                    <button type="submit" class="btn btn-success">Assign Telephone Allowances</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>

            <!-- Duty-telephone allowances list -->
            <h2 class="text-2xl font-semibold mt-8 mb-6">Duty and Telephone Allowance Links</h2>
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>Duty Designation</th>
                        <th>Add Salary</th>
                        <th>Telephone Allowances</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Group telephone allowances by duty
                    $dutyGroups = [];
                    $serialNumber = 1; // Start serial number at 1
                    foreach ($dutyTelephoneLinks as $link) {
                        $dutyGroups[$link['addDuty_id']]['designation'] = $link['designation'];
                        $dutyGroups[$link['addDuty_id']]['addSalary'] = $link['addSalary'];
                        $dutyGroups[$link['addDuty_id']]['telephones'][] = $link['telephoneAllw'];
                        $dutyGroups[$link['addDuty_id']]['ids'][] = $link['id'];
                    }

                    // Display the grouped duties and linked telephone allowances
                    foreach ($dutyGroups as $dutyId => $group): 
                        ?>
                        <tr>
                            <td><?php echo $serialNumber++; ?></td>
                            <td><?php echo htmlspecialchars($group['designation']); ?></td>
                            <td><?php echo htmlspecialchars($group['addSalary']); ?></td>
                            <td>
                                <?php
                                // Display each telephone in a separate border area (no commas)
                                foreach ($group['telephones'] as $telephone) {
                                    echo "<span class=\"inline-block border p-2 m-1\">" . htmlspecialchars($telephone) . "</span>";
                                }
                                ?>
                            </td>
                            <td class="flex space-x-2">
                                <!-- Single Delete Button for each duty -->
                                <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?php echo $dutyId; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal for confirmation -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-8 rounded-lg shadow-lg w-1/3">
            <h2 class="text-xl font-semibold mb-4">Are you sure you want to delete this duty and all associated telephone allowances?</h2>
            <div class="flex justify-end space-x-4">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <a id="deleteConfirmBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(dutyId) {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteConfirmBtn').href = '?delete_id=' + dutyId;
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('dropdown');
            dropdown.classList.toggle('hidden');
        }

        // Optional: Close dropdown if clicked outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdown');
            const button = event.target.closest('button');
            if (!dropdown.contains(event.target) && !button) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
