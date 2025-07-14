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
                    'Faculty_name' => sanitizeInput($_POST['Faculty_name']),
                    'email' => sanitizeInput($_POST['email']),
                    'phone' => sanitizeInput($_POST['phone']),
                    'department' => sanitizeInput($_POST['department'])
                ];
                if (insertData('faculty', $data)) {
                    echo generateResponse(true, 'Faculty member added successfully');
                } else {
                    echo generateResponse(false, 'Error adding faculty member');
                }
                exit();
            
            case 'edit':
                $data = [
                    'Faculty_name' => sanitizeInput($_POST['Faculty_name']),
                    'email' => sanitizeInput($_POST['email']),
                    'phone' => sanitizeInput($_POST['phone']),
                    'department' => sanitizeInput($_POST['department'])
                ];
                $where = "faculty_id = " . intval($_POST['faculty_id']);
                if (updateData('faculty', $data, $where)) {
                    echo generateResponse(true, 'Faculty member updated successfully');
                } else {
                    echo generateResponse(false, 'Error updating faculty member');
                }
                exit();
            
            case 'delete':
                $where = "faculty_id = " . intval($_POST['faculty_id']);
                if (deleteData('faculty', $where)) {
                    echo generateResponse(true, 'Faculty member deleted successfully');
                } else {
                    echo generateResponse(false, 'Error deleting faculty member');
                }
                exit();
        }
    }
}

// Get all faculty members
$faculty = getTableData('faculty');

// Search functionality
$search = $_GET['search'] ?? '';
if ($search) {
    $faculty = getTableData('faculty', '*', 
        "first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR department LIKE '%$search%'", 
        'first_name ASC, last_name ASC');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalFaculty = count(getTableData('faculty'));
$totalPages = ceil($totalFaculty / $limit);

$faculty = getTableData('faculty', '*', null, 'first_name ASC, last_name ASC LIMIT ' . $offset . ', ' . $limit);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Faculty Members</h2>
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
                Add New Faculty Member
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
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faculty as $member): ?>
                            <tr>
                                <td><?php echo $member['faculty_id']; ?></td>
                                <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                <td><?php echo htmlspecialchars($member['department']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-faculty" 
                                        data-id="<?php echo $member['faculty_id']; ?>"
                                        data-first-name="<?php echo htmlspecialchars($member['first_name']); ?>"
                        data-last-name="<?php echo htmlspecialchars($member['last_name']); ?>"
                                        data-email="<?php echo htmlspecialchars($member['email']); ?>"
                                        data-phone="<?php echo htmlspecialchars($member['phone']); ?>"
                                        data-department="<?php echo htmlspecialchars($member['department']); ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editFacultyModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-faculty" 
                                        data-id="<?php echo $member['faculty_id']; ?>">
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
                    <h5 class="modal-title">Add New Faculty Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control" id="department" name="department" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Faculty Member</button>
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
                    <h5 class="modal-title">Edit Faculty Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="faculty_id" id="faculty_id">
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_department" class="form-label">Department</label>
                        <input type="text" class="form-control" id="edit_department" name="department" required>
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