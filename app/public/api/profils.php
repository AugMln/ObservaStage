<?php
// Profils de mapping : liste (GET) et suppression (POST {id}).
require __DIR__ . '/_init.php';

use ObservaStage\Db;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) (json_decode(file_get_contents('php://input'), true)['id'] ?? 0);
    Db::pdo()->prepare('DELETE FROM profil_mapping WHERE id = ?')->execute([$id]);
    repondre(['ok' => true]);
} else {
    repondre(Db::lignes(
        "SELECT p.id, p.nom, to_char(p.cree_le, 'DD/MM/YYYY HH24:MI') AS date,
                count(r.*) FILTER (WHERE r.champ_cible IS NOT NULL) AS colonnes_importees,
                count(r.*) FILTER (WHERE r.champ_cible IS NULL) AS colonnes_ignorees,
                (SELECT count(*) FROM import_lot l WHERE l.profil_id = p.id) AS imports
         FROM profil_mapping p LEFT JOIN regle_mapping r ON r.profil_id = p.id
         GROUP BY p.id ORDER BY p.cree_le DESC"
    ));
}
