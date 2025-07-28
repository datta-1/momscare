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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone_number = sanitizeInput($_POST['phone_number']);
    $date_of_birth = sanitizeInput($_POST['date_of_birth']);
    $expected_due_date = sanitizeInput($_POST['expected_due_date']);
    $emergency_contact_name = sanitizeInput($_POST['emergency_contact_name']);
    $emergency_contact_phone = sanitizeInput($_POST['emergency_contact_phone']);
    $user_id = $current_user['user_id'];
    
    // Check if email is already used by another user
    $query = "SELECT user_id FROM users WHERE email = :email AND user_id != :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $error = 'This email is already registered to another account.';
    } else {
        // Update user profile
        $query = "UPDATE users SET 
                  full_name = :full_name, 
                  email = :email, 
                  phone_number = :phone_number, 
                  date_of_birth = :date_of_birth, 
                  expected_due_date = :expected_due_date,
                  emergency_contact_name = :emergency_contact_name,
                  emergency_contact_phone = :emergency_contact_phone
                  WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':date_of_birth', $date_of_birth);
        $stmt->bindParam(':expected_due_date', $expected_due_date);
        $stmt->bindParam(':emergency_contact_name', $emergency_contact_name);
        $stmt->bindParam(':emergency_contact_phone', $emergency_contact_phone);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully!';
            $current_user = getCurrentUser(); // Refresh user data
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $current_user['user_id'];
    
    // Verify current password
    if (!password_verify($current_password, $current_user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = :password WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            $success = 'Password changed successfully!';
        } else {
            $error = 'Failed to change password. Please try again.';
        }
    }
}

// Handle notification preferences
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
    $appointment_reminders = isset($_POST['appointment_reminders']) ? 1 : 0;
    $medication_reminders = isset($_POST['medication_reminders']) ? 1 : 0;
    $weekly_tips = isset($_POST['weekly_tips']) ? 1 : 0;
    $user_id = $current_user['user_id'];
    
    // Check if notification preferences exist
    $query = "SELECT user_id FROM notification_preferences WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Update existing preferences
        $query = "UPDATE notification_preferences SET 
                  email_notifications = :email_notifications,
                  sms_notifications = :sms_notifications,
                  appointment_reminders = :appointment_reminders,
                  medication_reminders = :medication_reminders,
                  weekly_tips = :weekly_tips
                  WHERE user_id = :user_id";
    } else {
        // Insert new preferences
        $query = "INSERT INTO notification_preferences 
                  (user_id, email_notifications, sms_notifications, appointment_reminders, medication_reminders, weekly_tips)
                  VALUES (:user_id, :email_notifications, :sms_notifications, :appointment_reminders, :medication_reminders, :weekly_tips)";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':email_notifications', $email_notifications);
    $stmt->bindParam(':sms_notifications', $sms_notifications);
    $stmt->bindParam(':appointment_reminders', $appointment_reminders);
    $stmt->bindParam(':medication_reminders', $medication_reminders);
    $stmt->bindParam(':weekly_tips', $weekly_tips);
    
    if ($stmt->execute()) {
        $success = 'Notification preferences updated successfully!';
    } else {
        $error = 'Failed to update notification preferences. Please try again.';
    }
}

// Get current notification preferences
$query = "SELECT * FROM notification_preferences WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $current_user['user_id']);
$stmt->execute();
$notification_prefs = $stmt->fetch(PDO::FETCH_ASSOC);

// Set defaults if no preferences exist
if (!$notification_prefs) {
    $notification_prefs = [
        'email_notifications' => 1,
        'sms_notifications' => 0,
        'appointment_reminders' => 1,
        'medication_reminders' => 1,
        'weekly_tips' => 1
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - MOMCARE ⚙️</title>
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
                            <i class="fas fa-cog text-gray-600 mr-3"></i>Account Settings
                        </h1>
                        <p class="text-gray-600">Manage your profile, preferences, and account security</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Last updated</div>
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo date('M j, Y g:i A', strtotime($current_user['updated_at'] ?? $current_user['created_at'])); ?>
                        </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                    <nav class="space-y-2">
                        <button onclick="showSection('profile')" class="settings-nav-item active w-full text-left px-4 py-3 rounded-lg transition-colors">
                            <i class="fas fa-user mr-3"></i>Profile Information
                        </button>
                        <button onclick="showSection('security')" class="settings-nav-item w-full text-left px-4 py-3 rounded-lg transition-colors">
                            <i class="fas fa-lock mr-3"></i>Security & Password
                        </button>
                        <button onclick="showSection('notifications')" class="settings-nav-item w-full text-left px-4 py-3 rounded-lg transition-colors">
                            <i class="fas fa-bell mr-3"></i>Notifications
                        </button>
                        <button onclick="showSection('privacy')" class="settings-nav-item w-full text-left px-4 py-3 rounded-lg transition-colors">
                            <i class="fas fa-shield-alt mr-3"></i>Privacy & Data
                        </button>
                        <button onclick="showSection('support')" class="settings-nav-item w-full text-left px-4 py-3 rounded-lg transition-colors">
                            <i class="fas fa-question-circle mr-3"></i>Help & Support
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Profile Information Section -->
                <div id="profileSection" class="settings-section bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-user text-2xl text-blue-600 mr-3"></i>
                        <h2 class="text-2xl font-bold text-gray-900">Profile Information</h2>
                    </div>
                    
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($current_user['full_name']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($current_user['phone_number'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                <input type="date" name="date_of_birth" value="<?php echo $current_user['date_of_birth'] ?? ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expected Due Date</label>
                                <input type="date" name="expected_due_date" value="<?php echo $current_user['expected_due_date'] ?? ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account Status</label>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-2"></i>Active
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Emergency Contact</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Name</label>
                                    <input type="text" name="emergency_contact_name" value="<?php echo htmlspecialchars($current_user['emergency_contact_name'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                    <input type="tel" name="emergency_contact_phone" value="<?php echo htmlspecialchars($current_user['emergency_contact_phone'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" name="update_profile" class="btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Section -->
                <div id="securitySection" class="settings-section bg-white rounded-xl shadow-lg p-6 mb-6 hidden">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-lock text-2xl text-red-600 mr-3"></i>
                        <h2 class="text-2xl font-bold text-gray-900">Security & Password</h2>
                    </div>
                    
                    <form method="POST" class="space-y-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-yellow-800">Password Security</h3>
                                    <p class="text-sm text-yellow-700 mt-1">Choose a strong password that includes uppercase letters, lowercase letters, numbers, and special characters.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                            <input type="password" name="current_password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                            <input type="password" name="new_password" required minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" required minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" name="change_password" class="btn-primary">
                                <i class="fas fa-key mr-2"></i>Change Password
                            </button>
                        </div>
                    </form>
                    
                    <div class="border-t pt-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Activity</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <div>
                                    <div class="font-medium text-gray-900">Last Login</div>
                                    <div class="text-sm text-gray-500">Your most recent sign-in activity</div>
                                </div>
                                <div class="text-sm text-gray-900">
                                    <?php echo date('M j, Y g:i A', strtotime($current_user['last_login'] ?? $current_user['created_at'])); ?>
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <div>
                                    <div class="font-medium text-gray-900">Account Created</div>
                                    <div class="text-sm text-gray-500">When you joined MOMCARE</div>
                                </div>
                                <div class="text-sm text-gray-900">
                                    <?php echo date('M j, Y', strtotime($current_user['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Section -->
                <div id="notificationsSection" class="settings-section bg-white rounded-xl shadow-lg p-6 mb-6 hidden">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-bell text-2xl text-green-600 mr-3"></i>
                        <h2 class="text-2xl font-bold text-gray-900">Notification Preferences</h2>
                    </div>
                    
                    <form method="POST" class="space-y-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-4 border-b border-gray-100">
                                <div class="flex-1">
                                    <h3 class="text-base font-medium text-gray-900">Email Notifications</h3>
                                    <p class="text-sm text-gray-500">Receive updates and alerts via email</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="email_notifications" <?php echo $notification_prefs['email_notifications'] ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between py-4 border-b border-gray-100">
                                <div class="flex-1">
                                    <h3 class="text-base font-medium text-gray-900">SMS Notifications</h3>
                                    <p class="text-sm text-gray-500">Receive text messages for urgent updates</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="sms_notifications" <?php echo $notification_prefs['sms_notifications'] ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between py-4 border-b border-gray-100">
                                <div class="flex-1">
                                    <h3 class="text-base font-medium text-gray-900">Appointment Reminders</h3>
                                    <p class="text-sm text-gray-500">Get reminders before your appointments</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="appointment_reminders" <?php echo $notification_prefs['appointment_reminders'] ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between py-4 border-b border-gray-100">
                                <div class="flex-1">
                                    <h3 class="text-base font-medium text-gray-900">Medication Reminders</h3>
                                    <p class="text-sm text-gray-500">Never miss your prenatal vitamins and medications</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="medication_reminders" <?php echo $notification_prefs['medication_reminders'] ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between py-4">
                                <div class="flex-1">
                                    <h3 class="text-base font-medium text-gray-900">Weekly Pregnancy Tips</h3>
                                    <p class="text-sm text-gray-500">Receive helpful tips and insights for your pregnancy journey</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="weekly_tips" <?php echo $notification_prefs['weekly_tips'] ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" name="update_notifications" class="btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Preferences
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Privacy Section -->
                <div id="privacySection" class="settings-section bg-white rounded-xl shadow-lg p-6 mb-6 hidden">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-shield-alt text-2xl text-purple-600 mr-3"></i>
                        <h2 class="text-2xl font-bold text-gray-900">Privacy & Data</h2>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <i class="fas fa-info-circle text-blue-600 mr-3 mt-1"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-blue-800">Your Privacy Matters</h3>
                                    <p class="text-sm text-blue-700 mt-1">We are committed to protecting your personal health information and comply with HIPAA regulations.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-4 border-b border-gray-100">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900">Data Usage</h3>
                                    <p class="text-sm text-gray-500">How your health data is used to improve your care</p>
                                </div>
                                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Details
                                </button>
                            </div>
                            
                            <div class="flex justify-between items-center py-4 border-b border-gray-100">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900">Data Sharing</h3>
                                    <p class="text-sm text-gray-500">Information shared with healthcare providers</p>
                                </div>
                                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Manage
                                </button>
                            </div>
                            
                            <div class="flex justify-between items-center py-4 border-b border-gray-100">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900">Download Your Data</h3>
                                    <p class="text-sm text-gray-500">Export your health records and account data</p>
                                </div>
                                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Download
                                </button>
                            </div>
                            
                            <div class="flex justify-between items-center py-4">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900">Delete Account</h3>
                                    <p class="text-sm text-gray-500">Permanently remove your account and data</p>
                                </div>
                                <button class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Request Deletion
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support Section -->
                <div id="supportSection" class="settings-section bg-white rounded-xl shadow-lg p-6 mb-6 hidden">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-question-circle text-2xl text-yellow-600 mr-3"></i>
                        <h2 class="text-2xl font-bold text-gray-900">Help & Support</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-book text-blue-600 mr-3"></i>
                                    <h3 class="font-medium text-gray-900">User Guide</h3>
                                </div>
                                <p class="text-sm text-gray-600">Learn how to use all MOMCARE features</p>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-question text-green-600 mr-3"></i>
                                    <h3 class="font-medium text-gray-900">FAQ</h3>
                                </div>
                                <p class="text-sm text-gray-600">Frequently asked questions and answers</p>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-headset text-purple-600 mr-3"></i>
                                    <h3 class="font-medium text-gray-900">Contact Support</h3>
                                </div>
                                <p class="text-sm text-gray-600">Get help from our support team</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-bug text-red-600 mr-3"></i>
                                    <h3 class="font-medium text-gray-900">Report Issue</h3>
                                </div>
                                <p class="text-sm text-gray-600">Report bugs or technical problems</p>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-lightbulb text-yellow-600 mr-3"></i>
                                    <h3 class="font-medium text-gray-900">Suggest Feature</h3>
                                </div>
                                <p class="text-sm text-gray-600">Share ideas for new features</p>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-file-alt text-gray-600 mr-3"></i>
                                    <h3 class="font-medium text-gray-900">Privacy Policy</h3>
                                </div>
                                <p class="text-sm text-gray-600">Read our privacy policy and terms</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Need Immediate Help?</h3>
                            <p class="text-gray-600 mb-4">For urgent medical concerns, contact your healthcare provider or emergency services.</p>
                            <div class="flex justify-center space-x-4">
                                <button class="btn-primary">
                                    <i class="fas fa-phone mr-2"></i>Emergency: 911
                                </button>
                                <button class="btn-secondary">
                                    <i class="fas fa-comments mr-2"></i>Live Chat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/momcare-app.js"></script>
    <script>
        // Settings navigation
        function showSection(sectionName) {
            // Hide all sections
            const sections = document.querySelectorAll('.settings-section');
            sections.forEach(section => section.classList.add('hidden'));
            
            // Show selected section
            document.getElementById(sectionName + 'Section').classList.remove('hidden');
            
            // Update navigation
            const navItems = document.querySelectorAll('.settings-nav-item');
            navItems.forEach(item => {
                item.classList.remove('active', 'bg-blue-100', 'text-blue-800');
                item.classList.add('text-gray-600', 'hover:bg-gray-100');
            });
            
            // Activate selected nav item
            event.target.classList.remove('text-gray-600', 'hover:bg-gray-100');
            event.target.classList.add('active', 'bg-blue-100', 'text-blue-800');
        }
        
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.querySelector('input[name="new_password"]');
            const confirmPassword = document.querySelector('input[name="confirm_password"]');
            
            if (newPassword && confirmPassword) {
                confirmPassword.addEventListener('input', function() {
                    if (this.value !== newPassword.value) {
                        this.setCustomValidity('Passwords do not match');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
            
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
