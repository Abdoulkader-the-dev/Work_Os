<x-app-layout>
@section('page-title', 'Mes tâches')

@php
    $items = auth()->user()?->items()
        ->with('group.board')
        ->orderByRaw("case when status = 'blocked' then 0 when status = 'progress' then 1 when status = 'todo' then 2 when status = 'ongoing' then 3 when status = 'done' then 4 else 5 end")
        ->orderBy('deadline')
        ->get() ?? collect();
@endphp

<div style="display:flex;flex-direction:column;gap:18px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="font-size:12px;font-family:'DM Mono',monospace;color:var(--text-3);">
            {{ $items->count() }} tâche{{ $items->count() > 1 ? 's' : '' }}
        </div>
    </div>

    @if($items->isEmpty())
        <div style="text-align:center;padding:72px 24px;color:var(--text-3);">
            <div style="font-size:40px;margin-bottom:12px;">🗂️</div>
            <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:6px;">Aucune tâche assignée</div>
            <div style="font-size:13px;">Tes tâches apparaîtront ici dès qu'elles te seront attribuées.</div>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;">
            @foreach($items as $item)
                <div class="bento-card" style="padding:18px;display:flex;flex-direction:column;gap:12px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:15px;font-weight:600;letter-spacing:-0.015em;line-height:1.35;">
                                {{ $item->name }}
                            </div>
                            <div style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);margin-top:5px;">
                                {{ $item->group->board->name ?? 'Sans board' }} / {{ $item->group->name ?? 'Sans groupe' }}
                            </div>
                        </div>
                        <span class="s-{{ $item->status }}" style="font-size:11px;font-weight:500;padding:3px 8px;border-radius:20px;white-space:nowrap;">
                            {{ match($item->status) {
                                'done' => 'Achevé',
                                'progress' => 'En cours',
                                'todo' => 'Non commencé',
                                'blocked' => 'Bloqué',
                                'ongoing' => 'Continu',
                                default => ucfirst($item->status),
                            } }}
                        </span>
                    </div>

                    @if($item->deliverable)
                        <div style="font-size:13px;color:var(--text-2);line-height:1.5;">
                            {{ $item->deliverable }}
                        </div>
                    @endif

                    <div style="display:flex;align-items:center;justify-content:space-between;padding-top:10px;border-top:1px solid var(--border);gap:12px;">
                        <div style="font-size:11px;font-family:'DM Mono',monospace;color:{{ $item->deadline?->isPast() ? '#dc2626' : 'var(--text-3)' }};">
                            {{ $item->deadline ? 'Deadline ' . $item->deadline->format('d M Y') : 'Sans deadline' }}
                        </div>
                        @if($item->group?->board)
                            <a href="{{ route('boards.show', $item->group->board) }}"
                               style="font-size:12px;font-weight:500;color:var(--blue);text-decoration:none;">
                                Ouvrir le board →
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-app-layout>
