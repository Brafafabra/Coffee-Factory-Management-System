<?php
// Only start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', 'http://localhost/coffee_factory/');
define('SYSTEM_NAME', 'Mbilini Coffee Factory Management System');
define('SYSTEM_VERSION', '1.0.0');
?>
