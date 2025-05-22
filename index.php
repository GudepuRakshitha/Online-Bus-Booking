<?php
require_once 'config/database.php';
require_once 'includes/header.php';

// Fetch all unique cities for dropdowns
$stmt = $conn->query("SELECT DISTINCT from_city FROM routes ORDER BY from_city");
$from_cities = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $conn->query("SELECT DISTINCT to_city FROM routes ORDER BY to_city");
$to_cities = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="main-container">
    <div class="hero-section">
        <h1>We're going on a trip.</h1>
        <p>Book your bus tickets with ease</p>
    </div>

    <div class="search-container">
        <form id="searchForm" action="search_buses.php" method="GET">
            <div class="search-grid">
                <div class="form-group">
                    <label for="from">From</label>
                    <select name="from" id="from" required>
                        <option value="">Select Source City</option>
                        <?php foreach($from_cities as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>">
                                <?php echo htmlspecialchars($city); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="to">To</label>
                    <select name="to" id="to" required>
                        <option value="">Select Destination City</option>
                        <?php foreach($to_cities as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>">
                                <?php echo htmlspecialchars($city); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Journey Date</label>
                    <input type="date" id="date" name="date" required 
                           min="<?php echo date('Y-m-d'); ?>" 
                           value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <button type="submit" class="search-btn">Search Buses</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent selecting same source and destination
    const fromSelect = document.getElementById('from');
    const toSelect = document.getElementById('to');

    fromSelect.addEventListener('change', function() {
        Array.from(toSelect.options).forEach(option => {
            option.disabled = option.value === this.value;
        });
    });

    toSelect.addEventListener('change', function() {
        Array.from(fromSelect.options).forEach(option => {
            option.disabled = option.value === this.value;
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 