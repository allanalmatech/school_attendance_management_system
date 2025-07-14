 
<?php
require_once 'config.php';
require_once 'auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/dashboard.php">Attendance System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <?php if (hasRole('admin')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/students.php">Students</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/faculty.php">Faculty</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/programs.php">Programs</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/courses.php">Courses</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/add_accounts.php">Manage Accounts</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/semesters.php">Semesters</a></li>
                    <?php endif; ?>
                    <?php if (hasRole('faculty')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/mark_attendance.php">Mark Attendance</a></li>
                    <?php endif; ?>
                    <?php if (hasRole('student')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/my_attendance.php">My Attendance</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        </div>
    </div>