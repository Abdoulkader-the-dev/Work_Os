<x-app-layout>
@section('page-title', 'Boards')
@section('topbar-action')
    <button class="btn-primary">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 2v10M2 7h10" stroke="white" stroke-width="1.8" stroke-linecap="round"/></svg>
        Nouveau board
    </button>
@endsection
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
    @foreach(\App\Models\Board::all() as $board)
        <a href="{{ route('boards.show', $board) }}"
           class="bento-card" style="padding:20px;text-decoration:none;color:inherit;display:block;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                <div style="width:10px;height:10px;border-radius:50%;background:{{ $board->color }};"></div>
                <span style="font-size:15px;font-weight:600;letter-spacing:-0.015em;">{{ $board->name }}</span>
            </div>
            <div style="height:3px;background:var(--color-bg);border-radius:10px;overflow:hidden;margin-bottom:12px;">
                <div style="height:100%;background:var(--color-unipod-blue);width:{{ $board->progress }}%;transition:width .4s;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--color-text-3);font-family:'DM Mono',monospace;">
                <span>{{ $board->items_count }} tâches</span>
                <span>{{ $board->progress }}%</span>
            </div>
        </a>
    @endforeach
</div>
</x-app-layout>