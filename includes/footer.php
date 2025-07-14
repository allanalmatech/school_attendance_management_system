 
<?php
?>
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">Student Attendance Management System © 2025</span>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted">Version 1.0</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Modal for messages -->


    <style>
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .loading-overlay.show {
            display: flex;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .footer .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $_SERVER['DOCUMENT_ROOT']; ?>/school_attendance_management_system/assets/js/app.js"></script>
</body>
</html>