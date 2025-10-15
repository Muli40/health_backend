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
//validate required fields//
if (empty($patient_id)) {
    echo json_encode(["status" => "error", "message" => "Patient ID is required"]);
    exit;
}
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Fetch billing records for the given patient
    $stmt = $conn->prepare("
    SELECT b.Bill_ID, b.Patient_ID, p.first_name AS patient_first_name, p.last_name AS patient_last_name,
           b.Amount, b.Date, b.Payment_Status, p.contact_Number,b.Payment_Method
    FROM billing b
    LEFT JOIN patients p ON b.Patient_ID = p.Patient_ID
    WHERE b.Patient_ID = ?
    ORDER BY b.Date DESC
    ");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $bills = [];
    while ($row = $result->fetch_assoc()) {
        $bills[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "bills" => $bills
    ]);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
exit;
$conn->close();
?>