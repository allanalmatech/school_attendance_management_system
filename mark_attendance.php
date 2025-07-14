<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

requireRole('faculty');

// Get current user's faculty ID
$facultyId = $_SESSION['user_id'];

// Get faculty's assigned programs
$facultyPrograms = $pdo->query("
    SELECT p.*
    FROM programs p
    JOIN faculty_has_programs fp ON p.program_id = fp.program_id
    WHERE fp.faculty_id = $facultyId
")->fetchAll();

// Get current semester (assuming only one active semester)
$currentSemester = $pdo->query("
    SELECT s.*
    FROM semester s
    WHERE s.status = 1
    ORDER BY s.start_date DESC
    LIMIT 1
")->fetch();

if (!$currentSemester) {
    die('No active semester found');
}

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'mark_attendance') {
        $data = [
            'course_id' => intval($_POST['course_id']),
            'semester_id' => intval($_POST['semester_id']),
            'date' => date('Y-m-d')
        ];
        
        // Delete existing attendance for this course/date
        $pdo->query("
            DELETE FROM attendance_has_courses 
            WHERE course_id = " . $data['course_id'] . "
            AND semester_id = " . $data['semester_id'] . "
            AND date = '" . $data['date'] . "'
        ");
        
        // Insert new attendance records
        foreach ($_POST['students'] as $studentId => $present) {
            $pdo->query("
                INSERT INTO attendance_has_courses 
                (course_id, semester_id, student_id, date, present)
                VALUES (
                    " . $data['course_id'] . ",
                    " . $data['semester_id'] . ",
                    $studentId,
                    '" . $data['date'] . "',
                    " . (intval($present) === 1 ? 1 : 0) . "
                )
            ");
        }
        
        echo generateResponse(true, 'Attendance marked successfully');
        exit();
    }
}

// Get faculty's courses for the current semester
$courses = $pdo->query("
    SELECT c.*, p.Programme_name
    FROM courses c
    JOIN programs_has_courses pc ON c.course_id = pc.course_id
    JOIN programs p ON pc.program_id = p.program_id
    JOIN faculty_has_programs fp ON p.program_id = fp.program_id
    WHERE fp.faculty_id = $facultyId
    AND pc.semester_id = " . $currentSemester['semester_id'] . "
    ORDER BY p.Programme_name, c.Course_name
")->fetchAll();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Mark Attendance</h2>
            <hr>
        </div>
    </div>

    <!-- Select Course -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h3>Select Course</h3>
            <form method="POST" action="" data-ajax class="row g-3">
                <input type="hidden" name="action" value="mark_attendance">
                <div class="col-md-6">
                    <label for="course_id" class="form-label">Course</label>
                    <select class="form-control" id="course_id" name="course_id" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['Programme_name'] . ' - ' . $course['Course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="semester_id" class="form-label">Semester</label>
                    <select class="form-control" id="semester_id" name="semester_id" required>
                        <option value="<?php echo $currentSemester['semester_id']; ?>">
                            <?php echo htmlspecialchars($currentSemester['semester_name']); ?>
                        </option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Load Students</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Table -->
    <div id="attendanceTable" class="row mb-4" style="display: none;">
        <div class="col-md-12">
            <h3>Mark Attendance for Selected Course</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Registration Number</th>
                            <th>Present</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceBody">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button id="submitAttendance" class="btn btn-primary" disabled>
                    Submit Attendance
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageModalBody">
                <!-- Message will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_id');
    const semesterSelect = document.getElementById('semester_id');
    const attendanceTable = document.getElementById('attendanceTable');
    const attendanceBody = document.getElementById('attendanceBody');
    const submitBtn = document.getElementById('submitAttendance');
    let selectedCourse = null;

    // Load students when course is selected
    courseSelect.addEventListener('change', function() {
        if (this.value) {
            selectedCourse = this.value;
            loadStudents(this.value, semesterSelect.value);
        }
    });

    // Load students when semester is selected
    semesterSelect.addEventListener('change', function() {
        if (selectedCourse) {
            loadStudents(selectedCourse, this.value);
        }
    });

    // Load students via AJAX
    function loadStudents(courseId, semesterId) {
        fetch('api/attendance.php?action=get_students&course_id=' + courseId + '&semester_id=' + semesterId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    attendanceTable.style.display = 'block';
                    submitBtn.disabled = false;
                    populateTable(data.data);
                }
            });
    }

    // Populate attendance table
    function populateTable(students) {
        let html = '';
        students.forEach(student => {
            html += `
                <tr>
                    <td>${student.Student_name}</td>
                    <td>${student.Reg_number}</td>
                    <td>
                        <input type="hidden" name="students[${student.ID}]" value="0">
                        <input type="checkbox" name="students[${student.ID}]" value="1" class="form-check-input">
                    </td>
                </tr>
            `;
        });
        attendanceBody.innerHTML = html;
    }

    // Handle form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'mark_attendance');
        
        // Add attendance data from checkboxes
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            formData.append('students[' + checkbox.name.split('[')[1].split(']')[0] + ']', checkbox.checked ? 1 : 0);
        });

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                document.getElementById('messageModalTitle').textContent = 'Success';
                document.getElementById('messageModalBody').innerHTML = `
                    <div class="alert alert-success">
                        ${data.message}
                    </div>
                `;
                messageModal.show();
                
                // Clear form and hide attendance table
                this.reset();
                attendanceTable.style.display = 'none';
                submitBtn.disabled = true;
            } else {
                // Show error message
                const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                document.getElementById('messageModalTitle').textContent = 'Error';
                document.getElementById('messageModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message}
                    </div>
                `;
                messageModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            document.getElementById('messageModalTitle').textContent = 'Error';
            document.getElementById('messageModalBody').innerHTML = `
                <div class="alert alert-danger">
                    An error occurred while marking attendance. Please try again.
                </div>
            `;
            messageModal.show();
        });
    });
});
</script>