<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

//requireRole('faculty');

// Load courses
$stmt = $pdo->query("SELECT course_id, course_name FROM courses ORDER BY course_name");
$courses = $stmt->fetchAll();

// Load students
$stmt = $pdo->query("
    SELECT student_id, registration_number, first_name, last_name 
    FROM students 
    ORDER BY first_name, last_name
");
$students = $stmt->fetchAll();

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $attendance_date = $_POST['attendance_date'] ?? date('Y-m-d');
    $present_students = $_POST['present_students'] ?? [];

    foreach ($present_students as $student_id) {
        // Check if already recorded
        $stmt = $pdo->prepare("SELECT attendance_id FROM attendance_records WHERE student_id = ? AND course_id = ? AND attendance_date = ?");
        $stmt->execute([$student_id, $course_id, $attendance_date]);
        if (!$stmt->fetch()) {
            // Insert new attendance record
            $stmt = $pdo->prepare("
                INSERT INTO attendance_records 
                (student_id, course_id, attendance_date, status, created_by, created_at)
                VALUES (?, ?, ?, 'present', ?, NOW())
            ");
            $stmt->execute([$student_id, $course_id, $attendance_date, $_SESSION['user_id']]);
        }
    }

    $message = "Attendance recorded successfully.";
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="container mt-4">
    <h2>Mark Attendance</h2>
    <hr>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Course</label>
                <select name="course_id" class="form-control" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>">
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" class="form-control" required>
            </div>
        </div>

        <h4>Select Present Students</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th></th><th>Student</th></tr></thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="present_students[]" value="<?php echo $student['student_id']; ?>">
                            </td>
                            <td>
                                <?php echo htmlspecialchars(
                                    $student['registration_number'] . " - " . 
                                    $student['first_name'] . " " . $student['last_name']
                                ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Save Attendance</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
