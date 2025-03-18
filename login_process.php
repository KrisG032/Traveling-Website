<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validation
    $errors = [];
    
    // Check if email is empty
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    // Check if password is empty
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, proceed with login
    if (empty($errors)) {
        try {
            // Get user by email
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['success'] = "Welcome back, " . $user['username'] . "!";
                header("Location: index.html");
                exit();
            } else {
                $_SESSION['error'] = "Invalid email or password";
                header("Location: login.html");
                exit();
            }
            
        } catch(PDOException $e) {
            $_SESSION['error'] = "Login failed: " . $e->getMessage();
            header("Location: login.html");
            exit();
        }
    } else {
        $_SESSION['errors'] = $errors;
        header("Location: login.html");
        exit();
    }
}
?> 