<?php

use App\Livewire\ContactForm;
use App\Livewire\LandingPage;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('/contact', ContactForm::class);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('chat', 'chat')
    ->middleware(['auth', 'verified'])
    ->name('chat');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

