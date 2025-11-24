<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['available' => false, 'error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['venue_id'], $data['date'], $data['start_time'], $data['duration'])) {
    echo json_encode(['available' => false, 'error' => 'Missing parameters']);
    exit;
}

$venue_id = $data['venue_id'];
$date = $data['date'];
$start_time = $data['date'] . ' ' . $data['start_time'];
$duration = (int)$data['duration'];
$end_time = date('Y-m-d H:i:s', strtotime($start_time . " +$duration hours"));

$is_available = checkAvailability($pdo, $venue_id, $start_time, $end_time);

echo json_encode(['available' => $is_available]);
?>
