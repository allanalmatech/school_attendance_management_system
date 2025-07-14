 
<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo generateResponse(false, 'Unauthorized');
    exit();
}

// Check database connection
if (!$pdo) {
    echo generateResponse(false, 'Database connection error');
    exit();
}

// Helper function for validating email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function for validating phone number
function validatePhone($phone) {
    // Basic phone number validation (allowing digits and special characters)
    return preg_match("/^[\d\s\-\(\)]+$/", $phone);
}

// Handle different actions
try {
    switch ($_POST['action'] ?? '') {
    case 'add_student':
        $data = [
            'Student_name' => trim($_POST['Student_name'] ?? ''),
            'Email' => trim($_POST['Email'] ?? ''),
            'Phone' => trim($_POST['Phone'] ?? ''),
            'Program_id' => intval($_POST['Program_id'] ?? 0),
            'Semester_id' => intval($_POST['Semester_id'] ?? 0),
            'Registration_number' => trim($_POST['Registration_number'] ?? '')
        ];
        
        // Validate required fields
        if (empty($data['Student_name']) || empty($data['Email']) || empty($data['Registration_number'])) {
            echo generateResponse(false, 'Student name, email, and registration number are required');
            exit();
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE Email = ?");
        $stmt->execute([$data['Email']]);
        $existing = $stmt->fetch()['count'];
        
        if ($existing > 0) {
            throw new Exception('Email already exists');
        }
        
        // Check if registration number already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE Registration_number = ?");
        $stmt->execute([$data['Registration_number']]);
        $existing = $stmt->fetch()['count'];
        
        if ($existing > 0) {
            throw new Exception('Registration number already exists');
        }
        
        // Check if program exists
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE program_id = ?");
$stmt->execute([$data['Program_id']]);
$program = $stmt->fetch();
        
        // Check if semester exists
        $stmt = $pdo->prepare("SELECT * FROM semesters WHERE semester_id = ?");
$stmt->execute([$data['Semester_id']]);
$semester = $stmt->fetch();
        
        // Validate input formats
        if (!validateEmail($data['Email'])) {
            throw new Exception('Invalid email format');
        }
        if (!validatePhone($data['Phone'])) {
            throw new Exception('Invalid phone number format');
        }
        
        // Insert student record
        $stmt = $pdo->prepare("INSERT INTO students 
            (first_name, last_name, date_of_birth, gender, phone, email, 
            program_id, semester_id, registration_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['date_of_birth'],
            $data['gender'],
            $data['Phone'],
            $data['Email'],
            $data['Program_id'],
            $data['Semester_id'],
            $data['Registration_number']
        ]);
        
        if (!$success) {
            throw new Exception('Error adding student');
        }
        
        echo generateResponse(true, 'Student added successfully');
        break;
        
    case 'edit_student':
        $studentId = intval($_POST['student_id'] ?? 0);
        $data = [
            'Student_name' => trim($_POST['Student_name'] ?? ''),
            'Email' => trim($_POST['Email'] ?? ''),
            'Phone' => trim($_POST['Phone'] ?? ''),
            'Program_id' => intval($_POST['Program_id'] ?? 0),
            'Semester_id' => intval($_POST['Semester_id'] ?? 0),
            'Registration_number' => trim($_POST['Registration_number'] ?? '')
        ];
        
        // Validate required fields
        if (empty($data['Student_name']) || empty($data['Email']) || empty($data['Registration_number'])) {
            echo generateResponse(false, 'Student name, email, and registration number are required');
            exit();
        }
        
        // Check if student exists
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();
        if (!$student) {
            echo generateResponse(false, 'Student not found');
            exit();
        }
        
        // Check if email already exists (excluding current student)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM students 
            WHERE Email = ?
            AND student_id != ?
        ");
        $stmt->execute([$data['Email'], $studentId]);
        $existing = $stmt->fetch()['count'];
        
        if ($existing > 0) {
            throw new Exception('Email already exists');
        }
        
        // Check if registration number already exists (excluding current student)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM students 
            WHERE Registration_number = ?
            AND student_id != ?
        ");
        $stmt->execute([$data['Registration_number'], $studentId]);
        $existing = $stmt->fetch()['count'];
        
        if ($existing > 0) {
            throw new Exception('Registration number already exists');
        }
        
        // Check if program exists
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE program_id = ?");
        $stmt->execute([$data['Program_id']]);
        $program = $stmt->fetch();
        if (!$program) {
            throw new Exception('Program not found');
        }
        
        // Check if semester exists
        $stmt = $pdo->prepare("SELECT * FROM semesters WHERE semester_id = ?");
        $stmt->execute([$data['Semester_id']]);
        $semester = $stmt->fetch();
        if (!$semester) {
            throw new Exception('Semester not found');
        }
        
        // Update student record
        $stmt = $pdo->prepare("UPDATE students SET 
            Student_name = ?, 
            Email = ?, 
            Phone = ?, 
            Program_id = ?, 
            Semester_id = ?, 
            Registration_number = ? 
            WHERE student_id = ?");
        $success = $stmt->execute([
            $data['Student_name'],
            $data['Email'],
            $data['Phone'],
            $data['Program_id'],
            $data['Semester_id'],
            $data['Registration_number'],
            $studentId
        ]);
        
        if (!$success) {
            throw new Exception('Error updating student');
        }
        
        echo generateResponse(true, 'Student updated successfully');
        break;
        
    case 'delete_student':
        $studentId = intval($_POST['student_id'] ?? 0);
        
        if (!$studentId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Check if student exists
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();
        if (!$student) {
            echo generateResponse(false, 'Student not found');
            exit();
        }
        
        // Delete student record
        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
        $success = $stmt->execute([$studentId]);
        
        if (!$success) {
            throw new Exception('Error deleting student');
        }
        
        // Also delete any related attendance records
        $stmt = $pdo->prepare("DELETE FROM attendance WHERE student_id = ?");
        $stmt->execute([$studentId]);
        
        echo generateResponse(true, 'Student deleted successfully');
        break;
        
    case 'get_student':
        $studentId = intval($_POST['student_id'] ?? 0);
        
        if (!$studentId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        $student = $pdo->query("SELECT * FROM students WHERE student_id = $studentId")->fetch();
        
        echo generateResponse($student ? true : false, 
            $student ? 'Student fetched successfully' : 'Student not found', 
            $student);
        break;
        
    case 'get_all_students':
        $search = trim($_POST['search'] ?? '');
        $page = intval($_POST['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT s.*, p.Program_name, sem.Semester_name 
            FROM students s 
            LEFT JOIN programs p ON s.Program_id = p.program_id
            LEFT JOIN semesters sem ON s.Semester_id = sem.semester_id
            WHERE 1=1
        ";
        
        if ($search) {
            $query .= " AND (s.Student_name LIKE :search 
                OR s.Email LIKE :search 
                OR s.Registration_number LIKE :search 
                OR p.Program_name LIKE :search 
                OR sem.Semester_name LIKE :search)";
        }
        
        $query .= " ORDER BY s.Student_name LIMIT :offset, :limit";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count for pagination
        $totalQuery = "
            SELECT COUNT(*) as total 
            FROM students s 
            LEFT JOIN programs p ON s.Program_id = p.program_id
            LEFT JOIN semesters sem ON s.Semester_id = sem.semester_id
            WHERE 1=1
        ";
        
        if ($search) {
            $totalQuery .= " AND (s.Student_name LIKE :search 
                OR s.Email LIKE :search 
                OR s.Registration_number LIKE :search 
                OR p.Program_name LIKE :search 
                OR sem.Semester_name LIKE :search)";
        }
        
        $totalQuery .= " ORDER BY s.Student_name";
        
        $totalStmt = $pdo->prepare($totalQuery);
        $totalStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $totalStmt->execute();
        
        $totalStudents = $totalStmt->fetch()['total'];
        
        // Get total pages for pagination
        $totalPages = ceil($totalStudents / $limit);
        
        // Format the response data
        $responseData = [
            'success' => $students ? true : false,
            'message' => $students ? 'Students fetched successfully' : 'No students found',
            'data' => $students,
            'pagination' => [
                'total_items' => $totalStudents,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'items_per_page' => $limit,
                'has_previous' => $page > 1,
                'has_next' => $page < $totalPages
            ]
        ];
        
        echo json_encode($responseData);
        break;
    default:
        echo generateResponse(false, 'Invalid action');
    }
} catch (Exception $e) {
    // Log the error
    error_log("Student API Error: " . $e->getMessage());
    echo generateResponse(false, 'An error occurred while processing your request: ' . $e->getMessage());
}

?>
