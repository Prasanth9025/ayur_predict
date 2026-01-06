<?php
include 'db_connect.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) { echo json_encode(null); exit(); }

// 1. Fetch Last 7 Days
$sql = "SELECT vata_score, pitta_score, kapha_score, created_at 
        FROM prediction_history 
        WHERE user_id = '$user_id' 
        ORDER BY created_at DESC LIMIT 7";

$result = $conn->query($sql);
$history = [];

while($row = $result->fetch_assoc()) {
    // Ensure numbers are integers, not strings
    $row['vata_score'] = (int)$row['vata_score'];
    $row['pitta_score'] = (int)$row['pitta_score'];
    $row['kapha_score'] = (int)$row['kapha_score'];
    $history[] = $row;
}

// 2. Separate Current vs History
$current = $history[0] ?? null; 
$previous = $history[1] ?? null;

// 3. Calculate Trends
function calculateTrend($curr, $prev) {
    if (!$prev || $prev == 0) return 0;
    return round((($curr - $prev) / $prev) * 100);
}

$response = [
    "current" => $current,
    "history" => array_reverse($history), // Send oldest -> newest for graph
    "trends" => [
        "vata_change" => calculateTrend($current['vata_score'] ?? 0, $previous['vata_score'] ?? 0),
        "pitta_change" => calculateTrend($current['pitta_score'] ?? 0, $previous['pitta_score'] ?? 0),
        "kapha_change" => calculateTrend($current['kapha_score'] ?? 0, $previous['kapha_score'] ?? 0)
    ]
];

echo json_encode($response);
?>