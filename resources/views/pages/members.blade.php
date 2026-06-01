<x-app-layout>
@section('page-title', 'Membres')

@php
    $workspace = auth()->user()?->activeWorkspace;
    $members = collect([$workspace?->owner])
        ->merge($workspace?->members ?? collect())
        ->filter()
        ->unique('id')
        ->values();
@endphp

<div style="display:flex;flex-direction:column;gap:14px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="font-size:12px;font-family:'DM Mono',monospace;color:var(--text-3);">
            {{ $members->count() }} membre{{ $members->count() > 1 ? 's' : '' }} dans {{ $workspace?->name ?? 'ce workspace' }}
        </div>
    </div>

    @forelse($members as $member)
        @php
            $taskQuery = $member->items()->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id));
            $openCount = (clone $taskQuery)->where('status', '!=', 'done')->count();
            $doneCount = (clone $taskQuery)->where('status', 'done')->count();
            $blockedCount = (clone $taskQuery)->where('status', 'blocked')->count();
            $role = $workspace && $workspace->user_id === $member->id ? 'Owner' : ($member->pivot->role ?? 'Membre');
        @endphp
        <div class="bento-card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="width:44px;height:44px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;flex-shrink:0;">
                {{ strtoupper(substr($member->name, 0, 2)) }}
            </div>
            <div style="flex:1;min-width:220px;">
                <div style="font-size:14px;font-weight:600;">{{ $member->name }}</div>
                <div style="font-size:12px;color:var(--text-3);margin-top:2px;">{{ $member->email }}</div>
            </div>
            <span style="font-size:11px;font-family:'DM Mono',monospace;padding:3px 10px;background:var(--bg);border-radius:20px;color:var(--text-2);">
                {{ $role }}
            </span>
            <span style="font-size:11px;font-family:'DM Mono',monospace;padding:3px 10px;background:var(--bg);border-radius:20px;color:var(--text-2);">
                {{ $openCount }} ouvertes
            </span>
            <span style="font-size:11px;font-family:'DM Mono',monospace;padding:3px 10px;background:#dcfce7;border-radius:20px;color:#16a34a;">
                {{ $doneCount }} achevées
            </span>
            <span style="font-size:11px;font-family:'DM Mono',monospace;padding:3px 10px;background:#fee2e2;border-radius:20px;color:#dc2626;">
                {{ $blockedCount }} bloquées
            </span>
        </div>
    @empty
        <div style="text-align:center;padding:72px 24px;color:var(--text-3);border:1px dashed var(--border-md);border-radius:14px;background:white;">
            <div style="font-size:40px;margin-bottom:12px;">👥</div>
            <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:6px;">Aucun membre trouvé</div>
            <div style="font-size:13px;">Le workspace n'a encore aucun membre associé.</div>
        </div>
    @endforelse
</div>
</x-app-layout>
