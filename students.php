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
                    'Student_name' => sanitizeInput($_POST['Student_name']),
                    'Reg_number' => sanitizeInput($_POST['Reg_number']),
                    'Year_joined' => intval($_POST['Year_joined'])
                ];
                if (insertData('student', $data)) {
                    echo generateResponse(true, 'Student added successfully');
                } else {
                    echo generateResponse(false, 'Error adding student');
                }
                exit();
            
            case 'edit':
                $data = [
                    'Student_name' => sanitizeInput($_POST['Student_name']),
                    'Reg_number' => sanitizeInput($_POST['Reg_number']),
                    'Year_joined' => intval($_POST['Year_joined'])
                ];
                $where = "ID = " . intval($_POST['ID']);
                if (updateData('student', $data, $where)) {
                    echo generateResponse(true, 'Student updated successfully');
                } else {
                    echo generateResponse(false, 'Error updating student');
                }
                exit();
            
            case 'delete':
                $where = "ID = " . intval($_POST['ID']);
                if (deleteData('student', $where)) {
                    echo generateResponse(true, 'Student deleted successfully');
                } else {
                    echo generateResponse(false, 'Error deleting student');
                }
                exit();
        }
    }
}

// Get all students
$students = getTableData('student');

// Search functionality
$search = $_GET['search'] ?? '';
if ($search) {
    $students = getTableData('student', '*', 
        "Student_name LIKE '%$search%' OR Reg_number LIKE '%$search%'", 
        'Student_name ASC');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalStudents = count(getTableData('student'));
$totalPages = ceil($totalStudents / $limit);

$students = getTableData('student', '*', null, 'Student_name ASC LIMIT ' . $offset . ', ' . $limit);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Students</h2>
            <hr>
        </div>
    </div>

    <!-- Search form -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form id="searchForm" class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Search students..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- Add student button -->
    <div class="row mb-4">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                Add New Student
            </button>
        </div>
    </div>

    <!-- Students table -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Registration Number</th>
                            <th>Year Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $student['ID']; ?></td>
                                <td><?php echo htmlspecialchars($student['Student_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['Reg_number']); ?></td>
                                <td><?php echo $student['Year_joined']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-student" 
                                        data-id="<?php echo $student['ID']; ?>"
                                        data-name="<?php echo htmlspecialchars($student['Student_name']); ?>"
                                        data-reg-number="<?php echo htmlspecialchars($student['Reg_number']); ?>"
                                        data-year-joined="<?php echo $student['Year_joined']; ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editStudentModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-student" 
                                        data-id="<?php echo $student['ID']; ?>">
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

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="Student_name" class="form-label">Student Name</label>
                        <input type="text" class="form-control" id="Student_name" name="Student_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="Reg_number" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="Reg_number" name="Reg_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="Year_joined" class="form-label">Year Joined</label>
                        <input type="number" class="form-control" id="Year_joined" name="Year_joined" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="ID" id="editStudentId">
                    <div class="mb-3">
                        <label for="editStudentName" class="form-label">Student Name</label>
                        <input type="text" class="form-control" id="editStudentName" name="Student_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editRegNumber" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="editRegNumber" name="Reg_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="editYearJoined" class="form-label">Year Joined</label>
                        <input type="number" class="form-control" id="editYearJoined" name="Year_joined" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Student</button>
                </div>
            </form>
        </div>
    </div>
</div>