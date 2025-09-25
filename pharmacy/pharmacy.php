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
$medicine_name      = $data['medicine_name'] ?? '';
$quantity_available = $data['quantity_available'] ?? '';
$expiry_date        = $data['expiry_date'] ?? '';

// Validate required fields
if (empty($medicine_name) || empty($quantity_available) || empty($expiry_date)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Insert into Pharmacy table
    $stmt = $conn->prepare("INSERT INTO Pharmacy (Medicine_Name, Quantity_Available, Expiry_Date) 
                            VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $medicine_name, $quantity_available, $expiry_date);
    $stmt->execute();

    $medicine_id = $conn->insert_id;

    echo json_encode([
        "status" => "success",
        "message" => "Medicine record saved",
        "medicine_id" => $medicine_id
    ]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>