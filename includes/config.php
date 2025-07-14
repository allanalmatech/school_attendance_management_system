<?php
// Configuration file for School Attendance Management System

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_attendance');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Settings
define('APP_NAME', 'School Attendance Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/school_attendance_management_system');

// Timezone
date_default_timezone_set('Asia/Kuwait');

// Session Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Error Reporting (Set to 0 in production)
define('DEBUG_MODE', 1);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Email Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-specific-password');

// Attendance Settings
define('ATTENDANCE_THRESHOLD', 75); // Minimum attendance percentage
define('LATE_THRESHOLD_MINUTES', 15); // Minutes after start time considered late

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour in seconds

// Logging Settings
define('LOG_FILE', __DIR__ . '/../logs/app.log');
define('LOG_LEVEL', 'debug'); // debug, info, warning, error

// Return the database connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
        );
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Helper function to get base URL
function getBaseUrl() {
    return BASE_URL;
}

// Helper function to log messages
function logMessage($level, $message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message\n";
    error_log($logEntry, 3, LOG_FILE);
}