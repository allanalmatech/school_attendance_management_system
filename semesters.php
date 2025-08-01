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
                    'semester_name' => sanitizeInput($_POST['semester_name'] ?? ''),
                    'academic_year' => sanitizeInput($_POST['academic_year'] ?? ''),
                    'start_date' => sanitizeInput($_POST['start_date'] ?? ''),
                    'end_date' => sanitizeInput($_POST['end_date'] ?? '')
                ];
                if (insertData('semesters', $data)) {
                    echo generateResponse(true, 'Semester added successfully');
                } else {
                    echo generateResponse(false, 'Error adding semester');
                }
                exit();
            
            case 'edit':
                $data = [
                    'semester_name' => sanitizeInput($_POST['semester_name'] ?? ''),
                    'academic_year' => sanitizeInput($_POST['academic_year'] ?? ''),
                    'start_date' => sanitizeInput($_POST['start_date'] ?? ''),
                    'end_date' => sanitizeInput($_POST['end_date'] ?? '')
                ];
                $where = "semester_id = " . intval($_POST['semester_id']);
                if (updateData('semesters', $data, $where)) {
                    echo generateResponse(true, 'Semester updated successfully');
                } else {
                    echo generateResponse(false, 'Error updating semester');
                }
                exit();
            
            case 'delete':
                $where = "semester_id = " . intval($_POST['semester_id']);
                if (deleteData('semesters', $where)) {
                    echo generateResponse(true, 'Semester deleted successfully');
                } else {
                    echo generateResponse(false, 'Error deleting semester');
                }
                exit();
        }
    }
}

// Get all semesters
$semesters = getTableData('semesters');

// Search functionality
$search = $_GET['search'] ?? '';
if ($search) {
    $semesters = getTableData('semesters', '*', 
        "semester_name LIKE '%$search%' OR academic_year LIKE '%$search%'", 
        'semester_name ASC');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalSemesters = count(getTableData('semesters'));
$totalPages = ceil($totalSemesters / $limit);

$semesters = getTableData('semesters', '*', null, 'semester_name ASC LIMIT ' . $offset . ', ' . $limit);
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
                            <th>Academic Year</th> <!-- Add this -->
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
                                <td><?php echo htmlspecialchars($semester['academic_year']); ?></td> <!-- Add this -->
                                <td><?php echo htmlspecialchars($semester['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($semester['end_date']); ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-semester" 
                                        data-id="<?php echo $semester['semester_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($semester['semester_name']); ?>"
                                        data-start="<?php echo htmlspecialchars($semester['start_date']); ?>"
                                        data-end="<?php echo htmlspecialchars($semester['end_date']); ?>"
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit button click
    document.querySelectorAll('.edit-semester').forEach(button => {
        button.addEventListener('click', function() {
            const semesterId = this.dataset.id;
            const semesterName = this.dataset.name;
            const startDate = this.dataset.start;
            const endDate = this.dataset.end;
            
            document.getElementById('semester_id').value = semesterId;
            document.getElementById('edit_semester_name').value = semesterName;
            document.getElementById('edit_start_date').value = startDate;
            document.getElementById('edit_end_date').value = endDate;
        });
    });

    // Handle delete button click
    document.querySelectorAll('.delete-semester').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const semesterId = this.dataset.id;
            
            if (confirm('Are you sure you want to delete this semester?')) {
                fetch(window.location.href, {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'delete',
                        semester_id: semesterId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Semester deleted successfully!');
                        updateTable();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred while deleting the semester.', 'error');
                });
            }
        });
    });

    // Add Semester
    document.getElementById('addSemesterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.set('action', 'add');
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showFormMessage(data.message, data.success ? 'success' : 'error', 'addSemesterMessageBox');
        if (data.success) {
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('addSemesterModal')).hide();
            }, 1500);
            setTimeout(() => location.reload(), 5000);
        }
    });
});

    // Edit Semester
    document.getElementById('editSemesterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.set('action', 'edit');
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showFormMessage(data.message, data.success ? 'success' : 'error', 'editSemesterMessageBox');
        if (data.success) {
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('editSemesterModal')).hide();
            }, 1500);
            setTimeout(() => location.reload(), 5000);
        }
    });
});


    // Show message in modal
    function showFormMessage(message, type = 'success', boxId = 'addSemesterMessageBox') {
        const box = document.getElementById(boxId);
        box.textContent = message;
        box.classList.remove('d-none', 'alert-success', 'alert-danger');
        box.classList.add(type === 'error' ? 'alert-danger' : 'alert-success');
        setTimeout(() => box.classList.add('d-none'), 5000);
    }
});
</script>
<div class="modal fade" id="addSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addSemesterForm" method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Add New Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- 🟢 Message box here inside modal-body -->
                    <div id="addSemesterMessageBox" class="alert d-none" role="alert"></div>

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
                        <label for="acade                        const form = document.querySelector('form[data-ajax]');
                        if (form) {
                            form.addEventListener('submit', function(e) {
                                // ... generic AJAX code ...
                            });
                        }mic_year" class="form-label">Academic Year</label>
                        <input type="text" class="form-control" id="academic_year" name="academic_year" required>
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
            <form id="editSemesterForm" method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- 🟢 Message box here inside modal-body -->
                    <div id="editSemesterMessageBox" class="alert d-none" role="alert"></div>

                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="semester_id" id="semester_id">

                    <div class="mb-3">
                        <label for="edit_semester_name" class="form-label">Semester Name</label>
                        <input type="text" class="form-control" id="edit_semester_name" name="semester_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_academic_year" class="form-label">Academic Year</label>
                        <input type="text" class="form-control" id="edit_academic_year" name="academic_year" required>
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

<?php require_once 'includes/footer.php'; ?>