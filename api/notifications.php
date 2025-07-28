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
    
    // Check for various types of notifications
    $notifications = [];
    
    // Medication reminders due in next hour
    $query = "SELECT COUNT(*) as count FROM medication_reminders mr 
              JOIN medications m ON mr.medication_id = m.id 
              WHERE m.user_id = :user_id 
              AND mr.reminder_date = CURDATE() 
              AND mr.reminder_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 HOUR)
              AND mr.is_taken = 0 
              AND m.is_active = 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $medication_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($medication_count > 0) {
        $notifications[] = [
            'type' => 'warning',
            'message' => "You have $medication_count medication(s) due soon!"
        ];
    }
    
    // Upcoming appointments (within 24 hours)
    $query = "SELECT COUNT(*) as count FROM appointments 
              WHERE user_id = :user_id 
              AND appointment_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
              AND status = 'scheduled'";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $appointment_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($appointment_count > 0) {
        $notifications[] = [
            'type' => 'info',
            'message' => "You have $appointment_count appointment(s) coming up!"
        ];
    }
    
    // Weekly milestone check
    $user_profile = $user->getById($user_id);
    $weeks_pregnant = $user_profile['weeks_pregnant'] ?? 0;
    
    if ($weeks_pregnant > 0 && in_array($weeks_pregnant, [12, 20, 28, 36, 40])) {
        // Check if we've already shown this milestone notification today
        $query = "SELECT COUNT(*) as count FROM user_progress 
                  WHERE user_id = :user_id 
                  AND milestone_type = 'week_milestone' 
                  AND milestone_date = CURDATE()
                  AND JSON_EXTRACT(details, '$.week') = :week";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':week', $weeks_pregnant);
        $stmt->execute();
        $milestone_shown = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($milestone_shown == 0) {
            $notifications[] = [
                'type' => 'success',
                'message' => "Congratulations! You've reached week $weeks_pregnant - a major milestone! 🎉"
            ];
            
            // Mark milestone as shown
            $query = "INSERT INTO user_progress (user_id, milestone_type, milestone_date, details, points_earned) 
                      VALUES (:user_id, 'week_milestone', CURDATE(), :details, 10)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $details = json_encode(['week' => $weeks_pregnant]);
            $stmt->bindParam(':details', $details);
            $stmt->execute();
        }
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
