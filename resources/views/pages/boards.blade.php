<x-app-layout>
@section('page-title', 'Boards')
@section('topbar-action')
    <button type="button" class="btn-primary" onclick="document.getElementById('create-board-modal').showModal()">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 2v10M2 7h10" stroke="white" stroke-width="1.8" stroke-linecap="round"/></svg>
        Nouveau board
    </button>
@endsection

@php
    $boards = \App\Models\Board::query()
        ->where('workspace_id', auth()->user()?->currentWorkspace?->id)
        ->get();
@endphp

@if ($errors->any())
    <div style="margin-bottom:14px;padding:12px 14px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;font-size:13px;">
        {{ $errors->first() }}
    </div>
    <script>
        window.addEventListener('load', () => {
            document.getElementById('create-board-modal')?.showModal();
        });
    </script>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
    @forelse($boards as $board)
        <a href="{{ route('boards.show', $board) }}"
           class="bento-card" style="padding:20px;text-decoration:none;color:inherit;display:block;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                <div style="width:10px;height:10px;border-radius:50%;background:{{ $board->color }};"></div>
                <span style="font-size:15px;font-weight:600;letter-spacing:-0.015em;">{{ $board->name }}</span>
            </div>
            <div style="height:3px;background:var(--bg);border-radius:10px;overflow:hidden;margin-bottom:12px;">
                <div style="height:100%;background:var(--blue);width:{{ $board->progress }}%;transition:width .4s;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-3);font-family:'DM Mono',monospace;">
                <span>{{ $board->items_count }} tâches</span>
                <span>{{ $board->progress }}%</span>
            </div>
        </a>
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:72px 24px;color:var(--text-3);border:1px dashed var(--border-md);border-radius:14px;background:white;">
            <div style="font-size:40px;margin-bottom:12px;">🗂️</div>
            <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:6px;">Aucun board</div>
            <div style="font-size:13px;">Crée ton premier board pour organiser les tâches du workspace.</div>
        </div>
    @endforelse
</div>

<dialog id="create-board-modal" style="border:none;border-radius:16px;padding:0;max-width:460px;width:calc(100% - 32px);box-shadow:0 24px 60px rgba(0,0,0,0.16);">
    <form method="dialog" style="position:absolute;top:14px;right:14px;">
        <button type="submit" style="width:30px;height:30px;border:none;border-radius:8px;background:var(--bg);cursor:pointer;color:var(--text-3);font-size:16px;">✕</button>
    </form>

    <div style="padding:24px;">
        <div style="font-size:20px;font-weight:600;letter-spacing:-0.02em;margin-bottom:6px;">Nouveau board</div>
        <div style="font-size:13px;color:var(--text-3);margin-bottom:18px;">Crée un espace de travail pour regrouper tes tâches.</div>

        <form method="POST" action="{{ route('boards.store') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf

            <label style="display:flex;flex-direction:column;gap:6px;">
                <span style="font-size:12px;font-weight:600;color:var(--text-2);">Nom du board</span>
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       required
                       maxlength="255"
                       placeholder="Ex: Lancement challenge"
                       style="height:42px;padding:0 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;">
            </label>

            <label style="display:flex;flex-direction:column;gap:8px;">
                <span style="font-size:12px;font-weight:600;color:var(--text-2);">Couleur</span>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    @foreach(['#0091CD', '#22c55e', '#ef4444', '#f97316', '#8b5cf6', '#111110'] as $color)
                        <label style="cursor:pointer;">
                            <input type="radio" name="color" value="{{ $color }}" {{ old('color', '#0091CD') === $color ? 'checked' : '' }} style="display:none;">
                            <span style="display:block;width:28px;height:28px;border-radius:50%;background:{{ $color }};border:2px solid rgba(0,0,0,0.08);"></span>
                        </label>
                    @endforeach
                </div>
            </label>

            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:4px;">
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

@push('scripts')
<script>
    (() => {
        const dialog = document.getElementById('create-board-modal');
        if (!dialog) return;

        dialog.addEventListener('click', (event) => {
            const rect = dialog.getBoundingClientRect();
            const inside =
                event.clientX >= rect.left &&
                event.clientX <= rect.right &&
                event.clientY >= rect.top &&
                event.clientY <= rect.bottom;

            if (!inside) {
                dialog.close();
            }
        });
    })();
</script>
@endpush
</x-app-layout>
