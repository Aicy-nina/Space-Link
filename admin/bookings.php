<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get all bookings
$bookings = $pdo->query("
    SELECT 
        b.*,
        v.name as venue_name,
        v.host_id,
        h.username as host_name,
        c.username as client_name,
        c.email as client_email
    FROM bookings b
    JOIN venues v ON b.venue_id = v.id
    JOIN users h ON v.host_id = h.id
    JOIN users c ON b.client_id = c.id
    ORDER BY b.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Calculate earnings summary
$total_revenue = 0;
$confirmed_bookings = 0;
$venue_earnings = [];

foreach ($bookings as $booking) {
    if ($booking['status'] === 'confirmed') {
        $total_revenue += $booking['total_price'];
        $confirmed_bookings++;
        
        // Track earnings by venue
        $venue_name = $booking['venue_name'];
        if (!isset($venue_earnings[$venue_name])) {
            $venue_earnings[$venue_name] = [
                'revenue' => 0,
                'bookings' => 0,
                'host' => $booking['host_name']
            ];
        }
        $venue_earnings[$venue_name]['revenue'] += $booking['total_price'];
        $venue_earnings[$venue_name]['bookings']++;
    }
}

$admin_commission = $total_revenue * 0.20;
$host_earnings = $total_revenue * 0.80;

// Sort venues by revenue
arsort($venue_earnings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings - Admin</title>
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
        .stat-label { color: #6b7280; font-size: 0.9rem; margin-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 30px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; font-size: 0.9rem; }
        td { font-size: 0.9rem; }
        
        .status-badge { padding: 4px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .status-badge.pending { background: #fef3c7; color: #92400e; }
        .status-badge.confirmed { background: #d1fae5; color: #065f46; }
        .status-badge.cancelled { background: #fee2e2; color: #991b1b; }
        
        .venue-breakdown { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .venue-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #e5e7eb; }
        .venue-item:last-child { border-bottom: none; }
        .venue-info h4 { margin: 0 0 5px 0; color: #1f2937; }
        .venue-info p { margin: 0; color: #6b7280; font-size: 0.85rem; }
        .venue-revenue { text-align: right; }
        .venue-revenue .amount { font-size: 1.5rem; font-weight: bold; color: #4f46e5; }
        .venue-revenue .commission { font-size: 0.85rem; color: #10b981; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2 style="margin-bottom: 30px;">Admin Panel</h2>
            <a href="index.php">Dashboard</a>
            <a href="users.php">Manage Users</a>
            <a href="venues.php">Manage Venues</a>
            <a href="bookings.php" class="active">Manage Bookings</a>
            <a href="../index.php">View Site</a>
            <a href="../logout.php" style="margin-top: auto; color: #ef4444;">Logout</a>
        </div>
        <div class="main-content">
            <h1>Manage Bookings</h1>
            
            <!-- Revenue Overview -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px;">
                <h2 style="color: white; margin-bottom: 20px;">Revenue Overview</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                    <div style="background: rgba(255, 255, 255, 0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                        <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">Total Revenue</div>
                        <div style="font-size: 2rem; font-weight: bold;">sh <?php echo number_format($total_revenue, 2); ?></div>
                        <div style="font-size: 0.8rem; opacity: 0.8; margin-top: 5px;"><?php echo $confirmed_bookings; ?> confirmed bookings</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                        <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">Admin Commission (20%)</div>
                        <div style="font-size: 2rem; font-weight: bold;">sh <?php echo number_format($admin_commission, 2); ?></div>
                    </div>
                    <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                        <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">Host Earnings (80%)</div>
                        <div style="font-size: 2rem; font-weight: bold;">sh <?php echo number_format($host_earnings, 2); ?></div>
                    </div>
                </div>
            </div>

            <!-- All Bookings Table -->
            <h2 style="margin-bottom: 15px;">All Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Venue</th>
                        <th>Host</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): 
                        $start = new DateTime($booking['start_time']);
                    ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['venue_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['host_name']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($booking['client_name']); ?><br>
                            <small style="color: #6b7280;">
                                <?php echo htmlspecialchars($booking['client_email']); ?></small>
                        </td>
                        <td><?php echo $start->format('M d, Y H:i'); ?></td>
                        <td>sh <?php echo number_format($booking['total_price'], 2); ?></td>
                        <td>
                            <span class="status-badge <?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Admin Earnings by Venue -->
            <h2 style="margin-bottom: 15px;">Admin Earnings by Venue</h2>
            <div class="venue-breakdown">
                <?php foreach ($venue_earnings as $venue_name => $data): 
                    $venue_commission = $data['revenue'] * 0.20;
                ?>
                <div class="venue-item">
                    <div class="venue-info">
                        <h4><?php echo htmlspecialchars($venue_name); ?></h4>
                        <p>Host: <?php echo htmlspecialchars($data['host']); ?> • <?php echo $data['bookings']; ?> bookings</p>
                    </div>
                    <div class="venue-revenue">
                        <div class="amount">sh <?php echo number_format($venue_commission, 2); ?></div>
                        <div class="commission">From total: sh <?php echo number_format($data['revenue'], 2); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
