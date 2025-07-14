<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo generateResponse(false, 'Not logged in');
    exit();
}

// Get user's role
$role = $_SESSION['role'];

// Handle different actions
switch ($_GET['action'] ?? '') {
    case 'get_students':
        if ($role !== 'faculty') {
            echo generateResponse(false, 'Unauthorized');
            exit();
        }
        
        $courseId = intval($_GET['course_id'] ?? 0);
        $semesterId = intval($_GET['semester_id'] ?? 0);
        
        if (!$courseId || !$semesterId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Get students enrolled in the course's program
        $students = $pdo->query("
            SELECT s.*, p.Programme_name
            FROM students s
            JOIN student_has_programs shp ON s.ID = shp.student_id
            JOIN programs p ON shp.program_id = p.program_id
            JOIN programs_has_courses pc ON p.program_id = pc.program_id
            WHERE pc.course_id = $courseId
            AND pc.semester_id = $semesterId
            ORDER BY s.Student_name
        ")->fetchAll();
        
        echo generateResponse(true, 'Students fetched successfully', $students);
        break;
        
    case 'get_attendance':
        if ($role !== 'student') {
            echo generateResponse(false, 'Unauthorized');
            exit();
        }
        
        $studentId = $_SESSION['user_id'];
        $courseId = intval($_GET['course_id'] ?? 0);
        $semesterId = intval($_GET['semester_id'] ?? 0);
        
        if (!$courseId || !$semesterId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Get attendance records for the student
        $attendance = $pdo->query("
            SELECT a.date, a.present
            FROM attendance_has_courses a
            WHERE a.student_id = $studentId
            AND a.course_id = $courseId
            AND a.semester_id = $semesterId
            ORDER BY a.date DESC
        ")->fetchAll();
        
        echo generateResponse(true, 'Attendance fetched successfully', $attendance);
        break;
        
    default:
        echo generateResponse(false, 'Invalid action');
        break;
}