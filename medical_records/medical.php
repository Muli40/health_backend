<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

include __DIR__ . '/../confg/config.php'; 

// Connect
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}

// Collect input data
$patient_id   = $data['patient_id'] ?? '';
$doctor_id    = $data['doctor_id'] ?? '';
$diagnosis    = $data['diagnosis'] ?? '';
$prescription = $data['prescription'] ?? '';
$date         = $data['date'] ?? date('Y-m-d'); // default: today

// Validate required fields
if (empty($patient_id) || empty($doctor_id) || empty($date)) {
    echo json_encode(["status" => "error", "message" => "Patient ID, Doctor ID, and Date are required"]);
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $stmt = $conn->prepare("INSERT INTO medicalrecords 
        (Patient_ID, Doctor_ID, Diagnosis, Prescription, Date) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $diagnosis, $prescription, $date);
    $stmt->execute();
    $record_id = $conn->insert_id;
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Medical record added successfully",
        "record_id" => $record_id
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>