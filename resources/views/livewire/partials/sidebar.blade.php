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

    <div x-data="{ open: false }" class="sidebar-footer">
        <div class="nav-section-label" style="padding:0 2px;margin-bottom:6px;">Workspace</div>
        <div class="workspace-selector" @click="open = !open" @click.outside="open = false">
            <div style="width:8px;height:8px;border-radius:50%;background:var(--blue);flex-shrink:0;"></div>
            <span class="text-sm font-medium" style="flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
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
                <div class="flex-center" style="gap:8px;padding:8px 10px;border-radius:6px;font-size:13px;cursor:pointer;transition:background .15s;"
                     onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $ws->color ?? 'var(--blue)' }};"></div>
                    {{ $ws->name }}
                </div>
            @endforeach
        </div>
    </div>

</aside>
