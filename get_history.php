<?php
error_reporting(E_ALL ^ E_WARNING);
include 'db_connect.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([]); 
    exit();
}

// UPDATED QUERY:
// Now selects vata/pitta/kapha scores (for History Graph)
// AND sleep_hours, hydration, stress_level (for Insights Charts)
$sql = "SELECT id, predicted_dosha, vata_score, pitta_score, kapha_score, sleep_hours, hydration, stress_level, created_at 
        FROM prediction_history 
        WHERE user_id = '$user_id' 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

$history = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $history[] = [
            "id" => $row['id'],
            "predicted_dosha" => $row['predicted_dosha'],
            
            // Dosha Scores (Integers)
            "vata_score" => (int)$row['vata_score'],
            "pitta_score" => (int)$row['pitta_score'],
            "kapha_score" => (int)$row['kapha_score'],
            
            // Insights Data (Floats/Ints)
            // Use 'floatval' to ensure they aren't strings in JSON
            "sleep_hours" => floatval($row['sleep_hours']),
            "hydration" => floatval($row['hydration']),
            "stress_level" => (int)$row['stress_level'],
            
            "created_at" => $row['created_at']
        ];
    }
}

echo json_encode($history);
$conn->close();
?>