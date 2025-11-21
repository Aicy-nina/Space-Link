<?php
function getVenues($pdo, $search = '', $min_price = '', $max_price = '', $capacity = '') {
    $sql = "SELECT * FROM venues WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (name LIKE :search OR address LIKE :search)";
        $params['search'] = "%$search%";
    }
    if ($min_price) {
        $sql .= " AND price_per_hour >= :min_price";
        $params['min_price'] = $min_price;
    }
    if ($max_price) {
        $sql .= " AND price_per_hour <= :max_price";
        $params['max_price'] = $max_price;
    }
    if ($capacity) {
        $sql .= " AND capacity >= :capacity";
        $params['capacity'] = $capacity;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getVenueById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM venues WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserBookings($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT b.*, v.name as venue_name 
        FROM bookings b 
        JOIN venues v ON b.venue_id = v.id 
        WHERE b.client_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHostVenues($pdo, $host_id) {
    $stmt = $pdo->prepare("SELECT * FROM venues WHERE host_id = :host_id");
    $stmt->execute(['host_id' => $host_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHostBookings($pdo, $host_id) {
    $stmt = $pdo->prepare("
        SELECT b.*, v.name as venue_name, u.username as client_name, u.email as client_email
        FROM bookings b 
        JOIN venues v ON b.venue_id = v.id 
        JOIN users u ON b.client_id = u.id
        WHERE v.host_id = :host_id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute(['host_id' => $host_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getReviews($pdo, $venue_id) {
    $stmt = $pdo->prepare("
        SELECT r.*, u.username 
        FROM reviews r 
        JOIN users u ON r.client_id = u.id 
        WHERE r.venue_id = :venue_id 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute(['venue_id' => $venue_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addReview($pdo, $venue_id, $client_id, $rating, $comment) {
    $sql = "INSERT INTO reviews (venue_id, client_id, rating, comment) VALUES (:venue_id, :client_id, :rating, :comment)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'venue_id' => $venue_id,
        'client_id' => $client_id,
        'rating' => $rating,
        'comment' => $comment
    ]);
}
?>
