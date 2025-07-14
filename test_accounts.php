<?php
// Include configuration
require_once __DIR__ . '/includes/config.php';

// Get database connection
$pdo = getDBConnection();

// Password hashing
$password = password_hash('admin123', PASSWORD_DEFAULT);

// Prepare and execute the insert statement
try {
    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute(['admin', 'admin@example.com', $password, 'admin']);
    
    if ($result) {
        echo "<div class='alert alert-success'>Admin account created successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to create admin account</div>";
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Duplicate entry error
        echo "<div class='alert alert-warning'>Admin account already exists!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error creating admin account: " . $e->getMessage() . "</div>";
    }
}

// Close the database connection
$pdo = null;
