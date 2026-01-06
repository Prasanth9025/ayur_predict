<?php
// htdocs/ayur_predict/reset_password.php

// 1. Set Timezone to India
date_default_timezone_set('Asia/Kolkata');

include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validate Input
if (!isset($data['email']) || !isset($data['otp']) || !isset($data['new_password'])) {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit();
}

$email = $conn->real_escape_string($data['email']);
$otp = $conn->real_escape_string($data['otp']);
$plain_password = $conn->real_escape_string($data['new_password']);

// 2. Get Current India Time
$current_time = date("Y-m-d H:i:s");

// 3. Verify OTP
$sql = "SELECT id FROM users WHERE email = '$email' AND otp = '$otp' AND otp_expiry > '$current_time'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    
    // --- FIX: Hash the Password ---
    // This creates a secure hash like: $2y$10$e/y...
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    // 4. Store the HASHED password in the database
    $updateSql = "UPDATE users SET password = '$hashed_password', otp = NULL, otp_expiry = NULL WHERE email = '$email'";
    
    if ($conn->query($updateSql)) {
        echo json_encode(["status" => "success", "message" => "Password updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database Error"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid or Expired OTP"]);
}
?>