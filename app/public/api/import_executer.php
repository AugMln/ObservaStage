<?php
// Étape 2 de l'import : enregistrement du profil de mapping, import, rapport d'anomalies.
require __DIR__ . '/_init.php';

use ObservaStage\{Importeur, LecteurFichier, Mapping};

$requete = json_decode(file_get_contents('php://input'), true) ?? [];
$base = cheminTemporaire($requete['jeton'] ?? '');
$chemins = glob("$base.*");
if (!$chemins) {
    throw new RuntimeException('Fichier expiré : merci de le téléverser à nouveau.');
}
$chemin = $chemins[0];
$nomFichier = $requete['fichier'] ?? basename($chemin);

try {
    $lecteur = new LecteurFichier($chemin, $chemin);
    $choix = array_map(fn ($c) => $c ?: null, $requete['mapping'] ?? []);
    $profilId = Mapping::enregistrerProfil(trim($requete['profil'] ?? '') ?: "Profil du " . date('d/m/Y'), $lecteur->entetes, $choix);
    $bilan = (new Importeur())->importer($lecteur, $nomFichier, hash_file('sha256', $chemin), $choix, $profilId);
} finally {
    unlink($chemin); // Minimisation : le fichier n'est pas conservé après l'import.
}

repondre($bilan);
