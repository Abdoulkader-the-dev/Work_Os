{{-- resources/views/livewire/boards/board-calendar.blade.php --}}

@section('page-title', $board->name)

@section('view-switcher')
    <a class="view-btn" href="{{ route('boards.show', $board) }}">Tableau</a>
    <a class="view-btn" href="{{ route('boards.kanban', $board) }}">Kanban</a>
    <a class="view-btn active" href="{{ route('boards.calendar', $board) }}">Calendrier</a>
@endsection

@section('topbar-action')
    <a class="btn-primary" href="{{ route('boards.calendar', ['board' => $board, 'createTask' => 1, 'month' => $month, 'year' => $year]) }}">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M7 2v10M2 7h10" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        Nouvelle tâche
    </a>
@endsection

<div style="display:flex;flex-direction:column;gap:20px;">
    @include('livewire.boards.partials.create-task-panel', [
        'groups' => $groups,
        'taskContext' => [
            'show' => request()->boolean('createTask'),
            'open_url' => route('boards.calendar', ['board' => $board, 'createTask' => 1, 'month' => $month, 'year' => $year]),
            'cancel_url' => route('boards.calendar', ['board' => $board, 'month' => $month, 'year' => $year]),
            'view' => 'calendar',
            'group_id' => request('group'),
            'status' => request('status', 'todo'),
            'deadline' => request('date'),
            'month' => $month,
            'year' => $year,
        ],
    ])

    {{-- ── NAVIGATION ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:12px;">
            <button wire:click="prevMonth"
                    style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-2);transition:all .15s;"
                    onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='white'">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <h2 style="font-size:20px;font-weight:600;letter-spacing:-0.02em;min-width:200px;text-align:center;text-transform:capitalize;">
                {{ $monthLabel }}
            </h2>

            <button wire:click="nextMonth"
                    style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-2);transition:all .15s;"
                    onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='white'">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M5 3l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <button wire:click="goToday"
                style="font-size:12px;font-weight:500;font-family:'DM Sans',sans-serif;padding:6px 14px;border-radius:8px;border:1px solid var(--border);background:white;cursor:pointer;color:var(--text-2);transition:all .15s;"
                onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='white'">
            Aujourd'hui
        </button>
    </div>

    {{-- ── GRILLE ── --}}
    <div style="border:1px solid var(--border);border-radius:14px;overflow:hidden;">

        {{-- Headers jours --}}
        <div style="display:grid;grid-template-columns:repeat(7,1fr);background:var(--bg);border-bottom:1px solid var(--border);">
            @foreach(['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $day)
                <div style="padding:10px;text-align:center;font-size:11px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;color:var(--text-3);">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Semaines --}}
        @foreach($weeks as $week)
            <div style="display:grid;grid-template-columns:repeat(7,1fr);">
                @foreach($week as $day)
                    @php
                        $key         = $day->format('Y-m-d');
                        $dayItems    = $items[$key] ?? collect();
                        $isToday     = $day->isToday();
                        $isThisMonth = $day->month === $month;
                        $isWeekend   = $day->isWeekend();
                    @endphp

                    <div style="min-height:100px;padding:8px;
                                border-right:1px solid var(--border);
                                border-bottom:1px solid var(--border);
                                background:{{ !$isThisMonth ? 'var(--bg)' : ($isWeekend ? '#fafaf8' : 'white') }};
                                transition:background .15s;">

                        {{-- Numéro du jour --}}
                        <div style="margin-bottom:6px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                            @if($isToday)
                                <span style="display:inline-flex;width:26px;height:26px;border-radius:50%;background:var(--text-1);color:white;align-items:center;justify-content:center;font-size:12px;font-weight:600;font-family:'DM Mono',monospace;">
                                    {{ $day->day }}
                                </span>
                            @else
                                <span style="font-size:12px;font-weight:{{ $isThisMonth ? '500' : '400' }};font-family:'DM Mono',monospace;color:{{ $isThisMonth ? 'var(--text-1)' : 'var(--text-3)' }};">
                                    {{ $day->day }}
                                </span>
                            @endif
                                <a href="{{ route('boards.calendar', ['board' => $board, 'createTask' => 1, 'date' => $key, 'month' => $month, 'year' => $year]) }}"
                                   style="width:20px;height:20px;border-radius:6px;border:1px solid var(--border);background:white;display:flex;align-items:center;justify-content:center;color:var(--text-3);text-decoration:none;flex-shrink:0;"
                                   title="Ajouter une tâche à cette date">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                        <path d="M5 1v8M1 5h8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        {{-- Items du jour --}}
                        @foreach($dayItems->take(3) as $item)
                            <div style="font-size:11px;font-weight:500;padding:2px 7px;border-radius:4px;margin-bottom:3px;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:opacity .15s;
                                        background:{{ match($item->status) {
                                            'done'     => '#dcfce7',
                                            'progress' => '#e6f5fb',
                                            'blocked'  => '#fee2e2',
                                            'ongoing'  => '#fff4e5',
                                            default    => '#f4f4f3'
                                        } }};
                                        color:{{ match($item->status) {
                                            'done'     => '#16a34a',
                                            'progress' => '#0091CD',
                                            'blocked'  => '#dc2626',
                                            'ongoing'  => '#ea580c',
                                            default    => '#6b6b68'
                                        } }};"
                                 onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'"
                                 wire:click="openItemPanel({{ $item->id }})">
                                {{ $item->name }}
                            </div>
                        @endforeach

                        {{-- Overflow --}}
                        @if($dayItems->count() > 3)
                            <div style="font-size:11px;color:var(--blue);cursor:pointer;padding:1px 7px;font-weight:500;">
                                +{{ $dayItems->count() - 3 }} autres
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @endforeach

    </div>

    {{-- ── LÉGENDE ── --}}
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding-bottom:24px;">
        @foreach([
            'done'     => ['#dcfce7','#16a34a','Achevé'],
            'progress' => ['#e6f5fb','#0091CD','En cours'],
            'ongoing'  => ['#fff4e5','#ea580c','Continu'],
            'blocked'  => ['#fee2e2','#dc2626','Bloqué'],
            'todo'     => ['#f4f4f3','#6b6b68','Non commencé'],
        ] as $s => [$bg, $color, $label])
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-2);">
                <span style="width:10px;height:10px;border-radius:3px;background:{{ $bg }};display:inline-block;"></span>
                {{ $label }}
            </div>
        @endforeach
    </div>

</div>
