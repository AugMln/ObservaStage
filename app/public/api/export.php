<?php
require __DIR__ . '/_init.php';

use ObservaStage\{Export, Indicateurs, Recherche};

$format = ($_GET['format'] ?? 'csv') === 'xls' ? 'xls' : 'csv';

if (($_GET['objet'] ?? 'stages') === 'indicateurs') {
    Export::envoyer(Indicateurs::versTableau(Indicateurs::calculer(criteres())), 'observastage_indicateurs', $format);
} else {
    $lignes = Recherche::tout(criteres());
    array_unshift($lignes, array_values(Recherche::COLONNES));
    Export::envoyer($lignes, 'observastage_stages', $format);
}
