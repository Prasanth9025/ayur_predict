<?php
// htdocs/ayur_predict/forgot_password.php

// --- FIX: Set Timezone to India (IST) ---
date_default_timezone_set('Asia/Kolkata');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email required"]);
    exit();
}

// 1. Check if user exists
$checkSql = "SELECT id FROM users WHERE email = '$email'";
$result = $conn->query($checkSql);

if ($result->num_rows > 0) {
    // 2. Generate OTP
    $otp = rand(100000, 999999);
    
    // 3. Calculate Expiry in India Time (Current Time + 10 Minutes)
    // We use "H" (24-hour format) so the Database understands it correctly.
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // 4. Save OTP to DB
    $updateSql = "UPDATE users SET otp = '$otp', otp_expiry = '$expiry' WHERE email = '$email'";
    $conn->query($updateSql);

    // 5. Send Email via Gmail SMTP
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'prasanthumapathi123@gmail.com'; 
        $mail->Password   = 'ekhb byje vdba swke'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('prasanthumapathi123@gmail.com', 'AyurPredict');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password - AyurPredict';
        $mail->Body    = "<b>Your OTP is: $otp</b><br>It expires in 10 minutes.";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "OTP sent to email"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Mail Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Email not found"]);
}
?>