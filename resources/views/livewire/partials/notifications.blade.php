<div x-data="{ open: false }" class="dropdown-shell">

    <button type="button"
            @click="open = !open"
            @keydown.escape.window="open = false"
            :aria-expanded="open ? 'true' : 'false'"
            aria-controls="notifications-menu"
            class="icon-btn"
            aria-label="Notifications">

        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M8 1.5a5 5 0 00-5 5v3l-1.5 2h13L13 9.5v-3a5 5 0 00-5-5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
            <path d="M6.5 13.5a1.5 1.5 0 003 0" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        </svg>

        @if($unreadCount > 0)
            <div style="position:absolute;top:5px;right:5px;width:7px;height:7px;background:var(--yellow);border-radius:50%;border:1.5px solid white;"></div>
        @endif
    </button>

    <div id="notifications-menu"
         x-cloak
         x-show="open"
         x-transition.opacity.scale.98.duration.180ms.origin.top.right
         @click.outside="open = false"
         class="surface-menu notification-popover dropdown-panel dropdown-panel--right dropdown-panel--mobile-full"
         style="top:calc(100% + 10px);z-index:60;transform-origin:top right;">

        <div class="surface-menu__header" style="display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:14px;font-weight:600;letter-spacing:-0.01em;">Notifications</span>
                @if($unreadCount > 0)
                    <span style="font-size:10px;font-weight:700;font-family:'DM Mono',monospace;padding:2px 7px;border-radius:20px;background:var(--yellow);color:var(--text-1);">
                        {{ $unreadCount }}
                    </span>
                @endif
            </div>
                @if($unreadCount > 0)
                <button wire:click="markAllAsRead"
                        class="action-link"
                        style="font-size:11px;font-weight:500;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;">
                    Tout marquer
                </button>
            @endif
        </div>

        <div style="max-height:400px;overflow-y:auto;">
        @forelse($this->notifications as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                    $iconMap  = [
                        'mention'    => ['color' => '#0091CD', 'label' => '@'],
                        'status'     => ['color' => '#f97316', 'label' => '○'],
                        'assignment' => ['color' => '#FFD100', 'label' => '+'],
                        'deadline'   => ['color' => '#ef4444', 'label' => '!'],
                        'comment'    => ['color' => '#6b6b68', 'label' => '…'],
                        'default'    => ['color' => '#a3a39f', 'label' => '·'],
                    ];
                    $icon = $iconMap[$notif->type ?? 'default'] ?? $iconMap['default'];
                @endphp

                <div wire:click="markAsRead({{ $notif->id }})"
                     class="notification-row {{ $isUnread ? 'notification-row--unread' : '' }}"
                     style="cursor:pointer;">
                    <div class="notification-row__dot" style="background:{{ $isUnread ? 'var(--blue)' : 'transparent' }};"></div>
                    <div class="notification-row__icon" style="background:{{ $icon['color'] }}22;color:{{ $icon['color'] }};">
                        {{ $icon['label'] }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;color:var(--text-1);line-height:1.4;">{!! $notif->message ?? '' !!}</div>
                        <div class="notification-row__meta">
                            {{ $notif->created_at->diffForHumans() }}
                        </div>
                        @if($notif->action_url ?? false)
                            <a href="{{ $notif->action_url }}"
                               class="action-link notification-row__action"
                               style="font-size:11px;font-weight:500;">
                                → {{ $notif->action_label ?? 'Voir' }}
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <x-empty-state compact title="Tout est à jour" description="Vous n'avez aucune notification">
                    <x-slot:icon>
                        <div style="font-size:28px;">🔔</div>
                    </x-slot:icon>
                </x-empty-state>
            @endforelse
        </div>

        @if($this->notifications->count() > 0)
            <div class="surface-menu__footer">
                <a href="{{ route('notifications.index') }}"
                   class="action-link" style="font-size:12px;font-weight:500;">
                    Voir toutes les notifications →
                </a>
            </div>
        @endif
    </div>
</div>
