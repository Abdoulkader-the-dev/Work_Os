<div style="display:flex;flex-direction:column;gap:18px;">
    <div class="flex-between flex-wrap" style="gap:12px;">
        <div class="text-xs text-3 f-mono">
            {{ $items->count() }} tâche{{ $items->count() > 1 ? 's' : '' }}
        </div>
    </div>

    @if($items->isEmpty())
        <div style="text-align:center;padding:72px 24px;color:var(--text-3);">
            <div style="font-size:40px;margin-bottom:12px;">🗂️</div>
            <div class="text-md font-semibold text-1" style="margin-bottom:6px;">Aucune tâche assignée</div>
            <div class="text-sm">Tes tâches apparaîtront ici dès qu'elles te seront attribuées.</div>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;">
            @foreach($items as $item)
                <div class="bento-card" style="padding:18px;display:flex;flex-direction:column;gap:12px;" wire:key="my-task-{{ $item->id }}">
                    <div class="flex-between" style="align-items:flex-start;gap:10px;">
                        <div style="flex:1;min-width:0;">
                            <div class="text-md font-semibold text-1" style="letter-spacing:-0.015em;line-height:1.35;">
                                {{ $item->name }}
                            </div>
                            <div class="text-xs text-3 f-mono" style="margin-top:5px;">
                                {{ $item->group->board->name ?? 'Sans board' }} / {{ $item->group->name ?? 'Sans groupe' }}
                            </div>
                        </div>
                        <x-status-badge status="{{ $item->status }}" style="font-size:11px;" />
                    </div>

                    @if($item->deliverable)
                        <div class="text-sm text-2" style="line-height:1.5;">
                            {{ $item->deliverable }}
                        </div>
                    @endif

                    <div class="flex-between" style="padding-top:10px;border-top:1px solid var(--border);gap:12px;">
                        <div class="text-xs f-mono" style="color:{{ $item->deadline?->isPast() ? '#dc2626' : 'var(--text-3)' }};">
                            {{ $item->deadline ? 'Deadline ' . $item->deadline->format('d M Y') : 'Sans deadline' }}
                        </div>
                        @if($item->group?->board)
                            <a href="{{ route('boards.show', $item->group->board) }}"
                               class="text-sm font-medium" style="color:var(--blue);text-decoration:none;">
                                Ouvrir le board →
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
