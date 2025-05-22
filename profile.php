<?php
session_start();
require_once 'config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
        }
        .profile {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        h1 {
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-info {
            margin-bottom: 15px;
        }
        .profile-info label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="profile">
        <h1>User Profile</h1>

        <?php if ($user): ?>
            <div class="profile-info">
                <label>Name:</label>
                <p><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <div class="profile-info">
                <label>Email:</label>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="profile-info">
                <label>Phone:</label>
                <p><?php echo htmlspecialchars($user['phone']); ?></p>
            </div>
            <div class="profile-info">
                <label>Date of Birth:</label>
                <p><?php echo htmlspecialchars($user['dob']); ?></p>
            </div>
            <div class="profile-info">
                <label>Age:</label>
                <p><?php echo htmlspecialchars($user['age']); ?></p>
            </div>
        <?php else: ?>
            <p>User not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
