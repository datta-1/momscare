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
    
    // Get health metrics data for charts
    $query = "SELECT metric_type, value, systolic, diastolic, unit, recorded_at 
              FROM health_metrics 
              WHERE user_id = :user_id 
              ORDER BY recorded_at DESC 
              LIMIT 50";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process data for charts
    $chart_data = [
        'bloodPressure' => ['dates' => [], 'systolic' => [], 'diastolic' => []],
        'bloodSugar' => ['dates' => [], 'values' => []],
        'weight' => ['dates' => [], 'values' => []],
        'heartRate' => ['dates' => [], 'values' => []]
    ];
    
    $latest_metrics = [];
    
    foreach ($metrics as $metric) {
        $type = $metric['metric_type'];
        $date = date('M j', strtotime($metric['recorded_at']));
        
        // Store latest metric for each type
        if (!isset($latest_metrics[$type])) {
            $latest_metrics[$type] = $metric;
        }
        
        // Prepare chart data (reverse order so oldest is first)
        if ($type === 'blood_pressure') {
            array_unshift($chart_data['bloodPressure']['dates'], $date);
            array_unshift($chart_data['bloodPressure']['systolic'], (float)$metric['systolic']);
            array_unshift($chart_data['bloodPressure']['diastolic'], (float)$metric['diastolic']);
        } elseif ($type === 'blood_sugar') {
            array_unshift($chart_data['bloodSugar']['dates'], $date);
            array_unshift($chart_data['bloodSugar']['values'], (float)$metric['value']);
        } elseif ($type === 'weight') {
            array_unshift($chart_data['weight']['dates'], $date);
            array_unshift($chart_data['weight']['values'], (float)$metric['value']);
        } elseif ($type === 'heart_rate') {
            array_unshift($chart_data['heartRate']['dates'], $date);
            array_unshift($chart_data['heartRate']['values'], (float)$metric['value']);
        }
    }
    
    // Limit chart data points to last 20 for better visualization
    foreach ($chart_data as $type => &$data) {
        if (isset($data['dates']) && count($data['dates']) > 20) {
            $data['dates'] = array_slice($data['dates'], -20);
            if (isset($data['values'])) {
                $data['values'] = array_slice($data['values'], -20);
            }
            if (isset($data['systolic'])) {
                $data['systolic'] = array_slice($data['systolic'], -20);
                $data['diastolic'] = array_slice($data['diastolic'], -20);
            }
        }
    }
    
    // Format latest metrics for display
    $response = [
        'success' => true,
        'chartData' => $chart_data,
        'latestMetrics' => []
    ];
    
    if (isset($latest_metrics['blood_pressure'])) {
        $response['latestMetrics']['lastBloodPressure'] = 
            $latest_metrics['blood_pressure']['systolic'] . '/' . $latest_metrics['blood_pressure']['diastolic'];
    }
    
    if (isset($latest_metrics['blood_sugar'])) {
        $response['latestMetrics']['lastBloodSugar'] = $latest_metrics['blood_sugar']['value'] . ' mg/dL';
    }
    
    if (isset($latest_metrics['weight'])) {
        $response['latestMetrics']['currentWeight'] = $latest_metrics['weight']['value'] . ' lbs';
    }
    
    if (isset($latest_metrics['heart_rate'])) {
        $response['latestMetrics']['lastHeartRate'] = $latest_metrics['heart_rate']['value'] . ' BPM';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
