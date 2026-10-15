<?php
// Supprime un import : les conventions dont ce lot est la dernière version, ses anomalies,
// puis les structures, étudiants et encadrants qui ne sont plus rattachés à aucun stage.
require __DIR__ . '/_init.php';

use ObservaStage\Db;

$id = (int) (json_decode(file_get_contents('php://input'), true)['id'] ?? 0);
if (!Db::valeur('SELECT 1 FROM import_lot WHERE id = ?', [$id])) {
    throw new RuntimeException('Import introuvable.');
}

$pdo = Db::pdo();
$pdo->beginTransaction();
$supprimees = (int) Db::valeur('SELECT count(*) FROM stage WHERE lot_id = ?', [$id]);
$pdo->prepare('DELETE FROM import_lot WHERE id = ?')->execute([$id]); // stages et anomalies en cascade
$pdo->exec('DELETE FROM structure WHERE id NOT IN (SELECT structure_id FROM stage WHERE structure_id IS NOT NULL)');
$pdo->exec('DELETE FROM etudiant WHERE id NOT IN (SELECT etudiant_id FROM stage WHERE etudiant_id IS NOT NULL)');
$pdo->exec('DELETE FROM encadrant WHERE id NOT IN (SELECT enseignant_id FROM stage WHERE enseignant_id IS NOT NULL
                                                  UNION SELECT tuteur_id FROM stage WHERE tuteur_id IS NOT NULL)');
$pdo->commit();

repondre(['conventions_supprimees' => $supprimees]);
