<?php
error_reporting(E_ALL ^ E_WARNING);
include 'db_connect.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "User ID required"]);
    exit();
}

// 1. SELECT the new columns
$sql = "SELECT id, name, email, phone, gender, dob, country FROM users WHERE id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // 2. Return all fields (Must match Kotlin @SerializedName)
    echo json_encode([
        "status" => "success",
        "user_id" => $row['id'],
        "name" => $row['name'],
        "email" => $row['email'],
        "phone" => $row['phone'],       // New
        "gender" => $row['gender'],     // New
        "dob" => $row['dob'],           // New
        "country" => $row['country']    // New
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$conn->close();
?>