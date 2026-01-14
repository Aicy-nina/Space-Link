<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$venue = getVenueById($pdo, $_GET['id']);
if (!$venue) {
    die("Venue not found.");
}

$message = '';
// Booking logic moved to checkout.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($venue['name']); ?> - Space Link</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Space Link</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 40px;">
        <?php if ($message): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="venue-details-grid">
            <div class="venue-main">
                <!-- === PRESENTATION POINT: IMAGE DISPLAY === -->
                <!-- This is where the uploaded image is shown to the user -->
                <img src="<?php echo htmlspecialchars($venue['image_url'] ?: 'assets/images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($venue['name']); ?>" style="width: 100%; border-radius: 12px; margin-bottom: 20px;">
                <h1><?php echo htmlspecialchars($venue['name']); ?></h1>
                <p class="location" style="font-size: 1.1rem;"><?php echo htmlspecialchars($venue['address']); ?></p>
                <div class="description" style="margin-top: 20px; line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($venue['description'])); ?>
                </div>
                <div class="specs" style="margin-top: 20px; display: flex; gap: 20px;">
                    <span><strong>Capacity:</strong> <?php echo $venue['capacity']; ?> people</span>
                    <span><strong>Price:</strong> sh <?php echo $venue['price_per_day']; ?>/day</span>
                </div>
            </div>

            <div class="booking-sidebar">
                <div class="booking-card">
                    <h3>Book this Space</h3>
                    <p class="price-tag">sh <?php echo $venue['price_per_day']; ?> <span>/ day</span></p>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- === PRESENTATION POINT: BOOKING FORM === -->
                        <!-- This form collects the booking details (date, time, duration) -->
                        <form action="checkout.php" method="POST">
                            <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                            <input type="hidden" name="price_per_day" value="<?php echo $venue['price_per_day']; ?>">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" name="start_time" required>
                            </div>
                            <div class="form-group">
                                <label>Duration (days)</label>
                                <input type="number" id="duration" name="duration" min="1" value="1" required>
                            </div>
                            
                            <div style="margin-top: 15px; padding: 10px; background: #f3f4f6; border-radius: 6px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <span>Total Price:</span>
                                    <strong id="total-price">sh <?php echo $venue['price_per_day']; ?></strong>
                                </div>
                                <div id="availability-status" style="font-size: 0.9rem; text-align: right;"></div>
                            </div>

                            <button type="submit" id="book-btn" class="btn full-width" style="margin-top: 15px;">Proceed to Checkout</button>
                        </form>

                        <script>
                            const pricePerDay = <?php echo $venue['price_per_day']; ?>;
                            const venueId = <?php echo $venue['id']; ?>;
                            const dateInput = document.querySelector('input[name="date"]');
                            const timeInput = document.querySelector('input[name="start_time"]');
                            const durationInput = document.getElementById('duration');
                            const totalPriceEl = document.getElementById('total-price');
                            const statusEl = document.getElementById('availability-status');
                            const bookBtn = document.getElementById('book-btn');

                            function updatePrice() {
                                const duration = parseInt(durationInput.value) || 0;
                                const total = (duration * pricePerDay).toFixed(2);
                                totalPriceEl.textContent = 'sh ' + total;
                            }

                            async function checkAvailability() {
                                const date = dateInput.value;
                                const time = timeInput.value;
                                const duration = durationInput.value;

                                if (!date || !time || !duration) return;

                                statusEl.textContent = 'Checking availability...';
                                statusEl.style.color = '#6b7280';
                                bookBtn.disabled = true;

                                try {
                                    const response = await fetch('api/check_availability.php', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ venue_id: venueId, date, start_time: time, duration }) // duration is now days
                                    });
                                    const data = await response.json();

                                    if (data.available) {
                                        statusEl.textContent = 'Available';
                                        statusEl.style.color = '#059669';
                                        bookBtn.disabled = false;
                                    } else {
                                        statusEl.textContent = 'Not Available';
                                        statusEl.style.color = '#dc2626';
                                        bookBtn.disabled = true;
                                    }
                                } catch (error) {
                                    console.error('Error checking availability:', error);
                                    statusEl.textContent = 'Error checking availability';
                                }
                            }

                            durationInput.addEventListener('input', () => {
                                updatePrice();
                                checkAvailability();
                            });
                            dateInput.addEventListener('change', checkAvailability);
                            timeInput.addEventListener('change', checkAvailability);
                        </script>
                    <?php else: ?>
                        <p>Please <a href="login.php">login</a> to book this venue.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>Reviews</h2>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
                if (isset($_SESSION['user_id'])) {
                    addReview($pdo, $venue['id'], $_SESSION['user_id'], $_POST['rating'], $_POST['comment']);
                    echo "<p style='color: green;'>Review submitted!</p>";
                } else {
                    echo "<p style='color: red;'>Please login to review.</p>";
                }
            }
            
            $reviews = getReviews($pdo, $venue['id']);
            if (count($reviews) > 0):
                foreach ($reviews as $review): ?>
                    <div style="border-bottom: 1px solid #e5e7eb; padding: 15px 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                            <span style="color: #f59e0b;">★ <?php echo $review['rating']; ?>/5</span>
                        </div>
                        <p><?php echo htmlspecialchars($review['comment']); ?></p>
                        <small style="color: #6b7280;"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                    </div>
                <?php endforeach;
            else: ?>
                <p>No reviews yet. Be the first to review!</p>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <h3 style="margin-top: 30px; margin-bottom: 15px;">Leave a Review</h3>
                <form action="venue.php?id=<?php echo $venue['id']; ?>" method="POST">
                    <input type="hidden" name="submit_review" value="1">
                    <div class="form-group">
                        <label>Rating</label>
                        <select name="rating" required>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Average</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Terrible</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Comment</label>
                        <textarea name="comment" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn">Submit Review</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
