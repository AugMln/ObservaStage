<?php
// Point d'entrée commun de l'API JSON.

require __DIR__ . '/../../vendor/autoload.php';

use ObservaStage\Db;

set_exception_handler(function (Throwable $e) {
    http_response_code($e instanceof RuntimeException ? 400 : 500);
    header('Content-Type: application/json; charset=utf-8');
    error_log((string) $e);
    echo json_encode(['erreur' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
});

function repondre(mixed $donnees): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
}

/** Critères de filtre reçus en GET. */
function criteres(): array
{
    return array_intersect_key($_GET, array_flip(ObservaStage\Filtres::CRITERES));
}

/** Fichier téléversé en attente de mapping (supprimé après l'import). */
function cheminTemporaire(string $jeton): string
{
    if (!preg_match('/^[a-f0-9]{32}$/', $jeton)) {
        throw new RuntimeException('Jeton d\'import invalide.');
    }
    return sys_get_temp_dir() . "/observastage_$jeton";
}
