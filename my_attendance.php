<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';
requireRole('faculty');
?>

<div class="container mt-4">
    <h2>Mark Attendance (QR Scan)</h2>
    <hr>

    <!-- Select session details -->
    <form id="sessionForm" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Program</label>
                <select name="program_id" class="form-control" required>
                    <!-- populate with DB -->
                    <option value="1">Program A</option>
                    <option value="2">Program B</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Semester</label>
                <select name="semester_id" class="form-control" required>
                    <option value="1">2023 Semester 1</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Course</label>
                <select name="course_id" class="form-control" required>
                    <option value="1">Math 101</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Date</label>
                <input type="date" name="attendance_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>
    </form>

    <!-- QR Scanner -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div id="reader" style="width:100%;"></div>
        </div>
        <div class="col-md-6">
            <h4>Marked Present</h4>
            <ul id="attendanceList" class="list-group"></ul>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let sessionData = {};

document.getElementById('sessionForm').addEventListener('change', function(){
    const formData = new FormData(this);
    sessionData = Object.fromEntries(formData);
});

// Initialize QR scanner
let scanner = new Html5Qrcode("reader");

Html5Qrcode.getCameras().then(devices => {
    if (devices && devices.length) {
        scanner.start(
            devices[0].id,
            { fps: 10, qrbox: 250 },
            onScanSuccess
        );
    }
});

function onScanSuccess(decodedText, decodedResult) {
    console.log(`QR code scanned: ${decodedText}`);

    if (!sessionData.program_id) {
        alert('Please select program/semester/course/date first.');
        return;
    }

    // Send AJAX to record attendance
    fetch('api/attendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'mark_attendance',
            student_id: decodedText, // assumes QR code holds student_ID
            ...sessionData
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const item = document.createElement('li');
            item.className = 'list-group-item list-group-item-success';
            item.textContent = `Marked: ${data.student_name}`;
            document.getElementById('attendanceList').prepend(item);
        } else {
            alert(data.message);
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
