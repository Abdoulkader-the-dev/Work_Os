<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ProfileController;
use App\Livewire\Boards\BoardTable;
use App\Livewire\Boards\BoardKanban;
use App\Livewire\Boards\BoardCalendar;
use App\Models\Board;
use App\Models\Notification;

// Racine → dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Routes protégées
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');

    // Boards
    Route::get('/boards', fn() => view('pages.boards'))->name('boards.index');
    Route::post('/boards', function (Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $workspace = $request->user()?->currentWorkspace;

        abort_unless($workspace, 422, 'Aucun workspace actif pour créer un board.');

        $board = Board::create([
            'name' => $data['name'],
            'color' => $data['color'] ?: '#0091CD',
            'workspace_id' => $workspace->id,
        ]);

        return redirect()->route('boards.show', $board);
    })->name('boards.store');
    Route::post('/boards/{board}/groups', function (Request $request, Board $board) {
        $workspace = $request->user()?->currentWorkspace;

        abort_unless($workspace && $board->workspace_id === $workspace->id, 403);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $group = $board->groups()->create([
            'name' => trim($data['name'] ?? '') ?: 'Nouveau groupe',
            'color' => $data['color'] ?: '#0091CD',
            'order' => ((int) $board->groups()->max('order')) + 1,
        ]);

        return redirect()->route('boards.show', ['board' => $board])->with('group_created_id', $group->id);
    })->name('boards.groups.store');
    Route::post('/boards/{board}/items', function (Request $request, Board $board) {
        $workspace = $request->user()?->currentWorkspace;

        abort_unless($workspace && $board->workspace_id === $workspace->id, 403);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'group_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:todo,progress,ongoing,blocked,done'],
            'priority' => ['nullable', 'in:basse,moyenne,haute,critique'],
            'deadline' => ['nullable', 'date'],
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
            'deliverable' => $request->input('deliverable') ?: null,
            'order' => ((int) $group->items()->max('order')) + 1,
        ]);

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

// Auth routes
require __DIR__.'/auth.php';
