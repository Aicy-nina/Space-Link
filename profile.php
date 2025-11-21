<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    
    // Optional: Update password logic could go here
    
    $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'id' => $user_id
        ]);
        $message = "Profile updated successfully!";
        // Update session data if needed
        $_SESSION['username'] = $user['username']; // Username doesn't change here, but good practice
        
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = "Error updating profile: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - VenueBook</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">VenueBook</a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 40px;">
        <div class="dashboard-section" style="max-width: 500px; margin: 0 auto;">
            <h2>Edit Profile</h2>
            <?php if ($message): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="profile.php" method="POST">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Username (Cannot be changed)</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background-color: #e5e7eb; cursor: not-allowed;">
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn">Save Changes</button>
                    <a href="dashboard.php" class="btn" style="background-color: #6b7280;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
