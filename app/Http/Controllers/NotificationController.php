<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAllAsRead(Request $request): RedirectResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }

    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($request->user()->can('update', $notification), 403);

        $notification->update(['read_at' => now()]);

        return back();
    }
}
