<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

requireRole('admin');

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $faculty_name = sanitizeInput($_POST['faculty_name']);
                    
                    // Check if faculty already exists
                    $existing = getTableData('faculty', '*', "faculty_name = '$faculty_name'");
                    if (!empty($existing)) {
                        throw new Exception('Faculty already exists');
                    }
                    
                    $data = [
                        'faculty_name' => $faculty_name
                    ];
                    
                    if (insertData('faculty', $data)) {
                        echo generateResponse(true, 'Faculty added successfully');
                    } else {
                        throw new Exception('Database error: Failed to add faculty');
                    }
                } catch (Exception $e) {
                    error_log("Add faculty error: " . $e->getMessage());
                    echo generateResponse(false, $e->getMessage());
                }
                exit();
            
            case 'edit':
                $data = [
                    'faculty_name' => sanitizeInput($_POST['faculty_name'])
                ];
                $where = "faculty_id = " . intval($_POST['faculty_id']);
                if (updateData('faculty', $data, $where)) {
                    echo generateResponse(true, 'Faculty updated successfully');
                } else {
                    echo generateResponse(false, 'Error updating Faculty');
                }
                exit();
            
            case 'delete':
                try {
                    $faculty_id = intval($_POST['faculty_id']);
                    if ($faculty_id <= 0) {
                        throw new Exception('Invalid faculty ID');
                    }
                    
                    // First check if faculty exists
                    $faculty = getTableData('faculty', '*', "faculty_id = $faculty_id");
                    if (empty($faculty)) {
                        throw new Exception('Faculty not found');
                    }
                    
                    // Check if faculty is being used in programs
                    $programs = getTableData('programs', '*', "faculty_id = $faculty_id");
                    if (!empty($programs)) {
                        throw new Exception('Cannot delete faculty because it is being used in programs');
                    }
                    
                    // Delete faculty
                    $where = "faculty_id = :faculty_id";
                    $params = [':faculty_id' => $faculty_id];
                    
                    $result = deleteData('faculty', $where, $params);
                    if ($result) {
                        // Return success response
                        echo generateResponse(true, 'Faculty deleted successfully');
                    } else {
                        throw new Exception('Database error: Failed to delete faculty');
                    }
                } catch (Exception $e) {
                    error_log("Delete faculty error: " . $e->getMessage());
                    echo generateResponse(false, $e->getMessage());
                }
                exit();
        }
    }
}

// Get all faculty 
$faculty = getTableData('faculty');
            
// Search functionality
$search = $_GET['search'] ?? '';
if ($search) {
    $faculty = getTableData('faculty', '*', 
        "faculty_name LIKE '%$search%'", 
        'faculty_name ASC');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalFaculty = count($faculty);
$totalPages = ceil($totalFaculty / $limit);

$faculty = getTableData('faculty', '*', null, 'faculty_name ASC LIMIT ' . $offset . ', ' . $limit);

?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Faculty </h2>
            <hr>
        </div>
    </div>

    <!-- Search form -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form id="searchForm" class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Search faculty..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- Add faculty button -->
    <div class="row mb-4">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                Add New Faculty
            </button>
        </div>
    </div>

    <!-- Faculty table -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Faculty Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faculty as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['faculty_name'] ?? ''); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-faculty" 
                                        data-id="<?php echo $member['faculty_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($member['faculty_name'] ?? ''); ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editFacultyModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-faculty" 
                                         data-id="<?php echo $member['faculty_id']; ?>"
                                         data-name="<?php echo htmlspecialchars($member['faculty_name'] ?? ''); ?>">
                                         <i class="fas fa-trash"></i>
                                     </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-md-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" data-page="<?php echo $page - 1; ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" data-page="<?php echo $page + 1; ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Faculty Modal -->
<div class="modal fade" id="addFacultyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Add New Faculty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="faculty_name" class="form-label">Faculty Name</label>
                        <input type="text" class="form-control" id="faculty_name" name="faculty_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Faculty</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Faculty Modal -->
<div class="modal fade" id="editFacultyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Faculty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="faculty_id" id="faculty_id">
                    <div class="mb-3">
                        <label for="faculty_name" class="form-label">Faculty Name</label>
                        <input type="text" class="form-control" id="faculty_name" name="faculty_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Faculty</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete faculty
    document.querySelectorAll('.delete-faculty').forEach(button => {
        button.addEventListener('click', function() {
            const facultyId = this.dataset.id;
            const facultyName = this.dataset.name;
            
            if (confirm(`Are you sure you want to delete faculty: ${facultyName}?`)) {
                // Send delete request
                fetch('faculty.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&faculty_id=${facultyId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message before reload
                        alert('Faculty deleted successfully');
                        // Reload the page
                        location.reload();
                    } else {
                        alert('Error deleting faculty: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error occurred while deleting faculty');
                });
            }
        });
    });
});
</script>