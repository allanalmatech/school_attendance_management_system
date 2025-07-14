<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

requireRole('admin');

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    'semester_name' => sanitizeInput($_POST['semester_name']),
                    'start_date' => sanitizeInput($_POST['start_date']),
                    'end_date' => sanitizeInput($_POST['end_date']),
                    'status' => intval($_POST['status'])
                ];
                if (insertData('semester', $data)) {
                    echo generateResponse(true, 'Semester added successfully');
                } else {
                    echo generateResponse(false, 'Error adding semester');
                }
                exit();
            
            case 'edit':
                $data = [
                    'semester_name' => sanitizeInput($_POST['semester_name']),
                    'start_date' => sanitizeInput($_POST['start_date']),
                    'end_date' => sanitizeInput($_POST['end_date']),
                    'status' => intval($_POST['status'])
                ];
                $where = "semester_id = " . intval($_POST['semester_id']);
                if (updateData('semester', $data, $where)) {
                    echo generateResponse(true, 'Semester updated successfully');
                } else {
                    echo generateResponse(false, 'Error updating semester');
                }
                exit();
            
            case 'delete':
                $where = "semester_id = " . intval($_POST['semester_id']);
                if (deleteData('semester', $where)) {
                    echo generateResponse(true, 'Semester deleted successfully');
                } else {
                    echo generateResponse(false, 'Error deleting semester');
                }
                exit();
        }
    }
}

// Get all semesters
$semesters = getTableData('semester');

// Search functionality
$search = $_GET['search'] ?? '';
if ($search) {
    $semesters = getTableData('semester', '*', 
        "semester_name LIKE '%$search%' OR status LIKE '%$search%'", 
        'semester_name ASC');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalSemesters = count(getTableData('semester'));
$totalPages = ceil($totalSemesters / $limit);

$semesters = getTableData('semester', '*', null, 'semester_name ASC LIMIT ' . $offset . ', ' . $limit);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Semesters</h2>
            <hr>
        </div>
    </div>

    <!-- Search form -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form id="searchForm" class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Search semesters..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- Add semester button -->
    <div class="row mb-4">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSemesterModal">
                Add New Semester
            </button>
        </div>
    </div>

    <!-- Semesters table -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Semester Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($semesters as $semester): ?>
                            <tr>
                                <td><?php echo $semester['semester_id']; ?></td>
                                <td><?php echo htmlspecialchars($semester['semester_name']); ?></td>
                                <td><?php echo htmlspecialchars($semester['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($semester['end_date']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $semester['status'] == 1 ? 'success' : 'warning'; ?>">
                                        <?php echo $semester['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-semester" 
                                        data-id="<?php echo $semester['semester_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($semester['semester_name']); ?>"
                                        data-start="<?php echo htmlspecialchars($semester['start_date']); ?>"
                                        data-end="<?php echo htmlspecialchars($semester['end_date']); ?>"
                                        data-status="<?php echo $semester['status']; ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editSemesterModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-semester" 
                                        data-id="<?php echo $semester['semester_id']; ?>">
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

<!-- Add Semester Modal -->
<div class="modal fade" id="addSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Add New Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="semester_name" class="form-label">Semester Name</label>
                        <input type="text" class="form-control" id="semester_name" name="semester_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Semester</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Semester Modal -->
<div class="modal fade" id="editSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="semester_id" id="semester_id">
                    <div class="mb-3">
                        <label for="editSemesterName" class="form-label">Semester Name</label>
                        <input type="text" class="form-control" id="editSemesterName" name="semester_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="editStartDate" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEndDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="editEndDate" name="end_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-control" id="editStatus" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Semester</button>
                </div>
            </form>
        </div>
    </div>
</div>