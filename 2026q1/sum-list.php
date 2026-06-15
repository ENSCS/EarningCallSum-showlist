<?php
$script_dir     = realpath(dirname(__FILE__));
$script_url_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
$folder_name    = basename($script_dir);
$base_url       = 'https://www.mrlikestock.com';
$reader_prefix  = 'https://www.mrlikestock.com/web/share/notes.php?url=';

// หาไฟล์ .md ทั้งหมด
$files = array();
$items = scandir($script_dir);
foreach ($items as $item) {
    if (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
        $files[] = $item;
    }
}

// เรียง: ตัวเลขก่อน ตามด้วย A-Z
usort($files, function($a, $b) {
    $a_num = is_numeric(substr($a, 0, 1));
    $b_num = is_numeric(substr($b, 0, 1));
    if ($a_num && !$b_num) return -1;
    if (!$a_num && $b_num) return 1;
    return strcmp($a, $b);
});

// จัดกลุ่มตามตัวอักษรนำ
$groups = array();
foreach ($files as $file) {
    $first  = substr($file, 0, 1);
    $letter = is_numeric($first) ? '0-9' : strtoupper($first);
    $name   = pathinfo($file, PATHINFO_FILENAME);
    $parts  = explode('_', $name);
    $ticker = strtoupper($parts[0]);
    $file_url   = $base_url . $script_url_dir . rawurlencode($file);
    $reader_url = $reader_prefix . urlencode($file_url);
    $groups[$letter][] = array(
        'ticker'     => $ticker,
        'reader_url' => $reader_url,
    );
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>สรุป Earnings Call <?php echo htmlspecialchars($folder_name); ?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600;700&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg: #faf9f5;
  --fg: #1d1d1f;
  --muted: #6e6e73;
  --border: #e5e5e7;
  --link: #0071e3;
  --link-hover: #0077ed;
  --code-bg: #f5f0e8;
  --radius: 12px;
  --h1color: #a9583e;
  --h3color: #17437d;
}

[data-theme="dark"] {
  --bg: #181715;
  --fg: #f5f5f7;
  --muted: #86868b;
  --border: #2d2d2d;
  --link: #47a8ff;
  --link-hover: #70bfff;
  --code-bg: #252320;
  --h3color: #005cb3;
}

body {
  background: var(--bg);
  color: var(--fg);
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans Thai", sans-serif;
  font-size: 17px;
  line-height: 1.75;
  -webkit-font-smoothing: antialiased;
  transition: background 0.2s, color 0.2s;
}

.container {
  max-width: 740px;
  margin: 0 auto;
  padding: 3rem 2rem 8rem;
}

.theme-wrap {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 2rem;
}

.theme-toggle {
  background: var(--code-bg);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 0.3em 0.9em;
  font-size: 0.85em;
  color: var(--fg);
  cursor: pointer;
  transition: background 0.2s;
}
.theme-toggle:hover { background: var(--border); }

h1 {
  font-family: "Noto Sans Thai", sans-serif;
  font-size: 2.2em;
  font-weight: 700;
  color: var(--h1color);
  letter-spacing: -0.02em;
  line-height: 1.4;
  margin-bottom: 0.3em;
}

.subtitle {
  color: var(--muted);
  font-size: 0.9em;
  margin-bottom: 2rem;
}

.count {
  display: inline-block;
  background: var(--code-bg);
  border: 1px solid var(--border);
  border-radius: 15px;
  padding: 0.15em 0.8em;
  font-size: 0.7em;
  color: var(--muted);
  margin-left: 0.6rem;
  vertical-align: middle;
}

hr {
  border: none;
  border-top: 1px solid var(--border);
  margin: 1.5rem 0;
}

.section-letter {
  font-family: "Noto Sans Thai", sans-serif;
  font-size: 1.4em;
  font-weight: 700;
  color: var(--h3color);
  margin: 2rem 0 0.7rem;
  letter-spacing: 0.02em;
}

.chips-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
  margin-bottom: 0.5rem;
}

.chip {
  display: inline-block;
  background: var(--code-bg);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 0.28em 0.85em;
  font-size: 1.1em;
  font-weight: 600;
  color: var(--muted);
  text-decoration: none;
  letter-spacing: 0.03em;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}

.chip:hover {
  background: #a9583e;
  border-color: #a9583e;
  color: #fff;
  text-decoration: none;
}

.empty {
  color: var(--muted);
  font-size: 0.95em;
  padding: 2rem 0;
}

@media (max-width: 600px) {
  body { font-size: 15px; }
  .container { padding: 1.5rem 1rem 5rem; }
  h1 { font-size: 1.8em; }
}
</style>
</head>
<body>
<div class="container">

  <div class="theme-wrap">
    <button class="theme-toggle" onclick="toggleTheme()">&#127769; Dark</button>
  </div>

  <h1>
    สรุป Earnings Call <?php echo htmlspecialchars($folder_name); ?>
    <span class="count"><?php echo count($files); ?> ไฟล์</span>
  </h1>
  <p class="subtitle">Earnings Call Summaries</p>
  <hr>

  <?php if (empty($groups)): ?>
    <p class="empty">ไม่พบไฟล์ .md ในโฟลเดอร์นี้</p>
  <?php else: ?>
    <?php foreach ($groups as $letter => $entries): ?>
      <h3 class="section-letter"><?php echo htmlspecialchars($letter); ?></h3>
      <div class="chips-wrap">
        <?php foreach ($entries as $entry): ?>
          <a class="chip" href="<?php echo htmlspecialchars($entry['reader_url']); ?>" target="_blank">
            <?php echo htmlspecialchars($entry['ticker']); ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
<script>
function toggleTheme() {
  var d = document.documentElement;
  var btn = document.querySelector('.theme-toggle');
  if (d.getAttribute('data-theme') === 'dark') {
    d.removeAttribute('data-theme');
    btn.textContent = '\u{1F319} Dark';
  } else {
    d.setAttribute('data-theme', 'dark');
    btn.textContent = '\u2600\uFE0F Light';
  }
}
</script>
</body>
</html>
