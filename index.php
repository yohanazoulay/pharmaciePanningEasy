<?php
// Auto delete save files older than 15 days
$savesDir = __DIR__ . '/saves';
if (!is_dir($savesDir)) {
    mkdir($savesDir, 0777, true);
}
$files = glob($savesDir . '/*.save');
$now = time();
foreach ($files as $file) {
    if ($now - filemtime($file) > 15 * 24 * 60 * 60) {
        @unlink($file);
    }
}

$code = '';
$schedule = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['load_code'])) {
        $code = preg_replace('/[^0-9]/', '', $_POST['load_code']);
        $file = "$savesDir/$code.save";
        if (file_exists($file)) {
            $schedule = json_decode(file_get_contents($file), true);
        } else {
            $code = '';
        }
    } elseif (isset($_POST['new_project'])) {
        do {
            $code = str_pad(strval(random_int(0, 99999999)), 8, '0', STR_PAD_LEFT);
            $file = "$savesDir/$code.save";
        } while (file_exists($file));
        file_put_contents($file, json_encode($schedule));
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planification Pharmacie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php if (!$code): ?>
    <div class="starter">
        <h1>Planification Pharmacie</h1>
        <form method="POST" class="new-load">
            <button type="submit" name="new_project">Nouveau projet</button>
        </form>
        <form method="POST" class="new-load">
            <input type="text" name="load_code" placeholder="Code de projet" pattern="\d{8}" maxlength="8">
            <button type="submit">Charger</button>
        </form>
    </div>
<?php else: ?>
    <div class="app">
        <h1>Code projet : <span id="projectCode"><?php echo htmlspecialchars($code); ?></span></h1>
        <div id="schedule"></div>
        <button id="saveBtn">Sauvegarder</button>
    </div>
    <script>
        const initialSchedule = <?php echo json_encode($schedule); ?>;
        const projectCode = "<?php echo htmlspecialchars($code); ?>";
    </script>
    <script src="script.js"></script>
<?php endif; ?>
</body>
</html>
