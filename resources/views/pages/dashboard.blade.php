<x-app-layout>
@section('page-title', 'Dashboard')

@php
    $user = auth()->user();
    $workspace = $user?->currentWorkspace;

    $boardQuery = \App\Models\Board::query()
        ->where('workspace_id', $workspace?->id);

    $itemQuery = \App\Models\Item::query()
        ->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id));

    $boards = (clone $boardQuery)->get();
    $activeBoards = $boards->count();
    $totalTasks = (clone $itemQuery)->count();
    $doneTasks = (clone $itemQuery)->where('status', 'done')->count();
    $blockedTasks = (clone $itemQuery)->where('status', 'blocked')->count();
    $completionRate = $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0;

    $weekStart = now()->copy()->startOfWeek();
    $weekEnd = now()->copy()->endOfWeek();
    $lastWeekStart = $weekStart->copy()->subWeek();
    $lastWeekEnd = $weekEnd->copy()->subWeek();

    $tasksThisWeek = (clone $itemQuery)->whereBetween('created_at', [$weekStart, $weekEnd])->count();
    $tasksLastWeek = (clone $itemQuery)->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();
    $weeklyDelta = $tasksLastWeek > 0
        ? (int) round((($tasksThisWeek - $tasksLastWeek) / $tasksLastWeek) * 100)
        : ($tasksThisWeek > 0 ? 100 : 0);

    $tasksDueThisWeek = (clone $itemQuery)
        ->whereNotNull('deadline')
        ->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()])
        ->count();

    $boardsPreview = (clone $boardQuery)->take(5)->get();
    $urgentTasks = (clone $itemQuery)
        ->where(function ($query) {
            $query->where('status', 'blocked')
                ->orWhere('priority', 'critique')
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('deadline')
                        ->whereDate('deadline', '<=', now()->addDays(3)->toDateString())
                        ->where('status', '!=', 'done');
                });
        })
        ->with('group.board')
        ->orderByRaw("case when status = 'blocked' then 0 when priority = 'critique' then 1 else 2 end")
        ->orderBy('deadline')
        ->take(6)
        ->get();

    $recentItems = (clone $itemQuery)
        ->with('group.board')
        ->latest('updated_at')
        ->take(4)
        ->get()
        ->map(fn ($item) => [
            'title' => $item->name,
            'subtitle' => ($item->group->board->name ?? 'Board') . ' / ' . ($item->group->name ?? 'Groupe'),
            'time' => $item->updated_at,
            'badge' => match ($item->status) {
                'done' => 'Achevé',
                'progress' => 'En cours',
                'blocked' => 'Bloqué',
                'ongoing' => 'Continu',
                default => 'Non commencé',
            },
        ]);

    $recentMeetings = \App\Models\Meeting::query()
        ->where('user_id', $user?->id)
        ->latest('date')
        ->take(3)
        ->get()
        ->map(fn ($meeting) => [
            'title' => $meeting->title,
            'subtitle' => 'Réunion',
            'time' => $meeting->updated_at ?? $meeting->created_at,
            'badge' => 'Meeting',
        ]);

    $recentActivity = $recentItems
        ->concat($recentMeetings)
        ->sortByDesc('time')
        ->take(6)
        ->values();

    $members = collect([$workspace?->owner])
        ->merge($workspace?->members ?? collect())
        ->filter()
        ->unique('id')
        ->take(5)
        ->values();
@endphp

<div style="display:flex;flex-direction:column;gap:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
        <div>
            <h2 style="font-size:28px;font-weight:600;letter-spacing:-0.03em;line-height:1;">Aujourd'hui</h2>
            <p style="font-size:13px;color:var(--text-3);font-family:'DM Mono',monospace;margin-top:6px;">
                {{ now()->isoFormat('ddd D MMM · HH:mm') }}
            </p>
        </div>

        <div class="bento-card" style="display:flex;align-items:center;gap:16px;padding:14px 20px;">
            <div>
                <div style="font-size:13px;font-weight:500;">Workspace actif</div>
                <div style="font-size:18px;font-weight:600;letter-spacing:-0.02em;margin-top:2px;color:var(--text-1);">
                    {{ $workspace?->name ?? 'Aucun workspace' }}
                </div>
            </div>
            <div style="width:42px;height:42px;background:var(--yellow-light);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" style="color:var(--yellow);">
                    <path d="M3 5.5a2 2 0 012-2h3.25a1 1 0 01.71.29L10.17 5H13a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-7.5z" stroke="currentColor" stroke-width="1.6"/>
                </svg>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        <div class="bento-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:500;color:var(--text-2);margin-bottom:18px;">Progression globale</div>
            <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;line-height:1;">{{ $completionRate }}%</div>
            <div style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;margin-top:6px;">
                {{ $doneTasks }} / {{ $totalTasks }} tâches achevées
            </div>
        </div>

        <div class="bento-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:500;color:var(--text-2);margin-bottom:18px;">Créées cette semaine</div>
            <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;line-height:1;">{{ str_pad((string) $tasksThisWeek, 2, '0', STR_PAD_LEFT) }}</div>
            <div style="font-size:11px;color:{{ $weeklyDelta >= 0 ? '#16a34a' : '#dc2626' }};font-family:'DM Mono',monospace;margin-top:6px;">
                {{ $weeklyDelta >= 0 ? '+' : '' }}{{ $weeklyDelta }}% vs semaine passée
            </div>
        </div>

        <div class="bento-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:500;color:var(--text-2);margin-bottom:18px;">Boards actifs</div>
            <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;line-height:1;">{{ str_pad((string) $activeBoards, 2, '0', STR_PAD_LEFT) }}</div>
            <div style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;margin-top:6px;">
                Workspace courant
            </div>
        </div>

        <div class="bento-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:500;color:var(--text-2);margin-bottom:18px;">À traiter cette semaine</div>
            <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;line-height:1;">{{ str_pad((string) $tasksDueThisWeek, 2, '0', STR_PAD_LEFT) }}</div>
            <div style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;margin-top:6px;">
                Tâches avec deadline
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1.15fr .85fr;gap:14px;">
        <div class="bento-card" style="padding:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <span style="font-size:14px;font-weight:600;letter-spacing:-0.01em;">Activité récente</span>
                <a href="{{ route('notifications.index') }}" style="font-size:12px;color:var(--blue);text-decoration:none;font-weight:500;">Notifications</a>
            </div>
            <div style="display:flex;flex-direction:column;">
                @forelse($recentActivity as $event)
                    <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                            {{ strtoupper(substr($event['title'], 0, 2)) }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:500;">{{ $event['title'] }}</div>
                            <div style="font-size:12px;color:var(--text-2);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $event['subtitle'] }}
                            </div>
                            <div style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;margin-top:2px;">
                                {{ $event['time']->diffForHumans() }}
                            </div>
                        </div>
                        <span style="font-size:11px;font-weight:500;padding:2px 8px;border-radius:20px;background:var(--bg);color:var(--text-2);white-space:nowrap;flex-shrink:0;">
                            {{ $event['badge'] }}
                        </span>
                    </div>
                @empty
                    <div style="text-align:center;padding:32px 0;color:var(--text-3);font-size:13px;">
                        Aucune activité récente
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bento-card" style="padding:20px;display:flex;flex-direction:column;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <span style="font-size:14px;font-weight:600;letter-spacing:-0.01em;">Boards</span>
                <a href="{{ route('boards.index') }}" style="font-size:12px;font-weight:500;color:var(--text-2);text-decoration:none;display:flex;align-items:center;gap:4px;padding:5px 10px;border-radius:6px;border:1px solid var(--border);transition:all .15s;">
                    Voir tout
                </a>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;gap:2px;">
                @forelse($boardsPreview as $board)
                    <a href="{{ route('boards.show', $board) }}"
                       style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;text-decoration:none;color:inherit;transition:background .15s;"
                       onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $board->color ?? '#0091CD' }};flex-shrink:0;"></div>
                        <span style="flex:1;font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $board->name }}</span>
                        <div style="width:80px;height:3px;background:var(--bg);border-radius:10px;flex-shrink:0;overflow:hidden;">
                            <div style="height:100%;background:var(--blue);border-radius:10px;width:{{ $board->progress }}%;transition:width .4s;"></div>
                        </div>
                        <span style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);flex-shrink:0;">{{ $board->items_count }}</span>
                    </a>
                @empty
                    <div style="text-align:center;padding:24px 0;color:var(--text-3);font-size:13px;">Aucun board actif</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bento-card" style="padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:14px;font-weight:600;letter-spacing:-0.01em;">Tâches urgentes</span>
                @if($blockedTasks > 0)
                    <span style="background:#fee2e2;color:#dc2626;font-size:10px;font-weight:600;font-family:'DM Mono',monospace;padding:2px 7px;border-radius:20px;">
                        {{ $blockedTasks }} bloquées
                    </span>
                @endif
            </div>
            <a href="{{ route('my-tasks') }}" style="font-size:12px;color:var(--blue);text-decoration:none;font-weight:500;">Voir tout →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px;">
            @forelse($urgentTasks as $task)
                <a href="{{ route('boards.show', $task->group->board) }}"
                   style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:var(--bg);border-radius:10px;cursor:pointer;transition:background .15s;text-decoration:none;color:inherit;"
                   onmouseover="this.style.background='#eaeae8'" onmouseout="this.style.background='var(--bg)'">
                    <div style="width:6px;height:6px;border-radius:50%;margin-top:5px;flex-shrink:0;background:{{ $task->status === 'blocked' ? '#dc2626' : ($task->priority === 'critique' ? '#8b5cf6' : '#f97316') }};"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $task->name }}</div>
                        <div style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;margin-top:3px;">
                            {{ $task->group->board->name ?? '—' }} · {{ $task->deadline?->format('d M') ?? 'Sans deadline' }}
                        </div>
                    </div>
                    <span class="s-{{ $task->status }}" style="font-size:10px;font-weight:500;padding:2px 7px;border-radius:20px;flex-shrink:0;">
                        {{ match($task->status) {
                            'done'     => 'Achevé',
                            'progress' => 'En cours',
                            'todo'     => 'Non commencé',
                            'blocked'  => 'Bloqué',
                            'ongoing'  => 'Continu',
                            default    => ucfirst($task->status)
                        } }}
                    </span>
                </a>
            @empty
                <div style="grid-column:1/-1;text-align:center;padding:16px;color:var(--text-3);font-size:13px;">
                    Aucune tâche urgente dans ce workspace.
                </div>
            @endforelse
        </div>
    </div>

    <div class="bento-card" style="padding:20px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:14px;font-weight:600;letter-spacing:-0.01em;">Membres</span>
                <span style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);background:var(--bg);padding:1px 7px;border-radius:20px;">
                    {{ $members->count() }}
                </span>
            </div>
            <a href="{{ route('members') }}" style="font-size:12px;color:var(--blue);text-decoration:none;font-weight:500;">Gérer →</a>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:0 10px 10px 0;">Membre</th>
                        <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:0 10px 10px;">Tâches ouvertes</th>
                        <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:0 10px 10px;">En cours</th>
                        <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:0 0 10px;">Rôle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        @php
                            $memberTaskQuery = $member->items()->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id));
                            $memberOpenTasks = (clone $memberTaskQuery)->where('status', '!=', 'done')->count();
                            $memberInProgress = (clone $memberTaskQuery)->where('status', 'progress')->count();
                            $memberRole = $workspace && $workspace->user_id === $member->id ? 'Owner' : ($member->pivot->role ?? 'Membre');
                        @endphp
                        <tr style="transition:background .15s;" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                            <td style="padding:10px 10px 10px 0;border-top:1px solid var(--border);">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                                        {{ strtoupper(substr($member->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="font-size:13px;font-weight:500;">{{ $member->name }}</div>
                                        <div style="font-size:11px;color:var(--text-3);">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:10px;border-top:1px solid var(--border);font-size:13px;color:var(--text-2);">{{ $memberOpenTasks }}</td>
                            <td style="padding:10px;border-top:1px solid var(--border);font-size:13px;color:var(--text-2);">{{ $memberInProgress }}</td>
                            <td style="padding:10px 0 10px 10px;border-top:1px solid var(--border);font-size:12px;color:var(--text-2);">{{ $memberRole }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:24px;text-align:center;color:var(--text-3);font-size:13px;border-top:1px solid var(--border);">
                                Aucun membre dans ce workspace.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-app-layout>
