<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shipment_id'])) {
    $shipment_id = (int) $_POST['shipment_id'];

    $stmt = $conn->prepare("UPDATE shipments SET paid = 1, archived = 1 WHERE id = ?");
    $stmt->bind_param("i", $shipment_id);

    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid request']);
