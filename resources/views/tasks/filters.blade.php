<!-- Task Filters -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Task Filters</h5>
        <div class="d-flex align-items-center">
            <label class="form-label me-2 mb-0">Per Page:</label>
            <select class="form-select form-select-sm me-3" id="tasksPerPage" onchange="changeTasksPerPage()" style="width: auto;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-control" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="inprogress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select class="form-control" id="filterPriority">
                    <option value="">All Priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Assigned To</label>
                <select class="form-control" id="filterAssignedTo">
                    <option value="">All Users</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" id="filterFromDate">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" id="filterToDate">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tags</label>
                <select class="form-control selectpicker" id="filterTags" multiple data-live-search="true" data-actions-box="true" title="Filter by Tags">
                </select>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label class="form-label">Keyword</label>
                <input type="text" class="form-control" id="filterKeyword" placeholder="Search tasks...">
            </div>
            <div class="col-md-3">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="filterDeleted">
                    <label class="form-check-label" for="filterDeleted">Show Deleted Only</label>
                </div>
            </div>
            <div class="col-md-3 ">
                <div class="d-flex gap-3 mt-4" style="justify-content: flex-end;">
                    <button class="btn btn-primary " onclick="loadTasks(1)">Apply Filters</button>
                    <button class="btn btn-secondary" onclick="clearFilters()">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>
</div>