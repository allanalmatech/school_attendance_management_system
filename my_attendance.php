<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

requireRole('student');

// Get current user's student ID
$studentId = $_SESSION['user_id'];

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

// Get student's enrolled programs
$programs = $pdo->query("
    SELECT p.*, shp.enrollment_date
    FROM programs p
    JOIN student_has_programs shp ON p.program_id = shp.program_id
    WHERE shp.student_id = $studentId
")->fetchAll();

// Get student's courses for the current semester
$courses = $pdo->query("
    SELECT c.*, pc.semester_id, p.Programme_name
    FROM courses c
    JOIN programs_has_courses pc ON c.course_id = pc.course_id
    JOIN programs p ON pc.program_id = p.program_id
    JOIN student_has_programs shp ON p.program_id = shp.program_id
    WHERE shp.student_id = $studentId
    AND pc.semester_id = " . $currentSemester['semester_id'] . "
    ORDER BY p.Programme_name, c.Course_name
")->fetchAll();

// Calculate attendance percentage for each course
$attendanceData = [];
foreach ($courses as $course) {
    $totalClasses = $pdo->query("
        SELECT COUNT(*) as total
        FROM attendance_has_courses
        WHERE course_id = " . $course['course_id'] . "
        AND semester_id = " . $course['semester_id'] . "
    ")->fetch()['total'];
    
    $presentClasses = $pdo->query("
        SELECT COUNT(*) as present
        FROM attendance_has_courses
        WHERE course_id = " . $course['course_id'] . "
        AND semester_id = " . $course['semester_id'] . "
        AND student_id = $studentId
        AND present = 1
    ")->fetch()['present'];
    
    $attendanceData[$course['course_id']] = [
        'total' => $totalClasses,
        'present' => $presentClasses,
        'percentage' => $totalClasses > 0 ? round(($presentClasses / $totalClasses) * 100) : 0
    ];
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>My Attendance</h2>
            <hr>
        </div>
    </div>

    <!-- Attendance Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h3>Attendance Summary</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Course</th>
                            <th>Classes Attended</th>
                            <th>Total Classes</th>
                            <th>Attendance %</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['Programme_name']); ?></td>
                                <td><?php echo htmlspecialchars($course['Course_name']); ?></td>
                                <td><?php echo $attendanceData[$course['course_id']]['present']; ?></td>
                                <td><?php echo $attendanceData[$course['course_id']]['total']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress me-2" style="width: 150px;">
                                            <div class="progress-bar" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $attendanceData[$course['course_id']]['percentage']; ?>%;"
                                                 aria-valuenow="<?php echo $attendanceData[$course['course_id']]['percentage']; ?>"
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?php echo $attendanceData[$course['course_id']]['percentage']; ?>%
                                            </div>
                                        </div>
                                        <span><?php echo $attendanceData[$course['course_id']]['percentage']; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $percentage = $attendanceData[$course['course_id']]['percentage'];
                                    if ($percentage >= 75) {
                                        echo '<span class="badge bg-success">Good</span>';
                                    } elseif ($percentage >= 50) {
                                        echo '<span class="badge bg-warning">Needs Improvement</span>';
                                    } else {
                                        echo '<span class="badge bg-danger">Poor</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Attendance -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h3>Detailed Attendance Records</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $attendanceRecords = $pdo->query("
                            SELECT c.Course_name, a.date, a.present
                            FROM attendance_has_courses a
                            JOIN courses c ON a.course_id = c.course_id
                            WHERE a.student_id = $studentId
                            AND a.semester_id = " . $currentSemester['semester_id'] . "
                            ORDER BY a.date DESC
                        ")->fetchAll();
                        
                        foreach ($attendanceRecords as $record):
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['Course_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($record['date'])); ?></td>
                                <td>
                                    <?php if ($record['present'] == 1): ?>
                                        <span class="badge bg-success">Present</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Absent</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>