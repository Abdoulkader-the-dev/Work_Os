<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Boards\BoardTable;
use App\Livewire\Boards\BoardKanban;
use App\Livewire\Boards\BoardCalendar;

// Racine → dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Routes protégées
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');

    // Boards
    Route::get('/boards', fn() => view('pages.boards'))->name('boards.index');
    Route::get('/boards/{board}',          BoardTable::class)->name('boards.show');
    Route::get('/boards/{board}/kanban',   BoardKanban::class)->name('boards.kanban');
    Route::get('/boards/{board}/calendar', BoardCalendar::class)->name('boards.calendar');

    // Réunions
    Route::get('/meetings',                fn() => view('pages.meetings'))->name('meetings.index');
    Route::get('/meetings/create',         fn() => view('pages.meeting-create'))->name('meetings.create');
    Route::get('/meetings/{meeting}/edit', fn($m) => view('pages.meeting-edit', ['meetingId' => $m]))->name('meetings.edit');
    Route::get('/meetings/{meeting}',      fn($m) => view('pages.meeting-show', ['meetingId' => $m]))->name('meetings.show');

    // Autres
    Route::get('/my-tasks',      fn() => view('pages.my-tasks'))->name('my-tasks');
    Route::get('/calendar',      fn() => view('pages.calendar'))->name('calendar');
    Route::get('/reports',       fn() => view('pages.reports'))->name('reports');
    Route::get('/members',       fn() => view('pages.members'))->name('members');
    Route::get('/settings',      fn() => view('pages.settings'))->name('settings');
    Route::get('/notifications', fn() => view('pages.notifications'))->name('notifications.index');

});

// Auth routes
require __DIR__.'/auth.php';
Route::get('/profile', fn() => view('pages.settings'))->name('profile.edit');
Route::patch('/profile', fn() => back())->name('profile.update');
Route::delete('/profile', fn() => redirect('/'))->name('profile.destroy');