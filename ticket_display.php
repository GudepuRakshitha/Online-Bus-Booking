<?php
session_start();
require_once 'config/database.php';

// Check if ticket_id is provided
$ticket_id = $_GET['ticket_id'] ?? '';

if (empty($ticket_id)) {
    echo "No ticket ID provided.";
    exit();
}

// Fetch booking details from the database
$stmt = $conn->prepare("SELECT * FROM bookings WHERE ticket_id = ?");
$stmt->execute([$ticket_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "No booking found for this ticket ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="ticket-details">
        <h1>Your Ticket</h1>
        <div class="ticket-info">
            <h2>Booking Information</h2>
            <p><strong>Ticket ID:</strong> <?php echo htmlspecialchars($booking['ticket_id']); ?></p>
            <p><strong>Passenger Name:</strong> <?php echo htmlspecialchars($booking['passenger_name']); ?></p>
            <p><strong>User ID:</strong> <?php echo htmlspecialchars($booking['user_id']); ?></p>
            <p><strong>Bus ID:</strong> <?php echo htmlspecialchars($booking['bus_id']); ?></p>
            <p><strong>Seats:</strong> <?php echo htmlspecialchars($booking['seats']); ?></p>
            <p><strong>Journey Date:</strong> <?php echo htmlspecialchars($booking['journey_date']); ?></p>
            <p><strong>Journey Time:</strong> <?php echo htmlspecialchars($booking['journey_time']); ?></p>
            <p><strong>Total Amount:</strong> ₹<?php echo number_format($booking['amount'], 2); ?></p>
        </div>
        <div class="bus-details">
            <h2>Bus Details</h2>
            <!-- You can add more bus-related information here if needed -->
            <p><strong>Bus Type:</strong> [Bus Type Here]</p> <!-- Replace with actual bus type if available -->
            <p><strong>Departure Time:</strong> [Departure Time Here]</p> <!-- Replace with actual departure time if available -->
            <p><strong>Arrival Time:</strong> [Arrival Time Here]</p> <!-- Replace with actual arrival time if available -->
        </div>
    </div>
</body>
</html>
