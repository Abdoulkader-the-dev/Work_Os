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
        <div class="empty-state bento-card empty-state--compact">
            <div class="empty-state__title">Aucun espace de travail actif</div>
            <div class="empty-state__copy">Créez ou sélectionnez un espace de travail pour afficher les membres.</div>
        </div>
    @else
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="font-size:12px;font-family:'DM Mono',monospace;color:var(--text-3);">
            {{ $members->count() }} membre{{ $members->count() > 1 ? 's' : '' }} dans {{ $workspace?->name ?? 'cet espace de travail' }}
        </div>
    </div>

    @if($isAdmin)
        <div class="bento-card members-card page-card--tight" style="display:grid;gap:12px;margin-top:14px;">
            <div>
                <div class="section-title">Partager cet espace de travail</div>
                <div class="section-copy">Ajoutez un utilisateur existant par e-mail et choisissez son rôle.</div>
            </div>
            <form method="POST" action="{{ route('workspaces.members.store', $workspace) }}" style="display:grid;grid-template-columns:minmax(0,1fr) 140px auto;gap:10px;align-items:end;">
                @csrf
                <label style="display:grid;gap:6px;">
                    <span style="font-size:11px;color:var(--text-3);">E-mail</span>
                    <input type="email" name="email" placeholder="utilisateur@exemple.com" class="form-control" required>
                </label>
                <label style="display:grid;gap:6px;">
                    <span style="font-size:11px;color:var(--text-3);">Rôle</span>
                    <select name="role" class="form-control">
                        <option value="member">Membre</option>
                        <option value="reader">Lecture seule</option>
                        <option value="admin">Admin</option>
                    </select>
                </label>
                <button type="submit" class="btn-primary" style="height:42px;">Partager</button>
            </form>
        </div>
    @else
        <div class="info-banner info-banner--success" style="margin-top:14px;">
            Cette vue est en lecture seule. Les actions de gestion des membres sont réservées aux administrateurs de l'espace de travail.
        </div>
    @endif

    @forelse($members as $member)
        @php
            $taskQuery = $member->items()->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id));
            $openCount = (clone $taskQuery)->where('status', '!=', 'done')->count();
            $doneCount = (clone $taskQuery)->where('status', 'done')->count();
            $blockedCount = (clone $taskQuery)->where('status', 'blocked')->count();
            $role = $workspace && $workspace->user_id === $member->id ? 'Propriétaire' : ($member->pivot->role ?? 'Membre');
        @endphp
        <div class="bento-card members-card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="width:44px;height:44px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;flex-shrink:0;">
                {{ strtoupper(substr($member->name, 0, 2)) }}
            </div>
            <div style="flex:1;min-width:220px;">
                <div style="font-size:14px;font-weight:600;">{{ $member->name }}</div>
                <div style="font-size:12px;color:var(--text-3);margin-top:2px;">{{ $member->email }}</div>
            </div>
            <span class="count-pill">
                {{ $role }}
            </span>
            <span class="count-pill">
                {{ $openCount }} ouvertes
            </span>
            <span class="count-pill" style="background:#dcfce7;color:#16a34a;">
                {{ $doneCount }} achevées
            </span>
            <span class="count-pill" style="background:#fee2e2;color:#dc2626;">
                {{ $blockedCount }} bloquées
            </span>
            @if($isAdmin && $workspace && $workspace->user_id !== $member->id)
                <form method="POST" action="{{ route('workspaces.members.update', [$workspace, $member]) }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    @csrf
                    @method('PATCH')
                    <select name="role" class="form-control" style="width:160px;height:38px;font-size:12px;">
                        <option value="member" @selected(($member->pivot->role ?? null) === 'member')>Membre</option>
                        <option value="reader" @selected(($member->pivot->role ?? null) === 'reader')>Lecture seule</option>
                        <option value="admin" @selected(($member->pivot->role ?? null) === 'admin')>Admin</option>
                    </select>
                    <button type="submit" class="btn-primary" style="height:38px;padding:0 12px;background:var(--bg);color:var(--text-1);border:1px solid var(--border);">Mettre à jour</button>
                </form>
                <form method="POST" action="{{ route('workspaces.members.destroy', [$workspace, $member]) }}" onsubmit="return confirm('Retirer ce membre de l\'espace de travail ?');" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="surface-menu__item surface-menu__item--danger" style="height:38px;padding:0 12px;">Retirer</button>
                </form>
            @endif
        </div>
    @empty
        <div class="empty-state bento-card empty-state--compact">
            <div class="empty-state__title">Aucun membre trouvé</div>
            <div class="empty-state__copy">L'espace de travail n'a encore aucun membre associé.</div>
        </div>
    @endforelse
    @endif
</div>
</x-app-layout>
