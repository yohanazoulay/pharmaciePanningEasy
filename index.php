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
    $invalid = false;
    foreach($data['pharm_sched'] as $d=>&$segs){
        $opens = $data['schedule'][$d] ?? [];
        foreach($segs as $i=>$seg){
            $s = $seg['start'] ?? '';
            $e = $seg['end'] ?? '';
            $inside = false;
            if($s && $e){
                foreach($opens as $o){
                    if($s >= ($o['start'] ?? '') && $e <= ($o['end'] ?? '')){ $inside = true; break; }
                }
            }
            if(!$inside){ unset($segs[$i]); $invalid = true; }
        }
        $segs = array_values($segs);
    }
    if($invalid){
        $error = 'Tranches pharmaciens hors horaires d\'ouverture ignorées.';
    }
    if (file_put_contents("$code.save", json_encode($data)) !== false) {
        $message = 'Projet sauvegardé. Pensez à noter votre code d\'accès pour réouvrir votre travail.';
    } else {
        $error = 'Erreur lors de la sauvegarde.';
    }
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
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
</head>
<body>
<button id="helpToggle" class="help-button bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded fixed top-4 right-4">Aide</button>
<div class="container mx-auto p-4">
<?php if(!$code): ?>
    <h1>Planning Pharmacie</h1>
    <?php if($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <div class="options">
        <a class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" href="?new=1">Nouveau projet</a>
        <form method="get" class="load-form">
            <input type="text" name="code" placeholder="Code projet" minlength="8" maxlength="8" required>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" type="submit">Charger</button>
        </form>
    </div>
<?php else: ?>
    <h1>Projet #<?php echo htmlspecialchars($code); ?></h1>
    <?php if($message): ?><p class="message"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php $pharmacists = $data['pharmacists']; ?>
    <form method="post" id="scheduleForm" class="space-y-4">
        <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
        <?php $daysNames = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche']; ?>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="section section-options bg-white rounded shadow p-4">
                <h2 class="text-xl font-semibold mb-2">Section 0 : Options</h2>
                <div class="pharmacist-option">
                    <label>Nom pharmacien A <input type="text" name="pharmacists[A][name]" value="<?php echo htmlspecialchars($pharmacists['A']['name']); ?>"></label>
                    <label>Couleur <input type="color" name="pharmacists[A][color]" value="<?php echo htmlspecialchars($pharmacists['A']['color']); ?>"></label>
                </div>
                <div class="pharmacist-option">
                    <label>Nom pharmacien B <input type="text" name="pharmacists[B][name]" value="<?php echo htmlspecialchars($pharmacists['B']['name']); ?>"></label>
                    <label>Couleur <input type="color" name="pharmacists[B][color]" value="<?php echo htmlspecialchars($pharmacists['B']['color']); ?>"></label>
                </div>
            </div>
            <div class="section summary bg-white rounded shadow p-4">
                <h2 class="text-xl font-semibold mb-2">Section 3 : Récapitulatif</h2>
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
                <p class="open-hours text-center mt-2">Heures d'ouverture (Lun-Sam): <span id="openHours">0</span></p>
            </div>
        </div>
        <div class="section section-openings bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-2">Section 1 : Horaires d'ouverture</h2>
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
                        echo '<td><button type="button" class="add-segment" data-day="'.$day.'">+ Ajouter tranche</button></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="section section-pharmacists bg-white rounded shadow p-4">
            <h2 class="text-xl font-semibold mb-2">Section 2 : Planning des pharmaciens</h2>
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
                    echo '<button type="button" class="add-pharm" data-day="'.$day.'">+ Ajouter tranche</button>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
            </table>
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" type="submit" name="save" id="saveBtn">Sauvegarder</button>
    </form>
    <p class="code-info">Code du projet: <strong><?php echo htmlspecialchars($code); ?></strong></p>
    <p class="code-info"><a class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded inline-block" target="_blank" href="print.php?code=<?php echo htmlspecialchars($code); ?>">Planning imprimable (PDF)</a></p>
<?php endif; ?>
</div>
<footer class="text-center text-sm mt-4">
    Fait avec ❤️ par Meditrust pour les pharmacies
    <a href="https://meditrust.io/contacter-nous/" target="_blank">
        <img src="https://meditrust.io/wp-content/uploads/2023/09/meditrust-logo-green.png" alt="Logo Meditrust" class="inline-block h-6 align-middle ml-2">
    </a>
</footer>
<div id="toast" data-message="<?php echo htmlspecialchars($message); ?>" data-error="<?php echo htmlspecialchars($error); ?>"></div>
<div id="helpOverlay" class="help-overlay"></div>
<div id="helpSidebar" class="help-sidebar">
    <button id="closeHelp" class="close-help">&times;</button>
    <h2 class="text-xl font-semibold mb-4">Comment utiliser l'outil</h2>
    <ol class="list-decimal list-inside space-y-2">
        <li>Définir les noms et couleurs des pharmaciens.</li>
        <li>Renseigner les horaires d'ouverture.</li>
        <li>Ajouter le planning des pharmaciens.</li>
        <li>Sauvegarder et noter le code du projet.</li>
    </ol>
</div>
<script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
</body>
</html>
