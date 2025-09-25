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
$first_name = $data['first_name'] ?? '';
$last_name = $data['last_name'] ?? '';
$dob = $data['dob'] ?? '';
$gender = $data['gender'] ?? '';
$phone = $data['phone'] ?? '';
$email = $data['email'] ?? '';
$address = $data['address'] ?? '';
$emergency_contact = $data['emergency_contact'] ?? '';

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($dob) || empty($gender) ||
    empty($phone) || empty($email) || empty($address) || empty($emergency_contact)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert patient
    $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, date_of_Birth, gender, contact_Number, email, address, emergency_contact) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $first_name, $last_name, $dob, $gender, $phone, $email, $address, $emergency_contact);
    $stmt->execute();
    $stmt->close();

    // Create user account for patient
    $email = strtolower(trim($email));
    $password = password_hash($phone, PASSWORD_DEFAULT); // default password = phone
    $role = 1; // patient role
    $username = strtolower($first_name . "." . $last_name);
    $lastLogin = null;

    $stmt2 = $conn->prepare("INSERT INTO users (Username, user_Password, role_id, Last_Login, email, phone) 
                             VALUES (?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("ssisss", $username, $password, $role, $lastLogin, $email, $phone);
    $stmt2->execute();
    $stmt2->close();

    // Commit transaction
    $conn->commit();

    echo json_encode(["status" => "success", "message" => "Patient added successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>