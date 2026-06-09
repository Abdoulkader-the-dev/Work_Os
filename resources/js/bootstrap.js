// resources/js/bootstrap.js

import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// ── Axios ─────────────────────────────────────────────────
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ── Laravel Echo + Pusher ─────────────────────────────────
window.Pusher = Pusher;

const broadcastDriver = import.meta.env.VITE_BROADCAST_CONNECTION ?? 'reverb';

if (broadcastDriver === 'pusher') {
    // ── Production: Pusher ─────────────────────────────────
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'eu',
        forceTLS: true,
    });
} else {
    // ── Local dev: Reverb ──────────────────────────────────
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1',
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 8081,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
    });
}
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */
