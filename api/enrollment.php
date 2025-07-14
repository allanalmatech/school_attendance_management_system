<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo generateResponse(false, 'Unauthorized');
    exit();
}

// Handle different actions
switch ($_POST['action'] ?? '') {
    case 'enroll_student':
        $studentId = intval($_POST['student_id'] ?? 0);
        $programId = intval($_POST['program_id'] ?? 0);
        
        if (!$studentId || !$programId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Check if student is already enrolled in this program
        $existing = $pdo->query("
            SELECT COUNT(*) as count 
            FROM student_has_programs 
            WHERE student_id = $studentId 
            AND program_id = $programId
        ")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Student is already enrolled in this program');
            exit();
        }
        
        // Insert enrollment record
        $stmt = $pdo->prepare("
            INSERT INTO student_has_programs (student_id, program_id, enrollment_date)
            VALUES (?, ?, ?)
        ");
        $success = $stmt->execute([$studentId, $programId, date('Y-m-d')]);
        
        echo generateResponse($success, $success ? 'Student enrolled successfully' : 'Error enrolling student');
        break;
        
    case 'assign_course':
        $programId = intval($_POST['program_id'] ?? 0);
        $courseId = intval($_POST['course_id'] ?? 0);
        $semesterId = intval($_POST['semester_id'] ?? 0);
        
        if (!$programId || !$courseId || !$semesterId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Check if course is already assigned to this program in this semester
        $existing = $pdo->query("
            SELECT COUNT(*) as count 
            FROM programs_has_courses 
            WHERE program_id = $programId 
            AND course_id = $courseId
            AND semester_id = $semesterId
        ")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Course is already assigned to this program in this semester');
            exit();
        }
        
        // Insert course assignment
        $stmt = $pdo->prepare("
            INSERT INTO programs_has_courses (program_id, course_id, semester_id)
            VALUES (?, ?, ?)
        ");
        $success = $stmt->execute([$programId, $courseId, $semesterId]);
        
        echo generateResponse($success, $success ? 'Course assigned successfully' : 'Error assigning course');
        break;
        
    case 'assign_faculty':
        $facultyId = intval($_POST['faculty_id'] ?? 0);
        $programId = intval($_POST['program_id'] ?? 0);
        
        if (!$facultyId || !$programId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Check if faculty is already assigned to this program
        $existing = $pdo->query("
            SELECT COUNT(*) as count 
            FROM faculty_has_programs 
            WHERE faculty_id = $facultyId 
            AND program_id = $programId
        ")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Faculty is already assigned to this program');
            exit();
        }
        
        // Insert faculty assignment
        $stmt = $pdo->prepare("
            INSERT INTO faculty_has_programs (faculty_id, program_id)
            VALUES (?, ?)
        ");
        $success = $stmt->execute([$facultyId, $programId]);
        
        echo generateResponse($success, $success ? 'Faculty assigned successfully' : 'Error assigning faculty');
        break;
        
    case 'delete_assignment':
        $facultyId = intval($_POST['faculty_id'] ?? 0);
        $programId = intval($_POST['program_id'] ?? 0);
        
        if (!$facultyId || !$programId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Delete faculty assignment
        $stmt = $pdo->prepare("
            DELETE FROM faculty_has_programs 
            WHERE faculty_id = ? 
            AND program_id = ?
        ");
        $success = $stmt->execute([$facultyId, $programId]);
        
        echo generateResponse($success, $success ? 'Assignment deleted successfully' : 'Error deleting assignment');
        break;
        
    default:
        echo generateResponse(false, 'Invalid action');
        break;
}