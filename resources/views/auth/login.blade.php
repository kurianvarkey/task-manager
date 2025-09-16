@extends('layouts.app')

@section('title', 'Login - Task Manager')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Login</h4>
            </div>
            <div class="card-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="loginEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="text" class="form-control" id="loginPassword" value="12345678"  required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="{{ route('auth.signup') }}">Sign up here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const LOGIN_URL = '/api/login';

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            try {
                const response = await axios.post(LOGIN_URL, { email, password });
                
                if (response.data.status === 'success') {
                    const currentUser = response.data.data || {};

                    localStorage.setItem('authToken', response.data.data.token);

                    delete currentUser.token;
                    localStorage.setItem('currentUser', JSON.stringify(currentUser));
                    
                    showAlert('Login successful!', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("dashboard") }}';
                    }, 1000);
                } else {
                    showAlert(response.data.message || 'Login failed', 'danger');
                }
            } catch (error) {
                const errorMessage = handleApiError(error);
                showAlert(errorMessage, 'danger');
            }
        });
    }
});
</script>
@endsection