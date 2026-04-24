import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.App = window.App ?? {};
window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    const rawHost = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname;
    const parsedHost = rawHost.replace(/^https?:\/\//, '').replace(/\/$/, '');
    const rawScheme = (import.meta.env.VITE_REVERB_SCHEME ?? 'http').replace(':', '');
    const forceTls = rawScheme === 'https';
    const parsedPort = Number(import.meta.env.VITE_REVERB_PORT ?? (forceTls ? 443 : 80));
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: parsedHost,
        wsPort: parsedPort,
        wssPort: parsedPort,
        forceTLS: forceTls,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        csrfToken,
        withCredentials: true,
    });

    const dispatchRealtimeStatus = (state, reason = null) => {
        window.dispatchEvent(new CustomEvent('freshleaf-realtime-status', {
            detail: { state, reason },
        }));
    };

    const connection = window.Echo?.connector?.pusher?.connection;

    if (connection) {
        connection.bind('connected', () => dispatchRealtimeStatus('connected'));
        connection.bind('disconnected', () => dispatchRealtimeStatus('disconnected'));
        connection.bind('unavailable', () => dispatchRealtimeStatus('unavailable'));
        connection.bind('failed', () => dispatchRealtimeStatus('failed'));
        connection.bind('error', (error) => {
            const message = error?.error?.message ?? error?.type ?? 'unknown';
            dispatchRealtimeStatus('error', message);
        });
    }
}
