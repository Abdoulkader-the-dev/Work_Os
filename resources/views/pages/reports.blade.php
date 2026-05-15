<x-app-layout>
@section('page-title', 'Rapports')

@php
    $workspace = auth()->user()?->currentWorkspace;
    $boards = \App\Models\Board::query()->where('workspace_id', $workspace?->id)->get();
    $itemQuery = \App\Models\Item::query()->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id));

    $totalTasks = (clone $itemQuery)->count();
    $doneTasks = (clone $itemQuery)->where('status', 'done')->count();
    $progressTasks = (clone $itemQuery)->where('status', 'progress')->count();
    $blockedTasks = (clone $itemQuery)->where('status', 'blocked')->count();
    $todoTasks = (clone $itemQuery)->where('status', 'todo')->count();
    $ongoingTasks = (clone $itemQuery)->where('status', 'ongoing')->count();

    $statusRows = [
        ['label' => 'Achevé', 'count' => $doneTasks, 'class' => 's-done'],
        ['label' => 'En cours', 'count' => $progressTasks, 'class' => 's-progress'],
        ['label' => 'Bloqué', 'count' => $blockedTasks, 'class' => 's-blocked'],
        ['label' => 'Non commencé', 'count' => $todoTasks, 'class' => 's-todo'],
        ['label' => 'Continu', 'count' => $ongoingTasks, 'class' => 's-ongoing'],
    ];

    $completionRate = $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0;
    $criticalTasks = (clone $itemQuery)->where('priority', 'critique')->count();
    $overdueTasks = (clone $itemQuery)->whereNotNull('deadline')->whereDate('deadline', '<', now()->toDateString())->where('status', '!=', 'done')->count();
@endphp

<div style="display:flex;flex-direction:column;gap:20px;">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        <div class="bento-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:500;color:var(--text-2);margin-bottom:16px;">Tâches totales</div>
            <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;">{{ $totalTasks }}</div>
        </div>
        <div class="bento-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:500;color:var(--text-2);margin-bottom:16px;">Taux d'achèvement</div>
            <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;">{{ $completionRate }}%</div>
        </div>
        <div class="bento-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:500;color:var(--text-2);margin-bottom:16px;">Priorité critique</div>
            <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;">{{ $criticalTasks }}</div>
        </div>
        <div class="bento-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:500;color:var(--text-2);margin-bottom:16px;">En retard</div>
            <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;color:{{ $overdueTasks > 0 ? '#dc2626' : 'var(--text-1)' }};">{{ $overdueTasks }}</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:.8fr 1.2fr;gap:14px;">
        <div class="bento-card" style="padding:20px;">
            <div style="font-size:14px;font-weight:600;letter-spacing:-0.01em;margin-bottom:16px;">Répartition par statut</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($statusRows as $row)
                    @php
                        $width = $totalTasks > 0 ? max(6, (int) round(($row['count'] / $totalTasks) * 100)) : 0;
                    @endphp
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                            <span style="font-size:13px;color:var(--text-2);">{{ $row['label'] }}</span>
                            <span style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);">{{ $row['count'] }}</span>
                        </div>
                        <div style="height:8px;background:var(--bg);border-radius:999px;overflow:hidden;">
                            <div class="{{ $row['class'] }}" style="height:100%;width:{{ $width }}%;border-radius:999px;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bento-card" style="padding:20px;">
            <div style="font-size:14px;font-weight:600;letter-spacing:-0.01em;margin-bottom:16px;">Performance des boards</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @forelse($boards as $board)
                    <a href="{{ route('boards.show', $board) }}"
                       style="display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--border);border-radius:12px;text-decoration:none;color:inherit;transition:background .15s;"
                       onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='white'">
                        <div style="width:10px;height:10px;border-radius:50%;background:{{ $board->color ?? '#0091CD' }};flex-shrink:0;"></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $board->name }}</div>
                            <div style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;margin-top:3px;">
                                {{ $board->items_count }} tâches
                            </div>
                        </div>
                        <div style="width:120px;height:6px;background:var(--bg);border-radius:999px;overflow:hidden;flex-shrink:0;">
                            <div style="height:100%;width:{{ $board->progress }}%;background:var(--blue);border-radius:999px;"></div>
                        </div>
                        <span style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);width:40px;text-align:right;flex-shrink:0;">{{ $board->progress }}%</span>
                    </a>
                @empty
                    <div style="text-align:center;padding:32px;color:var(--text-3);font-size:13px;">Aucun board à analyser.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
</x-app-layout>
