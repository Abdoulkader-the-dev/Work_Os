<x-app-layout>
@section('page-title', 'Calendrier')

@php
    $workspace = auth()->user()?->activeWorkspace;
    $year = max(2000, min(2100, (int) request('year', now()->year)));
    $month = max(1, min(12, (int) request('month', now()->month)));

    $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
    $endOfMonth = $startOfMonth->copy()->endOfMonth();

    $items = \App\Models\Item::query()
        ->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id))
        ->with('group.board')
        ->whereNotNull('deadline')
        ->whereBetween('deadline', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
        ->orderBy('deadline')
        ->get()
        ->groupBy(fn ($item) => $item->deadline->format('Y-m-d'));

    $startOfGrid = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $endOfGrid = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

    $weeks = [];
    $cursor = $startOfGrid->copy();
    while ($cursor <= $endOfGrid) {
        $week = [];
        for ($day = 0; $day < 7; $day++) {
            $week[] = $cursor->copy();
            $cursor->addDay();
        }
        $weeks[] = $week;
    }

    $monthLabel = $startOfMonth->isoFormat('MMMM YYYY');
    $scheduledCount = $items->flatten()->count();
    $overdueCount = $items->flatten()->where('deadline', '<', now()->startOfDay())->where('status', '!=', 'done')->count();
@endphp

<div style="display:flex;flex-direction:column;gap:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('calendar', ['month' => $startOfMonth->copy()->subMonth()->month, 'year' => $startOfMonth->copy()->subMonth()->year]) }}"
               style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-2);text-decoration:none;">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <h2 style="font-size:20px;font-weight:600;letter-spacing:-0.02em;min-width:220px;text-transform:capitalize;">{{ $monthLabel }}</h2>
            <a href="{{ route('calendar', ['month' => $startOfMonth->copy()->addMonth()->month, 'year' => $startOfMonth->copy()->addMonth()->year]) }}"
               style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-2);text-decoration:none;">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <div class="bento-card" style="padding:10px 14px;font-size:12px;color:var(--text-2);">
                {{ $scheduledCount }} tâches planifiées
            </div>
            <div class="bento-card" style="padding:10px 14px;font-size:12px;color:{{ $overdueCount > 0 ? '#dc2626' : 'var(--text-2)' }};">
                {{ $overdueCount }} en retard
            </div>
            <a href="{{ route('calendar') }}" style="font-size:12px;font-weight:500;font-family:'DM Sans',sans-serif;padding:6px 14px;border-radius:8px;border:1px solid var(--border);background:white;cursor:pointer;color:var(--text-2);transition:all .15s;text-decoration:none;">
                Aujourd'hui
            </a>
        </div>
    </div>

    <div style="border:1px solid var(--border);border-radius:14px;overflow:hidden;">
        <div style="display:grid;grid-template-columns:repeat(7,1fr);background:var(--bg);border-bottom:1px solid var(--border);">
            @foreach(['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $day)
                <div style="padding:10px;text-align:center;font-size:11px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:var(--text-3);">{{ $day }}</div>
            @endforeach
        </div>

        @foreach($weeks as $week)
            <div style="display:grid;grid-template-columns:repeat(7,1fr);">
                @foreach($week as $day)
                    @php
                        $key = $day->format('Y-m-d');
                        $dayItems = $items[$key] ?? collect();
                        $isToday = $day->isToday();
                        $isThisMonth = $day->month === $month;
                    @endphp
                    <div style="min-height:120px;padding:8px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);background:{{ $isThisMonth ? 'white' : 'var(--bg)' }};">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px;">
                            <span style="font-size:12px;font-weight:{{ $isThisMonth ? '500' : '400' }};font-family:'DM Mono',monospace;color:{{ $isToday ? 'white' : ($isThisMonth ? 'var(--text-1)' : 'var(--text-3)') }};background:{{ $isToday ? 'var(--text-1)' : 'transparent' }};width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;">
                                {{ $day->day }}
                            </span>
                            <span style="font-size:10px;color:var(--text-3);font-family:'DM Mono',monospace;">
                                {{ $dayItems->count() > 0 ? $dayItems->count() . ' tâche(s)' : '' }}
                            </span>
                        </div>

                        @foreach($dayItems->take(4) as $item)
                            <a href="{{ route('boards.show', $item->group->board) }}"
                               style="display:block;font-size:11px;font-weight:500;padding:3px 7px;border-radius:5px;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-decoration:none;transition:opacity .15s;background:{{ match($item->status) {
                                    'done' => '#dcfce7',
                                    'progress' => '#e6f5fb',
                                    'blocked' => '#fee2e2',
                                    'ongoing' => '#fff4e5',
                                    default => '#f4f4f3'
                               } }};color:{{ match($item->status) {
                                    'done' => '#16a34a',
                                    'progress' => '#0091CD',
                                    'blocked' => '#dc2626',
                                    'ongoing' => '#ea580c',
                                    default => '#6b6b68'
                               } }};"
                               onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                                {{ $item->name }}
                            </a>
                        @endforeach

                        @if($dayItems->count() > 4)
                            <div style="font-size:11px;color:var(--blue);padding:1px 7px;font-weight:500;">+{{ $dayItems->count() - 4 }} autres</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
</x-app-layout>
