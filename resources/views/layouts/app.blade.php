<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Task Management SPA')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <style>
        .bootstrap-select .dropdown-toggle {
            width: 100% !important;
        }
        .bootstrap-select .dropdown-menu {
            z-index: 1050 !important;
        }
        .bootstrap-select .dropdown-item {
            padding: 0.375rem 0.75rem;
        }
        .bootstrap-select .dropdown-item.active {
            background-color: #0d6efd;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div id="app">
        @include('partials.navbar')
        
        <div class="container mt-4">
            @yield('content')
        </div>
    </div>

    @include('modals.task-modal')
    @include('modals.tag-modal')

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    
    <!-- Core JS -->
    <script src="{{ asset('js/core/utils.js') }}"></script>
    <script src="{{ asset('js/core/app.js') }}"></script>
    <script src="{{ asset('js/core/auth.js') }}"></script>
    
    <!-- Feature JS (only load when needed) -->
    @if(request()->routeIs('tasks.*') || request()->routeIs('dashboard'))
        <script src="{{ asset('js/features/tasks.js') }}"></script>
    @endif
    
    @if(request()->routeIs('tags.*') || request()->routeIs('dashboard'))
        <script src="{{ asset('js/features/tags.js') }}"></script>
    @endif

    @yield('scripts')
</body>
</html>