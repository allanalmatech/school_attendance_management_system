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
            'semester_name' => trim($_POST['semester_name'] ?? ''),
            'academic_year' => trim($_POST['academic_year'] ?? ''),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? '')
        ];
        
        // Validate required fields
        if (empty($data['semester_name']) || empty($data['start_date']) || empty($data['end_date'])) {
            echo generateResponse(false, 'Semester name, start date, and end date are required');
            exit();
        }
        
        // Validate date format
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['start_date']) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['end_date'])) {
            echo generateResponse(false, 'Invalid date format. Please use YYYY-MM-DD');
            exit();
        }
        
        // Validate if start date is before end date
        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            echo generateResponse(false, 'Start date must be before end date');
            exit();
        }
        
        // Check if semester name already exists
        $existing = $pdo->query("SELECT COUNT(*) as count FROM semesters WHERE semester_name = '" . $pdo->quote($data['semester_name']) . "'")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Semester name already exists');
            exit();
        }
        
        // Insert semester record
        $stmt = $pdo->prepare("INSERT INTO semesters (semester_name, academic_year, start_date, end_date) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([
            $data['semester_name'],
            $data['academic_year'],
            $data['start_date'],
            $data['end_date']
        ]);
        
        echo generateResponse($success, $success ? 'Semester added successfully' : 'Error adding semester');
        break;
        
    case 'edit_semester':
        $semesterId = intval($_POST['semester_id'] ?? 0);
        $data = [
            'semester_name' => trim($_POST['semester_name'] ?? ''),
            'academic_year' => trim($_POST['academic_year'] ?? ''),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? '')
        ];
        
        // Validate required fields
        if (empty($data['semester_name']) || empty($data['start_date']) || empty($data['end_date'])) {
            echo generateResponse(false, 'Semester name, start date, and end date are required');
            exit();
        }
        
        // Validate date format
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['start_date']) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['end_date'])) {
            echo generateResponse(false, 'Invalid date format. Please use YYYY-MM-DD');
            exit();
        }
        
        // Validate if start date is before end date
        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
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
            WHERE semester_name = '" . $pdo->quote($data['semester_name']) . "'
            AND semester_id != $semesterId
        ")->fetch()['count'];
        
        if ($existing > 0) {
            echo generateResponse(false, 'Semester name already exists');
            exit();
        }
        
        // Update semester record
        $stmt = $pdo->prepare("UPDATE semesters SET semester_name = ?, academic_year = ?, start_date = ?, end_date = ? WHERE semester_id = ?");
        $success = $stmt->execute([
            $data['semester_name'],
            $data['academic_year'],
            $data['start_date'],
            $data['end_date'],
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
            $query .= " AND (semester_name LIKE :search OR status LIKE :search)";
        }
        
        $query .= " ORDER BY start_date DESC LIMIT :offset, :limit";
        
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
            $totalQuery .= " AND (semester_name LIKE :search OR status LIKE :search)";
        }
        
        $totalQuery .= " ORDER BY start_date DESC";
        
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
<div id="toastMessage" class="alert d-none position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index:9999"></div>
<script>
function showToast(message, type = 'success') {
    const toast = document.getElementById('toastMessage');
    toast.textContent = message;
    toast.classList.remove('d-none', 'alert-success', 'alert-danger');
    toast.classList.add(type === 'error' ? 'alert-danger' : 'alert-success');
    setTimeout(() => toast.classList.add('d-none'), 5000);
}

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('api/semesters.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                setTimeout(() => {
                    bootstrap.Modal.getInstance(this.closest('.modal')).hide();
                    location.reload();
                }, 1500);
            }
        });
    });
});

document.querySelectorAll('.delete-semester').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const semesterId = this.dataset.id;
        if (confirm('Are you sure you want to delete this semester?')) {
            fetch('api/semesters.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'delete_semester',
                    semester_id: semesterId
                })
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            });
        }
    });
});
</script>

<!-- Add Semester Modal -->
<div id="addSemesterMessageBox" class="alert d-none mx-3 mt-3" role="alert"></div>
<input type="hidden" name="semester_id" id="semester_id">
<input type="text" name="semester_name" id="edit_semester_name" required>
<input type="text" name="academic_year" id="edit_academic_year" required>
<input type="date" name="start_date" id="edit_start_date" required>
<input type="date" name="end_date" id="edit_end_date" required>

<!-- Edit Semester Modal -->
<div id="editSemesterMessageBox" class="alert d-none mx-3 mt-3" role="alert"></div>
<input type="hidden" name="semester_id" id="semester_id">
<input type="text" name="semester_name" id="edit_semester_name" required>
<input type="text" name="academic_year" id="edit_academic_year" required>
<input type="date" name="start_date" id="edit_start_date" required>
<input type="date" name="end_date" id="edit_end_date" required>
