<?php
require_once 'db_connect.php';
header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"));

if (isset($data->name) && isset($data->email) && isset($data->password)) {
    
    // 1. Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $data->email);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered"]);
        exit();
    }
    $check->close();

    // 2. Hash the password (Security Best Practice)
    $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);

    // 3. Insert User
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data->name, $data->email, $hashed_password);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User registered successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data"]);
}
$conn->close();
?>