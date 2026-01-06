<?php
// 1. Hide ugly warnings
error_reporting(E_ALL ^ E_WARNING);

include 'db_connect.php'; 

// 2. Get input
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

// 3. Safe Data Extraction
$user_id = $data['user_id'] ?? $_POST['user_id'] ?? null;
$name    = $data['name']    ?? $_POST['name']    ?? null;
// --- ADDED EMAIL HERE ---
$email   = $data['email']   ?? $_POST['email']   ?? null; 
$phone   = $data['phone']   ?? $_POST['phone']   ?? null;
$gender  = $data['gender']  ?? $_POST['gender']  ?? null;
$dob     = $data['dob']     ?? $_POST['dob']     ?? null;
$country = $data['country'] ?? $_POST['country'] ?? null;

// 4. Security Check
if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "User ID required"]);
    exit();
}

// 5. Update Query
$email = $data['email'] ?? $_POST['email'] ?? null;
// --- ADDED email = '$email' TO THE QUERY ---
$sql = "UPDATE users SET 
            name = '$name', 
            email = '$email',
            phone = '$phone', 
            gender = '$gender', 
            dob = '$dob', 
            country = '$country' 
        WHERE id = '$user_id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating record: " . $conn->error]);
}

$conn->close();
?>