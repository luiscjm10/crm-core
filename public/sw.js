self.addEventListener('push', function (event) {
    if (!event.data) return;

    try {
        const data = event.data.json();
        const options = {
            body: data.body || '',
            icon: data.icon || '/notification-icon.png',
            badge: data.badge || '/notification-badge.png',
            data: data.data || {},
            tag: data.tag || 'default',
            renotify: true,
            vibrate: [200, 100, 200],
        };

        event.waitUntil(
            self.registration.showNotification(data.title || 'Notificación', options)
        );
    } catch (e) {
        console.error('Error showing notification:', e);
    }
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const url = event.notification.data?.url;
    if (!url) return;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(function (clientList) {
                for (const client of clientList) {
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});
