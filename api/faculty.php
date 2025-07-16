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
        case 'add_faculty':
            $faculty_name = trim($_POST['faculty_name'] ?? '');
            if (empty($faculty_name)) throw new Exception('Faculty name is required');

            // Check duplicate
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM faculty WHERE faculty_name = ?");
            $stmt->execute([$faculty_name]);
            if ($stmt->fetchColumn() > 0) throw new Exception('Faculty already exists');

            // Insert
            $stmt = $pdo->prepare("INSERT INTO faculty (faculty_name) VALUES (?)");
            $stmt->execute([$faculty_name]);
            echo json_encode(['success' => true, 'message' => 'Faculty added']);
            break;

        case 'edit_faculty':
            $id = intval($_POST['faculty_id'] ?? 0);
            $faculty_name = trim($_POST['faculty_name'] ?? '');
            if (!$id || empty($faculty_name)) throw new Exception('Invalid data');

            // Check duplicate
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM faculty WHERE faculty_name = ? AND faculty_id != ?");
            $stmt->execute([$faculty_name, $id]);
            if ($stmt->fetchColumn() > 0) throw new Exception('Faculty name already exists');

            // Update
            $stmt = $pdo->prepare("UPDATE faculty SET faculty_name = ? WHERE faculty_id = ?");
            $stmt->execute([$faculty_name, $id]);
            echo json_encode(['success' => true, 'message' => 'Faculty updated']);
            break;

            case 'delete_faculty':
                $id = intval($_POST['faculty_id'] ?? 0);
                if (!$id) throw new Exception('Invalid ID');
            
                $stmt = $pdo->prepare("DELETE FROM faculty WHERE faculty_id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Faculty deleted']);
                break;
            

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
