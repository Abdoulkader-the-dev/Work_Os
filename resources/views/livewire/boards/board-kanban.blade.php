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

    <div class="kanban-container" data-kanban-board data-kanban-component-id="{{ $this->getId() }}">

        @foreach($columns as $status => $col)
            <div class="kanban-col">

                {{-- Column header --}}
                <div class="kanban-col-header">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $col['color'] }};flex-shrink:0;"></div>
                    <span class="text-sm font-semibold" style="letter-spacing:-0.01em;">{{ $col['label'] }}</span>
                    <span class="kanban-col-count">
                        {{ ($items[$status] ?? collect())->count() }}
                    </span>
                    <a href="{{ route('boards.kanban', ['board' => $board, 'createTask' => 1, 'status' => $status]) }}"
                       class="icon-btn" style="margin-left:auto;width:22px;height:22px;border-radius:5px;background:white;"
                       title="Ajouter une tâche">
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                            <path d="M5 1v8M1 5h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </a>
                </div>

                {{-- Column items — SortableJS target --}}
                <div class="kanban-column-body kanban-column"
                    data-status="{{ $status }}"
                    id="col-{{ $status }}"
                    ondragover="window.kanbanDnD?.over(event)"
                    ondrop="window.kanbanDnD?.drop(event, this)">

                    @foreach(($items[$status] ?? collect()) as $item)
                        <div class="kanban-card"
                            data-item-id="{{ $item->id }}"
                            data-status="{{ $item->status }}"
                            wire:key="kanban-{{ $item->id }}"
                            draggable="true"
                            ondragstart="window.kanbanDnD?.start(event, this)"
                            ondragend="window.kanbanDnD?.end(event, this)"
                            onclick="if (this.dataset.dragging === 'true') { event.stopPropagation(); return false; }"
                            wire:click="openItemPanel({{ $item->id }})">

                            {{-- Row 1 : Priorité + Deadline --}}
                            <div class="flex-between" style="margin-bottom:8px;">
                                <div class="text-xs font-medium text-2" style="display:flex;align-items:center;gap:4px;">
                                    <span style="width:6px;height:6px;border-radius:50%;background:{{ match($item->priority ?? 'moyenne') {
                                        'critique' => '#8b5cf6',
                                        'haute'    => '#ef4444',
                                        'moyenne'  => '#FFD100',
                                        'basse'    => '#22c55e',
                                        default    => '#a3a39f'
                                    } }};"></span>
                                    {{ ucfirst($item->priority ?? 'Moyenne') }}
                                </div>
                                <div class="flex-center" style="gap:8px;flex-shrink:0;">
                                    @if($item->deadline)
                                        <span class="text-xs f-mono" style="font-size:10px;color:{{ $item->deadline->isPast() ? '#dc2626' : 'var(--text-3)' }};">
                                            {{ $item->deadline->format('d M') }}
                                        </span>
                                    @endif
                                    <div class="kanban-drag-handle text-3"
                                        title="Glisser pour changer de colonne"
                                        style="display:flex;align-items:center;justify-content:center;width:18px;height:18px;cursor:grab;flex-shrink:0;">
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
                            <div class="text-md font-medium text-1" style="letter-spacing:-0.01em;line-height:1.35;margin-bottom:8px;">
                                {{ $item->name }}
                            </div>
    
                            {{-- Row 3 : Livrable --}}
                            @if($item->deliverable)
                                <div class="text-xs text-3" style="margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $item->deliverable }}
                                </div>
                            @endif

                            {{-- Row 4 : Assignés + obstacle indicator --}}
                            <div class="flex-between">
                                <div style="display:flex;align-items:center;">
                                    @foreach($item->assignees->take(3) as $assignee)
                                        <div class="user-avatar text-xs font-semibold" 
                                             style="width:22px;height:22px;border:2px solid white;margin-left:-4px;"
                                            title="{{ $assignee->name }}">
                                            {{ strtoupper(substr($assignee->name, 0, 2)) }}
                                        </div>
                                    @endforeach
                                    @if($item->assignees->count() > 3)
                                        <div class="user-avatar text-xs font-semibold" 
                                             style="width:22px;height:22px;background:var(--bg);color:var(--text-3);border:2px solid white;margin-left:-4px;">
                                            +{{ $item->assignees->count() - 3 }}
                                        </div>
                                    @endif
                                </div>
                                @if($item->obstacles)
                                    <span class="badge s-ongoing" style="font-size:10px;">⚠</span>
                                @endif
                            </div>

                        </div>
                    @endforeach

                </div>

                {{-- Add item button --}}
                <a href="{{ route('boards.kanban', ['board' => $board, 'createTask' => 1, 'status' => $status]) }}"
                   class="kanban-add-task-inline"
                   onmouseover="this.style.borderColor='{{ $col['color'] }}';this.style.color='{{ $col['color'] }}';this.style.background='{{ $col['color'] }}11'"
                   onmouseout="this.style.borderColor='';this.style.color='';this.style.background=''">
                    + Ajouter une tâche
                </a>

            </div>
        @endforeach

    </div>
</div>

@push('scripts')
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
