<x-app-layout>
@section('page-title', 'Paramètres')
<div class="bento-card" style="padding:32px;max-width:560px;">
    <h2 style="font-size:16px;font-weight:600;margin-bottom:20px;">Profil</h2>
    <div style="display:flex;flex-direction:column;gap:14px;">
        <div>
            <label style="font-size:12px;font-weight:500;color:var(--color-text-2);display:block;margin-bottom:5px;">Nom</label>
            <input type="text" value="{{ auth()->user()?->name }}" style="width:100%;padding:9px 12px;border:1px solid var(--color-border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;" onfocus="this.style.borderColor='var(--color-unipod-blue)'" onblur="this.style.borderColor='var(--color-border)'">
        </div>
        <div>
            <label style="font-size:12px;font-weight:500;color:var(--color-text-2);display:block;margin-bottom:5px;">Email</label>
            <input type="email" value="{{ auth()->user()?->email }}" style="width:100%;padding:9px 12px;border:1px solid var(--color-border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;" onfocus="this.style.borderColor='var(--color-unipod-blue)'" onblur="this.style.borderColor='var(--color-border)'">
        </div>
        <button class="btn-primary" style="width:fit-content;margin-top:6px;">Sauvegarder</button>
    </div>
</div>
</x-app-layout>