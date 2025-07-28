<?php
session_start();

// Database connection
require_once 'config/database.php';
require_once 'includes/classes.php';

$database = new Database();
$db = $database->connect();

// Initialize classes
$user = new User($db);
$sessionManager = new Session($db);
$chatMessage = new ChatMessage($db);

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['session_token']);
}

// Get current user
function getCurrentUser() {
    global $sessionManager;
    
    if(!isLoggedIn()) {
        return false;
    }
    
    $session_data = $sessionManager->validate($_SESSION['session_token']);
    if(!$session_data) {
        // Invalid session, clear it
        session_destroy();
        return false;
    }
    
    return $session_data;
}

// Redirect to login if not authenticated
function requireAuth() {
    if(!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate CSRF token
function generateCSRFToken() {
    if(!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Format date
function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}

// Get time ago format
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}

// AI Chat API (Mock implementation - replace with actual AI service)
function getAIResponse($message, $userContext = []) {
    // This is a mock implementation. In production, you would integrate with
    // an actual AI service like OpenAI, Google Gemini, or similar
    
    $responses = [
        'hello' => "Hello! I'm your AI pregnancy care assistant. How can I help you today?",
        'symptoms' => "I understand you're experiencing some symptoms. Can you tell me more about what you're feeling? Remember, if you have severe symptoms, please contact your healthcare provider immediately.",
        'nutrition' => "Proper nutrition is crucial during pregnancy. Focus on eating a balanced diet with plenty of fruits, vegetables, whole grains, lean proteins, and dairy. Make sure you're taking your prenatal vitamins as recommended by your doctor.",
        'exercise' => "Light to moderate exercise is generally safe and beneficial during pregnancy. Activities like walking, swimming, and prenatal yoga are great options. Always consult with your healthcare provider before starting any new exercise routine.",
        'appointment' => "It's important to keep up with your prenatal appointments. Would you like help scheduling your next appointment or setting up reminders?",
        'emergency' => "If you're experiencing severe symptoms like heavy bleeding, severe abdominal pain, persistent headaches, or difficulty breathing, please seek immediate medical attention or call emergency services.",
        'default' => "I'm here to help with your pregnancy journey. You can ask me about symptoms, nutrition, exercise, appointments, or any other pregnancy-related concerns. How can I assist you today?"
    ];
    
    $message = strtolower($message);
    
    // Simple keyword matching
    if(strpos($message, 'hello') !== false || strpos($message, 'hi') !== false) {
        return $responses['hello'];
    } elseif(strpos($message, 'symptom') !== false || strpos($message, 'feel') !== false || strpos($message, 'pain') !== false) {
        return $responses['symptoms'];
    } elseif(strpos($message, 'nutrition') !== false || strpos($message, 'eat') !== false || strpos($message, 'food') !== false) {
        return $responses['nutrition'];
    } elseif(strpos($message, 'exercise') !== false || strpos($message, 'workout') !== false) {
        return $responses['exercise'];
    } elseif(strpos($message, 'appointment') !== false || strpos($message, 'doctor') !== false) {
        return $responses['appointment'];
    } elseif(strpos($message, 'emergency') !== false || strpos($message, 'urgent') !== false) {
        return $responses['emergency'];
    } else {
        return $responses['default'];
    }
}

// File upload handling
function handleFileUpload($file, $user_id, $allowed_types = ['jpg', 'jpeg', 'png', 'pdf']) {
    global $db;
    
    if(!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if(!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    $max_size = 5 * 1024 * 1024; // 5MB
    if($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $upload_dir = 'uploads/';
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    if(move_uploaded_file($file['tmp_name'], $file_path)) {
        // Save to database
        $query = "INSERT INTO medical_documents (user_id, filename, original_filename, file_path, file_type, file_size) 
                  VALUES (:user_id, :filename, :original_filename, :file_path, :file_type, :file_size)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':original_filename', $file['name']);
        $stmt->bindParam(':file_path', $file_path);
        $stmt->bindParam(':file_type', $file['type']);
        $stmt->bindParam(':file_size', $file['size']);
        
        if($stmt->execute()) {
            return ['success' => true, 'filename' => $filename, 'path' => $file_path];
        }
    }
    
    return ['success' => false, 'message' => 'Upload failed'];
}
?>
