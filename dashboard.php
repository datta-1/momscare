<?php
require_once 'includes/functions.php';
requireAuth();

$current_user = getCurrentUser();
if (!$current_user) {
    header('Location: login.php');
    exit();
}

// Get user profile data
$user_profile = $user->getById($current_user['user_id']);
$weeks_pregnant = $user_profile['weeks_pregnant'] ?? 0;

// Calculate pregnancy progress
$pregnancy_progress = ($weeks_pregnant / 40) * 100;
$trimester = $weeks_pregnant <= 12 ? 1 : ($weeks_pregnant <= 28 ? 2 : 3);

// Get recent chat messages
$recent_messages = $chatMessage->getHistory($current_user['user_id'], 5);

// Get upcoming appointments
$query = "SELECT * FROM appointments WHERE user_id = :user_id AND appointment_date >= NOW() ORDER BY appointment_date ASC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$upcoming_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get upcoming wellness classes
$query = "SELECT cs.*, wc.class_name, wc.class_type, wc.instructor_name 
          FROM class_schedules cs 
          JOIN wellness_classes wc ON cs.class_id = wc.id 
          LEFT JOIN class_enrollments ce ON cs.id = ce.schedule_id AND ce.user_id = :user_id
          WHERE cs.scheduled_date >= CURDATE() AND (ce.id IS NULL OR ce.attendance_status = 'enrolled')
          ORDER BY cs.scheduled_date ASC, cs.start_time ASC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$upcoming_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get today's medication reminders
$query = "SELECT mr.*, m.medication_name, m.dosage, m.instructions 
          FROM medication_reminders mr 
          JOIN medications m ON mr.medication_id = m.id 
          WHERE m.user_id = :user_id AND mr.reminder_date = CURDATE() AND m.is_active = 1
          ORDER BY mr.reminder_time ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$todays_medications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get latest health metrics
$query = "SELECT metric_type, value, systolic, diastolic, recorded_at 
          FROM health_metrics 
          WHERE user_id = :user_id 
          ORDER BY recorded_at DESC 
          LIMIT 20";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$health_metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process health metrics for display
$latest_metrics = [];
$chart_data = [
    'blood_pressure' => ['dates' => [], 'systolic' => [], 'diastolic' => []],
    'blood_sugar' => ['dates' => [], 'values' => []],
    'weight' => ['dates' => [], 'values' => []],
    'heart_rate' => ['dates' => [], 'values' => []]
];

foreach ($health_metrics as $metric) {
    $type = $metric['metric_type'];
    $date = date('M j', strtotime($metric['recorded_at']));
    
    if (!isset($latest_metrics[$type])) {
        $latest_metrics[$type] = $metric;
    }
    
    if ($type === 'blood_pressure') {
        array_unshift($chart_data[$type]['dates'], $date);
        array_unshift($chart_data[$type]['systolic'], $metric['systolic']);
        array_unshift($chart_data[$type]['diastolic'], $metric['diastolic']);
    } else {
        if (isset($chart_data[$type])) {
            array_unshift($chart_data[$type]['dates'], $date);
            array_unshift($chart_data[$type]['values'], $metric['value']);
        }
    }
}

// Get document count
$query = "SELECT COUNT(*) as count FROM medical_documents WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$document_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get pregnancy tips for current week
$query = "SELECT * FROM pregnancy_tips WHERE week_number = :week ORDER BY category";
$stmt = $db->prepare($query);
$stmt->bindParam(':week', $weeks_pregnant);
$stmt->execute();
$weekly_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get nearby hospitals (simplified - in reality, you'd use user's location)
$query = "SELECT * FROM hospitals ORDER BY rating DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$nearby_hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MOMCARE AI Assistant 🤰</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/momcare-ui.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Enhanced Navigation -->
    <nav class="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="navbar-brand">
                        <i class="fas fa-heart mr-2"></i>MOMCARE
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="chat.php" class="text-gray-600 hover:text-indigo-600 relative">
                        <i class="fas fa-comments mr-1"></i>Chat
                        <?php if (count($recent_messages) > 0): ?>
                            <span class="notification-badge"><?php echo min(count($recent_messages), 9); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="appointments.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-calendar-alt mr-1"></i>Appointments
                    </a>
                    <a href="health-metrics.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-chart-line mr-1"></i>Health
                    </a>
                    <a href="classes.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-yoga mr-1"></i>Classes
                    </a>
                    <div class="relative">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    </div>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Dashboard Header with Pregnancy Progress -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 card">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            <i class="fas fa-tachometer-alt text-indigo-600 mr-3"></i>Your Pregnancy Dashboard
                        </h1>
                        <p class="text-gray-600">Track your journey to motherhood with AI-powered insights</p>
                    </div>
                    <div class="week-indicator">
                        <div class="week-number"><?php echo $weeks_pregnant; ?></div>
                        <div class="week-text">weeks pregnant</div>
                        <div class="mt-2">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $pregnancy_progress; ?>%"></div>
                            </div>
                            <p class="text-sm opacity-75 mt-1">Trimester <?php echo $trimester; ?> • <?php echo round($pregnancy_progress); ?>% Complete</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card primary">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar-week text-3xl text-indigo-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Weeks Pregnant</dt>
                            <dd class="text-2xl font-bold text-gray-900" data-stat="weeks_pregnant">
                                <?php echo $weeks_pregnant ?: 'Not set'; ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="stat-card secondary">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar-alt text-3xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Next Appointment</dt>
                            <dd class="text-lg font-bold text-gray-900">
                                <?php 
                                if (count($upcoming_appointments) > 0) {
                                    echo date('M j', strtotime($upcoming_appointments[0]['appointment_date']));
                                } else {
                                    echo 'None scheduled';
                                }
                                ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-pills text-3xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Medications Today</dt>
                            <dd class="text-2xl font-bold text-gray-900"><?php echo count($todays_medications); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="stat-card warm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-medical text-3xl text-purple-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Medical Documents</dt>
                            <dd class="text-2xl font-bold text-gray-900"><?php echo $document_count; ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Health Metrics -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Health Charts -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-heartbeat text-red-500 mr-2"></i>Blood Pressure Trends
                        </h3>
                        <button class="btn-primary btn-sm" onclick="document.getElementById('bpModal').style.display='block'">
                            <i class="fas fa-plus mr-1"></i>Add Reading
                        </button>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="bloodPressureChart"></canvas>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-weight text-amber-500 mr-2"></i>Weight Progress
                            </h3>
                        </div>
                        <div style="height: 200px;">
                            <canvas id="weightChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-tint text-blue-500 mr-2"></i>Blood Sugar
                            </h3>
                        </div>
                        <div style="height: 200px;">
                            <canvas id="bloodSugarChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Health Metrics Summary -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-line text-indigo-600 mr-2"></i>Latest Health Metrics
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php if (isset($latest_metrics['blood_pressure'])): ?>
                        <div class="metric-card">
                            <div class="metric-value" data-stat="lastBP">
                                <?php echo $latest_metrics['blood_pressure']['systolic'] . '/' . $latest_metrics['blood_pressure']['diastolic']; ?>
                            </div>
                            <div class="metric-label">Blood Pressure</div>
                            <div class="metric-trend trend-up">
                                <i class="fas fa-arrow-up"></i> Normal
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($latest_metrics['weight'])): ?>
                        <div class="metric-card">
                            <div class="metric-value" data-stat="currentWeight">
                                <?php echo $latest_metrics['weight']['value']; ?> lbs
                            </div>
                            <div class="metric-label">Current Weight</div>
                            <div class="metric-trend trend-up">
                                <i class="fas fa-arrow-up"></i> +2 lbs
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($latest_metrics['blood_sugar'])): ?>
                        <div class="metric-card">
                            <div class="metric-value" data-stat="lastSugar">
                                <?php echo $latest_metrics['blood_sugar']['value']; ?>
                            </div>
                            <div class="metric-label">Blood Sugar</div>
                            <div class="metric-trend trend-up">
                                <i class="fas fa-check"></i> Normal
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($latest_metrics['heart_rate'])): ?>
                        <div class="metric-card">
                            <div class="metric-value" data-stat="lastHeartRate">
                                <?php echo $latest_metrics['heart_rate']['value']; ?> BPM
                            </div>
                            <div class="metric-label">Heart Rate</div>
                            <div class="metric-trend trend-up">
                                <i class="fas fa-heartbeat"></i> Good
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Appointments, Medications, Tips -->
            <div class="space-y-6">
                <!-- Today's Medications -->
                <?php if (count($todays_medications) > 0): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-pills text-green-600 mr-2"></i>Today's Medications
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($todays_medications as $med): ?>
                        <div class="medication-card <?php echo $med['is_taken'] ? 'taken' : 'due'; ?>" data-reminder-id="<?php echo $med['id']; ?>">
                            <div class="medication-name"><?php echo htmlspecialchars($med['medication_name']); ?></div>
                            <div class="medication-time">
                                <i class="fas fa-clock mr-1"></i><?php echo date('g:i A', strtotime($med['reminder_time'])); ?>
                            </div>
                            <div class="medication-dosage"><?php echo htmlspecialchars($med['dosage']); ?></div>
                            <?php if (!$med['is_taken']): ?>
                            <button class="medication-taken-btn btn-success btn-sm mt-2" data-reminder-id="<?php echo $med['id']; ?>">
                                <i class="fas fa-check mr-1"></i>Mark as Taken
                            </button>
                            <?php else: ?>
                            <div class="text-green-600 text-sm mt-2">
                                <i class="fas fa-check-circle mr-1"></i>Taken
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Upcoming Appointments -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Upcoming Appointments
                        </h3>
                        <a href="appointments.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View All</a>
                    </div>
                    
                    <?php if (count($upcoming_appointments) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                            <div class="appointment-card">
                                <div class="appointment-date">
                                    <?php echo date('M j, Y • g:i A', strtotime($appointment['appointment_date'])); ?>
                                </div>
                                <div class="appointment-doctor">
                                    <i class="fas fa-user-md mr-1"></i>
                                    <?php echo htmlspecialchars($appointment['doctor_name'] ?: 'Doctor Visit'); ?>
                                </div>
                                <div class="appointment-location">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?php echo htmlspecialchars($appointment['location'] ?: 'Location TBD'); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 text-gray-500">
                            <i class="fas fa-calendar-plus text-4xl mb-3"></i>
                            <p>No upcoming appointments</p>
                            <a href="appointments.php" class="btn-primary mt-3">Schedule Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming Classes -->
                <?php if (count($upcoming_classes) > 0): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-yoga text-purple-600 mr-2"></i>Wellness Classes
                        </h3>
                        <a href="classes.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View All</a>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($upcoming_classes as $class): ?>
                        <div class="appointment-card">
                            <div class="appointment-date">
                                <?php echo date('M j • g:i A', strtotime($class['scheduled_date'] . ' ' . $class['start_time'])); ?>
                            </div>
                            <div class="appointment-doctor">
                                <i class="fas fa-dumbbell mr-1"></i>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </div>
                            <div class="appointment-location">
                                <i class="fas fa-user mr-1"></i>
                                <?php echo htmlspecialchars($class['instructor_name']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Weekly Tips -->
                <?php if (count($weekly_tips) > 0): ?>
                <div class="bg-gradient-to-br from-pink-50 to-purple-50 rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-lightbulb text-amber-500 mr-2"></i>Tips for Week <?php echo $weeks_pregnant; ?>
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($weekly_tips as $tip): ?>
                        <div class="bg-white rounded-lg p-4 border border-pink-100">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <?php
                                    $icons = [
                                        'health' => 'fas fa-heartbeat text-red-500',
                                        'nutrition' => 'fas fa-apple-alt text-green-500',
                                        'exercise' => 'fas fa-dumbbell text-blue-500',
                                        'mental_health' => 'fas fa-brain text-purple-500',
                                        'preparation' => 'fas fa-baby text-pink-500'
                                    ];
                                    $icon = $icons[$tip['category']] ?? 'fas fa-info-circle text-gray-500';
                                    ?>
                                    <i class="<?php echo $icon; ?> text-lg mr-3 mt-1"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($tip['title']); ?></h4>
                                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($tip['content']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Emergency Contacts -->
                <div class="bg-red-50 rounded-xl shadow-lg p-6 border-l-4 border-red-500">
                    <h3 class="text-lg font-semibold text-red-900 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Emergency Contacts
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-red-800">Emergency Services</span>
                            <a href="tel:911" class="text-red-600 font-semibold hover:text-red-800">911</a>
                        </div>
                        <?php if (count($nearby_hospitals) > 0): ?>
                        <div class="text-sm text-red-700">
                            <strong>Nearest Hospital:</strong><br>
                            <?php echo htmlspecialchars($nearby_hospitals[0]['name']); ?><br>
                            <a href="tel:<?php echo $nearby_hospitals[0]['phone']; ?>" class="text-red-600 hover:text-red-800">
                                <?php echo $nearby_hospitals[0]['phone']; ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>Quick Actions
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <a href="chat.php" class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg hover:shadow-md transition-all duration-300">
                    <i class="fas fa-comments text-2xl text-blue-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900">AI Chat</span>
                </a>
                
                <button onclick="document.getElementById('healthMetricModal').style.display='block'" class="flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg hover:shadow-md transition-all duration-300">
                    <i class="fas fa-plus-circle text-2xl text-green-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900">Log Health</span>
                </button>
                
                <a href="appointments.php" class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-violet-50 rounded-lg hover:shadow-md transition-all duration-300">
                    <i class="fas fa-calendar-plus text-2xl text-purple-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900">Book Appointment</span>
                </a>
                
                <a href="classes.php" class="flex flex-col items-center p-4 bg-gradient-to-br from-pink-50 to-rose-50 rounded-lg hover:shadow-md transition-all duration-300">
                    <i class="fas fa-yoga text-2xl text-pink-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900">Join Class</span>
                </a>
                
                <a href="hospitals.php" class="flex flex-col items-center p-4 bg-gradient-to-br from-red-50 to-pink-50 rounded-lg hover:shadow-md transition-all duration-300">
                    <i class="fas fa-hospital text-2xl text-red-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900">Find Hospital</span>
                </a>
                
                <a href="resources.php" class="flex flex-col items-center p-4 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg hover:shadow-md transition-all duration-300">
                    <i class="fas fa-book-open text-2xl text-amber-600 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900">Resources</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Emergency Button -->
    <button class="emergency-btn" onclick="MomCare.handleEmergency()">
        <i class="fas fa-exclamation-triangle mr-2"></i>Emergency
    </button>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-medical text-2xl text-blue-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Medical Documents</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $document_count; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-comments text-2xl text-purple-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Chat Messages</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo count($recent_messages); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <a href="chat.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-robot text-3xl text-indigo-600 mr-4"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Chat with AI</h3>
                        <p class="text-gray-600">Get instant pregnancy advice</p>
                    </div>
                </div>
            </a>

            <a href="appointments.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-calendar-plus text-3xl text-green-600 mr-4"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Schedule Appointment</h3>
                        <p class="text-gray-600">Book your next checkup</p>
                    </div>
                </div>
            </a>

            <a href="medicaldocuments.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-upload text-3xl text-blue-600 mr-4"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Upload Documents</h3>
                        <p class="text-gray-600">Store medical records</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Chat Messages -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Chat</h3>
                    <?php if (empty($recent_messages)): ?>
                        <p class="text-gray-500">No recent messages. <a href="chat.php" class="text-indigo-600 hover:text-indigo-500">Start chatting now</a></p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach (array_slice($recent_messages, -3) as $message): ?>
                                <div class="flex <?php echo $message['sender'] == 'user' ? 'justify-end' : 'justify-start'; ?>">
                                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg <?php echo $message['sender'] == 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800'; ?>">
                                        <p class="text-sm"><?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>...</p>
                                        <p class="text-xs mt-1 opacity-75"><?php echo timeAgo($message['timestamp']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4">
                            <a href="chat.php" class="text-indigo-600 hover:text-indigo-500 text-sm">View all messages →</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upcoming Appointments</h3>
                    <?php if (empty($upcoming_appointments)): ?>
                        <p class="text-gray-500">No upcoming appointments. <a href="appointments.php" class="text-indigo-600 hover:text-indigo-500">Schedule one now</a></p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <div class="border-l-4 border-indigo-400 pl-4">
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($appointment['doctor_name'] ?? 'Doctor Visit'); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo formatDate($appointment['appointment_date']); ?></p>
                                    <?php if ($appointment['location']): ?>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($appointment['location']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4">
                            <a href="appointments.php" class="text-indigo-600 hover:text-indigo-500 text-sm">View all appointments →</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Metric Modal -->
    <div id="healthMetricModal" class="fixed inset-0 bg-gray-600 bg-opacity-50" style="display: none; z-index: 1000;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Log Health Metric</h3>
                    <button onclick="document.getElementById('healthMetricModal').style.display='none'" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form class="health-metric-form">
                    <div class="form-group">
                        <label class="form-label">Metric Type</label>
                        <select name="metric_type" class="form-input" required>
                            <option value="">Select metric</option>
                            <option value="blood_pressure">Blood Pressure</option>
                            <option value="blood_sugar">Blood Sugar</option>
                            <option value="weight">Weight</option>
                            <option value="heart_rate">Heart Rate</option>
                        </select>
                    </div>
                    
                    <div id="bpFields" style="display: none;">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Systolic</label>
                                <input type="number" name="systolic" class="form-input" placeholder="120">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Diastolic</label>
                                <input type="number" name="diastolic" class="form-input" placeholder="80">
                            </div>
                        </div>
                    </div>
                    
                    <div id="valueField" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Value</label>
                            <input type="number" step="0.1" name="value" class="form-input">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-input" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('healthMetricModal').style.display='none'" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                        <button type="submit" class="btn-primary">Save Metric</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Blood Pressure Modal -->
    <div id="bpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50" style="display: none; z-index: 1000;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Add Blood Pressure Reading</h3>
                    <button onclick="document.getElementById('bpModal').style.display='none'" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form class="health-metric-form">
                    <input type="hidden" name="metric_type" value="blood_pressure">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Systolic</label>
                            <input type="number" name="systolic" class="form-input" placeholder="120" required min="80" max="200">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Diastolic</label>
                            <input type="number" name="diastolic" class="form-input" placeholder="80" required min="50" max="130">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-input" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('bpModal').style.display='none'" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                        <button type="submit" class="btn-primary">Save Reading</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/momcare-app.js"></script>
    <script>
        // Initialize chart data
        const chartData = <?php echo json_encode($chart_data); ?>;
        
        // Pass chart data to MomCare app
        document.addEventListener('DOMContentLoaded', function() {
            // Set up metric type selection
            const metricSelect = document.querySelector('select[name="metric_type"]');
            if (metricSelect) {
                metricSelect.addEventListener('change', function() {
                    const bpFields = document.getElementById('bpFields');
                    const valueField = document.getElementById('valueField');
                    
                    if (this.value === 'blood_pressure') {
                        bpFields.style.display = 'block';
                        valueField.style.display = 'none';
                    } else if (this.value) {
                        bpFields.style.display = 'none';
                        valueField.style.display = 'block';
                        
                        const valueInput = valueField.querySelector('input');
                        const label = valueField.querySelector('label');
                        
                        switch(this.value) {
                            case 'blood_sugar':
                                label.textContent = 'Blood Sugar (mg/dL)';
                                valueInput.placeholder = '100';
                                valueInput.setAttribute('min', '50');
                                valueInput.setAttribute('max', '400');
                                break;
                            case 'weight':
                                label.textContent = 'Weight (lbs)';
                                valueInput.placeholder = '150';
                                valueInput.setAttribute('min', '80');
                                valueInput.setAttribute('max', '300');
                                break;
                            case 'heart_rate':
                                label.textContent = 'Heart Rate (BPM)';
                                valueInput.placeholder = '75';
                                valueInput.setAttribute('min', '50');
                                valueInput.setAttribute('max', '120');
                                break;
                        }
                    } else {
                        bpFields.style.display = 'none';
                        valueField.style.display = 'none';
                    }
                });
            }
            
            // Update charts with real data
            if (chartData) {
                MomCare.updateChartData(chartData);
            }
        });
        
        // Add some sample notifications
        setTimeout(() => {
            MomCare.showNotification('Welcome back to MOMCARE! 🤰', 'success');
        }, 1000);
        
        <?php if ($weeks_pregnant > 0 && $weeks_pregnant % 4 == 0): ?>
        setTimeout(() => {
            MomCare.showNotification('Congratulations! You\'ve reached <?php echo $weeks_pregnant; ?> weeks! 🎉', 'info', 8000);
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
