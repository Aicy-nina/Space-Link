<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'host') {
    header("Location: login.php");
    exit;
}

$venue_id = $_GET['id'] ?? null;
if (!$venue_id) {
    header("Location: dashboard.php");
    exit;
}

$venue = getVenueById($pdo, $venue_id);

// Check if venue exists and belongs to the current host
if (!$venue || $venue['host_id'] !== $_SESSION['user_id']) {
    header("Location: dashboard.php");
    exit;
}
 
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $address = $_POST['address'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    
    // Handle Image Upload (Optional update)
    $image_url = $venue['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // Validate file type - only allow images
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
        $original_name = $_FILES['image']['name'];
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowed_extensions)) {
            $message = "Error: Only image files are allowed (JPG, JPEG, PNG, GIF, WEBP)";
        } else {
            $relative_dir = 'uploads/';
            $absolute_dir = __DIR__ . '/' . $relative_dir;
            
            if (!is_dir($absolute_dir)) {
                mkdir($absolute_dir, 0777, true);
            }

            // Convert jfif to jpg for better compatibility
            if ($extension === 'jfif') {
                $extension = 'jpg';
            }
            
            // Create a clean filename
            $filename = uniqid() . '.' . $extension;
            $target_file = $absolute_dir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = $relative_dir . $filename;
            }
        }
    }

    $sql = "UPDATE venues SET name = :name, description = :description, address = :address, 
            price_per_day = :price, capacity = :capacity, image_url = :image_url 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'address' => $address,
            'price' => $price,
            'capacity' => $capacity,
            'image_url' => $image_url,
            'id' => $venue_id
        ]);
        $message = "Venue updated successfully!";
        // Refresh venue data
        $venue = getVenueById($pdo, $venue_id);
    } catch (PDOException $e) {
        $message = "Error updating venue: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Venue - Space Link</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Space Link</a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 40px;">
        <div class="dashboard-section" style="max-width: 600px; margin: 0 auto;">
            <h2>Edit Venue</h2>
            <?php if ($message): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="edit_venue.php?id=<?php echo $venue_id; ?>" method="POST" enctype="multipart/form-data" class="venue-form">
                <div class="form-group">
                    <label>Venue Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($venue['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?php echo htmlspecialchars($venue['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($venue['address']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Price per Day (sh)</label>
                    <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($venue['price_per_day']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" value="<?php echo htmlspecialchars($venue['capacity']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Current Image</label>
                    <?php if ($venue['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($venue['image_url']); ?>" alt="Current Venue Image" style="width: 100px; height: auto; display: block; margin-bottom: 10px; border-radius: 4px;">
                    <?php else: ?>
                        <p>No image uploaded.</p>
                    <?php endif; ?>
                    <label>Change Image (Optional)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn">Update Venue</button>
                    <a href="dashboard.php" class="btn" style="background-color: #6b7280;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
