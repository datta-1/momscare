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

try {
    $user_id = $current_user['user_id'];
    
    // Get user data
    $user_profile = $user->getById($user_id);
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user_id,
            'name' => $current_user['full_name'],
            'email' => $user_profile['email'],
            'weeks_pregnant' => $user_profile['weeks_pregnant'] ?? 0,
            'age' => $user_profile['age'] ?? null
        ],
        'currentWeek' => $user_profile['weeks_pregnant'] ?? 0
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
