<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::inertia('staff', 'Staff')->name('staff');
    Route::inertia('hospital', 'Hospital')->name('hospital');
});

require __DIR__.'/settings.php';
