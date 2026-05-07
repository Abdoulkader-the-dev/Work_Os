{{-- resources/views/livewire/boards/board-kanban.blade.php --}}

@section('page-title', $board->name)

@section('view-switcher')
    <button class="view-btn" wire:navigate href="{{ route('boards.show', $board) }}">Tableau</button>
    <button class="view-btn active">Kanban</button>
    <button class="view-btn" wire:navigate href="{{ route('boards.calendar', $board) }}">Calendrier</button>
@endsection

<div style="display:flex;gap:16px;height:calc(100vh - 60px - 56px);overflow-x:auto;padding-bottom:16px;">

    @foreach($columns as $status => $col)
        <div style="display:flex;flex-direction:column;width:280px;flex-shrink:0;">

            {{-- Column header --}}
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;padding:0 2px;">
                <div style="width:8px;height:8px;border-radius:50%;background:{{ $col['color'] }};flex-shrink:0;"></div>
                <span style="font-size:13px;font-weight:600;letter-spacing:-0.01em;">{{ $col['label'] }}</span>
                <span style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);background:var(--bg);padding:1px 6px;border-radius:20px;">
                    {{ ($items[$status] ?? collect())->count() }}
                </span>
                <button wire:click="addItemToColumn('{{ $status }}')"
                        style="margin-left:auto;width:22px;height:22px;border-radius:5px;border:1px solid var(--border);background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-3);transition:all .15s;"
                        onmouseover="this.style.background='var(--bg)';this.style.color='var(--text-1)'" onmouseout="this.style.background='white';this.style.color='var(--text-3)'"
                        title="Ajouter une tâche">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M5 1v8M1 5h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>

            {{-- Column items (SortableJS target) --}}
            <div class="kanban-column"
                 data-status="{{ $status }}"
                 style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:10px;padding:2px;min-height:80px;border-radius:10px;transition:background .15s;"
                 id="col-{{ $status }}">

                @foreach(($items[$status] ?? collect()) as $item)
                    <div class="kanban-card"
                         data-item-id="{{ $item->id }}"
                         wire:key="kanban-{{ $item->id }}"
                         style="background:white;border:1px solid var(--border);border-radius:12px;padding:14px;cursor:pointer;
                                transition:transform .2s cubic-bezier(0.34,1.56,0.64,1), box-shadow .2s, border-color .15s;"
                         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 20px rgba(0,0,0,0.08)';this.style.borderColor='var(--border-md)'"
                         onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='var(--border)'"
                         wire:click="openItemPanel({{ $item->id }})">

                        {{-- Row 1 : Priorité + Deadline --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                            <div style="display:flex;align-items:center;gap:4px;font-size:11px;font-weight:500;color:var(--text-2);">
                                <span style="width:6px;height:6px;border-radius:50%;background:{{ match($item->priority ?? 'moyenne') {'critique'=>'#8b5cf6','haute'=>'#ef4444','moyenne'=>'#FFD100','basse'=>'#22c55e',default=>'#a3a39f'} }};"></span>
                                {{ ucfirst($item->priority ?? 'Moyenne') }}
                            </div>
                            @if($item->deadline)
                                <span style="font-size:10px;font-family:'DM Mono',monospace;color:{{ $item->deadline->isPast() ? '#dc2626' : 'var(--text-3)' }};">
                                    {{ $item->deadline->format('d M') }}
                                </span>
                            @endif
                        </div>

                        {{-- Row 2 : Titre --}}
                        <div style="font-size:14px;font-weight:500;letter-spacing:-0.01em;line-height:1.35;margin-bottom:8px;color:var(--text-1);">
                            {{ $item->name }}
                        </div>

                        {{-- Row 3 : Livrable (si existe) --}}
                        @if($item->deliverable)
                            <div style="font-size:12px;color:var(--text-3);margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $item->deliverable }}
                            </div>
                        @endif

                        {{-- Row 4 : Assignés + obstacles indicator --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;">
                            <div style="display:flex;align-items:center;">
                                @foreach($item->assignees->take(3) as $assignee)
                                    <div style="width:22px;height:22px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:600;border:2px solid white;margin-left:-4px;first:margin-left:0;"
                                         title="{{ $assignee->name }}">
                                        {{ strtoupper(substr($assignee->name, 0, 2)) }}
                                    </div>
                                @endforeach
                                @if($item->assignees->count() > 3)
                                    <div style="width:22px;height:22px;border-radius:50%;background:var(--bg);color:var(--text-3);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:600;border:2px solid white;margin-left:-4px;">
                                        +{{ $item->assignees->count() - 3 }}
                                    </div>
                                @endif
                            </div>
                            @if($item->obstacles)
                                <span style="font-size:10px;font-weight:500;padding:2px 6px;border-radius:20px;background:#fff4e5;color:#ea580c;">⚠</span>
                            @endif
                        </div>

                    </div>
                @endforeach

            </div>

            {{-- Add item button --}}
            <button wire:click="addItemToColumn('{{ $status }}')"
                    style="margin-top:8px;padding:10px;border:2px dashed var(--border);border-radius:10px;background:none;font-size:12px;color:var(--text-3);cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s;width:100%;"
                    onmouseover="this.style.borderColor='{{ $col['color'] }}';this.style.color='{{ $col['color'] }}';this.style.background='{{ $col['color'] }}11'"
                    onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-3)';this.style.background='none'">
                + Ajouter une tâche
            </button>

        </div>
    @endforeach

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
(function initKanban() {
    function setupSortable() {
        document.querySelectorAll('.kanban-column').forEach(col => {
            if (col._sortable) col._sortable.destroy();
            col._sortable = Sortable.create(col, {
                group:       'kanban',
                animation:   180,
                delay:       80,
                delayOnTouchOnly: true,
                ghostClass:  'kanban-ghost',
                dragClass:   'kanban-dragging',
                onStart() {
                    document.querySelectorAll('.kanban-column').forEach(c => {
                        c.style.background = 'rgba(0,0,0,0.02)';
                        c.style.borderRadius = '10px';
                    });
                },
                onEnd(evt) {
                    document.querySelectorAll('.kanban-column').forEach(c => {
                        c.style.background = '';
                    });
                    const itemId = parseInt(evt.item.dataset.itemId);
                    const status = evt.to.dataset.status;
                    const order  = evt.newIndex;
                    if (!itemId || !status) return;
                    Livewire.dispatch('item-moved', { itemId, status, order });
                }
            });
        });
    }

    // Init au chargement
    document.addEventListener('livewire:initialized', setupSortable);
    // Ré-init après chaque re-render Livewire
    document.addEventListener('livewire:updated', setupSortable);
})();
</script>
<style>
.kanban-ghost {
    opacity: 0.4;
    transform: scale(0.97);
    border: 2px dashed var(--border-md) !important;
    background: var(--bg) !important;
}
.kanban-dragging {
    opacity: 0.95;
    transform: rotate(2deg) scale(1.02);
    box-shadow: 0 20px 48px rgba(0,0,0,0.18) !important;
    border-color: var(--blue) !important;
    cursor: grabbing;
}
</style>
@endpush