---
name: reverb-broadcast-setup
description: Debug and fix Laravel Reverb broadcasting errors — Pusher SDK crashes, invalid socket ID, echo-private vs echo mismatch, and env config issues
source: auto-skill
extracted_at: '2026-06-09T21:30:00.000Z'
---

## Pattern: Reverb broadcasting errors in Laravel 13

### Error 1: Pusher::__construct() — secret must be of type string, null

```
Failed to create broadcaster for connection "reverb" with error:
Pusher\Pusher::__construct(): Argument #2 ($secret) must be of type string, null given
```

**Root cause**: Reverb uses the Pusher PHP SDK under the hood (Reverb speaks the Pusher protocol).
If `REVERB_APP_SECRET` or `REVERB_APP_ID` is missing/null in `.env`, the Pusher client crashes.

**Fix**: In `.env`, uncomment and set all three Reverb credentials:

```env
REVERB_APP_ID=123456
REVERB_APP_KEY=your-key-here
REVERB_APP_SECRET=your-secret-here
```

Generate fresh credentials:
```bash
php -r "echo 'REVERB_APP_KEY=' . base64_encode(random_bytes(32)) . PHP_EOL;"
php -r "echo 'REVERB_APP_SECRET=' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

**IMPORTANT**: Do NOT reuse the Laravel `APP_KEY` as the `REVERB_APP_KEY`. They are separate. The Reverb key should be a fresh random value. Also do NOT prefix with `base64:` — that's the Laravel app key format, not Reverb's.

Also set the matching Vite env vars for the frontend Echo client. **Only `VITE_REVERB_APP_KEY` is needed** — Echo JS does not use the secret or app_id:

```env
VITE_REVERB_APP_KEY=your-key-here
```

**Do NOT set** `VITE_REVERB_APP_SECRET` — it has no Echo JS counterpart and may confuse credential rotation.

Also ensure `BROADCAST_CONNECTION=reverb` is set in `.env` (not `pusher`).

Then clear config cache: `php artisan config:clear`

### Error 2b: echo-private: vs echo: listener mismatch (MOST COMMON CAUSE)

**Symptom**: `Pusher\PusherException: Invalid socket ID undefined` even though Reverb is running and env vars are correct.

**Root cause**: The broadcast **event** uses public `Channel` in `broadcastOn()`, but the Livewire **listener** uses `echo-private:` instead of `echo:`. This mismatch forces the Pusher auth handshake (which requires a valid socket ID) on a channel that doesn't need auth. The socket ID is `undefined` because Echo never completed the WebSocket handshake for that private subscription.

**How to diagnose**: Search all Livewire components for `echo-private:` and check if the corresponding event's `broadcastOn()` returns `Channel` (public) or `PrivateChannel`:

```bash
# Find all echo-private listeners
grep -rn "echo-private:" app/Livewire/

# Find all broadcastOn() — check if they use Channel or PrivateChannel
grep -rn "broadcastOn" app/Events/
```

**Fix**: For every event that uses `new Channel(...)` (public), change the listener from `echo-private:` to `echo:`:

```php
// Event (public Channel)
public function broadcastOn(): array
{
    return [new Channel("boards.{$this->board->id}")];
}

// Listener — WRONG
"echo-private:boards.{$this->board->id},BoardUpdated" => '$refresh',

// Listener — CORRECT
"echo:boards.{$this->board->id},BoardUpdated" => '$refresh',
```

**Scope**: In this project, ALL events use public `Channel` — every `echo-private:` must become `echo:`. Check these files:
- `app/Livewire/Boards/BoardTable.php`
- `app/Livewire/Boards/BoardKanban.php`
- `app/Livewire/Boards/BoardCalendar.php`
- `app/Livewire/Boards/BoardIndex.php`
- `app/Livewire/Dashboard.php`
- `app/Livewire/MyTasks.php`
- `app/Livewire/Notifications.php`
- `app/Livewire/Partials/Notifications.php`
- `app/Livewire/Partials/Sidebar.php`
- `app/Livewire/Meetings/MeetingList.php`

Use `echo-private:` **only** when the event uses `PrivateChannel` in `broadcastOn()` AND you have corresponding auth routes in `routes/channels.php`.

### Error 2: Invalid socket ID undefined

```
Pusher\PusherException: Invalid socket ID undefined
```

Header: `x-socket-id: undefined`

**Where the crash happens**: The stack trace shows the error originates from the `broadcast()` call in the Livewire component (e.g., `BoardTable.php:221`), triggered by `->toOthers()`. This is a **server-side** crash — the Pusher PHP SDK rejects the `"undefined"` string. The client-side listener subscription is a separate (also broken) issue.

**Two distinct failure points**:

| Failure | Where | Why |
|---|---|---|
| **Server-side** (the crash) | `->toOthers()` → `dontBroadcastToCurrentUser()` → reads `X-Socket-ID: undefined` header → passes to Pusher SDK → throws | Pusher SDK's `validate_socket_id("undefined")` rejects non-numeric strings |
| **Client-side** (no real-time updates) | `echo-private:` listener fails auth handshake or Echo WebSocket never connects | Stale JS bundle, env vars not injected, or listener/channel type mismatch |

**The server-side fix — Create `app/Events/BroadcastEvent.php`:**

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Broadcast;

abstract class BroadcastEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Override to exclude current user only when socket ID is valid.
     * Prevents "Invalid socket ID undefined" when Echo WebSocket hasn't connected.
     */
    public function dontBroadcastToCurrentUser()
    {
        $socketId = Broadcast::socket();

        if ($socketId !== null && $socketId !== 'undefined' && $socketId !== '') {
            $this->socket = $socketId;
        }

        return $this;
    }
}
```

Then update ALL event classes to extend `BroadcastEvent` instead of implementing `ShouldBroadcastNow` directly:

```php
// Before
class BoardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

// After
class BoardUpdated extends BroadcastEvent
{
    // traits inherited from BroadcastEvent
}
```

Remove the now-removed `use` imports (`InteractsWithSockets`, `ShouldBroadcastNow`, `Dispatchable`, `SerializesModels`) since they're inherited.

This makes `->toOthers()` broadcast to **everyone** (including the sender) when Echo isn't connected, instead of crashing. When Echo IS connected, it correctly excludes the sender.

**Debugging checklist (in order)**:
1. Check for `echo-private:` / `echo:` listener mismatch first (Error 2b) — fix ALL occurrences
2. Create the `BroadcastEvent` base class and update all events to extend it
3. Ensure Reverb keys in `.env` are fresh values (not `APP_KEY`, no `base64:` prefix)
4. Ensure only ONE Reverb instance is running — kill all PHP processes and start fresh
5. Clear caches: `php artisan view:clear && php artisan cache:clear && php artisan config:clear`
6. Hard-refresh browser (`Ctrl+Shift+R`)

### Port alignment checklist

| `.env` key | Default | Must match |
|---|---|---|
| `REVERB_PORT` | 8081 | Reverb server `--port` flag |
| `VITE_REVERB_PORT` | 8081 | Same as `REVERB_PORT` |
| `REVERB_SCHEME` | http | `http` for local, `https` for TLS |
| `VITE_REVERB_SCHEME` | http | Same as `REVERB_SCHEME` |
| `REVERB_HOST` | 127.0.0.1 | Reverb server `--host` flag |
| `VITE_REVERB_HOST` | 127.0.0.1 | Same as `REVERB_HOST` |

### PrivateChannel can cause "Invalid socket ID undefined"

If the `x-socket-id: undefined` error persists even after env config is correct and Reverb is running, check the `echo-private:` vs `echo:` listener mismatch first (Error 2b). If that's not the issue, **switch events from `PrivateChannel` to public `Channel`**:

```php
// Before (private — requires auth, triggers Pusher auth flow)
use Illuminate\Broadcasting\Channel;
return [new PrivateChannel('workspaces.' . $this->meeting->workspace_id)];

// After (public — no auth needed)
use Illuminate\Broadcasting\Channel;
return [new Channel('workspaces.' . $this->meeting->workspace_id)];
```

Then **remove the corresponding auth routes** from `routes/channels.php` (or clear the file entirely if all channels are public).

Private channels require the browser to authenticate via an HTTP POST to `/broadcasting/auth`, which needs a valid socket ID. If the socket ID is `undefined` (i.e., the WebSocket hasn't connected yet), this creates a circular failure. Public channels skip this entirely.

Use `PrivateChannel` only when you need per-user authorization (e.g., private notifications). For workspace/board broadcasting, public `Channel` is simpler and avoids this class of error.

### Event class checklist for Reverb

Event classes (`app/Events/*.php`) should:
- Implement `ShouldBroadcastNow` (not `ShouldBroadcast` unless queued)
- Use `Channel` (public) by default; only use `PrivateChannel` when auth is truly needed
- Return channel name from `broadcastOn()` that matches `routes/channels.php` auth definitions (if using private channels)
- Not reference any Pusher-specific classes — Reverb is fully compatible with the standard Laravel broadcasting API

### Dual Echo initialization files

This project has both `resources/js/bootstrap.js` and `resources/js/echo.js`. Only `bootstrap.js` is imported by `resources/js/app.js` (which is the Vite entry point). The `echo.js` file is orphaned and can be ignored or removed. Reverb config lives in `bootstrap.js`:

```js
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1',
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8081,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```
