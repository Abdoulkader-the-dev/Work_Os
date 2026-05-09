{{-- resources/views/pages/dashboard.blade.php --}}
<x-app-layout>
@section('page-title', 'Dashboard')

@php
    $weeklyActivity = 0;
    $weeklyHours    = '00:00:00';
    $activeBoards   = \App\Models\Board::count();
    $recentActivity = [];
    $boards         = \App\Models\Board::take(5)->get();
    $urgentTasks    = \App\Models\Item::where('status', 'blocked')
                        ->orWhere('priority', 'critique')
                        ->with('group.board')
                        ->take(6)
                        ->get();
    $urgentCount    = $urgentTasks->count();
    $members        = \App\Models\User::take(5)->get();
@endphp

<div style="display:flex;flex-direction:column;gap:24px;">

    {{-- ══ HEADER BANNER ══ --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">

        <div>
            <h2 style="font-size:28px;font-weight:600;letter-spacing:-0.03em;line-height:1;">
                Aujourd'hui
            </h2>
            <p style="font-size:13px;color:var(--color-text-3);font-family:'DM Mono',monospace;margin-top:6px;">
                {{ now()->isoFormat('ddd D MMM · HH:mm') }}
            </p>
        </div>

        {{-- Time tracker --}}
        <div class="bento-card"
             style="display:flex;align-items:center;gap:16px;padding:14px 20px;cursor:pointer;"
             x-data="timeTracker()"
             @click="toggle()">
            <div>
                <div style="font-size:13px;font-weight:500;">Démarrer le chrono</div>
                <div style="font-size:18px;font-weight:600;font-family:'DM Mono',monospace;letter-spacing:-0.02em;margin-top:2px;color:var(--color-text-1);"
                     x-text="display">00:00:00</div>
            </div>
            <div style="width:42px;height:42px;background:var(--color-unipod-yellow);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:transform .2s;flex-shrink:0;"
                 :style="running ? 'transform:scale(1.08)' : ''">
                <svg x-show="!running" width="16" height="16" viewBox="0 0 16 16" fill="none" style="margin-left:2px;">
                    <path d="M5 3l9 5-9 5V3z" fill="#111110"/>
                </svg>
                <svg x-show="running" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <rect x="3" y="3" width="4" height="10" rx="1" fill="#111110"/>
                    <rect x="9" y="3" width="4" height="10" rx="1" fill="#111110"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ══ STATS ROW ══ --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">

        {{-- Activité hebdo --}}
        <div class="bento-card" style="padding:20px;cursor:pointer;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <span style="font-size:13px;font-weight:500;color:var(--color-text-2);">Activité hebdo</span>
                <button style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;border-radius:4px;border:none;background:none;color:var(--color-text-3);cursor:pointer;font-size:16px;line-height:1;">···</button>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:flex-end;">
                <div>
                    <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;line-height:1;">
                        {{ $weeklyActivity }}%
                    </div>
                    <div style="font-size:11px;color:var(--color-text-3);font-family:'DM Mono',monospace;margin-top:6px;">
                        vs semaine passée
                    </div>
                </div>
                <div style="width:44px;height:44px;background:var(--color-unipod-yellow-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" style="color:var(--color-unipod-yellow);">
                        <path d="M4 8l4 8 5-12 4 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Heures travaillées --}}
        <div class="bento-card" style="padding:20px;cursor:pointer;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <span style="font-size:13px;font-weight:500;color:var(--color-text-2);">Temps travaillé</span>
                <button style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;border-radius:4px;border:none;background:none;color:var(--color-text-3);cursor:pointer;font-size:16px;line-height:1;">···</button>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:flex-end;">
                <div>
                    <div style="font-size:28px;font-weight:600;letter-spacing:-0.03em;line-height:1;font-family:'DM Mono',monospace;">
                        {{ $weeklyHours }}
                    </div>
                    <div style="font-size:11px;color:var(--color-text-3);font-family:'DM Mono',monospace;margin-top:6px;">cette semaine</div>
                </div>
                <div style="width:44px;height:44px;background:var(--color-unipod-yellow-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" style="color:var(--color-unipod-yellow);">
                        <circle cx="11" cy="11" r="9" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M11 6v5.5l3.5 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Projets actifs --}}
        <div class="bento-card" style="padding:20px;cursor:pointer;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <span style="font-size:13px;font-weight:500;color:var(--color-text-2);">Projets actifs</span>
                <button style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;border-radius:4px;border:none;background:none;color:var(--color-text-3);cursor:pointer;font-size:16px;line-height:1;">···</button>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:flex-end;">
                <div>
                    <div style="font-size:32px;font-weight:600;letter-spacing:-0.04em;line-height:1;">
                        {{ str_pad($activeBoards, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    <div style="font-size:11px;color:var(--color-text-3);font-family:'DM Mono',monospace;margin-top:6px;">en cours</div>
                </div>
                <div style="width:44px;height:44px;background:var(--color-unipod-yellow-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" style="color:var(--color-unipod-yellow);">
                        <path d="M3 5a2 2 0 012-2h3.586a1 1 0 01.707.293L10.707 5H17a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ MAIN GRID ══ --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

        {{-- Activité récente --}}
        <div class="bento-card" style="padding:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <span style="font-size:14px;font-weight:600;letter-spacing:-0.01em;">Activité récente</span>
                <a href="#" style="font-size:12px;color:var(--color-unipod-blue);text-decoration:none;font-weight:500;">
                    Voir tout
                </a>
            </div>
            <div style="display:flex;flex-direction:column;">
                @forelse($recentActivity as $event)
                    <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid var(--color-border);">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--color-text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                            {{ strtoupper(substr($event['user'] ?? 'U', 0, 2)) }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:500;">{{ $event['user'] ?? '' }}</div>
                            <div style="font-size:12px;color:var(--color-text-2);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $event['action'] ?? '' }}
                            </div>
                            <div style="font-size:11px;color:var(--color-text-3);font-family:'DM Mono',monospace;margin-top:2px;">
                                {{ $event['time'] ?? '' }}
                            </div>
                        </div>
                        @if($event['status'] ?? false)
                            <span class="s-{{ $event['status'] }}" style="font-size:11px;font-weight:500;padding:2px 8px;border-radius:20px;white-space:nowrap;flex-shrink:0;">
                                {{ ucfirst($event['status']) }}
                            </span>
                        @endif
                    </div>
                @empty
                    <div style="text-align:center;padding:32px 0;">
                        <div style="font-size:28px;margin-bottom:8px;">📋</div>
                        <div style="font-size:13px;color:var(--color-text-3);">Aucune activité récente</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Boards actifs --}}
        <div class="bento-card" style="padding:20px;display:flex;flex-direction:column;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <span style="font-size:14px;font-weight:600;letter-spacing:-0.01em;">Boards</span>
                <a href="{{ route('boards.index') }}"
                   style="font-size:12px;font-weight:500;color:var(--color-text-2);text-decoration:none;display:flex;align-items:center;gap:4px;padding:5px 10px;border-radius:6px;border:1px solid var(--color-border);transition:all .15s;"
                   onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    + Nouveau
                </a>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;gap:2px;">
                @forelse($boards as $board)
                    <a href="{{ route('boards.show', $board) }}"
                       style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;text-decoration:none;color:inherit;transition:background .15s;"
                       onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $board->color ?? '#0091CD' }};flex-shrink:0;"></div>
                        <span style="flex:1;font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $board->name }}
                        </span>
                        <div style="width:80px;height:3px;background:var(--color-bg);border-radius:10px;flex-shrink:0;overflow:hidden;">
                            <div style="height:100%;background:var(--color-unipod-blue);border-radius:10px;width:{{ $board->progress }}%;transition:width .4s;"></div>
                        </div>
                        <span style="font-size:11px;font-family:'DM Mono',monospace;color:var(--color-text-3);flex-shrink:0;">
                            {{ $board->items_count }}
                        </span>
                    </a>
                @empty
                    <div style="text-align:center;padding:24px 0;color:var(--color-text-3);font-size:13px;">
                        Aucun board actif
                    </div>
                @endforelse
            </div>
            <a href="{{ route('boards.index') }}"
               style="display:block;margin-top:14px;padding:9px;background:var(--color-text-1);color:#fff;text-align:center;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;transition:background .15s;"
               onmouseover="this.style.background='#2a2a28'" onmouseout="this.style.background='var(--color-text-1)'">
                Voir tous les boards
            </a>
        </div>

    </div>

    {{-- ══ TÂCHES URGENTES ══ --}}
    <div class="bento-card" style="padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:14px;font-weight:600;letter-spacing:-0.01em;">Tâches urgentes</span>
                @if($urgentCount > 0)
                    <span style="background:#fee2e2;color:#dc2626;font-size:10px;font-weight:600;font-family:'DM Mono',monospace;padding:2px 7px;border-radius:20px;">
                        {{ $urgentCount }} bloquées
                    </span>
                @endif
            </div>
            <a href="{{ route('my-tasks') }}" style="font-size:12px;color:var(--color-unipod-blue);text-decoration:none;font-weight:500;">
                Voir tout →
            </a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px;">
            @forelse($urgentTasks as $task)
                <div style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:var(--color-bg);border-radius:10px;cursor:pointer;transition:background .15s;"
                     onmouseover="this.style.background='#eaeae8'" onmouseout="this.style.background='var(--color-bg)'">
                    <div style="width:6px;height:6px;border-radius:50%;margin-top:5px;flex-shrink:0;
                        background:{{ $task->status === 'blocked' ? '#dc2626' : ($task->priority === 'critique' ? '#8b5cf6' : '#f97316') }};"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $task->name }}
                        </div>
                        <div style="font-size:11px;color:var(--color-text-3);font-family:'DM Mono',monospace;margin-top:3px;">
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
                </div>
            @empty
                <div style="grid-column:1/-1;text-align:center;padding:16px;color:var(--color-text-3);font-size:13px;">
                    ✅ Aucune tâche urgente — beau travail !
                </div>
            @endforelse
        </div>
    </div>

    {{-- ══ MEMBRES ══ --}}
    <div class="bento-card" style="padding:20px;margin-bottom:40px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:14px;font-weight:600;letter-spacing:-0.01em;">Membres</span>
                <span style="font-size:11px;font-family:'DM Mono',monospace;color:var(--color-text-3);background:var(--color-bg);padding:1px 7px;border-radius:20px;">
                    {{ $members->count() }}
                </span>
            </div>
            <a href="{{ route('members') }}" style="font-size:12px;color:var(--color-unipod-blue);text-decoration:none;font-weight:500;">
                Gérer →
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;font-size:11px;font-weight:500;color:var(--color-text-3);letter-spacing:.05em;text-transform:uppercase;padding:0 10px 10px 0;">Membre</th>
                        <th style="text-align:left;font-size:11px;font-weight:500;color:var(--color-text-3);letter-spacing:.05em;text-transform:uppercase;padding:0 10px 10px;">Tâche en cours</th>
                        <th style="text-align:center;font-size:11px;font-weight:500;color:var(--color-text-3);letter-spacing:.05em;text-transform:uppercase;padding:0 10px 10px;">Aujourd'hui</th>
                        <th style="text-align:center;font-size:11px;font-weight:500;color:var(--color-text-3);letter-spacing:.05em;text-transform:uppercase;padding:0 0 10px;">Cette semaine</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr style="transition:background .15s;cursor:pointer;"
                            onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                            <td style="padding:10px 10px 10px 0;border-top:1px solid var(--color-border);">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:var(--color-text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                                        {{ strtoupper(substr($member->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="font-size:13px;font-weight:500;">{{ $member->name }}</div>
                                        <div style="font-size:11px;color:var(--color-text-3);">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:10px;border-top:1px solid var(--color-border);font-size:13px;color:var(--color-text-2);">
                                @php $currentTask = $member->items()->where('status', 'progress')->first(); @endphp
                                {{ $currentTask?->name ?? '—' }}
                            </td>
                            <td style="padding:10px;border-top:1px solid var(--color-border);text-align:center;font-size:12px;font-family:'DM Mono',monospace;color:var(--color-text-2);">
                                00:00
                            </td>
                            <td style="padding:10px 0 10px 10px;border-top:1px solid var(--color-border);text-align:center;font-size:12px;font-family:'DM Mono',monospace;color:var(--color-text-2);">
                                00:00
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:24px;text-align:center;color:var(--color-text-3);font-size:13px;border-top:1px solid var(--color-border);">
                                Aucun membre dans ce workspace
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
function timeTracker() {
    return {
        running: false,
        seconds: 0,
        timer:   null,
        get display() {
            const h = String(Math.floor(this.seconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((this.seconds % 3600) / 60)).padStart(2, '0');
            const s = String(this.seconds % 60).padStart(2, '0');
            return `${h}:${m}:${s}`;
        },
        toggle() {
            this.running = !this.running;
            if (this.running) {
                this.timer = setInterval(() => this.seconds++, 1000);
            } else {
                clearInterval(this.timer);
            }
        }
    }
}
</script>
@endpush

</x-app-layout>