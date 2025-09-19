<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

include __DIR__ . '/../confg/config.php';

// Try to connect to the database
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

if (!empty($data['dep_name']) && !empty($data['dep_desc'])) {
    $dep_name = "good";
    $dep_desc = "nice";

    // IMPORTANT: Make sure your table column names match here
    $stmt = $conn->prepare("INSERT INTO Departments (Department_Name, Description) VALUES (?, ?)");
    $stmt->bind_param("ss", $dep_name, $dep_desc);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Department created successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>
