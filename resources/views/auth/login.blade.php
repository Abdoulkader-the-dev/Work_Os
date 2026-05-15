{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
@section('title', 'Connexion')

@if(session('status'))
    <div style="padding:10px 14px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;color:#16a34a;margin-bottom:16px;">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:16px;">
    @csrf

    {{-- Titre --}}
    <div style="margin-bottom:4px;">
        <h1 style="font-size:18px;font-weight:600;letter-spacing:-0.02em;">Connexion</h1>
        <p style="font-size:13px;color:var(--text-3);margin-top:3px;">Accédez à votre espace UniPod</p>
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
               autofocus
               autocomplete="username">
        @error('email')
            <p class="auth-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Mot de passe --}}
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
            <label for="password" class="auth-label" style="margin-bottom:0;">Mot de passe</label>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   style="font-size:12px;color:var(--blue);text-decoration:none;font-weight:500;"
                   onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>
        <input id="password"
               type="password"
               name="password"
               class="auth-input"
               placeholder="••••••••"
               required
               autocomplete="current-password">
        @error('password')
            <p class="auth-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Se souvenir de moi --}}
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--text-2);">
        <input type="checkbox"
               name="remember"
               style="width:15px;height:15px;border-radius:4px;accent-color:var(--blue);cursor:pointer;">
        Se souvenir de moi
    </label>

    {{-- Bouton --}}
    <button type="submit" class="auth-btn" style="margin-top:4px;">
        Se connecter
    </button>

    {{-- Lien register --}}
    @if(Route::has('register'))
        <p style="text-align:center;font-size:13px;color:var(--text-3);">
            Pas encore de compte ?
            <a href="{{ route('register') }}"
               style="color:var(--blue);text-decoration:none;font-weight:500;"
               onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                Créer un compte
            </a>
        </p>
    @endif

</form>
</x-guest-layout>