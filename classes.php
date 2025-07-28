<?php
require_once 'includes/functions.php';
requireAuth();

$current_user = getCurrentUser();
if (!$current_user) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle class enrollment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll_class'])) {
    $schedule_id = sanitizeInput($_POST['schedule_id']);
    $user_id = $current_user['user_id'];
    
    // Check if user is already enrolled
    $query = "SELECT id FROM class_enrollments WHERE user_id = :user_id AND schedule_id = :schedule_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':schedule_id', $schedule_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $error = 'You are already enrolled in this class.';
    } else {
        // Check available spots
        $query = "SELECT cs.available_spots, COUNT(ce.id) as enrolled_count 
                  FROM class_schedules cs 
                  LEFT JOIN class_enrollments ce ON cs.id = ce.schedule_id AND ce.attendance_status != 'cancelled'
                  WHERE cs.id = :schedule_id 
                  GROUP BY cs.id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':schedule_id', $schedule_id);
        $stmt->execute();
        $class_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($class_info && $class_info['enrolled_count'] < $class_info['available_spots']) {
            // Enroll user
            $query = "INSERT INTO class_enrollments (user_id, schedule_id) VALUES (:user_id, :schedule_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':schedule_id', $schedule_id);
            
            if ($stmt->execute()) {
                $success = 'Successfully enrolled in the class!';
            } else {
                $error = 'Failed to enroll in the class. Please try again.';
            }
        } else {
            $error = 'This class is full. Please choose another time slot.';
        }
    }
}

// Handle class cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_enrollment'])) {
    $enrollment_id = sanitizeInput($_POST['enrollment_id']);
    $user_id = $current_user['user_id'];
    
    $query = "UPDATE class_enrollments 
              SET attendance_status = 'cancelled' 
              WHERE id = :enrollment_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':enrollment_id', $enrollment_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        $success = 'Class enrollment cancelled successfully.';
    } else {
        $error = 'Failed to cancel enrollment. Please try again.';
    }
}

// Get available classes
$query = "SELECT cs.*, wc.class_name, wc.class_type, wc.description, wc.instructor_name, wc.duration_minutes,
                 COUNT(ce.id) as enrolled_count, cs.available_spots,
                 (CASE WHEN uce.id IS NOT NULL THEN 1 ELSE 0 END) as user_enrolled,
                 uce.id as enrollment_id, uce.attendance_status
          FROM class_schedules cs
          JOIN wellness_classes wc ON cs.class_id = wc.id
          LEFT JOIN class_enrollments ce ON cs.id = ce.schedule_id AND ce.attendance_status != 'cancelled'
          LEFT JOIN class_enrollments uce ON cs.id = uce.schedule_id AND uce.user_id = :user_id AND uce.attendance_status != 'cancelled'
          WHERE cs.scheduled_date >= CURDATE() AND wc.is_active = 1
          GROUP BY cs.id
          ORDER BY cs.scheduled_date ASC, cs.start_time ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's enrolled classes
$query = "SELECT cs.*, wc.class_name, wc.class_type, wc.instructor_name, ce.id as enrollment_id, ce.attendance_status
          FROM class_enrollments ce
          JOIN class_schedules cs ON ce.schedule_id = cs.id
          JOIN wellness_classes wc ON cs.class_id = wc.id
          WHERE ce.user_id = :user_id AND ce.attendance_status != 'cancelled'
          AND cs.scheduled_date >= CURDATE()
          ORDER BY cs.scheduled_date ASC, cs.start_time ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$my_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness Classes - MOMCARE 🧘‍♀️</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/momcare-ui.css" rel="stylesheet">
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
                    <a href="dashboard.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                    <a href="chat.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-comments mr-1"></i>Chat
                    </a>
                    <a href="appointments.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-calendar-alt mr-1"></i>Appointments
                    </a>
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 card">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            <i class="fas fa-yoga text-purple-600 mr-3"></i>Wellness Classes
                        </h1>
                        <p class="text-gray-600">Join prenatal yoga, meditation, and preparation classes designed for expecting mothers</p>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="scrollToMyClasses()" class="btn-secondary">
                            <i class="fas fa-list mr-2"></i>My Classes
                        </button>
                        <button onclick="filterClasses('all')" class="btn-primary">
                            <i class="fas fa-filter mr-2"></i>All Classes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success mb-6"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Class Type Filters -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-lg p-4">
                <div class="flex flex-wrap gap-3">
                    <span class="text-sm font-medium text-gray-700 mr-3 self-center">Filter by type:</span>
                    <button onclick="filterClasses('all')" class="filter-btn active quick-action-btn">
                        <i class="fas fa-th mr-1"></i>All Classes
                    </button>
                    <button onclick="filterClasses('yoga')" class="filter-btn quick-action-btn">
                        <i class="fas fa-yoga mr-1"></i>Yoga
                    </button>
                    <button onclick="filterClasses('meditation')" class="filter-btn quick-action-btn">
                        <i class="fas fa-brain mr-1"></i>Meditation
                    </button>
                    <button onclick="filterClasses('nutrition')" class="filter-btn quick-action-btn">
                        <i class="fas fa-apple-alt mr-1"></i>Nutrition
                    </button>
                    <button onclick="filterClasses('birthing')" class="filter-btn quick-action-btn">
                        <i class="fas fa-baby mr-1"></i>Birthing
                    </button>
                    <button onclick="filterClasses('parenting')" class="filter-btn quick-action-btn">
                        <i class="fas fa-heart mr-1"></i>Parenting
                    </button>
                </div>
            </div>
        </div>

        <!-- My Enrolled Classes -->
        <?php if (count($my_classes) > 0): ?>
        <div id="myClassesSection" class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                <i class="fas fa-bookmark text-green-600 mr-2"></i>My Enrolled Classes
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($my_classes as $class): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                                    Enrolled
                                </span>
                            </h3>
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-user text-gray-400 mr-2"></i>
                                <?php echo htmlspecialchars($class['instructor_name']); ?>
                            </p>
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-calendar text-blue-500 mr-2"></i>
                                <?php echo date('M j, Y', strtotime($class['scheduled_date'])); ?>
                            </p>
                            <p class="text-gray-600 mb-4">
                                <i class="fas fa-clock text-purple-500 mr-2"></i>
                                <?php echo date('g:i A', strtotime($class['start_time'])); ?> - 
                                <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($class['is_virtual']): ?>
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <div class="flex items-center text-blue-800">
                            <i class="fas fa-video mr-2"></i>
                            <span class="font-medium">Virtual Class</span>
                        </div>
                        <?php if ($class['virtual_link']): ?>
                        <a href="<?php echo $class['virtual_link']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm mt-1 block">
                            Join Meeting Link
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span><?php echo htmlspecialchars($class['location'] ?: 'Location TBD'); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex space-x-3">
                        <?php if (strtotime($class['scheduled_date'] . ' ' . $class['start_time']) > time()): ?>
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="enrollment_id" value="<?php echo $class['enrollment_id']; ?>">
                            <button type="submit" name="cancel_enrollment" class="w-full px-4 py-2 text-red-600 hover:text-red-800 border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                                <i class="fas fa-times mr-2"></i>Cancel Enrollment
                            </button>
                        </form>
                        <?php endif; ?>
                        <button onclick="addToCalendar('<?php echo $class['class_name']; ?>', '<?php echo $class['scheduled_date']; ?>', '<?php echo $class['start_time']; ?>', '<?php echo $class['end_time']; ?>')" class="px-4 py-2 text-indigo-600 hover:text-indigo-800 border border-indigo-300 rounded-lg hover:bg-indigo-50 transition-colors">
                            <i class="fas fa-calendar-plus mr-2"></i>Add to Calendar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Available Classes -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                <i class="fas fa-plus-circle text-blue-600 mr-2"></i>Available Classes
            </h2>
            
            <?php if (count($classes) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="classesGrid">
                <?php foreach ($classes as $class): ?>
                <div class="class-card bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300" data-type="<?php echo $class['class_type']; ?>">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <?php
                                $icons = [
                                    'yoga' => 'fas fa-yoga text-purple-500',
                                    'meditation' => 'fas fa-brain text-blue-500',
                                    'nutrition' => 'fas fa-apple-alt text-green-500',
                                    'birthing' => 'fas fa-baby text-pink-500',
                                    'parenting' => 'fas fa-heart text-red-500'
                                ];
                                $icon = $icons[$class['class_type']] ?? 'fas fa-circle text-gray-500';
                                ?>
                                <i class="<?php echo $icon; ?> text-2xl mr-3"></i>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo ucfirst($class['class_type']); ?>
                                </span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </h3>
                            <p class="text-gray-600 mb-3 text-sm">
                                <?php echo htmlspecialchars($class['description']); ?>
                            </p>
                        </div>
                        <div class="text-right ml-4">
                            <div class="text-lg font-bold text-green-600">
                                <?php echo $class['available_spots'] - $class['enrolled_count']; ?>
                            </div>
                            <div class="text-xs text-gray-500">spots left</div>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-user text-gray-400 mr-3 w-4"></i>
                            <span><?php echo htmlspecialchars($class['instructor_name']); ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-calendar text-blue-500 mr-3 w-4"></i>
                            <span><?php echo date('M j, Y', strtotime($class['scheduled_date'])); ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-clock text-purple-500 mr-3 w-4"></i>
                            <span>
                                <?php echo date('g:i A', strtotime($class['start_time'])); ?> - 
                                <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                                (<?php echo $class['duration_minutes']; ?> min)
                            </span>
                        </div>
                        <?php if ($class['is_virtual']): ?>
                        <div class="flex items-center text-blue-600">
                            <i class="fas fa-video mr-3 w-4"></i>
                            <span>Virtual Class</span>
                        </div>
                        <?php else: ?>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-map-marker-alt text-red-500 mr-3 w-4"></i>
                            <span><?php echo htmlspecialchars($class['location'] ?: 'Location TBD'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="border-t pt-4">
                        <?php if ($class['user_enrolled']): ?>
                            <div class="text-center py-2">
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-2"></i>Already Enrolled
                                </span>
                            </div>
                        <?php elseif ($class['enrolled_count'] >= $class['available_spots']): ?>
                            <div class="text-center py-2">
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-2"></i>Class Full
                                </span>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="schedule_id" value="<?php echo $class['id']; ?>">
                                <button type="submit" name="enroll_class" class="w-full btn-primary">
                                    <i class="fas fa-plus mr-2"></i>Enroll in Class
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-yoga text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No classes available</h3>
                    <p class="text-gray-500">Check back soon for new wellness classes!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Class Benefits -->
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                <i class="fas fa-star text-yellow-500 mr-2"></i>Benefits of Prenatal Wellness Classes
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-heart text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Reduce Stress</h3>
                    <p class="text-gray-600 text-sm">Lower cortisol levels and promote relaxation</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-dumbbell text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Stay Active</h3>
                    <p class="text-gray-600 text-sm">Maintain fitness safely during pregnancy</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Connect</h3>
                    <p class="text-gray-600 text-sm">Build community with other expecting mothers</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-graduation-cap text-pink-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Learn</h3>
                    <p class="text-gray-600 text-sm">Prepare for labor and new parenthood</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/momcare-app.js"></script>
    <script>
        // Filter classes by type
        function filterClasses(type) {
            const cards = document.querySelectorAll('.class-card');
            const filterBtns = document.querySelectorAll('.filter-btn');
            
            // Update active filter button
            filterBtns.forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.toLowerCase().includes(type) || type === 'all') {
                    if (type === 'all' && btn.textContent.includes('All')) {
                        btn.classList.add('active');
                    } else if (type !== 'all' && btn.textContent.toLowerCase().includes(type)) {
                        btn.classList.add('active');
                    }
                }
            });
            
            // Show/hide cards
            cards.forEach(card => {
                if (type === 'all' || card.dataset.type === type) {
                    card.style.display = 'block';
                    card.classList.add('fade-in-up');
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Scroll to my classes section
        function scrollToMyClasses() {
            const section = document.getElementById('myClassesSection');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Add to calendar function
        function addToCalendar(className, date, startTime, endTime) {
            const startDateTime = new Date(date + ' ' + startTime);
            const endDateTime = new Date(date + ' ' + endTime);
            
            const formatDate = (date) => {
                return date.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
            };
            
            const calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(className)}&dates=${formatDate(startDateTime)}/${formatDate(endDateTime)}&details=${encodeURIComponent('MOMCARE Wellness Class')}&location=${encodeURIComponent('MOMCARE Center')}`;
            
            window.open(calendarUrl, '_blank');
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to cards
            const cards = document.querySelectorAll('.class-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in-up');
                }, index * 100);
            });
            
            // Show success message
            <?php if (!empty($success)): ?>
            setTimeout(() => {
                MomCare.showNotification('<?php echo addslashes($success); ?>', 'success');
            }, 500);
            <?php endif; ?>
            
            // Show error message
            <?php if (!empty($error)): ?>
            setTimeout(() => {
                MomCare.showNotification('<?php echo addslashes($error); ?>', 'error');
            }, 500);
            <?php endif; ?>
        });
    </script>
</body>
</html>
