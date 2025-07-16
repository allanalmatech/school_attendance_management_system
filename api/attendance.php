<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    switch ($action) {
        case 'mark_attendance':
            $student_id = intval($data['student_id']);
            $program_id = intval($data['program_id']);
            $semester_id = intval($data['semester_id']);
            $course_id = intval($data['course_id']);
            $date = $data['attendance_date'];

            // Insert attendance session if not exists
            $stmt = $pdo->prepare("SELECT attendance_id FROM attendance WHERE Attendance_date = ?");
            $stmt->execute([$date]);
            $attendance_id = $stmt->fetchColumn();
            
            if (!$attendance_id) {
                $pdo->prepare("INSERT INTO attendance (Attendance_date) VALUES (?)")
                    ->execute([$date]);
                $attendance_id = $pdo->lastInsertId();
            }

            // Link attendance to semester & course
            $pdo->prepare("INSERT IGNORE INTO attendance_has_semester (attendance_id, semester_id) VALUES (?, ?)")
                ->execute([$attendance_id, $semester_id]);
            $pdo->prepare("INSERT IGNORE INTO attendance_has_courses (attendance_id, course_id) VALUES (?, ?)")
                ->execute([$attendance_id, $course_id]);

            // Mark student
            $pdo->prepare("INSERT IGNORE INTO attendance_has_student (attendance_id, student_ID, user_id) VALUES (?, ?, ?)")
                ->execute([$attendance_id, $student_id, 0]);

            // Get student name
            $stmt = $pdo->prepare("SELECT Student_name FROM student WHERE ID = ?");
            $stmt->execute([$student_id]);
            $student_name = $stmt->fetchColumn();

            echo json_encode(['success' => true, 'student_name' => $student_name]);
            break;

        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
