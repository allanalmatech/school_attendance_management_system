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
                    'program_code' => sanitizeInput($_POST['program_code']),
                    'program_name' => sanitizeInput($_POST['program_name']),
                    'description' => sanitizeInput($_POST['description']),
                    'faculty_id' => intval($_POST['faculty_id'])
                ];
                if (insertData('programs', $data)) {
                    echo generateResponse(true, 'Program added successfully');
                } else {
                    echo generateResponse(false, 'Error adding program');
                }
                exit();
            
            case 'edit':
                $data = [
                    'program_code' => sanitizeInput($_POST['program_code']),
                    'program_name' => sanitizeInput($_POST['program_name']),
                    'description' => sanitizeInput($_POST['description']),
                    'faculty_id' => intval($_POST['faculty_id'])
                ];
                $where = "program_id = " . intval($_POST['program_id']);
                if (updateData('programs', $data, $where)) {
                    echo generateResponse(true, 'Program updated successfully');
                } else {
                    echo generateResponse(false, 'Error updating program');
                }
                exit();
            
            case 'delete':
                $where = "program_id = " . intval($_POST['program_id']);
                if (deleteData('programs', $where)) {
                    echo generateResponse(true, 'Program deleted successfully');
                } else {
                    echo generateResponse(false, 'Error deleting program');
                }
                exit();
        }
    }
}

// Get all programs
$programs = getTableData('programs');

// Search functionality
$search = $_GET['search'] ?? '';
if ($search) {
    $programs = getTableData('programs', '*', 
        "program_name LIKE '%$search%' OR description LIKE '%$search%'", 
        'program_name ASC');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalPrograms = count(getTableData('programs'));
$totalPages = ceil($totalPrograms / $limit);

$programs = getTableData('programs', '*', null, 'program_name ASC LIMIT ' . $offset . ', ' . $limit);
$faculties = getTableData('faculty');
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Academic Programs</h2>
            <hr>
        </div>
    </div>

    <!-- Search form -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form id="searchForm" class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Search programs..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- Add program button -->
    <div class="row mb-4">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                Add New Program
            </button>
        </div>
    </div>

    <!-- Programs table -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Program Code</th>
                            <th>Program Name</th>
                            <th>Description</th>
                            <th>Faculty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $program): ?>
                            <tr>
                                <td><?php echo $program['program_id']; ?></td>
                                <td><?php echo htmlspecialchars($program['program_code'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($program['program_name']); ?></td>
                                <td><?php echo htmlspecialchars($program['description']); ?></td>
                                <td>
                                    <?php
                                    $facultyName = '';
                                    foreach ($faculties as $faculty) {
                                        if ($faculty['faculty_id'] == $program['faculty_id']) {
                                            $facultyName = $faculty['faculty_name'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($facultyName);
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-program" 
                                        data-id="<?php echo $program['program_id']; ?>"
                                        data-code="<?php echo htmlspecialchars($program['program_code'] ?? ''); ?>"
                                        data-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                        data-description="<?php echo htmlspecialchars($program['description']); ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editProgramModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-program" 
                                        data-id="<?php echo $program['program_id']; ?>">
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

<!-- Add Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" data-ajax="true" id="programForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="program_code" class="form-label">Program Code</label>
                        <input type="text" class="form-control" id="program_code" name="program_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="program_name" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="program_name" name="program_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="faculty_id" class="form-label">Faculty</label>
                        <select class="form-control" id="faculty_id" name="faculty_id" required>
                            <option value="">Select Faculty</option>
                            <?php foreach ($faculties as $faculty): ?>
                                <option value="<?php echo $faculty['faculty_id']; ?>">
                                    <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitProgram">Add Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Program Modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modal
    const addModal = new bootstrap.Modal(document.getElementById('addProgramModal'));
    const editModal = new bootstrap.Modal(document.getElementById('editProgramModal'));

    // Handle form submissions
    document.getElementById('programForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const action = formData.get('action');
        
        // Ensure program_code is included
        if (!formData.has('program_code')) {
            formData.append('program_code', '');
        }

        // Show loading overlay
        showLoading();
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message);
                // Optionally reload the page or update the table
                setTimeout(() => { location.reload(); }, 1500); // Reload after 1.5s
                // Close the appropriate modal
                const currentModal = bootstrap.Modal.getInstance(document.querySelector('.modal.show'));
                if (currentModal) {
                    currentModal.hide();
                }
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('An error occurred while ' + (action === 'add' ? 'adding' : 'updating') + ' the program.', 'error');
        })
        .finally(() => {
            // Hide loading overlay
            hideLoading();
        });
    });
    // Handle form submissions
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const action = formData.get('action');

            // Add program_code field if it's missing
            if (!formData.has('program_code')) {
                formData.append('program_code', '');
            }

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message); // Show success message
                    // Optionally, update the table here via JS if you want
                    const modal = bootstrap.Modal.getInstance(this.closest('.modal'));
                    if (modal) {
                        modal.hide();
                    }
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('An error occurred while ' + (action === 'add' ? 'adding' : 'updating') + ' the program.', 'error');
            });
    });

    // Handle edit button click
    document.querySelectorAll('.edit-program').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('program_id').value = this.dataset.id;
            document.getElementById('edit_program_code').value = this.dataset.code;
            document.getElementById('edit_program_name').value = this.dataset.name;
            document.getElementById('edit_description').value = this.dataset.description;
            document.getElementById('edit_faculty_id').value = this.dataset.facultyId;
        });
    });

    // Handle delete button click
    document.querySelectorAll('.delete-program').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const programId = this.dataset.id;

            if (confirm('Are you sure you want to delete this program?')) {
                fetch(window.location.href, {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'delete',
                        program_id: programId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message);
                        // Optionally, remove the row from the table here
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('An error occurred while deleting the program.', 'error');
                });
            }
        });
    });
});

// Message Display function
function showMessage(message, type = 'success') {
    const box = document.getElementById('messageBox');
    box.textContent = message;
    box.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
    if (type === 'error') {
        box.classList.add('alert-danger');
    } else if (type === 'info') {
        box.classList.add('alert-info');
    } else {
        box.classList.add('alert-success');
    }
    // Hide after 5 seconds
    setTimeout(() => {
        box.classList.add('d-none');
    }, 5000);
}
</script>
<div class="modal fade" id="editProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" data-ajax="true" id="programForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="program_id" id="program_id">
                    <div class="mb-3">
                        <label for="edit_program_code" class="form-label">Program Code</label>
                        <input type="text" class="form-control" id="edit_program_code" name="program_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_program_name" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="edit_program_name" name="program_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitProgram">Update Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Message Display -->
<div id="messageBox" class="alert d-none" role="alert"></div>
<!-- End Message Display -->

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="toastMessage" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<!-- End Toast Notification -->

<?php require_once 'includes/footer.php'; ?>