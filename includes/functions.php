<?php
function getTableData($table, $columns = '*', $where = null, $orderBy = null) {
    global $pdo;
    $sql = "SELECT $columns FROM $table";
    if ($where) {
        $sql .= " WHERE $where";
    }
    if ($orderBy) {
        $sql .= " ORDER BY $orderBy";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function insertData($table, $data) {
    global $pdo;
    $fields = implode(", ", array_keys($data));
    $values = ":" . implode(", :", array_keys($data));
    $sql = "INSERT INTO $table ($fields) VALUES ($values)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($data);
}

function updateData($table, $data, $where) {
    global $pdo;
    $set = array_map(function($field) { return "$field = :$field"; }, array_keys($data));
    $sql = "UPDATE $table SET " . implode(", ", $set) . " WHERE $where";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($data);
}

function deleteData($table, $where) {
    global $pdo;
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute();
}

function generateResponse($success, $message = '', $data = null) {
    return json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
}

function formatDateTime($datetime) {
    return date('Y-m-d H:i:s', strtotime($datetime));
}

function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}

function formatTime($time) {
    return date('H:i:s', strtotime($time));
}

function generateAttendanceStatus($present, $total) {
    if ($total == 0) return '-';
    $percentage = ($present / $total) * 100;
    return number_format($percentage, 2) . '%';
}

function getAttendanceColor($percentage) {
    if ($percentage >= 90) return 'success';
    if ($percentage >= 75) return 'warning';
    return 'danger';
}

function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Administrator',
        'faculty' => 'Faculty Member',
        'student' => 'Student'
    ];
    return $roles[$role] ?? ucfirst($role);
}

function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input));
}

function generateUniqueCode($length = 6) {
    $characters = '0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}