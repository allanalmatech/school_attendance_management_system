<?php
// Include configuration
require_once __DIR__ . '/includes/config.php';

// Get database connection
$pdo = getDBConnection();

// Read and execute the schema file
try {
    // Read the schema file
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // Split the SQL into individual statements
    $statements = explode(';', $sql);
    
    // Execute each statement
    foreach ($statements as $statement) {
        $trimmed = trim($statement);
        if ($trimmed !== '') {
            try {
                // Skip empty statements and comments
                if (strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0) {
                    continue;
                }
                $pdo->exec($trimmed);
            } catch (PDOException $e) {
                // Skip DELIMITER statements and other non-executable lines
                if (strpos($e->getMessage(), 'DELIMITER') === false) {
                    throw $e;
                }
            }
        }
    }
    
    echo "<div class='alert alert-success'>Database and tables created successfully!</div>";
    
    // Create default admin account
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@example.com', $password, 'admin']);
    
    echo "<div class='alert alert-success'>Default admin account created!</div>";
    
    // Add some default data
    // Add a default program
    $stmt = $pdo->prepare("INSERT INTO programs (program_code, program_name) VALUES (?, ?)");
    $stmt->execute(['CS', 'Computer Science']);
    
    // Add a default semester
    $stmt = $pdo->prepare("INSERT INTO semesters (semester_name, academic_year, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Fall 2023', '2023-2024', '2023-09-01', '2023-12-31']);
    
    echo "<div class='alert alert-success'>Default data added!</div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

// Close the database connection
$pdo = null;
