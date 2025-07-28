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
    $emergency = $input['emergency'] ?? false;
    $user_id = $current_user['user_id'];
    
    if (!$emergency) {
        throw new Exception('Invalid emergency request');
    }
    
    // Log emergency alert
    $query = "INSERT INTO chat_messages (user_id, message, sender) 
              VALUES (:user_id, :message, 'user')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $message = "EMERGENCY ALERT: User has activated emergency assistance at " . date('Y-m-d H:i:s');
    $stmt->bindParam(':message', $message);
    $stmt->execute();
    
    // Get user's emergency contacts
    $query = "SELECT contact_name, phone, relationship FROM emergency_contacts 
              WHERE user_id = :user_id ORDER BY is_primary DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $emergency_contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user preferences for emergency contact info
    $query = "SELECT emergency_contact_1, emergency_contact_2 FROM user_preferences 
              WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // In a real application, you would:
    // 1. Send SMS/call to emergency contacts
    // 2. Contact emergency services if configured
    // 3. Send location data if available
    // 4. Trigger emergency protocols
    
    echo json_encode([
        'success' => true,
        'message' => 'Emergency alert logged',
        'emergency_contacts' => $emergency_contacts,
        'next_steps' => [
            'Call 911 if you need immediate medical assistance',
            'Contact your doctor or healthcare provider',
            'Notify your emergency contacts'
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
