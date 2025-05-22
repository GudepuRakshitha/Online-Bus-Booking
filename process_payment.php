<?php
session_start();
require_once 'config/database.php';

// Enable error reporting for debugging (set to 0 in production)
ini_set('display_errors', 0); // Suppress warnings
error_reporting(E_ALL);

// Debug log function to track issues
function debugLog($message, $data = null) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if ($data) {
        $logMessage .= " - Data: " . print_r($data, true);
    }
    error_log($logMessage);
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user's name from the session
$user_name = $_SESSION['user_name'] ?? ''; // Assuming you stored the name in the session during login

try {
    // Get POST data
    $bus_id = $_POST['bus_id'] ?? '';
    $journey_date = $_POST['journey_date'] ?? '';
    $seats = $_POST['seats'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $upi_id = $_POST['upi_id'] ?? ''; // Get UPI ID
    $journey_time = $_POST['journey_time'] ?? ''; // Get journey time

    // Start database transaction
    $conn->beginTransaction();

    // Generate ticket ID
    $ticket_id = 'TICKET' . time() . rand(100, 999);
    $user_id = $_SESSION['user_id'];

    if (empty($bus_id) || empty($journey_date) || empty($seats) || $amount <= 0) {
        throw new Exception('Invalid payment details');
    }

    // Check if the payment method is valid
    if ($payment_method === 'google_pay' || $payment_method === 'phone_pe') {
        // Simulate successful payment
        // Insert booking details
        $stmt = $conn->prepare("INSERT INTO bookings (ticket_id, user_id, bus_id, journey_date, seats, amount, passenger_name, journey_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ticket_id, $user_id, $bus_id, $journey_date, $seats, $amount, $user_name, $journey_time]);

        // Commit transaction
        $conn->commit();

        // Display success message and ticket details
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Payment Successful</title>
            <link rel='stylesheet' href='assets/css/style.css'>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f8f9fa;
                    color: #333;
                    padding: 20px;
                }
                .ticket-details {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                h1 {
                    color: #28a745;
                }
                h2 {
                    margin-top: 20px;
                    color: #333;
                }
                p {
                    margin: 10px 0;
                }
                strong {
                    color: #555;
                }
            </style>
        </head>
        <body>
            <div class='ticket-details'>
                <h1>Payment Successful</h1>
                <h2>Your Ticket</h2>
                <p><strong>Ticket ID:</strong> " . htmlspecialchars($ticket_id) . "</p>
                <p><strong>User ID:</strong> " . htmlspecialchars($user_id) . "</p>
                <p><strong>Bus ID:</strong> " . htmlspecialchars($bus_id) . "</p>
                <p><strong>Seats:</strong> " . htmlspecialchars($seats) . "</p>
                <p><strong>Journey Date:</strong> " . htmlspecialchars($journey_date) . "</p>
                <p><strong>Total Amount:</strong> ₹" . number_format($amount, 2) . "</p>
            </div>
        </body>
        </html>";
        exit();
    } else {
        throw new Exception('Invalid payment method');
    }

} catch (Exception $e) {
    // Rollback transaction in case of failure
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // Handle error (you can log it or display a message)
    echo "Payment processing failed: " . htmlspecialchars($e->getMessage());
    exit();
}
?> 