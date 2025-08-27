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
$data = ['schedule'=>[]];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $code = preg_replace('/\D/', '', $_POST['code']);
    $data['schedule'] = $_POST['schedule'] ?? [];
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
    <div class="columns">
        <div class="planner">
            <h2>Ce que vous avez fait aujourd'hui</h2>
            <form method="post" id="scheduleForm">
                <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Jour</th>
                            <th>Tranches d'ouverture</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $daysNames = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
                        for($day=0;$day<7;$day++){
                            $segments = $data['schedule'][$day] ?? [];
                            echo '<tr>';
                            echo '<td>'.$daysNames[$day].'</td>';
                            echo '<td class="segments" data-day="'.$day.'">';
                            foreach($segments as $i=>$seg){
                                $start = htmlspecialchars($seg['start'] ?? '');
                                $end = htmlspecialchars($seg['end'] ?? '');
                                $ph1 = $seg['ph1'] ?? 'A';
                                $ph2 = $seg['ph2'] ?? 'A';
                                echo '<div class="segment">'
                                    .'<input type="time" name="schedule['.$day.']['.$i.'][start]" value="'.$start.'">'
                                    .'<input type="time" name="schedule['.$day.']['.$i.'][end]" value="'.$end.'">'
                                    .'<select name="schedule['.$day.']['.$i.'][ph1]">'
                                        .'<option value="A"'.($ph1=='A'?' selected':'').'>A S1</option>'
                                        .'<option value="B"'.($ph1=='B'?' selected':'').'>B S1</option>'
                                    .'</select>'
                                    .'<select name="schedule['.$day.']['.$i.'][ph2]">'
                                        .'<option value="A"'.($ph2=='A'?' selected':'').'>A S2</option>'
                                        .'<option value="B"'.($ph2=='B'?' selected':'').'>B S2</option>'
                                    .'</select>'
                                    .'<button type="button" class="remove-segment">&times;</button>'
                                    .'</div>';
                            }
                            echo '</td>';
                            echo '<td><button type="button" class="add-segment" data-day="'.$day.'">Ajouter tranche</button></td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <button class="btn" type="submit" name="save" id="saveBtn">Sauvegarder</button>
            </form>
        </div>
        <div class="summary">
            <h2>Récapitulatif</h2>
            <table class="recap">
                <thead>
                    <tr>
                        <th></th><th>Semaine 1</th><th>Semaine 2</th><th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Pharmacien A</td><td id="w1A">0</td><td id="w2A">0</td><td id="totA">0</td></tr>
                    <tr><td>Pharmacien B</td><td id="w1B">0</td><td id="w2B">0</td><td id="totB">0</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <p class="code-info">Code du projet: <strong><?php echo htmlspecialchars($code); ?></strong></p>
<?php endif; ?>
</div>
<script src="script.js"></script>
</body>
</html>
