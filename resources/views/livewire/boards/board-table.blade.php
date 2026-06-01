{{-- resources/views/livewire/boards/board-table.blade.php --}}

@section('page-title', $board->name)

@section('view-switcher')
    <a class="view-btn active" href="{{ route('boards.show', $board) }}">Tableau</a>
    <a class="view-btn" href="{{ route('boards.kanban', $board) }}">Kanban</a>
    <a class="view-btn" href="{{ route('boards.calendar', $board) }}">Calendrier</a>
@endsection

@section('topbar-action')
    <div style="display:flex;align-items:center;gap:8px;" data-tour-id="board-create-actions">
        <a class="btn-primary" href="{{ route('boards.show', ['board' => $board, 'createTask' => 1]) }}">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M7 2v10M2 7h10" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            Nouvelle tâche
        </a>
            <a href="{{ route('boards.show', ['board' => $board, 'createGroup' => 1]) }}"
           data-tour-id="board-new-group"
           style="height:36px;display:inline-flex;align-items:center;gap:6px;padding:0 14px;border:1px solid var(--border);border-radius:8px;background:white;color:var(--text-2);font-size:13px;font-weight:500;font-family:'DM Sans',sans-serif;text-decoration:none;">
            Nouveau groupe
        </a>
    </div>
@endsection

<div style="display:flex;flex-direction:column;gap:0;">
    @include('livewire.boards.partials.create-task-panel', [
        'groups' => $taskGroups,
        'taskContext' => [
            'show' => request()->boolean('createTask'),
            'open_url' => route('boards.show', [
                'board' => $board,
                'createTask' => 1,
            ]),
            'cancel_url' => route('boards.show', $board),
            'view' => 'table',
            'group_id' => request('group'),
            'status' => request('status', 'todo'),
            'deadline' => request('date'),
        ],
    ])

    {{-- ── BULK ACTION BAR ── --}}
    <div x-show="$wire.bulkMode" class="bulk-action-bar">
        <span class="text-sm font-medium" style="color:var(--blue);">
            <span x-text="$wire.bulkSelected.length"></span> tâche(s) sélectionnée(s)
        </span>
        <div style="flex:1;"></div>
        <select wire:model="bulkAction"
                class="view-btn"
                style="border:1px solid var(--blue);background:white;">
            <option value="">Choisir une action...</option>
            <optgroup label="Statut">
                <option value="status:done">→ Achevé</option>
                <option value="status:progress">→ En cours</option>
                <option value="status:todo">→ Non commencé</option>
                <option value="status:blocked">→ Bloqué</option>
                <option value="status:ongoing">→ Continu</option>
            </optgroup>
            <optgroup label="Priorité">
                <option value="priority:critique">→ Critique</option>
                <option value="priority:haute">→ Haute</option>
                <option value="priority:moyenne">→ Moyenne</option>
                <option value="priority:basse">→ Basse</option>
            </optgroup>
        </select>
        <button wire:click="applyBulkAction" class="btn-primary" style="padding:5px 14px;height:auto;font-size:12px;">
            Appliquer
        </button>
        <button wire:click="$set('bulkSelected', []); $set('bulkMode', false)"
                class="icon-btn" style="width:24px;height:24px;border:none;background:none;">✕</button>
    </div>

    {{-- ── GROUPES ── --}}
    @foreach($groups as $group)
        <div wire:key="group-{{ $group->id }}" style="margin-bottom:24px;">

            {{-- Group header --}}
            <div class="board-group-header">

                <div class="flex-center" style="cursor:pointer;flex:1;justify-content:flex-start;gap:8px;"
                     wire:click="toggleGroup({{ $group->id }})">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="text-3" style="transition:transform .2s;{{ in_array($group->id, $openGroups) ? '' : 'transform:rotate(-90deg)' }}">
                        <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    <div style="width:4px;height:16px;border-radius:2px;background:{{ $group->color ?? '#0091CD' }};flex-shrink:0;"></div>

                    @if(($editingGroup['group_id'] ?? null) == $group->id)
                        <input type="text"
                               value="{{ $group->name }}"
                               style="background:transparent;border:none;border-bottom:2px solid var(--blue);outline:none;font-size:13px;font-weight:600;font-family:'DM Sans',sans-serif;padding:0;"
                               autofocus
                               wire:blur="saveGroupName({{ $group->id }}, $event.target.value)"
                               wire:keydown.enter="saveGroupName({{ $group->id }}, $event.target.value)"
                               wire:keydown.escape="$set('editingGroup', [])"
                               @click.stop>
                    @else
                        <span class="text-sm font-semibold text-1" style="letter-spacing:-0.01em;"
                              wire:dblclick.stop="startEditingGroup({{ $group->id }})">
                            {{ $group->name }}
                        </span>
                    @endif

                    <span class="badge f-mono" style="background:var(--bg);color:var(--text-3);">
                        {{ $group->items->count() }}
                    </span>
                </div>

                {{-- Group actions dropdown --}}
                <div class="relative" data-group-menu>
                    <button type="button"
                            class="icon-btn"
                            style="width:26px;height:26px;border:none;background:none;"
                            data-group-menu-trigger
                            aria-expanded="false">
                        ···
                    </button>

                    <div data-group-menu-panel
                         hidden
                         class="absolute z-50 mt-1 rounded-md shadow-lg"
                         style="right:0;top:calc(100% + 4px);background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;min-width:160px;">
                        <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;transition:background .12s;"
                             onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"
                             wire:click="startEditingGroup({{ $group->id }})" @click="open=false">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M1 9.5l1.5 1.5L11 2.5 9.5 1 1 9.5zM1 9.5v1.5h1.5" stroke="currentColor" stroke-width="1.2"/></svg>
                            Renommer
                        </div>

                        <div style="padding:6px 10px;font-size:10px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Couleur</div>
                        <div style="display:flex;gap:6px;padding:4px 10px 8px;flex-wrap:wrap;">
                            @foreach(['#0091CD', '#22c55e', '#ef4444', '#f97316', '#8b5cf6', '#111110'] as $color)
                                <div wire:click="updateGroupColor({{ $group->id }}, '{{ $color }}')"
                                     style="width:16px;height:16px;border-radius:50%;background:{{ $color }};cursor:pointer;border:{{ ($group->color ?? '#0091CD') === $color ? '2px solid #111110' : '1px solid var(--border)' }};"
                                     title="{{ $color }}"></div>
                            @endforeach
                        </div>

                        <div style="border-top:1px solid var(--border);margin-top:4px;padding-top:4px;"></div>
                        <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;color:#dc2626;transition:background .12s;"
                             onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background=''"
                             wire:click="deleteGroup({{ $group->id }})" wire:confirm="Supprimer ce groupe et toutes ses tâches ?" @click="open=false">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 3h8M5 3V1.5h2V3M4 3v7h4V3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                            Supprimer
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            @if(in_array($group->id, $openGroups))
                <div class="table-container">
                    <table class="styled-table">

                        <thead>
                            <tr>
                                <th style="width:40px;padding:8px 10px;">
                                    <input type="checkbox" style="cursor:pointer;accent-color:var(--blue);"
                                           onchange="document.querySelectorAll('.row-check-{{ $group->id }}').forEach(cb => cb.click())">
                                </th>
                                <th style="padding:8px 10px;">Tâche</th>
                                <th style="padding:8px 10px;width:150px;">Statut</th>
                                <th style="padding:8px 10px;width:120px;">Assigné</th>
                                <th style="padding:8px 10px;width:110px;">Priorité</th>
                                <th style="padding:8px 10px;width:120px;">Deadline</th>
                                <th style="padding:8px 10px;width:160px;">Livrable</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($group->items as $item)
                                <tr wire:key="item-{{ $item->id }}"
                                    style="cursor:pointer;{{ in_array($item->id, $bulkSelected) ? 'background:var(--blue-light);' : '' }}"
                                    onmouseover="if(!{{ in_array($item->id, $bulkSelected) ? 'true' : 'false' }}) this.style.background='var(--bg)'"
                                    onmouseout="if(!{{ in_array($item->id, $bulkSelected) ? 'true' : 'false' }}) this.style.background=''">

                                    {{-- Checkbox --}}
                                    <td style="padding:10px;width:40px;">
                                        <input type="checkbox"
                                               class="row-check-{{ $group->id }}"
                                               style="cursor:pointer;accent-color:var(--blue);"
                                               {{ in_array($item->id, $bulkSelected) ? 'checked' : '' }}
                                               wire:click.stop="toggleBulkSelect({{ $item->id }})">
                                    </td>

                                    {{-- Nom --}}
                                    <td style="padding:10px;"
                                        wire:click="openItemPanel({{ $item->id }})">
                                        @if(($editingCell['item_id'] ?? null) == $item->id && ($editingCell['field'] ?? '') === 'name')
                                            <input type="text"
                                                   value="{{ $item->name }}"
                                                   style="width:100%;background:transparent;border:none;border-bottom:2px solid var(--blue);outline:none;font-size:13px;font-weight:500;font-family:'DM Sans',sans-serif;padding:0 0 2px;"
                                                   autofocus
                                                   wire:blur="saveCell({{ $item->id }}, 'name', $event.target.value)"
                                                   wire:keydown.enter="saveCell({{ $item->id }}, 'name', $event.target.value)"
                                                   wire:keydown.escape="stopEditing()"
                                                   @click.stop>
                                        @else
                                            <div class="flex-center" style="gap:8px;justify-content:flex-start;">
                                                <div style="width:14px;height:14px;border:1.5px solid var(--border);border-radius:3px;flex-shrink:0;"></div>
                                                <span class="text-sm font-medium text-1" style="letter-spacing:-0.01em;"
                                                      wire:dblclick.stop="startEditing({{ $item->id }}, 'name')">
                                                    {{ $item->name }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Statut --}}
                                    <td style="padding:10px;" @click.stop>
                                        <div x-data="{ open: false }" style="position:relative;">
                                            <button type="button" @click="open = !open" @click.outside="open = false"
                                                    class="badge s-{{ $item->status }}"
                                                    style="gap:5px;border:none;cursor:pointer;">
                                                <span style="width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7;flex-shrink:0;"></span>
                                                {{ match($item->status) {
                                                    'done'     => 'Achevé',
                                                    'progress' => 'En cours',
                                                    'todo'     => 'Non commencé',
                                                    'blocked'  => 'Bloqué',
                                                    'ongoing'  => 'Continu',
                                                    default    => ucfirst($item->status)
                                                } }}
                                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" style="opacity:.6;">
                                                    <path d="M2 4l3 3 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                                </svg>
                                            </button>

                                            {{-- Dropdown statut --}}
                                            <div x-show="open"
                                                 style="display:none;position:absolute;top:calc(100% + 4px);left:0;z-index:50;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;min-width:170px;">
                                                @foreach(['done' => 'Achevé', 'progress' => 'En cours', 'todo' => 'Non commencé', 'blocked' => 'Bloqué', 'ongoing' => 'Continu'] as $val => $label)
                                                    <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;transition:background .12s;"
                                                         onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"
                                                         wire:click="updateStatus({{ $item->id }}, '{{ $val }}')"
                                                         @click="open = false">
                                                        <span class="s-{{ $val }}" style="width:8px;height:8px;border-radius:50%;display:inline-block;"></span>
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Assignés --}}
                                    <td style="padding:10px;" @click.stop>
                                        <div class="flex-center" style="justify-content:flex-start;cursor:pointer;"
                                             wire:click="openItemPanel({{ $item->id }})">
                                            @foreach($item->assignees->take(3) as $assignee)
                                                <div class="user-avatar text-xs font-semibold"
                                                     style="width:26px;height:26px;border:2px solid white;margin-left:-6px;"
                                                     title="{{ $assignee->name }}">
                                                    {{ strtoupper(substr($assignee->name, 0, 2)) }}
                                                </div>
                                            @endforeach
                                            @if($item->assignees->count() > 3)
                                                <div class="user-avatar text-xs font-semibold text-2"
                                                     style="width:26px;height:26px;background:var(--bg);border:2px solid white;margin-left:-6px;">
                                                    +{{ $item->assignees->count() - 3 }}
                                                </div>
                                            @endif
                                            @if($item->assignees->isEmpty())
                                                <div class="flex-center" style="width:26px;height:26px;border-radius:50%;border:1.5px dashed var(--border);color:var(--text-3);">
                                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 2v8M2 6h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Priorité --}}
                                    <td style="padding:10px;" @click.stop>
                                        <div x-data="{ open: false }" style="position:relative;">
                                            <button type="button" @click="open = !open" @click.outside="open = false"
                                                    class="flex-center text-xs font-medium text-2" style="background:none;border:none;cursor:pointer;gap:5px;">
                                                <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;background:{{ match($item->priority) { 'critique'=>'#8b5cf6','haute'=>'#ef4444','moyenne'=>'#FFD100','basse'=>'#22c55e',default=>'#a3a39f'} }};"></span>
                                                {{ ucfirst($item->priority ?? 'moyenne') }}
                                            </button>

                                            {{-- Dropdown priorité --}}
                                            <div x-show="open"
                                                 style="display:none;position:absolute;top:calc(100% + 4px);left:0;z-index:50;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;min-width:140px;">
                                                @foreach(['critique'=>['#8b5cf6','Critique'],'haute'=>['#ef4444','Haute'],'moyenne'=>['#FFD100','Moyenne'],'basse'=>['#22c55e','Basse']] as $val=>[$color,$label])
                                                    <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;transition:background .12s;"
                                                         onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"
                                                         wire:click="updatePriority({{ $item->id }}, '{{ $val }}')"
                                                         @click="open=false">
                                                        <span style="width:7px;height:7px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></span>
                                                        {{ $label }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Deadline --}}
                                    <td style="padding:10px;" @click.stop>
                                        <input type="text"
                                               value="{{ $item->deadline?->format('d M Y') ?? '' }}"
                                               placeholder="— —"
                                               class="text-xs f-mono"
                                               style="background:none;border:none;outline:none;cursor:pointer;width:100%;color:{{ $item->deadline && $item->deadline->isPast() ? '#dc2626' : 'var(--text-2)' }};"
                                               x-init="flatpickr($el, { locale:'fr', dateFormat:'d M Y', onChange(dates){ $wire.updateDeadline({{ $item->id }}, dates[0]?.toISOString().split('T')[0] || '') } })">
                                    </td>

                                    {{-- Livrable --}}
                                    <td style="padding:10px;" @click.stop>
                                        @if(($editingCell['item_id'] ?? null) == $item->id && ($editingCell['field'] ?? '') === 'deliverable')
                                            <input type="text"
                                                   value="{{ $item->deliverable }}"
                                                   style="width:100%;background:transparent;border:none;border-bottom:1px solid var(--blue);outline:none;font-size:12px;font-family:'DM Sans',sans-serif;padding:0 0 2px;"
                                                   autofocus
                                                   wire:blur="saveCell({{ $item->id }}, 'deliverable', $event.target.value)"
                                                   wire:keydown.enter="saveCell({{ $item->id }}, 'deliverable', $event.target.value)"
                                                   wire:keydown.escape="stopEditing()">
                                        @else
                                            <span class="text-xs" style="color:{{ $item->deliverable ? 'var(--text-2)' : 'var(--text-3)' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:150px;"
                                                  wire:dblclick="startEditing({{ $item->id }}, 'deliverable')">
                                                {{ $item->deliverable ?: '—' }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions ··· --}}
                                    <td style="padding:10px;text-align:center;" @click.stop>
                                        <div x-data="{ open: false }" style="position:relative;display:inline-block;">
                                            <button type="button" @click="open = !open" @click.outside="open = false"
                                                    class="icon-btn" style="width:28px;height:28px;border:none;background:none;">
                                                ···
                                            </button>

                                            {{-- Dropdown actions --}}
                                            <div x-show="open"
                                                 style="display:none;position:absolute;right:0;top:calc(100% + 4px);z-index:50;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;min-width:160px;">
                                                <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;transition:background .12s;"
                                                     onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"
                                                     wire:click="openItemPanel({{ $item->id }})" @click="open=false">
                                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M1 6s2-4 5-4 5 4 5 4-2 4-5 4-5-4-5-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="6" cy="6" r="1.5" stroke="currentColor" stroke-width="1.2"/></svg>
                                                    Ouvrir
                                                </div>
                                                <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;color:#dc2626;transition:background .12s;"
                                                     onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background=''"
                                                     wire:click="deleteItem({{ $item->id }})" wire:confirm="Supprimer cette tâche ?" @click="open=false">
                                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 3h8M5 3V1.5h2V3M4 3v7h4V3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                                                    Supprimer
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            @endforeach

                            {{-- Add item row --}}
                            <tr>
                                <td colspan="8" style="padding:0;border-top:1px solid var(--border);">
                                    <div style="padding:8px 52px;">
                                        <a href="{{ route('boards.show', ['board' => $board, 'createTask' => 1, 'group' => $group->id]) }}"
                                           class="text-sm text-3"
                                           style="display:flex;align-items:center;gap:6px;cursor:pointer;width:fit-content;padding:4px 8px;border-radius:6px;transition:all .15s;text-decoration:none;"
                                           onmouseover="this.style.color='var(--blue)';this.style.background='var(--blue-light)'"
                                           onmouseout="this.style.color='';this.style.background=''">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 2v10M2 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            Ajouter une tâche
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach

    {{-- Add group inline input --}}
    <div style="margin-top:8px;">
        @if (session('group_created_id'))
            <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;border-radius:10px;font-size:13px;">
                Groupe ajouté au board.
            </div>
        @endif

        @if($errors->any() && $isCreatingGroup)
            <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;font-size:13px;">
                {{ $errors->first() }}
            </div>
        @endif

        @if(!$isCreatingGroup)
        <a href="{{ route('boards.show', ['board' => $board, 'createGroup' => 1]) }}"
             class="text-sm text-3"
             style="display:block;width:100%;padding:14px;border:2px dashed var(--border-md);border-radius:12px;background:none;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s;text-align:center;text-decoration:none;"
             onmouseover="this.style.borderColor='var(--blue)';this.style.color='var(--blue)';this.style.background='var(--blue-light)'"
             onmouseout="this.style.borderColor='var(--border-md)';this.style.color='';this.style.background=''">
            + Ajouter un groupe
        </a>
        @endif

        @if($isCreatingGroup)
        <div style="padding:16px;background:white;border:1px solid var(--blue);border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <div class="flex-center" style="gap:12px;justify-content:flex-start;">
                <div style="width:12px;height:12px;border-radius:50%;background:{{ old('color', '#0091CD') }};flex-shrink:0;"></div>
                <form method="POST" action="{{ route('boards.groups.store', $board) }}" class="flex-center" style="gap:12px;flex:1;">
                    @csrf
                    <input type="text"
                           id="new-group-input"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="Nom du nouveau groupe..."
                           autofocus
                           style="flex:1;font-size:14px;font-weight:600;background:transparent;border:none;border-bottom:2px solid var(--blue);outline:none;padding:4px 0;font-family:'DM Sans',sans-serif;">

                    <div style="display:flex;gap:4px;">
                        @foreach(['#0091CD', '#22c55e', '#ef4444', '#f97316', '#8b5cf6', '#111110'] as $color)
                            <label style="cursor:pointer;">
                                <input type="radio" name="color" value="{{ $color }}" {{ old('color', '#0091CD') === $color ? 'checked' : '' }} style="display:none;">
                                <span style="display:block;width:18px;height:18px;border-radius:50%;background:{{ $color }};border:{{ old('color', '#0091CD') === $color ? '2px solid #111110' : '1px solid var(--border)' }};"></span>
                            </label>
                        @endforeach
                    </div>

                    <button type="submit" class="btn-primary" style="padding:6px 16px;height:auto;">
                        Créer
                    </button>
                    <a href="{{ route('boards.show', $board) }}"
                       class="text-xl text-3"
                       style="padding:6px;background:none;border:none;cursor:pointer;text-decoration:none;">✕</a>
                </form>
            </div>
        </div>
        @endif
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
<script>
(() => {
    const closeGroupMenus = (exceptMenu = null) => {
        document.querySelectorAll('[data-group-menu]').forEach((menu) => {
            const panel = menu.querySelector('[data-group-menu-panel]');
            const trigger = menu.querySelector('[data-group-menu-trigger]');
            if (!panel || !trigger) return;

            const shouldKeepOpen = exceptMenu && menu === exceptMenu;
            panel.hidden = !shouldKeepOpen;
            trigger.setAttribute('aria-expanded', shouldKeepOpen ? 'true' : 'false');
        });
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-group-menu-trigger]');
        if (trigger) {
            const menu = trigger.closest('[data-group-menu]');
            const panel = menu?.querySelector('[data-group-menu-panel]');
            if (!menu || !panel) return;

            const willOpen = panel.hidden;
            closeGroupMenus(willOpen ? menu : null);
            event.stopPropagation();
            return;
        }

        if (!event.target.closest('[data-group-menu]')) {
            closeGroupMenus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeGroupMenus();
        }
    });

    document.addEventListener('livewire:navigated', () => closeGroupMenus());
    document.addEventListener('livewire:rendered', () => closeGroupMenus());
})();
</script>
@endpush
