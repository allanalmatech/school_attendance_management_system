<?php
// Include configuration
require_once __DIR__ . '/includes/config.php';

// Get database connection
$pdo = getDBConnection();

// Check database connection and users table
try {
    $stmt = $pdo->query("SELECT 1");
    $stmt->fetch();
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $table_exists = $stmt->fetch();
    if (!$table_exists) {
        throw new Exception("Users table does not exist!");
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    exit("Database error. Please check the logs.");
}

// Default accounts data
$default_accounts = [
    [
        'username' => 'admin',
        'email' => 'admin@school.edu',
        'role' => 'admin',
        'password' => 'admin123', // Will be hashed before storing
    ],
    [
        'username' => 'faculty1',
        'email' => 'faculty1@school.edu',
        'role' => 'faculty',
        'password' => 'faculty123',
    ],
    [
        'username' => 'faculty2',
        'email' => 'faculty2@school.edu',
        'role' => 'faculty',
        'password' => 'faculty123',
    ],
    [
        'username' => 'student1',
        'email' => 'student1@school.edu',
        'role' => 'student',
        'password' => 'student123',
    ],
    [
        'username' => 'student2',
        'email' => 'student2@school.edu',
        'role' => 'student',
        'password' => 'student123',
    ],
];

// Handle account creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_accounts'])) {
    try {
        $pdo->beginTransaction();
        
        // Read SQL file
        $sql_file = __DIR__ . '/../create_accounts.sql';
        if (!file_exists($sql_file)) {
            throw new Exception("SQL file not found: " . $sql_file);
        }
        
        $sql = file_get_contents($sql_file);

        
        // Execute SQL
        $pdo->exec($sql);
        
        $pdo->commit();
        $message = 'Default accounts created successfully!';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Error creating accounts: ' . $e->getMessage();
        error_log($error);
        
        error_log("Error creating accounts: " . $e->getMessage());
    }
}

// Fetch existing accounts
$existing_accounts = [];
try {
    $sql = "SELECT * FROM users ORDER BY role, username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $existing_accounts = $stmt->fetchAll();
    

} catch (PDOException $e) {
    $error = 'Error fetching existing accounts: ' . $e->getMessage();
    error_log($error);
}

// Include header
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Manage Default Accounts</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($message)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Default Accounts</label>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Password</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($default_accounts as $account): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($account['username']); ?></td>
                                                <td><?php echo htmlspecialchars($account['email']); ?></td>
                                                <td><?php echo htmlspecialchars($account['role']); ?></td>
                                                <td><?php echo htmlspecialchars($account['password']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" name="create_accounts" class="btn btn-primary">Create Default Accounts</button>
                        </div>
                    </form>

                    <h4 class="mt-4">Existing Accounts</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($existing_accounts as $account): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($account['username']); ?></td>
                                        <td><?php echo htmlspecialchars($account['email']); ?></td>
                                        <td><?php echo htmlspecialchars($account['role']); ?></td>
                                        <td><?php echo htmlspecialchars($account['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
