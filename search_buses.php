<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

try {
    $query = "
        SELECT 
            b.id as bus_id,
            b.bus_number,
            b.bus_type,
            b.total_seats,
            b.fare,
            b.departure_time,
            b.arrival_time,
            r.from_city,
            r.to_city,
            r.distance,
            r.duration,
            (
                SELECT COUNT(*) 
                FROM seats s 
                WHERE s.bus_id = b.id 
                AND s.status = 'available'
            ) as available_seats
        FROM buses b
        JOIN routes r ON b.route_id = r.id
        WHERE r.from_city = :from 
        AND r.to_city = :to
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute(['from' => $from, 'to' => $to]);
    $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $buses = [];
    echo "Error: " . $e->getMessage();
}
?>

<div class="search-results-container">
    <div class="search-summary">
        <h2><?php echo htmlspecialchars($from); ?> → <?php echo htmlspecialchars($to); ?></h2>
        <p>Journey Date: <?php echo date('d M Y', strtotime($date)); ?></p>
    </div>

    <?php if(empty($buses)): ?>
        <div class="no-results">
            <i class="fas fa-bus"></i>
            <h3>No buses found for this route</h3>
            <p>Try searching for a different date or route</p>
            <a href="index.php" class="back-btn">Search Again</a>
        </div>
    <?php else: ?>
        <div class="bus-list">
            <?php foreach($buses as $bus): ?>
                <div class="bus-card">
                    <div class="bus-info">
                        <div class="bus-primary-info">
                            <h3><?php echo htmlspecialchars($bus['bus_type']); ?></h3>
                            <p class="bus-number"><?php echo htmlspecialchars($bus['bus_number']); ?></p>
                        </div>
                        
                        <div class="bus-time-info">
                            <div class="departure">
                                <h4><?php echo date('H:i', strtotime($bus['departure_time'])); ?></h4>
                                <p><?php echo htmlspecialchars($from); ?></p>
                            </div>
                            <div class="duration">
                                <p><?php echo htmlspecialchars($bus['duration']); ?></p>
                                <div class="duration-line"></div>
                            </div>
                            <div class="arrival">
                                <h4><?php echo date('H:i', strtotime($bus['arrival_time'])); ?></h4>
                                <p><?php echo htmlspecialchars($to); ?></p>
                            </div>
                        </div>

                        <div class="bus-details">
                            <p>Available Seats: <span class="seats"><?php echo $bus['available_seats']; ?></span></p>
                            <p>Fare: <span class="fare">₹<?php echo number_format($bus['fare'], 2); ?></span></p>
                        </div>
                    </div>

                    <div class="bus-actions">
                        <a href="select_seat.php?bus_id=<?php echo $bus['bus_id']; ?>&date=<?php echo htmlspecialchars($date); ?>" 
                           class="select-seats-btn">Select Seats</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Search Results Styles */
.search-results-container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 0 20px;
}

.search-summary {
    margin-bottom: 20px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.search-summary h2 {
    color: var(--text-color);
    font-size: 1.5rem;
}

.bus-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bus-info {
    flex: 1;
}

.bus-primary-info h3 {
    color: var(--text-color);
    margin-bottom: 5px;
}

.bus-number {
    color: #666;
    font-size: 0.9rem;
}

.bus-time-info {
    display: flex;
    align-items: center;
    margin: 20px 0;
    gap: 20px;
}

.duration {
    flex: 1;
    text-align: center;
}

.duration-line {
    height: 2px;
    background: #ddd;
    position: relative;
    margin: 10px 0;
}

.duration-line::before,
.duration-line::after {
    content: '';
    position: absolute;
    width: 8px;
    height: 8px;
    background: #ddd;
    border-radius: 50%;
    top: -3px;
}

.duration-line::before {
    left: 0;
}

.duration-line::after {
    right: 0;
}

.bus-details {
    display: flex;
    gap: 20px;
}

.seats, .fare {
    font-weight: 600;
    color: var(--primary-color);
}

.select-seats-btn {
    background: var(--primary-color);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s;
}

.select-seats-btn:hover {
    background: #0056b3;
}

.no-results {
    text-align: center;
    padding: 50px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.no-results i {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 20px;
}

.back-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

@media (max-width: 768px) {
    .bus-card {
        flex-direction: column;
    }

    .bus-actions {
        margin-top: 20px;
        width: 100%;
    }

    .select-seats-btn {
        display: block;
        text-align: center;
    }

    .bus-time-info {
        flex-direction: column;
        gap: 10px;
    }

    .bus-details {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 