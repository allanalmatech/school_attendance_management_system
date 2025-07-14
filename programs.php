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
                    'Programme_name' => sanitizeInput($_POST['Programme_name']),
                    'duration_years' => intval($_POST['duration_years']),
                    'description' => sanitizeInput($_POST['description'])
                ];
                if (insertData('programs', $data)) {
                    echo generateResponse(true, 'Program added successfully');
                } else {
                    echo generateResponse(false, 'Error adding program');
                }
                exit();
            
            case 'edit':
                $data = [
                    'Programme_name' => sanitizeInput($_POST['Programme_name']),
                    'duration_years' => intval($_POST['duration_years']),
                    'description' => sanitizeInput($_POST['description'])
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
        "Programme_name LIKE '%$search%' OR description LIKE '%$search%'", 
        'Programme_name ASC');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalPrograms = count(getTableData('programs'));
$totalPages = ceil($totalPrograms / $limit);

$programs = getTableData('programs', '*', null, 'Programme_name ASC LIMIT ' . $offset . ', ' . $limit);
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
                            <th>Program Name</th>
                            <th>Duration (Years)</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $program): ?>
                            <tr>
                                <td><?php echo $program['program_id']; ?></td>
                                <td><?php echo htmlspecialchars($program['Programme_name']); ?></td>
                                <td><?php echo $program['duration_years']; ?></td>
                                <td><?php echo htmlspecialchars($program['description']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-program" 
                                        data-id="<?php echo $program['program_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($program['Programme_name']); ?>"
                                        data-duration="<?php echo $program['duration_years']; ?>"
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
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Add New Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="Programme_name" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="Programme_name" name="Programme_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="duration_years" class="form-label">Duration (Years)</label>
                        <input type="number" class="form-control" id="duration_years" name="duration_years" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Program Modal -->
<div class="modal fade" id="editProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="program_id" id="program_id">
                    <div class="mb-3">
                        <label for="Programme_name" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="Programme_name" name="Programme_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="duration_years" class="form-label">Duration (Years)</label>
                        <input type="number" class="form-control" id="duration_years" name="duration_years" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Program</button>
                </div>
            </form>
        </div>
    </div>
</div>