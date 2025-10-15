<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

include __DIR__ . '/../confg/config.php'; 
// config.php should define: $dbHost, $dbUser, $dbPass, $dbName

// Connect with MySQLi
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) 
    die(json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ]));
// //collect input data//
// $specialization = $data['specialization'] ?? '';
// // Validate required fields//   
// if (empty($specialization)) {
//     echo json_encode(["status" => "error", "message" => "Specialization
// is required"]);
//     exit;
// }
try {
    $sql = "SELECT 
    dep.department_ID,
    dep.department_Name,
    dep.dept_desc
FROM health_db.departments dep
ORDER BY dep.department_ID ASC;";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        echo json_encode([
            "status" => "success",
            "message" => "Departments fetched successfully",
            "data" => $departments
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No departments found"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
$conn->close();
exit;
?>

