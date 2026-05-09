<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniPod — Connexion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--color-bg);font-family:'DM Sans',sans-serif;">
    <div style="width:100%;max-width:400px;padding:16px;">
        <div style="text-align:center;margin-bottom:32px;">
            <span style="font-size:24px;font-weight:700;letter-spacing:-0.02em;">
                Uni<span style="color:var(--color-unipod-yellow);">Pod</span>
            </span>
        </div>
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
