<div role="status" aria-live="polite" style="display:flex;flex-direction:column;gap:16px;">
    <div class="flex-between flex-wrap" style="gap:12px;">
        <div class="text-xs text-3 f-mono">
            {{ $notifications->count() }} notification{{ $notifications->count() > 1 ? 's' : '' }} · {{ $unreadCount }} non lue{{ $unreadCount > 1 ? 's' : '' }}
        </div>

        @if($unreadCount > 0)
        <button wire:click="markAllAsRead" class="btn-primary" aria-label="Marquer toutes les notifications comme lues">Tout marquer lu</button>
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
                 style="display:flex;align-items:flex-start;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border);background:{{ $isUnread ? 'rgba(0,145,205,0.03)' : 'white' }}; transition: background .3s;">
                <div style="width:8px;height:8px;border-radius:50%;background:{{ $isUnread ? 'var(--blue)' : 'transparent' }};flex-shrink:0;margin-top:5px;"></div>
                <div class="flex-center text-sm font-bold" style="width:34px;height:34px;border-radius:50%;flex-shrink:0;background:{{ $icon['color'] }}22;color:{{ $icon['color'] }};">
                    {{ $icon['label'] }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="text-sm" style="line-height:1.5;">{!! $notification->message !!}</div>
                    <div class="text-xs text-3 f-mono" style="margin-top:4px;">
                        {{ $notification->created_at->isoFormat('ddd D MMM · HH:mm') }}
                    </div>
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" class="text-sm font-medium" style="display:inline-block;margin-top:6px;color:var(--blue);text-decoration:none;">
                            {{ $notification->action_label ?? 'Voir' }} →
                        </a>
                    @endif
                </div>
                @if($isUnread)
                    <button wire:click="markAsRead({{ $notification->id }})" class="icon-btn" style="width:auto;padding:7px 10px;height:auto;font-size:12px;" aria-label="Marquer cette notification comme lue">
                        Marquer lu
                    </button>
                @endif
            </div>
        @empty
            <div style="text-align:center;padding:72px 24px;">
                <div style="font-size:40px;margin-bottom:12px;">🔔</div>
                <div class="text-md font-semibold text-1" style="margin-bottom:6px;">Aucune notification</div>
                <div class="text-sm text-3">Tout est à jour pour le moment.</div>
            </div>
        @endforelse
    </div>
</div>
