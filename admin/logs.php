<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM admin_logs ORDER BY created_at DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Logs - Space Link</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #1f2937; color: white; padding: 20px; }
        .sidebar a { display: block; color: #d1d5db; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 4px; }
        .sidebar a:hover, .sidebar a.active { background: #374151; color: white; }
        .main-content { flex: 1; padding: 40px; background: #f3f4f6; }
        .logs-table { width: 100%; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-collapse: collapse; }
        .logs-table th, .logs-table td { padding: 12px 20px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .logs-table th { background: #f9fafb; font-weight: 600; color: #374151; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <a href="index.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="venues.php">Venues</a>
            <a href="logs.php" class="active">Logs</a>
            <a href="../index.php">Back to Site</a>
        </div>
        <div class="main-content">
            <h1>System Logs</h1>
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Admin ID</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['id']; ?></td>
                        <td><?php echo $log['admin_id']; ?></td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                        <td><?php echo $log['created_at']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
