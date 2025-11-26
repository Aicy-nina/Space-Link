<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle Edit
if (isset($_POST['edit_venue'])) {
    $venue_id = $_POST['venue_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $address = $_POST['address'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    
    // Get current image
    $stmt = $pdo->prepare("SELECT image_url FROM venues WHERE id = :id");
    $stmt->execute(['id' => $venue_id]);
    $current_venue = $stmt->fetch(PDO::FETCH_ASSOC);
    $image_url = $current_venue['image_url'];
    
    // Handle Image Upload (Optional update)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $relative_dir = '../uploads/';
        $absolute_dir = __DIR__ . '/' . $relative_dir;
        
        if (!is_dir($absolute_dir)) {
            mkdir($absolute_dir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $absolute_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = 'uploads/' . $filename;
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
    } catch (PDOException $e) {
        $message = "Error updating venue: " . $e->getMessage();
    }
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
        .btn-edit { background: #f59e0b; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;}
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 5% auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: #000; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        .btn-primary { background: #4f46e5; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary:hover { background: #4338ca; }
        .success-msg { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .error-msg { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2 style="margin-bottom: 30px;">Admin Panel</h2>
            <a href="index.php">Dashboard</a>
            <a href="users.php">Manage Users</a>
            <a href="venues.php" class="active">Manage Venues</a>
            <a href="bookings.php">Manage Bookings</a>
            <a href="../index.php">View Site</a>
            <a href="../logout.php" style="margin-top: auto; color: #ef4444;">Logout</a>
        </div>
        <div class="main-content">
            <h1>Manage Venues</h1>
            <?php if (isset($message)): ?>
                <div class="<?php echo strpos($message, 'Error') !== false ? 'error-msg' : 'success-msg'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
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
                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($venue)); ?>)" class="btn-sm btn-edit">Edit</button>
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

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Venue</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_venue" value="1">
                <input type="hidden" name="venue_id" id="edit_venue_id">
                
                <div class="form-group">
                    <label>Venue Name</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" id="edit_address" required>
                </div>
                
                <div class="form-group">
                    <label>Price per Day (sh)</label>
                    <input type="number" name="price" id="edit_price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" id="edit_capacity" required>
                </div>
                
                <div class="form-group">
                    <label>Change Image (Optional)</label>
                    <input type="file" name="image" accept="image/*">
                    <small id="current_image_info" style="color: #6b7280;"></small>
                </div>
                
                <button type="submit" class="btn-primary">Update Venue</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(venue) {
            document.getElementById('edit_venue_id').value = venue.id;
            document.getElementById('edit_name').value = venue.name;
            document.getElementById('edit_description').value = venue.description || '';
            document.getElementById('edit_address').value = venue.address;
            document.getElementById('edit_price').value = venue.price_per_day;
            document.getElementById('edit_capacity').value = venue.capacity;
            
            if (venue.image_url) {
                document.getElementById('current_image_info').textContent = 'Current image: ' + venue.image_url;
            } else {
                document.getElementById('current_image_info').textContent = 'No image uploaded';
            }
            
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
