self.addEventListener('push', event => {
  let data = {};
  try { data = event.data ? event.data.json() : {}; } catch (_) {}
  const title = data.title || 'Galactic Command';
  const options = {
    body: data.body || 'New galactic intelligence is available.',
    tag: data.tag || 'galactic-command',
    data: { url: data.url || '/' },
    renotify: false
  };
  event.waitUntil(self.registration.showNotification(title, options));
});
self.addEventListener('notificationclick', event => {
  event.notification.close();
  const requested = new URL((event.notification.data && event.notification.data.url) || '/', self.location.origin);
  const target = requested.origin === self.location.origin ? requested.href : new URL('/', self.location.origin).href;
  event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clients => {
    for (const client of clients) {
      if (client.url === target && 'focus' in client) return client.focus();
    }
    if (self.clients.openWindow) return self.clients.openWindow(target);
  }));
});
