<?php
// Compare la lecture d'un fichier EsupStage original et de ses versions réenregistrées par Excel.
require '/var/www/vendor/autoload.php';
use ObservaStage\{LecteurFichier, Texte};

$dir = '/tmp/robustesse'; // fichiers copiés par docker cp (voir README)
$ref = new LecteurFichier("$dir/original.xls", 'original.xls');
$idxRef = [];
foreach ($ref->entetes as $i => $e) $idxRef[Texte::normaliser($e)] = $i;
// Clé de ligne : N° de convention
$cle = fn ($l, $idx) => $l[$idx[Texte::normaliser('N° de la convention')]];
$refParCle = [];
foreach ($ref->lignes as $l) $refParCle[$cle($l, $idxRef)] = $l;

foreach (['resauve.xls', 'resauve.xlsx', 'resauve_csv_excel.csv', 'resauve_csv_utf8.csv'] as $f) {
    echo "\n=== $f ===\n";
    try { $v = new LecteurFichier("$dir/$f", $f); } catch (Throwable $e) { echo "ERREUR LECTURE : {$e->getMessage()}\n"; continue; }
    $idx = [];
    foreach ($v->entetes as $i => $e) $idx[Texte::normaliser($e)] = $i;
    $manquantes = array_diff(array_keys($idxRef), array_keys($idx));
    echo count($v->lignes) . " lignes, " . count($v->entetes) . " colonnes ; en-têtes non retrouvés : " . (count($manquantes) ? implode(' | ', array_slice($manquantes, 0, 6)) : 'aucun') . "\n";
    if (!isset($idx[Texte::normaliser('N° de la convention')])) { echo "N° de convention introuvable\n"; continue; }
    $diffs = [];
    foreach ($v->lignes as $l) {
        $r = $refParCle[$cle($l, $idx)] ?? null;
        if (!$r) { $diffs['(ligne inconnue)'][] = $cle($l, $idx); continue; }
        foreach ($idxRef as $n => $i) {
            if (!isset($idx[$n])) continue;
            $a = $r[$i]; $b = $l[$idx[$n]] ?? '';
            if ($a !== $b) $diffs[$ref->entetes[$i]][] = mb_strimwidth($a, 0, 30, '…') . ' → ' . mb_strimwidth($b, 0, 30, '…');
        }
    }
    if (!$diffs) { echo "AUCUNE DIFFÉRENCE\n"; continue; }
    foreach ($diffs as $col => $ex) printf("  %-36s %4d diff.  ex : %s\n", mb_strimwidth($col, 0, 36), count($ex), implode(' ; ', array_slice(array_unique($ex), 0, 2)));
}
