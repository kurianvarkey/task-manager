@extends('layouts.app')

@section('title', 'Tasks - Task Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tasks</h2>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <a href="{{ route('tags.index') }}" class="btn btn-outline-primary me-2">
            <i class="fas fa-tags"></i> Tags
        </a>
        <button class="btn btn-primary" onclick="showTaskModal()">
            <i class="fas fa-plus"></i> Add Task
        </button>
    </div>
</div>

@include('tasks.filters')

<!-- Tasks List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Tasks</h5>
    </div>
    <div class="card-body">
        <div id="tasksContainer">
            <!-- Tasks will be loaded here -->
        </div>
        
        <!-- Pagination -->
        <div id="tasksPagination" class="d-flex justify-content-between align-items-center mt-4">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>

@include('modals.task-modal')
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    checkAuthAndLoadTasks();
});

async function checkAuthAndLoadTasks() {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '{{ route("auth.login") }}';
        return;
    }
    
    setupAxios();
    await loadInitialData();
    loadTasks();
}

async function loadInitialData() {
    await loadTags();
    await loadUsers();
}

</script>
@endsection