<?php
require __DIR__ . '/_init.php';

repondre(ObservaStage\Recherche::rechercher(criteres(), max(1, (int) ($_GET['page'] ?? 1))));
