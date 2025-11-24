<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = '';

// Handle Venue Creation (Host only)
if ($role === 'host' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_venue'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $address = $_POST['address'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    
    // Handle Image Upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $relative_dir = 'uploads/';
        $absolute_dir = __DIR__ . '/' . $relative_dir;
        
        if (!is_dir($absolute_dir)) {
            if (!mkdir($absolute_dir, 0777, true)) {
                $message = "Error: Failed to create uploads directory at " . $absolute_dir;
            }
        }
        
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $absolute_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = $relative_dir . $filename;
        } else {
            $message = "Error: Failed to move uploaded file to " . $target_file . 
                       " | Tmp: " . $_FILES['image']['tmp_name'] . 
                       " | Err: " . $_FILES['image']['error'] .
                       " | Dir Exists: " . (is_dir($absolute_dir) ? 'Yes' : 'No') .
                       " | Writable: " . (is_writable($absolute_dir) ? 'Yes' : 'No');
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $message = "Upload Error Code: " . $_FILES['image']['error'];
    }

    $sql = "INSERT INTO venues (host_id, name, description, address, price_per_day, capacity, image_url) 
            VALUES (:host_id, :name, :description, :address, :price, :capacity, :image_url)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([
            'host_id' => $user_id,
            'name' => $name,
            'description' => $description,
            'address' => $address,
            'price' => $price,
            'capacity' => $capacity,
            'image_url' => $image_url
        ]);
        $message = "Venue created successfully!";
    } catch (PDOException $e) {
        $message = "Error creating venue: " . $e->getMessage();
    }
}

// Handle Booking Status Update (Host only)
if ($role === 'host' && isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $booking_id]);
    $message = "Booking updated.";
}

// Handle Venue Deletion (Host only)
if ($role === 'host' && isset($_POST['delete_venue'])) {
    $venue_id = $_POST['venue_id'];
    // Verify ownership
    $stmt = $pdo->prepare("SELECT host_id FROM venues WHERE id = :id");
    $stmt->execute(['id' => $venue_id]);
    $venue = $stmt->fetch();
    
    if ($venue && $venue['host_id'] == $user_id) {
        $stmt = $pdo->prepare("DELETE FROM venues WHERE id = :id");
        $stmt->execute(['id' => $venue_id]);
        $message = "Venue deleted successfully.";
    } else {
        $message = "Error: You don't have permission to delete this venue.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Space Link</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Space Link</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <?php if ($role === 'admin'): ?>
                    <a href="admin/index.php">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <a href="profile.php" class="btn" style="padding: 8px 16px; font-size: 0.9rem;">Edit Profile</a>
        </div>

        <?php if ($message): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($role === 'host'): ?>
            <div class="dashboard-section">
                <h2>Add New Venue</h2>
                <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="venue-form">
                    <input type="hidden" name="create_venue" value="1">
                    <div class="form-group">
                        <label>Venue Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" required>
                    </div>
                    <div class="form-group">
                        <label>Price per Day (sh)</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Capacity</label>
                        <input type="number" name="capacity" required>
                    </div>
                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn">Publish Venue</button>
                </form>
            </div>

            <div class="dashboard-section">
                <h2>Your Venues</h2>
                <div class="venue-grid">
                    <?php
                    $my_venues = getHostVenues($pdo, $user_id);
                    foreach ($my_venues as $venue): ?>
                        <div class="venue-card">
                            <img src="<?php echo htmlspecialchars($venue['image_url'] ?: 'assets/images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($venue['name']); ?>">
                            <div class="venue-info">
                                <h3><?php echo htmlspecialchars($venue['name']); ?></h3>
                                <p>sh <?php echo htmlspecialchars($venue['price_per_day']); ?>/day</p>
                                <div style="display: flex; gap: 10px; margin-top: 10px;">
                                    <a href="venue.php?id=<?php echo $venue['id']; ?>" class="btn" style="flex: 1; text-align: center;">View</a>
                                    <a href="edit_venue.php?id=<?php echo $venue['id']; ?>" class="btn" style="flex: 1; text-align: center; background-color: #f59e0b;">Edit</a>
                                    <form action="dashboard.php" method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to delete this venue?');">
                                        <input type="hidden" name="delete_venue" value="1">
                                        <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                                        <button type="submit" class="btn full-width" style="background-color: #ef4444;">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Manage Bookings</h2>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Venue</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $host_bookings = getHostBookings($pdo, $user_id);
                        foreach ($host_bookings as $booking): 
                            $start = new DateTime($booking['start_time']);
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['venue_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($booking['client_name']); ?><br>
                                    <small><?php echo htmlspecialchars($booking['client_email']); ?></small>
                                </td>
                                <td><?php echo $start->format('M d, Y H:i'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <form action="dashboard.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="update_status" value="1">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="status" value="confirmed" class="btn" style="background-color: #10b981; padding: 5px 10px; font-size: 0.8rem;">Approve</button>
                                            <button type="submit" name="status" value="cancelled" class="btn" style="background-color: #ef4444; padding: 5px 10px; font-size: 0.8rem;">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="dashboard-section">
                <h2>Your Bookings</h2>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $bookings = getUserBookings($pdo, $user_id);
                        foreach ($bookings as $booking): 
                            $start = new DateTime($booking['start_time']);
                            $end = new DateTime($booking['end_time']);
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['venue_name']); ?></td>
                                <td><?php echo $start->format('M d, Y'); ?></td>
                                <td><?php echo $start->format('H:i') . ' - ' . $end->format('H:i'); ?></td>
                                <td>$<?php echo htmlspecialchars($booking['total_price']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
