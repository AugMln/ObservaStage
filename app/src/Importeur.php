<?php

namespace ObservaStage;

use DateTimeImmutable;
use RuntimeException;
use Throwable;

/**
 * Importe un fichier EsupStage selon un mapping validé par l'utilisatrice.
 *
 * - Seules les colonnes mappées sont lues (minimisation des données).
 * - Les valeurs sont converties (dates jj/mm/aaaa, nombres, Oui/Non) et contrôlées ;
 *   les anomalies sont enregistrées, jamais corrigées.
 * - L'import est idempotent : clé (année universitaire, N° de convention).
 */
final class Importeur
{
    private const CHAMPS_BLOQUANTS = ['annee_universitaire', 'numero_convention'];

    private int $lotId;
    private array $types = [];
    private array $obligatoires = [];
    private array $etapesConnues = [];
    private float $heuresHebdoMax;
    private array $cacheStructures = [];
    private array $cacheEtudiants = [];
    private array $cacheEncadrants = [];
    private array $anomalies = [];

    /**
     * @param array<int, ?string> $choix index de colonne => code du champ cible (null = ignorer)
     * @return array<string, mixed> bilan de l'import
     */
    public function importer(LecteurFichier $fichier, string $nomFichier, string $empreinte, array $choix, ?int $profilId): array
    {
        foreach (Mapping::catalogue() as $champ) {
            $this->types[$champ['code']] = $champ['type'];
            if ($champ['obligatoire']) {
                $this->obligatoires[] = $champ['code'];
            }
        }
        $manquants = array_diff(self::CHAMPS_BLOQUANTS, array_filter($choix));
        if ($manquants) {
            throw new RuntimeException('Champs indispensables non associés : ' . implode(', ', $manquants));
        }
        $this->etapesConnues = array_flip(array_column(Db::lignes('SELECT code_etape FROM ref_etape'), 'code_etape'));
        $this->heuresHebdoMax = (float) Db::valeur("SELECT param('heures_hebdo_max')");

        $pdo = Db::pdo();
        $pdo->beginTransaction();
        try {
            $this->lotId = (int) Db::valeur(
                'INSERT INTO import_lot (fichier, empreinte, profil_id) VALUES (?, ?, ?) RETURNING id',
                [$nomFichier, $empreinte, $profilId]
            );

            $bilan = ['lignes' => 0, 'crees' => 0, 'mis_a_jour' => 0, 'rejetes' => 0];
            $clesVues = [];
            foreach ($fichier->lignes as $n => $valeurs) {
                $numeroLigne = $n + 2; // ligne 1 = en-têtes
                $bilan['lignes']++;

                $donnees = [];
                foreach ($choix as $i => $code) {
                    if ($code !== null && $code !== '') {
                        $donnees[$code] = $this->convertir($code, $valeurs[$i] ?? '', $numeroLigne);
                    }
                }

                if (!$this->controler($donnees, $numeroLigne)) {
                    $bilan['rejetes']++;
                    continue;
                }
                $cle = $donnees['annee_universitaire'] . '|' . $donnees['numero_convention'];
                if (isset($clesVues[$cle])) {
                    $this->anomalie($numeroLigne, 'avertissement', 'numero_convention',
                        "Convention déjà présente ligne {$clesVues[$cle]} : la dernière ligne est conservée");
                }
                $clesVues[$cle] = $numeroLigne;

                $bilan[$this->enregistrer($donnees) ? 'crees' : 'mis_a_jour']++;
            }

            $this->sauverAnomalies();
            Db::pdo()->prepare(
                'UPDATE import_lot SET nb_lignes = ?, nb_crees = ?, nb_mis_a_jour = ?, nb_rejetes = ? WHERE id = ?'
            )->execute([$bilan['lignes'], $bilan['crees'], $bilan['mis_a_jour'], $bilan['rejetes'], $this->lotId]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return ['lot' => $this->lotId] + $bilan + ['anomalies' => self::resumeAnomalies($this->lotId)];
    }

    private function convertir(string $code, string $brut, int $ligne): mixed
    {
        $brut = trim($brut);
        if ($brut === '') {
            return null;
        }
        switch ($this->types[$code] ?? 'texte') {
            case 'date':
                $date = DateTimeImmutable::createFromFormat('!d/m/Y', $brut);
                if ($date === false || $date->format('d/m/Y') !== $brut) {
                    $this->anomalie($ligne, 'avertissement', $code, "Date illisible : « {$brut} »");
                    return null;
                }
                return $date->format('Y-m-d');
            case 'nombre':
                $nombre = str_replace([',', ' '], ['.', ''], $brut);
                if (!is_numeric($nombre)) {
                    $this->anomalie($ligne, 'avertissement', $code, "Nombre illisible : « {$brut} »");
                    return null;
                }
                return (float) $nombre;
            case 'booleen':
                $valeur = Texte::normaliser($brut);
                if (in_array($valeur, ['oui', 'o', 'vrai', '1'], true)) {
                    return true;
                }
                if (in_array($valeur, ['non', 'n', 'faux', '0'], true)) {
                    return false;
                }
                $this->anomalie($ligne, 'avertissement', $code, "Valeur Oui/Non inattendue : « {$brut} »");
                return null;
            default:
                return $brut;
        }
    }

    /** @return bool false si la ligne doit être rejetée */
    private function controler(array $d, int $ligne): bool
    {
        foreach (self::CHAMPS_BLOQUANTS as $code) {
            if (($d[$code] ?? null) === null) {
                $this->anomalie($ligne, 'bloquant', $code, 'Champ indispensable vide : ligne non importée');
                return false;
            }
        }
        foreach ($this->obligatoires as $code) {
            if (!in_array($code, self::CHAMPS_BLOQUANTS, true) && array_key_exists($code, $d) && $d[$code] === null) {
                $this->anomalie($ligne, 'avertissement', $code, 'Champ obligatoire vide');
            }
        }
        if (isset($d['date_debut'], $d['date_fin']) && $d['date_fin'] < $d['date_debut']) {
            $this->anomalie($ligne, 'avertissement', 'date_fin', 'Date de fin antérieure à la date de début');
        }
        if (array_key_exists('heures_hebdo', $d)) {
            $h = $d['heures_hebdo'];
            if ($h === null) {
                $this->anomalie($ligne, 'avertissement', 'heures_hebdo', 'Heures hebdomadaires manquantes : exclu des calculs en heures');
            } elseif ($h <= 0 || $h > $this->heuresHebdoMax) {
                $this->anomalie($ligne, 'avertissement', 'heures_hebdo',
                    "Heures hebdomadaires aberrantes ({$h} h) : exclu des calculs en heures");
            }
        }
        if (array_key_exists('duree_heures', $d) && $d['duree_heures'] !== null && $d['duree_heures'] <= 0) {
            $this->anomalie($ligne, 'avertissement', 'duree_heures', 'Durée déclarée nulle : exclu des calculs en heures');
        }
        if (isset($d['code_etape']) && !isset($this->etapesConnues[$d['code_etape']])) {
            $this->anomalie($ligne, 'avertissement', 'code_etape',
                "Code étape inconnu du référentiel : « {$d['code_etape']} »");
        }
        return true;
    }

    /** @return bool true si la convention a été créée, false si elle a été mise à jour */
    private function enregistrer(array $d): bool
    {
        $stmt = Db::pdo()->prepare(
            'INSERT INTO stage (annee_universitaire, numero_convention, etudiant_id, structure_id, enseignant_id, tuteur_id, code_etape,
                service_nom, service_pays, service_code_postal, service_commune, date_debut, date_fin,
                duree_heures, heures_hebdo, thematique, sujet, fonctions_taches, type_convention,
                commentaire_duree_travail, validation_convention, validation_pedagogique, avenant,
                interruption, date_modification, lot_id)
             VALUES (:annee_universitaire, :numero_convention, :etudiant_id, :structure_id, :enseignant_id, :tuteur_id, :code_etape,
                :service_nom, :service_pays, :service_code_postal, :service_commune, :date_debut, :date_fin,
                :duree_heures, :heures_hebdo, :thematique, :sujet, :fonctions_taches, :type_convention,
                :commentaire_duree_travail, :validation_convention, :validation_pedagogique, :avenant,
                :interruption, :date_modification, :lot_id)
             ON CONFLICT (annee_universitaire, numero_convention) DO UPDATE SET
                etudiant_id = EXCLUDED.etudiant_id, structure_id = EXCLUDED.structure_id,
                enseignant_id = EXCLUDED.enseignant_id, tuteur_id = EXCLUDED.tuteur_id,
                code_etape = EXCLUDED.code_etape, service_nom = EXCLUDED.service_nom,
                service_pays = EXCLUDED.service_pays, service_code_postal = EXCLUDED.service_code_postal,
                service_commune = EXCLUDED.service_commune, date_debut = EXCLUDED.date_debut,
                date_fin = EXCLUDED.date_fin, duree_heures = EXCLUDED.duree_heures,
                heures_hebdo = EXCLUDED.heures_hebdo, thematique = EXCLUDED.thematique,
                sujet = EXCLUDED.sujet, fonctions_taches = EXCLUDED.fonctions_taches,
                type_convention = EXCLUDED.type_convention,
                commentaire_duree_travail = EXCLUDED.commentaire_duree_travail,
                validation_convention = EXCLUDED.validation_convention,
                validation_pedagogique = EXCLUDED.validation_pedagogique, avenant = EXCLUDED.avenant,
                interruption = EXCLUDED.interruption, date_modification = EXCLUDED.date_modification,
                lot_id = EXCLUDED.lot_id
             RETURNING (xmax = 0) AS cree'
        );
        $colonnes = ['annee_universitaire', 'numero_convention', 'code_etape', 'service_nom', 'service_pays',
            'service_code_postal', 'service_commune', 'date_debut', 'date_fin', 'duree_heures', 'heures_hebdo',
            'thematique', 'sujet', 'fonctions_taches', 'type_convention', 'commentaire_duree_travail',
            'validation_convention', 'validation_pedagogique', 'avenant', 'interruption', 'date_modification'];
        $params = [];
        foreach ($colonnes as $colonne) {
            $valeur = $d[$colonne] ?? null;
            $params[$colonne] = is_bool($valeur) ? ($valeur ? 'true' : 'false') : $valeur;
        }
        $params['etudiant_id'] = $this->etudiant($d);
        $params['enseignant_id'] = $this->encadrant('enseignant', $d['enseignant_nom'] ?? null, $d['enseignant_email'] ?? null);
        $params['tuteur_id'] = $this->encadrant('tuteur', $d['tuteur_nom'] ?? null, $d['tuteur_email'] ?? null);
        $params['structure_id'] = $this->structure($d);
        $params['lot_id'] = $this->lotId;
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /** Étudiant rapproché par son numéro ; nom, prénom et e-mail mis à jour s'ils sont fournis. */
    private function etudiant(array $d): ?int
    {
        $numero = $d['numero_etudiant'] ?? null;
        if ($numero === null) {
            return null;
        }
        return $this->cacheEtudiants[$numero] ??= (int) Db::valeur(
            'INSERT INTO etudiant (numero, nom, prenom, email) VALUES (?, ?, ?, ?)
             ON CONFLICT (numero) DO UPDATE SET nom = coalesce(EXCLUDED.nom, etudiant.nom),
                prenom = coalesce(EXCLUDED.prenom, etudiant.prenom), email = coalesce(EXCLUDED.email, etudiant.email)
             RETURNING id',
            [$numero, $d['etudiant_nom'] ?? null, $d['etudiant_prenom'] ?? null, $d['etudiant_email'] ?? null]
        );
    }

    /** Encadrant rapproché par e-mail, sinon par nom normalisé. */
    private function encadrant(string $role, ?string $nom, ?string $email): ?int
    {
        $email = $email !== null ? mb_strtolower($email) : null;
        $cle = $email ?? ($nom !== null ? 'NOM:' . Texte::normaliser($nom) : null);
        if ($cle === null) {
            return null;
        }
        return $this->cacheEncadrants["$role|$cle"] ??= (int) Db::valeur(
            'INSERT INTO encadrant (role, cle_rapprochement, nom, email) VALUES (?, ?, ?, ?)
             ON CONFLICT (role, cle_rapprochement) DO UPDATE SET nom = coalesce(EXCLUDED.nom, encadrant.nom),
                email = coalesce(EXCLUDED.email, encadrant.email)
             RETURNING id',
            [$role, $cle, $nom, $email]
        );
    }

    /** Rapprochement des structures : SIRET si présent, sinon nom normalisé + pays + code postal. */
    private function structure(array $d): ?int
    {
        $siret = preg_replace('/\D/', '', (string) ($d['structure_siret'] ?? ''));
        $nom = Texte::normaliser($d['structure_nom'] ?? '');
        if ($siret === '' && $nom === '') {
            return null;
        }
        $cle = $siret !== '' ? "SIRET:$siret"
            : 'NOM:' . $nom . '|' . Texte::normaliser($d['structure_pays'] ?? '') . '|' . ($d['structure_code_postal'] ?? '');

        return $this->cacheStructures[$cle] ??= (int) Db::valeur(
            'INSERT INTO structure (cle_rapprochement, siret, nom, pays, code_postal, commune, statut_juridique,
                type_structure, activite, effectif)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON CONFLICT (cle_rapprochement) DO UPDATE SET nom = EXCLUDED.nom, pays = EXCLUDED.pays,
                code_postal = EXCLUDED.code_postal, commune = EXCLUDED.commune,
                statut_juridique = EXCLUDED.statut_juridique, type_structure = EXCLUDED.type_structure,
                activite = EXCLUDED.activite, effectif = EXCLUDED.effectif
             RETURNING id',
            [$cle, $siret ?: null, $d['structure_nom'] ?? null, $d['structure_pays'] ?? null,
             $d['structure_code_postal'] ?? null, $d['structure_commune'] ?? null,
             $d['structure_statut_juridique'] ?? null, $d['structure_type'] ?? null,
             $d['structure_activite'] ?? null, $d['structure_effectif'] ?? null]
        );
    }

    private function anomalie(int $ligne, string $gravite, ?string $champ, string $message): void
    {
        $this->anomalies[] = [$ligne, $gravite, $champ, $message];
    }

    private function sauverAnomalies(): void
    {
        $stmt = Db::pdo()->prepare(
            'INSERT INTO import_anomalie (lot_id, ligne, gravite, champ, message) VALUES (?, ?, ?, ?, ?)'
        );
        foreach ($this->anomalies as [$ligne, $gravite, $champ, $message]) {
            $stmt->execute([$this->lotId, $ligne, $gravite, $champ, $message]);
        }
    }

    /** Anomalies regroupées par type (le message sans la valeur citée). */
    public static function resumeAnomalies(int $lotId): array
    {
        return Db::lignes(
            "SELECT gravite, champ, regexp_replace(message, ' ?(« .* »|\\(.*\\))', '', 'g') AS message,
                    count(*) AS nombre, array_to_string((array_agg(ligne ORDER BY ligne))[1:8], ', ') AS exemples_lignes
             FROM import_anomalie WHERE lot_id = ?
             GROUP BY 1, 2, 3 ORDER BY gravite, nombre DESC",
            [$lotId]
        );
    }
}
