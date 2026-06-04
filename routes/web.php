<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Requests\WorkspaceMemberStoreRequest;
use App\Http\Requests\WorkspaceMemberUpdateRequest;
use App\Livewire\Boards\BoardCalendar;
use App\Livewire\Boards\BoardKanban;
use App\Livewire\Boards\BoardTable;
use App\Models\Comment;
use App\Models\Item;
use App\Models\Notification;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
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
    // Gestion des membres au sein des workspaces
    Route::post('/workspaces/{workspace}/add-members', [WorkspaceController::class, 'addMember'])->name('workspaces.members.add');
    Route::patch('/workspaces/{workspace}/members/{user}/change-role', [WorkspaceController::class, 'changeMemberRole'])->name('workspaces.members.change-role');
    Route::delete('/workspaces/{workspace}/members/{user}/remove', [WorkspaceController::class, 'removeMember'])->name('workspaces.members.rm-member');


    // Members management within workspaces
    Route::post('/workspaces/{workspace}/members', function (WorkspaceMemberStoreRequest $request, Workspace $workspace) {
        $data = $request->validated();

        $member = User::where('email', $data['email'])->first();

        if (!$member) {
            return back()->withErrors([
                'email' => 'Aucun utilisateur ne correspond à cette adresse e-mail.',
            ], 'workspaceMembers');
        }

        $workspace->members()->syncWithoutDetaching([
            $member->id => ['role' => $data['role']],
        ]);

        if (!$member->current_workspace_id) {
            $member->forceFill([
                'current_workspace_id' => $workspace->id,
            ])->save();
        }

        return back()->with('status', 'workspace-member-added');
    })->name('workspaces.members.store');

    // Update member role
    Route::patch('/workspaces/{workspace}/members/{user}', function (WorkspaceMemberUpdateRequest $request, Workspace $workspace, User $user) {

        abort_unless($workspace->members()->whereKey($user->id)->exists(), 404);

        $data = $request->validated();

        if ((int) $workspace->user_id === (int) $user->id) {
            $data['role'] = 'admin';
        }

        $workspace->members()->updateExistingPivot($user->id, [
            'role' => $data['role'],
        ]);

        return back()->with('status', 'workspace-member-updated');
    })->name('workspaces.members.update');

    // Remove member from workspace
    Route::delete('/workspaces/{workspace}/members/{user}', function (Request $request, Workspace $workspace, User $user) {
        abort_unless($request->user()->can('manageMembers', $workspace), 403);

        abort_unless($workspace->members()->whereKey($user->id)->exists(), 404);

        if ((int) $workspace->user_id === (int) $user->id) {
            return back()->withErrors([
                'workspace' => 'Le propriétaire ne peut pas être retiré du workspace.',
            ], 'workspaceMembers');
        }

        $workspace->members()->detach($user->id);

        if ((int) $user->current_workspace_id === (int) $workspace->id) {
            $fallbackWorkspace = $user->workspaces()->whereKeyNot($workspace->id)->first();
            $user->forceFill([
                'current_workspace_id' => $fallbackWorkspace?->id,
            ])->save();
        }

        return back()->with('status', 'workspace-member-removed');
    })->name('workspaces.members.destroy');

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

    // Autres
    Route::get('/my-tasks',      fn() => view('pages.my-tasks'))->name('my-tasks');
    Route::get('/calendar',      fn() => view('pages.calendar'))->name('calendar');
    Route::get('/reports',       fn() => view('pages.reports'))->name('reports');
    Route::get('/members',       fn() => view('pages.members'))->name('members');
    Route::get('/settings',      fn() => view('pages.settings'))->name('settings');
    Route::get('/notifications', fn() => view('pages.notifications'))->name('notifications.index');
    Route::post('/tour/complete', function (Request $request) {
        $request->user()->markOnboardingCompleted();
        return response()->noContent();
    })->name('tour.complete');
    Route::post('/notifications/read-all', function (Request $request) {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    })->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', function (Request $request, Notification $notification) {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return back();
    })->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

Route::get('/workspaces/{workspace}/invite', function (Request $request, Workspace $workspace) {
    abort_unless($request->hasValidSignature(), 403);

    $role = $request->string('role')->toString() ?: 'member';
    abort_unless(in_array($role, ['admin', 'member', 'reader'], true), 403);

    if ($request->user()) {
        $workspace->members()->syncWithoutDetaching([
            $request->user()->id => ['role' => $role],
        ]);

        if (!$request->user()->current_workspace_id) {
            $request->user()->forceFill([
                'current_workspace_id' => $workspace->id,
            ])->save();
        }

        return redirect()->route('members')->with('status', 'workspace-invite-accepted');
    }

    session([
        'pending_workspace_invite' => [
            'workspace_id' => $workspace->id,
            'role' => $role,
        ],
    ]);

    return redirect()->route('register')->with('status', 'workspace-invite-pending');
})->name('workspaces.members.invite')->middleware('signed');

// Auth routes
require __DIR__.'/auth.php';
