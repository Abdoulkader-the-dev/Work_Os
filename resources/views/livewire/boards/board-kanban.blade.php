{{-- resources/views/livewire/boards/board-kanban.blade.php --}}

@section('page-title', $board->name)

@section('view-switcher')
    <a class="view-btn" href="{{ route('boards.show', $board) }}">Tableau</a>
    <a class="view-btn active" href="{{ route('boards.kanban', $board) }}">Kanban</a>
    <a class="view-btn" href="{{ route('boards.calendar', $board) }}">Calendrier</a>
@endsection

@section('topbar-action')
    <a class="btn-primary" href="{{ route('boards.kanban', ['board' => $board, 'createTask' => 1]) }}">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M7 2v10M2 7h10" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        Nouvelle tâche
    </a>
@endsection

<div style="display:flex;flex-direction:column;gap:16px;">
    @include('livewire.boards.partials.create-task-panel', [
        'groups' => $groups,
        'taskContext' => [
            'show' => request()->boolean('createTask'),
            'open_url' => route('boards.kanban', ['board' => $board, 'createTask' => 1]),
            'cancel_url' => route('boards.kanban', $board),
            'view' => 'kanban',
            'group_id' => request('group'),
            'status' => request('status', 'todo'),
            'deadline' => request('date'),
        ],
    ])

    <div data-kanban-board data-kanban-component-id="{{ $this->getId() }}" style="display:flex;gap:16px;height:calc(100vh - 60px - 56px);overflow-x:auto;padding-bottom:16px;">

        @foreach($columns as $status => $col)
            <div style="display:flex;flex-direction:column;width:280px;flex-shrink:0;">

                {{-- Column header --}}
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;padding:0 2px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $col['color'] }};flex-shrink:0;"></div>
                    <span style="font-size:13px;font-weight:600;letter-spacing:-0.01em;">{{ $col['label'] }}</span>
                    <span style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);background:var(--bg);padding:1px 6px;border-radius:20px;">
                        {{ ($items[$status] ?? collect())->count() }}
                    </span>
                    <a href="{{ route('boards.kanban', ['board' => $board, 'createTask' => 1, 'status' => $status]) }}"
                    style="margin-left:auto;width:22px;height:22px;border-radius:5px;border:1px solid var(--border);background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-3);transition:all .15s;text-decoration:none;"
                    onmouseover="this.style.background='var(--bg)';this.style.color='var(--text-1)'"
                    onmouseout="this.style.background='white';this.style.color='var(--text-3)'"
                    title="Ajouter une tâche">
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                            <path d="M5 1v8M1 5h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </a>
                </div>

                {{-- Column items — SortableJS target --}}
                <div class="kanban-column"
                    data-status="{{ $status }}"
                    id="col-{{ $status }}"
                    ondragover="window.kanbanDnD?.over(event)"
                    ondrop="window.kanbanDnD?.drop(event, this)"
                    style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:10px;padding:2px;min-height:80px;border-radius:10px;transition:background .15s;">

                    @foreach(($items[$status] ?? collect()) as $item)
                        <div class="kanban-card"
                            data-item-id="{{ $item->id }}"
                            data-status="{{ $item->status }}"
                            wire:key="kanban-{{ $item->id }}"
                            draggable="true"
                            ondragstart="window.kanbanDnD?.start(event, this)"
                            ondragend="window.kanbanDnD?.end(event, this)"
                            style="background:white;border:1px solid var(--border);border-radius:12px;padding:14px;cursor:pointer;
                                transition:transform .2s cubic-bezier(0.34,1.56,0.64,1),box-shadow .2s,border-color .15s;"
                            onclick="if (this.dataset.dragging === 'true') { event.stopPropagation(); return false; }"
                            onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 20px rgba(0,0,0,0.08)';this.style.borderColor='var(--border-md)'"
                            onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='var(--border)'"
                            wire:click="openItemPanel({{ $item->id }})">

                            {{-- Row 1 : Priorité + Deadline --}}
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                <div style="display:flex;align-items:center;gap:4px;font-size:11px;font-weight:500;color:var(--text-2);">
                                    <span style="width:6px;height:6px;border-radius:50%;background:{{ match($item->priority ?? 'moyenne') {
                                        'critique' => '#8b5cf6',
                                        'haute'    => '#ef4444',
                                        'moyenne'  => '#FFD100',
                                        'basse'    => '#22c55e',
                                        default    => '#a3a39f'
                                    } }};"></span>
                                    {{ ucfirst($item->priority ?? 'Moyenne') }}
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                    @if($item->deadline)
                                        <span style="font-size:10px;font-family:'DM Mono',monospace;color:{{ $item->deadline->isPast() ? '#dc2626' : 'var(--text-3)' }};">
                                            {{ $item->deadline->format('d M') }}
                                        </span>
                                    @endif
                                    <div class="kanban-drag-handle"
                                        title="Glisser pour changer de colonne"
                                        style="display:flex;align-items:center;justify-content:center;width:18px;height:18px;color:var(--text-3);cursor:grab;flex-shrink:0;">
                                        <svg width="10" height="14" viewBox="0 0 10 14" fill="none" aria-hidden="true">
                                            <circle cx="3" cy="3" r="1" fill="currentColor"/>
                                            <circle cx="7" cy="3" r="1" fill="currentColor"/>
                                            <circle cx="3" cy="7" r="1" fill="currentColor"/>
                                            <circle cx="7" cy="7" r="1" fill="currentColor"/>
                                            <circle cx="3" cy="11" r="1" fill="currentColor"/>
                                            <circle cx="7" cy="11" r="1" fill="currentColor"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
    
                            {{-- Row 2 : Titre --}}
                            <div style="font-size:14px;font-weight:500;letter-spacing:-0.01em;line-height:1.35;margin-bottom:8px;color:var(--text-1);">
                                {{ $item->name }}
                            </div>
    
                            {{-- Row 3 : Livrable --}}
                            @if($item->deliverable)
                                <div style="font-size:12px;color:var(--text-3);margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $item->deliverable }}
                                </div>
                            @endif

                            {{-- Row 4 : Assignés + obstacle indicator --}}
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <div style="display:flex;align-items:center;">
                                    @foreach($item->assignees->take(3) as $assignee)
                                        <div style="width:22px;height:22px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:600;border:2px solid white;margin-left:-4px;"
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
                <a href="{{ route('boards.kanban', ['board' => $board, 'createTask' => 1, 'status' => $status]) }}"
                style="margin-top:8px;padding:10px;border:2px dashed var(--border);border-radius:10px;background:none;font-size:12px;color:var(--text-3);cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s;width:100%;text-decoration:none;display:block;text-align:center;"
                onmouseover="this.style.borderColor='{{ $col['color'] }}';this.style.color='{{ $col['color'] }}';this.style.background='{{ $col['color'] }}11'"
                onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-3)';this.style.background='none'">
                    + Ajouter une tâche
                </a>

            </div>
        @endforeach

    </div>
</div>

@push('scripts')
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
    cursor: grabbing;
    z-index: 999;
}
</style>
<script>
window.kanbanDnD = window.kanbanDnD || {
    draggedCard: null,
    sourceColumn: null,

    getColumnIds(column) {
        return Array.from(column.querySelectorAll('.kanban-card'))
            .map((card) => parseInt(card.dataset.itemId, 10))
            .filter(Boolean);
    },

    start(event, card) {
        this.draggedCard = card;
        this.sourceColumn = card.closest('.kanban-column');
        card.dataset.dragging = 'true';
        card.classList.add('kanban-dragging');

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.dataset.itemId || '');
        }

        document.querySelectorAll('.kanban-column').forEach((column) => {
            column.style.background = 'rgba(0,0,0,0.02)';
            column.style.borderRadius = '10px';
        });
    },

    over(event) {
        event.preventDefault();
        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = 'move';
        }
    },

    drop(event, targetColumn) {
        event.preventDefault();

        const card = this.draggedCard;
        const sourceColumn = this.sourceColumn;

        if (!card || !sourceColumn || !targetColumn) return;

        targetColumn.appendChild(card);

        const itemId = parseInt(card.dataset.itemId || '', 10);
        const status = targetColumn.dataset.status;
        const targetIds = this.getColumnIds(targetColumn);
        const sourceIds = sourceColumn === targetColumn ? [] : this.getColumnIds(sourceColumn);
        const board = targetColumn.closest('[data-kanban-board]');
        const componentId = board?.getAttribute('data-kanban-component-id');
        const component = componentId && window.Livewire ? window.Livewire.find(componentId) : null;

        card.dataset.status = status;

        if (component && itemId && status) {
            component.call('moveItem', itemId, status, targetIds, sourceIds);
        }

        this.end(null, card);
    },

    end(_event, card) {
        document.querySelectorAll('.kanban-column').forEach((column) => {
            column.style.background = '';
        });

        if (card) {
            card.classList.remove('kanban-dragging');
            setTimeout(() => {
                delete card.dataset.dragging;
            }, 0);
        }

        this.draggedCard = null;
        this.sourceColumn = null;
    }
};
</script>
@endpush
