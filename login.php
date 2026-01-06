<?php
require_once 'db_connect.php';
header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"));

if (isset($data->email) && isset($data->password)) {
    
    // 1. Find user by email
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $data->email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        // 2. Verify Password
        if (password_verify($data->password, $hashed_password)) {
            // SUCCESS: Return the User ID and Name
            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "user_id" => $id,
                "name" => $name
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data"]);
}
$conn->close();
?>