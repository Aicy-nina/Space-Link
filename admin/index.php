<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get stats
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM venues");
$total_venues = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
$total_bookings = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Space Link</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #1f2937; color: white; padding: 20px; }
        .sidebar a { display: block; color: #d1d5db; padding: 10px; text-decoration: none; margin-bottom: 5px; border-radius: 4px; }
        .sidebar a:hover, .sidebar a.active { background: #374151; color: white; }
        .main-content { flex: 1; padding: 30px; background: #f3f4f6; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #4f46e5; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2 style="margin-bottom: 30px;">Admin Panel</h2>
            <a href="index.php" class="active">Dashboard</a>
            <a href="users.php">Manage Users</a>
            <a href="venues.php">Manage Venues</a>
            <a href="../index.php">View Site</a>
            <a href="../logout.php" style="margin-top: auto; color: #ef4444;">Logout</a>
        </div>
        <div class="main-content">
            <h1>Dashboard Overview</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="stat-number"><?php echo $total_users; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Venues</h3>
                    <div class="stat-number"><?php echo $total_venues; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <div class="stat-number"><?php echo $total_bookings; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
