<?php

namespace ObservaStage;

final class Recherche
{
    /** Colonnes affichées dans la recherche. */
    public const COLONNES = [
        'annee_universitaire' => 'Année',
        'numero_convention' => 'N° convention',
        'etudiant' => 'Étudiant',
        'niveau' => 'Niveau',
        'specialite' => 'Spécialité',
        'structure_nom' => 'Structure',
        'service_commune' => 'Commune',
        'service_pays' => 'Pays',
        'date_debut' => 'Début',
        'date_fin' => 'Fin',
        'duree_semaines' => 'Semaines',
        'thematique' => 'Thématique',
        'type_experience' => 'Type',
        'enseignant' => 'Enseignant référent',
        'tuteur' => 'Tuteur professionnel',
        'sujet' => 'Sujet',
    ];

    /** Colonnes exportées : celles de la recherche, plus les identifiants et e-mails. */
    public const COLONNES_EXPORT = self::COLONNES + [
        'etudiant_numero' => 'N° étudiant',
        'etudiant_email' => 'E-mail étudiant',
        'enseignant_email' => 'E-mail enseignant référent',
        'tuteur_email' => 'E-mail tuteur',
    ];

    public static function rechercher(array $criteres, int $page = 1, int $parPage = 50): array
    {
        [$where, $params] = Filtres::versSql($criteres);
        $total = (int) Db::valeur("SELECT count(*) FROM v_stage $where", $params);
        $decalage = max(0, $page - 1) * $parPage;
        return [
            'total' => $total,
            'page' => $page,
            'par_page' => $parPage,
            'colonnes' => self::COLONNES,
            'lignes' => Db::lignes(self::requete($where, self::COLONNES) . " LIMIT $parPage OFFSET $decalage", $params),
        ];
    }

    public static function tout(array $criteres): array
    {
        [$where, $params] = Filtres::versSql($criteres);
        return Db::lignes(self::requete($where, self::COLONNES_EXPORT), $params);
    }

    private static function requete(string $where, array $colonnes): string
    {
        $formats = [
            'date_debut' => "to_char(date_debut, 'DD/MM/YYYY')",
            'date_fin' => "to_char(date_fin, 'DD/MM/YYYY')",
            'duree_semaines' => 'round(duree_semaines, 1)',
        ];
        $select = implode(', ', array_map(
            fn ($c) => isset($formats[$c]) ? "{$formats[$c]} AS $c" : $c,
            array_keys($colonnes)
        ));
        return "SELECT $select
                FROM v_stage $where
                ORDER BY annee_universitaire DESC, niveau_ordre, specialite, numero_convention";
    }

    /** Valeurs proposées dans les listes de filtres. */
    public static function options(): array
    {
        $liste = fn (string $sql) => array_column(Db::lignes($sql), 'v');
        return [
            'annee' => $liste('SELECT DISTINCT annee_universitaire AS v FROM stage ORDER BY 1 DESC'),
            'niveau' => $liste('SELECT niveau AS v FROM ref_niveau ORDER BY ordre'),
            'specialite' => $liste('SELECT DISTINCT specialite AS v FROM ref_etape ORDER BY 1'),
            'pays' => $liste('SELECT DISTINCT upper(service_pays) AS v FROM stage WHERE service_pays IS NOT NULL ORDER BY 1'),
            'thematique' => $liste('SELECT DISTINCT thematique AS v FROM stage WHERE thematique IS NOT NULL ORDER BY 1'),
            'type_experience' => ['Stage', 'CDD', 'CDI', 'Intérim', 'Bénévolat'],
        ];
    }
}
