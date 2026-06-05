<x-app-layout>
@section('page-title', 'Paramètres')

<div class="settings-grid">
    <div class="bento-card page-card" data-tour-id="settings-profile">
        <div class="section-title">Profil</div>

        @if (session('status') === 'profile-updated')
            <div class="status-banner status-banner--success" style="margin-bottom:14px;">
                Profil mis à jour.
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" style="display:flex;flex-direction:column;gap:14px;">
            @csrf
            @method('PATCH')

            <label style="display:flex;flex-direction:column;gap:6px;">
                <span style="font-size:12px;font-weight:600;color:var(--text-2);">Nom</span>
                <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" class="form-control">
                @error('name')
                    <span style="font-size:12px;color:#dc2626;">{{ $message }}</span>
                @enderror
            </label>

            <label style="display:flex;flex-direction:column;gap:6px;">
                <span style="font-size:12px;font-weight:600;color:var(--text-2);">E-mail</span>
                <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" class="form-control">
                @error('email')
                    <span style="font-size:12px;color:#dc2626;">{{ $message }}</span>
                @enderror
            </label>

            <button type="submit" class="btn-primary" style="width:fit-content;">Enregistrer</button>
        </form>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="bento-card page-card" data-tour-id="settings-security">
            <div class="section-title">Sécurité</div>

            @if (session('status') === 'password-updated')
                <div class="status-banner status-banner--success" style="margin-bottom:14px;">
                    Mot de passe mis à jour.
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" style="display:flex;flex-direction:column;gap:14px;">
                @csrf
                @method('PUT')

                <label style="display:flex;flex-direction:column;gap:6px;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Mot de passe actuel</span>
                    <input type="password" name="current_password" class="form-control">
                    @error('current_password', 'updatePassword')
                        <span style="font-size:12px;color:#dc2626;">{{ $message }}</span>
                    @enderror
                </label>

                <label style="display:flex;flex-direction:column;gap:6px;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Nouveau mot de passe</span>
                    <input type="password" name="password" class="form-control">
                    @error('password', 'updatePassword')
                        <span style="font-size:12px;color:#dc2626;">{{ $message }}</span>
                    @enderror
                </label>

                <label style="display:flex;flex-direction:column;gap:6px;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Confirmation</span>
                    <input type="password" name="password_confirmation" class="form-control">
                </label>

            <button type="submit" class="btn-primary" style="width:fit-content;">Mettre à jour</button>
            </form>
        </div>

        <div class="bento-card page-card" style="border-color:#fecaca;">
            <div class="section-title" style="color:#991b1b;">Supprimer le compte</div>
            <div class="section-copy" style="margin-bottom:16px;">
                Cette action est définitive. Saisis ton mot de passe pour confirmer.
            </div>

            <form method="POST" action="{{ route('profile.destroy') }}" style="display:flex;flex-direction:column;gap:14px;">
                @csrf
                @method('DELETE')

                <label style="display:flex;flex-direction:column;gap:6px;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">Mot de passe</span>
                    <input type="password" name="password" class="form-control">
                    @error('password', 'userDeletion')
                        <span style="font-size:12px;color:#dc2626;">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit"
                        style="width:fit-content;height:40px;padding:0 14px;border:1px solid #fecaca;border-radius:10px;background:#fef2f2;color:#dc2626;font-size:13px;font-family:'DM Sans',sans-serif;cursor:pointer;">
                    Supprimer le compte
                </button>
            </form>
        </div>
    </div>
</div>
</x-app-layout>
