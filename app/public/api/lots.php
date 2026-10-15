<?php
require __DIR__ . '/_init.php';

use ObservaStage\{Db, Importeur};

if (isset($_GET['id'])) {
    repondre(Importeur::resumeAnomalies((int) $_GET['id']));
} else {
    repondre(Db::lignes(
        "SELECT l.id, l.fichier, to_char(l.importe_le, 'DD/MM/YYYY HH24:MI') AS date, p.nom AS profil,
                l.nb_lignes, l.nb_crees, l.nb_mis_a_jour, l.nb_rejetes,
                (SELECT count(*) FROM import_anomalie a WHERE a.lot_id = l.id) AS nb_anomalies
         FROM import_lot l LEFT JOIN profil_mapping p ON p.id = l.profil_id
         ORDER BY l.id DESC"
    ));
}
