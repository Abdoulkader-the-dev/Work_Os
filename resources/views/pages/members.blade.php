<x-app-layout>
@section('page-title', 'Membres')

@php
    $workspace = auth()->user()?->activeWorkspace;
    $members = collect([$workspace?->owner])
        ->merge($workspace?->members ?? collect())
        ->filter()
        ->unique('id')
        ->values();
    $isAdmin = auth()->user()?->can('manageMembers', $workspace);
@endphp

<div class="members-list" data-tour-id="members-list">
    @if(!$workspace)
        <div style="text-align:center;padding:72px 24px;color:var(--text-3);border:1px dashed var(--border-md);border-radius:14px;background:white;">
            <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:6px;">Aucun workspace actif</div>
            <div style="font-size:13px;">Créez ou sélectionnez un workspace pour afficher les membres.</div>
        </div>
    @else
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="font-size:12px;font-family:'DM Mono',monospace;color:var(--text-3);">
            {{ $members->count() }} membre{{ $members->count() > 1 ? 's' : '' }} dans {{ $workspace?->name ?? 'ce workspace' }}
        </div>
    </div>

    @if($isAdmin)
        <div class="bento-card members-card" style="padding:18px 20px;display:grid;gap:12px;margin-top:14px;">
            <div>
                <div style="font-size:14px;font-weight:600;">Partager ce workspace</div>
                <div style="font-size:12px;color:var(--text-3);margin-top:4px;">Ajoutez un utilisateur existant par e-mail et choisissez son rôle.</div>
            </div>
            <form method="POST" action="{{ route('workspaces.members.store', $workspace) }}" style="display:grid;grid-template-columns:minmax(0,1fr) 140px auto;gap:10px;align-items:end;">
                @csrf
                <label style="display:grid;gap:6px;">
                    <span style="font-size:11px;color:var(--text-3);">E-mail</span>
                    <input type="email" name="email" placeholder="utilisateur@exemple.com" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text-1);" required>
                </label>
                <label style="display:grid;gap:6px;">
                    <span style="font-size:11px;color:var(--text-3);">Rôle</span>
                    <select name="role" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text-1);">
                        <option value="member">Membre</option>
                        <option value="reader">Lecture seule</option>
                        <option value="admin">Admin</option>
                    </select>
                </label>
                <button type="submit" style="padding:11px 14px;border:none;border-radius:10px;background:var(--text-1);color:#fff;font-weight:600;cursor:pointer;">Partager</button>
            </form>
        </div>
    @endif

    @forelse($members as $member)
        @php
            $taskQuery = $member->items()->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id));
            $openCount = (clone $taskQuery)->where('status', '!=', 'done')->count();
            $doneCount = (clone $taskQuery)->where('status', 'done')->count();
            $blockedCount = (clone $taskQuery)->where('status', 'blocked')->count();
            $role = $workspace && $workspace->user_id === $member->id ? 'Owner' : ($member->pivot->role ?? 'Membre');
        @endphp
        <div class="bento-card members-card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
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
            @if($isAdmin && $workspace && $workspace->user_id !== $member->id)
                <form method="POST" action="{{ route('workspaces.members.update', [$workspace, $member]) }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    @csrf
                    @method('PATCH')
                    <select name="role" style="padding:8px 10px;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text-1);font-size:12px;">
                        <option value="member" @selected(($member->pivot->role ?? null) === 'member')>Membre</option>
                        <option value="reader" @selected(($member->pivot->role ?? null) === 'reader')>Lecture seule</option>
                        <option value="admin" @selected(($member->pivot->role ?? null) === 'admin')>Admin</option>
                    </select>
                    <button type="submit" style="padding:8px 10px;border:none;border-radius:10px;background:var(--bg);color:var(--text-1);font-size:12px;cursor:pointer;">Mettre à jour</button>
                </form>
                <form method="POST" action="{{ route('workspaces.members.destroy', [$workspace, $member]) }}" onsubmit="return confirm('Retirer ce membre du workspace ?');" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding:8px 10px;border:none;border-radius:10px;background:#fee2e2;color:#dc2626;font-size:12px;cursor:pointer;">Retirer</button>
                </form>
            @endif
        </div>
    @empty
        <div style="text-align:center;padding:72px 24px;color:var(--text-3);border:1px dashed var(--border-md);border-radius:14px;background:white;">
            <div style="font-size:40px;margin-bottom:12px;">👥</div>
            <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:6px;">Aucun membre trouvé</div>
            <div style="font-size:13px;">Le workspace n'a encore aucun membre associé.</div>
        </div>
    @endforelse
    @endif
</div>
</x-app-layout>
