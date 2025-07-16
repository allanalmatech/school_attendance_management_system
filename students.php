<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

requireRole('admin');

// Get all students for display
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch students via API
$students = [];
$totalStudents = 0;
$totalPages = 1;

try {
    $postData = http_build_query([
        'action' => 'get_all_students',
        'search' => $search,
        'page' => $page
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'api/student.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (is_array($data) && isset($data['success']) && $data['success']) {
        $students = $data['data'];
        $totalStudents = $data['pagination']['total_items'];
        $totalPages = $data['pagination']['total_pages'];
    } else {
        $students = [];
        $totalStudents = 0;
        $totalPages = 1;
        error_log("API error or invalid JSON: " . $response);
    }
} catch (Exception $e) {
    error_log("Error fetching students: " . $e->getMessage());
    error_log("API response: " . $response);
}
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
            <form id="searchForm" class="d-flex" method="GET" action="">
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
                                <td><?php echo htmlspecialchars($student['phone'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['program_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['semester_name'] ?? ''); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-student" 
                                        data-id="<?php echo $student['student_id']; ?>"
                                        data-reg="<?php echo htmlspecialchars($student['registration_number']); ?>"
                                        data-first="<?php echo htmlspecialchars($student['first_name']); ?>"
                                        data-last="<?php echo htmlspecialchars($student['last_name']); ?>"
                                        data-dob="<?php echo htmlspecialchars($student['date_of_birth']); ?>"
                                        data-gender="<?php echo htmlspecialchars($student['gender']); ?>"
                                        data-phone="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>"
                                        data-email="<?php echo htmlspecialchars($student['email']); ?>"
                                        data-address="<?php echo htmlspecialchars($student['address'] ?? ''); ?>"
                                        data-program-id="<?php echo $student['program_id']; ?>"
                                        data-semester-id="<?php echo $student['semester_id']; ?>"
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
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next</a>
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
            <form id="addStudentForm" method="POST" action="api/student.php">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="addStudentMessageBox" class="alert d-none" role="alert"></div>
                    <input type="hidden" name="action" value="add_student">
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
                        <label for="pam_gender" class="form-label">Gender</label>
                        <select class="form-control" id="pam_gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
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
                            <option value="">Select Program</option>
                            <?php
                            $programs = getTableData('programs');
                            foreach ($programs as $program) {
                                echo '<option value="' . $program['program_id'] . '">' . htmlspecialchars($program['program_name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="semester_id" class="form-label">Semester</label>
                        <select class="form-control" id="semester_id" name="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php
                            $semesters = getTableData('semesters');
                            foreach ($semesters as $semester) {
                                echo '<option value="' . $semester['semester_id'] . '">' . htmlspecialchars($semester['semester_name']) . '</option>';
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
            <form id="editStudentForm" method="POST" action="api/student.php">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="editStudentMessageBox" class="alert d-none" role="alert"></div>
                    <input type="hidden" name="action" value="edit_student">
                    <input type="hidden" name="student_id" id="edit_student_id">
                    <div class="mb-3">
                        <label for="edit_registration_number" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="edit_registration_number" name="registration_number" required>
                    </div>
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
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address"></textarea>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Student
    document.getElementById('addStudentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('api/student.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showFormMessage(data.message, data.success ? 'success' : 'error', 'addStudentMessageBox');
            if (data.success) {
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('addStudentModal')).hide();
                }, 5000);
                setTimeout(() => location.reload(), 5000);
            }
        })
        .catch(error => {
            showFormMessage('An error occurred: ' + error.message, 'error', 'addStudentMessageBox');
        });
    });

    // Edit Student
    document.querySelectorAll('.edit-student').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_student_id').value = this.dataset.id;
            document.getElementById('edit_registration_number').value = this.dataset.reg;
            document.getElementById('edit_first_name').value = this.dataset.first;
            document.getElementById('edit_last_name').value = this.dataset.last;
            document.getElementById('edit_date_of_birth').value = this.dataset.dob;
            document.getElementById('edit_gender').value = this.dataset.gender;
            document.getElementById('edit_phone').value = this.dataset.phone;
            document.getElementById('edit_email').value = this.dataset.email;
            document.getElementById('edit_address').value = this.dataset.address;
            document.getElementById('edit_program_id').value = this.dataset.programId;
            document.getElementById('edit_semester_id').value = this.dataset.semesterId;
        });
    });

    document.getElementById('editStudentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('api/student.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showFormMessage(data.message, data.success ? 'success' : 'error', 'editStudentMessageBox');
            if (data.success) {
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('editStudentModal')).hide();
                }, 5000);
                setTimeout(() => location.reload(), 5000);
            }
        })
        .catch(error => {
            showFormMessage('An error occurred: ' + error.message, 'error', 'editStudentMessageBox');
        });
    });

    // Delete Student
    document.querySelectorAll('.delete-student').forEach(button => {
        button.addEventListener('click', function() {
            if (!confirm('Are you sure you want to delete this student?')) return;
            const formData = new FormData();
            formData.set('action', 'delete_student');
            formData.set('student_id', this.dataset.id);
            fetch('api/student.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showFormMessage(data.message, data.success ? 'success' : 'error', 'addStudentMessageBox');
                if (data.success) {
                    setTimeout(() => location.reload(), 5000);
                }
            })
            .catch(error => {
                showFormMessage('An error occurred: ' + error.message, 'error', 'addStudentMessageBox');
            });
        });
    });

    function showFormMessage(message, type = 'success', boxId = 'addStudentMessageBox') {
        const box = document.getElementById(boxId);
        box.textContent = message;
        box.classList.remove('d-none', 'alert-success', 'alert-danger');
        box.classList.add(type === 'error' ? 'alert-danger' : 'alert-success');
        setTimeout(() => box.classList.add('d-none'), 5000);
    }
});
</script>