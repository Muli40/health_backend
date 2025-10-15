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
//     echo json_encode(["status" => "error", "message" => "Specialization is required"]);
//     exit;
// }
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Fetch doctors with the given specialization
    $stmt = $conn->prepare("SELECT 
                              d.doctor_ID,
                              concat(d.first_name, ' ', d.last_name) AS full_name,
                              d.specialty,
                              d.contact_Number,
                              d.email,
                              dep.department_name
                              FROM health_db.doctors d
                              LEFT JOIN health_db.departments dep ON d.department_ID = dep.department_ID
                              ORDER BY d.doctor_ID ASC;");
    // $stmt->bind_param("s", $specialization);
    $stmt->execute();
    $result = $stmt->get_result();

    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "doctors" => $doctors
    ]);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn->close();
exit;
?>
