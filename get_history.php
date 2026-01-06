<?php
error_reporting(E_ALL ^ E_WARNING);
include 'db_connect.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([]); // Return empty list if no ID
    exit();
}

// 1. Fetch History (Newest first)
$sql = "SELECT id, predicted_dosha, created_at FROM predictions WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);

$history = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // 2. Format the date nicely (Optional)
        // This converts SQL date "2024-01-15 14:30:00" to "2024-01-15"
        $date = date("Y-m-d", strtotime($row['created_at']));
        
        $history[] = [
            "id" => $row['id'],
            "predicted_dosha" => $row['predicted_dosha'], // Must match @SerializedName in Kotlin
            "created_at" => $date                         // Must match @SerializedName in Kotlin
        ];
    }
}

// 3. Return JSON List
echo json_encode($history);

$conn->close();
?>