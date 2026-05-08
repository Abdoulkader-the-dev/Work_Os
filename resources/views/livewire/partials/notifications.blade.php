{{-- resources/views/livewire/partials/notifications.blade.php --}}

<div style="position:relative;" x-data @click.outside="$wire.close()">

    {{-- ── TRIGGER — Cloche ── --}}
    <button wire:click="toggle"
            style="width:36px;height:36px;border-radius:8px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;color:var(--text-2);transition:all .15s;"
            onmouseover="this.style.background='#e8e8e6';this.style.borderColor='var(--border-md)'"
            onmouseout="this.style.background='var(--bg)';this.style.borderColor='var(--border)'"
            :aria-expanded="$wire.isOpen"
            aria-label="Notifications">

        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M8 1.5a5 5 0 00-5 5v3l-1.5 2h13L13 9.5v-3a5 5 0 00-5-5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
            <path d="M6.5 13.5a1.5 1.5 0 003 0" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        </svg>

        {{-- Dot badge non lus --}}
        @if($unreadCount > 0)
            <div style="position:absolute;top:5px;right:5px;min-width:7px;height:7px;background:var(--yellow);border-radius:50%;border:1.5px solid white;display:flex;align-items:center;justify-content:center;">
                @if($unreadCount > 9)
                    <span style="font-size:8px;font-weight:700;font-family:'DM Mono',monospace;color:var(--text-1);padding:0 2px;"></span>
                @endif
            </div>
        @endif
    </button>

    {{-- ── DROPDOWN ── --}}
    <div x-show="$wire.isOpen"
         x-transition:enter="transition ease-out duration-180"
         x-transition:enter-start="opacity-0 translate-y-[-6px]"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-140"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-[-6px]"
         style="position:absolute;right:0;top:calc(100% + 10px);width:380px;background:white;border:1px solid var(--border);border-radius:14px;box-shadow:0 16px 48px rgba(0,0,0,0.12);z-index:60;overflow:hidden;">

        {{-- Header dropdown --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border);">
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
                        style="font-size:11px;font-weight:500;color:var(--blue);background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;transition:opacity .15s;"
                        onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'">
                    Tout marquer lu
                </button>
            @endif
        </div>

        {{-- Liste --}}
        <div style="max-height:420px;overflow-y:auto;">
            @forelse($this->notifications as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                    $type     = $notif->type ?? 'default';
                    $iconMap  = [
                        'mention'    => ['color' => '#0091CD', 'label' => '@'],
                        'status'     => ['color' => '#f97316', 'label' => '○'],
                        'assignment' => ['color' => '#FFD100', 'label' => '+'],
                        'deadline'   => ['color' => '#ef4444', 'label' => '!'],
                        'comment'    => ['color' => '#6b6b68', 'label' => '…'],
                        'default'    => ['color' => '#a3a39f', 'label' => '·'],
                    ];
                    $icon = $iconMap[$type] ?? $iconMap['default'];
                @endphp

                <div wire:click="markAsRead({{ $notif->id }})"
                     style="display:flex;align-items:flex-start;gap:12px;padding:12px 18px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .15s;background:{{ $isUnread ? 'rgba(0,145,205,0.03)' : 'white' }};"
                     onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='{{ $isUnread ? 'rgba(0,145,205,0.03)' : 'white' }}'">

                    {{-- Dot non-lu --}}
                    <div style="width:7px;height:7px;border-radius:50%;background:{{ $isUnread ? 'var(--blue)' : 'transparent' }};flex-shrink:0;margin-top:5px;"></div>

                    {{-- Icône type --}}
                    <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;font-weight:700;background:{{ $icon['color'] }}18;color:{{ $icon['color'] }};">
                        {{ $icon['label'] }}
                    </div>

                    {{-- Contenu --}}
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;color:var(--text-1);line-height:1.4;">
                            {!! $notif->message ?? '' !!}
                        </div>
                        <div style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);margin-top:3px;">
                            {{ $notif->created_at->diffForHumans() }}
                        </div>
                        @if($notif->action_url ?? false)
                            <a href="{{ $notif->action_url }}"
                               style="font-size:11px;color:var(--blue);text-decoration:none;margin-top:3px;display:inline-block;font-weight:500;"
                               onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                → {{ $notif->action_label ?? 'Voir' }}
                            </a>
                        @endif
                    </div>

                </div>
            @empty
                <div style="text-align:center;padding:40px 24px;">
                    <div style="font-size:28px;margin-bottom:10px;">🔔</div>
                    <div style="font-size:13px;font-weight:500;margin-bottom:4px;">Tout est à jour</div>
                    <div style="font-size:12px;color:var(--text-3);">Vous n'avez aucune notification</div>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($this->notifications->count() > 0)
            <div style="padding:10px 18px;border-top:1px solid var(--border);text-align:center;">
                <a href="{{ route('notifications.index') }}"
                   style="font-size:12px;font-weight:500;color:var(--blue);text-decoration:none;transition:opacity .15s;"
                   onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'">
                    Voir toutes les notifications →
                </a>
            </div>
        @endif

    </div>
</div>