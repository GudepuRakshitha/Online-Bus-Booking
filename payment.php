<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$bus_id = $_GET['bus_id'] ?? '';
$journey_date = $_GET['date'] ?? '';
$selected_seats = explode(',', $_GET['seats'] ?? '');

// Fetch bus details from the database
$stmt = $conn->prepare("SELECT * FROM buses WHERE id = ?");
$stmt->execute([$bus_id]);
$bus = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate total amount
$total_amount = count($selected_seats) * ($bus['fare'] ?? 0);
$tax = $total_amount * 0.05; // 5% tax
$final_amount = $total_amount + $tax;
?>

<div class="payment-wrapper">
    <div class="payment-container">
        <div class="payment-header">
            <h2>Payment</h2>
            <div class="booking-summary">
                <div class="route">
                    <?php echo htmlspecialchars($bus['from_city'] ?? ''); ?> → 
                    <?php echo htmlspecialchars($bus['to_city'] ?? ''); ?>
                </div>
                <div class="journey-details">
                    <span><?php echo date('d M Y', strtotime($journey_date)); ?></span>
                    <span>Seats: <?php echo implode(', ', $selected_seats); ?></span>
                </div>
            </div>
        </div>

        <div class="amount-details">
            <div class="amount-row">
                <span>Base Fare</span>
                <span>₹<?php echo number_format($total_amount, 2); ?></span>
            </div>
            <div class="amount-row">
                <span>Tax (5%)</span>
                <span>₹<?php echo number_format($tax, 2); ?></span>
            </div>
            <div class="amount-row total">
                <span>Total Amount</span>
                <span>₹<?php echo number_format($final_amount, 2); ?></span>
            </div>
        </div>

        <form id="payment-form" action="process_payment.php" method="POST">
            <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($bus_id); ?>">
            <input type="hidden" name="journey_date" value="<?php echo htmlspecialchars($journey_date); ?>">
            <input type="hidden" name="seats" value="<?php echo htmlspecialchars(implode(',', $selected_seats)); ?>">
            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($final_amount); ?>">

            <h3>Select Payment Method:</h3>
            <button type="button" class="pay-button" onclick="showUPIInput('google_pay')">Google Pay</button>
            <button type="button" class="pay-button" onclick="showUPIInput('phone_pe')">PhonePe</button>
        </form>
    </div>
</div>

<!-- UPI Input Modal -->
<div id="upi-modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Enter UPI ID</h2>
        <input type="text" id="upi-id" placeholder="Enter your UPI ID">
        <button id="confirm-payment">Confirm Payment</button>
    </div>
</div>

<style>
.payment-wrapper {
    max-width: 500px;
    margin: 30px auto;
    padding: 20px;
}

.payment-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.payment-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.payment-header h2 {
    margin-bottom: 15px;
    color: #333;
}

.booking-summary {
    font-size: 0.9rem;
    color: #666;
}

.route {
    font-weight: 500;
    margin-bottom: 5px;
}

.amount-details {
    padding: 20px;
    background: #f8f9fa;
}

.amount-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #666;
}

.amount-row.total {
    font-weight: 600;
    color: #333;
    border-top: 1px solid #ddd;
    padding-top: 10px;
    margin-top: 10px;
}

.payment-methods {
    padding: 20px;
}

.payment-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.tab-btn {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    background: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.tab-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.payment-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group label {
    font-size: 0.9rem;
    color: #666;
}

.form-group input {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
}

.card-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.pay-button {
    background: var(--primary-color);
    color: white;
    padding: 15px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 10px;
}

.pay-button:hover {
    background: #0056b3;
}

.upi-apps {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 20px 0;
}

.upi-app-btn {
    background: none;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.upi-app-btn:hover {
    border-color: var(--primary-color);
}

.upi-app-btn img {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.hidden {
    display: none;
}

@media (max-width: 768px) {
    .payment-wrapper {
        padding: 10px;
    }
    
    .card-details {
        grid-template-columns: 1fr;
    }
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 300px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}
.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>

<script>
function showUPIInput(method) {
    document.getElementById('upi-modal').style.display = 'block';
    document.getElementById('confirm-payment').onclick = function() {
        const upiId = document.getElementById('upi-id').value;
        processPayment(method, upiId);
    };
}

function closeModal() {
    document.getElementById('upi-modal').style.display = 'none';
}

function processPayment(method, upiId) {
    const form = document.getElementById('payment-form');
    const formData = new FormData(form);
    formData.append('payment_method', method);
    formData.append('upi_id', upiId); // Append UPI ID

    // Simulate payment processing
    fetch('process_payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.body.innerHTML = data; // Display the response directly
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>