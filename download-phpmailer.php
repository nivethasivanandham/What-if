<?php
/**
 * PHPMailer Automatic Downloader — What If Food Ordering Platform
 * Downloads PHPMailer directly from GitHub into includes/PHPMailer/
 *
 * HOW TO USE:
 *   Browser: http://localhost/what_if01/download-phpmailer.php
 *   PHP CLI:  php download-phpmailer.php
 *
 * AFTER SUCCESS: Delete this file before deploying to production.
 */

$targetDir = __DIR__ . '/includes/PHPMailer/';

// Create directory if it does not exist
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$files = [
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php'      => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
];

$results = [];

foreach ($files as $filename => $url) {
    $outputPath = $targetDir . $filename;

    // Skip if already downloaded and valid (non-empty)
    if (file_exists($outputPath) && filesize($outputPath) > 1000) {
        $results[$filename] = [
            'status'  => 'already',
            'message' => 'Already installed (' . round(filesize($outputPath) / 1024, 1) . ' KB). Skipped.',
        ];
        continue;
    }

    $content = false;

    // Method 1: file_get_contents (works when allow_url_fopen is on)
    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(['http' => ['timeout' => 20]]);
        $content = @file_get_contents($url, false, $ctx);
    }

    // Method 2: cURL fallback
    if ($content === false && function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $content = curl_exec($ch);
        curl_close($ch);
    }

    if ($content !== false && strlen($content) > 1000) {
        if (file_put_contents($outputPath, $content) !== false) {
            $results[$filename] = [
                'status'  => 'success',
                'message' => 'Downloaded successfully — ' . round(strlen($content) / 1024, 1) . ' KB saved.',
            ];
        } else {
            $results[$filename] = [
                'status'  => 'danger',
                'message' => 'Download OK but failed to write file. Check directory write permissions on includes/PHPMailer/.',
            ];
        }
    } else {
        $results[$filename] = [
            'status'  => 'danger',
            'message' => 'Could not download from GitHub. Check your internet connection or allow_url_fopen/cURL settings.',
        ];
    }
}

// Verify the mailer.php integration path
$mailerOk = file_exists(__DIR__ . '/includes/PHPMailer/PHPMailer.php')
         && file_exists(__DIR__ . '/includes/PHPMailer/SMTP.php')
         && file_exists(__DIR__ . '/includes/PHPMailer/Exception.php');

$successCount = count(array_filter($results, fn($r) => in_array($r['status'], ['success', 'already'])));
$allOk        = $successCount === 3 && $mailerOk;

// CLI mode — print plain text output
if (PHP_SAPI === 'cli') {
    echo "\n=== What If — PHPMailer Installer ===\n\n";
    foreach ($results as $file => $res) {
        $icon = $res['status'] === 'danger' ? '✗' : '✓';
        echo "  {$icon}  {$file}: {$res['message']}\n";
    }
    echo "\n" . ($allOk ? "✓ All files installed. SMTP email is ready.\n" : "✗ Installation incomplete. Check errors above.\n") . "\n";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHPMailer Installer — What If</title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --red: #e23744;
      --red-dark: #c0392b;
      --green: #10b981;
      --gold: #f59e0b;
      --bg: #fafbfc;
      --card: #ffffff;
      --border: #e5e7eb;
      --text: #1c1c1c;
      --muted: #6b7280;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.08);
      max-width: 520px;
      width: 100%;
      overflow: hidden;
    }
    /* Header */
    .card-header {
      background: linear-gradient(135deg, #1a0a0a 0%, #2d1111 100%);
      padding: 28px 32px;
      text-align: center;
    }
    .logo-icon {
      width: 56px;
      height: 56px;
      background: rgba(226,55,68,.2);
      border: 2px solid rgba(226,55,68,.4);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      color: var(--red);
      margin: 0 auto 14px;
      animation: pulse 2.5s infinite;
    }
    @keyframes pulse {
      0%   { box-shadow: 0 0 0 0 rgba(226,55,68,.4); }
      70%  { box-shadow: 0 0 0 14px rgba(226,55,68,0); }
      100% { box-shadow: 0 0 0 0 rgba(226,55,68,0); }
    }
    .card-header h1 {
      font-family: 'Nunito', sans-serif;
      font-size: 20px;
      font-weight: 900;
      color: #fff;
      margin-bottom: 4px;
    }
    .card-header p {
      font-size: 12.5px;
      color: rgba(255,255,255,.5);
    }
    /* Body */
    .card-body { padding: 28px 32px; }
    /* File rows */
    .file-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 0;
      border-bottom: 1px solid var(--border);
      gap: 12px;
    }
    .file-row:last-child { border-bottom: none; }
    .file-info { flex: 1; min-width: 0; }
    .file-name {
      font-weight: 700;
      font-size: 14.5px;
      color: var(--text);
      margin-bottom: 3px;
    }
    .file-msg {
      font-size: 11.5px;
      color: var(--muted);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    /* Badges */
    .badge {
      font-size: 10.5px;
      font-weight: 800;
      padding: 5px 12px;
      border-radius: 50px;
      white-space: nowrap;
      letter-spacing: .5px;
      text-transform: uppercase;
    }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-already { background: #e0f2fe; color: #075985; }
    .badge-danger  { background: #fee2e2; color: #991b1b; }
    /* Result banner */
    .result-banner {
      border-radius: 12px;
      padding: 14px 18px;
      margin-top: 20px;
      font-size: 13.5px;
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }
    .result-banner i { font-size: 18px; margin-top: 1px; flex-shrink: 0; }
    .result-banner.ok  { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .result-banner.err { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    /* Buttons */
    .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 13px 20px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      transition: .2s;
      border: none;
      margin-top: 12px;
    }
    .btn-red  { background: var(--red); color: #fff; }
    .btn-red:hover { background: var(--red-dark); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(226,55,68,.3); }
    .btn-ghost { background: transparent; color: var(--text); border: 1.5px solid var(--border); }
    .btn-ghost:hover { border-color: var(--red); color: var(--red); }
    /* Path info */
    .path-info {
      background: #f3f4f6;
      border-radius: 8px;
      padding: 10px 14px;
      margin-top: 18px;
      font-size: 11.5px;
      color: var(--muted);
    }
    .path-info code {
      font-family: monospace;
      background: #e5e7eb;
      padding: 2px 6px;
      border-radius: 4px;
      color: #374151;
    }
  </style>
</head>
<body>
  <div class="card">
    <!-- Header -->
    <div class="card-header">
      <div class="logo-icon"><i class="fas fa-envelope-open-text"></i></div>
      <h1>PHPMailer Installer</h1>
      <p>What If Food Ordering Platform — SMTP Library Setup</p>
    </div>

    <!-- File results -->
    <div class="card-body">
      <div>
        <?php foreach ($results as $filename => $res): ?>
          <div class="file-row">
            <div class="file-info">
              <div class="file-name"><i class="fas fa-file-code" style="color:var(--muted);font-size:12px;margin-right:6px;"></i><?= htmlspecialchars($filename) ?></div>
              <div class="file-msg"><?= htmlspecialchars($res['message']) ?></div>
            </div>
            <span class="badge badge-<?= $res['status'] ?>">
              <?php
                if ($res['status'] === 'success') echo '✓ Downloaded';
                elseif ($res['status'] === 'already') echo '✓ Installed';
                else echo '✗ Failed';
              ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Result banner -->
      <?php if ($allOk): ?>
        <div class="result-banner ok">
          <i class="fas fa-check-circle"></i>
          <div>
            <strong>All 3 PHPMailer files are installed!</strong><br>
            SMTP email notifications are now active for ticket alerts. You can safely delete this installer script before going to production.
          </div>
        </div>
        <a href="index.php" class="btn btn-red"><i class="fas fa-home"></i> Go to What If</a>
        <a href="admin/support/tickets.php" class="btn btn-ghost"><i class="fas fa-ticket-alt"></i> Go to Admin Tickets</a>
      <?php else: ?>
        <div class="result-banner err">
          <i class="fas fa-exclamation-circle"></i>
          <div>
            <strong>Installation incomplete.</strong><br>
            Check your internet connection or ensure PHP has <code>allow_url_fopen = On</code> or cURL enabled in <code>php.ini</code>.
          </div>
        </div>
        <a href="download-phpmailer.php" class="btn btn-red"><i class="fas fa-redo"></i> Retry Download</a>
      <?php endif; ?>

      <!-- Target path info -->
      <div class="path-info">
        <i class="fas fa-folder-open" style="color:var(--red);margin-right:5px;"></i>
        Install path: <code>includes/PHPMailer/</code>
        &nbsp;·&nbsp;
        Loaded by: <code>includes/mailer.php</code>
      </div>
    </div>
  </div>
</body>
</html>
