<?php
/**
 * Skapa ett nytt blogginlägg med alla fyra delarna rätt:
 * sidkatalog + index.txt, databaspost, kategorilista och datumsortering.
 *
 *   php bin/nytt-inlagg.php --slug=... --titel="..." --kategori=mat-halsa \
 *       --beskrivning="..." [--datum="YYYY-MM-DD HH:MM:SS"] [--cover=bild.jpg]
 *
 * Körs från bl-content-roten (repots rot). Skriver uuid:t till stdout så att
 * bilder kan läggas i uploads/pages/<uuid>/.
 */

$opts = getopt('', ['slug:', 'titel:', 'kategori:', 'beskrivning:', 'datum::', 'cover::']);
foreach (['slug', 'titel', 'kategori', 'beskrivning'] as $req) {
    if (empty($opts[$req])) {
        fwrite(STDERR, "Saknar --$req\n");
        exit(1);
    }
}
$root = dirname(__DIR__);
$slug = $opts['slug'];
if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
    fwrite(STDERR, "Slug får bara innehålla a-z, 0-9 och bindestreck.\n");
    exit(1);
}

$pagesFile = "$root/databases/pages.php";
$catsFile  = "$root/databases/categories.php";
$load = fn($f) => json_decode(preg_replace('/^.*?\n/', '', file_get_contents($f)), true);
$save = function ($f, $d) {
    file_put_contents($f, "<?php defined('BLUDIT') or die('Bludit CMS.'); ?>\n"
        . json_encode($d, JSON_PRETTY_PRINT));
};

$pages = $load($pagesFile);
$cats  = $load($catsFile);
if (isset($pages[$slug])) { fwrite(STDERR, "Slug finns redan: $slug\n"); exit(1); }
if (!isset($cats[$opts['kategori']])) {
    fwrite(STDERR, "Okänd kategori: {$opts['kategori']} (finns: " . implode(', ', array_keys($cats)) . ")\n");
    exit(1);
}

$uuid = md5(random_bytes(16));
$maxPos = max(array_map(fn($p) => (int)($p['position'] ?? 0), $pages) ?: [0]);
$pages[$slug] = [
    'title' => $opts['titel'],
    'description' => $opts['beskrivning'],
    'username' => 'erik',
    'tags' => [],
    'type' => 'published',
    'date' => $opts['datum'] ?? date('Y-m-d H:i:s'),
    'dateModified' => '',
    'allowComments' => false,
    'position' => $maxPos + 1,
    'coverImage' => $opts['cover'] ?? '',
    'md5file' => '',
    'category' => $opts['kategori'],
    'uuid' => $uuid,
    'parent' => '',
    'template' => '',
    'noindex' => false,
    'nofollow' => false,
    'noarchive' => false,
];
// Bludit läser flödesordningen ur filens ordning – sortera på datum, nyast först.
uasort($pages, fn($a, $b) => strcmp($b['date'], $a['date']));
$save($pagesFile, $pages);

$cats[$opts['kategori']]['list'][] = $slug;
$save($catsFile, $cats);

mkdir("$root/pages/$slug", 0755, true);
file_put_contents("$root/pages/$slug/index.txt",
    "Skriv ingressen här – första stycket visas större på artikelsidan.\n\nOch brödtexten här.\n");
mkdir("$root/uploads/pages/$uuid", 0755, true);

echo "Skapat: pages/$slug/index.txt\n";
echo "uuid:   $uuid  (bilder: uploads/pages/$uuid/)\n";
echo "Nästa:  skriv texten, committa och pusha.\n";
