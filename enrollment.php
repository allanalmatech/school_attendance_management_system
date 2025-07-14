<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

requireRole('admin');

// Get all programs for dropdown
$programs = getTableData('programs');

// Get all courses for dropdown
$courses = getTableData('courses');

// Get all semesters for dropdown
$semesters = getTableData('semester');

// Handle enrollment operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'enroll_student':
                $data = [
                    'student_id' => intval($_POST['student_id']),
                    'program_id' => intval($_POST['program_id']),
                    'enrollment_date' => date('Y-m-d')
                ];
                if (insertData('student_has_programs', $data)) {
                    echo generateResponse(true, 'Student enrolled successfully');
                } else {
                    echo generateResponse(false, 'Error enrolling student');
                }
                exit();
            
            case 'assign_course':
                $data = [
                    'program_id' => intval($_POST['program_id']),
                    'course_id' => intval($_POST['course_id']),
                    'semester_id' => intval($_POST['semester_id'])
                ];
                if (insertData('programs_has_courses', $data)) {
                    echo generateResponse(true, 'Course assigned successfully');
                } else {
                    echo generateResponse(false, 'Error assigning course');
                }
                exit();
            
            case 'assign_faculty':
                $data = [
                    'faculty_id' => intval($_POST['faculty_id']),
                    'program_id' => intval($_POST['program_id'])
                ];
                if (insertData('faculty_has_programs', $data)) {
                    echo generateResponse(true, 'Faculty assigned successfully');
                } else {
                    echo generateResponse(false, 'Error assigning faculty');
                }
                exit();
        }
    }
}

// Get all enrollments with student and program details
$enrollments = $pdo->query("
    SELECT s.*, p.Programme_name, pr.program_id 
    FROM student s 
    LEFT JOIN student_has_programs shp ON s.ID = shp.student_id 
    LEFT JOIN programs p ON shp.program_id = p.program_id
    ORDER BY s.Student_name ASC
")->fetchAll();

// Get all course assignments with program and course details
$courseAssignments = $pdo->query("
    SELECT pc.*, c.Course_name, p.Programme_name 
    FROM programs_has_courses pc 
    LEFT JOIN courses c ON pc.course_id = c.course_id 
    LEFT JOIN programs p ON pc.program_id = p.program_id
    ORDER BY p.Programme_name, c.Course_name
")->fetchAll();

// Get all faculty assignments with faculty and program details
$facultyAssignments = $pdo->query("
    SELECT fp.*, f.Faculty_name, p.Programme_name 
    FROM faculty_has_programs fp 
    LEFT JOIN faculty f ON fp.faculty_id = f.faculty_id 
    LEFT JOIN programs p ON fp.program_id = p.program_id
    ORDER BY p.Programme_name, f.Faculty_name
")->fetchAll();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Enrollment Management</h2>
            <hr>
        </div>
    </div>

    <!-- Enroll Student -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h3>Enroll Student</h3>
            <form method="POST" action="" data-ajax class="row g-3">
                <input type="hidden" name="action" value="enroll_student">
                <div class="col-md-6">
                    <label for="student_id" class="form-label">Student</label>
                    <select class="form-control" id="student_id" name="student_id" required>
                        <option value="">Select Student</option>
                        <?php 
                        $students = getTableData('student');
                        foreach ($students as $student): 
                        ?>
                            <option value="<?php echo $student['ID']; ?>">
                                <?php echo htmlspecialchars($student['Student_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="program_id" class="form-label">Program</label>
                    <select class="form-control" id="program_id" name="program_id" required>
                        <option value="">Select Program</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?php echo $program['program_id']; ?>">
                                <?php echo htmlspecialchars($program['Programme_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Enroll Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Course to Program -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h3>Assign Course to Program</h3>
            <form method="POST" action="" data-ajax class="row g-3">
                <input type="hidden" name="action" value="assign_course">
                <div class="col-md-4">
                    <label for="program_id_course" class="form-label">Program</label>
                    <select class="form-control" id="program_id_course" name="program_id" required>
                        <option value="">Select Program</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?php echo $program['program_id']; ?>">
                                <?php echo htmlspecialchars($program['Programme_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="course_id" class="form-label">Course</label>
                    <select class="form-control" id="course_id" name="course_id" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['Course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="semester_id" class="form-label">Semester</label>
                    <select class="form-control" id="semester_id" name="semester_id" required>
                        <option value="">Select Semester</option>
                        <?php foreach ($semesters as $semester): ?>
                            <option value="<?php echo $semester['semester_id']; ?>">
                                <?php echo htmlspecialchars($semester['semester_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Assign Course</button>
                </div>
            </form>
        </div>
    </div>

   <!-- Assign Faculty to Program -->
<div class="row mb-4">
    <div class="col-md-12">
        <h3>Assign Faculty to Program</h3>
        <form method="POST" action="" data-ajax class="row g-3">
            <input type="hidden" name="action" value="assign_faculty">
            <div class="col-md-6">
                <label for="faculty_id" class="form-label">Faculty</label>
                <select class="form-control" id="faculty_id" name="faculty_id" required>
                    <option value="">Select Faculty</option>
                    <?php 
                    $faculty = getTableData('faculty');
                    foreach ($faculty as $member): 
                    ?>
                        <option value="<?php echo $member['faculty_id']; ?>">
                            <?php echo htmlspecialchars($member['Faculty_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="program_id_faculty" class="form-label">Program</label>
                <select class="form-control" id="program_id_faculty" name="program_id" required>
                    <option value="">Select Program</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?php echo $program['program_id']; ?>">
                            <?php echo htmlspecialchars($program['Programme_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Assign Faculty</button>
            </div>
        </form>
    </div>
</div>

<!-- Faculty Assignments Table -->
<div class="row mb-4">
    <div class="col-md-12">
        <h3>Current Faculty Assignments</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Faculty Name</th>
                        <th>Program</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facultyAssignments as $assignment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['Faculty_name']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['Programme_name']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-danger delete-assignment" 
                                    data-id="<?php echo $assignment['faculty_id']; ?>"
                                    data-program="<?php echo $assignment['program_id']; ?>">
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