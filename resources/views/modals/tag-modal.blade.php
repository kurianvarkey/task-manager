<!-- Tag Modal -->
<div class="modal fade" id="tagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tagModalTitle">Add Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="tagForm">
                <div class="modal-body">
                    <!-- Inline Alert Container -->
                    <div id="tagModalAlert" class="alert-container mb-3" style="display: none;"></div>
                    
                    <input type="hidden" id="tagId">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="tagName" required>
                        <div class="invalid-feedback" id="tagNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control" id="tagColor" value="#007bff">
                        <div class="invalid-feedback" id="tagColorError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="tagModalClose" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="tagModalSave" class="btn btn-primary">Save Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>