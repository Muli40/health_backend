<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

include __DIR__ . '/../confg/config.php';

// Try to connect to the database
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$dep_name = $data['dep_name'] ?? '';
$dep_desc = $data['dep_desc'] ?? '';


$sql = "INSERT INTO departments (department_Name, dept_desc) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $dep_name, $dep_desc);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["status" => "success", "message" => "Department created successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to create department"]);
}
?>
