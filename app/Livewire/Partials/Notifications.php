<?php
// app/Livewire/Partials/Notifications.php

namespace App\Livewire\Partials;

use App\Models\Notification;
use Livewire\Component;
use Livewire\Attributes\Locked;

class Notifications extends Component
{
    #[Locked]
    public bool $isOpen      = false;
    public int  $unreadCount = 0;

    // Écoute Pusher via Laravel Echo
    protected function getListeners(): array
    {
        $userId = auth()->id();
        return [
            "echo-private:users.{$userId},NotificationSent" => 'refreshNotifications',
        ];
    }

    public function mount(): void
    {
        $this->unreadCount = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function markAsRead(int $id): void
    {
        Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->update(['read_at' => now()]);

        $this->refreshNotifications();
    }

    public function markAllAsRead(): void
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->refreshNotifications();
    }

    public function refreshNotifications(): void
    {
        $this->unreadCount = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    public function getNotificationsProperty()
    {
        return Notification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.partials.notifications');
    }
}