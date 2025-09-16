// Tasks pagination variables
let currentTasksPage = 1;
let tasksPerPage = 10;
let tasksMeta = {};

// Tasks functions
async function loadTasks(page = 1) {
    try {
        const params = new URLSearchParams();

        // Add pagination
        params.append('page', page);
        params.append('limit', tasksPerPage);

        // Add filters
        const status = document.getElementById('filterStatus')?.value;
        const priority = document.getElementById('filterPriority')?.value;
        const assignedTo = document.getElementById('filterAssignedTo')?.value;
        const fromDate = document.getElementById('filterFromDate')?.value;
        const toDate = document.getElementById('filterToDate')?.value;
        const keyword = document.getElementById('filterKeyword')?.value;
        const deleted = document.getElementById('filterDeleted')?.checked;

        if (status) params.append('status', status);
        if (priority) params.append('priority', priority);
        if (assignedTo) params.append('assigned_to', assignedTo);

        // Handle date range filter
        if (fromDate || toDate) {
            const dateRange = `${fromDate || ''},${toDate || ''}`;
            params.append('due_date_range', dateRange);
        }

        if (keyword) params.append('keyword', keyword);
        if (deleted) params.append('only_deleted', '1');

        // Add selected tags
        const filterTags = $('#filterTags');
        if (filterTags.length) {
            const selectedTags = filterTags.selectpicker('val');
            if (selectedTags && selectedTags.length > 0) {
                params.append('tags', selectedTags.join(','));
            }
        }

        const response = await axios.get(`/tasks?${params.toString()}`);

        if (response.data.success || response.data.status === 'success') {
            const data = response.data.data;

            // Handle both paginated and non-paginated responses
            if (data.results) {
                // Paginated response
                tasksMeta = data.meta;
                currentTasksPage = page;
                displayTasks(data.results);
                displayTasksPagination();
            } else {
                // Non-paginated response (fallback)
                displayTasks(data);
            }
        } else {
            showAlert('Failed to load tasks', 'danger');
        }
    } catch (error) {
        const errorMessage = handleApiError(error);
        showAlert('Error loading tasks: ' + errorMessage, 'danger');
    }
}

function displayTasks(tasks) {
    const container = document.getElementById('tasksContainer');

    if (tasks.length === 0) {
        container.innerHTML = '<p class="text-muted">No tasks found.</p>';
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr>';
    html += '<th>Title</th><th>Status</th><th>Priority</th><th>Due Date</th><th>Assigned To</th><th>Tags</th><th class="text-end">Actions</th>';
    html += '</tr></thead><tbody>';

    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    };

    tasks.forEach(task => {
        let dueDate = task?.due_date || '';
        dueDate = dueDate ? new Intl.DateTimeFormat('en-GB', options).format(new Date(dueDate)) : '-';

        const statusBadge = getStatusBadge(task.status);
        const priorityBadge = getPriorityBadge(task.priority);
        const tags = task.tags ? task.tags.map(tag => `<span class="badge" style="background-color: ${tag.color}">${tag.name}</span>`).join(' ') : '';
        const isDeleted = (task.deleted_at || null) !== null;

        html += `<tr class="${isDeleted ? 'table-secondary' : ''}">`;
        html += `<td>${task.title} ${isDeleted ? '<small class="text-muted">(Deleted)</small>' : ''}</td>`;
        html += `<td>${statusBadge}</td>`;
        html += `<td>${priorityBadge}</td>`;
        html += `<td>${dueDate}</td>`;
        html += `<td>${task.assigned_to ? task.assigned_to.name : '-'}</td>`;
        html += `<td>${tags}</td>`;
        html += `<td class="d-flex justify-content-end">`;

        if (isDeleted) {
            html += `<button class="btn btn-success btn-sm me-1" onclick="restoreTask(${task.id})" title="Restore">
                <i class="fas fa-undo"></i>
            </button>`;
        } else {
            html += `<button class="btn btn-info btn-sm me-1" onclick="toggleTaskStatus(${task.id})" title="Toggle Status">
                <i class="fas fa-toggle-on"></i>
            </button>`;            
            html += `<button class="btn btn-warning btn-sm me-1" onclick="editTask(${task.id})" title="Edit">
                <i class="fas fa-edit"></i>
            </button>`;
            html += `<button class="btn btn-secondary btn-sm me-1" onclick="showTaskLogs(${task.id})" title="View Logs">
                <i class="fas fa-history"></i>
            </button>`;
            html += `<button class="btn btn-danger btn-sm" onclick="deleteTask(${task.id})" title="Delete">
                <i class="fas fa-trash"></i>
            </button>`;
        }

        html += `</td></tr>`;
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function displayTasksPagination() {
    const container = document.getElementById('tasksPagination');
    if (!container || !tasksMeta) return;

    const { current_page, last_page, from, to, total } = tasksMeta;

    let html = '<div class="d-flex justify-content-between align-items-center w-100">';

    // Info text
    html += `<div class="text-muted">Showing ${from || 0} to ${to || 0} of ${total || 0} entries</div>`;

    // Pagination buttons
    if (last_page > 1) {
        html += '<nav><ul class="pagination pagination-sm mb-0">';

        // Previous button
        html += `<li class="page-item ${current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadTasks(${current_page - 1}); return false;">Previous</a>
        </li>`;

        // Page numbers
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);

        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTasks(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadTasks(${i}); return false;">${i}</a>
            </li>`;
        }

        if (endPage < last_page) {
            if (endPage < last_page - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTasks(${last_page}); return false;">${last_page}</a></li>`;
        }

        // Next button
        html += `<li class="page-item ${current_page === last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadTasks(${current_page + 1}); return false;">Next</a>
        </li>`;

        html += '</ul></nav>';
    }

    html += '</div>';
    container.innerHTML = html;
}

function changeTasksPerPage() {
    tasksPerPage = parseInt(document.getElementById('tasksPerPage').value);
    loadTasks(1); // Reset to first page
}

function clearFilters() {
    const filterStatus = document.getElementById('filterStatus');
    const filterPriority = document.getElementById('filterPriority');
    const filterAssignedTo = document.getElementById('filterAssignedTo');
    const filterFromDate = document.getElementById('filterFromDate');
    const filterToDate = document.getElementById('filterToDate');
    const filterKeyword = document.getElementById('filterKeyword');
    const filterDeleted = document.getElementById('filterDeleted');
    const filterTags = $('#filterTags');

    if (filterStatus) filterStatus.value = '';
    if (filterPriority) filterPriority.value = '';
    if (filterAssignedTo) filterAssignedTo.value = '';
    if (filterFromDate) filterFromDate.value = '';
    if (filterToDate) filterToDate.value = '';
    if (filterKeyword) filterKeyword.value = '';
    if (filterDeleted) filterDeleted.checked = false;
    if (filterTags.length) {
        filterTags.selectpicker('deselectAll');
        filterTags.selectpicker('refresh');
    }

    loadTasks(1);
}

function showTaskModal(taskId = null) {
    const modal = new bootstrap.Modal(document.getElementById('taskModal'));
    const form = document.getElementById('taskForm');

    // Clear any previous errors and alerts
    clearInlineErrors();
    clearModalAlert('taskModal');

    // Reset form
    form.reset();
    document.getElementById('taskId').value = '';
    document.getElementById('taskModalTitle').textContent = taskId ? 'Edit Task' : 'Add Task';

    // Populate dropdowns
    populateTaskForms();

    if (taskId) {
        loadTaskForEdit(taskId);
    }

    modal.show();
}

async function populateTaskForms() {
    // Populate assigned to dropdown
    const assignedToSelect = document.getElementById('taskAssignedTo');
    assignedToSelect.innerHTML = '<option value="">Select User</option>';
    allUsers.forEach(user => {
        assignedToSelect.innerHTML += `<option value="${user.id}">${user.name}</option>`;
    });

    // Populate tags dropdown for task modal
    const tagsSelect = $('#taskTags');
    tagsSelect.empty();
    allTags.forEach(tag => {
        tagsSelect.append(`<option value="${tag.id}">${tag.name}</option>`);
    });

    if (tagsSelect.hasClass('selectpicker')) {
        tagsSelect.selectpicker('destroy');
    }
    tagsSelect.selectpicker();
}

async function populateTaskDropdowns() {
    // Populate assigned to dropdown
    const assignedToSelect = document.getElementById('taskAssignedTo');
    assignedToSelect.innerHTML = '<option value="">Select User</option>';
    allUsers.forEach(user => {
        assignedToSelect.innerHTML += `<option value="${user.id}">${user.name}</option>`;
    });

    // Populate tags dropdown for task modal
    const tagsSelect = $('#filterTags');
    tagsSelect.empty();
    allTags.forEach(tag => {
        tagsSelect.append(`<option value="${tag.id}">${tag.name}</option>`);
    });

    // Initialize or refresh selectpicker for task modal
    if (tagsSelect.hasClass('selectpicker')) {
        tagsSelect.selectpicker('refresh');
    } else {
        tagsSelect.selectpicker();
    }

    // Also populate filter dropdowns
    const filterAssignedTo = document.getElementById('filterAssignedTo');
    if (filterAssignedTo.children.length <= 1) {
        allUsers.forEach(user => {
            filterAssignedTo.innerHTML += `<option value="${user.id}">${user.name}</option>`;
        });
    }

    // Populate filter tags dropdown
    /*  const filterTags = $('#filterTags');
     if (filterTags.children().length === 0) {
         allTags.forEach(tag => {
             filterTags.append(`<option value="${tag.id}">${tag.name}</option>`);
         });
         
         // Initialize selectpicker for filter without automatic change event
         filterTags.selectpicker();
     } */
}

async function loadTaskForEdit(taskId) {
    try {
        const response = await axios.get(`/tasks/${taskId}`);
        if (response.data.status === 'success') {
            const task = response.data.data;
            document.getElementById('taskId').value = task.id;
            document.getElementById('taskTitle').value = task.title;
            document.getElementById('taskDescription').value = task.description || '';
            document.getElementById('taskStatus').value = task.status;
            document.getElementById('taskPriority').value = task.priority;
            document.getElementById('taskDueDate').value = task.due_date || '';
            document.getElementById('taskAssignedTo').value = task?.assigned_to?.id || '';
            document.getElementById('version').value = task?.version || '';

            // Set metadata field
            const metadataField = document.getElementById('taskMetadata');
            if (task.metadata && typeof task.metadata === 'object') {
                metadataField.value = JSON.stringify(task.metadata, null, 2);
            } else {
                metadataField.value = '';
            }

            // Set selected tags
            const tagIds = task.tags ? task.tags.map(tag => tag.id.toString()) : [];
            const taskTagsSelect = $('#taskTags');
            taskTagsSelect.selectpicker('deselectAll');
            if (tagIds.length > 0) {
                taskTagsSelect.selectpicker('val', tagIds);
            }
            //taskTagsSelect.selectpicker('refresh');
        }
    } catch (error) {
        showAlert('Error loading task details', 'danger');
    }
}

// Task form handler
document.addEventListener('DOMContentLoaded', function () {
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const taskId = document.getElementById('taskId').value;
            const isEdit = taskId !== '';

            // Handle metadata JSON validation
            let metadata = null;
            const metadataValue = document.getElementById('taskMetadata').value.trim();
            if (metadataValue) {
                try {
                    metadata = JSON.parse(metadataValue);
                } catch (e) {
                    showInlineError('taskMetadata', 'Invalid JSON format');
                    showModalAlert('taskModal', 'Please fix the metadata JSON format', 'danger');
                    return;
                }
            }

            const formData = {
                title: document.getElementById('taskTitle').value,
                description: document.getElementById('taskDescription').value,
                status: document.getElementById('taskStatus').value,
                priority: document.getElementById('taskPriority').value,
                due_date: document.getElementById('taskDueDate').value || null,
                assigned_to: document.getElementById('taskAssignedTo').value ? { id: parseInt(document.getElementById('taskAssignedTo').value, 10) } : null,
                metadata: metadata,
                tags: $('#taskTags').selectpicker('val').map(id => ({ id: parseInt(id, 10) })) || []
            };

            document.getElementById('taskModalSave').disabled = true;

            try {
                let response;
                if (isEdit) {
                    formData.version = document.getElementById('version').value || null;
                    response = await axios.put(`/tasks/${taskId}`, formData);
                } else {
                    response = await axios.post('/tasks', formData);
                }

                if (response.data.success || response.data.status === 'success') {
                    showModalAlert('taskModal', isEdit ? 'Task updated successfully!' : 'Task created successfully!', 'success');
                    setTimeout(() => {
                        const tagsSelect = $('#taskTags');
                        tagsSelect.selectpicker('destroy');

                        bootstrap.Modal.getInstance(document.getElementById('taskModal')).hide();
                        loadTasks(currentTasksPage);
                    }, 500);
                } else {
                    showModalAlert('taskModal', response.data.message || 'Operation failed', 'danger');
                }
                document.getElementById('taskModalSave').disabled = false;
            } catch (error) {
                document.getElementById('taskModalSave').disabled = false;
                const errorMessage = handleInlineApiError(error, 'task');
                showModalAlert('taskModal', 'Error: ' + errorMessage, 'danger');
            }
        });
    }
});

function editTask(taskId) {
    showTaskModal(taskId);
}

async function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) return;

    try {
        const response = await axios.delete(`/tasks/${taskId}`);
        if (response.status === 204) { // No content
            showAlert('Task deleted successfully!', 'success');
            loadTasks(currentTasksPage);
        } else {
            showAlert('Failed to delete task', 'danger');
        }
    } catch (error) {
        const errorMessage = handleApiError(error);
        showAlert('Error deleting task: ' + errorMessage, 'danger');
    }
}

async function toggleTaskStatus(taskId) {
    try {
        const response = await axios.patch(`/tasks/${taskId}/toggle-status`);
        if (response.data.success || response.data.status === 'success') {
            showAlert('Task status updated!', 'success');
            loadTasks(currentTasksPage);
        } else {
            showAlert('Failed to update task status', 'danger');
        }
    } catch (error) {
        const errorMessage = handleApiError(error);
        showAlert('Error updating task status: ' + errorMessage, 'danger');
    }
}

async function restoreTask(taskId) {
    try {
        const response = await axios.patch(`/tasks/${taskId}/restore`);
        if (response.data.success || response.data.status === 'success') {
            showAlert('Task restored successfully!', 'success');
            loadTasks(currentTasksPage);
        } else {
            showAlert('Failed to restore task', 'danger');
        }
    } catch (error) {
        const errorMessage = handleApiError(error);
        showAlert('Error restoring task: ' + errorMessage, 'danger');
    }
}

let currentLogsPage = 1;
let logsPerPage = 25;
let currentTaskId = null;

async function showTaskLogs(taskId) {
    currentTaskId = taskId;
    currentLogsPage = 1;

    const modal = new bootstrap.Modal(document.getElementById('taskLogsModal'));
    document.getElementById('taskLogsModalTitle').textContent = `Task Logs - ID: ${taskId}`;

    // Show loading state
    document.getElementById('logsLoading').style.display = 'block';
    document.getElementById('logsContent').style.display = 'none';
    document.getElementById('noLogsMessage').style.display = 'none';

    modal.show();

    // Load logs
    await loadTaskLogs(taskId, 1);
}

async function loadTaskLogs(taskId, page = 1) {
    try {
        const params = new URLSearchParams();
        params.append('page', page);
        params.append('limit', logsPerPage);

        const response = await axios.get(`/tasks/${taskId}/logs?${params.toString()}`);

        if (response.data.status === 'success') {
            const data = response.data.data;
            const logs = data.results || [];
            const meta = data.meta || {};

            // Hide loading
            document.getElementById('logsLoading').style.display = 'none';

            if (logs.length === 0) {
                document.getElementById('noLogsMessage').style.display = 'block';
                document.getElementById('logsContent').style.display = 'none';
            } else {
                document.getElementById('noLogsMessage').style.display = 'none';
                document.getElementById('logsContent').style.display = 'block';

                displayTaskLogs(logs);
                displayLogsPagination(meta);
            }
        } else {
            throw new Error('Failed to load logs');
        }
    } catch (error) {
        document.getElementById('logsLoading').style.display = 'none';
        document.getElementById('noLogsMessage').innerHTML = '<p class="text-danger">Error loading logs: ' + (error.response?.data?.message || error.message) + '</p>';
        document.getElementById('noLogsMessage').style.display = 'block';
        document.getElementById('logsContent').style.display = 'none';
    }
}

function displayTaskLogs(logs) {
    const tbody = document.getElementById('logsTableBody');
    let html = '';

    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false // Use 24-hour clock
    };

    logs.forEach(log => {
        const date = new Intl.DateTimeFormat('en-GB', options).format(new Date(log.created_at || Date.now()))
        const operation = formatOperationType(log.operation_type);
        const user = log.created_by ? log.created_by.name : 'System';
        const changes = formatLogChanges(log.changes, log.operation_type);

        html += `
            <tr>
                <td>${date}</td>
                <td><span class="badge ${getOperationBadgeClass(log.operation_type)}">${operation}</span></td>
                <td>${user}</td>
                <td>${changes}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function formatOperationType(type) {
    const types = {
        'created': 'Created',
        'updated': 'Updated',
        'deleted': 'Deleted',
        'restored': 'Restored',
        'status_changed': 'Status Changed',
        'assigned': 'Assigned',
        'unassigned': 'Unassigned'
    };
    return types[type] || type.charAt(0).toUpperCase() + type.slice(1);
}

function getOperationBadgeClass(type) {
    const classes = {
        'created': 'bg-success',
        'updated': 'bg-info',
        'deleted': 'bg-danger',
        'restored': 'bg-warning',
        'status_changed': 'bg-primary',
        'assigned': 'bg-secondary',
        'unassigned': 'bg-light text-dark'
    };
    return classes[type] || 'bg-secondary';
}

function formatLogChanges(changes, operationType) {
    if (!changes || typeof changes !== 'object') {
        return '-';
    }

    let html = '<div class="small">';

    // For created operation, show key fields
    if (operationType === 'created') {
        const keyFields = ['title', 'status', 'priority', 'due_date', 'assigned_to'];
        keyFields.forEach(field => {
            if (changes[field] !== undefined && changes[field] !== null) {
                let value = changes[field];
                if (field === 'assigned_to' && typeof value === 'object') {
                    value = value.name || value.id;
                }
                html += `<div><strong>${field.replace('_', ' ')}:</strong> ${value}</div>`;
            }
        });
    } else {
        // For other operations, show all changes
        Object.keys(changes).forEach(key => {
            if (key !== 'updated_at' && key !== 'created_at' && key !== 'id') {
                let value = changes[key];
                if (typeof value === 'object' && value !== null) {
                    if (value.name) value = value.name;
                    else if (value.id) value = `ID: ${value.id}`;
                    else value = JSON.stringify(value);
                }
                html += `<div><strong>${key.replace('_', ' ')}:</strong> ${value || 'null'}</div>`;
            }
        });
    }

    html += '</div>';
    return html;
}

function displayLogsPagination(meta) {
    const container = document.getElementById('logsPagination');
    if (!container || !meta) return;

    const { current_page, last_page, from, to, total } = meta;

    let html = '<div class="d-flex justify-content-between align-items-center w-100">';

    // Info text
    html += `<div class="text-muted">Showing ${from || 0} to ${to || 0} of ${total || 0} log entries</div>`;

    // Pagination buttons
    if (last_page > 1) {
        html += '<nav><ul class="pagination pagination-sm mb-0">';

        // Previous button
        html += `<li class="page-item ${current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadTaskLogs(${currentTaskId}, ${current_page - 1}); return false;">Previous</a>
        </li>`;

        // Page numbers
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);

        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTaskLogs(${currentTaskId}, 1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadTaskLogs(${currentTaskId}, ${i}); return false;">${i}</a>
            </li>`;
        }

        if (endPage < last_page) {
            if (endPage < last_page - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTaskLogs(${currentTaskId}, ${last_page}); return false;">${last_page}</a></li>`;
        }

        // Next button
        html += `<li class="page-item ${current_page === last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadTaskLogs(${currentTaskId}, ${current_page + 1}); return false;">Next</a>
        </li>`;

        html += '</ul></nav>';
    }

    html += '</div>';
    container.innerHTML = html;
}