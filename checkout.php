<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    // Simulate payment processing
    sleep(1); // Fake delay
    
    $venue_id = $_POST['venue_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];
    $total_price = $_POST['total_price'];
    
    $start_datetime = $date . ' ' . $start_time;
    $end_datetime = date('Y-m-d H:i:s', strtotime($start_datetime . " +$duration hours"));
    
    $sql = "INSERT INTO bookings (venue_id, client_id, start_time, end_time, total_price, status) 
            VALUES (:venue_id, :client_id, :start_time, :end_time, :total_price, 'pending')";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            'venue_id' => $venue_id,
            'client_id' => $_SESSION['user_id'],
            'start_time' => $start_datetime,
            'end_time' => $end_datetime,
            'total_price' => $total_price
        ]);
        header("Location: dashboard.php?booking_success=1");
        exit;
    } catch (PDOException $e) {
        $error = "Booking failed: " . $e->getMessage();
    }
}

// Data passed from venue.php
$venue_id = $_POST['venue_id'] ?? '';
$date = $_POST['date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$duration = $_POST['duration'] ?? '';
$price_per_hour = $_POST['price_per_hour'] ?? 0;
$total_price = $price_per_hour * $duration;

if (!$venue_id) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - VenueBook</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">VenueBook</a>
        </div>
    </nav>

    <div class="auth-container" style="max-width: 500px;">
        <h2>Checkout</h2>
        <div style="margin-bottom: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
            <h3>Order Summary</h3>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($date); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars($start_time); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($duration); ?> hours</p>
            <p style="font-size: 1.2rem; margin-top: 10px; color: var(--primary-color);"><strong>Total: $<?php echo number_format($total_price, 2); ?></strong></p>
        </div>

        <form action="checkout.php" method="POST">
            <input type="hidden" name="process_payment" value="1">
            <input type="hidden" name="venue_id" value="<?php echo $venue_id; ?>">
            <input type="hidden" name="date" value="<?php echo $date; ?>">
            <input type="hidden" name="start_time" value="<?php echo $start_time; ?>">
            <input type="hidden" name="duration" value="<?php echo $duration; ?>">
            <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">

            <div class="form-group">
                <label>Card Number</label>
                <input type="text" placeholder="0000 0000 0000 0000" required maxlength="19">
            </div>
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Expiry</label>
                    <input type="text" placeholder="MM/YY" required maxlength="5">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>CVV</label>
                    <input type="text" placeholder="123" required maxlength="3">
                </div>
            </div>
            <div class="form-group">
                <label>Cardholder Name</label>
                <input type="text" placeholder="John Doe" required>
            </div>

            <button type="submit" class="btn full-width">Pay & Book</button>
        </form>
    </div>
</body>
</html>
