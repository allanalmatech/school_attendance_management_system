<?php
require_once __DIR__ . '/config.php';

session_start();

// Initialize database connection
$pdo = getDBConnection();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit();
    }
}

function requireRole($role) {
    if (!hasRole($role)) {
        header('Location: ../dashboard.php');
        exit();
    }
}

function login($username, $password) {
    global $pdo;
    try {
        // Log the login attempt
        error_log("Login attempt for username: " . $username);
        
        // First check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            error_log("User not found: " . $username);
            return false;
        }
        
        // Check password
        if (password_verify($password, $user['password'])) {
            error_log("Login successful for: " . $username);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            return true;
        } else {
            error_log("Invalid password for: " . $username);
            return false;
        }
    } catch (PDOException $e) {
        error_log("Database error in login: " . $e->getMessage());
        return false;
    }
}

function logout() {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

function getCurrentUser() {
    if (isLoggedIn()) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database error in getCurrentUser: " . $e->getMessage());
            return null;
        }
    }
    return null;
}