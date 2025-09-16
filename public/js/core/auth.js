// Auth utility functions - now handled in individual page scripts
// This file is kept for any shared auth utilities

// Check if user is authenticated
function isAuthenticated() {
    return localStorage.getItem('authToken') !== null;
}

// Get current user
function getCurrentUser() {
    return JSON.parse(localStorage.getItem('currentUser') || '{}');
}

// Redirect to login if not authenticated
function requireAuth() {
    if (!isAuthenticated()) {
        window.location.href = '/login';
        return false;
    }
    return true;
}