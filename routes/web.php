<?php

use App\Http\Controllers\InvitationController;
use App\Livewire\BoardIndex;
use App\Livewire\BoardShow;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Home → boards
    Route::redirect('/', '/boards');

    // Boards
    Route::get('/boards', BoardIndex::class)->name('boards.index');
    Route::get('/boards/{board}', BoardShow::class)->name('boards.show');

    // Invitation acceptance
    Route::get('/invitations/{token}', [InvitationController::class, 'show'])
         ->name('invitations.show');
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])
         ->name('invitations.accept');
});

require __DIR__.'/auth.php';
