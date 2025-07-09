<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../config.php';

header('Content-Type: application/json');

// Verify that the request is coming from an authenticated user
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. You must be logged in.']);
    exit;
}

// Check if it's an AJAX request
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Farmer ID is missing.']);
    exit;
}

$farmer_id = intval($_GET['id']);

try {
    // Verify the farmer exists
    $stmt = $conn->prepare("SELECT id, first_name, last_name FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $farmer = $result->fetch_assoc();
    $stmt->close();

    if (!$farmer) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Farmer not found.']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Delete related records first if needed
    // $conn->query("DELETE FROM farmer_payments WHERE farmer_id = $farmer_id");
    // $conn->query("DELETE FROM farmer_produce WHERE farmer_id = $farmer_id");

    // Delete the farmer
    $stmt = $conn->prepare("DELETE FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('No records were deleted.');
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "Farmer {$farmer['first_name']} {$farmer['last_name']} was successfully deleted."
    ]);

} catch (Exception $e) {
    // Roll back on error
    $conn->rollback();
    
    // Log the error
    error_log("Error deleting farmer: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete farmer. ' . $e->getMessage()
    ]);
}