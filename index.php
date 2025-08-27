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
$data = [
    'schedule'=>[],
    'pharm_sched'=>[],
    'pharmacists'=>[
        'A'=>['name'=>'Pharmacien A','color'=>'#ff6666'],
        'B'=>['name'=>'Pharmacien B','color'=>'#6666ff']
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $code = preg_replace('/\D/', '', $_POST['code']);
    $data['schedule'] = $_POST['schedule'] ?? [];
    $data['pharm_sched'] = $_POST['pharm_sched'] ?? [];
    $data['pharmacists'] = $_POST['pharmacists'] ?? $data['pharmacists'];
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
        if(!isset($data['pharm_sched'])){
            $data['pharm_sched'] = [];
        }
        if(isset($data['schedule'][0][0]['ph1'])){
            foreach($data['schedule'] as $d=>&$segs){
                foreach($segs as $i=>$seg){
                    $data['pharm_sched'][$d][$i] = [
                        'start'=>$seg['start'] ?? '',
                        'end'=>$seg['end'] ?? '',
                        'ph1'=>$seg['ph1'] ?? 'A',
                        'ph2'=>$seg['ph2'] ?? 'A'
                    ];
                    unset($segs[$i]['ph1'],$segs[$i]['ph2']);
                }
            }
        }
        if(!isset($data['pharmacists'])){
            $data['pharmacists'] = [
                'A'=>['name'=>'Pharmacien A','color'=>'#ff6666'],
                'B'=>['name'=>'Pharmacien B','color'=>'#6666ff']
            ];
        }
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
<link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
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
    <?php $pharmacists = $data['pharmacists']; ?>
    <div class="columns">
        <div class="planner">
            <form method="post" id="scheduleForm">
                <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
                <?php $daysNames = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche']; ?>
                <div class="section section-options">
                    <h2>Section 0 : Options</h2>
                    <div class="pharmacist-option">
                        <label>Nom pharmacien A <input type="text" name="pharmacists[A][name]" value="<?php echo htmlspecialchars($pharmacists['A']['name']); ?>"></label>
                        <label>Couleur <input type="color" name="pharmacists[A][color]" value="<?php echo htmlspecialchars($pharmacists['A']['color']); ?>"></label>
                    </div>
                    <div class="pharmacist-option">
                        <label>Nom pharmacien B <input type="text" name="pharmacists[B][name]" value="<?php echo htmlspecialchars($pharmacists['B']['name']); ?>"></label>
                        <label>Couleur <input type="color" name="pharmacists[B][color]" value="<?php echo htmlspecialchars($pharmacists['B']['color']); ?>"></label>
                    </div>
                </div>
                <div class="section section-openings">
                    <h2>Section 1 : Horaires d'ouverture</h2>
                    <table class="openings">
                        <thead>
                            <tr>
                                <th>Jour</th>
                                <th>Tranches d'ouverture</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            for($day=0;$day<7;$day++){
                                $segments = $data['schedule'][$day] ?? [];
                                echo '<tr>';
                                echo '<td>'.$daysNames[$day].'</td>';
                                echo '<td class="segments-open" data-day="'.$day.'">';
                                foreach($segments as $i=>$seg){
                                    $start = htmlspecialchars($seg['start'] ?? '');
                                    $end = htmlspecialchars($seg['end'] ?? '');
                                    echo '<div class="segment" data-index="'.$i.'">'
                                        .'<input type="time" name="schedule['.$day.']['.$i.'][start]" value="'.$start.'">'
                                        .'<input type="time" name="schedule['.$day.']['.$i.'][end]" value="'.$end.'">'
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
                </div>
                <div class="section section-pharmacists">
                    <h2>Section 2 : Planning des pharmaciens</h2>
                    <table class="planning">
                        <thead>
                            <tr>
                                <th>Jour</th>
                                <th>Planning</th>
                            </tr>
                        </thead>
                    <tbody>
                            <?php
                            for($day=0;$day<7;$day++){
                                $segments = $data['pharm_sched'][$day] ?? [];
                                echo '<tr>';
                                echo '<td>'.$daysNames[$day].'</td>';
                                echo '<td class="segments-pharm" data-day="'.$day.'">';
                                foreach($segments as $i=>$seg){
                                    $start = htmlspecialchars($seg['start'] ?? '');
                                    $end = htmlspecialchars($seg['end'] ?? '');
                                    $ph1 = $seg['ph1'] ?? 'A';
                                    $ph2 = $seg['ph2'] ?? 'A';
                                    echo '<div class="segment" data-index="'.$i.'">'
                                        .'<input type="time" name="pharm_sched['.$day.']['.$i.'][start]" value="'.$start.'">'
                                        .'<input type="time" name="pharm_sched['.$day.']['.$i.'][end]" value="'.$end.'">'
                                        .'<select name="pharm_sched['.$day.']['.$i.'][ph1]" class="ph-select" data-slot="S1">'
                                            .'<option value="A"'.($ph1=='A'?' selected':'').' style="background-color:'.$pharmacists['A']['color'].'">'.htmlspecialchars($pharmacists['A']['name']).' S1</option>'
                                            .'<option value="B"'.($ph1=='B'?' selected':'').' style="background-color:'.$pharmacists['B']['color'].'">'.htmlspecialchars($pharmacists['B']['name']).' S1</option>'
                                        .'</select>'
                                        .'<select name="pharm_sched['.$day.']['.$i.'][ph2]" class="ph-select" data-slot="S2">'
                                            .'<option value="A"'.($ph2=='A'?' selected':'').' style="background-color:'.$pharmacists['A']['color'].'">'.htmlspecialchars($pharmacists['A']['name']).' S2</option>'
                                            .'<option value="B"'.($ph2=='B'?' selected':'').' style="background-color:'.$pharmacists['B']['color'].'">'.htmlspecialchars($pharmacists['B']['name']).' S2</option>'
                                        .'</select>'
                                        .'<button type="button" class="remove-pharm">&times;</button>'
                                        .'</div>';
                                }
                                echo '<button type="button" class="add-pharm" data-day="'.$day.'">Ajouter tranche</button>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <button class="btn" type="submit" name="save" id="saveBtn">Sauvegarder</button>
            </form>
        </div>
        <div class="summary">
            <h2>Section 3 : Récapitulatif</h2>
            <table class="recap">
                <thead>
                    <tr>
                        <th></th><th>Semaine 1</th><th>Semaine 2</th><th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td id="labelA" style="color: <?php echo htmlspecialchars($pharmacists['A']['color']); ?>"><?php echo htmlspecialchars($pharmacists['A']['name']); ?></td><td id="w1A">0</td><td id="w2A">0</td><td id="totA">0</td></tr>
                    <tr><td id="labelB" style="color: <?php echo htmlspecialchars($pharmacists['B']['color']); ?>"><?php echo htmlspecialchars($pharmacists['B']['name']); ?></td><td id="w1B">0</td><td id="w2B">0</td><td id="totB">0</td></tr>
                </tbody>
            </table>
            <p class="open-hours">Heures d'ouverture (Lun-Sam): <span id="openHours">0</span></p>
        </div>
    </div>
    <p class="code-info">Code du projet: <strong><?php echo htmlspecialchars($code); ?></strong></p>
<?php endif; ?>
</div>
<script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
</body>
</html>
