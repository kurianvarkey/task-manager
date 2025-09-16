// Tags pagination variables
let currentTagsPage = 1;
let tagsPerPage = 10;
let tagsMeta = {};

// Tags functions
async function loadTags(page = 1) {
    try {
        const params = new URLSearchParams();
        params.append('page', page);
        params.append('limit', tagsPerPage);
        
        const response = await axios.get(`/tags?${params.toString()}`);
        if (response.data.status === 'success') {
            const data = response.data.data;
            allTags = data.results;
            tagsMeta = data.meta;
            currentTagsPage = page;
            
            displayTags(allTags);
            displayTagsPagination();
        } else {
            showAlert('Failed to load tags', 'danger');
        }
    } catch (error) {
        const errorMessage = handleApiError(error);
        showAlert('Error loading tags: ' + errorMessage, 'danger');
    }
}

function displayTags(tags) {
    const container = document.getElementById('tagsContainer');
    
    if (tags.length === 0) {
        container.innerHTML = '<p class="text-muted">No tags found.</p>';
        return;
    }

    let html = '<div class="row">';
    tags.forEach(tag => {
        html += `
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge" style="background-color: ${tag.color}">${tag.name}</span> 
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" onclick="editTag(${tag.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteTag(${tag.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function displayTagsPagination() {
    const container = document.getElementById('tagsPagination');
    if (!container || !tagsMeta) return;
    
    const { current_page, last_page, from, to, total } = tagsMeta;
    
    let html = '<div class="d-flex justify-content-between align-items-center w-100">';
    
    // Info text
    html += `<div class="text-muted">Showing ${from || 0} to ${to || 0} of ${total || 0} entries</div>`;
    
    // Pagination buttons
    if (last_page > 1) {
        html += '<nav><ul class="pagination pagination-sm mb-0">';
        
        // Previous button
        html += `<li class="page-item ${current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadTags(${current_page - 1}); return false;">Previous</a>
        </li>`;
        
        // Page numbers
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);
        
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTags(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadTags(${i}); return false;">${i}</a>
            </li>`;
        }
        
        if (endPage < last_page) {
            if (endPage < last_page - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTags(${last_page}); return false;">${last_page}</a></li>`;
        }
        
        // Next button
        html += `<li class="page-item ${current_page === last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadTags(${current_page + 1}); return false;">Next</a>
        </li>`;
        
        html += '</ul></nav>';
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function changeTagsPerPage() {
    tagsPerPage = parseInt(document.getElementById('tagsPerPage').value);
    loadTags(1); // Reset to first page
}

function showTagModal(tagId = null) {
    const modal = new bootstrap.Modal(document.getElementById('tagModal'));
    const form = document.getElementById('tagForm');
    
    // Clear any previous errors and alerts
    clearInlineErrors();
    clearModalAlert('tagModal');
    
    // Reset form
    form.reset();
    document.getElementById('tagId').value = '';
    document.getElementById('tagModalTitle').textContent = tagId ? 'Edit Tag' : 'Add Tag';
    document.getElementById('tagColor').value = '#007bff';
    
    if (tagId) {
        loadTagForEdit(tagId);
    }
    
    modal.show();
}

async function loadTagForEdit(tagId) {
    try {
        const response = await axios.get(`/tags/${tagId}`);
        if (response.data.status === 'success') {
            const tag = response.data.data;
            document.getElementById('tagId').value = tag.id;
            document.getElementById('tagName').value = tag.name;
            document.getElementById('tagColor').value = tag.color || '#007bff';
        }
    } catch (error) {
        showAlert('Error loading tag details', 'danger');
    }
}

// Tag form handler
document.addEventListener('DOMContentLoaded', function() {
    const tagForm = document.getElementById('tagForm');
    if (tagForm) {
        tagForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const tagId = document.getElementById('tagId').value;
            const isEdit = tagId !== '';
            
            const formData = {
                name: document.getElementById('tagName').value,
                color: document.getElementById('tagColor').value
            };

            document.getElementById('tagModalSave').disabled = true;

            try {
                let response;
                if (isEdit) {
                    response = await axios.put(`/tags/${tagId}`, formData);
                } else {
                    response = await axios.post('/tags', formData);
                }
                
                if (response.data.success || response.data.status === 'success') {
                    showModalAlert('tagModal', isEdit ? 'Tag updated successfully!' : 'Tag created successfully!', 'success');
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('tagModal')).hide();
                        loadTags(currentTagsPage);
                    }, 500);
                } else {
                    showModalAlert('tagModal', response.data.message || 'Operation failed', 'danger');
                }
                
                document.getElementById('tagModalSave').disabled = false;
            } catch (error) {
                document.getElementById('tagModalSave').disabled = false;
                const errorMessage = handleInlineApiError(error, 'tag');
                showModalAlert('tagModal', 'Error: ' + errorMessage, 'danger');
            }
        });
    }
});

function editTag(tagId) {
    showTagModal(tagId);
}

async function deleteTag(tagId) {
    if (!confirm('Are you sure you want to delete this tag?')) return;
    
    try {
        const response = await axios.delete(`/tags/${tagId}`);
        if (response.status === 204) { // No content
            showAlert('Tag deleted successfully!', 'success');
            loadTags(currentTagsPage);
        } else {
            showAlert('Failed to delete tag', 'danger');
        }
    } catch (error) {
        const errorMessage = handleApiError(error);
        showAlert('Error deleting tag: ' + errorMessage, 'danger');
    }
}