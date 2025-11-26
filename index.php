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
    <title>Space Link</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar"> 
        <div class="container">
            <a href="index.php" class="logo">Space Link</a>
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
                <input type="text" name="search" placeholder="Search by location, price, and capacity..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
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
            
            // Pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 6; // Venues per page
            $offset = ($page - 1) * $limit;

            $venues = getVenues($pdo, $search, $min_price, $max_price, $capacity, $limit, $offset);
            $total_venues = getTotalVenues($pdo, $search, $min_price, $max_price, $capacity);
            $total_pages = ceil($total_venues / $limit);

            foreach ($venues as $venue): ?>
                <div class="venue-card">
                    <img src="<?php echo htmlspecialchars($venue['image_url'] ?: 'assets/images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($venue['name']); ?>">
                    <div class="venue-info">
                        <h3><?php echo htmlspecialchars($venue['name']); ?></h3>
                        <p class="location"><?php echo htmlspecialchars($venue['address']); ?></p>
                        <p class="price">sh <?php echo htmlspecialchars($venue['price_per_day']); ?>/day</p>
                        <a href="venue.php?id=<?php echo $venue['id']; ?>" class="btn">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination Links -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination" style="margin-top: 30px; display: flex; justify-content: center; gap: 10px;">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="btn" style="background: #e5e7eb; color: #374151;">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="btn" style="<?php echo $i === $page ? '' : 'background: #e5e7eb; color: #374151;'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="btn" style="background: #e5e7eb; color: #374151;">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Space Link. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
