<?php
require_once '../includes/functions.php';
requireAuth();

header('Content-Type: application/json');

$current_user = getCurrentUser();
if (!$current_user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $reminder_id = $input['reminderId'] ?? null;
    
    if (empty($reminder_id)) {
        throw new Exception('Reminder ID is required');
    }
    
    $user_id = $current_user['user_id'];
    
    // Verify the reminder belongs to the current user
    $query = "SELECT mr.id FROM medication_reminders mr 
              JOIN medications m ON mr.medication_id = m.id 
              WHERE mr.id = :reminder_id AND m.user_id = :user_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':reminder_id', $reminder_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Reminder not found or access denied');
    }
    
    // Mark the reminder as taken
    $query = "UPDATE medication_reminders 
              SET is_taken = 1, taken_at = NOW() 
              WHERE id = :reminder_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':reminder_id', $reminder_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Medication marked as taken'
        ]);
    } else {
        throw new Exception('Failed to update medication reminder');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
