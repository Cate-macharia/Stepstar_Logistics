<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shipment_id'])) {
    $shipment_id = (int) $_POST['shipment_id'];

    $stmt = $conn->prepare("UPDATE shipments SET paid = 1, archived = 1 WHERE id = ?
");
    $stmt->bind_param("i", $shipment_id);
    $stmt->execute();
    
    // Respond with success status without redirect
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
