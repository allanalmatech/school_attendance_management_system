 
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
    case 'add_semester':
        $data = [
            'Semester_name' => trim($_POST['Semester_name'] ?? ''),
            'Start_date' => trim($_POST['Start_date'] ?? ''),
            'End_date' => trim($_POST['End_date'] ?? ''),
            'Status' => trim($_POST['Status'] ?? 'active')
        ];
        
        // Validate required fields
        if (empty($data['Semester_name']) || empty($data['Start_date']) || empty($data['End_date'])) {
            echo generateResponse(false, 'Semester name, start date, and end date are required');
            exit();
        }
        
        // Validate date format
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['Start_date']) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['End_date'])) {
            echo generateResponse(false, 'Invalid date format. Please use YYYY-MM-DD');
            exit();
        }
        
        // Validate if start date is before end date
        if (strtotime($data['Start_date']) >= strtotime($data['End_date'])) {
            echo generateResponse(false, 'Start date must be before end date');
            exit();
        }
        
        // Check if semester name already exists
        $existing = $pdo->query("SELECT COUNT(*) as count FROM semesters WHERE Semester_name = '" . $pdo->quote($data['Semester_name']) . "'")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Semester name already exists');
            exit();
        }
        
        // Insert semester record
        $stmt = $pdo->prepare("INSERT INTO semesters (Semester_name, Start_date, End_date, Status) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([
            $data['Semester_name'],
            $data['Start_date'],
            $data['End_date'],
            $data['Status']
        ]);
        
        echo generateResponse($success, $success ? 'Semester added successfully' : 'Error adding semester');
        break;
        
    case 'edit_semester':
        $semesterId = intval($_POST['semester_id'] ?? 0);
        $data = [
            'Semester_name' => trim($_POST['Semester_name'] ?? ''),
            'Start_date' => trim($_POST['Start_date'] ?? ''),
            'End_date' => trim($_POST['End_date'] ?? ''),
            'Status' => trim($_POST['Status'] ?? 'active')
        ];
        
        // Validate required fields
        if (empty($data['Semester_name']) || empty($data['Start_date']) || empty($data['End_date'])) {
            echo generateResponse(false, 'Semester name, start date, and end date are required');
            exit();
        }
        
        // Validate date format
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['Start_date']) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['End_date'])) {
            echo generateResponse(false, 'Invalid date format. Please use YYYY-MM-DD');
            exit();
        }
        
        // Validate if start date is before end date
        if (strtotime($data['Start_date']) >= strtotime($data['End_date'])) {
            echo generateResponse(false, 'Start date must be before end date');
            exit();
        }
        
        // Check if semester exists
        $semester = $pdo->query("SELECT * FROM semesters WHERE semester_id = $semesterId")->fetch();
        
        if (!$semester) {
            echo generateResponse(false, 'Semester not found');
            exit();
        }
        
        // Check if semester name already exists (excluding current semester)
        $existing = $pdo->query("
            SELECT COUNT(*) as count 
            FROM semesters 
            WHERE Semester_name = '" . $pdo->quote($data['Semester_name']) . "'
            AND semester_id != $semesterId
        ")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Semester name already exists');
            exit();
        }
        
        // Update semester record
        $stmt = $pdo->prepare("UPDATE semesters SET Semester_name = ?, Start_date = ?, End_date = ?, Status = ? WHERE semester_id = ?");
        $success = $stmt->execute([
            $data['Semester_name'],
            $data['Start_date'],
            $data['End_date'],
            $data['Status'],
            $semesterId
        ]);
        
        echo generateResponse($success, $success ? 'Semester updated successfully' : 'Error updating semester');
        break;
        
    case 'delete_semester':
        $semesterId = intval($_POST['semester_id'] ?? 0);
        
        if (!$semesterId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        // Check if semester exists
        $semester = $pdo->query("SELECT * FROM semesters WHERE semester_id = $semesterId")->fetch();
        
        if (!$semester) {
            echo generateResponse(false, 'Semester not found');
            exit();
        }
        
        // Delete semester record
        $stmt = $pdo->prepare("DELETE FROM semesters WHERE semester_id = ?");
        $success = $stmt->execute([$semesterId]);
        
        echo generateResponse($success, $success ? 'Semester deleted successfully' : 'Error deleting semester');
        break;
        
    case 'get_semester':
        $semesterId = intval($_POST['semester_id'] ?? 0);
        
        if (!$semesterId) {
            echo generateResponse(false, 'Invalid parameters');
            exit();
        }
        
        $semester = $pdo->query("SELECT * FROM semesters WHERE semester_id = $semesterId")->fetch();
        
        echo generateResponse($semester ? true : false, 
            $semester ? 'Semester fetched successfully' : 'Semester not found', 
            $semester);
        break;
        
    case 'get_all_semesters':
        $search = trim($_POST['search'] ?? '');
        $page = intval($_POST['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT * FROM semesters 
            WHERE 1=1
        ";
        
        if ($search) {
            $query .= " AND (Semester_name LIKE :search OR Status LIKE :search)";
        }
        
        $query .= " ORDER BY Start_date DESC LIMIT :offset, :limit";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $semesters = $stmt->fetchAll();
        
        // Get total count for pagination
        $totalQuery = "
            SELECT COUNT(*) as total 
            FROM semesters 
            WHERE 1=1
        ";
        
        if ($search) {
            $totalQuery .= " AND (Semester_name LIKE :search OR Status LIKE :search)";
        }
        
        $totalQuery .= " ORDER BY Start_date DESC";
        
        $totalStmt = $pdo->prepare($totalQuery);
        $totalStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $totalStmt->execute();
        
        $totalSemesters = $totalStmt->fetch()['total'];
        
        // Get total pages for pagination
        $totalPages = ceil($totalSemesters / $limit);
        
        // Format the response data
        $responseData = [
            'success' => $semesters ? true : false,
            'message' => $semesters ? 'Semesters fetched successfully' : 'No semesters found',
            'data' => $semesters,
            'pagination' => [
                'total_items' => $totalSemesters,
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
