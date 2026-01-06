<?php
// htdocs/ayur_predict/login.php

include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $conn->real_escape_string($data['email']);
$password = $data['password']; // Plain text password from app

// 1. Fetch user by Email ONLY (do not check password in SQL)
$sql = "SELECT id, name, password FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // 2. Verify the Password Hash
    // This checks the plain text '$password' against the hash from DB '$row['password']'
    if (password_verify($password, $row['password'])) {
        echo json_encode([
            "status" => "success", 
            "message" => "Login successful",
            "user_id" => $row['id'],
            "name" => $row['name']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Password"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}
?>