<div>
    @if (session('status'))
        <div class="badge s-done" style="margin-bottom:14px;padding:12px 14px;width:100%;justify-content:flex-start;border-radius:10px;">
            {{ match(session('status')) {
                'board-updated' => 'Board mis à jour.',
                'board-deleted' => 'Board supprimé.',
                default => session('status'),
            } }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
        @forelse($boards as $board)
            <div class="bento-card" style="padding:20px;display:flex;flex-direction:column;gap:14px;" wire:key="board-{{ $board->id }}">
                <div class="flex-between" style="align-items:flex-start;gap:10px;">
                    <a href="{{ route('boards.show', $board) }}" wire:navigate style="flex:1;min-width:0;text-decoration:none;color:inherit;display:block;">
                        <div class="flex-center" style="gap:10px;margin-bottom:14px;justify-content:flex-start;">
                            <div style="width:10px;height:10px;border-radius:50%;background:{{ $board->color }};"></div>
                            <span class="text-md font-semibold text-1" style="letter-spacing:-0.015em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $board->name }}</span>
                        </div>
                    </a>

                    @can('update', $board)
                        <button type="button"
                                onclick="document.getElementById('edit-board-modal-{{ $board->id }}').showModal()"
                                class="icon-btn"
                                style="width:28px;height:28px;border:none;background:none;">···</button>
                    @endcan
                </div>

                <div style="height:3px;background:var(--bg);border-radius:10px;overflow:hidden;">
                    <div style="height:100%;background:var(--blue);width:{{ $board->progress }}%;transition:width .4s;"></div>
                </div>
                <div class="flex-between text-xs text-3 f-mono">
                    <span>{{ $board->items_count }} tâches</span>
                    <span>{{ $board->progress }}%</span>
                </div>

                @can('update', $board)
                    <dialog id="edit-board-modal-{{ $board->id }}" wire:ignore.self>
                        <form method="dialog">
                            <button type="submit" class="modal-close">✕</button>
                        </form>

                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="text-xl font-semibold text-1" style="letter-spacing:-0.02em;margin-bottom:6px;">Modifier le board</div>
                                <div class="text-sm text-3">Met à jour le nom ou la couleur du board.</div>
                            </div>

                            <form method="POST" action="{{ route('boards.update', $board) }}" style="display:flex;flex-direction:column;gap:16px;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="_board_form" value="edit">
                                <input type="hidden" name="_board_modal_id" value="edit-board-modal-{{ $board->id }}">

                                <label style="display:flex;flex-direction:column;gap:6px;">
                                    <span class="text-xs font-semibold text-2">Nom du board</span>
                                    <input type="text"
                                           name="name"
                                           value="{{ $board->name }}"
                                           required
                                           maxlength="255"
                                           style="height:42px;padding:0 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;">
                                </label>

                                <label style="display:flex;flex-direction:column;gap:8px;">
                                    <span class="text-xs font-semibold text-2">Couleur</span>
                                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                        @foreach(['#0091CD', '#22c55e', '#ef4444', '#f97316', '#8b5cf6', '#111110'] as $color)
                                            <label style="cursor:pointer;">
                                                <input type="radio" name="color" value="{{ $color }}" {{ $board->color === $color ? 'checked' : '' }} style="display:none;">
                                                <span style="display:block;width:28px;height:28px;border-radius:50%;background:{{ $color }};border:{{ $board->color === $color ? '3px solid #111110' : '2px solid rgba(0,0,0,0.08)' }};"></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </label>

                                <div class="flex-between" style="gap:10px;padding-top:4px;justify-content:flex-start;">
                                    <button type="submit" class="btn-primary" style="height:40px;">
                                        Enregistrer
                                    </button>
                                    <button type="button"
                                            onclick="document.getElementById('edit-board-modal-{{ $board->id }}').close()"
                                            style="height:40px;padding:0 14px;border:1px solid var(--border);border-radius:10px;background:white;color:var(--text-2);font-size:13px;font-family:'DM Sans',sans-serif;cursor:pointer;">
                                        Annuler
                                    </button>
                                </div>
                            </form>

                            @can('delete', $board)
                                <form method="POST" action="{{ route('boards.destroy', $board) }}" onsubmit="return confirm('Supprimer ce board et tout son contenu ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            style="height:40px;padding:0 14px;border:1px solid #fecaca;border-radius:10px;background:#fef2f2;color:#dc2626;font-size:13px;font-family:'DM Sans',sans-serif;cursor:pointer;">
                                        Supprimer le board
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </dialog>
                @endcan
            </div>
        @empty
            <div style="grid-column:1/-1;text-align:center;padding:72px 24px;color:var(--text-3);border:1px dashed var(--border-md);border-radius:14px;background:white;">
                <div style="font-size:40px;margin-bottom:12px;">🗂️</div>
                <div class="text-md font-semibold text-1" style="margin-bottom:6px;">Aucun board</div>
                <div class="text-sm">Crée ton premier board pour organiser les tâches du workspace.</div>
            </div>
        @endforelse
    </div>

    @if($canManageBoards)
        <dialog id="create-board-modal" wire:ignore.self>
            <form method="dialog">
                <button type="submit" class="modal-close">✕</button>
            </form>

            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-xl font-semibold text-1" style="letter-spacing:-0.02em;margin-bottom:6px;">Nouveau board</div>
                    <div class="text-sm text-3">Crée un espace de travail pour regrouper tes tâches.</div>
                </div>

                <form method="POST" action="{{ route('boards.store') }}" style="display:flex;flex-direction:column;gap:16px;">
                    @csrf
                    <input type="hidden" name="_board_form" value="create">

                    <label style="display:flex;flex-direction:column;gap:6px;">
                        <span class="text-xs font-semibold text-2">Nom du board</span>
                        <input type="text"
                               name="name"
                               required
                               maxlength="255"
                               placeholder="Ex: Lancement challenge"
                               style="height:42px;padding:0 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;">
                    </label>

                    <label style="display:flex;flex-direction:column;gap:8px;">
                        <span class="text-xs font-semibold text-2">Couleur</span>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            @foreach(['#0091CD', '#22c55e', '#ef4444', '#f97316', '#8b5cf6', '#111110'] as $color)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="color" value="{{ $color }}" {{ $loop->first ? 'checked' : '' }} style="display:none;">
                                    <span style="display:block;width:28px;height:28px;border-radius:50%;background:{{ $color }};border:2px solid rgba(0,0,0,0.08);"></span>
                                </label>
                            @endforeach
                        </div>
                    </label>

                    <div class="flex-between" style="gap:10px;padding-top:4px;justify-content:flex-end;">
                        <button type="button"
                                onclick="document.getElementById('create-board-modal').close()"
                                style="height:40px;padding:0 14px;border:1px solid var(--border);border-radius:10px;background:white;color:var(--text-2);font-size:13px;font-family:'DM Sans',sans-serif;cursor:pointer;">
                            Annuler
                        </button>
                        <button type="submit" class="btn-primary" style="height:40px;">
                            Créer le board
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
</div>
