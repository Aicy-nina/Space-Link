<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT id, name, image_url FROM venues ORDER BY id DESC LIMIT 5");
    $venues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Debug Venues</h1>";
    echo "<pre>";
    print_r($venues);
    echo "</pre>";
    
    echo "<h2>Uploads Directory Check</h2>";
    $files = scandir('uploads');
    print_r($files);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
