<?php
// predict.php - FULL UPDATED VERSION
$raw_input = file_get_contents("php://input");
file_put_contents("debug_log.txt", "Received: " . $raw_input . "\n", FILE_APPEND);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once 'db_connect.php';

$data = json_decode($raw_input);

if (isset($data->user_id)) {
    $user_id = $data->user_id;
    $sleep_quality = $data->sleep_quality;
    $stress_level = $data->stress_level;
    $energy_level = $data->energy_level;
    $digestion = $data->digestion;
    $stool_type = $data->stool_type;
    // New Fields
    $sleep_hours = isset($data->sleep_hours) ? $data->sleep_hours : 0;
    $hydration = isset($data->hydration) ? $data->hydration : 0;

    // --- SCORING LOGIC ---
    $vata_score = 10;
    $pitta_score = 10;
    $kapha_score = 10;

    if ($stress_level >= 7) $vata_score += 30;
    if ($stool_type == 3) $vata_score += 20;
    if ($digestion == 3) $vata_score += 20;
    if ($sleep_quality <= 4) $vata_score += 15;

    if ($energy_level >= 7) $pitta_score += 30;
    if ($stool_type == 8) $pitta_score += 20;
    if ($digestion == 1) $pitta_score += 20;
    
    if ($energy_level <= 4) $kapha_score += 20;
    if ($sleep_quality >= 7) $kapha_score += 20;
    if ($stool_type == 2) $kapha_score += 20;
    if ($digestion == 2) $kapha_score += 20;

    $dosha = "Vata";
    if ($pitta_score > $vata_score && $pitta_score > $kapha_score) $dosha = "Pitta";
    elseif ($kapha_score > $vata_score && $kapha_score > $pitta_score) $dosha = "Kapha";

    // --- INSERT SCORES & INSIGHTS ---
    $stmt = $conn->prepare("INSERT INTO prediction_history (user_id, sleep_quality, stress_level, energy_level, predicted_dosha, vata_score, pitta_score, kapha_score, sleep_hours, hydration, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    // "iiiisiiidd" -> 10 variables
    $stmt->bind_param("iiiisiiidd", $user_id, $sleep_quality, $stress_level, $energy_level, $dosha, $vata_score, $pitta_score, $kapha_score, $sleep_hours, $hydration);
    
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "dosha" => $dosha,
            "vata_score" => $vata_score,
            "pitta_score" => $pitta_score,
            "kapha_score" => $kapha_score
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Insert failed"]);
    }
    $stmt->close();
} else {
    echo json_encode(["message" => "Data missing"]);
}
?>