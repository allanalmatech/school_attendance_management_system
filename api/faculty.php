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
    case 'add_faculty':
        $data = [
            'Faculty_name' => trim($_POST['Faculty_name'] ?? ''),
            'Email' => trim($_POST['Email'] ?? ''),
            'Phone' => trim($_POST['Phone'] ?? ''),
            'Department' => trim($_POST['Department'] ?? '')
        ];
        
        // Validate required fields
        if (empty($data['Faculty_name']) || empty($data['Email'])) {
            echo generateResponse(false, 'Faculty name and email are required');
            exit();
        }
        
        // Check if email already exists
        $existing = $pdo->query("
            SELECT COUNT(*) as count 
            FROM faculty 
            WHERE Email = '" . $pdo->quote($data['Email']) . "'
        ")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Email already exists');
            exit();
        }
        
        // Insert faculty record
        $stmt = $pdo->prepare("
            INSERT INTO faculty (Faculty_name, Email, Phone, Department)
            VALUES (?, ?, ?, ?)
        ");
        $success = $stmt->execute([
            $data['Faculty_name'],
            $data['Email'],
            $data['Phone'],
            $data['Department']
        ]);
        
        echo generateResponse($success, $success ? 'Faculty added successfully' : 'Error adding faculty');
        break;
        
    case 'edit_faculty':
        $facultyId = intval($_POST['faculty_id'] ?? 0);
        $data = [
            'Faculty_name' => trim($_POST['Faculty_name'] ?? ''),
            'Email' => trim($_POST['Email'] ?? ''),
            'Phone' => trim($_POST['Phone'] ?? ''),
            'Department' => trim($_POST['Department'] ?? '')
        ];
        
        // Validate required fields
        if (empty($data['Faculty_name']) || empty($data['Email'])) {
            echo generateResponse(false, 'Faculty name and email are required');
            exit();
        }
        
        // Check if faculty exists
        $faculty = $pdo->query("
            SELECT * FROM faculty 
            WHERE faculty_id = $facultyId
        ")->fetch();
        
        if (!$faculty) {
            echo generateResponse(false, 'Faculty not found');
            exit();
        }
        
        // Check if email is already used by another faculty
        $existing = $pdo->query("
            SELECT COUNT(*) as count 
            FROM faculty 
            WHERE Email = '" . $pdo->quote($data['Email']) . "'
            AND faculty_id != $facultyId
        ")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Email already exists');
            exit();
        }
        
        // Update faculty record
        $stmt = $pdo->prepare("
            UPDATE faculty 
            SET Faculty_name = ?, 
                Email = ?, 
                Phone = ?, 
                Department = ?
            WHERE faculty_id = ?
        ");
        $success = $stmt->execute([
            $data['Faculty_name'],
            $data['Email'],
            $data['Phone'],
            $data['Department'],
            $facultyId
        ]);
        
        echo generateResponse($success, $success ? 'Faculty updated successfully' : 'Error updating faculty');
        break;
        
    case 'delete_faculty':
        $facultyId = intval($_POST['faculty_id'] ?? 0);
        
        if (!$facultyId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Check if faculty exists
        $faculty = $pdo->query("
            SELECT * FROM faculty 
            WHERE faculty_id = $facultyId
        ")->fetch();
        
        if (!$faculty) {
            echo generateResponse(false, 'Faculty not found');
            exit();
        }
        
        // Delete faculty record
        $stmt = $pdo->prepare("
            DELETE FROM faculty 
            WHERE faculty_id = ?
        ");
        $success = $stmt->execute([$facultyId]);
        
        // Also delete any related assignments
        $pdo->query("
            DELETE FROM faculty_has_programs 
            WHERE faculty_id = $facultyId
        ");
        
        echo generateResponse($success, $success ? 'Faculty deleted successfully' : 'Error deleting faculty');
        break;
        
    case 'get_faculty':
        $facultyId = intval($_POST['faculty_id'] ?? 0);
        
        if (!$facultyId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        $faculty = $pdo->query("
            SELECT * FROM faculty 
            WHERE faculty_id = $facultyId
        ")->fetch();
        
        echo generateResponse($faculty ? true : false, 
            $faculty ? 'Faculty fetched successfully' : 'Faculty not found', 
            $faculty);
        break;
        
    case 'get_all_faculty':
        $search = trim($_POST['search'] ?? '');
        $page = intval($_POST['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT * FROM faculty 
            WHERE 1=1
        ";
        
        if ($search) {
            $query .= " AND (Faculty_name LIKE :search OR Email LIKE :search OR Department LIKE :search)";
        }
        
        $query .= " ORDER BY Faculty_name LIMIT :offset, :limit";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $faculty = $stmt->fetchAll();
        
        // Get total count for pagination
        $totalQuery = "
            SELECT COUNT(*) as total 
            FROM faculty 
            WHERE 1=1
        ";
        
        if ($search) {
            $totalQuery .= " AND (Faculty_name LIKE :search OR Email LIKE :search OR Department LIKE :search)";
        }
        
        $totalQuery .= " ORDER BY Faculty_name";
        
        $totalStmt = $pdo->prepare($totalQuery);
        $totalStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $totalStmt->execute();
        
        $totalFaculty = $totalStmt->fetch()['total'];
        
        // Get total pages for pagination
        $totalPages = ceil($totalFaculty / $limit);

        // Format the response data
        $responseData = array(
            'success' => $faculty ? true : false,
            'message' => $faculty ? 'Faculty fetched successfully' : 'Faculty not found',
            'data' => $faculty,
            'pagination' => array(
                'total_items' => $totalFaculty,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'items_per_page' => $limit,
                'has_previous' => $page > 1,
                'has_next' => $page < $totalPages
            )
        );

        echo json_encode($responseData);
        break;
    default:
        echo generateResponse(false, 'Invalid action');
        break;
}
?>