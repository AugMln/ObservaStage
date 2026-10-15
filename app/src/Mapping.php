<?php

namespace ObservaStage;

/**
 * Mapping configurable : associe les colonnes d'un fichier aux champs cibles.
 *
 * 1. Si la signature du fichier (ensemble des en-têtes normalisés) correspond à un profil
 *    enregistré, le profil est appliqué tel quel.
 * 2. Sinon, chaque colonne est comparée aux alias du catalogue de champs :
 *    correspondance exacte (confiance « sûre »), puis similarité (« à vérifier »).
 * 3. Les colonnes restantes sont proposées en « ignorer » : elles ne seront pas stockées.
 */
final class Mapping
{
    private const SEUIL_SIMILARITE = 0.78;

    /** En-têtes dont les valeurs d'exemple ne sont jamais affichées. */
    private const MOTS_PERSONNELS = ['nom', 'prenom', 'mail', 'telephone', 'adresse', 'signataire', 'tuteur', 'enseignant', 'etudiant'];

    /** @return list<array<string, mixed>> */
    public static function catalogue(): array
    {
        return Db::lignes('SELECT code, libelle, type, obligatoire, alias FROM champ_cible ORDER BY ordre');
    }

    /** @param list<string> $entetes */
    public static function signature(array $entetes): string
    {
        $normalises = array_map([Texte::class, 'normaliser'], $entetes);
        sort($normalises);
        return hash('sha256', implode('|', $normalises));
    }

    /**
     * @return array{profil: ?array, colonnes: list<array<string, mixed>>}
     */
    public static function suggerer(LecteurFichier $fichier): array
    {
        $entetes = $fichier->entetes;
        $profil = Db::ligne('SELECT id, nom, cree_le FROM profil_mapping WHERE signature = ?', [self::signature($entetes)]);

        $suggestions = $profil ? self::depuisProfil((int) $profil['id'], $entetes) : self::depuisCatalogue($entetes);

        $colonnes = [];
        foreach ($entetes as $i => $entete) {
            $suggestion = $suggestions[$i] ?? ['champ' => null, 'confiance' => 'ignoree'];
            $colonnes[] = [
                'index' => $i,
                'source' => $entete,
                'exemples' => self::estPersonnelle($entete) ? [] : $fichier->exemples($i),
                'masquee' => self::estPersonnelle($entete),
                'champ' => $suggestion['champ'],
                'confiance' => $profil && $suggestion['champ'] ? 'profil' : $suggestion['confiance'],
            ];
        }
        return ['profil' => $profil, 'colonnes' => $colonnes];
    }

    /**
     * Les règles sont stockées par intitulé : on les replace dans l'ordre des colonnes du fichier.
     * @param list<string> $entetes
     * @return array<int, array{champ: ?string, confiance: string}>
     */
    private static function depuisProfil(int $profilId, array $entetes): array
    {
        $regles = Db::lignes('SELECT colonne_source, champ_cible FROM regle_mapping WHERE profil_id = ?', [$profilId]);
        $parSource = array_column($regles, 'champ_cible', 'colonne_source');
        return array_map(
            fn ($entete) => ['champ' => $parSource[$entete] ?? null, 'confiance' => 'profil'],
            $entetes
        );
    }

    /**
     * @param list<string> $entetes
     * @return array<int, array{champ: ?string, confiance: string}>
     */
    private static function depuisCatalogue(array $entetes): array
    {
        $catalogue = self::catalogue();
        $normalises = array_map([Texte::class, 'normaliser'], $entetes);
        $resultat = [];
        $pris = [];

        // 0. Colonnes déjà rencontrées : on reprend la décision du profil le plus récent.
        $connues = [];
        foreach (Db::lignes('SELECT r.colonne_source, r.champ_cible FROM regle_mapping r
                             JOIN profil_mapping p ON p.id = r.profil_id ORDER BY p.cree_le') as $regle) {
            $connues[Texte::normaliser($regle['colonne_source'])] = $regle['champ_cible'];
        }
        foreach ($normalises as $i => $entete) {
            if (array_key_exists($entete, $connues) && !isset($pris[$connues[$entete]])) {
                $resultat[$i] = ['champ' => $connues[$entete], 'confiance' => $connues[$entete] ? 'sure' : 'connue'];
                if ($connues[$entete]) {
                    $pris[$connues[$entete]] = true;
                }
            }
        }

        // 1. Correspondances exactes avec un alias.
        foreach ($normalises as $i => $entete) {
            if (isset($resultat[$i])) {
                continue;
            }
            foreach ($catalogue as $champ) {
                $alias = array_map([Texte::class, 'normaliser'], self::tableauPg($champ['alias']));
                if (in_array($entete, $alias, true) && !isset($pris[$champ['code']])) {
                    $resultat[$i] = ['champ' => $champ['code'], 'confiance' => 'sure'];
                    $pris[$champ['code']] = true;
                    break;
                }
            }
        }

        // 2. Similarité pour les champs encore libres : meilleurs couples d'abord.
        $candidats = [];
        foreach ($normalises as $i => $entete) {
            if (isset($resultat[$i])) {
                continue;
            }
            foreach ($catalogue as $champ) {
                if (isset($pris[$champ['code']])) {
                    continue;
                }
                $score = 0.0;
                foreach (self::tableauPg($champ['alias']) as $alias) {
                    $score = max($score, Texte::similarite($entete, Texte::normaliser($alias)));
                }
                if ($score >= self::SEUIL_SIMILARITE) {
                    $candidats[] = [$score, $i, $champ['code']];
                }
            }
        }
        rsort($candidats);
        foreach ($candidats as [$score, $i, $code]) {
            if (!isset($resultat[$i]) && !isset($pris[$code])) {
                $resultat[$i] = ['champ' => $code, 'confiance' => 'a_verifier'];
                $pris[$code] = true;
            }
        }

        return $resultat;
    }

    /**
     * Enregistre (ou remplace) le profil correspondant à ces en-têtes.
     * @param list<string> $entetes
     * @param array<int, ?string> $choix index de colonne => code du champ cible (null = ignorer)
     */
    public static function enregistrerProfil(string $nom, array $entetes, array $choix): int
    {
        $pdo = Db::pdo();
        $profilId = (int) Db::valeur(
            'INSERT INTO profil_mapping (nom, signature) VALUES (?, ?)
             ON CONFLICT (signature) DO UPDATE SET nom = EXCLUDED.nom RETURNING id',
            [$nom, self::signature($entetes)]
        );
        $pdo->prepare('DELETE FROM regle_mapping WHERE profil_id = ?')->execute([$profilId]);
        $insertion = $pdo->prepare('INSERT INTO regle_mapping (profil_id, colonne_source, champ_cible) VALUES (?, ?, ?)');
        foreach ($entetes as $i => $entete) {
            $insertion->execute([$profilId, $entete, $choix[$i] ?? null]);
        }
        return $profilId;
    }

    public static function estPersonnelle(string $entete): bool
    {
        $mots = explode(' ', Texte::normaliser($entete));
        return array_intersect($mots, self::MOTS_PERSONNELS) !== [] && !in_array(Texte::normaliser($entete), [
            'nom structure d accueil', 'service d accueil nom', 'service d accueil adresse', 'adresse',
        ], true);
    }

    /** Convertit un tableau PostgreSQL renvoyé par PDO ('{"a","b"}') en tableau PHP. */
    private static function tableauPg(string $valeur): array
    {
        return str_getcsv(trim($valeur, '{}'), ',', '"', '\\');
    }
}
