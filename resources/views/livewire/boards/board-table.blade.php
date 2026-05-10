{{-- resources/views/livewire/boards/board-table.blade.php --}}

@section('page-title', $board->name)

@section('view-switcher')
    <button class="view-btn active" wire:navigate href="{{ route('boards.show', $board) }}">Tableau</button>
    <button class="view-btn" wire:navigate href="{{ route('boards.kanban', $board) }}">Kanban</button>
    <button class="view-btn" wire:navigate href="{{ route('boards.calendar', $board) }}">Calendrier</button>
@endsection

@section('topbar-action')
    <button class="btn-primary" wire:click="addGroup">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M7 2v10M2 7h10" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        Nouveau groupe
    </button>
@endsection

<div style="display:flex;flex-direction:column;gap:0;">

    {{-- ── BULK ACTION BAR ── --}}
    <div x-show="$wire.bulkMode"
         style="display:none;align-items:center;gap:12px;padding:10px 16px;background:var(--blue-light);border:1px solid var(--blue);border-radius:10px;margin-bottom:14px;">
        <span style="font-size:13px;font-weight:500;color:var(--blue);">
            <span x-text="$wire.bulkSelected.length"></span> tâche(s) sélectionnée(s)
        </span>
        <div style="flex:1;"></div>
        <select wire:model="bulkAction"
                style="font-size:12px;padding:5px 10px;border-radius:6px;border:1px solid var(--blue);background:white;color:var(--text-1);font-family:'DM Sans',sans-serif;cursor:pointer;">
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
        <button wire:click="applyBulkAction"
                style="padding:5px 14px;background:var(--blue);color:white;border:none;border-radius:6px;font-size:12px;font-weight:500;font-family:'DM Sans',sans-serif;cursor:pointer;">
            Appliquer
        </button>
        <button wire:click="$set('bulkSelected', []); $set('bulkMode', false)"
                style="padding:5px 10px;background:none;border:none;color:var(--text-3);font-size:13px;cursor:pointer;">✕</button>
    </div>

    {{-- ── GROUPES ── --}}
    @foreach($groups as $group)
        <div wire:key="group-{{ $group->id }}" style="margin-bottom:24px;">

            {{-- Group header --}}
            <div style="display:flex;align-items:center;gap:8px;padding:6px 4px;cursor:pointer;user-select:none;border-radius:6px;transition:background .15s;"
                 wire:click="toggleGroup({{ $group->id }})"
                 onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">

                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="color:var(--text-3);flex-shrink:0;transition:transform .2s;{{ in_array($group->id, $openGroups) ? '' : 'transform:rotate(-90deg)' }}">
                    <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>

                <div style="width:4px;height:16px;border-radius:2px;background:{{ $group->color ?? '#0091CD' }};flex-shrink:0;"></div>

                <span style="font-size:13px;font-weight:600;letter-spacing:-0.01em;">{{ $group->name }}</span>

                <span style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);background:var(--bg);padding:1px 7px;border-radius:20px;">
                    {{ $group->items->count() }}
                </span>
            </div>

            {{-- Table --}}
            @if(in_array($group->id, $openGroups))
                <div style="margin-top:4px;border:1px solid var(--border);border-radius:12px;overflow:hidden;">
                    <table style="width:100%;border-collapse:collapse;">

                        <thead>
                            <tr style="border-bottom:1px solid var(--border);">
                                <th style="width:40px;padding:8px 10px;">
                                    <input type="checkbox" style="cursor:pointer;accent-color:var(--blue);"
                                           onchange="document.querySelectorAll('.row-check-{{ $group->id }}').forEach(cb => cb.click())">
                                </th>
                                <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:8px 10px;">Tâche</th>
                                <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:8px 10px;width:150px;">Statut</th>
                                <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:8px 10px;width:120px;">Assigné</th>
                                <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:8px 10px;width:110px;">Priorité</th>
                                <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:8px 10px;width:120px;">Deadline</th>
                                <th style="text-align:left;font-size:11px;font-weight:500;color:var(--text-3);letter-spacing:.05em;text-transform:uppercase;padding:8px 10px;width:160px;">Livrable</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($group->items as $item)
                                <tr wire:key="item-{{ $item->id }}"
                                    style="transition:background .15s;cursor:pointer;{{ in_array($item->id, $bulkSelected) ? 'background:var(--blue-light);' : '' }}"
                                    onmouseover="if(!{{ in_array($item->id, $bulkSelected) ? 'true' : 'false' }}) this.style.background='var(--bg)'"
                                    onmouseout="if(!{{ in_array($item->id, $bulkSelected) ? 'true' : 'false' }}) this.style.background=''">

                                    {{-- Checkbox --}}
                                    <td style="padding:10px;border-top:1px solid var(--border);width:40px;">
                                        <input type="checkbox"
                                               class="row-check-{{ $group->id }}"
                                               style="cursor:pointer;accent-color:var(--blue);"
                                               {{ in_array($item->id, $bulkSelected) ? 'checked' : '' }}
                                               wire:click.stop="toggleBulkSelect({{ $item->id }})">
                                    </td>

                                    {{-- Nom --}}
                                    <td style="padding:10px;border-top:1px solid var(--border);"
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
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <div style="width:14px;height:14px;border:1.5px solid var(--border);border-radius:3px;flex-shrink:0;"></div>
                                                <span style="font-size:13px;font-weight:500;letter-spacing:-0.01em;"
                                                      wire:dblclick.stop="startEditing({{ $item->id }}, 'name')">
                                                    {{ $item->name }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Statut --}}
                                    <td style="padding:10px;border-top:1px solid var(--border);" @click.stop>
                                        <div x-data="{ open: false }" style="position:relative;">
                                            <button @click="open = !open" @click.outside="open = false"
                                                    style="display:flex;align-items:center;gap:5px;padding:3px 9px;border-radius:20px;border:none;cursor:pointer;font-size:11px;font-weight:500;font-family:'DM Sans',sans-serif;"
                                                    class="s-{{ $item->status }}">
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

                                            {{-- Dropdown statut — display:none natif ── --}}
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
                                    <td style="padding:10px;border-top:1px solid var(--border);" @click.stop>
                                        <div style="display:flex;align-items:center;cursor:pointer;"
                                             wire:click="openItemPanel({{ $item->id }})">
                                            @foreach($item->assignees->take(3) as $assignee)
                                                <div style="width:26px;height:26px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;border:2px solid white;margin-left:-6px;"
                                                     title="{{ $assignee->name }}">
                                                    {{ strtoupper(substr($assignee->name, 0, 2)) }}
                                                </div>
                                            @endforeach
                                            @if($item->assignees->count() > 3)
                                                <div style="width:26px;height:26px;border-radius:50%;background:var(--bg);color:var(--text-2);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;border:2px solid white;margin-left:-6px;">
                                                    +{{ $item->assignees->count() - 3 }}
                                                </div>
                                            @endif
                                            @if($item->assignees->isEmpty())
                                                <div style="width:26px;height:26px;border-radius:50%;border:1.5px dashed var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-3);">
                                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 2v8M2 6h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Priorité --}}
                                    <td style="padding:10px;border-top:1px solid var(--border);" @click.stop>
                                        <div x-data="{ open: false }" style="position:relative;">
                                            <button @click="open = !open" @click.outside="open = false"
                                                    style="display:flex;align-items:center;gap:5px;font-size:12px;font-weight:500;font-family:'DM Sans',sans-serif;background:none;border:none;cursor:pointer;color:var(--text-2);">
                                                <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;background:{{ match($item->priority) { 'critique'=>'#8b5cf6','haute'=>'#ef4444','moyenne'=>'#FFD100','basse'=>'#22c55e',default=>'#a3a39f'} }};"></span>
                                                {{ ucfirst($item->priority ?? 'moyenne') }}
                                            </button>

                                            {{-- Dropdown priorité — display:none natif ── --}}
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
                                    <td style="padding:10px;border-top:1px solid var(--border);" @click.stop>
                                        <input type="text"
                                               value="{{ $item->deadline?->format('d M Y') ?? '' }}"
                                               placeholder="— —"
                                               style="font-size:12px;font-family:'DM Mono',monospace;background:none;border:none;outline:none;cursor:pointer;width:100%;color:{{ $item->deadline && $item->deadline->isPast() ? '#dc2626' : 'var(--text-2)' }};"
                                               x-init="flatpickr($el, { locale:'fr', dateFormat:'d M Y', onChange(dates){ $wire.updateDeadline({{ $item->id }}, dates[0]?.toISOString().split('T')[0] || '') } })">
                                    </td>

                                    {{-- Livrable --}}
                                    <td style="padding:10px;border-top:1px solid var(--border);" @click.stop>
                                        @if(($editingCell['item_id'] ?? null) == $item->id && ($editingCell['field'] ?? '') === 'deliverable')
                                            <input type="text"
                                                   value="{{ $item->deliverable }}"
                                                   style="width:100%;background:transparent;border:none;border-bottom:1px solid var(--blue);outline:none;font-size:12px;font-family:'DM Sans',sans-serif;padding:0 0 2px;"
                                                   autofocus
                                                   wire:blur="saveCell({{ $item->id }}, 'deliverable', $event.target.value)"
                                                   wire:keydown.enter="saveCell({{ $item->id }}, 'deliverable', $event.target.value)"
                                                   wire:keydown.escape="stopEditing()">
                                        @else
                                            <span style="font-size:12px;color:{{ $item->deliverable ? 'var(--text-2)' : 'var(--text-3)' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:150px;"
                                                  wire:dblclick="startEditing({{ $item->id }}, 'deliverable')">
                                                {{ $item->deliverable ?: '—' }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions ··· --}}
                                    <td style="padding:10px;border-top:1px solid var(--border);text-align:center;" @click.stop>
                                        <div x-data="{ open: false }" style="position:relative;display:inline-block;">
                                            <button @click="open = !open" @click.outside="open = false"
                                                    style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:6px;border:none;background:none;cursor:pointer;font-size:16px;color:var(--text-3);transition:background .12s;"
                                                    onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                                                ···
                                            </button>

                                            {{-- Dropdown actions — display:none natif ── --}}
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
                                    <div x-data="{ adding: false }" style="padding:8px 52px;">
                                        <div x-show="!adding"
                                             @click="adding = true"
                                             style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-3);cursor:pointer;width:fit-content;padding:4px 8px;border-radius:6px;transition:all .15s;"
                                             onmouseover="this.style.color='var(--blue)';this.style.background='var(--blue-light)'" onmouseout="this.style.color='var(--text-3)';this.style.background=''">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 2v10M2 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            Ajouter une tâche
                                        </div>
                                        <div x-show="adding"
                                             style="display:none;align-items:center;gap:8px;">
                                            <input type="text"
                                                   wire:model="newItemName"
                                                   placeholder="Nom de la tâche..."
                                                   style="flex:1;font-size:13px;background:transparent;border:none;border-bottom:2px solid var(--blue);outline:none;padding:4px 0;font-family:'DM Sans',sans-serif;"
                                                   x-ref="newInput"
                                                   x-init="$watch('adding', v => v && $nextTick(() => $refs.newInput?.focus()))"
                                                   wire:keydown.enter="addItem({{ $group->id }})"
                                                   wire:keydown.escape="$set('newItemName', ''); adding = false">
                                            <button wire:click="addItem({{ $group->id }})"
                                                    style="padding:4px 12px;background:var(--text-1);color:white;border:none;border-radius:6px;font-size:12px;font-weight:500;font-family:'DM Sans',sans-serif;cursor:pointer;">
                                                Ajouter
                                            </button>
                                            <button @click="adding = false"
                                                    style="padding:4px 8px;background:none;border:none;color:var(--text-3);cursor:pointer;font-size:13px;">✕</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach

    {{-- Add group button --}}
    <button wire:click="addGroup"
            style="width:100%;padding:14px;border:2px dashed var(--border-md);border-radius:12px;background:none;font-size:13px;color:var(--text-3);cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s;margin-top:8px;"
            onmouseover="this.style.borderColor='var(--blue)';this.style.color='var(--blue)';this.style.background='var(--blue-light)'"
            onmouseout="this.style.borderColor='var(--border-md)';this.style.color='var(--text-3)';this.style.background=''">
        + Ajouter un groupe
    </button>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
@endpush