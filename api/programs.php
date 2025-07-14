 
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
    case 'add_program':
        $data = [
            'Program_name' => trim($_POST['Program_name'] ?? ''),
            'Department' => trim($_POST['Department'] ?? ''),
            'Duration' => intval($_POST['Duration'] ?? 0)
        ];
        
        // Validate required fields
        if (empty($data['Program_name']) || empty($data['Department']) || $data['Duration'] <= 0) {
            echo generateResponse(false, 'Program name, department, and duration are required');
            exit();
        }
        
        // Check if program already exists
        $existing = $pdo->query("SELECT COUNT(*) as count FROM programs WHERE Program_name = '" . $pdo->quote($data['Program_name']) . "' AND Department = '" . $pdo->quote($data['Department']) . "'")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Program already exists in this department');
            exit();
        }
        
        // Insert program record
        $stmt = $pdo->prepare("INSERT INTO programs (Program_name, Department, Duration) VALUES (?, ?, ?)");
        $success = $stmt->execute([
            $data['Program_name'],
            $data['Department'],
            $data['Duration']
        ]);
        
        echo generateResponse($success, $success ? 'Program added successfully' : 'Error adding program');
        break;
        
    case 'edit_program':
        $programId = intval($_POST['program_id'] ?? 0);
        $data = [
            'Program_name' => trim($_POST['Program_name'] ?? ''),
            'Department' => trim($_POST['Department'] ?? ''),
            'Duration' => intval($_POST['Duration'] ?? 0)
        ];
        
        // Validate required fields
        if (empty($data['Program_name']) || empty($data['Department']) || $data['Duration'] <= 0) {
            echo generateResponse(false, 'Program name, department, and duration are required');
            exit();
        }
        
        // Check if program exists
        $program = $pdo->query("SELECT * FROM programs WHERE program_id = $programId")->fetch();
        
        if (!$program) {
            echo generateResponse(false, 'Program not found');
            exit();
        }
        
        // Check if program name already exists in this department (excluding current program)
        $existing = $pdo->query("
            SELECT COUNT(*) as count 
            FROM programs 
            WHERE Program_name = '" . $pdo->quote($data['Program_name']) . "' 
            AND Department = '" . $pdo->quote($data['Department']) . "'
            AND program_id != $programId
        ")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Program already exists in this department');
            exit();
        }
        
        // Update program record
        $stmt = $pdo->prepare("UPDATE programs SET Program_name = ?, Department = ?, Duration = ? WHERE program_id = ?");
        $success = $stmt->execute([
            $data['Program_name'],
            $data['Department'],
            $data['Duration'],
            $programId
        ]);
        
        echo generateResponse($success, $success ? 'Program updated successfully' : 'Error updating program');
        break;
        
    case 'delete_program':
        $programId = intval($_POST['program_id'] ?? 0);
        
        if (!$programId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Check if program exists
        $program = $pdo->query("SELECT * FROM programs WHERE program_id = $programId")->fetch();
        
        if (!$program) {
            echo generateResponse(false, 'Program not found');
            exit();
        }
        
        // Delete program record
        $stmt = $pdo->prepare("DELETE FROM programs WHERE program_id = ?");
        $success = $stmt->execute([$programId]);
        
        // Also delete any related assignments
        $pdo->query("DELETE FROM faculty_has_programs WHERE program_id = $programId");
        
        echo generateResponse($success, $success ? 'Program deleted successfully' : 'Error deleting program');
        break;
        
    case 'get_program':
        $programId = intval($_POST['program_id'] ?? 0);
        
        if (!$programId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        $program = $pdo->query("SELECT * FROM programs WHERE program_id = $programId")->fetch();
        
        echo generateResponse($program ? true : false, 
            $program ? 'Program fetched successfully' : 'Program not found', 
            $program);
        break;
        
    case 'get_all_programs':
        $search = trim($_POST['search'] ?? '');
        $page = intval($_POST['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT * FROM programs 
            WHERE 1=1
        ";
        
        if ($search) {
            $query .= " AND (Program_name LIKE :search OR Department LIKE :search)";
        }
        
        $query .= " ORDER BY Program_name LIMIT :offset, :limit";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $programs = $stmt->fetchAll();
        
        // Get total count for pagination
        $totalQuery = "
            SELECT COUNT(*) as total 
            FROM programs 
            WHERE 1=1
        ";
        
        if ($search) {
            $totalQuery .= " AND (Program_name LIKE :search OR Department LIKE :search)";
        }
        
        $totalQuery .= " ORDER BY Program_name";
        
        $totalStmt = $pdo->prepare($totalQuery);
        $totalStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $totalStmt->execute();
        
        $totalPrograms = $totalStmt->fetch()['total'];
        
        // Get total pages for pagination
        $totalPages = ceil($totalPrograms / $limit);
        
        // Format the response data
        $responseData = [
            'success' => $programs ? true : false,
            'message' => $programs ? 'Programs fetched successfully' : 'No programs found',
            'data' => $programs,
            'pagination' => [
                'total_items' => $totalPrograms,
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
        break;
}
?>
