<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    // Destroy session from database
    $sessionManager->destroy($_SESSION['session_token']);
}

// Destroy PHP session
session_destroy();

// Redirect to home page
header('Location: index.php');
exit();
?>
