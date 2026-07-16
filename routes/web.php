<?php

use App\Http\Controllers\InvitationController;
use App\Livewire\BoardShow;
use App\Livewire\WorkspaceIndex;
use App\Livewire\WorkspaceShow;
use App\Livewire\WorkspaceSettings;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile',     'settings.profile')->name('settings.profile');
    Volt::route('settings/password',    'settings.password')->name('settings.password');
    Volt::route('settings/appearance',  'settings.appearance')->name('settings.appearance');

    // Root → workspaces
    Route::redirect('/', '/workspaces');

    // Workspaces
    Route::get('/workspaces',                      WorkspaceIndex::class)->name('workspaces.index');
    Route::get('/workspaces/{workspace}',          WorkspaceShow::class)->name('workspaces.show');
    Route::get('/workspaces/{workspace}/settings', WorkspaceSettings::class)->name('workspaces.settings');

    // Boards
    Route::get('/boards/{board}', BoardShow::class)->name('boards.show');

    // Invitations
    Route::get('/invitations/{token}',        [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{token}/accept',[InvitationController::class, 'accept'])->name('invitations.accept');
});

require __DIR__.'/auth.php';
