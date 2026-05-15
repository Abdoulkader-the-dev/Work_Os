<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>UniPod — @yield('title', 'Connexion')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --blue:        #0091CD;
            --blue-light:  #e6f5fb;
            --yellow:      #FFD100;
            --bg:          #f5f5f3;
            --surface:     #ffffff;
            --border:      rgba(0,0,0,0.07);
            --border-md:   rgba(0,0,0,0.14);
            --text-1:      #111110;
            --text-2:      #6b6b68;
            --text-3:      #a3a39f;
            --radius-card: 14px;
            --radius-btn:  8px;
        }
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        html { font-size:15px; -webkit-font-smoothing:antialiased; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text-1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            padding: 36px 32px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.06);
        }

        .auth-input {
            width: 100%;
            padding: 10px 14px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-1);
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-btn);
            outline: none;
            transition: border-color .15s, background .15s;
        }
        .auth-input:focus {
            border-color: var(--blue);
            background: white;
        }
        .auth-input::placeholder { color: var(--text-3); }

        .auth-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-2);
            margin-bottom: 5px;
        }

        .auth-btn {
            width: 100%;
            padding: 11px 16px;
            background: var(--text-1);
            color: white;
            border: none;
            border-radius: var(--radius-btn);
            font-size: 14px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: background .15s, transform .15s;
        }
        .auth-btn:hover { background: #2a2a28; transform: translateY(-1px); }
        .auth-btn:active { transform: translateY(0); }

        .auth-error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        {{-- Logo --}}
        <div style="text-align:center;margin-bottom:28px;">
            <div style="display:inline-flex;align-items:center;gap:8px;margin-bottom:8px;">
                <div style="width:32px;height:32px;background:var(--text-1);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <rect x="1" y="1" width="6" height="6" rx="1.5" fill="white"/>
                        <rect x="9" y="1" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>
                        <rect x="1" y="9" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>
                        <rect x="9" y="9" width="6" height="6" rx="1.5" fill="white" opacity=".3"/>
                    </svg>
                </div>
                <span style="font-size:20px;font-weight:700;letter-spacing:-0.02em;">
                    Uni<span style="color:var(--yellow);">Pod</span>
                </span>
            </div>
            <p style="font-size:13px;color:var(--text-3);">Work OS — UniPod</p>
        </div>

        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>