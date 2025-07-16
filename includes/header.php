 
<?php
require_once 'config.php';
require_once 'auth.php';



// Handle authentication before any output
requireLogin();

// Set up the session variables if not already set
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'student'; // Default role
}

// Get the base URL
$base_url = rtrim(dirname($_SERVER['PHP_SELF']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background-color: #343a40;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .sidebar.hide {
            width: 80px;
        }
        .sidebar .logo {
            color: white;
            font-size: 24px;
            margin-bottom: 30px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .sidebar .nav-link span {
            display: inline-block;
        }
        .sidebar.hide .nav-link span {
            display: none;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .sidebar.hide ~ .main-content {
            margin-left: 80px;
        }
        .toggle-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #343a40;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            .main-content {
                margin-left: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i> Attendance
        </div>
        <nav class="nav flex-column">
            <?php if (hasRole('admin')): ?>
                <a class="nav-link" href="<?php echo $base_url; ?>/dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link" href="<?php echo $base_url; ?>/students.php">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
                <a class="nav-link" href="<?php echo $base_url; ?>/faculty.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Faculty</span>
                </a>
                <a class="nav-link" href="<?php echo $base_url; ?>/programs.php">
                    <i class="fas fa-book"></i>
                    <span>Programs</span>
                </a>
                <a class="nav-link" href="<?php echo $base_url; ?>/courses.php">
                    <i class="fas fa-book-reader"></i>
                    <span>Courses</span>
                </a>
                <a class="nav-link" href="<?php echo $base_url; ?>/add_accounts.php">
                    <i class="fas fa-user-plus"></i>
                    <span>Manage Accounts</span>
                </a>
                <a class="nav-link" href="<?php echo $base_url; ?>/semesters.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Semesters</span>
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="attendanceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/mark_attendance.php">
                            <i class="fas fa-qrcode"></i> Mark Attendance
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/attendance_records.php">
                            <i class="fas fa-history"></i> Attendance Records
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/attendance_reports.php">
                            <i class="fas fa-file-alt"></i> Attendance Reports
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/attendance_settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a></li>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (hasRole('faculty')): ?>
                <a class="nav-link" href="<?php echo $base_url; ?>/mark_attendance.php">
                    <i class="fas fa-qrcode"></i>
                    <span>Mark Attendance</span>
                </a>
                <a class="nav-link" href="<?php echo $base_url; ?>/attendance_records.php">
                    <i class="fas fa-history"></i>
                    <span>Attendance Records</span>
                </a>
                <a class="nav-link" href="<?php echo $base_url; ?>/attendance_reports.php">
                    <i class="fas fa-file-alt"></i>
                    <span>Attendance Reports</span>
                </a>
            <?php endif; ?>
            <?php if (hasRole('student')): ?>
                <a class="nav-link" href="<?php echo $base_url; ?>/my_attendance.php">
                    <i class="fas fa-chart-line"></i>
                    <span>My Attendance</span>
                </a>
            <?php endif; ?>
            <a class="nav-link" href="<?php echo $base_url; ?>/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
    <div class="main-content">
        <button class="toggle-btn" id="toggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="container-fluid">
            <div class="page-header mb-4">
                <h2 class="text-white bg-dark p-3 rounded">
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                    <small class="text-muted">(<?php echo ucfirst($_SESSION['role']); ?>)</small>
                </h2>
            </div>