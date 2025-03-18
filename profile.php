<?php
session_start();
require_once "config/database.php";
require_once "models/User.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Get user profile
$profile = $user->getProfile($_SESSION['user_id']);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => $_POST['full_name'],
        'phone_number' => $_POST['phone_number'],
        'date_of_birth' => $_POST['date_of_birth'],
        'address' => $_POST['address'],
        'bio' => $_POST['bio']
    ];

    if ($user->updateProfile($_SESSION['user_id'], $data)) {
        $success_message = "Profile updated successfully!";
        $profile = $user->getProfile($_SESSION['user_id']); // Refresh profile data
    } else {
        $error_message = "Failed to update profile.";
    }
}

// Handle password update
if (isset($_POST['update_password'])) {
    if (password_verify($_POST['current_password'], $profile['password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            if ($user->updatePassword($_SESSION['user_id'], $_POST['new_password'])) {
                $success_message = "Password updated successfully!";
            } else {
                $error_message = "Failed to update password.";
            }
        } else {
            $error_message = "New passwords do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Kristian Tours</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 80px auto 0;
            padding: 20px;
        }
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .profile-title {
            font-size: 24px;
            color: #2c3e50;
        }
        .profile-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .profile-section h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .profile-button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .profile-button:hover {
            background-color: #2980b9;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <h1 class="profile-title">My Profile</h1>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin/dashboard.php" class="profile-button">Admin Dashboard</a>
            <?php endif; ?>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="profile-section">
            <h2>Profile Information</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($profile['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($profile['email']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($profile['phone_number']); ?>">
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $profile['date_of_birth']; ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?php echo htmlspecialchars($profile['address']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio"><?php echo htmlspecialchars($profile['bio']); ?></textarea>
                </div>
                <button type="submit" class="profile-button">Update Profile</button>
            </form>
        </div>

        <div class="profile-section">
            <h2>Change Password</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="update_password" class="profile-button">Update Password</button>
            </form>
        </div>
    </div>
</body>
</html> 