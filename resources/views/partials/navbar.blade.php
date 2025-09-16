<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}"><i class="fas fa-tasks"></i> Task Manager</a>
        <div class="navbar-nav ms-auto" id="navbarContent">
            @if(request()->routeIs('auth.*'))
                <!-- Auth pages - no nav items -->
            @else
                <span class="navbar-text me-3" id="welcomeText">Welcome, User</span>
                <button class="btn btn-outline-light btn-sm" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            @endif
        </div>
    </div>
</nav>