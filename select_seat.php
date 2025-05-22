<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$bus_id = $_GET['bus_id'] ?? '';
$journey_date = $_GET['date'] ?? '';

// Fetch bus and route details
try {
    $stmt = $conn->prepare("
        SELECT b.*, r.from_city, r.to_city, r.duration
        FROM buses b
        JOIN routes r ON b.route_id = r.id
        WHERE b.id = ?
    ");
    $stmt->execute([$bus_id]);
    $bus = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch seat status
    $stmt = $conn->prepare("
        SELECT seat_number, status, seat_type
        FROM seats
        WHERE bus_id = ?
    ");
    $stmt->execute([$bus_id]);
    $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<div class="seat-selection-wrapper">
    <div class="bus-details-header">
        <h2><?php echo htmlspecialchars($bus['from_city']); ?> → <?php echo htmlspecialchars($bus['to_city']); ?></h2>
        <p><?php echo date('d M Y', strtotime($journey_date)); ?> | <?php echo htmlspecialchars($bus['bus_type']); ?></p>
    </div>

    <div class="seat-layout-container">
        <div class="seat-indicators">
            <div class="indicator">
                <span class="seat available"></span>
                <label>Available</label>
            </div>
            <div class="indicator">
                <span class="seat booked"></span>
                <label>Booked</label>
            </div>
            <div class="indicator">
                <span class="seat selected"></span>
                <label>Selected</label>
            </div>
            <div class="indicator">
                <span class="seat ladies"></span>
                <label>Ladies</label>
            </div>
        </div>

        <div class="bus-layout">
            <div class="steering-wheel">
                <i class="fas fa-steering-wheel"></i>
            </div>
            
            <div class="seats-container">
                <?php
                $seatMap = [];
                foreach($seats as $seat) {
                    $seatMap[$seat['seat_number']] = $seat;
                }
                
                for($i = 1; $i <= 28; $i++) {
                    $seatStatus = $seatMap[$i]['status'] ?? 'available';
                    $seatType = $seatMap[$i]['seat_type'] ?? 'normal';
                    $seatClass = $seatStatus . ' ' . $seatType;
                    ?>
                    <div class="seat <?php echo $seatClass; ?>" 
                         data-seat="<?php echo $i; ?>"
                         data-status="<?php echo $seatStatus; ?>"
                         data-type="<?php echo $seatType; ?>">
                        <?php echo $i; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <div class="booking-summary">
        <div class="selected-seats">
            <h3>Selected Seats: <span id="selected-seats-numbers">-</span></h3>
            <p>Total Fare: ₹<span id="total-fare">0</span></p>
        </div>
        <button id="proceed-button" class="proceed-btn" disabled>Proceed to Payment</button>
    </div>
</div>

<style>
.seat-selection-wrapper {
    max-width: 900px;
    margin: 30px auto;
    padding: 20px;
}

.bus-details-header {
    background: white;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.seat-layout-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.seat-indicators {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.indicator {
    display: flex;
    align-items: center;
    gap: 8px;
}

.bus-layout {
    position: relative;
    padding-top: 40px;
}

.steering-wheel {
    position: absolute;
    top: 0;
    right: 20px;
    font-size: 24px;
    color: #666;
}

.seats-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    max-width: 400px;
    margin: 0 auto;
}

.seat {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.seat.available {
    background: #e9ecef;
    color: #495057;
}

.seat.booked {
    background: #dee2e6;
    color: #adb5bd;
    cursor: not-allowed;
}

.seat.selected {
    background: #4dabf7;
    color: white;
}

.seat.ladies {
    background: #f783ac;
    color: white;
}

.seat:not(.booked):hover {
    transform: scale(1.05);
}

.booking-summary {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.proceed-btn {
    background: var(--primary-color);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s;
}

.proceed-btn:disabled {
    background: #adb5bd;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .seat-indicators {
        flex-wrap: wrap;
    }
    
    .booking-summary {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seatsContainer = document.querySelector('.seats-container');
    const selectedSeatsSpan = document.getElementById('selected-seats-numbers');
    const totalFareSpan = document.getElementById('total-fare');
    const proceedButton = document.getElementById('proceed-button');
    const baseFare = <?php echo $bus['fare']; ?>;
    
    let selectedSeats = [];

    seatsContainer.addEventListener('click', function(e) {
        const seat = e.target.closest('.seat');
        if (!seat) return;
        
        if (seat.dataset.status === 'booked') return;
        
        const seatNumber = seat.dataset.seat;
        
        if (seat.classList.contains('selected')) {
            // Deselect seat
            seat.classList.remove('selected');
            selectedSeats = selectedSeats.filter(num => num !== seatNumber);
        } else {
            // Select seat
            if (selectedSeats.length >= 6) {
                alert('You can only select up to 6 seats');
                return;
            }
            seat.classList.add('selected');
            selectedSeats.push(seatNumber);
        }
        
        // Update summary
        selectedSeatsSpan.textContent = selectedSeats.length ? selectedSeats.join(', ') : '-';
        totalFareSpan.textContent = (selectedSeats.length * baseFare).toFixed(2);
        proceedButton.disabled = selectedSeats.length === 0;
    });

    proceedButton.addEventListener('click', function() {
        if (selectedSeats.length === 0) return;
        
        const queryParams = new URLSearchParams({
            bus_id: '<?php echo $bus_id; ?>',
            date: '<?php echo $journey_date; ?>',
            seats: selectedSeats.join(',')
        });
        
        window.location.href = 'payment.php?' + queryParams.toString();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 