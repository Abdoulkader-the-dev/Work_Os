<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'UniPod') }} — @yield('title', 'Work OS')</title>

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
            --yellow-light:#fff8d6;
            --bg:          #f5f5f3;
            --surface:     #ffffff;
            --border:      rgba(0,0,0,0.07);
            --border-md:   rgba(0,0,0,0.14);
            --text-1:      #111110;
            --text-2:      #6b6b68;
            --text-3:      #a3a39f;
            --sidebar-w:   220px;
            --topbar-h:    60px;
            --radius-card: 14px;
            --radius-btn:  8px;
            --ease-bounce: cubic-bezier(0.34,1.56,0.64,1);
            --ease-out:    cubic-bezier(0.4,0,0.2,1);
        }

        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        html { font-size:15px; -webkit-font-smoothing:antialiased; }
        body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text-1); display:flex; height:100vh; overflow:hidden; }

        .bento-card {
            background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-card);
            transition: transform 0.22s var(--ease-bounce), box-shadow 0.22s var(--ease-out), border-color 0.18s var(--ease-out);
        }
        .bento-card:hover { transform:translateY(-4px) scale(1.01); box-shadow:0 16px 40px rgba(0,0,0,0.09),0 4px 12px rgba(0,0,0,0.05); border-color:var(--border-md); }
        .bento-card:active { transform:translateY(-1px) scale(1.005); }

        .s-done     { background:#dcfce7; color:#16a34a; }
        .s-progress { background:var(--blue-light); color:var(--blue); }
        .s-todo     { background:#f4f4f3; color:var(--text-2); }
        .s-blocked  { background:#fee2e2; color:#dc2626; }
        .s-ongoing  { background:#fff4e5; color:#ea580c; }

        #sidebar {
            width:var(--sidebar-w); min-width:var(--sidebar-w);
            background:var(--surface); border-right:1px solid var(--border);
            display:flex; flex-direction:column; height:100vh;
            position:relative; z-index:30;
            transition:transform 0.25s var(--ease-out);
        }

        #topbar {
            height:var(--topbar-h); min-height:var(--topbar-h);
            background:var(--surface); border-bottom:1px solid var(--border);
            display:flex; align-items:center; gap:14px; padding:0 28px;
            position:relative; z-index:50; overflow:visible; flex-shrink:0;
        }

        #content { flex:1; overflow-y:auto; padding:28px; }

        #sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.3); z-index:29; }

        @media (max-width:768px) {
            #sidebar { position:fixed; left:0; top:0; bottom:0; transform:translateX(-100%); }
            #sidebar.open { transform:translateX(0); box-shadow:8px 0 32px rgba(0,0,0,0.12); }
            #sidebar-overlay.open { display:block; }
            #content { padding:20px 16px; }
        }

        .nav-item {
            display:flex; align-items:center; gap:10px; padding:7px 10px;
            border-radius:var(--radius-btn); font-size:14px; color:var(--text-2);
            cursor:pointer; text-decoration:none; transition:background .15s,color .15s;
        }
        .nav-item:hover  { background:var(--bg); color:var(--text-1); }
        .nav-item.active { background:var(--text-1); color:#fff; font-weight:500; }
        .nav-item.active svg { filter:invert(1); }
        .nav-item.active .nav-badge { background:rgba(255,255,255,0.2); color:#fff; }

        .nav-badge {
            margin-left:auto; background:var(--yellow); color:var(--text-1);
            font-size:10px; font-weight:600; font-family:'DM Mono',monospace;
            padding:1px 6px; border-radius:20px; line-height:1.6;
        }

        .nav-section-label {
            font-size:10px; font-weight:500; letter-spacing:.08em; text-transform:uppercase;
            color:var(--text-3); padding:0 10px; margin:16px 0 5px;
        }

        .view-switcher { display:flex; gap:2px; background:var(--bg); border-radius:var(--radius-btn); padding:3px; }
        .view-btn {
            padding:5px 13px; border-radius:6px; font-size:12px; font-weight:500;
            color:var(--text-2); cursor:pointer; border:none; background:none;
            font-family:'DM Sans',sans-serif; transition:all .15s; text-decoration:none;
        }
        .view-btn.active { background:var(--surface); color:var(--text-1); box-shadow:0 1px 4px rgba(0,0,0,0.1); }
        .view-btn:hover:not(.active) { color:var(--text-1); }

        .search-box {
            display:flex; align-items:center; gap:8px; background:var(--bg);
            border:1px solid var(--border); border-radius:var(--radius-btn);
            padding:7px 12px; font-size:13px; color:var(--text-3); width:200px;
            transition:border-color .15s,width .25s var(--ease-out);
        }
        .search-box:focus-within { border-color:var(--blue); width:260px; }
        .search-box input { background:none; border:none; outline:none; font-size:13px; font-family:'DM Sans',sans-serif; color:var(--text-1); width:100%; }
        .search-box input::placeholder { color:var(--text-3); }

        .icon-btn {
            width:36px; height:36px; border-radius:var(--radius-btn);
            background:var(--bg); border:1px solid var(--border);
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:background .15s,border-color .15s;
            position:relative; color:var(--text-2);
        }
        .icon-btn:hover { background:#e8e8e6; border-color:var(--border-md); }

        .user-avatar {
            width:36px; height:36px; border-radius:50%;
            background:var(--text-1); color:#fff; font-size:12px; font-weight:600;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; letter-spacing:.03em;
            border:2px solid transparent; transition:border-color .15s;
        }
        .user-avatar:hover { border-color:var(--border-md); }

        .btn-primary {
            display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
            background:var(--text-1); color:#fff; border:none;
            border-radius:var(--radius-btn); font-size:13px; font-weight:500;
            font-family:'DM Sans',sans-serif; cursor:pointer; text-decoration:none;
            transition:background .15s,transform .15s;
        }
        .btn-primary:hover { background:#2a2a28; transform:translateY(-1px); }
        .btn-primary:active { transform:translateY(0); }

        .workspace-selector {
            display:flex; align-items:center; gap:8px; padding:8px 10px;
            background:var(--bg); border-radius:var(--radius-btn);
            cursor:pointer; transition:background .15s;
        }
        .workspace-selector:hover { background:#e8e8e6; }

        ::-webkit-scrollbar { width:5px; height:5px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:rgba(0,0,0,0.12); border-radius:10px; }
        ::-webkit-scrollbar-thumb:hover { background:rgba(0,0,0,0.22); }
    </style>
</head>

<body
    x-data="{
        mobileSidebarOpen: false,
        toggleMobileSidebar() {
            this.mobileSidebarOpen = !this.mobileSidebarOpen;
            document.getElementById('sidebar').classList.toggle('open', this.mobileSidebarOpen);
            document.getElementById('sidebar-overlay').classList.toggle('open', this.mobileSidebarOpen);
        },
        closeMobileSidebar() {
            this.mobileSidebarOpen = false;
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebar-overlay').classList.remove('open');
        }
    }"
    @keydown.escape.window="closeMobileSidebar()">

    {{-- ══ SIDEBAR ══ --}}
    <aside id="sidebar" :class="{ 'open': mobileSidebarOpen }">

        <div style="padding:22px 20px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;background:var(--text-1);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <rect x="1" y="1" width="6" height="6" rx="1.5" fill="white"/>
                    <rect x="9" y="1" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>
                    <rect x="1" y="9" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>
                    <rect x="9" y="9" width="6" height="6" rx="1.5" fill="white" opacity=".3"/>
                </svg>
            </div>
            <span style="font-size:17px;font-weight:600;letter-spacing:-0.02em;">
                Uni<span style="color:var(--yellow);">Pod</span>
            </span>
        </div>

        <nav style="flex:1;padding:14px 12px;overflow-y:auto;">

            <div class="nav-section-label">Principal</div>

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                    <rect x="1" y="1" width="6.5" height="6.5" rx="1.5" fill="currentColor"/>
                    <rect x="8.5" y="1" width="6.5" height="6.5" rx="1.5" fill="currentColor"/>
                    <rect x="1" y="8.5" width="6.5" height="6.5" rx="1.5" fill="currentColor"/>
                    <rect x="8.5" y="8.5" width="6.5" height="6.5" rx="1.5" fill="currentColor"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('boards.index') }}" class="nav-item {{ request()->routeIs('boards.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                    <rect x="1" y="1" width="14" height="3" rx="1" fill="currentColor"/>
                    <rect x="1" y="6.5" width="14" height="3" rx="1" fill="currentColor"/>
                    <rect x="1" y="12" width="9" height="3" rx="1" fill="currentColor"/>
                </svg>
                Boards
            </a>

            <a href="{{ route('meetings.index') }}" class="nav-item {{ request()->routeIs('meetings.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                    <rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M5 1v2M11 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M1 6h14" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                Réunions
            </a>

            <div class="nav-section-label">Personnel</div>

            <a href="{{ route('my-tasks') }}" class="nav-item {{ request()->routeIs('my-tasks') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Mes tâches
                @php $myTasksCount = auth()->user()?->items()->whereNotIn('status',['done'])->count() ?? 0; @endphp
                @if($myTasksCount > 0)
                    <span class="nav-badge">{{ $myTasksCount }}</span>
                @endif
            </a>

            <a href="{{ route('calendar') }}" class="nav-item {{ request()->routeIs('calendar') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                    <rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M1 6h14M5 1v2M11 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <rect x="4" y="8.5" width="2" height="2" rx=".5" fill="currentColor"/>
                    <rect x="7" y="8.5" width="2" height="2" rx=".5" fill="currentColor"/>
                    <rect x="10" y="8.5" width="2" height="2" rx=".5" fill="currentColor"/>
                </svg>
                Calendrier
            </a>

            <a href="{{ route('reports') }}" class="nav-item {{ request()->routeIs('reports') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                    <path d="M2 12L5.5 8l3 3L12 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1 15h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                Rapports
            </a>

            <div class="nav-section-label">Système</div>

            <a href="{{ route('members') }}" class="nav-item {{ request()->routeIs('members') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                    <circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M1 14c0-2.76 2.24-5 5-5s5 2.24 5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M11 7c1.66 0 3 1.34 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                Membres
            </a>

            <a href="{{ route('settings') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                    <circle cx="8" cy="8" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M8 1v1.5M8 13.5V15M1 8h1.5M13.5 8H15M2.93 2.93l1.06 1.06M12.01 12.01l1.06 1.06M2.93 13.07l1.06-1.06M12.01 3.99l1.06-1.06" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                Paramètres
            </a>

        </nav>

        <div x-data="{ open: false }" style="padding:12px;border-top:1px solid var(--border);position:relative;">
            <div style="font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);padding:0 2px;margin-bottom:6px;">Workspace</div>
            <div class="workspace-selector" @click="open = !open" @click.outside="open = false">
                <div style="width:8px;height:8px;border-radius:50%;background:var(--blue);flex-shrink:0;"></div>
                <span style="font-size:13px;font-weight:500;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ auth()->user()?->workspaces()->first()?->name ?? 'UniPod HQ' }}
                </span>
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="color:var(--text-3);flex-shrink:0;transition:transform .2s;" :style="open ? 'transform:rotate(180deg)' : ''">
                    <path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            {{-- Dropdown workspace --}}
            <div x-cloak
                 x-show="open"
                  style="display:none;position:absolute;bottom:68px;left:12px;right:12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-btn);box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;z-index:50;">
                @foreach(auth()->user()?->workspaces ?? [] as $ws)
                    <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:6px;font-size:13px;cursor:pointer;transition:background .15s;"
                         onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $ws->color ?? 'var(--blue)' }};"></div>
                        {{ $ws->name }}
                    </div>
                @endforeach
            </div>
        </div>

    </aside>

    <div id="sidebar-overlay" :class="{ 'open': mobileSidebarOpen }" @click="closeMobileSidebar()"></div>

    {{-- ══ ZONE PRINCIPALE ══ --}}
    <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;">

        <header id="topbar">

            <button class="icon-btn" style="display:none;" id="burger-btn" @click="toggleMobileSidebar()">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M2 4h12M2 8h12M2 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>

            <h1 style="font-size:18px;font-weight:600;letter-spacing:-0.02em;white-space:nowrap;">
                @yield('page-title', 'Dashboard')
            </h1>

            @hasSection('view-switcher')
                <div class="view-switcher" style="margin-left:8px;">@yield('view-switcher')</div>
            @endif

            <div style="flex:1;"></div>

            @hasSection('topbar-action')
                @yield('topbar-action')
            @endif

            <div class="search-box" role="search">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="flex-shrink:0;color:var(--text-3);">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M9.5 9.5L13 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <input type="text" placeholder="Rechercher...">
            </div>

            {{-- Notifications --}}
            <livewire:partials.notifications />

            {{-- User menu --}}
            <div style="position:relative;z-index:70;">
                <div class="user-avatar"
                     data-dropdown-trigger="profile-menu"
                     aria-controls="profile-menu"
                     aria-expanded="false"
                     tabindex="0" role="button">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
                </div>

                <div id="profile-menu"
                     data-dropdown-menu
                     hidden
                     style="position:absolute;right:0;top:calc(100% + 8px);width:200px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-card);box-shadow:0 12px 32px rgba(0,0,0,0.1);padding:6px;z-index:60;">

                    <div style="padding:10px 12px 8px;border-bottom:1px solid var(--border);margin-bottom:4px;">
                        <div style="font-size:13px;font-weight:600;">{{ auth()->user()?->name }}</div>
                        <div style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;margin-top:1px;">{{ auth()->user()?->email }}</div>
                    </div>

                    <a href="{{ route('settings') }}"
                       style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:13px;color:var(--text-2);text-decoration:none;transition:background .15s;"
                       onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <circle cx="7" cy="7" r="2" stroke="currentColor" stroke-width="1.4"/>
                            <path d="M7 1v1M7 12v1M1 7h1M12 7h1M2.87 2.87l.7.7M10.43 10.43l.7.7M2.87 11.13l.7-.7M10.43 3.57l.7-.7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        </svg>
                        Paramètres
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                style="width:100%;display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:13px;color:#dc2626;background:none;border:none;font-family:'DM Sans',sans-serif;cursor:pointer;transition:background .15s;"
                                onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background=''">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M9 1H12a1 1 0 011 1v10a1 1 0 01-1 1H9M6 10l3-3-3-3M9 7H1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>

        </header>

        <main id="content" role="main">
            {{ $slot }}
        </main>

    </div>
{{-- Item Panel global (écoute les events de toutes les vues) --}}
<livewire:items.item-panel />
    @livewireScripts

    <script>
    function checkBurger() {
        const b = document.getElementById('burger-btn');
        if (b) b.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
    }

    function closeDropdownMenus(exceptId = null) {
        document.querySelectorAll('[data-dropdown-menu]').forEach((menu) => {
            const menuId = menu.id;
            const trigger = document.querySelector(`[data-dropdown-trigger="${menuId}"]`);
            const shouldStayOpen = exceptId && menuId === exceptId;

            menu.hidden = !shouldStayOpen;

            if (trigger) {
                trigger.setAttribute('aria-expanded', shouldStayOpen ? 'true' : 'false');
            }
        });
    }

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-dropdown-trigger]');

        if (trigger) {
            const menuId = trigger.getAttribute('data-dropdown-trigger');
            const menu = document.getElementById(menuId);

            if (!menu) return;

            const willOpen = menu.hidden;
            closeDropdownMenus(willOpen ? menuId : null);
            return;
        }

        if (!event.target.closest('[data-dropdown-menu]')) {
            closeDropdownMenus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdownMenus();
        }
    });

    document.addEventListener('keydown', (event) => {
        const trigger = event.target.closest('[data-dropdown-trigger]');

        if (!trigger) return;

        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            trigger.click();
        }
    });

    checkBurger();
    window.addEventListener('resize', checkBurger);
    </script>

    @stack('scripts')

</body>
</html>
