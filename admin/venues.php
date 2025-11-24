<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle Delete
if (isset($_POST['delete_venue'])) {
    $stmt = $pdo->prepare("DELETE FROM venues WHERE id = :id");
    $stmt->execute(['id' => $_POST['venue_id']]);
    $message = "Venue deleted successfully.";
}

$venues = $pdo->query("
    SELECT v.*, u.username as host_name 
    FROM venues v 
    JOIN users u ON v.host_id = u.id 
    ORDER BY v.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Venues - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #1f2937; color: white; padding: 20px; }
        .sidebar a { display: block; color: #d1d5db; padding: 10px; text-decoration: none; margin-bottom: 5px; border-radius: 4px; }
        .sidebar a:hover, .sidebar a.active { background: #374151; color: white; }
        .main-content { flex: 1; padding: 30px; background: #f3f4f6; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; }
        .btn-sm { padding: 5px 10px; font-size: 0.8rem; }
        .btn-danger { background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-view { background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;}
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2 style="margin-bottom: 30px;">Admin Panel</h2>
            <a href="index.php">Dashboard</a>
            <a href="users.php">Manage Users</a>
            <a href="venues.php" class="active">Manage Venues</a>
            <a href="../index.php">View Site</a>
            <a href="../logout.php" style="margin-top: auto; color: #ef4444;">Logout</a>
        </div>
        <div class="main-content">
            <h1>Manage Venues</h1>
            <?php if (isset($message)) echo "<p style='color: green; margin-bottom: 15px;'>$message</p>"; ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Host</th>
                        <th>Price/Day</th>
                        <th>Capacity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($venues as $venue): ?>
                    <tr>
                        <td><?php echo $venue['id']; ?></td>
                        <td><?php echo htmlspecialchars($venue['name']); ?></td>
                        <td><?php echo htmlspecialchars($venue['host_name']); ?></td>
                        <td>sh <?php echo htmlspecialchars($venue['price_per_day']); ?></td>
                        <td><?php echo htmlspecialchars($venue['capacity']); ?></td>
                        <td>
                            <a href="../venue.php?id=<?php echo $venue['id']; ?>" target="_blank" class="btn-sm btn-view">View</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                                <button type="submit" name="delete_venue" class="btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
