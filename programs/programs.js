document.addEventListener('DOMContentLoaded', function () {
    const apiUrl = 'programs.php';

    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const action = formData.get('action');

            fetch(apiUrl, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    showMessage(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(() => {
                    showMessage(`Error ${action === 'add' ? 'adding' : 'updating'} program`, 'error');
                });
        });
    });

    document.querySelectorAll('.edit-program').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('program_id').value = this.dataset.id;
            document.getElementById('edit_program_code').value = this.dataset.code;
            document.getElementById('edit_program_name').value = this.dataset.name;
            document.getElementById('editDescription').value = this.dataset.description;
        });
    });

    document.querySelectorAll('.delete-program').forEach(button => {
        button.addEventListener('click', function () {
            if (!confirm('Are you sure you want to delete this program?')) return;

            fetch(apiUrl, {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'delete',
                    program_id: this.dataset.id
                })
            })
                .then(res => res.json())
                .then(data => {
                    showMessage(data.message, data.success ? 'success' : 'error');
                    if (data.success) setTimeout(() => location.reload(), 1000);
                })
                .catch(() => {
                    showMessage('Error deleting program.', 'error');
                });
        });
    });
});

// Message display
function showMessage(message, type = 'success') {
    const box = document.getElementById('messageBox');
    box.textContent = message;
    box.className = `alert alert-${type} mt-3`;
    box.classList.remove('d-none');
    setTimeout(() => box.classList.add('d-none'), 5000);
}
