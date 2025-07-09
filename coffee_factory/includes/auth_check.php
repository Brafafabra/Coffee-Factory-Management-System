<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Log user activity
function log_activity($action, $description = '') {
    global $conn;
    
    $sql = "INSERT INTO system_logs (user_id, action, description, ip_address) 
            VALUES (?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $user_id = $_SESSION['user_id'] ?? null;
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        $stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}

// Check if user has required role
function require_role($required_role) {
    if ($_SESSION['role'] !== $required_role) {
        $_SESSION['error_message'] = "You don't have permission to access this page";
        header("Location: ../index.php");
        exit;
    }
}

// Check if user has any of the required roles
function require_any_role($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        $_SESSION['error_message'] = "You don't have permission to access this page";
        header("Location: ../index.php");
        exit;
    }
}
?>