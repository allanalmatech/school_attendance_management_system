<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Get user information
$user = getCurrentUser();
$role = $_SESSION['role'];

// Get statistics based on role
$stats = [];
switch ($role) {
    case 'admin':
        $stats = [
            'total_students' => count(getTableData('student')),
            'total_faculty' => count(getTableData('faculty')),
            'total_programs' => count(getTableData('programs')),
            'total_courses' => count(getTableData('courses')),
            'total_semesters' => count(getTableData('semester'))
        ];
        break;
    case 'faculty':
        $stats = [
            'assigned_courses' => count(getTableData('faculty_has_programs', 'COUNT(DISTINCT p.program_id) as count', 
                'fp.faculty_id = ?', [$_SESSION['user_id']])),
            'students_in_courses' => count(getTableData('student_has_courses', 'COUNT(DISTINCT s.student_ID) as count', 
                's.student_ID IN (SELECT student_ID FROM faculty WHERE faculty_id = ?)', [$_SESSION['user_id']])),
            'recent_attendance' => getTableData('attendance', '*', 'Attendance_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)', 
                'Attendance_date DESC LIMIT 5')
        ];
        break;
    case 'student':
        $stats = [
            'enrolled_courses' => count(getTableData('student_has_courses', 'COUNT(course_id) as count', 
                'student_ID = ?', [$_SESSION['user_id']])),
            'attendance_percentage' => getAttendanceStatus($_SESSION['user_id']),
            'upcoming_classes' => getUpcomingClasses($_SESSION['user_id'])
        ];
        break;
}

// Helper functions for student dashboard
function getAttendanceStatus($studentId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
            COUNT(*) as total
        FROM attendance_has_student
        WHERE student_ID = ?
    ");
    $stmt->execute([$studentId]);
    $result = $stmt->fetch();
    
    return generateAttendanceStatus($result['present'], $result['total']);
}

function getUpcomingClasses($studentId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            c.course_name,
            s.Academic_year,
            a.Attendance_date
        FROM attendance a
        JOIN attendance_has_student ash ON ash.attendance_id = a.attendance_id
        JOIN student_has_courses shc ON shc.student_ID = ash.student_ID
        JOIN courses c ON c.course_id = shc.course_id
        JOIN semester s ON s.semester_id = a.semester_id
        WHERE ash.student_ID = ? AND a.Attendance_date >= CURRENT_DATE
        ORDER BY a.Attendance_date ASC
        LIMIT 5
    ");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Dashboard</h2>
            <hr>
        </div>
    </div>

    <div class="row mt-4">
        <?php if ($role === 'admin'): ?>
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Students</h5>
                        <p class="card-text display-6"><?php echo $stats['total_students']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Faculty</h5>
                        <p class="card-text display-6"><?php echo $stats['total_faculty']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Total Programs</h5>
                        <p class="card-text display-6"><?php echo $stats['total_programs']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Total Courses</h5>
                        <p class="card-text display-6"><?php echo $stats['total_courses']; ?></p>
                    </div>
                </div>
            </div>
        <?php elseif ($role === 'faculty'): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Assigned Courses</h5>
                        <p class="card-text display-6"><?php echo $stats['assigned_courses']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Students in Courses</h5>
                        <p class="card-text display-6"><?php echo $stats['students_in_courses']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Attendance</h5>
                        <ul class="list-group">
                            <?php foreach ($stats['recent_attendance'] as $attendance): ?>
                                <li class="list-group-item">
                                    <?php echo $attendance['Attendance_date']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php elseif ($role === 'student'): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Enrolled Courses</h5>
                        <p class="card-text display-6"><?php echo $stats['enrolled_courses']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Attendance Percentage</h5>
                        <p class="card-text display-6"><?php echo $stats['attendance_percentage']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Classes</h5>
                        <ul class="list-group">
                            <?php foreach ($stats['upcoming_classes'] as $class): ?>
                                <li class="list-group-item">
                                    <?php echo $class['course_name'] . ' - ' . $class['Academic_year']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>