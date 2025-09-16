<!-- Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalTitle">Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="taskForm">
                <div class="modal-body">
                    <!-- Inline Alert Container -->
                    <div id="taskModalAlert" class="alert-container mb-3" style="display: none;"></div>

                    <input type="hidden" id="version">
                    
                    <input type="hidden" id="taskId">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" id="taskTitle" required>
                            <div class="invalid-feedback" id="taskTitleError"></div>
                        </div>                        
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="taskDescription" rows="2"></textarea>
                            <div class="invalid-feedback" id="taskDescriptionError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select class="form-control" id="taskPriority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected="selected">Medium</option>
                                <option value="high">High</option>
                            </select>
                            <div class="invalid-feedback" id="taskPriorityError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="taskStatus" required>
                                <option value="pending">Pending</option>
                                <option value="inprogress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            <div class="invalid-feedback" id="taskStatusError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="taskDueDate">
                            <div class="invalid-feedback" id="taskDueDateError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned To</label>
                            <select class="form-control" id="taskAssignedTo">
                                <option value="">Select User</option>
                            </select>
                            <div class="invalid-feedback" id="taskAssignedToError"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Tags</label>
                            <select class="form-control selectpicker" id="taskTags" multiple data-live-search="true" data-actions-box="true" title="Select Tags">
                            </select>
                            <div class="invalid-feedback" id="taskTagsError"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Metadata <small class="text-muted">(Optional JSON for extra properties)</small></label>
                            <textarea class="form-control" id="taskMetadata" rows="2" placeholder='{"key": "value", "custom_field": "data"}'></textarea>
                            <div class="invalid-feedback" id="taskMetadataError"></div>
                            <div class="form-text">Enter valid JSON format for additional task properties</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="taskModalClose" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="taskModalSave" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Logs Modal -->
<div class="modal fade" id="taskLogsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskLogsModalTitle">Task Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading indicator -->
                <div id="logsLoading" class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading logs...</p>
                </div>
                
                <!-- Logs content -->
                <div id="logsContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Operation</th>
                                    <th>User</th>
                                    <th>Changes</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody">
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination for logs -->
                    <div id="logsPagination" class="d-flex justify-content-center mt-3"></div>
                </div>
                
                <!-- No logs message -->
                <div id="noLogsMessage" class="text-center py-4" style="display: none;">
                    <p class="text-muted">No logs found for this task.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>