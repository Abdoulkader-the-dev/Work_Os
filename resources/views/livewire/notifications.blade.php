<div role="status" aria-live="polite" class="page-stack">
    <div class="page-toolbar">
        <div class="text-xs text-3 f-mono">
            {{ $notifications->count() }} notification{{ $notifications->count() > 1 ? 's' : '' }} · {{ $unreadCount }} non lue{{ $unreadCount > 1 ? 's' : '' }}
        </div>

        @if($unreadCount > 0)
        <button wire:click="markAllAsRead" class="btn-primary" aria-label="Marquer toutes les notifications comme lues">Tout marquer</button>
        @endif
    </div>

    <div class="bento-card" style="padding:0;overflow:hidden;" data-tour-id="notifications-list">
        @forelse($notifications as $notification)
            @php
                $isUnread = is_null($notification->read_at);
                $iconMap = [
                    'mention' => ['color' => '#0091CD', 'label' => '@'],
                    'status' => ['color' => '#f97316', 'label' => '○'],
                    'assignment' => ['color' => '#FFD100', 'label' => '+'],
                    'deadline' => ['color' => '#ef4444', 'label' => '!'],
                    'comment' => ['color' => '#6b6b68', 'label' => '…'],
                    'default' => ['color' => '#a3a39f', 'label' => '·'],
                ];
                $icon = $iconMap[$notification->type ?? 'default'] ?? $iconMap['default'];
            @endphp

            <div wire:key="notif-{{ $notification->id }}"
                 tabindex="0"
                 class="notification-row {{ $isUnread ? 'notification-row--unread' : '' }}">
                <div class="notification-row__dot" style="background:{{ $isUnread ? 'var(--blue)' : 'transparent' }};"></div>
                <div class="notification-row__icon" style="background:{{ $icon['color'] }}22;color:{{ $icon['color'] }};">
                    {{ $icon['label'] }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="text-sm" style="line-height:1.5;">{!! $notification->message !!}</div>
                    <div class="notification-row__meta">
                        {{ $notification->created_at->isoFormat('ddd D MMM · HH:mm') }}
                    </div>
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" class="action-link notification-row__action" style="font-size:12px;font-weight:500;">
                            {{ $notification->action_label ?? 'Voir' }} →
                        </a>
                    @endif
                </div>
                @if($isUnread)
                    <button wire:click="markAsRead({{ $notification->id }})" class="surface-menu__item" style="width:auto;padding:7px 10px;height:auto;font-size:12px;" aria-label="Marquer cette notification comme lue">
                        Marquer lu
                    </button>
                @endif
            </div>
        @empty
            <x-empty-state compact title="Aucune notification" description="Tout est à jour pour le moment." />
        @endforelse
    </div>
</div>
