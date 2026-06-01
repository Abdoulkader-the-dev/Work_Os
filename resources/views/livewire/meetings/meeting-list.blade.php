{{-- resources/views/livewire/meetings/meeting-list.blade.php --}}

@section('page-title', 'Réunions')

@section('topbar-action')
    <a href="{{ route('meetings.create') }}" class="btn-primary" wire:navigate>
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M7 2v10M2 7h10" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        Nouveau CR
    </a>
@endsection

<div style="display:flex;flex-direction:column;gap:20px;">
    <div wire:loading.flex style="align-items:center;gap:8px;color:var(--text-3);font-size:13px;">
        <svg class="animate-spin" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.5" stroke-dasharray="16" stroke-linecap="round" opacity=".35"/>
            <path d="M7 2a5 5 0 015 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        Chargement des comptes rendus...
    </div>

    {{-- ── TOOLBAR ── --}}
    <div class="flex-between flex-wrap" style="gap:12px;">

        {{-- Search --}}
        <div class="search-box" style="flex:1;min-width:220px;max-width:360px;">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="color:var(--text-3);flex-shrink:0;">
                <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.4"/>
                <path d="M9.5 9.5L13 13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher un CR...">
        </div>

        {{-- Sort --}}
        <div class="flex-center" style="gap:6px;">
            <span class="text-xs text-3">Trier par</span>
            <button wire:click="sortBy('date')"
                    class="view-btn {{ $sortField === 'date' ? 'active' : '' }}"
                    style="border:1px solid {{ $sortField === 'date' ? 'var(--blue)' : 'var(--border)' }};">
                Date
                @if($sortField === 'date')
                    {{ $sortDir === 'asc' ? '↑' : '↓' }}
                @endif
            </button>
            <button wire:click="sortBy('title')"
                    class="view-btn {{ $sortField === 'title' ? 'active' : '' }}"
                    style="border:1px solid {{ $sortField === 'title' ? 'var(--blue)' : 'var(--border)' }};">
                Titre
                @if($sortField === 'title')
                    {{ $sortDir === 'asc' ? '↑' : '↓' }}
                @endif
            </button>
        </div>

        {{-- Count --}}
        <span class="text-xs text-3 f-mono" style="margin-left:auto;">
            {{ $meetings->total() }} CR
        </span>
    </div>

    {{-- ── GRILLE DES CR ── --}}
    @if($meetings->isEmpty())
        <div style="text-align:center;padding:64px 24px;">
            <div style="font-size:40px;margin-bottom:12px;">📋</div>
            <div class="text-md font-semibold text-1" style="margin-bottom:6px;">Aucun compte rendu</div>
            <div class="text-sm text-3" style="margin-bottom:20px;">
                {{ $search ? 'Aucun résultat pour "' . $search . '"' : 'Créez votre premier CR de réunion.' }}
            </div>
            @if(!$search)
                <a href="{{ route('meetings.create') }}" class="btn-primary" wire:navigate>
                    + Nouveau CR
                </a>
            @endif
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;">
            @foreach($meetings as $meeting)
                <div class="bento-card" style="padding:20px;display:flex;flex-direction:column;gap:14px;" wire:key="meeting-{{ $meeting->id }}">

                    {{-- Header card --}}
                    <div class="flex-between" style="align-items:flex-start;gap:10px;">
                        <div style="flex:1;min-width:0;">
                            <a href="{{ route('meetings.show', $meeting) }}" wire:navigate
                               class="text-md font-semibold text-1"
                               style="text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:color .15s;"
                               onmouseover="this.style.color='var(--blue)'" onmouseout="this.style.color=''">
                                {{ $meeting->title }}
                            </a>
                            <div class="text-xs text-3 f-mono" style="margin-top:4px;">
                                {{ $meeting->date->isoFormat('ddd D MMM YYYY') }}
                            </div>
                        </div>

                        {{-- Menu ··· --}}
                        <div style="position:relative;flex-shrink:0;">
                            <button type="button"
                                    class="icon-btn"
                                    data-dropdown-trigger="meeting-menu-{{ $meeting->id }}"
                                    aria-controls="meeting-menu-{{ $meeting->id }}"
                                    aria-expanded="false"
                                    style="width:28px;height:28px;border:none;background:none;">···</button>
                            <div id="meeting-menu-{{ $meeting->id }}"
                                 data-dropdown-menu
                                 hidden
                                 style="position:absolute;right:0;top:calc(100%+4px);background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;min-width:150px;z-index:40;">
                                <a href="{{ route('meetings.show', $meeting) }}" wire:navigate
                                   style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;color:var(--text-2);text-decoration:none;transition:background .12s;"
                                   onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M1 6s2-4 5-4 5 4 5 4-2 4-5 4-5-4-5-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="6" cy="6" r="1.5" stroke="currentColor" stroke-width="1.2"/></svg>
                                    Voir
                                </a>
                                <a href="{{ route('meetings.edit', $meeting) }}" wire:navigate
                                   style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;color:var(--text-2);text-decoration:none;transition:background .12s;"
                                   onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M8.5 2.5l1 1L4 9H3V8l5.5-5.5z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Modifier
                                </a>
                                <div style="height:1px;background:var(--border);margin:3px 0;"></div>
                                <button wire:click="deleteMeeting({{ $meeting->id }})"
                                        wire:confirm="Supprimer ce compte rendu ?"
                                        style="width:100%;display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;color:#dc2626;background:none;border:none;font-family:'DM Sans',sans-serif;cursor:pointer;transition:background .12s;"
                                        onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background=''">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 3h8M5 3V2h2v1M4 3v7h4V3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Participants --}}
                    @if(!empty($meeting->attendees))
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            @foreach(array_slice($meeting->attendees, 0, 4) as $attendee)
                                <span class="badge" style="background:var(--bg);color:var(--text-2);">
                                    {{ $attendee }}
                                </span>
                            @endforeach
                            @if(count($meeting->attendees) > 4)
                                <span class="text-xs text-3 f-mono">
                                    +{{ count($meeting->attendees) - 4 }}
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Stats actions --}}
                    @php
                        $actions    = collect($meeting->actions ?? []);
                        $totalAct   = $actions->where('text', '!=', '')->count();
                        $converted  = $actions->where('converted', true)->count();
                    @endphp
                    @if($totalAct > 0)
                        <div>
                            <div class="flex-between" style="margin-bottom:5px;">
                                <span class="text-xs text-3">
                                    {{ $converted }}/{{ $totalAct }} actions converties
                                </span>
                                <span class="text-xs f-mono" style="color:{{ $converted === $totalAct ? '#16a34a' : 'var(--text-3)' }};">
                                    {{ $totalAct > 0 ? round($converted / $totalAct * 100) : 0 }}%
                                </span>
                            </div>
                            <div style="height:3px;background:var(--bg);border-radius:10px;overflow:hidden;">
                                <div style="height:100%;background:{{ $converted === $totalAct ? '#22c55e' : 'var(--blue)' }};border-radius:10px;width:{{ $totalAct > 0 ? round($converted / $totalAct * 100) : 0 }}%;transition:width .4s;"></div>
                            </div>
                        </div>
                    @endif

                    {{-- Footer card --}}
                    <div class="flex-between" style="padding-top:10px;border-top:1px solid var(--border);">
                        <div style="display:flex;gap:12px;">
                            <span class="text-xs text-3" style="display:flex;align-items:center;gap:4px;">
                                <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M1 5.5l2.5 2.5L10 2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                                {{ count($meeting->bilan ?? []) }} bilans
                            </span>
                            <span class="text-xs text-3" style="display:flex;align-items:center;gap:4px;">
                                <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><circle cx="5.5" cy="5.5" r="4" stroke="currentColor" stroke-width="1.1"/><path d="M3.5 5.5h4M5.5 3.5v4" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/></svg>
                                {{ $totalAct }} actions
                            </span>
                        </div>
                        <a href="{{ route('meetings.show', $meeting) }}" wire:navigate
                           class="text-sm font-medium" style="color:var(--blue);text-decoration:none;display:flex;align-items:center;gap:3px;transition:gap .15s;"
                           onmouseover="this.style.gap='6px'" onmouseout="this.style.gap='3px'">
                            Ouvrir <span>→</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($meetings->hasPages())
            <div class="flex-center" style="padding-top:8px;">
                {{ $meetings->links() }}
            </div>
        @endif
    @endif

</div>
