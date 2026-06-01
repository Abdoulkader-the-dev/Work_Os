@php
    $taskErrorBag = $errors->getBag('createTask');
    $groups = collect($groups ?? []);
    $showTaskForm = ($taskContext['show'] ?? false) || $taskErrorBag->any();
    $selectedGroupId = old('group_id', $taskContext['group_id'] ?? $groups->first()?->id);
    $selectedStatus = old('status', $taskContext['status'] ?? 'todo');
    $selectedPriority = old('priority', 'moyenne');
    $selectedDeadline = old('deadline', $taskContext['deadline'] ?? '');
    $cancelUrl = $taskContext['cancel_url'] ?? route('boards.show', $board);
    $viewName = $taskContext['view'] ?? 'table';
@endphp

<div style="margin-bottom:20px;">
    @if (session('item_created_id'))
        <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;border-radius:10px;font-size:13px;">
            Tâche ajoutée : {{ session('item_created_name') }}.
        </div>
    @endif

    @if($taskErrorBag->any())
        <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;font-size:13px;">
            {{ $taskErrorBag->first() }}
        </div>
    @endif

    @if(!$showTaskForm)
        <a href="{{ $taskContext['open_url'] }}"
           style="display:block;width:100%;padding:14px;border:2px dashed var(--border-md);border-radius:12px;background:none;font-size:13px;color:var(--text-3);cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s;text-align:center;text-decoration:none;"
           onmouseover="this.style.borderColor='var(--blue)';this.style.color='var(--blue)';this.style.background='var(--blue-light)'"
           onmouseout="this.style.borderColor='var(--border-md)';this.style.color='var(--text-3)';this.style.background=''">
            + Ajouter une tâche
        </a>
    @else
        <div style="padding:16px;background:white;border:1px solid var(--blue);border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <form method="POST" action="{{ route('boards.items.store', $board) }}" class="create-task-grid" style="display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:12px;align-items:end;">
                @csrf
                <input type="hidden" name="redirect_view" value="{{ $viewName }}">

                @if($viewName === 'calendar')
                    <input type="hidden" name="redirect_month" value="{{ $taskContext['month'] ?? '' }}">
                    <input type="hidden" name="redirect_year" value="{{ $taskContext['year'] ?? '' }}">
                @endif

                <label style="display:flex;flex-direction:column;gap:6px;min-width:0;grid-column:span 4;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Tâche</span>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="Ex: Préparer la présentation"
                           autofocus
                           required
                           style="height:42px;padding:0 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;">
                </label>

                <label style="display:flex;flex-direction:column;gap:6px;grid-column:span 2;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Groupe</span>
                    <select name="group_id"
                            style="height:42px;padding:0 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'DM Sans',sans-serif;background:white;outline:none;">
                        @forelse($groups as $group)
                            <option value="{{ $group->id }}" {{ (string) $selectedGroupId === (string) $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @empty
                            <option value="">Général</option>
                        @endforelse
                    </select>
                </label>

                <label style="display:flex;flex-direction:column;gap:6px;grid-column:span 2;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Statut</span>
                    <select name="status"
                            style="height:42px;padding:0 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'DM Sans',sans-serif;background:white;outline:none;">
                        @foreach(['todo' => 'Non commencé', 'progress' => 'En cours', 'ongoing' => 'Continu', 'blocked' => 'Bloqué', 'done' => 'Achevé'] as $value => $label)
                            <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label style="display:flex;flex-direction:column;gap:6px;grid-column:span 2;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Deadline</span>
                    <input type="date"
                           name="deadline"
                           value="{{ $selectedDeadline }}"
                           style="height:42px;padding:0 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;">
                </label>

                <div style="display:flex;gap:8px;align-items:end;grid-column:span 2;">
                    <button type="submit" class="btn-primary" style="height:42px;">
                        Créer
                    </button>
                    <a href="{{ $cancelUrl }}"
                       style="height:42px;display:inline-flex;align-items:center;justify-content:center;padding:0 14px;border:1px solid var(--border);border-radius:10px;background:white;color:var(--text-2);font-size:13px;font-family:'DM Sans',sans-serif;cursor:pointer;text-decoration:none;">
                        Annuler
                    </a>
                </div>

                <label style="display:flex;flex-direction:column;gap:6px;grid-column:1 / span 6;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Livrable</span>
                    <input type="text"
                           name="deliverable"
                           value="{{ old('deliverable') }}"
                           placeholder="Ex: Support final envoyé"
                           style="height:42px;padding:0 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;">
                </label>

                <label style="display:flex;flex-direction:column;gap:6px;grid-column:span 2;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Priorité</span>
                    <select name="priority"
                            style="height:42px;padding:0 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'DM Sans',sans-serif;background:white;outline:none;">
                        @foreach(['basse' => 'Basse', 'moyenne' => 'Moyenne', 'haute' => 'Haute', 'critique' => 'Critique'] as $value => $label)
                            <option value="{{ $value }}" {{ $selectedPriority === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div style="grid-column:1 / span 12;" wire:ignore>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <span style="font-size:12px;font-weight:600;color:var(--text-2);">Description</span>
                        <div x-data
                             x-init="
                                const input = $refs.input;
                                const editor = $refs.editor;
                                editor.editor?.loadHTML(input.value || '');
                                editor.addEventListener('trix-change', () => { input.value = editor.value; });
                             ">
                            <input id="create-task-description-{{ $viewName }}-{{ $board->id }}" type="hidden" name="description" x-ref="input" value="{{ old('description') }}">
                            <trix-editor input="create-task-description-{{ $viewName }}-{{ $board->id }}"
                                         x-ref="editor"
                                         class="trix-content"
                                         style="background:var(--bg);border:1px solid var(--border);border-radius:10px;min-height:140px;padding:10px 12px;"></trix-editor>
                        </div>
                    </div>
                </div>

                <div style="grid-column:1 / span 12;font-size:11px;color:var(--text-3);display:flex;align-items:center;justify-content:flex-end;">
                    La tâche sera disponible immédiatement dans Tableau, Kanban et Calendrier.
                </div>
            </form>
        </div>
    @endif
</div>

<style>
@media (max-width: 900px) {
    .create-task-grid, .create-task-grid > * {
        grid-column: 1 / -1 !important;
    }
}
@media (max-width: 768px) {
    .create-task-grid {
        grid-template-columns: 1fr !important;
    }
    .create-task-grid > * {
        grid-column: 1 / -1 !important;
    }
}
</style>
