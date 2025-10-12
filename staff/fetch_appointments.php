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
$doctor_id = $data['doctor_id'] ?? '';
// Validate required fields//
if (empty($doctor_id)) {
    echo json_encode(["status" => "error", "message" => "Doctor ID is required"]);
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Fetch appointments for the given doctor
    $stmt = $conn->prepare("SELECT a.Appointment_ID, a.Patient_ID, p.first_name, p.last_name, a.Appointment_DateTime, a.Status, a.Reason ,
                            u.phone, u.email
                            FROM Appointments a 
                            LEFT JOIN patients p ON a.Patient_ID = p.Patient_ID 
                            LEFT JOIN users u ON u.patient_id = p.Patient_ID
                            WHERE a.Doctor_ID = ? 
                            ORDER BY a.Appointment_DateTime DESC");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "appointments" => $appointments
    ]);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>