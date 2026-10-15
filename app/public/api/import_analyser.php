<?php
// Étape 1 de l'import : lecture du fichier et proposition de mapping.
require __DIR__ . '/_init.php';

use ObservaStage\{LecteurFichier, Mapping};

$fichier = $_FILES['fichier'] ?? null;
if (!$fichier || $fichier['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Aucun fichier reçu.');
}

// Minimisation : les fichiers analysés mais jamais importés sont supprimés au bout d'une heure.
foreach (glob(sys_get_temp_dir() . '/observastage_*') as $ancien) {
    if (filemtime($ancien) < time() - 3600) {
        unlink($ancien);
    }
}

$lecteur = new LecteurFichier($fichier['tmp_name'], $fichier['name']);

$jeton = bin2hex(random_bytes(16));
$extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
move_uploaded_file($fichier['tmp_name'], cheminTemporaire($jeton) . ".$extension");

$suggestion = Mapping::suggerer($lecteur);
repondre([
    'jeton' => $jeton,
    'fichier' => $fichier['name'],
    'lignes' => count($lecteur->lignes),
    'avertissements' => $lecteur->avertissements,
    'profil' => $suggestion['profil'],
    'colonnes' => $suggestion['colonnes'],
    'champs' => Mapping::catalogue(),
]);
