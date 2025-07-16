<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

requireRole('admin');

// Get all faculty
$faculty = getTableData('faculty', '*', null, 'faculty_name ASC');
?>

<div class="container mt-4">
    <h2>Faculty Management</h2>
    <hr>

    <!-- Add Faculty Button -->
    <div class="mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
            Add Faculty
        </button>
    </div>

    <!-- Faculty Table -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Faculty Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($faculty as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['faculty_name']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" 
                            data-id="<?php echo $row['faculty_id']; ?>"
                            data-name="<?php echo htmlspecialchars($row['faculty_name']); ?>">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn"
                            data-id="<?php echo $row['faculty_id']; ?>"
                            data-name="<?php echo htmlspecialchars($row['faculty_name']); ?>">
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Faculty Modal -->
<div class="modal fade" id="addFacultyModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addFacultyForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Faculty</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Faculty Name</label>
          <input type="text" name="faculty_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Faculty Modal -->
<div class="modal fade" id="editFacultyModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editFacultyForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Faculty</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="faculty_id">
        <div class="mb-3">
          <label class="form-label">Faculty Name</label>
          <input type="text" name="faculty_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" type="submit">Update</button>
      </div>
    </form>
  </div>
</div>

<script>
// Add Faculty
document.getElementById('addFacultyForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'add_faculty');

    const resp = await fetch('api/faculty.php', { method: 'POST', body: formData });
    const data = await resp.json();
    alert(data.message);
    if (data.success) location.reload();
});

// Edit Faculty - populate modal
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = new bootstrap.Modal(document.getElementById('editFacultyModal'));
        document.querySelector('#editFacultyForm [name="faculty_id"]').value = btn.dataset.id;
        document.querySelector('#editFacultyForm [name="faculty_name"]').value = btn.dataset.name;
        modal.show();
    });
});

// Edit Faculty - submit
document.getElementById('editFacultyForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'edit_faculty');

    const resp = await fetch('api/faculty.php', { method: 'POST', body: formData });
    const data = await resp.json();
    alert(data.message);
    if (data.success) location.reload();
});

// Delete Faculty
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm(`Delete faculty: ${btn.dataset.name}?`)) return;

        const formData = new FormData();
        formData.append('action', 'delete_faculty');
        formData.append('faculty_id', btn.dataset.id);

        const resp = await fetch('api/faculty.php', { method: 'POST', body: formData });
        const data = await resp.json();
        alert(data.message);
        if (data.success) location.reload();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
