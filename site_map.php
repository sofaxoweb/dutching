<?php
// ═══════════════════════════════════════════════════════════════════
//  SITEMAP — calculadoradedutching.online
//  File:    site_map.php
//  Serves:  /site_map.xml  (routed via .htaccess RewriteRule)
//  Submit:  https://calculadoradedutching.online/site_map.xml
//
//  ARCHITECTURE:
//  This is a tool site (single-page calculator + support pages).
//  Priority and changefreq are hand-tuned per page type:
//
//  /              → Tool homepage  → priority 1.0  | daily
//  /about         → E-E-A-T page   → priority 0.7  | monthly
//  /contact       → Trust signal   → priority 0.6  | monthly
//  /privacy-policy
//  /terms-of-use
//  /disclaimer
//  /cookie-policy → Legal / noindex candidates → priority 0.2 | yearly
//
//  CLEAN URL POLICY:
//  .htaccess strips .html from all URLs.
//  index.html → /  (trailing slash)
//  about.html → /about  (no extension, no trailing slash)
//
//  LASTMOD:
//  Uses actual file mtime from disk — always accurate, never stale.
//  Format: W3C Datetime (YYYY-MM-DDTHH:MM:SS+00:00)
// ═══════════════════════════════════════════════════════════════════

// ── Output XML header immediately — no whitespace before this line ──
header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex'); // sitemap itself should not be indexed

$baseUrl = 'https://calculadoradedutching.online';
$rootDir = __DIR__;

// ════════════════════════════════════════════════════════════════════
//  PAGE REGISTRY
//  Each entry is keyed by clean URL path.
//  'file'        → actual .html filename on disk (for mtime lookup)
//  'priority'    → 0.0–1.0, hand-tuned per page role
//  'changefreq'  → Google hint for crawl frequency
//  'index'       → true = include in sitemap | false = exclude
// ════════════════════════════════════════════════════════════════════

$pages = [

    // ── TOOL PAGE — highest value, crawl daily ───────────────────
    '/' => [
        'file'        => 'index.html',
        'priority'    => '1.0',
        'changefreq'  => 'daily',
        'index'       => true,
    ],

    // ── CONTENT PAGES — E-E-A-T and trust signals ───────────────
    '/about' => [
        'file'        => 'about.html',
        'priority'    => '0.7',
        'changefreq'  => 'monthly',
        'index'       => true,
    ],

    '/contact' => [
        'file'        => 'contact.html',
        'priority'    => '0.6',
        'changefreq'  => 'monthly',
        'index'       => true,
    ],

    // ── LEGAL PAGES — required for AdSense/trust, low crawl value ─
    // Priority 0.2: tells Google these exist but are not content pages.
    // If you later add noindex meta to these pages, set 'index' => false
    // here and they will be excluded from the sitemap automatically.
    '/privacy-policy' => [
        'file'        => 'privacy-policy.html',
        'priority'    => '0.2',
        'changefreq'  => 'yearly',
        'index'       => true,
    ],

    '/terms-of-use' => [
        'file'        => 'terms-of-use.html',
        'priority'    => '0.2',
        'changefreq'  => 'yearly',
        'index'       => true,
    ],

    '/disclaimer' => [
        'file'        => 'disclaimer.html',
        'priority'    => '0.2',
        'changefreq'  => 'yearly',
        'index'       => true,
    ],

    '/cookie-policy' => [
        'file'        => 'cookie-policy.html',
        'priority'    => '0.2',
        'changefreq'  => 'yearly',
        'index'       => true,
    ],
];

// ════════════════════════════════════════════════════════════════════
//  BUILD URL LIST
//  - Reads actual mtime from disk for each file
//  - Skips any entry where 'index' is false
//  - Skips any entry where the file does not exist on disk
//  - Sorts: priority DESC, then path ASC (deterministic output)
// ════════════════════════════════════════════════════════════════════

$urls = [];

foreach ($pages as $path => $config) {

    // Skip excluded pages
    if (empty($config['index'])) {
        continue;
    }

    $filePath = rtrim($rootDir, '/') . '/' . ltrim($config['file'], '/');

    // Skip if file not found on disk — prevents ghost URLs in sitemap
    if (!file_exists($filePath)) {
        continue;
    }

    // W3C Datetime format — required by Google for precise lastmod
    $lastmod = gmdate('Y-m-d\TH:i:s+00:00', filemtime($filePath));

    // Build canonical loc — homepage gets trailing slash, all others do not
    $loc = $baseUrl . $path;

    $urls[] = [
        'loc'        => $loc,
        'lastmod'    => $lastmod,
        'changefreq' => $config['changefreq'],
        'priority'   => $config['priority'],
        '_sort_key'  => (float) $config['priority'],
    ];
}

// Sort: highest priority first, then alphabetical path for determinism
usort($urls, function ($a, $b) {
    $cmp = $b['_sort_key'] <=> $a['_sort_key'];
    return $cmp !== 0 ? $cmp : strcmp($a['loc'], $b['loc']);
});

// ════════════════════════════════════════════════════════════════════
//  OUTPUT XML
//  Namespace: http://www.sitemaps.org/schemas/sitemap/0.9
//  This is the only valid namespace — do not modify it.
// ════════════════════════════════════════════════════════════════════

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($urls as $url) {
    echo '  <url>' . "\n";
    echo '    <loc>'        . htmlspecialchars($url['loc'],        ENT_XML1, 'UTF-8') . '</loc>'        . "\n";
    echo '    <lastmod>'    . htmlspecialchars($url['lastmod'],    ENT_XML1, 'UTF-8') . '</lastmod>'    . "\n";
    echo '    <changefreq>' . htmlspecialchars($url['changefreq'], ENT_XML1, 'UTF-8') . '</changefreq>' . "\n";
    echo '    <priority>'   . htmlspecialchars($url['priority'],   ENT_XML1, 'UTF-8') . '</priority>'   . "\n";
    echo '  </url>' . "\n";
}

echo '</urlset>' . "\n";