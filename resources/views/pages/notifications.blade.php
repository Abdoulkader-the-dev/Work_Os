<x-app-layout>
@section('page-title', 'Notifications')

@php
    $notifications = auth()->user()?->notifications()->latest()->get() ?? collect();
    $unreadCount = $notifications->whereNull('read_at')->count();
@endphp

<div style="display:flex;flex-direction:column;gap:16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="font-size:12px;font-family:'DM Mono',monospace;color:var(--text-3);">
            {{ $notifications->count() }} notification{{ $notifications->count() > 1 ? 's' : '' }} · {{ $unreadCount }} non lue{{ $unreadCount > 1 ? 's' : '' }}
        </div>

        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn-primary">Tout marquer lu</button>
            </form>
        @endif
    </div>

    <div class="bento-card" style="padding:0;overflow:hidden;">
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

            <div style="display:flex;align-items:flex-start;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border);background:{{ $isUnread ? 'rgba(0,145,205,0.03)' : 'white' }};">
                <div style="width:8px;height:8px;border-radius:50%;background:{{ $isUnread ? 'var(--blue)' : 'transparent' }};flex-shrink:0;margin-top:5px;"></div>
                <div style="width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;font-weight:700;background:{{ $icon['color'] }}22;color:{{ $icon['color'] }};">
                    {{ $icon['label'] }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;line-height:1.5;">{!! $notification->message !!}</div>
                    <div style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-3);margin-top:4px;">
                        {{ $notification->created_at->isoFormat('ddd D MMM · HH:mm') }}
                    </div>
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" style="display:inline-block;margin-top:6px;font-size:12px;color:var(--blue);text-decoration:none;font-weight:500;">
                            {{ $notification->action_label ?? 'Voir' }} →
                        </a>
                    @endif
                </div>
                @if($isUnread)
                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                        @csrf
                        <button type="submit" style="padding:7px 10px;border:1px solid var(--border);border-radius:8px;background:white;color:var(--text-2);font-size:12px;font-family:'DM Sans',sans-serif;cursor:pointer;">
                            Marquer lu
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div style="text-align:center;padding:72px 24px;">
                <div style="font-size:40px;margin-bottom:12px;">🔔</div>
                <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:6px;">Aucune notification</div>
                <div style="font-size:13px;color:var(--text-3);">Tout est à jour pour le moment.</div>
            </div>
        @endforelse
    </div>
</div>
</x-app-layout>
