<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;

class Notifications extends Component
{
    public function refresh(): void {}

    protected function getListeners(): array
    {
        $userId = auth()->id();

        if (!$userId) {
            return [];
        }

        return [
            "echo:users.{$userId},NotificationSent" => 'refresh',
        ];
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        $this->dispatch('notifications-updated');
    }

    public function markAsRead($id)
    {
        Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->update(['read_at' => now()]);

        $this->dispatch('notifications-updated');
    }

    public function render()
    {
        $notifications = auth()->user()?->notifications()->latest()->get() ?? collect();
        $unreadCount = $notifications->whereNull('read_at')->count();

        return view('livewire.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
