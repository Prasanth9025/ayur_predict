<?php
// 1. ENABLE DEBUGGING
$raw_input = file_get_contents("php://input");
file_put_contents("debug_log.txt", "Received: " . $raw_input . "\n", FILE_APPEND);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once 'db_connect.php';

$data = json_decode($raw_input);

// ADDED: Check for user_id here too
if (
    isset($data->user_id) && 
    isset($data->sleep_quality) &&
    isset($data->stress_level)
) {
    $user_id = $data->user_id; // Get the ID
    $sleep_quality = $data->sleep_quality;
    $stress_level = $data->stress_level;
    $energy_level = $data->energy_level;
    $digestion = $data->digestion;
    $stool_type = $data->stool_type;

    // --- LOGIC SECTION (Same as before) ---
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

    $total = $vata_score + $pitta_score + $kapha_score;
    $vata_percent = round(($vata_score / $total) * 100);
    $pitta_percent = round(($pitta_score / $total) * 100);
    $kapha_percent = round(($kapha_score / $total) * 100);

    $dosha = "Vata";
    if ($pitta_score > $vata_score && $pitta_score > $kapha_score) {
        $dosha = "Pitta";
    } elseif ($kapha_score > $vata_score && $kapha_score > $pitta_score) {
        $dosha = "Kapha";
    }

    // --- FIX IS HERE ---
    
    // 1. Add 'user_id' to the column list
    // 2. Add another '?' to the values
    $stmt = $conn->prepare("INSERT INTO prediction_history (user_id, sleep_quality, stress_level, energy_level, predicted_dosha) VALUES (?, ?, ?, ?, ?)");
    
    // 3. Update bind_param:
    //    - Change "iiis" to "iiiis" (5 parameters now)
    //    - Add $user_id as the first variable
    $stmt->bind_param("iiiis", $user_id, $sleep_quality, $stress_level, $energy_level, $dosha);
    
    $stmt->execute();
    $stmt->close();
    // -------------------

    $response = array(
        "dosha" => $dosha,
        "vata" => $vata_percent,
        "pitta" => $pitta_percent,
        "kapha" => $kapha_percent,
        "recommendations" => ["Test Recommendation 1", "Test Recommendation 2"]
    );

    echo json_encode($response);

} else {
    echo json_encode(array("message" => "Data missing or User ID missing"));
}
?>