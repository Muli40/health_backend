<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

include __DIR__ . '/../confg/config.php'; 
// config.php should define: $dbHost, $dbUser, $dbPass, $dbName

// Connect with MySQLi
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}

//collect input data//
$patient_id = $data['patient_id'] ?? '';
$doctor_id = $data['doctor_id'] ?? '';
$test_name = $data['test_name'] ?? '';
$test_result = $data['test_result'] ?? '';
$date = $data['date'] ?? '';

// Validate required fields//
if (empty($patient_id) || empty($doctor_id) || empty($test_name) || empty($test_result) || empty($date)) {
    echo json_encode(["status" => "error", "message" => "$patient_id, $doctor_id, $test_name, $test_result and $date are required"]);
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Insert into Pharmacy table
    $stmt = $conn->prepare("INSERT INTO labtests (patient_ID, Doctor_ID, Test_Name, Test_Results ,date) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $test_name, $test_result, $date);
    $stmt->execute();
    $labtest_id = $conn->insert_id;
    echo json_encode([
        "status" => "success",
        "message" => "Lab test record saved",
        "labtest_id" => $labtest_id
    ]);
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn->close();
?>

