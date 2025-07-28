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
    $metric_type = $_POST['metric_type'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $user_id = $current_user['user_id'];
    
    if (empty($metric_type)) {
        throw new Exception('Metric type is required');
    }
    
    $query = "INSERT INTO health_metrics (user_id, metric_type, value, systolic, diastolic, unit, notes) 
              VALUES (:user_id, :metric_type, :value, :systolic, :diastolic, :unit, :notes)";
    
    $stmt = $db->prepare($query);
    
    $value = null;
    $systolic = null;
    $diastolic = null;
    $unit = 'unit';
    
    if ($metric_type === 'blood_pressure') {
        $systolic = $_POST['systolic'] ?? null;
        $diastolic = $_POST['diastolic'] ?? null;
        $value = $systolic; // Use systolic as the main value for sorting
        $unit = 'mmHg';
        
        if (empty($systolic) || empty($diastolic)) {
            throw new Exception('Both systolic and diastolic values are required for blood pressure');
        }
        
        if ($systolic < 80 || $systolic > 200 || $diastolic < 50 || $diastolic > 130) {
            throw new Exception('Blood pressure values are outside normal ranges');
        }
        
    } else {
        $value = $_POST['value'] ?? null;
        
        if (empty($value)) {
            throw new Exception('Value is required');
        }
        
        switch ($metric_type) {
            case 'blood_sugar':
                $unit = 'mg/dL';
                if ($value < 50 || $value > 400) {
                    throw new Exception('Blood sugar value is outside normal range');
                }
                break;
            case 'weight':
                $unit = 'lbs';
                if ($value < 80 || $value > 300) {
                    throw new Exception('Weight value is outside normal range');
                }
                break;
            case 'heart_rate':
                $unit = 'BPM';
                if ($value < 50 || $value > 120) {
                    throw new Exception('Heart rate value is outside normal range');
                }
                break;
            default:
                throw new Exception('Invalid metric type');
        }
    }
    
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':metric_type', $metric_type);
    $stmt->bindParam(':value', $value);
    $stmt->bindParam(':systolic', $systolic);
    $stmt->bindParam(':diastolic', $diastolic);
    $stmt->bindParam(':unit', $unit);
    $stmt->bindParam(':notes', $notes);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Health metric saved successfully',
            'metric_id' => $db->lastInsertId()
        ]);
    } else {
        throw new Exception('Failed to save health metric');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
