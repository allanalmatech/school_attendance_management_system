<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

requireRole('admin');

// Get all programs for dropdown
$programs = getTableData('programs');

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    'Course_name' => sanitizeInput($_POST['Course_name']),
                    'Course_code' => sanitizeInput($_POST['Course_code']),
                    'credits' => intval($_POST['credits']),
                    'program_id' => intval($_POST['program_id']),
                    'semester' => intval($_POST['semester'])
                ];
                if (insertData('courses', $data)) {
                    echo generateResponse(true, 'Course added successfully');
                } else {
                    echo generateResponse(false, 'Error adding course');
                }
                exit();
            
            case 'edit':
                $data = [
                    'Course_name' => sanitizeInput($_POST['Course_name']),
                    'Course_code' => sanitizeInput($_POST['Course_code']),
                    'credits' => intval($_POST['credits']),
                    'program_id' => intval($_POST['program_id']),
                    'semester' => intval($_POST['semester'])
                ];
                $where = "course_id = " . intval($_POST['course_id']);
                if (updateData('courses', $data, $where)) {
                    echo generateResponse(true, 'Course updated successfully');
                } else {
                    echo generateResponse(false, 'Error updating course');
                }
                exit();
            
            case 'delete':
                $where = "course_id = " . intval($_POST['course_id']);
                if (deleteData('courses', $where)) {
                    echo generateResponse(true, 'Course deleted successfully');
                } else {
                    echo generateResponse(false, 'Error deleting course');
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
    SELECT c.*, p.program_name 
    FROM courses c 
    LEFT JOIN programs p ON c.program_id = p.program_id
    ORDER BY c.Course_name ASC 
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
                                <td><?php echo htmlspecialchars($course['Course_name']); ?></td>
                                <td><?php echo htmlspecialchars($course['Course_code']); ?></td>
                                <td><?php echo $course['credits']; ?></td>
                                <td><?php echo htmlspecialchars($course['program_name']); ?></td>
                                <td><?php echo $course['semester']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-course" 
                                        data-id="<?php echo $course['course_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($course['Course_name']); ?>"
                                        data-code="<?php echo htmlspecialchars($course['Course_code']); ?>"
                                        data-credits="<?php echo $course['credits']; ?>"
                                        data-program="<?php echo $course['program_id']; ?>"
                                        data-semester="<?php echo $course['semester']; ?>"
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
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="Course_name" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="Course_name" name="Course_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="Course_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="Course_code" name="Course_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="credits" class="form-label">Credits</label>
                        <input type="number" class="form-control" id="credits" name="credits" min="1" required>
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
                        <label for="semester" class="form-label">Semester</label>
                        <select class="form-control" id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
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
            <form method="POST" action="" data-ajax>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="course_id" id="course_id">
                    <div class="mb-3">
                        <label for="editCourseName" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="editCourseName" name="Course_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCourseCode" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="editCourseCode" name="Course_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCredits" class="form-label">Credits</label>
                        <input type="number" class="form-control" id="editCredits" name="credits" min="1" required>
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
                        <select class="form-control" id="editSemester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
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