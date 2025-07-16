<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

requireRole('admin');

// Search
$search = $_GET['search'] ?? '';
$where = $search ? "program_name LIKE '%$search%' OR description LIKE '%$search%'" : null;

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$total = count(getTableData('programs', '*', $where));
$totalPages = ceil($total / $limit);

$programs = getTableData('programs', '*', $where, "program_name ASC LIMIT $offset, $limit");
?>

<div class="container mt-4">
    <h2>Academic Programs</h2>
    <hr>

    <!-- Search -->
    <form class="d-flex mb-3" method="GET">
        <input class="form-control me-2" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search programs...">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>

    <!-- Message Display -->
    <div id="messageBox" class="alert d-none" role="alert"></div>

    <!-- Add Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProgramModal">Add Program</button>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programs as $row): ?>
                <tr>
                    <td><?= $row['program_id']; ?></td>
                    <td><?= htmlspecialchars($row['program_code'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['program_name']); ?></td>
                    <td><?= htmlspecialchars($row['description']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-program"
                            data-id="<?= $row['program_id']; ?>"
                            data-code="<?= htmlspecialchars($row['program_code']); ?>"
                            data-name="<?= htmlspecialchars($row['program_name']); ?>"
                            data-description="<?= htmlspecialchars($row['description']); ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#editProgramModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-program" data-id="<?= $row['program_id']; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center mt-4">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search); ?>">Previous</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search); ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search); ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="program_logic.php" data-ajax="true">
                <div class="modal-header">
                    <h5 class="modal-title">Add Program</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Program Code</label>
                        <input class="form-control" name="program_code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Program Name</label>
                        <input class="form-control" name="program_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="program_logic.php" data-ajax="true">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Program</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="program_id" id="program_id">
                    <div class="mb-3">
                        <label class="form-label">Program Code</label>
                        <input class="form-control" name="program_code" id="edit_program_code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Program Name</label>
                        <input class="form-control" name="program_name" id="edit_program_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editDescription"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Load external JS -->
<script src="programs.js"></script>

<?php require_once '../includes/footer.php'; ?>
