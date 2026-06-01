<aside id="sidebar" :class="{ 'open': mobileSidebarOpen }">

    <div class="sidebar-header">
        <div class="sidebar-logo">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <rect x="1" y="1" width="6" height="6" rx="1.5" fill="white"/>
                <rect x="9" y="1" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>
                <rect x="1" y="9" width="6" height="6" rx="1.5" fill="white" opacity=".6"/>
                <rect x="9" y="9" width="6" height="6" rx="1.5" fill="white" opacity=".3"/>
            </svg>
        </div>
        <span class="text-lg font-semibold" style="letter-spacing:-0.02em;">
            Uni<span style="color:var(--yellow);">Pod</span>
        </span>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-section-label">Principal</div>

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" wire:navigate>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                <rect x="1" y="1" width="6.5" height="6.5" rx="1.5" fill="currentColor"/>
                <rect x="8.5" y="1" width="6.5" height="6.5" rx="1.5" fill="currentColor"/>
                <rect x="1" y="8.5" width="6.5" height="6.5" rx="1.5" fill="currentColor"/>
                <rect x="8.5" y="8.5" width="6.5" height="6.5" rx="1.5" fill="currentColor"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('boards.index') }}" class="nav-item {{ request()->routeIs('boards.*') ? 'active' : '' }}" wire:navigate>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                <rect x="1" y="1" width="14" height="3" rx="1" fill="currentColor"/>
                <rect x="1" y="6.5" width="14" height="3" rx="1" fill="currentColor"/>
                <rect x="1" y="12" width="9" height="3" rx="1" fill="currentColor"/>
            </svg>
            Boards
        </a>

        <a href="{{ route('meetings.index') }}" class="nav-item {{ request()->routeIs('meetings.*') ? 'active' : '' }}" wire:navigate>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                <rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M5 1v2M11 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M1 6h14" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Réunions
        </a>

        <div class="nav-section-label">Personnel</div>

        <a href="{{ route('my-tasks') }}" class="nav-item {{ request()->routeIs('my-tasks') ? 'active' : '' }}" wire:navigate>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                <path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Mes tâches
            @if($myTasksCount > 0)
                <span class="nav-badge">{{ $myTasksCount }}</span>
            @endif
        </a>

        <a href="{{ route('calendar') }}" class="nav-item {{ request()->routeIs('calendar') ? 'active' : '' }}" wire:navigate>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                <rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M1 6h14M5 1v2M11 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <rect x="4" y="8.5" width="2" height="2" rx=".5" fill="currentColor"/>
                <rect x="7" y="8.5" width="2" height="2" rx=".5" fill="currentColor"/>
                <rect x="10" y="8.5" width="2" height="2" rx=".5" fill="currentColor"/>
            </svg>
            Calendrier
        </a>

        <a href="{{ route('reports') }}" class="nav-item {{ request()->routeIs('reports') ? 'active' : '' }}" wire:navigate>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                <path d="M2 12L5.5 8l3 3L12 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M1 15h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            Rapports
        </a>

        <div class="nav-section-label">Système</div>

        <a href="{{ route('members') }}" class="nav-item {{ request()->routeIs('members') ? 'active' : '' }}" wire:navigate>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                <circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/>
                <path d="M1 14c0-2.76 2.24-5 5-5s5 2.24 5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M11 7c1.66 0 3 1.34 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            Membres
        </a>

        <a href="{{ route('settings') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}" wire:navigate>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="opacity:.55;flex-shrink:0;">
                <circle cx="8" cy="8" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 1v1.5M8 13.5V15M1 8h1.5M13.5 8H15M2.93 2.93l1.06 1.06M12.01 12.01l1.06 1.06M2.93 13.07l1.06-1.06M12.01 3.99l1.06-1.06" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            Paramètres
        </a>

    </nav>

    <div class="sidebar-footer">
        <div class="nav-section-label" style="padding:0 2px;margin-bottom:6px;">Workspace</div>

        {{-- Sélecteur actif --}}
        <div class="workspace-selector"
             data-dropdown-trigger="workspace-menu"
             onclick="wsResetCreating()"
             style="cursor:pointer;user-select:none;">
            <div style="width:8px;height:8px;border-radius:50%;background:{{ auth()->user()?->activeWorkspace?->color ?? 'var(--blue)' }};flex-shrink:0;"></div>
            <span class="text-sm font-medium" style="flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ auth()->user()?->activeWorkspace?->name ?? auth()->user()?->workspaces()->first()?->name ?? 'Aucun workspace' }}
            </span>
            <svg id="ws-chevron" width="14" height="14" viewBox="0 0 14 14" fill="none" style="color:var(--text-3);flex-shrink:0;transition:transform .2s;">
                <path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        {{-- Dropdown workspace --}}
        <div id="workspace-menu"
             data-dropdown-menu
             hidden
             style="position:absolute;bottom:72px;left:12px;right:12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-card);box-shadow:0 -8px 32px rgba(0,0,0,0.12);padding:6px;z-index:50;max-height:380px;overflow-y:auto;scrollbar-width:thin;">

            {{-- Liste des workspaces existants --}}
            @php $currentWsId = auth()->user()?->activeWorkspace?->id ?? auth()->user()?->workspaces()->first()?->id; @endphp
            @foreach(auth()->user()?->workspaces ?? [] as $ws)
                <form method="POST"
                      action="{{ route('workspaces.switch', $ws) }}"
                      onsubmit="wsSubmitting(this)"
                      style="margin:0;">
                    @csrf
                    <button type="submit"
                            class="flex-center"
                            style="width:100%;gap:8px;padding:8px 10px;border-radius:8px;font-size:13px;cursor:pointer;transition:background .12s;border:none;background:{{ $ws->id === $currentWsId ? 'var(--bg)' : 'transparent' }};font-weight:{{ $ws->id === $currentWsId ? '600' : '400' }};font-family:'DM Sans',sans-serif;color:var(--text-1);text-align:left;"
                            onmouseover="this.style.background='var(--bg)'"
                            onmouseout="this.style.background='{{ $ws->id === $currentWsId ? 'var(--bg)' : 'transparent' }}'">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $ws->color ?? 'var(--blue)' }};flex-shrink:0;"></div>
                        <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $ws->name }}</span>
                        <svg class="ws-submit-spinner animate-spin" width="12" height="12" viewBox="0 0 12 12" fill="none" hidden style="color:var(--text-3);">
                            <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.4" stroke-dasharray="16" stroke-linecap="round" opacity=".35"/>
                            <path d="M6 1a5 5 0 015 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        </svg>
                        @if($ws->id === $currentWsId)
                            <svg class="ws-current-check" width="12" height="12" viewBox="0 0 12 12" fill="none" style="flex-shrink:0;color:var(--blue);">
                                <path d="M2 6l2.5 2.5L10 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @endif
                    </button>
                </form>
            @endforeach

            {{-- Séparateur --}}
            <div style="height:1px;background:var(--border);margin:4px 2px;"></div>

            {{-- Bouton Créer un workspace --}}
            <div id="ws-btn-create">
                <button type="button"
                        onclick="wsShowCreating(event)"
                        class="flex-center"
                        style="width:100%;gap:8px;padding:8px 10px;border-radius:8px;font-size:13px;cursor:pointer;background:none;border:none;font-family:'DM Sans',sans-serif;color:var(--text-2);transition:background .12s;"
                        onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M7 2v10M2 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Créer un workspace
                </button>
            </div>

            {{-- Formulaire de création --}}
            <div id="ws-creating-form" hidden style="padding:2px 2px 4px;">
                <form method="POST"
                      action="{{ route('workspaces.store') }}"
                      onsubmit="return wsSubmitCreate(event)"
                      style="margin:0;">
                    @csrf
                    <input type="text"
                           id="ws-name-input"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="Nom du workspace..."
                           autocomplete="off"
                           onkeydown="wsInputKeydown(event)"
                           style="width:100%;padding:8px 10px;font-size:13px;font-family:'DM Sans',sans-serif;border:1px solid var(--border);border-radius:8px;background:var(--bg);outline:none;color:var(--text-1);margin-bottom:4px;transition:border-color .15s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='var(--blue)'"
                           onblur="this.style.borderColor='var(--border)'">

                    @error('name')
                        <div style="color:#dc2626;font-size:11px;margin-bottom:6px;padding-left:4px;">{{ $message }}</div>
                    @enderror

                    <div id="ws-name-error" hidden style="color:#dc2626;font-size:11px;margin-bottom:6px;padding-left:4px;">
                        Le nom est requis.
                    </div>

                    <div class="flex-center" style="gap:6px;">
                        <button type="submit"
                                id="ws-create-btn"
                                style="flex:1;padding:7px 10px;background:var(--text-1);color:white;border:none;border-radius:7px;font-size:12px;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:6px;"
                                onmouseover="this.style.background='#2a2a28'"
                                onmouseout="this.style.background='var(--text-1)'">
                            <span class="ws-submit-label">Créer</span>
                            <svg class="ws-submit-spinner animate-spin" width="12" height="12" viewBox="0 0 12 12" fill="none" hidden>
                                <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.5" stroke-dasharray="16" stroke-linecap="round" opacity="0.3"/>
                                <path d="M6 1a5 5 0 015 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <button type="button"
                                onclick="wsCancelCreating(event)"
                                style="padding:7px 10px;background:none;border:1px solid var(--border);border-radius:7px;font-size:12px;font-family:'DM Sans',sans-serif;cursor:pointer;color:var(--text-2);transition:background .15s;"
                                onmouseover="this.style.background='var(--bg)'"
                                onmouseout="this.style.background=''">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
    function wsShowCreating(event) {
        event.stopPropagation();
        var btnCreate = document.getElementById('ws-btn-create');
        var form = document.getElementById('ws-creating-form');
        if (btnCreate) btnCreate.hidden = true;
        if (form) form.hidden = false;
        setTimeout(function() {
            var input = document.getElementById('ws-name-input');
            if (input) { input.focus(); }
        }, 30);
    }

    function wsCancelCreating(event) {
        event.stopPropagation();
        var btnCreate = document.getElementById('ws-btn-create');
        var form = document.getElementById('ws-creating-form');
        if (btnCreate) btnCreate.hidden = false;
        if (form) form.hidden = true;
        var input = document.getElementById('ws-name-input');
        if (input) input.value = '';
        var error = document.getElementById('ws-name-error');
        if (error) error.hidden = true;
    }

    function wsResetCreating() {
        var btnCreate = document.getElementById('ws-btn-create');
        var form = document.getElementById('ws-creating-form');
        if (btnCreate) btnCreate.hidden = false;
        if (form) form.hidden = true;
        var input = document.getElementById('ws-name-input');
        if (input) input.value = '';
        var error = document.getElementById('ws-name-error');
        if (error) error.hidden = true;
    }

    function wsInputKeydown(event) {
        event.stopPropagation();
        if (event.key === 'Enter') {
            event.preventDefault();
            var input = document.getElementById('ws-name-input');
            if (input && input.form) input.form.requestSubmit();
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            wsCancelCreating(event);
            var menu = document.getElementById('workspace-menu');
            if (menu) menu.hidden = true;
        }
    }

    function wsSubmitCreate(event) {
        event.stopPropagation();
        var input = document.getElementById('ws-name-input');
        var error = document.getElementById('ws-name-error');
        if (!input || input.value.trim() === '') {
            event.preventDefault();
            if (error) error.hidden = false;
            if (input) input.focus();
            return false;
        }

        if (error) error.hidden = true;
        wsSubmitting(event.target);
        return true;
    }

    function wsSubmitting(form) {
        var button = form.querySelector('button[type="submit"]');
        if (!button) return;

        button.disabled = true;
        button.style.cursor = 'wait';

        var label = button.querySelector('.ws-submit-label');
        if (label) label.textContent = 'Création...';

        var check = button.querySelector('.ws-current-check');
        if (check) check.hidden = true;

        var spinner = button.querySelector('.ws-submit-spinner');
        if (spinner) spinner.hidden = false;
    }

    // Rotate chevron when dropdown opens/closes
    (function() {
        var observer = new MutationObserver(function() {
            var menu = document.getElementById('workspace-menu');
            var chevron = document.getElementById('ws-chevron');
            if (menu && chevron) {
                chevron.style.transform = menu.hidden ? '' : 'rotate(180deg)';
            }
        });
        var menu = document.getElementById('workspace-menu');
        if (menu) {
            observer.observe(menu, { attributes: true, attributeFilter: ['hidden'] });
        }
    })();
    </script>

</aside>
