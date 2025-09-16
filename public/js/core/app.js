// Global variables
let authToken = localStorage.getItem('authToken');
let currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
let allTags = [];
let allUsers = [];

// Setup Axios defaults
function setupAxios() {
    axios.defaults.baseURL = '/api';
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }
    
    const token = localStorage.getItem('authToken');
    if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }
}

// Update navbar with user info
function updateNavbar() {
    const welcomeText = document.getElementById('welcomeText');
    if (welcomeText) {
        const user = JSON.parse(localStorage.getItem('currentUser') || '{}');
        welcomeText.textContent = `Welcome, ${user.name || 'User'}`;
    }
}

// Logout function
function logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('currentUser');
    delete axios.defaults.headers.common['Authorization'];
    showAlert('Logged out successfully', 'info');
    setTimeout(() => {
        window.location.href = '/login';
    }, 1000);
}

// Initialize navbar on page load
document.addEventListener('DOMContentLoaded', function() {
    updateNavbar();
});