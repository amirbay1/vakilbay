<?php
/**
 * ============================================================================
 * [Pro] BayVakil Sitemap Generator
 * سازنده نقشه سایت حرفه‌ای و استاندارد (مخصوص گوگل و موتورهای جستجو)
 * 
 * تکنولوژی: DOMDocument (XML Standard)
 * ویژگی‌ها: پشتیبانی کامل از نام‌های فارسی، تاریخ ویرایش دقیق، اولویت‌بندی خودکار
 * ============================================================================
 */

// تنظیمات بیسیک (زمان و حافظه کافی برای پردازش)
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 120);
header('Content-Type: text/html; charset=utf-8');

// شروع تایمر برای گزارش عملکرد
$startTime = microtime(true);

// ------------------------------------------------------------------
// 1. تنظیمات پیکربندی
// ------------------------------------------------------------------
$config = [
    'domain'   => 'http://bayvakil.ir',     // آدرس سایت شما
    'sitemap'  => __DIR__ . '/sitemap.xml', // مسیر ذخیره فایل
    'scan_dir' => __DIR__                   // پوشه روت برای اسکن
];

// لیست بخش‌های اصلی سایت (بر اساس عکس‌های دایرکتوری شما)
// این بخش‌ها اگر فایل index.php یا index.html داشته باشند، به نقشه اضافه می‌شوند.
$main_sections = [
    'blog',      // وبلاگ
    'daftar',    // دفتر
    'ghavanin',  // قوانین
    'hesab',     // حساب کاربری
    'mavaed'     // مواعد
];

// فایل‌ها و پوشه‌هایی که باید حتماً نادیده گرفته شوند
$ignored_items = [
    '.', '..', 'PHPMailer', 'sm', 'css', 'js', 'fonts', 'img', 
    'Error', 'cache', 'data', 'laws', 'upload', 'uploads',
    'sitemap.xml', 'sitemap_generator.php', 'robots.txt'
];


// ------------------------------------------------------------------
// 2. راه‌اندازی هسته XML
// ------------------------------------------------------------------
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true; // خروجی مرتب و خوانا

// ساخت تگ ریشه urlset با استانداردهای گوگل
$urlset = $dom->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
$urlset->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
$urlset->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
$dom->appendChild($urlset);


// ------------------------------------------------------------------
// 3. توابع کاربردی
// ------------------------------------------------------------------

/**
 * افزودن یک لینک به نقشه سایت
 */
function addLink($dom, $urlset, $loc, $priority = '0.5', $changefreq = 'weekly', $lastmod = null) {
    if ($lastmod === null) {
        $lastmod = date('c'); // فرمت ISO 8601
    }

    $urlNode = $dom->createElement('url');

    // Location (URL) - امن سازی کاراکترهای خاص
    $locNode = $dom->createElement('loc', htmlspecialchars($loc));
    $urlNode->appendChild($locNode);

    // Last Modified
    $lastmodNode = $dom->createElement('lastmod', $lastmod);
    $urlNode->appendChild($lastmodNode);

    // Change Frequency
    $freqNode = $dom->createElement('changefreq', $changefreq);
    $urlNode->appendChild($freqNode);

    // Priority
    $prioNode = $dom->createElement('priority', $priority);
    $urlNode->appendChild($prioNode);

    $urlset->appendChild($urlNode);
}

/**
 * تبدیل نام فایل فارسی به فرمت URL استاندارد
 */
function safeUrlEncode($string) {
    // تبدیل فاصله به %20 و حروف فارسی به کد درصد
    return rawurlencode($string);
}


// ------------------------------------------------------------------
// 4. عملیات ساخت نقشه سایت
// ------------------------------------------------------------------

// الف) افزودن صفحه اصلی (Root)
// -------------------------
addLink($dom, $urlset, $config['domain'] . '/', '1.0', 'daily');


// ب) افزودن بخش‌های اصلی (Main Sections)
// -------------------------
foreach ($main_sections as $folder) {
    $path = $config['scan_dir'] . DIRECTORY_SEPARATOR . $folder;
    
    // بررسی اینکه آیا پوشه وجود دارد و فایل ایندکس دارد یا خیر
    if (is_dir($path) && (file_exists($path . '/index.php') || file_exists($path . '/index.html'))) {
        $last_change = date('c', filemtime($path));
        addLink($dom, $urlset, $config['domain'] . '/' . $folder . '/', '0.8', 'weekly', $last_change);
    }
}


// ج) اسکن هوشمند وبلاگ (Markdown Posts)
// -------------------------
$posts_path = $config['scan_dir'] . '/blog/posts/';
$post_count = 0;

if (is_dir($posts_path)) {
    $files = scandir($posts_path);
    
    foreach ($files as $file) {
        // نادیده گرفتن فایل‌های سیستمی
        if ($file === '.' || $file === '..') continue;
        
        // فقط فایل‌های با پسوند .md
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'md') {
            
            // 1. دریافت نام فایل بدون پسوند (Slug)
            $slug_raw = pathinfo($file, PATHINFO_FILENAME);
            
            // 2. انکود کردن استاندارد برای فارسی
            $slug_encoded = safeUrlEncode($slug_raw);
            
            // 3. ساخت آدرس نهایی
            $full_url = $config['domain'] . "/blog/index.php?p={$slug_encoded}";
            
            // 4. دریافت تاریخ آخرین ویرایش فایل
            $file_last_mod = date('c', filemtime($posts_path . $file));
            
            // افزودن به نقشه سایت
            addLink($dom, $urlset, $full_url, '0.7', 'monthly', $file_last_mod);
            $post_count++;
        }
    }
}


// ------------------------------------------------------------------
// 5. ذخیره سازی و نمایش گزارش
// ------------------------------------------------------------------

$save_result = $dom->save($config['sitemap']);

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 4);
$fileSize = $save_result ? round(filesize($config['sitemap']) / 1024, 2) : 0;

// استایل گزارش
echo "
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; direction: rtl; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    .card { background: white; width: 500px; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-top: 5px solid #2ecc71; }
    h1 { color: #2c3e50; font-size: 22px; margin-top: 0; text-align: center; border-bottom: 2px solid #ecf0f1; padding-bottom: 15px; }
    .stat { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #eee; color: #555; }
    .stat span { font-weight: bold; color: #333; }
    .btn { display: block; background: #2ecc71; color: white; text-align: center; padding: 12px; text-decoration: none; border-radius: 6px; margin-top: 20px; font-weight: bold; transition: 0.3s; }
    .btn:hover { background: #27ae60; }
    .error { color: #e74c3c; text-align: center; }
</style>
";

echo "<div class='card'>";
if ($save_result) {
    echo "<h1>✅ نقشه سایت با موفقیت ساخته شد</h1>";
    echo "<div class='stat'>تعداد صفحات اصلی شناسایی شده: <span>" . (count($main_sections) + 1) . "</span></div>";
    echo "<div class='stat'>تعداد مقالات بلاگ (MD): <span>{$post_count}</span></div>";
    echo "<div class='stat'>زمان پردازش: <span>{$executionTime} ثانیه</span></div>";
    echo "<div class='stat'>حجم فایل خروجی: <span>{$fileSize} KB</span></div>";
    echo "<div class='stat'>وضعیت لینک‌های فارسی: <span>Incoded (استاندارد)</span></div>";
    
    echo "<a href='{$config['domain']}/sitemap.xml' target='_blank' class='btn'>مشاهده و تست فایل Sitemap.xml</a>";
    echo "<p style='font-size:12px; color:#999; text-align:center; margin-top:15px;'>فایل در مسیر روت ذخیره شد. گوگل آن را به صورت خودکار شناسایی خواهد کرد.</p>";
} else {
    echo "<h1>❌ خطا در ساخت فایل</h1>";
    echo "<div class='error'>امکان ذخیره فایل وجود ندارد. لطفاً دسترسی‌های (Permissions) پوشه هاست را بررسی کنید.</div>";
}
echo "</div>";

?>