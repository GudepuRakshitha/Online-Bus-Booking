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

// Fetch booking details for the logged-in user, excluding bus name
$stmt = $conn->prepare("
    SELECT b.*, bu.departure_time, bu.arrival_time 
    FROM bookings AS b 
    JOIN buses AS bu ON b.bus_id = bu.id 
    WHERE b.user_id = ?
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
        }
        .my-bookings {
            max-width: 900px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .alert {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        /* Additional styles for better layout */
        td:last-child {
            text-align: center; /* Center align the action column */
        }
    </style>
</head>
<body>
    <div class="my-bookings">
        <h1>My Bookings</h1>

        <?php if (empty($bookings)): ?>
            <p>No bookings found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Passenger Name</th>
                        <th>Journey Date</th>
                        <th>Journey Time</th>
                        <th>Departure Time</th>
                        <th>Arrival Time</th>
                        <th>Seats</th>
                        <th>Total Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['ticket_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['passenger_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['journey_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['journey_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['departure_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['arrival_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['seats']); ?></td>
                            <td>₹<?php echo number_format($booking['amount'], 2); ?></td>
                            <td>
                                <a href="ticket_display.php?ticket_id=<?php echo htmlspecialchars($booking['ticket_id']); ?>">View Ticket</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
