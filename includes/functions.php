<?php
function getVenues($pdo, $search = '', $min_price = '', $max_price = '', $capacity = '', $limit = 10, $offset = 0) {
    $sql = "SELECT * FROM venues WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (name LIKE :search OR address LIKE :search)";
        $params['search'] = "%$search%";
    }
    if ($min_price) {
        $sql .= " AND price_per_day >= :min_price";
        $params['min_price'] = $min_price;
    }
    if ($max_price) {
        $sql .= " AND price_per_day <= :max_price";
        $params['max_price'] = $max_price;
    }
    if ($capacity) {
        $sql .= " AND capacity >= :capacity";
        $params['capacity'] = $capacity;
    }

    $sql .= " LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    // Bind params
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    
    $stmt->execute();
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
function checkAvailability($pdo, $venue_id, $start_time, $end_time) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM bookings 
        WHERE venue_id = :venue_id 
        AND status != 'cancelled'
        AND (
            (start_time < :end_time AND end_time > :start_time)
        )
    ");
    $stmt->execute([
        'venue_id' => $venue_id,
        'start_time' => $start_time,
        'end_time' => $end_time
    ]);
    return $stmt->fetchColumn() == 0;
}

function getTotalVenues($pdo, $search = '', $min_price = '', $max_price = '', $capacity = '') {
    $sql = "SELECT COUNT(*) FROM venues WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (name LIKE :search OR address LIKE :search)";
        $params['search'] = "%$search%";
    }
    if ($min_price) {
        $sql .= " AND price_per_day >= :min_price";
        $params['min_price'] = $min_price;
    }
    if ($max_price) {
        $sql .= " AND price_per_day <= :max_price";
        $params['max_price'] = $max_price;
    }
    if ($capacity) {
        $sql .= " AND capacity >= :capacity";
        $params['capacity'] = $capacity;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getHostRevenue($pdo, $host_id) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            COALESCE(SUM(b.total_price), 0) as total_revenue
        FROM bookings b
        JOIN venues v ON b.venue_id = v.id
        WHERE v.host_id = :host_id AND b.status = 'confirmed'
    ");
    $stmt->execute(['host_id' => $host_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_revenue = $result['total_revenue'];
    $admin_commission = $total_revenue * 0.20; // 20% commission
    $host_earnings = $total_revenue * 0.80; // 80% for host
    
    return [
        'total_bookings' => $result['total_bookings'],
        'total_revenue' => $total_revenue,
        'admin_commission' => $admin_commission,
        'host_earnings' => $host_earnings
    ];
}

function getTotalAdminCommission($pdo) {
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_price), 0) as total_revenue
        FROM bookings
        WHERE status = 'confirmed'
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_revenue'] * 0.20; // 20% commission
}
?>
