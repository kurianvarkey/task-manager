@extends('layouts.app')

@section('title', 'Sign Up - Task Manager')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Sign Up</h4>
            </div>
            <div class="card-body">
                <form id="signupForm">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="signupEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="signupName" required>
                    </div>                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="text" class="form-control" id="signupPassword" value="12345678" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-control" id="signupRole" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Sign Up</button>
                </form>
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="{{ route('auth.login') }}">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const SIGNUP_URL = '/api/signup';

document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('signupName').value;
            const email = document.getElementById('signupEmail').value;
            const password = document.getElementById('signupPassword').value;
            const role = document.getElementById('signupRole').value;

            try {
                const response = await axios.post(SIGNUP_URL, { name, email, password, role });
                
                if (response.data.success || response.data.status === 'success') {
                    showAlert('Signup successful! Redirecting to login...', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("auth.login") }}';
                    }, 2000);
                } else {
                    showAlert(response.data.message || 'Signup failed', 'danger');
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