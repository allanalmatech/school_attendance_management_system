document.addEventListener('DOMContentLoaded', function() {
    // Handle form submissions
    const forms = document.querySelectorAll('form[data-ajax]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleFormSubmit(this);
        });
    });

    // Handle delete actions
    const deleteButtons = document.querySelectorAll('[data-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this item?')) {
                handleDelete(this);
            }
        });
    });

    // Handle attendance marking
    const attendanceButtons = document.querySelectorAll('[data-attendance]');
    attendanceButtons.forEach(button => {
        button.addEventListener('click', function() {
            handleAttendance(this);
        });
    });
});

async function handleFormSubmit(form) {
    showLoading();
    const formData = new FormData(form);
    const url = form.getAttribute('action');
    const method = form.getAttribute('method');

    try {
        const response = await fetch(url, {
            method: method,
            body: formData
        });
        const result = await response.json();
        handleResponse(result);
    } catch (error) {
        showError('An error occurred while processing your request');
    } finally {
        hideLoading();
    }
}

function handleDelete(button) {
    const url = button.getAttribute('data-delete');
    showLoading();
    
    fetch(url, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(result => {
        handleResponse(result);
    })
    .catch(error => {
        showError('An error occurred while deleting the item');
    })
    .finally(() => {
        hideLoading();
    });
}

function handleAttendance(button) {
    const url = button.getAttribute('data-attendance');
    const studentId = button.getAttribute('data-student-id');
    const status = button.getAttribute('data-status');
    
    showLoading();
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            student_id: studentId,
            status: status
        })
    })
    .then(response => response.json())
    .then(result => {
        handleResponse(result);
        if (result.success) {
            button.setAttribute('data-status', status === 'present' ? 'absent' : 'present');
            button.classList.toggle('btn-success');
            button.classList.toggle('btn-danger');
        }
    })
    .catch(error => {
        showError('An error occurred while marking attendance');
    })
    .finally(() => {
        hideLoading();
    });
}

function handleResponse(result) {
    if (result.success) {
        showSuccess(result.message);
        if (result.redirect) {
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1000);
        } else {
            // Update table if needed
            const table = document.querySelector('#dataTable');
            if (table) {
                updateTable(table);
            }
        }
    } else {
        showError(result.message);
    }
}

function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.add('show');
    }
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.remove('show');
    }
}

function showSuccess(message) {
    showMessage('success', message);
    
    const toast = document.createElement('div');
    toast.className = `toast fade ${type === 'success' ? 'bg-success' : 'bg-danger'} text-white`;
    toast.role = 'alert';
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="toast-header ${type === 'success' ? 'bg-success' : 'bg-danger'} text-white">
            <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    toastContainer.appendChild(toast);
    document.body.appendChild(toastContainer);
    
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 3000
    });
    
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toastContainer.remove();
    });
}

function updateTable(table) {
    // Get the current page URL
    const url = window.location.pathname;
    
    // Get the current search parameters
    const params = new URLSearchParams(window.location.search);
    
    // Add the current page to the parameters
    params.set('page', currentPage);
    
    // Make the AJAX request
    fetch(url + '?' + params.toString())
        .then(response => response.text())
        .then(html => {
            // Find the table in the response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('#dataTable');
            
            if (newTable) {
                // Replace the old table with the new one
                table.replaceWith(newTable);
                
                // Reinitialize any event listeners
                initializeTableEvents(newTable);
            }
        })
        .catch(error => {
            console.error('Error updating table:', error);
        });
}

function initializeTableEvents(table) {
    // Reinitialize any event listeners for the table
    const deleteButtons = table.querySelectorAll('[data-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this item?')) {
                handleDelete(this);
            }
        });
    });

    const attendanceButtons = table.querySelectorAll('[data-attendance]');
    attendanceButtons.forEach(button => {
        button.addEventListener('click', function() {
            handleAttendance(this);
        });
    });
}

// Add event listener for page navigation
document.addEventListener('click', function(e) {
    if (e.target.matches('.page-link')) {
        e.preventDefault();
        const page = e.target.dataset.page;
        currentPage = page;
        updateTable();
    }
});

// Add event listener for search
document.addEventListener('submit', function(e) {
    if (e.target.id === 'searchForm') {
        e.preventDefault();
        const search = e.target.search.value;
        currentPage = 1;
        updateTable();
    }
});