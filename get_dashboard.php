<?php
include 'db_connect.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) { echo json_encode(null); exit(); }

// 1. Fetch History (Scores)
$sql = "SELECT vata_score, pitta_score, kapha_score, created_at 
        FROM prediction_history 
        WHERE user_id = '$user_id' 
        ORDER BY created_at DESC LIMIT 7";

$result = $conn->query($sql);
$history = [];

while($row = $result->fetch_assoc()) {
    $row['vata_score'] = (int)$row['vata_score'];
    $row['pitta_score'] = (int)$row['pitta_score'];
    $row['kapha_score'] = (int)$row['kapha_score'];
    $history[] = $row;
}

// 2. Calculate Trends
$current = $history[0] ?? null; 
$previous = $history[1] ?? null;

function calculateTrend($curr, $prev) {
    if (!$prev || $prev == 0) return 0;
    return round((($curr - $prev) / $prev) * 100);
}

// 3. --- NEW: CALCULATE STREAK ---
$streakSql = "SELECT DISTINCT DATE(created_at) as check_date 
              FROM prediction_history 
              WHERE user_id = '$user_id' 
              ORDER BY check_date DESC";
$streakResult = $conn->query($streakSql);

$streak = 0;
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$expectedDate = null; // The date we are looking for next

while ($row = $streakResult->fetch_assoc()) {
    $dbDate = $row['check_date'];

    // First iteration: The latest date must be Today or Yesterday to count
    if ($expectedDate === null) {
        if ($dbDate === $today) {
            $streak++;
            $expectedDate = $yesterday; // Next valid date is yesterday
        } elseif ($dbDate === $yesterday) {
            $streak++;
            $expectedDate = date('Y-m-d', strtotime('-2 days')); // Next valid is day before yesterday
        } else {
            // Streak is broken (last check-in was too long ago)
            break; 
        }
    } else {
        // Subsequent iterations: Date must match the expected consecutive day
        if ($dbDate === $expectedDate) {
            $streak++;
            // Move expected date back by 1 day
            $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day'));
        } else {
            break; // Gap found, stop counting
        }
    }
}

// 4. Return Final JSON
$response = [
    "current" => $current,
    "history" => array_reverse($history),
    "trends" => [
        "vata_change" => calculateTrend($current['vata_score'] ?? 0, $previous['vata_score'] ?? 0),
        "pitta_change" => calculateTrend($current['pitta_score'] ?? 0, $previous['pitta_score'] ?? 0),
        "kapha_change" => calculateTrend($current['kapha_score'] ?? 0, $previous['kapha_score'] ?? 0)
    ],
    "streak" => $streak // <--- SENDING REAL STREAK
];

echo json_encode($response);
?>