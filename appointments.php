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

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {
    $doctor_id = sanitizeInput($_POST['doctor_id']);
    $appointment_date = sanitizeInput($_POST['appointment_date']);
    $appointment_time = sanitizeInput($_POST['appointment_time']);
    $appointment_type = sanitizeInput($_POST['appointment_type']);
    $notes = sanitizeInput($_POST['notes']);
    $user_id = $current_user['user_id'];
    
    // Check if the time slot is available
    $query = "SELECT id FROM appointments 
              WHERE doctor_id = :doctor_id 
              AND appointment_date = :appointment_date 
              AND appointment_time = :appointment_time 
              AND appointment_status != 'cancelled'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':doctor_id', $doctor_id);
    $stmt->bindParam(':appointment_date', $appointment_date);
    $stmt->bindParam(':appointment_time', $appointment_time);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $error = 'This time slot is already booked. Please choose another time.';
    } else {
        // Book the appointment
        $query = "INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, appointment_type, notes) 
                  VALUES (:user_id, :doctor_id, :appointment_date, :appointment_time, :appointment_type, :notes)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':appointment_date', $appointment_date);
        $stmt->bindParam(':appointment_time', $appointment_time);
        $stmt->bindParam(':appointment_type', $appointment_type);
        $stmt->bindParam(':notes', $notes);
        
        if ($stmt->execute()) {
            $success = 'Appointment booked successfully!';
        } else {
            $error = 'Failed to book appointment. Please try again.';
        }
    }
}

// Handle appointment cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_appointment'])) {
    $appointment_id = sanitizeInput($_POST['appointment_id']);
    $user_id = $current_user['user_id'];
    
    $query = "UPDATE appointments 
              SET appointment_status = 'cancelled' 
              WHERE id = :appointment_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':appointment_id', $appointment_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        $success = 'Appointment cancelled successfully.';
    } else {
        $error = 'Failed to cancel appointment. Please try again.';
    }
}

// Handle appointment rescheduling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reschedule_appointment'])) {
    $appointment_id = sanitizeInput($_POST['appointment_id']);
    $new_date = sanitizeInput($_POST['new_date']);
    $new_time = sanitizeInput($_POST['new_time']);
    $user_id = $current_user['user_id'];
    
    // Check if new time slot is available
    $query = "SELECT a.doctor_id FROM appointments a WHERE a.id = :appointment_id AND a.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':appointment_id', $appointment_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($appointment) {
        $query = "SELECT id FROM appointments 
                  WHERE doctor_id = :doctor_id 
                  AND appointment_date = :appointment_date 
                  AND appointment_time = :appointment_time 
                  AND appointment_status != 'cancelled'
                  AND id != :appointment_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':doctor_id', $appointment['doctor_id']);
        $stmt->bindParam(':appointment_date', $new_date);
        $stmt->bindParam(':appointment_time', $new_time);
        $stmt->bindParam(':appointment_id', $appointment_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'The new time slot is already booked. Please choose another time.';
        } else {
            // Update appointment
            $query = "UPDATE appointments 
                      SET appointment_date = :new_date, appointment_time = :new_time 
                      WHERE id = :appointment_id AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':new_date', $new_date);
            $stmt->bindParam(':new_time', $new_time);
            $stmt->bindParam(':appointment_id', $appointment_id);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                $success = 'Appointment rescheduled successfully!';
            } else {
                $error = 'Failed to reschedule appointment. Please try again.';
            }
        }
    }
}

// Get user's appointments
$query = "SELECT a.*, d.full_name as doctor_name, d.specialization, d.phone_number as doctor_phone
          FROM appointments a
          JOIN doctors d ON a.doctor_id = d.id
          WHERE a.user_id = :user_id AND a.appointment_status != 'cancelled'
          ORDER BY a.appointment_date ASC, a.appointment_time ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available doctors
$query = "SELECT * FROM doctors WHERE is_active = 1 ORDER BY full_name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get upcoming appointments for quick view
$query = "SELECT a.*, d.full_name as doctor_name, d.specialization
          FROM appointments a
          JOIN doctors d ON a.doctor_id = d.id
          WHERE a.user_id = :user_id 
          AND a.appointment_status = 'scheduled' 
          AND a.appointment_date >= CURDATE()
          ORDER BY a.appointment_date ASC, a.appointment_time ASC
          LIMIT 3";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$upcoming_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - MOMCARE 📅</title>
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
                    <a href="classes.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-yoga mr-1"></i>Classes
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
                            <i class="fas fa-calendar-alt text-blue-600 mr-3"></i>My Appointments
                        </h1>
                        <p class="text-gray-600">Schedule and manage your prenatal care appointments</p>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="openBookingModal()" class="btn-primary">
                            <i class="fas fa-plus mr-2"></i>Book Appointment
                        </button>
                        <button onclick="viewCalendar()" class="btn-secondary">
                            <i class="fas fa-calendar mr-2"></i>Calendar View
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

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Upcoming</p>
                        <p class="text-2xl font-bold"><?php echo count($upcoming_appointments); ?></p>
                    </div>
                    <i class="fas fa-calendar-check text-3xl text-blue-200"></i>
                </div>
            </div>
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Total Appointments</p>
                        <p class="text-2xl font-bold"><?php echo count($appointments); ?></p>
                    </div>
                    <i class="fas fa-stethoscope text-3xl text-green-200"></i>
                </div>
            </div>
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm">Available Doctors</p>
                        <p class="text-2xl font-bold"><?php echo count($doctors); ?></p>
                    </div>
                    <i class="fas fa-user-md text-3xl text-purple-200"></i>
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <?php if (count($upcoming_appointments) > 0): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                <i class="fas fa-clock text-blue-600 mr-2"></i>Upcoming Appointments
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($upcoming_appointments as $appointment): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?>
                            </h3>
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-stethoscope text-gray-400 mr-2"></i>
                                <?php echo htmlspecialchars($appointment['specialization']); ?>
                            </p>
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-calendar text-blue-500 mr-2"></i>
                                <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?>
                            </p>
                            <p class="text-gray-600 mb-4">
                                <i class="fas fa-clock text-purple-500 mr-2"></i>
                                <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo ucfirst($appointment['appointment_type']); ?>
                        </span>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick="rescheduleAppointment(<?php echo $appointment['id']; ?>)" class="flex-1 px-3 py-2 text-indigo-600 hover:text-indigo-800 border border-indigo-300 rounded-lg hover:bg-indigo-50 transition-colors text-sm">
                            <i class="fas fa-edit mr-1"></i>Reschedule
                        </button>
                        <button onclick="cancelAppointment(<?php echo $appointment['id']; ?>)" class="flex-1 px-3 py-2 text-red-600 hover:text-red-800 border border-red-300 rounded-lg hover:bg-red-50 transition-colors text-sm">
                            <i class="fas fa-times mr-1"></i>Cancel
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- All Appointments -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-list text-gray-600 mr-2"></i>All Appointments
                </h2>
                <div class="flex space-x-2">
                    <button onclick="filterAppointments('all')" class="filter-btn active px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">All</button>
                    <button onclick="filterAppointments('scheduled')" class="filter-btn px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-800 hover:bg-blue-100 hover:text-blue-800">Scheduled</button>
                    <button onclick="filterAppointments('completed')" class="filter-btn px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-800 hover:bg-blue-100 hover:text-blue-800">Completed</button>
                </div>
            </div>
            
            <?php if (count($appointments) > 0): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="appointmentsTable">
                            <?php foreach ($appointments as $appointment): ?>
                            <tr class="appointment-row hover:bg-gray-50" data-status="<?php echo $appointment['appointment_status']; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-user-md text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($appointment['specialization']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <?php echo ucfirst($appointment['appointment_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status_colors = [
                                        'scheduled' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    $color = $status_colors[$appointment['appointment_status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color; ?>">
                                        <?php echo ucfirst($appointment['appointment_status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate">
                                        <?php echo htmlspecialchars($appointment['notes'] ?: 'No notes'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($appointment['appointment_status'] == 'scheduled' && strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']) > time()): ?>
                                    <div class="flex space-x-2">
                                        <button onclick="rescheduleAppointment(<?php echo $appointment['id']; ?>)" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="cancelAppointment(<?php echo $appointment['id']; ?>)" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-xl shadow-lg">
                    <i class="fas fa-calendar-alt text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No appointments yet</h3>
                    <p class="text-gray-500 mb-4">Schedule your first appointment to start your prenatal care journey</p>
                    <button onclick="openBookingModal()" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>Book Your First Appointment
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Book New Appointment</h3>
                    <button onclick="closeBookingModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form method="POST" id="bookingForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Doctor</label>
                            <select name="doctor_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Choose a doctor...</option>
                                <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>">
                                    Dr. <?php echo htmlspecialchars($doctor['full_name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Appointment Date</label>
                            <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Appointment Time</label>
                            <select name="appointment_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select time...</option>
                                <option value="09:00:00">9:00 AM</option>
                                <option value="09:30:00">9:30 AM</option>
                                <option value="10:00:00">10:00 AM</option>
                                <option value="10:30:00">10:30 AM</option>
                                <option value="11:00:00">11:00 AM</option>
                                <option value="11:30:00">11:30 AM</option>
                                <option value="14:00:00">2:00 PM</option>
                                <option value="14:30:00">2:30 PM</option>
                                <option value="15:00:00">3:00 PM</option>
                                <option value="15:30:00">3:30 PM</option>
                                <option value="16:00:00">4:00 PM</option>
                                <option value="16:30:00">4:30 PM</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Appointment Type</label>
                            <select name="appointment_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select type...</option>
                                <option value="routine">Routine Checkup</option>
                                <option value="ultrasound">Ultrasound</option>
                                <option value="consultation">Consultation</option>
                                <option value="follow-up">Follow-up</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Any specific concerns or notes for the doctor..."></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeBookingModal()" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" name="book_appointment" class="btn-primary">
                            <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div id="rescheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Reschedule Appointment</h3>
                    <button onclick="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form method="POST" id="rescheduleForm">
                    <input type="hidden" name="appointment_id" id="reschedule_appointment_id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Date</label>
                            <input type="date" name="new_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Time</label>
                            <select name="new_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select time...</option>
                                <option value="09:00:00">9:00 AM</option>
                                <option value="09:30:00">9:30 AM</option>
                                <option value="10:00:00">10:00 AM</option>
                                <option value="10:30:00">10:30 AM</option>
                                <option value="11:00:00">11:00 AM</option>
                                <option value="11:30:00">11:30 AM</option>
                                <option value="14:00:00">2:00 PM</option>
                                <option value="14:30:00">2:30 PM</option>
                                <option value="15:00:00">3:00 PM</option>
                                <option value="15:30:00">3:30 PM</option>
                                <option value="16:00:00">4:00 PM</option>
                                <option value="16:30:00">4:30 PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeRescheduleModal()" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" name="reschedule_appointment" class="btn-primary">
                            <i class="fas fa-edit mr-2"></i>Reschedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/momcare-app.js"></script>
    <script>
        // Modal functions
        function openBookingModal() {
            document.getElementById('bookingModal').classList.remove('hidden');
        }
        
        function closeBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }
        
        function rescheduleAppointment(appointmentId) {
            document.getElementById('reschedule_appointment_id').value = appointmentId;
            document.getElementById('rescheduleModal').classList.remove('hidden');
        }
        
        function closeRescheduleModal() {
            document.getElementById('rescheduleModal').classList.add('hidden');
        }
        
        function cancelAppointment(appointmentId) {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="appointment_id" value="${appointmentId}">
                    <input type="hidden" name="cancel_appointment" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Filter appointments
        function filterAppointments(status) {
            const rows = document.querySelectorAll('.appointment-row');
            const filterBtns = document.querySelectorAll('.filter-btn');
            
            // Update active filter button
            filterBtns.forEach(btn => {
                btn.classList.remove('active', 'bg-blue-100', 'text-blue-800');
                btn.classList.add('bg-gray-100', 'text-gray-800');
                if ((status === 'all' && btn.textContent === 'All') || 
                    (status !== 'all' && btn.textContent.toLowerCase() === status)) {
                    btn.classList.remove('bg-gray-100', 'text-gray-800');
                    btn.classList.add('active', 'bg-blue-100', 'text-blue-800');
                }
            });
            
            // Show/hide rows
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Calendar view (placeholder)
        function viewCalendar() {
            MomCare.showNotification('Calendar view feature coming soon!', 'info');
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
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
