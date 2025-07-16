<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo generateResponse(false, 'Unauthorized');
    exit();
}

try {
    switch ($_POST['action'] ?? '') {
        case 'add_course':
            $course_code = sanitizeInput($_POST['course_code'] ?? '');
            $course_name = sanitizeInput($_POST['course_name'] ?? '');
            $credit_hours = intval($_POST['credit_hours'] ?? 0);
            $program_id = intval($_POST['program_id'] ?? 0);
            $semester_id = intval($_POST['semester_id'] ?? 0);

            if (!$course_code || !$course_name || !$credit_hours || !$program_id || !$semester_id) {
                throw new Exception('All fields are required');
            }

            // Check duplicate code
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ?");
            $stmt->execute([$course_code]);
            if ($stmt->fetchColumn() > 0) throw new Exception('Course code already exists');

            $data = [
                'course_code' => $course_code,
                'course_name' => $course_name,
                'credit_hours' => $credit_hours,
                'program_id' => $program_id,
                'semester_id' => $semester_id
            ];
            if (insertData('courses', $data)) {
                echo generateResponse(true, 'Course added successfully');
            } else {
                echo generateResponse(false, 'Error adding course');
            }
            break;

        case 'edit_course':
            $course_id = intval($_POST['course_id'] ?? 0);
            $course_code = sanitizeInput($_POST['course_code'] ?? '');
            $course_name = sanitizeInput($_POST['course_name'] ?? '');
            $credit_hours = intval($_POST['credit_hours'] ?? 0);
            $program_id = intval($_POST['program_id'] ?? 0);
            $semester_id = intval($_POST['semester_id'] ?? 0);

            if (!$course_id || !$course_code || !$course_name || !$credit_hours || !$program_id || !$semester_id) {
                throw new Exception('All fields are required');
            }

            // Check duplicate code (exclude current)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ? AND course_id != ?");
            $stmt->execute([$course_code, $course_id]);
            if ($stmt->fetchColumn() > 0) throw new Exception('Course code already exists');

            $data = [
                'course_code' => $course_code,
                'course_name' => $course_name,
                'credit_hours' => $credit_hours,
                'program_id' => $program_id,
                'semester_id' => $semester_id
            ];
            $where = "course_id = $course_id";
            if (updateData('courses', $data, $where)) {
                echo generateResponse(true, 'Course updated successfully');
            } else {
                echo generateResponse(false, 'Error updating course');
            }
            break;

        case 'delete_course':
            $course_id = intval($_POST['course_id'] ?? 0);
            if (!$course_id) throw new Exception('Invalid course ID');
            $where = "course_id = $course_id";
            if (deleteData('courses', $where)) {
                echo generateResponse(true, 'Course deleted successfully');
            } else {
                echo generateResponse(false, 'Error deleting course');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo generateResponse(false, $e->getMessage());
}