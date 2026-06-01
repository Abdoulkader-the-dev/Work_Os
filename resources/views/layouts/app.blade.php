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
    data-onboarding-start="{{ session('onboarding') === 'start' || auth()->user()?->shouldShowOnboarding() ? '1' : '0' }}"
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

            <button class="icon-btn" style="display:none;" id="burger-btn" @click="toggleMobileSidebar()" data-tour-id="mobile-menu">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M2 4h12M2 8h12M2 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>

            <h1 class="text-xl font-semibold" style="letter-spacing:-0.02em;white-space:nowrap;" data-tour-id="page-title">
                @yield('page-title', 'Dashboard')
            </h1>

            @php
                $currentWorkspace = auth()->user()?->activeWorkspace;
                $currentRole = auth()->user()?->workspaceRole($currentWorkspace);
                $currentRoleLabel = match ($currentRole) {
                    'admin' => 'Admin',
                    'member' => 'Membre',
                    'reader' => 'Lecture seule',
                    default => null,
                };
            @endphp

            @if($currentRoleLabel)
                <span style="font-size:10px;font-family:'DM Mono',monospace;padding:4px 8px;border-radius:999px;background:var(--bg);color:var(--text-2);border:1px solid var(--border);margin-left:8px;">
                    {{ $currentRoleLabel }}
                </span>
            @endif

            @hasSection('view-switcher')
                <div class="view-switcher" style="margin-left:8px;" data-tour-id="view-switcher">@yield('view-switcher')</div>
            @endif

            <div style="flex:1;"></div>

            @hasSection('topbar-action')
                <div data-tour-id="topbar-action">@yield('topbar-action')</div>
            @endif

            <div class="search-box" role="search" data-tour-id="global-search">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="flex-shrink:0;color:var(--text-3);">
                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M9.5 9.5L13 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <input type="text" placeholder="Rechercher...">
            </div>

            {{-- Notifications --}}
            <div data-tour-id="notifications">
                <livewire:partials.notifications />
            </div>

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

    <div id="onboarding-overlay" style="position:fixed;inset:0;display:none;z-index:9998;pointer-events:none;"></div>
    <div id="onboarding-tooltip" style="position:fixed;display:none;z-index:9999;max-width:340px;background:var(--text-1);color:white;border-radius:18px;padding:16px 18px;box-shadow:0 24px 60px rgba(0,0,0,0.22);pointer-events:auto;border:1px solid rgba(255,255,255,0.08);backdrop-filter:blur(4px);">
        <div id="onboarding-step" style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.62);margin-bottom:8px;">Tutoriel</div>
        <div id="onboarding-title" style="font-size:15px;font-weight:700;line-height:1.3;margin-bottom:8px;"></div>
        <div id="onboarding-body" style="font-size:12px;line-height:1.55;color:rgba(255,255,255,.88);"></div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:14px;flex-wrap:wrap;">
            <button type="button" id="onboarding-skip" style="background:none;border:none;color:rgba(255,255,255,.72);font-size:12px;cursor:pointer;padding:0;">Passer</button>
            <div style="display:flex;gap:8px;flex-wrap:wrap;width:100%;justify-content:flex-end;">
                <button type="button" id="onboarding-prev" class="view-btn" style="background:rgba(255,255,255,.08);color:white;border:1px solid rgba(255,255,255,.12);min-width:84px;">Préc.</button>
                <button type="button" id="onboarding-next" class="btn-primary" style="background:var(--yellow);color:var(--text-1);min-width:108px;">Suivant</button>
            </div>
        </div>
    </div>

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

    <script>
    (function () {
        const shouldStart = document.body.dataset.onboardingStart === '1';
        const storageKey = 'unipod-onboarding-completed';
        const overlay = document.getElementById('onboarding-overlay');
        const tooltip = document.getElementById('onboarding-tooltip');
        const titleEl = document.getElementById('onboarding-title');
        const bodyEl = document.getElementById('onboarding-body');
        const stepEl = document.getElementById('onboarding-step');
        const prevBtn = document.getElementById('onboarding-prev');
        const nextBtn = document.getElementById('onboarding-next');
        const skipBtn = document.getElementById('onboarding-skip');

        if (!overlay || !tooltip || !titleEl || !bodyEl || !stepEl || !prevBtn || !nextBtn || !skipBtn) return;

        const routes = [
            {
                test: (p) => p === '/' || p === '/dashboard',
                steps: [
                    { anchor: '[data-tour-id="page-title"]', title: 'Bienvenue', body: 'Voici votre tableau de bord. Il résume l’activité du workspace et les priorités du moment.' },
                    { anchor: '[data-tour-id="global-search"]', title: 'Recherche', body: 'Ce champ sert à retrouver rapidement une information dans l’application.' },
                    { anchor: '[data-tour-id="notifications"]', title: 'Notifications', body: 'Ici arrivent les mentions, affectations et alertes importantes.' },
                    { anchor: '[data-tour-id="sidebar-dashboard"]', title: 'Navigation', body: 'Le menu latéral permet de passer entre les grandes pages du produit.' },
                    { anchor: '[data-tour-id="workspace-switcher"]', title: 'Workspace', body: 'Vous pouvez changer d’espace de travail ou en créer un nouveau depuis ce sélecteur.' },
                    { anchor: '[data-tour-id="dashboard-timer"]', title: 'Chrono', body: 'Ce chrono permet de suivre le temps de travail directement depuis le dashboard.' },
                ],
            },
            {
                test: (p) => p.startsWith('/boards'),
                steps: [
                    { anchor: '[data-tour-id="view-switcher"]', title: 'Vues du board', body: 'Passez de Tableau à Kanban ou Calendrier selon votre besoin.' },
                    { anchor: '[data-tour-id="topbar-action"]', title: 'Créer', body: 'Ce bouton permet d’ajouter une tâche rapidement dans le board courant.' },
                    { anchor: '[data-tour-id="sidebar-boards"]', title: 'Boards', body: 'Ici vous retrouvez la liste des boards disponibles dans le workspace.' },
                ],
            },
            {
                test: (p) => p.startsWith('/meetings'),
                steps: [
                    { anchor: '[data-tour-id="topbar-action"]', title: 'Nouveau CR', body: 'Créez ou ouvrez un compte rendu de réunion depuis cette action.' },
                    { anchor: '[data-tour-id="meeting-toolbar"]', title: 'Filtrer et trier', body: 'La barre d’outils permet de rechercher et d’ordonner les réunions.' },
                ],
            },
            {
                test: (p) => p.startsWith('/my-tasks'),
                steps: [
                    { anchor: '[data-tour-id="my-tasks-list"]', title: 'Mes tâches', body: 'Cette vue liste vos tâches assignées et leur état.' },
                ],
            },
            {
                test: (p) => p.startsWith('/calendar'),
                steps: [
                    { anchor: '[data-tour-id="calendar-toolbar"]', title: 'Calendrier', body: 'Naviguez dans le temps et repérez rapidement les échéances.' },
                ],
            },
            {
                test: (p) => p.startsWith('/reports'),
                steps: [
                    { anchor: '[data-tour-id="reports-summary"]', title: 'Rapports', body: 'Cette page montre les indicateurs principaux et la performance du workspace.' },
                ],
            },
            {
                test: (p) => p.startsWith('/members'),
                steps: [
                    { anchor: '[data-tour-id="members-list"]', title: 'Membres', body: 'Vous voyez ici les membres du workspace et leurs tâches.' },
                ],
            },
            {
                test: (p) => p.startsWith('/settings'),
                steps: [
                    { anchor: '[data-tour-id="settings-profile"]', title: 'Profil', body: 'Les réglages personnels se font ici: nom, email et mot de passe.' },
                    { anchor: '[data-tour-id="settings-security"]', title: 'Sécurité', body: 'Vous pouvez aussi gérer le mot de passe et la suppression du compte.' },
                ],
            },
            {
                test: (p) => p.startsWith('/notifications'),
                steps: [
                    { anchor: '[data-tour-id="notifications-list"]', title: 'Notifications', body: 'Cette page affiche toutes vos alertes, mentions et rappels.' },
                ],
            },
        ];

        const route = routes.find((item) => item.test(window.location.pathname));
        if (!route || !shouldStart || localStorage.getItem(storageKey) === '1') return;

        let index = 0;
        let currentRect = null;

        const hide = () => {
            overlay.style.display = 'none';
            tooltip.style.display = 'none';
            document.querySelectorAll('[data-tour-highlight="1"]').forEach((el) => {
                el.style.position = '';
                el.style.zIndex = '';
                el.style.boxShadow = '';
                el.style.borderRadius = '';
                el.removeAttribute('data-tour-highlight');
            });
        };

        const finish = () => {
            localStorage.setItem(storageKey, '1');
            hide();
            fetch('{{ route('tour.complete') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            }).catch(() => {});
        };

        const position = (anchor) => {
            const rect = anchor.getBoundingClientRect();
            currentRect = rect;
            overlay.style.display = 'block';
            tooltip.style.display = 'block';
            overlay.style.background = 'rgba(0,0,0,0.38)';

            anchor.setAttribute('data-tour-highlight', '1');
            anchor.style.position = 'relative';
            anchor.style.zIndex = '10000';
            anchor.style.boxShadow = '0 0 0 4px rgba(255, 209, 0, 0.32)';
            anchor.style.borderRadius = '12px';

            if (window.matchMedia('(max-width: 640px)').matches) {
                tooltip.style.left = '12px';
                tooltip.style.right = '12px';
                tooltip.style.width = 'calc(100vw - 24px)';
                tooltip.style.maxWidth = 'calc(100vw - 24px)';
                tooltip.style.top = 'auto';
                tooltip.style.bottom = '12px';
                tooltip.style.transformOrigin = 'center bottom';
                return;
            }

            const tooltipWidth = Math.min(320, window.innerWidth - 24);
            const tooltipHeight = tooltip.getBoundingClientRect().height || 180;
            const gap = 12;

            tooltip.style.width = `${tooltipWidth}px`;

            let top = rect.bottom + gap;
            let placeAbove = false;

            if (top + tooltipHeight > window.innerHeight - 12) {
                const above = rect.top - tooltipHeight - gap;
                if (above >= 12) {
                    top = above;
                    placeAbove = true;
                } else {
                    top = Math.max(12, window.innerHeight - tooltipHeight - 12);
                }
            }

            let left = rect.left;
            if (left + tooltipWidth > window.innerWidth - 12) {
                left = window.innerWidth - tooltipWidth - 12;
            }
            left = Math.max(12, left);

            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;
            tooltip.style.transformOrigin = placeAbove ? 'bottom left' : 'top left';
        };

        const render = () => {
            const step = route.steps[index];
            if (!step) {
                finish();
                return;
            }

            const anchor = document.querySelector(step.anchor);
            if (!anchor) {
                index += 1;
                render();
                return;
            }

            stepEl.textContent = `Étape ${index + 1} / ${route.steps.length}`;
            titleEl.textContent = step.title;
            bodyEl.textContent = step.body;
            prevBtn.disabled = index === 0;
            prevBtn.style.opacity = index === 0 ? '.45' : '1';
            nextBtn.textContent = index === route.steps.length - 1 ? 'Terminer' : 'Suivant';
            position(anchor);
        };

        prevBtn.addEventListener('click', () => {
            if (index > 0) {
                index -= 1;
                render();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (index >= route.steps.length - 1) {
                finish();
            } else {
                index += 1;
                render();
            }
        });

        skipBtn.addEventListener('click', finish);
    window.addEventListener('resize', () => { if (tooltip.style.display === 'block') render(); });
    window.addEventListener('scroll', () => { if (tooltip.style.display === 'block') render(); }, true);

        const mq = window.matchMedia('(max-width: 640px)');
        mq.addEventListener?.('change', () => {
            if (tooltip.style.display === 'block') render();
        });

        render();
    })();
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
