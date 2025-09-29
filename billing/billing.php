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
$patient_id     = $data['patient_id'] ?? '';
$amount         = $data['amount'] ?? '';
$payment_status = $data['payment_status'] ?? '';
$payment_method = $data['payment_method'] ?? '';
$date           = $data['date'] ?? '';

// Validate input data
if (empty($patient_id) || empty($amount) || empty($payment_status) || empty($payment_method) || empty($date)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Insert data into billing table
    $stmt = $conn->prepare("INSERT INTO Billing (Patient_ID, Amount, Payment_Status, Payment_Method, Date) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $patient_id, $amount, $payment_status, $payment_method, $date);
    $stmt->execute();

    $billing_id = $conn->insert_id;
    echo json_encode([
        "status" => "success",
        "message" => "Billing record saved",
        "billing_id" => $billing_id
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    $conn->close();
}
?>
