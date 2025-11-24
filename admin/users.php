<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle Delete
if (isset($_POST['delete_user'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $_POST['user_id']]);
    $message = "User deleted successfully.";
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2 style="margin-bottom: 30px;">Admin Panel</h2>
            <a href="index.php">Dashboard</a>
            <a href="users.php" class="active">Manage Users</a>
            <a href="venues.php">Manage Venues</a>
            <a href="../index.php">View Site</a>
            <a href="../logout.php" style="margin-top: auto; color: #ef4444;">Logout</a>
        </div>
        <div class="main-content">
            <h1>Manage Users</h1>
            <?php if (isset($message)) echo "<p style='color: green; margin-bottom: 15px;'>$message</p>"; ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span style="padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; background: <?php echo $user['role'] == 'admin' ? '#fee2e2; color: #991b1b' : ($user['role'] == 'host' ? '#d1fae5; color: #065f46' : '#e0f2fe; color: #075985'); ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn-sm btn-danger">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
