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
    <livewire:partials.sidebar />

    <div id="sidebar-overlay" :class="{ 'open': mobileSidebarOpen }" @click="closeMobileSidebar()"></div>

    {{-- ══ ZONE PRINCIPALE ══ --}}
    <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;">

        <header id="topbar">

            <button class="icon-btn" style="display:none;" id="burger-btn" @click="toggleMobileSidebar()">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M2 4h12M2 8h12M2 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>

            <h1 class="text-xl font-semibold" style="letter-spacing:-0.02em;white-space:nowrap;">
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
                        <div class="text-sm font-semibold">{{ auth()->user()?->name }}</div>
                        <div class="text-xs text-3 f-mono" style="margin-top:1px;">{{ auth()->user()?->email }}</div>
                    </div>

                    <a href="{{ route('settings') }}"
                       class="flex-center" style="gap:8px;padding:7px 10px;border-radius:6px;font-size:13px;color:var(--text-2);text-decoration:none;transition:background .15s;"
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
                                class="flex-center" style="width:100%;gap:8px;padding:7px 10px;border-radius:6px;font-size:13px;color:#dc2626;background:none;border:none;font-family:'DM Sans',sans-serif;cursor:pointer;transition:background .15s;"
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

    <div id="global-loader" class="loading-bar" style="display:none;"></div>

    <script>
    document.addEventListener('livewire:navigating', () => {
        document.getElementById('global-loader').style.display = 'block';
    });
    document.addEventListener('livewire:navigated', () => {
        document.getElementById('global-loader').style.display = 'none';
    });
    </script>
</body>
</html>
