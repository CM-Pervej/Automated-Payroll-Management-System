
<?php
include 'db_conn.php';

$query = $_GET['query'] ?? '';
$suggestions = [];

if (!empty($query)) {
    $param = '%' . $query . '%';

    // SQL query to search across specific fields
    $sql = "
        SELECT DISTINCT value FROM (
            SELECT e.employeeNo AS value FROM employee e WHERE e.employeeNo LIKE ?
            UNION ALL
            SELECT e.name AS value FROM employee e WHERE e.name LIKE ?
            UNION ALL
            SELECT e.empStatus AS value FROM employee e WHERE e.empStatus LIKE ?
            UNION ALL
            SELECT e.contactNo AS value FROM employee e WHERE e.contactNo LIKE ?
            UNION ALL
            SELECT e.email AS value FROM employee e WHERE e.email LIKE ?
            UNION ALL
            SELECT e.gender AS value FROM employee e WHERE e.gender LIKE ?
            UNION ALL
            SELECT g.grade AS value FROM grade g WHERE g.grade LIKE ?
            UNION ALL
            SELECT dep.department_name AS value FROM departments dep WHERE dep.department_name LIKE ?
            UNION ALL
            SELECT d.designation AS value FROM designations d WHERE d.designation LIKE ?
        ) AS suggestion_values
    ";

    // Bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssss', $param, $param, $param, $param, $param, $param, $param, $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();

    // Collect unique matched values
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = ['value' => $row['value']];
    }

    $stmt->close();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($suggestions);
?>
