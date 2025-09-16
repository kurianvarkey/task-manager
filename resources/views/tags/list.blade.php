@extends('layouts.app')

@section('title', 'Tags - Task Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tags</h2>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-primary me-2">
            <i class="fas fa-tasks"></i> Tasks
        </a>
        <button class="btn btn-primary" onclick="showTagModal()">
            <i class="fas fa-plus"></i> Add Tag
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tags</h5>
        <div class="d-flex align-items-center">
            <label class="form-label me-2 mb-0">Per Page:</label>
            <select class="form-select form-select-sm" id="tagsPerPage" onchange="changeTagsPerPage()" style="width: auto;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div id="tagsContainer">
            <!-- Tags will be loaded here -->
        </div>
        
        <!-- Pagination -->
        <div id="tagsPagination" class="d-flex justify-content-between align-items-center mt-4">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>

@include('modals.tag-modal')
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    checkAuthAndLoadTags();
});

async function checkAuthAndLoadTags() {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '{{ route("auth.login") }}';
        return;
    }
    
    setupAxios();
    loadTags();
}
</script>
@endsection