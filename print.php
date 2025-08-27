<?php
$code = $_GET['code'] ?? '';
if(!$code || !preg_match('/^\d{8}$/', $code) || !file_exists("$code.save")){
    echo 'Projet introuvable.';
    exit;
}
$content = file_get_contents("$code.save");
$data = json_decode($content, true);
if(!$data){
    echo 'Données invalides.';
    exit;
}
$daysNames = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
$pharmacists = $data['pharmacists'] ?? [
    'A'=>['name'=>'Pharmacien A','color'=>'#ff6666'],
    'B'=>['name'=>'Pharmacien B','color'=>'#6666ff']
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Planning imprimable</title>
<style>
@page { size: A4 landscape; margin: 10mm; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { border: 1px solid #ccc; padding: 4px; text-align: center; }
h2 { text-align: center; margin-top: 0; }
.segment { margin-bottom: 4px; }
.label { padding: 2px 4px; border-radius: 4px; color: #fff; }
</style>
</head>
<body onload="window.print()">
<?php for($week=1; $week<=2; $week++): ?>
    <h2>Planning semaine <?php echo $week; ?></h2>
    <table>
        <thead>
            <tr><th>Jour</th><th>Tranches</th></tr>
        </thead>
        <tbody>
        <?php for($day=0; $day<7; $day++): ?>
            <tr>
                <td><?php echo $daysNames[$day]; ?></td>
                <td>
                <?php
                $segments = $data['pharm_sched'][$day] ?? [];
                foreach($segments as $seg){
                    $start = htmlspecialchars($seg['start'] ?? '');
                    $end = htmlspecialchars($seg['end'] ?? '');
                    $ph = $seg[$week==1 ? 'ph1' : 'ph2'] ?? '';
                    $name = htmlspecialchars($pharmacists[$ph]['name'] ?? $ph);
                    $color = htmlspecialchars($pharmacists[$ph]['color'] ?? '#000');
                    if($start && $end){
                        echo '<div class="segment">'
                            .'<span>'.$start.' - '.$end.' </span>'
                            .'<span class="label" style="background-color:'.$color.';">'.$name.'</span>'
                            .'</div>';
                    }
                }
                ?>
                </td>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
<?php endfor; ?>
<footer style="text-align:center;font-size:12px;margin-top:20px;">
    Fait avec amour par Meditrust pour les pharmacies
    <a href="https://meditrust.io/contacter-nous/" target="_blank">
        <img src="https://meditrust.io/wp-content/uploads/2023/09/meditrust-logo-green.png" alt="Logo Meditrust" style="height:20px;vertical-align:middle;margin-left:4px;">
    </a>
</footer>
</body>
</html>
