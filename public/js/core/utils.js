// Utility functions

// Show alert
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);

    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Handle API errors and extract messages
function handleApiError(error) {
    if (error.response && error.response.data) {
        const data = error.response.data;

        // Check if it's your custom error format
        if (data.status === 'failed' && data.errors && Array.isArray(data.errors)) {
            const messages = data.errors.map(err => err.message).join('<br>');
            return messages;
        }

        // Fallback to other common error formats
        if (data.message) {
            return data.message;
        }

        if (data.error) {
            return data.error;
        }
    }

    // Default fallback
    return error.message || 'An unexpected error occurred';
}

// Status badge helper
function getStatusBadge(status) {
    const badges = {
        'pending': 'badge bg-warning',
        'in_progress': 'badge bg-info',
        'completed': 'badge bg-success'
    };
    return `<span class="${badges[status] || 'badge bg-secondary'}">${status.replace('_', ' ')}</span>`;
}

// Priority badge helper
function getPriorityBadge(priority) {
    const badges = {
        'low': 'badge bg-success',
        'medium': 'badge bg-warning',
        'high': 'badge bg-danger'
    };
    return `<span class="${badges[priority] || 'badge bg-secondary'}">${priority}</span>`;
}

// Load users function
async function loadUsers() {
    try {
        // Assuming you have a users endpoint, if not, we'll extract from tasks
        const response = await axios.get('/users');
        if (response.data.status === 'success') {
            const users = response.data.data?.results || [];
            const userIds = new Set();

            users.forEach(user => {
                userIds.add(user.id);                
            });

            // Add current user if not in list
            if (users.length == 0 && currentUser.id && !userIds.has(currentUser.id)) {
                users.push(currentUser);
            }

            allUsers = users;
            populateTaskDropdowns();
        }
    } catch (error) {
        console.log('Could not load users from tasks:', error);
        allUsers = [currentUser]; // Fallback to current user
        populateTaskDropdowns();
    }
}

async function loadTags() {
    try {
        const response = await axios.get('/tags');
        if (response.data.status === 'success') {
            allTags = response.data.data?.results || [];
            //populateTaskDropdowns();
        } else {
            showAlert('Failed to load tags', 'danger');
        }
    } catch (error) {
        console.log('Error loading tags:', error);
        showAlert('Error loading tags: ' + (error.response?.data?.message || error.message), 'danger');
    }
}

function clearInlineErrors() {
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

// Show inline error for a specific field
function showInlineError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + 'Error');

    if (field && errorDiv) {
        field.classList.add('is-invalid');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

// Handle API errors and display inline validation errors
function handleInlineApiError(error, formType = 'task') {
    clearInlineErrors();

    if (error.response && error.response.data) {
        const data = error.response.data;

        // Check if it's validation errors format
        if (data.status === 'failed' && data.errors && Array.isArray(data.errors)) {
            let hasInlineErrors = false;

            data.errors.forEach(err => {
                if (err.field) {
                    // Map API field names to form field IDs
                    const fieldMapping = {
                        // Task fields
                        'title': 'taskTitle',
                        'description': 'taskDescription',
                        'status': 'taskStatus',
                        'priority': 'taskPriority',
                        'due_date': 'taskDueDate',
                        'assigned_to': 'taskAssignedTo',
                        'tags': 'taskTags',
                        'metadata': 'taskMetadata',
                        // Tag fields
                        'name': 'tagName',
                        'color': 'tagColor'
                    };

                    const fieldId = fieldMapping[err.field];
                    if (fieldId) {
                        showInlineError(fieldId, err.message);
                        hasInlineErrors = true;
                    }
                }
            });

            // If we showed inline errors, return a general message
            if (hasInlineErrors) {
                return 'Please fix the errors below';
            }

            // If no field-specific errors, show all messages
            return data.errors.map(err => err.message).join('<br>');
        }

        // Fallback to regular error handling
        return handleApiError(error);
    }

    return error.message || 'An unexpected error occurred';
}

window.addEventListener('unhandledrejection', function (event) {
    console.log('Unhandled promise rejection:', event.reason);
    // Prevent the default browser behavior (logging to console)
    event.preventDefault();
});

window.addEventListener('error', function (event) {
    console.log('Global error:', event.error);
});

function showModalAlert(modalId, message, type = 'info') {
    const alertContainer = document.getElementById(modalId + 'Alert');
    if (!alertContainer) return;
    
    alertContainer.className = `alert alert-${type} alert-dismissible fade show mb-3`;
    alertContainer.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.style.display = 'block';
    
    // Auto-hide success messages after 3 seconds
    if (type === 'success') {
        setTimeout(() => {
            hideModalAlert(modalId);
        }, 3000);
    }
}

// Hide modal alert
function hideModalAlert(modalId) {
    const alertContainer = document.getElementById(modalId + 'Alert');
    if (alertContainer) {
        alertContainer.style.display = 'none';
        alertContainer.innerHTML = '';
    }
}

// Clear modal alert (alias for hideModalAlert)
function clearModalAlert(modalId) {
    hideModalAlert(modalId);
}