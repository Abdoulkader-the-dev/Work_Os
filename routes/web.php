<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Events\BoardUpdated;
use App\Http\Requests\BoardStoreRequest;
use App\Http\Requests\BoardUpdateRequest;
use App\Http\Controllers\ProfileController;
use App\Livewire\Boards\BoardTable;
use App\Livewire\Boards\BoardKanban;
use App\Livewire\Boards\BoardCalendar;
use App\Models\Board;
use App\Models\Notification;
use App\Models\User;
use App\Models\Workspace;

// Racine → dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Routes protégées
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');

    // Workspaces
    Route::post('/workspaces', function (Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Le nom est requis.',
        ]);

        $workspace = DB::transaction(function () use ($request, $data) {
            $workspace = Workspace::create([
                'name' => trim($data['name']),
                'user_id' => $request->user()->id,
                'color' => '#0091CD',
            ]);

            $request->user()->workspaces()->syncWithoutDetaching([
                $workspace->id => ['role' => 'admin'],
            ]);

            $request->user()->forceFill([
                'current_workspace_id' => $workspace->id,
            ])->save();

            $request->user()->markOnboardingStarted('dashboard');

            return $workspace;
        });

        return redirect()->route('dashboard')->with('status', 'workspace-created')->with('onboarding', 'start');
    })->name('workspaces.store');

    Route::post('/workspaces/{workspace}/switch', function (Request $request, Workspace $workspace) {
        abort_unless($request->user()->workspaces()->whereKey($workspace->id)->exists(), 403);

        $request->user()->forceFill([
            'current_workspace_id' => $workspace->id,
        ])->save();

        return redirect()->route('dashboard')->with('status', 'workspace-switched');
    })->name('workspaces.switch');

    Route::patch('/workspaces/{workspace}', function (Request $request, Workspace $workspace) {
        abort_unless($request->user()->can('update', $workspace), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Le nom est requis.',
        ]);

        $workspace->update([
            'name' => trim($data['name']),
        ]);

        return redirect()->route('dashboard')->with('status', 'workspace-updated');
    })->name('workspaces.update');

    Route::delete('/workspaces/{workspace}', function (Request $request, Workspace $workspace) {
        abort_unless($request->user()->can('delete', $workspace), 403);

        $user = $request->user();
        $workspaceId = $workspace->id;

        if ((int) $user->current_workspace_id === (int) $workspaceId) {
            $fallbackWorkspace = $user->workspaces()->whereKeyNot($workspaceId)->first();
            $user->forceFill([
                'current_workspace_id' => $fallbackWorkspace?->id,
            ])->save();
        }

        $user->workspaces()->detach($workspaceId);
        $workspace->delete();

        return redirect()->route('dashboard')->with('status', 'workspace-deleted');
    })->name('workspaces.destroy');

    Route::post('/workspaces/{workspace}/members', function (Request $request, Workspace $workspace) {
        abort_unless($request->user()->can('manageMembers', $workspace), 403);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:admin,member,reader'],
        ], [
            'email.required' => 'L’adresse e-mail est requise.',
            'email.email' => 'L’adresse e-mail est invalide.',
            'role.required' => 'Le rôle est requis.',
        ]);

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

    Route::patch('/workspaces/{workspace}/members/{user}', function (Request $request, Workspace $workspace, User $user) {
        abort_unless($request->user()->can('manageMembers', $workspace), 403);

        abort_unless($workspace->members()->whereKey($user->id)->exists(), 404);

        $data = $request->validate([
            'role' => ['required', 'in:admin,member,reader'],
        ]);

        if ((int) $workspace->user_id === (int) $user->id) {
            $data['role'] = 'admin';
        }

        $workspace->members()->updateExistingPivot($user->id, [
            'role' => $data['role'],
        ]);

        return back()->with('status', 'workspace-member-updated');
    })->name('workspaces.members.update');

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
    Route::get('/boards', fn() => view('pages.boards'))->name('boards.index');
    Route::post('/boards', function (BoardStoreRequest $request) {
        $data = $request->validated();

        $workspace = $request->user()?->activeWorkspace;

        abort_unless($workspace, 422, 'Aucun workspace actif pour créer un board.');

        $board = Board::create([
            'name' => $data['name'],
            'color' => $data['color'] ?: '#0091CD',
            'workspace_id' => $workspace->id,
        ]);

        return redirect()->route('boards.show', $board);
    })->name('boards.store');
    Route::patch('/boards/{board}', function (BoardUpdateRequest $request, Board $board) {
        $data = $request->validated();

        $board->update([
            'name' => trim($data['name']),
            'color' => $data['color'] ?: $board->color,
        ]);

        BoardUpdated::dispatch($board->fresh(), 'board.updated', ['board_id' => $board->id]);

        return back()->with('status', 'board-updated');
    })->name('boards.update');
    Route::delete('/boards/{board}', function (Request $request, Board $board) {
        abort_unless($request->user()?->can('delete', $board), 403);

        $board->delete();

        return redirect()->route('boards.index')->with('status', 'board-deleted');
    })->name('boards.destroy');
    Route::post('/boards/{board}/groups', function (Request $request, Board $board) {
        abort_unless($request->user()?->can('update', $board), 403);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $group = $board->groups()->create([
            'name' => trim($data['name'] ?? '') ?: 'Nouveau groupe',
            'color' => $data['color'] ?: '#0091CD',
            'order' => ((int) $board->groups()->max('order')) + 1,
        ]);

        BoardUpdated::dispatch($board->fresh(), 'group.created', ['group_id' => $group->id]);

        return redirect()->route('boards.show', ['board' => $board])->with('group_created_id', $group->id);
    })->name('boards.groups.store');
    Route::post('/boards/{board}/items', function (Request $request, Board $board) {
        abort_unless($request->user()?->can('update', $board), 403);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'group_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:todo,progress,ongoing,blocked,done'],
            'priority' => ['nullable', 'in:basse,moyenne,haute,critique'],
            'deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'deliverable' => ['nullable', 'string'],
            'redirect_view' => ['nullable', 'in:table,kanban,calendar'],
            'redirect_month' => ['nullable', 'integer', 'between:1,12'],
            'redirect_year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $redirectView = $request->input('redirect_view', 'table');
        $redirectParams = ['board' => $board];

        if ($redirectView === 'calendar') {
            if ($request->filled('redirect_month')) {
                $redirectParams['month'] = (int) $request->input('redirect_month');
            }

            if ($request->filled('redirect_year')) {
                $redirectParams['year'] = (int) $request->input('redirect_year');
            }
        }

        $redirectRoute = match ($redirectView) {
            'kanban' => 'boards.kanban',
            'calendar' => 'boards.calendar',
            default => 'boards.show',
        };

        if ($validator->fails()) {
            $redirectParams['createTask'] = 1;

            if ($request->filled('group_id')) {
                $redirectParams['group'] = $request->input('group_id');
            }

            if ($request->filled('status')) {
                $redirectParams['status'] = $request->input('status');
            }

            if ($request->filled('deadline')) {
                $redirectParams['date'] = $request->input('deadline');
            }

            return redirect()
                ->route($redirectRoute, $redirectParams)
                ->withErrors($validator, 'createTask')
                ->withInput();
        }

        $group = $board->groups()->find($request->integer('group_id'));

        if (!$group) {
            $group = $board->groups()->orderBy('order')->first();
        }

        if (!$group) {
            $group = $board->groups()->create([
                'name' => 'Général',
                'color' => '#0091CD',
                'order' => ((int) $board->groups()->max('order')) + 1,
            ]);
        }

        $item = $group->items()->create([
            'name' => trim($request->input('name')),
            'status' => $request->input('status', 'todo'),
            'priority' => $request->input('priority', 'moyenne'),
            'deadline' => $request->input('deadline') ?: null,
            'description' => filled($request->input('description'))
                ? strip_tags($request->input('description'), '<p><br><strong><em><a><ul><ol><li><blockquote><code>')
                : null,
            'deliverable' => $request->input('deliverable') ?: null,
            'order' => ((int) $group->items()->max('order')) + 1,
        ]);

        BoardUpdated::dispatch($board->fresh(), 'item.created', ['item_id' => $item->id]);

        return redirect()
            ->route($redirectRoute, $redirectParams)
            ->with('item_created_id', $item->id)
            ->with('item_created_name', $item->name);
    })->name('boards.items.store');
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
