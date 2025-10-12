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

// Collect input data  
$medical_record_id = $data['medical_record_id'] ?? '';

// Validate required fields
if (empty($medical_record_id)) {
    echo json_encode(["status" => "error", "message" => "Medical Record ID is required"]);
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Fetch medical record for the given ID
    $stmt = $conn->prepare("SELECT mr.Record_ID, mr.Patient_ID, 
                                   p.first_name AS patient_first_name, 
                                   p.last_name AS patient_last_name,
                                   mr.Doctor_ID, 
                                   d.first_name AS doctor_first_name, 
                                   d.last_name AS doctor_last_name,
                                   mr.Diagnosis, mr.Prescription, mr.Date
                            FROM medicalrecords mr
                            LEFT JOIN patients p ON mr.Patient_ID = p.Patient_ID
                            LEFT JOIN doctors d ON mr.Doctor_ID = d.Doctor_ID
                            WHERE mr.Record_ID = ?");
    $stmt->bind_param("i", $medical_record_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "status" => "success",
            "medical_record" => $row
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Medical record not found"
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
