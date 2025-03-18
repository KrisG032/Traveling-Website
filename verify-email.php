<?php
session_start();
require_once "config/database.php";
require_once "models/User.php";

$database = new Database();
$db = $database->getConnection();

$response = array();

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Get verification record
    $query = "SELECT ev.*, u.email, u.full_name 
              FROM email_verifications ev 
              JOIN users u ON ev.user_id = u.id 
              WHERE ev.verification_token = :token 
              AND ev.verified = 0 
              AND ev.expires_at > NOW()";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":token", $token);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $verification = $stmt->fetch();
        
        // Update verification status
        $update_query = "UPDATE email_verifications 
                        SET verified = 1 
                        WHERE id = :id";
        
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(":id", $verification['id']);
        
        if ($update_stmt->execute()) {
            // Update user status
            $user_query = "UPDATE users 
                          SET is_active = 1 
                          WHERE id = :user_id";
            
            $user_stmt = $db->prepare($user_query);
            $user_stmt->bindParam(":user_id", $verification['user_id']);
            
            if ($user_stmt->execute()) {
                $response["status"] = "success";
                $response["message"] = "Email verified successfully! You can now log in.";
                
                // Set session variables
                $_SESSION['user_id'] = $verification['user_id'];
                $_SESSION['user_email'] = $verification['email'];
                $_SESSION['user_name'] = $verification['full_name'];
                $_SESSION['is_verified'] = true;
            } else {
                $response["status"] = "error";
                $response["message"] = "Error updating user status.";
            }
        } else {
            $response["status"] = "error";
            $response["message"] = "Error updating verification status.";
        }
    } else {
        $response["status"] = "error";
        $response["message"] = "Invalid or expired verification token.";
    }
} else {
    $response["status"] = "error";
    $response["message"] = "No verification token provided.";
}

// Redirect to appropriate page based on verification status
if ($response["status"] === "success") {
    header("Location: index.php?verification=success");
} else {
    header("Location: login.php?verification=error&message=" . urlencode($response["message"]));
}
exit();
?> 