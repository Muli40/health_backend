<?php
header('Content-Type: application/json');

include __DIR__ . '/../confg/config.php';

// Connect to database
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}

// Get POST data (JSON)
$data = json_decode(file_get_contents("php://input"), true);

// Collect data from request
$patient_id   = $data['patient_id'] ?? '';
$doctor_id    = $data['doctor_id'] ?? '';
$appointment_datetime = $data['appointment_datetime'] ?? '';
$status       = $data['status'] ?? 'Scheduled';  // default
$reason       = $data['reason'] ?? '';

// Validate required fields
if (empty($patient_id) || empty($doctor_id) || empty($appointment_datetime)) {
    echo json_encode(["status" => "error", "message" => "Patient ID, Doctor ID, and Appointment DateTime are required"]);
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Insert into Appointments table
    $stmt = $conn->prepare("INSERT INTO Appointments (Patient_ID, Doctor_ID, Appointment_DateTime, Status, Reason) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $appointment_datetime, $status, $reason);
    $stmt->execute();

    $appointment_id = $conn->insert_id;

    echo json_encode([
        "status" => "success",
        "message" => "Appointment created successfully",
        "appointment_id" => $appointment_id
    ]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>