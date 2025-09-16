@extends('layouts.app')

@section('title', 'Dashboard - Task Manager')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            <div>
                <a href="{{ route('tasks.index') }}" class="btn btn-primary me-2">
                    <i class="fas fa-tasks"></i> Tasks
                </a>
                <a href="{{ route('tags.index') }}" class="btn btn-secondary">
                    <i class="fas fa-tags"></i> Tags
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Total Tasks</h4>
                                <h2 id="totalTasks">-</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tasks fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Completed</h4>
                                <h2 id="completedTasks">-</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Pending</h4>
                                <h2 id="pendingTasks">-</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Tasks</h5>
                    </div>
                    <div class="card-body">
                        <div id="recentTasks">
                            <!-- Recent tasks will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    checkAuthAndLoadDashboard();
});

async function checkAuthAndLoadDashboard() {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '{{ route("auth.login") }}';
        return;
    }
    
    setupAxios();
    await loadDashboardData();
}

async function loadDashboardData() {
    try {
        const response = await axios.get('/tasks?limit=10&sort=created_at&direction=desc'); // Get 10 tasks
        if (response.data.success || response.data.status === 'success') {
            let tasks = response.data.data;
            
            // Handle paginated response
            if (tasks.results) {
                tasks = tasks.results;
            }
            
            updateDashboardStats(tasks);
            displayRecentTasks(tasks.slice(0, 5)); // Show 5 recent tasks
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('authToken');
            localStorage.removeItem('currentUser');
            window.location.href = '{{ route("auth.login") }}';
        }
    }
}

function updateDashboardStats(tasks) {
    const total = tasks.length;
    const completed = tasks.filter(task => task.status === 'completed').length;
    const pending = tasks.filter(task => task.status === 'pending').length;
    
    document.getElementById('totalTasks').textContent = total;
    document.getElementById('completedTasks').textContent = completed;
    document.getElementById('pendingTasks').textContent = pending;
}

function displayRecentTasks(tasks) {
    const container = document.getElementById('recentTasks');
    
    if (tasks.length === 0) {
        container.innerHTML = '<p class="text-muted">No tasks found.</p>';
        return;
    }
    
    let html = '<div class="list-group">';
    tasks.forEach(task => {
        const statusBadge = getStatusBadge(task.status);
        const priorityBadge = getPriorityBadge(task.priority);
        
        html += `
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${task.title}</h6>
                    <small>${task.due_date || 'No due date'}</small>
                </div>
                <p class="mb-1">${task.description || 'No description'}</p>
                <div class="d-flex justify-content-between">
                    <div>
                        ${statusBadge} ${priorityBadge}
                    </div>
                    <small>Assigned to: ${task.assigned_user ? task.assigned_user.name : 'Unassigned'}</small>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}
</script>
@endsection