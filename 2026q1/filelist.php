<?php
// ========================================
// File Browser - PHP 5 Compatible
// ========================================

// base URL ของโฟลเดอร์ที่ script นี้อยู่
// เช่น /web/earncall/
$script_url_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';

// absolute path ของโฟลเดอร์ที่ script นี้อยู่ (ใช้เป็น root)
$script_dir = realpath(dirname(__FILE__));

// รับ dir จาก GET
$req_dir  = isset($_GET['dir']) ? $_GET['dir'] : $script_dir;
$base_dir = realpath($req_dir);

// Security
if ($base_dir === false || strpos($base_dir, $script_dir) !== 0) {
    $base_dir = $script_dir;
}

// แปลง absolute path → URL
// เช่น /home/.../earncall/2026q1/file.md → /web/earncall/2026q1/file.md
function toUrl($abs_path) {
    global $script_dir, $script_url_dir;
    $rel = substr($abs_path, strlen($script_dir));           // ตัด absolute prefix ออก
    $rel = str_replace('\\', '/', $rel);                     // Windows → forward slash
    $rel = ltrim($rel, '/');
    return $script_url_dir . $rel;
}

function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function getLabel($ext) {
    $ext = strtolower($ext);
    $map = array(
        'pdf'=>'PDF','doc'=>'DOC','docx'=>'DOC','xls'=>'XLS','xlsx'=>'XLS',
        'csv'=>'CSV','ppt'=>'PPT','pptx'=>'PPT','jpg'=>'IMG','jpeg'=>'IMG',
        'png'=>'IMG','gif'=>'IMG','svg'=>'IMG','webp'=>'IMG','mp4'=>'VID',
        'avi'=>'VID','mov'=>'VID','mp3'=>'AUD','wav'=>'AUD','zip'=>'ZIP',
        'rar'=>'ZIP','tar'=>'ZIP','php'=>'PHP','js'=>'JS','py'=>'PY',
        'html'=>'HTML','css'=>'CSS','txt'=>'TXT','md'=>'MD','log'=>'LOG',
        'sql'=>'SQL','json'=>'JSON','xml'=>'XML',
    );
    return isset($map[$ext]) ? $map[$ext] : strtoupper($ext);
}

// อ่านรายการ
$items = scandir($base_dir);
$dirs  = array();
$files = array();
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    $full = $base_dir . DIRECTORY_SEPARATOR . $item;
    if (is_dir($full)) { $dirs[]  = $item; }
    else               { $files[] = $item; }
}
sort($dirs);
sort($files);

// Breadcrumb
$crumb_parts  = array();
$relative_dir = str_replace($script_dir, '', $base_dir);
$relative_dir = ltrim(str_replace('\\', '/', $relative_dir), '/');
if ($relative_dir !== '') {
    $parts = explode('/', $relative_dir);
    $built = $script_dir;
    foreach ($parts as $part) {
        if ($part === '') continue;
        $built .= DIRECTORY_SEPARATOR . $part;
        $crumb_parts[] = array('label' => $part, 'path' => $built);
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File Browser</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f1117; color: #e2e8f0; min-height: 100vh; padding: 24px; }
.container { max-width: 960px; margin: 0 auto; }
header { margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #2d3748; }
header h1 { font-size: 1.4rem; font-weight: 600; color: #90cdf4; }
header p { font-size: 0.8rem; color: #718096; margin-top: 4px; word-break: break-all; }
.breadcrumb { background: #1a202c; border: 1px solid #2d3748; border-radius: 8px; padding: 10px 16px; margin-bottom: 14px; font-size: 0.9rem; }
.breadcrumb a { color: #63b3ed; text-decoration: none; padding: 2px 6px; border-radius: 4px; }
.breadcrumb a:hover { background: #2d3748; }
.breadcrumb span { color: #718096; margin: 0 2px; }
.stats { margin-bottom: 12px; font-size: 0.85rem; color: #718096; }
.search-box { margin-bottom: 14px; }
.search-box input { width: 100%; padding: 10px 16px; background: #1a202c; border: 1px solid #2d3748; border-radius: 8px; color: #e2e8f0; font-size: 0.95rem; outline: none; }
.search-box input:focus { border-color: #63b3ed; }
.file-table { width: 100%; border-collapse: collapse; background: #1a202c; border-radius: 10px; overflow: hidden; border: 1px solid #2d3748; }
.file-table thead { background: #2d3748; }
.file-table th { text-align: left; padding: 12px 16px; font-size: 0.8rem; font-weight: 600; color: #a0aec0; text-transform: uppercase; letter-spacing: 0.05em; }
.file-table td { padding: 11px 16px; font-size: 0.92rem; border-top: 1px solid #2d3748; vertical-align: middle; }
.file-table tr:hover td { background: #253048; }
.file-table a { color: #90cdf4; text-decoration: none; }
.file-table a:hover { text-decoration: underline; }
.dir-link { color: #fbd38d !important; }
.col-size { text-align: right; color: #718096; font-size: 0.85rem; white-space: nowrap; }
.col-date { color: #718096; font-size: 0.85rem; white-space: nowrap; }
.col-action { text-align: center; }
.btn-view { display: inline-block; padding: 4px 12px; background: #2b6cb0; color: #bee3f8 !important; border-radius: 5px; font-size: 0.8rem; text-decoration: none !important; }
.btn-view:hover { background: #3182ce; }
.parent-row td { background: #16202f; }
.ext-badge { display: inline-block; padding: 1px 6px; background: #2d3748; border-radius: 4px; font-size: 0.72rem; color: #a0aec0; font-family: monospace; }
.empty { text-align: center; padding: 40px; color: #4a5568; }
.debug { background:#1a1a2e; border:1px solid #444; border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:0.78rem; color:#888; font-family:monospace; }
</style>
</head>
<body>
<div class="container">

  <header>
    <h1>&#128193; File Browser</h1>
    <p><?php echo htmlspecialchars($base_dir); ?></p>
  </header>

  <!-- Debug (ลบออกได้หลัง confirm ว่า URL ถูก) -->
  <div class="debug">
    script_dir: <?php echo htmlspecialchars($script_dir); ?><br>
    script_url_dir: <?php echo htmlspecialchars($script_url_dir); ?><br>
    base_dir: <?php echo htmlspecialchars($base_dir); ?>
  </div>

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="?dir=<?php echo urlencode($script_dir); ?>">&#127968; root</a>
    <?php foreach ($crumb_parts as $crumb): ?>
      <span>/</span>
      <a href="?dir=<?php echo urlencode($crumb['path']); ?>"><?php echo htmlspecialchars($crumb['label']); ?></a>
    <?php endforeach; ?>
  </div>

  <div class="stats">
    &#128194; <?php echo count($dirs); ?> โฟลเดอร์ &nbsp;|&nbsp;
    &#128196; <?php echo count($files); ?> ไฟล์
  </div>

  <div style="display:flex; gap:10px; margin-bottom:14px;">
    <div style="flex:1;">
      <input type="text" id="searchInput" placeholder="ค้นหาไฟล์หรือโฟลเดอร์..." onkeyup="filterTable()" style="width:100%; padding:10px 16px; background:#1a202c; border:1px solid #2d3748; border-radius:8px; color:#e2e8f0; font-size:0.95rem; outline:none;">
    </div>
    <button onclick="exportCSV()" style="padding:10px 18px; background:#276749; color:#9ae6b4; border:none; border-radius:8px; font-size:0.9rem; cursor:pointer; white-space:nowrap;">&#8595; Export CSV</button>
  </div>

  <table class="file-table" id="fileTable">
    <thead>
      <tr>
        <th>ชื่อ</th>
        <th>ประเภท</th>
        <th class="col-size">ขนาด</th>
        <th class="col-date">แก้ไขล่าสุด</th>
        <th class="col-action">เปิด</th>
      </tr>
    </thead>
    <tbody>

      <?php if ($base_dir !== $script_dir): ?>
      <tr class="parent-row">
        <td colspan="5">
          <a href="?dir=<?php echo urlencode(dirname($base_dir)); ?>" class="dir-link">
            &uarr; ขึ้นไปโฟลเดอร์บน (<?php echo htmlspecialchars(basename(dirname($base_dir))); ?>)
          </a>
        </td>
      </tr>
      <?php endif; ?>

      <?php foreach ($dirs as $dir):
          $dir_path = $base_dir . DIRECTORY_SEPARATOR . $dir;
          $mod_time = date('d/m/Y H:i', filemtime($dir_path));
          $sub_items = scandir($dir_path);
          $sub_count = count($sub_items) - 2;
      ?>
      <tr>
        <td><a href="?dir=<?php echo urlencode($dir_path); ?>" class="dir-link">&#128194; <?php echo htmlspecialchars($dir); ?></a></td>
        <td><span class="ext-badge">folder</span></td>
        <td class="col-size"><?php echo $sub_count; ?> รายการ</td>
        <td class="col-date"><?php echo $mod_time; ?></td>
        <td class="col-action"><a href="?dir=<?php echo urlencode($dir_path); ?>" class="btn-view">เปิด</a></td>
      </tr>
      <?php endforeach; ?>

      <?php if (empty($files) && empty($dirs)): ?>
        <tr><td colspan="5" class="empty">ไม่มีไฟล์ในโฟลเดอร์นี้</td></tr>
      <?php else:
          foreach ($files as $file):
              $file_path = $base_dir . DIRECTORY_SEPARATOR . $file;
              $size      = filesize($file_path);
              $mod_time  = date('d/m/Y H:i', filemtime($file_path));
              $ext       = pathinfo($file, PATHINFO_EXTENSION);
              $label     = getLabel($ext);
              $file_url  = toUrl($file_path);
      ?>
      <tr>
        <td><a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">&#128196; <?php echo htmlspecialchars($file); ?></a></td>
        <td><span class="ext-badge"><?php echo htmlspecialchars($label); ?></span></td>
        <td class="col-size"><?php echo formatSize($size); ?></td>
        <td class="col-date"><?php echo $mod_time; ?></td>
        <td class="col-action"><a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank" class="btn-view">ดู</a></td>
      </tr>
      <?php endforeach; endif; ?>

    </tbody>
  </table>

</div>
<script>
function exportCSV() {
  var rows = document.getElementById('fileTable').getElementsByTagName('tr');
  var lines = ['filename'];
  for (var i = 1; i < rows.length; i++) {
    var row = rows[i];
    if (row.style.display === 'none') continue;
    if (row.className === 'parent-row') continue;
    // เอาเฉพาะไฟล์ (ไม่เอา folder)
    var badge = row.getElementsByClassName('ext-badge')[0];
    if (badge && badge.textContent.trim() === 'folder') continue;
    var cell = row.getElementsByTagName('td')[0];
    if (cell) {
      var name = (cell.textContent || cell.innerText || '').trim();
      // ตัด icon prefix ออก (เช่น "📄 filename.md")
      name = name.replace(/^\S+\s+/, '').trim();
      lines.push('"' + name.replace(/"/g, '""') + '"');
    }
  }
  var csv = lines.join('\n');
  var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  var url = URL.createObjectURL(blob);
  var a = document.createElement('a');
  a.href = url;
  a.download = 'filelist.csv';
  a.click();
  URL.revokeObjectURL(url);
}

function filterTable() {
  var q = document.getElementById('searchInput').value.toLowerCase();
  var rows = document.getElementById('fileTable').getElementsByTagName('tr');
  for (var i = 1; i < rows.length; i++) {
    var row = rows[i];
    if (row.className === 'parent-row') continue;
    var cell = row.getElementsByTagName('td')[0];
    if (cell) {
      var text = cell.textContent || cell.innerText || '';
      row.style.display = (text.toLowerCase().indexOf(q) > -1) ? '' : 'none';
    }
  }
}
</script>
</body>
</html>
