<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venue Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">VenueBook</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <h1>Find Unique Spaces for Your Next Event</h1>
            <p>Discover and book amazing venues for meetings, parties, and shoots.</p>
            <form action="index.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search by location or name..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <input type="number" name="min_price" placeholder="Min Price" style="width: 120px;" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                <input type="number" name="max_price" placeholder="Max Price" style="width: 120px;" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                <input type="number" name="capacity" placeholder="Capacity" style="width: 120px;" value="<?php echo htmlspecialchars($_GET['capacity'] ?? ''); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
    </header>

    <main class="container">
        <h2>Featured Venues</h2>
        <div class="venue-grid">
            <?php
            $search = $_GET['search'] ?? '';
            $min_price = $_GET['min_price'] ?? '';
            $max_price = $_GET['max_price'] ?? '';
            $capacity = $_GET['capacity'] ?? '';
            
            $venues = getVenues($pdo, $search, $min_price, $max_price, $capacity);
            foreach ($venues as $venue): ?>
                <div class="venue-card">
                    <img src="<?php echo htmlspecialchars($venue['image_url'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($venue['name']); ?>">
                    <div class="venue-info">
                        <h3><?php echo htmlspecialchars($venue['name']); ?></h3>
                        <p class="location"><?php echo htmlspecialchars($venue['address']); ?></p>
                        <p class="price">$<?php echo htmlspecialchars($venue['price_per_hour']); ?>/hr</p>
                        <a href="venue.php?id=<?php echo $venue['id']; ?>" class="btn">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> VenueBook. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
