<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use App\Livewire\Boards\BoardCalendar;
use App\Livewire\Boards\BoardKanban;
use App\Livewire\Boards\BoardTable;
use Illuminate\Support\Facades\Route;


// Racine → dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Routes protégées
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');

    // Workspaces
    // --- CRUD standard pour les workspaces
    Route::post('/workspaces',[WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::post('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');
    Route::patch('/workspaces/{workspace}', [WorkspaceController::class, 'update'])->name('workspaces.update');
    Route::delete('/workspaces/{workspace}', [WorkspaceController::class, 'destroy'])->name('workspaces.destroy');
    // Members management within workspaces
    Route::post('/workspaces/{workspace}/members', [WorkspaceController::class, 'storeMember'])->name('workspaces.members.store');

    // Update member role
    Route::patch('/workspaces/{workspace}/members/{user}', [WorkspaceController::class, 'updateMember'])->name('workspaces.members.update');

    // Remove member from workspace
    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceController::class, 'destroyMember'])->name('workspaces.members.destroy');

    // Boards
    // --- CRUD standard pour les boards
    Route::get('/boards', fn() => view('pages.boards'))->name('boards.index');
    Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');
    Route::patch('/boards/{board}', [BoardController::class, 'update'])->name('boards.update');
    Route::delete('/boards/{board}', [BoardController::class, 'destroy'])->name('boards.destroy');
    // Routes spécifiques pour les boards (groupes, items, vues...)
    Route::post('/boards/{board}/groups', [BoardController::class, 'storeGroupe'])->name('boards.groups.store');
    Route::post('/boards/{board}/items',[BoardController::class, 'storeItem'] )->name('boards.items.store');
    Route::get('/boards/{board}',          BoardTable::class)->name('boards.show');
    Route::get('/boards/{board}/kanban',   BoardKanban::class)->name('boards.kanban');
    Route::get('/boards/{board}/calendar', BoardCalendar::class)->name('boards.calendar');

    //Items
        //Item management routes
    Route::patch('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}',[ItemController::class, 'delete'])->name('items.destroy');

    //Comment
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::get('/items/{item}/comments', [CommentController::class, 'index'])->name('comments.index');

    // Réunions
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::patch('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
    Route::post('/meetings/{meeting}/actions/{actionIndex}/convert', [MeetingController::class, 'convertActionToTask'])->name('meetings.actions.convert');
    Route::post('/meetings/{meeting}/attendees', [MeetingController::class, 'addAttendee'])->name('meetings.attendees.store');
    Route::delete('/meetings/{meeting}/attendees/{index}', [MeetingController::class, 'removeAttendee'])->name('meetings.attendees.destroy');
    Route::post('/meetings/{meeting}/bilan', [MeetingController::class, 'addBilanPoint'])->name('meetings.bilan.store');
    Route::delete('/meetings/{meeting}/bilan/{index}', [MeetingController::class, 'removeBilanPoint'])->name('meetings.bilan.destroy');
    Route::post('/meetings/{meeting}/recommendations', [MeetingController::class, 'addRecommendation'])->name('meetings.recommendations.store');
    Route::delete('/meetings/{meeting}/recommendations/{index}', [MeetingController::class, 'removeRecommendation'])->name('meetings.recommendations.destroy');
    Route::post('/meetings/{meeting}/actions', [MeetingController::class, 'addAction'])->name('meetings.actions.store');
    Route::delete('/meetings/{meeting}/actions/{index}', [MeetingController::class, 'removeAction'])->name('meetings.actions.destroy');
    Route::get('/meetings/{meeting}/pdf', [MeetingController::class, 'exportPdf'])->name('meetings.pdf');

    // Autres
    Route::get('/my-tasks',      fn() => view('pages.my-tasks'))->name('my-tasks');
    Route::get('/calendar',      fn() => view('pages.calendar'))->name('calendar');
    Route::get('/reports',       fn() => view('pages.reports'))->name('reports');
    Route::get('/members',       fn() => view('pages.members'))->name('members');
    Route::get('/settings',      fn() => view('pages.settings'))->name('settings');
    Route::get('/notifications', fn() => view('pages.notifications'))->name('notifications.index');
    Route::post('/tour/complete', [OnboardingController::class, 'complete'])->name('tour.complete');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

Route::get('/workspaces/{workspace}/invite', [WorkspaceController::class, 'invite'])
    ->name('workspaces.members.invite')
    ->middleware('signed');

// Auth routes
require __DIR__.'/auth.php';
