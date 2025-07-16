<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

requireRole('admin');

// Get all programs for dropdown
$programs = getTableData('programs');
$semesters = getTableData('semesters', '*', null, 'semester_name ASC');

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_course':
                $data = [
                    'course_name' => sanitizeInput($_POST['course_name']),
                    'course_code' => sanitizeInput($_POST['course_code']),
                    'credit_hours' => intval($_POST['credit_hours']),
                    'program_id' => intval($_POST['program_id']),
                    'semester_id' => intval($_POST['semester_id'])
                ];
                if (insertData('courses', $data)) {
                    echo json_encode(['success' => true, 'message' => 'Course added successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error adding course']);
                }
                exit();
            
            case 'edit_course':
                $data = [
                    'course_name' => sanitizeInput($_POST['course_name']),
                    'course_code' => sanitizeInput($_POST['course_code']),
                    'credit_hours' => intval($_POST['credit_hours']),
                    'program_id' => intval($_POST['program_id']),
                    'semester_id' => intval($_POST['semester_id'])
                ];
                $where = "course_id = " . intval($_POST['course_id']);
                if (updateData('courses', $data, $where)) {
                    echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error updating course']);
                }
                exit();
            
            case 'delete_course':
                $where = "course_id = " . intval($_POST['course_id']);
                if (deleteData('courses', $where)) {
                    echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error deleting course']);
                }
                exit();
        }
    }
}

// Get all courses with program names
$courses = $pdo->query("
    SELECT c.*, p.program_name 
    FROM courses c 
    LEFT JOIN programs p ON c.program_id = p.program_id
")->fetchAll();

// Search functionality
$search = $_GET['search'] ?? '';
if ($search) {
    $courses = $pdo->query("
        SELECT c.*, p.program_name 
        FROM courses c 
        LEFT JOIN programs p ON c.program_id = p.program_id
        WHERE c.Course_name LIKE '%$search%' 
        OR c.Course_code LIKE '%$search%' 
        OR p.program_name LIKE '%$search%'
        ORDER BY c.Course_name ASC
    ")->fetchAll();
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$totalCourses = count($courses);
$totalPages = ceil($totalCourses / $limit);

$courses = $pdo->query("
    SELECT c.*, p.program_name, s.semester_name, s.academic_year
    FROM courses c
    LEFT JOIN programs p ON c.program_id = p.program_id
    LEFT JOIN semesters s ON c.semester_id = s.semester_id
    ORDER BY c.course_name ASC
    LIMIT $offset, $limit
")->fetchAll();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Courses</h2>
            <hr>
        </div>
    </div>

    <!-- Search form -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form id="searchForm" class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Search courses..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- Add course button -->
    <div class="row mb-4">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                Add New Course
            </button>
        </div>
    </div>

    <!-- Courses table -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Course Name</th>
                            <th>Course Code</th>
                            <th>Credits</th>
                            <th>Program</th>
                            <th>Semester</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo $course['course_id']; ?></td>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['credit_hours']; ?></td>
                                <td><?php echo htmlspecialchars($course['program_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($course['semester_name'] . ' (' . $course['academic_year'] . ')'); ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-course" 
                                        data-id="<?php echo $course['course_id']; ?>"
                                        data-code="<?php echo htmlspecialchars($course['course_code']); ?>"
                                        data-name="<?php echo htmlspecialchars($course['course_name']); ?>"
                                        data-credithours="<?php echo $course['credit_hours']; ?>"
                                        data-program="<?php echo $course['program_id']; ?>"
                                        data-semesterid="<?php echo $course['semester_id']; ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editCourseModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-course" 
                                        data-id="<?php echo $course['course_id']; ?>">
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

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addCourseForm" method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="addCourseMessageBox" class="alert d-none mx-3 mt-3" role="alert"></div>
                    <input type="hidden" name="action" value="add_course">
                    <div class="mb-3">
                        <label for="Course_name" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="Course_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="course_code" name="course_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="credits" class="form-label">Credits</label>
                        <input type="number" class="form-control" id="credit_hours" name="credit_hours" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="program_id" class="form-label">Program</label>
                        <select class="form-control" id="program_id" name="program_id" required>
                            <option value="">Select Program</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo $program['program_id']; ?>">
                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="semester_id" class="form-label">Semester</label>
                        <select class="form-control" id="semester_id" name="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?php echo $semester['semester_id']; ?>">
                                    <?php echo htmlspecialchars($semester['semester_name'] . ' (' . $semester['academic_year'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCourseForm" method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="editCourseMessageBox" class="alert d-none mx-3 mt-3" role="alert"></div>
                    <input type="hidden" name="action" value="edit_course">
                    <input type="hidden" name="course_id" id="course_id">
                    <div class="mb-3">
                        <label for="editCourseName" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="editCourseName" name="course_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCourseCode" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="editCourseCode" name="course_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCredits" class="form-label">Credits</label>
                        <input type="number" class="form-control" id="editCreditHours" name="credit_hours" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="editProgram" class="form-label">Program</label>
                        <select class="form-control" id="editProgram" name="program_id" required>
                            <option value="">Select Program</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo $program['program_id']; ?>">
                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editSemester" class="form-label">Semester</label>
                        <select class="form-control" id="editSemester" name="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?php echo $semester['semester_id']; ?>">
                                    <?php echo htmlspecialchars($semester['semester_name'] . ' (' . $semester['academic_year'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<div id="messageBox" class="alert d-none position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index:9999"></div>

<script>
function showFormMessage(message, type = 'success', boxId = 'formMessageBox') {
    const box = document.getElementById(boxId);
    box.textContent = message;
    box.classList.remove('d-none', 'alert-success', 'alert-danger');
    box.classList.add(type === 'error' ? 'alert-danger' : 'alert-success');
    setTimeout(() => box.classList.add('d-none'), 5000);
}

// Add Course
document.querySelector('#addCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.set('action', 'add_course');
    fetch('api/courses.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFormMessage(data.message, data.success ? 'success' : 'error', 'addCourseMessageBox');
        if (data.success) {
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('addCourseModal')).hide();
            }, 1500);
            setTimeout(() => location.reload(), 5000); // reload after message timeout
        }
    });
});

// Edit Modal: Fill form
document.querySelectorAll('.edit-course').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('course_id').value = this.dataset.id;
        document.getElementById('editCourseName').value = this.dataset.name;
        document.getElementById('editCourseCode').value = this.dataset.code;
        document.getElementById('editCredits').value = this.dataset.credits;
        document.getElementById('editProgram').value = this.dataset.program;
        document.getElementById('editSemester').value = this.dataset.semesterid; // Use semesterid
        document.getElementById('editCreditHours').value = this.dataset.credithours;
    });
});

// Edit Course
document.querySelector('#editCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.set('action', 'edit_course');
    fetch('api/courses.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFormMessage(data.message, data.success ? 'success' : 'error', 'editCourseMessageBox');
        if (data.success) {
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('editCourseModal')).hide();
            }, 1500);
            setTimeout(() => location.reload(), 5000);
        }
    });
});

// Delete Course
document.querySelectorAll('.delete-course').forEach(button => {
    button.addEventListener('click', function() {
        if (!confirm('Are you sure you want to delete this course?')) return;
        const formData = new FormData();
        formData.set('action', 'delete_course');
        formData.set('course_id', this.dataset.id);
        fetch('api/courses.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            showFormMessage(data.message, data.success ? 'success' : 'error', 'messageBox');
            if (data.success) {
                setTimeout(() => location.reload(), 1500);
            }
        });
    });
});
</script>