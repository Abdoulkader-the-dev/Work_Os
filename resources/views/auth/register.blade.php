{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
@section('title', 'Créer un compte')

<form method="POST" action="{{ route('register') }}" style="display:flex;flex-direction:column;gap:16px;">
    @csrf

    {{-- Titre --}}
    <div style="margin-bottom:4px;">
        <h1 style="font-size:18px;font-weight:600;letter-spacing:-0.02em;">Créer un compte</h1>
        <p style="font-size:13px;color:var(--text-3);margin-top:3px;">Rejoignez votre équipe sur UniPod</p>
    </div>

    {{-- Nom --}}
    <div>
        <label for="name" class="auth-label">Nom complet</label>
        <input id="name"
               type="text"
               name="name"
               value="{{ old('name') }}"
               class="auth-input"
               placeholder="Caleb Messohounsounou"
               required
               autofocus
               autocomplete="name">
        @error('name')
            <p class="auth-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="auth-label">Adresse email</label>
        <input id="email"
               type="email"
               name="email"
               value="{{ old('email') }}"
               class="auth-input"
               placeholder="caleb@unipod.com"
               required
               autocomplete="username">
        @error('email')
            <p class="auth-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Mot de passe --}}
    <div>
        <label for="password" class="auth-label">Mot de passe</label>
        <input id="password"
               type="password"
               name="password"
               class="auth-input"
               placeholder="••••••••"
               required
               autocomplete="new-password">
        @error('password')
            <p class="auth-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Confirmer mot de passe --}}
    <div>
        <label for="password_confirmation" class="auth-label">Confirmer le mot de passe</label>
        <input id="password_confirmation"
               type="password"
               name="password_confirmation"
               class="auth-input"
               placeholder="••••••••"
               required
               autocomplete="new-password">
        @error('password_confirmation')
            <p class="auth-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Bouton --}}
    <button type="submit" class="auth-btn" style="margin-top:4px;">
        Créer mon compte
    </button>

    {{-- Lien login --}}
    <p style="text-align:center;font-size:13px;color:var(--text-3);">
        Déjà un compte ?
        <a href="{{ route('login') }}"
           style="color:var(--blue);text-decoration:none;font-weight:500;"
           onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
            Se connecter
        </a>
    </p>

</form>
</x-guest-layout>
