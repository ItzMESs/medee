// Service worker: shows push notifications sent by the site and opens the article on click.
self.addEventListener('push', function (event) {
  var data = {};
  try { data = event.data ? event.data.json() : {}; } catch (e) { data = { title: event.data ? event.data.text() : '' }; }
  var base = self.registration.scope;
  event.waitUntil(self.registration.showNotification(data.title || 'Шинэ мэдээ', {
    body: data.body || '',
    icon: base + 'assets/img/icon-192.png',
    badge: base + 'assets/img/badge-72.png',
    image: data.image || undefined,
    tag: data.tag || undefined,
    data: { url: data.url || base },
  }));
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  var url = (event.notification.data && event.notification.data.url) || self.registration.scope;
  event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
    for (var i = 0; i < list.length; i++) {
      if (list[i].url === url && 'focus' in list[i]) return list[i].focus();
    }
    return clients.openWindow(url);
  }));
});

self.addEventListener('install', function () { self.skipWaiting(); });
self.addEventListener('activate', function (event) { event.waitUntil(self.clients.claim()); });
