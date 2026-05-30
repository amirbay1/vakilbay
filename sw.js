// ورژن را برای اطمینان از آپدیت، یک شماره بالا می‌بریم
const CACHE_NAME = 'attorney-fee-calculator-v5';

// لیست تمام فایل‌های ضروری برای اجرای آفلاین برنامه
const urlsToCache = [
  // --- فایل‌های داخل پوشه /hesab/ ---
  './',                  // صفحه اصلی پوشه (index.html)
  './index.html',        // فایل اصلی
  './manifest.json',     // فایل مانیفست (بسیار مهم)
  './icon-192x192.png',  // آیکون‌ها (بسیار مهم)
  './icon-512x512.png',  // آیکون‌ها (بسیار مهم)

  // --- فایل‌های خارج از پوشه /hesab/ با مسیردهی شما ---
  '../css/tailwind.min.js', // فایل CSS واقعی
  '../fonts/Vazirmatn.woff2'
];

// 1. نصب سرویس ورکر و ذخیره فایل‌ها در Cache
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache and caching essential files.');
        return cache.addAll(urlsToCache);
      })
      .catch(error => {
        console.error('Failed to cache files during install:', error);
      })
  );
});

// 2. پاسخ به درخواست‌ها از طریق Cache
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        return response || fetch(event.request);
      })
  );
});

// 3. پاک کردن کش‌های قدیمی
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(name => name !== CACHE_NAME)
          .map(name => caches.delete(name))
      );
    })
  );
});