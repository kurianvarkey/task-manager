<?php

use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::get('/login', function () {
    return view('auth.login');
})->name('auth.login');

Route::get('/signup', function () {
    return view('auth.signup');
})->name('auth.signup');

// Main app routes (protected by middleware in real app)
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::get('/tasks', function () {
    return view('tasks.list');
})->name('tasks.index');

Route::get('/tags', function () {
    return view('tags.list');
})->name('tags.index');
