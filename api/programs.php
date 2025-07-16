<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Ensure only admins can use
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    switch ($_POST['action'] ?? '') {
        case 'add_program':
            $program_code = trim($_POST['program_code'] ?? '');
            $program_name = trim($_POST['program_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $faculty_id = intval($_POST['faculty_id'] ?? 0);

            if (empty($program_code) || empty($program_name) || !$faculty_id) throw new Exception('All fields are required');

            // Check duplicate
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE program_code = ? OR program_name = ?");
            $stmt->execute([$program_code, $program_name]);
            if ($stmt->fetchColumn() > 0) throw new Exception('Program already exists');

            // Insert
            $stmt = $pdo->prepare("INSERT INTO programs (program_code, program_name, description, faculty_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$program_code, $program_name, $description, $faculty_id]);
            echo json_encode(['success' => true, 'message' => 'Program added']);
            break;

        case 'edit_program':
            $id = intval($_POST['program_id'] ?? 0);
            $program_code = trim($_POST['program_code'] ?? '');
            $program_name = trim($_POST['program_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $faculty_id = intval($_POST['faculty_id'] ?? 0);

            if (!$id || empty($program_code) || empty($program_name) || !$faculty_id) throw new Exception('All fields are required');

            // Check duplicate
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE (program_code = ? OR program_name = ?) AND program_id != ?");
            $stmt->execute([$program_code, $program_name, $id]);
            if ($stmt->fetchColumn() > 0) throw new Exception('Program already exists');

            // Update
            $stmt = $pdo->prepare("UPDATE programs SET program_code = ?, program_name = ?, description = ?, faculty_id = ? WHERE program_id = ?");
            $stmt->execute([$program_code, $program_name, $description, $faculty_id, $id]);
            echo json_encode(['success' => true, 'message' => 'Program updated']);
            break;

        case 'delete_program':
            $id = intval($_POST['program_id'] ?? 0);
            if (!$id) throw new Exception('Invalid ID');

            $stmt = $pdo->prepare("DELETE FROM programs WHERE program_id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Program deleted']);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
