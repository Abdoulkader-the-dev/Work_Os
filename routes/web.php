<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Boards\BoardTable;
use App\Livewire\Boards\BoardKanban;
use App\Livewire\Boards\BoardCalendar;
use App\Livewire\Meetings\MeetingList;
use App\Livewire\Meetings\MeetingEditor;

// Redirect racine → dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Dashboard
Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');

// Boards
Route::get('/boards', fn() => view('pages.boards'))->name('boards.index');
Route::get('/boards/{board}',          BoardTable::class)->name('boards.show');
Route::get('/boards/{board}/kanban',   BoardKanban::class)->name('boards.kanban');
Route::get('/boards/{board}/calendar', BoardCalendar::class)->name('boards.calendar');

// Réunions
Route::get('/meetings',             MeetingList::class)->name('meetings.index');
Route::get('/meetings/create',      MeetingEditor::class)->name('meetings.create');
Route::get('/meetings/{meeting}',   fn($id) => view('pages.meeting-show', ['id' => $id]))->name('meetings.show');
Route::get('/meetings/{meeting}/edit', MeetingEditor::class)->name('meetings.edit');

// Autres pages
Route::get('/my-tasks',  fn() => view('pages.my-tasks'))->name('my-tasks');
Route::get('/calendar',  fn() => view('pages.calendar'))->name('calendar');
Route::get('/reports',   fn() => view('pages.reports'))->name('reports');
Route::get('/members',   fn() => view('pages.members'))->name('members');
Route::get('/settings',  fn() => view('pages.settings'))->name('settings');
Route::get('/notifications', fn() => view('pages.notifications'))->name('notifications.index');

// Auth
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');