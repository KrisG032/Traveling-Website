<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    // Check if username is empty
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    // Check if password is at least 6 characters
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Email already exists";
                header("Location: register.html");
                exit();
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: login.html");
            exit();
            
        } catch(PDOException $e) {
            $_SESSION['error'] = "Registration failed: " . $e->getMessage();
            header("Location: register.html");
            exit();
        }
    } else {
        $_SESSION['errors'] = $errors;
        header("Location: register.html");
        exit();
    }
}
?> 