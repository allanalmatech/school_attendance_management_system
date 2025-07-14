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
                    'registration_number' => sanitizeInput($_POST['registration_number']),
                    'first_name' => sanitizeInput($_POST['first_name']),
                    'last_name' => sanitizeInput($_POST['last_name']),
                    'date_of_birth' => sanitizeInput($_POST['date_of_birth']),
                    'gender' => sanitizeInput($_POST['gender']),
                    'phone' => sanitizeInput($_POST['phone']),
                    'email' => sanitizeInput($_POST['email']),
                    'address' => sanitizeInput($_POST['address']),
                    'program_id' => intval($_POST['program_id']),
                    'semester_id' => intval($_POST['semester_id'])
                ];
                if (insertData('students', $data)) {
                    echo generateResponse(true, 'Student added successfully');
                } else {
                    echo generateResponse(false, 'Error adding student');
                }
                exit();
            
            case 'edit':
                $data = [
                    'registration_number' => sanitizeInput($_POST['registration_number']),
                    'first_name' => sanitizeInput($_POST['first_name']),
                    'last_name' => sanitizeInput($_POST['last_name']),
                    'date_of_birth' => sanitizeInput($_POST['date_of_birth']),
                    'gender' => sanitizeInput($_POST['gender']),
                    'phone' => sanitizeInput($_POST['phone']),
                    'email' => sanitizeInput($_POST['email']),
                    'address' => sanitizeInput($_POST['address']),
                    'program_id' => intval($_POST['program_id']),
                    'semester_id' => intval($_POST['semester_id'])
                ];
                $where = "student_id = " . intval($_POST['student_id']);
                if (updateData('students', $data, $where)) {
                    echo generateResponse(true, 'Student updated successfully');
                } else {
                    echo generateResponse(false, 'Error updating student');
                }
                exit();
            
            case 'delete':
                $where = "student_id = " . intval($_POST['student_id']);
                if (deleteData('students', $where)) {
                    echo generateResponse(true, 'Student deleted successfully');
                } else {
                    echo generateResponse(false, 'Error deleting student');
                }
                exit();
        }
    }
}

// Get all students
$students = getTableData('students');

// Search functionality
$search = $_GET['search'] ?? '';
if ($search) {
    $students = getTableData('students', '*', 
        "first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR registration_number LIKE '%$search%'", 
        'first_name ASC, last_name ASC');
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalStudents = count(getTableData('students'));
$totalPages = ceil($totalStudents / $limit);

$students = getTableData('students', '*', null, 'first_name ASC, last_name ASC LIMIT ' . $offset . ', ' . $limit);
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
                            <th>Registration Number</th>
                            <th>Full Name</th>
                            <th>Date of Birth</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Semester</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $student['student_id']; ?></td>
                                <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                                <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['program_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['semester_name']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-student" 
                                        data-id="<?php echo $student['student_id']; ?>"
                                        data-reg="<?php echo htmlspecialchars($student['registration_number']); ?>"
                                        data-first="<?php echo htmlspecialchars($student['first_name']); ?>"
                                        data-last="<?php echo htmlspecialchars($student['last_name']); ?>"
                                        data-dob="<?php echo htmlspecialchars($student['date_of_birth']); ?>"
                                        data-gender="<?php echo htmlspecialchars($student['gender']); ?>"
                                        data-phone="<?php echo htmlspecialchars($student['phone']); ?>"
                                        data-email="<?php echo htmlspecialchars($student['email']); ?>"
                                        data-address="<?php echo htmlspecialchars($student['address']); ?>"
                                        data-program="<?php echo htmlspecialchars($student['program_name']); ?>"
                                        data-semester="<?php echo htmlspecialchars($student['semester_name']); ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editStudentModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-student" 
                                        data-id="<?php echo $student['student_id']; ?>">
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
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="registration_number" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-control" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="program_id" class="form-label">Program</label>
                        <select class="form-control" id="program_id" name="program_id" required>
                            <?php
                            $programs = getTableData('programs');
                            foreach ($programs as $program) {
                                echo '<option value="' . $program['program_id'] . '">' . $program['program_name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="semester_id" class="form-label">Semester</label>
                        <select class="form-control" id="semester_id" name="semester_id" required>
                            <?php
                            $semesters = getTableData('semesters');
                            foreach ($semesters as $semester) {
                                echo '<option value="' . $semester['semester_id'] . '">' . $semester['semester_name'] . '</option>';
                            }
                            ?>
                        </select>
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
                    <input type="hidden" name="student_id" id="student_id">
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_gender" class="form-label">Gender</label>
                        <select class="form-control" id="edit_gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_program_id" class="form-label">Program</label>
                        <select class="form-control" id="edit_program_id" name="program_id" required>
                            <option value="">Select Program</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo $program['program_id']; ?>">
                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_semester_id" class="form-label">Semester</label>
                        <select class="form-control" id="edit_semester_id" name="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?php echo $semester['semester_id']; ?>">
                                    <?php echo htmlspecialchars($semester['semester_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_registration_number" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="edit_registration_number" name="registration_number" required>
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
<?php require_once 'includes/footer.php'; ?>