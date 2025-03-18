<?php
session_start();
require_once "config/database.php";
require_once "models/User.php";
require_once "utils/EmailSender.php";

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$emailSender = new EmailSender();

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"));

    // Validate required fields
    if (!empty($data->full_name) && !empty($data->email) && !empty($data->password)) {
        // Set user property values
        $user->full_name = $data->full_name;
        $user->email = $data->email;
        $user->password = $data->password;
        $user->phone_number = $data->phone_number ?? null;
        $user->date_of_birth = $data->date_of_birth ?? null;
        $user->role = "user";
        $user->is_active = 0; // User starts as inactive until email is verified

        // Check if email already exists
        if ($user->emailExists()) {
            $response["status"] = "error";
            $response["message"] = "Email already exists.";
        } else {
            // Create the user
            if ($user->create()) {
                // Create user profile
                $profile_query = "INSERT INTO user_profiles (user_id) VALUES (:user_id)";
                $profile_stmt = $db->prepare($profile_query);
                $profile_stmt->bindParam(":user_id", $user->id);
                $profile_stmt->execute();
                
                // Create email verification token
                $verification_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                $verification_query = "INSERT INTO email_verifications (user_id, verification_token, expires_at) 
                                    VALUES (:user_id, :token, :expires_at)";
                $verification_stmt = $db->prepare($verification_query);
                $verification_stmt->bindParam(":user_id", $user->id);
                $verification_stmt->bindParam(":token", $verification_token);
                $verification_stmt->bindParam(":expires_at", $expires_at);
                $verification_stmt->execute();
                
                // Send verification email
                if ($emailSender->sendVerificationEmail($user->email, $user->full_name, $verification_token)) {
                    $response["status"] = "success";
                    $response["message"] = "Registration successful! Please check your email to verify your account.";
                } else {
                    $response["status"] = "warning";
                    $response["message"] = "Registration successful, but there was an error sending the verification email. Please contact support.";
                }
            } else {
                $response["status"] = "error";
                $response["message"] = "Unable to register user.";
            }
        }
    } else {
        $response["status"] = "error";
        $response["message"] = "Missing required fields.";
    }
} else {
    $response["status"] = "error";
    $response["message"] = "Invalid request method.";
}

// Send response
header("Content-Type: application/json");
echo json_encode($response);
?> 