<?php
// Auto-delete .save files older than 15 days
foreach (glob("*.save") as $file) {
    if (filemtime($file) < time() - 15*24*60*60) {
        unlink($file);
    }
}

function generate_code() {
    return str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
}

$code = $_GET['code'] ?? null;
$new = isset($_GET['new']);
$message = '';
$error = '';
$data = ['schedule'=>[], 'pharmacists'=>[]];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $code = preg_replace('/\D/', '', $_POST['code']);
    $data['schedule'] = $_POST['schedule'] ?? [];
    $data['pharmacists'] = $_POST['pharmacists'] ?? [];
    file_put_contents("$code.save", json_encode($data));
    $message = 'Projet sauvegardé.';
}

if ($new && !$code) {
    $code = generate_code();
    file_put_contents("$code.save", json_encode($data));
} elseif ($code) {
    if (file_exists("$code.save")) {
        $content = file_get_contents("$code.save");
        $data = json_decode($content, true) ?: $data;
    } else {
        $error = 'Projet introuvable.';
        $code = null;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Planning Pharmacie</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<?php if(!$code): ?>
    <h1>Planning Pharmacie</h1>
    <?php if($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <div class="options">
        <a class="btn" href="?new=1">Nouveau projet</a>
        <form method="get" class="load-form">
            <input type="text" name="code" placeholder="Code projet" minlength="8" maxlength="8" required>
            <button class="btn" type="submit">Charger</button>
        </form>
    </div>
<?php else: ?>
    <h1>Projet #<?php echo htmlspecialchars($code); ?></h1>
    <?php if($message): ?><p class="message"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post" id="scheduleForm">
        <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
        <table>
            <thead>
                <tr>
                    <th>Jour</th>
                    <th>Matin</th>
                    <th>Après-midi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $daysNames = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
                for($week=1;$week<=2;$week++){
                    foreach($daysNames as $d){
                        $index = ($week-1)*7 + array_search($d,$daysNames);
                        $sch = $data['schedule'][$index] ?? [];
                        $ph = $data['pharmacists'][$index] ?? [];
                        echo '<tr>';
                        echo '<td>'.$d.' S'.$week.'</td>';
                        echo '<td class="slot">'
                            .'<input type="time" name="schedule['.$index.'][m_start]" value="'.($sch['m_start'] ?? '').'">'
                            .'<input type="time" name="schedule['.$index.'][m_end]" value="'.($sch['m_end'] ?? '').'">'
                            .'<select name="pharmacists['.$index.'][m]">'
                                .'<option value="A"'.(($ph['m'] ?? '')=='A'?' selected':'').'>Pharmacien A</option>'
                                .'<option value="B"'.(($ph['m'] ?? '')=='B'?' selected':'').'>Pharmacien B</option>'
                            .'</select>'
                            .'</td>';
                        echo '<td class="slot">'
                            .'<input type="time" name="schedule['.$index.'][a_start]" value="'.($sch['a_start'] ?? '').'">'
                            .'<input type="time" name="schedule['.$index.'][a_end]" value="'.($sch['a_end'] ?? '').'">'
                            .'<select name="pharmacists['.$index.'][a]">'
                                .'<option value="A"'.(($ph['a'] ?? '')=='A'?' selected':'').'>Pharmacien A</option>'
                                .'<option value="B"'.(($ph['a'] ?? '')=='B'?' selected':'').'>Pharmacien B</option>'
                            .'</select>'
                            .'</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
        <div class="totals">
            <p>Pharmacien A: <span id="totalA">0</span> h</p>
            <p>Pharmacien B: <span id="totalB">0</span> h</p>
        </div>
        <button class="btn" type="submit" name="save" id="saveBtn">Sauvegarder</button>
    </form>
    <p class="code-info">Code du projet: <strong><?php echo htmlspecialchars($code); ?></strong></p>
<?php endif; ?>
</div>
<script src="script.js"></script>
</body>
</html>
