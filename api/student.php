<?php
// Start buffer early and enable errors
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php'; // session started here

// Set header
header('Content-Type: application/json');

// Ensure PDO is available
global $pdo;

// Helper: JSON response with clean buffer
function sendJson($data) {
    ob_clean(); // Clear any previous output
    echo json_encode($data);
    exit();
}

// Validate session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    sendJson(['success' => false, 'message' => 'Unauthorized']);
}

// Validate DB connection
if (!$pdo) {
    sendJson(['success' => false, 'message' => 'Database connection error']);
}

// Email and phone validation
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function validatePhone($phone) {
    return preg_match("/^[\d\s\-\(\)]+$/", $phone);
}

try {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_student':
            $data = [
                'registration_number' => trim($_POST['registration_number'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
                'gender' => trim($_POST['gender'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'program_id' => intval($_POST['program_id'] ?? 0),
                'semester_id' => intval($_POST['semester_id'] ?? 0)
            ];

            foreach (['registration_number', 'first_name', 'last_name', 'email', 'date_of_birth', 'gender', 'program_id', 'semester_id'] as $field) {
                if (empty($data[$field])) {
                    sendJson(['success' => false, 'message' => 'All fields are required']);
                }
            }

            if (!validateEmail($data['email'])) {
                sendJson(['success' => false, 'message' => 'Invalid email format']);
            }
            if (!empty($data['phone']) && !validatePhone($data['phone'])) {
                sendJson(['success' => false, 'message' => 'Invalid phone number format']);
            }

            // Check duplicates
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetchColumn() > 0) {
                sendJson(['success' => false, 'message' => 'Email already exists']);
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE registration_number = ?");
            $stmt->execute([$data['registration_number']]);
            if ($stmt->fetchColumn() > 0) {
                sendJson(['success' => false, 'message' => 'Registration number already exists']);
            }

            // Validate foreign keys
            $stmt = $pdo->prepare("SELECT program_id FROM programs WHERE program_id = ?");
            $stmt->execute([$data['program_id']]);
            if (!$stmt->fetchColumn()) {
                sendJson(['success' => false, 'message' => 'Invalid program']);
            }

            $stmt = $pdo->prepare("SELECT semester_id FROM semesters WHERE semester_id = ?");
            $stmt->execute([$data['semester_id']]);
            if (!$stmt->fetchColumn()) {
                sendJson(['success' => false, 'message' => 'Invalid semester']);
            }

            // Insert student
            $stmt = $pdo->prepare("INSERT INTO students 
                (registration_number, first_name, last_name, date_of_birth, gender, phone, email, address, program_id, semester_id, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $success = $stmt->execute([
                $data['registration_number'],
                $data['first_name'],
                $data['last_name'],
                $data['date_of_birth'],
                $data['gender'],
                $data['phone'],
                $data['email'],
                $data['address'],
                $data['program_id'],
                $data['semester_id'],
                $_SESSION['user_id']
            ]);

            sendJson([
                'success' => $success,
                'message' => $success ? 'Student added successfully' : 'Error adding student'
            ]);
            break;

        case 'edit_student':
            $studentId = intval($_POST['student_id'] ?? 0);
            $data = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
                'gender' => trim($_POST['gender'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'program_id' => intval($_POST['program_id'] ?? 0),
                'semester_id' => intval($_POST['semester_id'] ?? 0),
                'registration_number' => trim($_POST['registration_number'] ?? '')
            ];

            foreach (['first_name', 'last_name', 'email', 'registration_number', 'date_of_birth', 'gender', 'program_id', 'semester_id'] as $field) {
                if (empty($data[$field])) {
                    sendJson(['success' => false, 'message' => 'All required fields must be filled']);
                }
            }

            if (!validateEmail($data['email'])) {
                sendJson(['success' => false, 'message' => 'Invalid email format']);
            }
            if (!empty($data['phone']) && !validatePhone($data['phone'])) {
                sendJson(['success' => false, 'message' => 'Invalid phone number format']);
            }

            $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
            $stmt->execute([$studentId]);
            if (!$stmt->fetchColumn()) {
                sendJson(['success' => false, 'message' => 'Student not found']);
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND student_id != ?");
            $stmt->execute([$data['email'], $studentId]);
            if ($stmt->fetchColumn() > 0) {
                sendJson(['success' => false, 'message' => 'Email already exists']);
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE registration_number = ? AND student_id != ?");
            $stmt->execute([$data['registration_number'], $studentId]);
            if ($stmt->fetchColumn() > 0) {
                sendJson(['success' => false, 'message' => 'Registration number already exists']);
            }

            $stmt = $pdo->prepare("SELECT program_id FROM programs WHERE program_id = ?");
            $stmt->execute([$data['program_id']]);
            if (!$stmt->fetchColumn()) {
                sendJson(['success' => false, 'message' => 'Invalid program']);
            }

            $stmt = $pdo->prepare("SELECT semester_id FROM semesters WHERE semester_id = ?");
            $stmt->execute([$data['semester_id']]);
            if (!$stmt->fetchColumn()) {
                sendJson(['success' => false, 'message' => 'Invalid semester']);
            }

            $stmt = $pdo->prepare("UPDATE students SET 
                first_name = ?, last_name = ?, date_of_birth = ?, gender = ?, 
                phone = ?, email = ?, address = ?, program_id = ?, semester_id = ?, 
                registration_number = ? 
                WHERE student_id = ?");
            $success = $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['date_of_birth'],
                $data['gender'],
                $data['phone'],
                $data['email'],
                $data['address'],
                $data['program_id'],
                $data['semester_id'],
                $data['registration_number'],
                $studentId
            ]);

            sendJson([
                'success' => $success,
                'message' => $success ? 'Student updated successfully' : 'Error updating student'
            ]);
            break;

        case 'delete_student':
            $studentId = intval($_POST['student_id'] ?? 0);
            if (!$studentId) sendJson(['success' => false, 'message' => 'Invalid student ID']);

            $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
            $stmt->execute([$studentId]);
            if (!$stmt->fetchColumn()) {
                sendJson(['success' => false, 'message' => 'Student not found']);
            }

            $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
            $success = $stmt->execute([$studentId]);

            $stmt = $pdo->prepare("DELETE FROM attendance WHERE student_id = ?");
            $stmt->execute([$studentId]);

            sendJson([
                'success' => $success,
                'message' => $success ? 'Student deleted successfully' : 'Error deleting student'
            ]);
            break;

        case 'get_student':
            $studentId = intval($_POST['student_id'] ?? 0);
            if (!$studentId) sendJson(['success' => false, 'message' => 'Invalid student ID']);

            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            sendJson([
                'success' => (bool)$student,
                'message' => $student ? 'Student found' : 'Student not found',
                'data' => $student
            ]);
            break;

        case 'get_all_students':
            $search = trim($_POST['search'] ?? '');
            $page = max(1, intval($_POST['page'] ?? 1));
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $query = "
                SELECT s.*, p.program_name, sem.semester_name 
                FROM students s 
                LEFT JOIN programs p ON s.program_id = p.program_id
                LEFT JOIN semesters sem ON s.semester_id = sem.semester_id
                WHERE 1=1
            ";

            if ($search) {
                $query .= " AND (
                    s.first_name LIKE :search OR
                    s.last_name LIKE :search OR
                    s.registration_number LIKE :search OR
                    s.email LIKE :search OR
                    p.program_name LIKE :search OR
                    sem.semester_name LIKE :search)";
            }

            $query .= " ORDER BY s.first_name LIMIT :offset, :limit";
            $stmt = $pdo->prepare($query);
            if ($search) $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalQuery = "
                SELECT COUNT(*) as total 
                FROM students s 
                LEFT JOIN programs p ON s.program_id = p.program_id
                LEFT JOIN semesters sem ON s.semester_id = sem.semester_id
                WHERE 1=1
            ";
            if ($search) {
                $totalQuery .= " AND (
                    s.first_name LIKE :search OR
                    s.last_name LIKE :search OR
                    s.registration_number LIKE :search OR
                    s.email LIKE :search OR
                    p.program_name LIKE :search OR
                    sem.semester_name LIKE :search)";
            }

            $totalStmt = $pdo->prepare($totalQuery);
            if ($search) $totalStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            $totalStmt->execute();

            $total = $totalStmt->fetch()['total'];
            $totalPages = ceil($total / $limit);

            sendJson([
                'success' => true,
                'data' => $students,
                'pagination' => [
                    'total_items' => $total,
                    'total_pages' => $totalPages,
                    'current_page' => $page,
                    'has_previous' => $page > 1,
                    'has_next' => $page < $totalPages
                ]
            ]);
            break;

        default:
            sendJson(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Student API Exception: " . $e->getMessage());
    sendJson(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
