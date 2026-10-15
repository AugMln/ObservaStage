<?php

namespace ObservaStage;

final class Recherche
{
    /** Colonnes exposées : aucune donnée personnelle (ni étudiant, ni encadrant). */
    public const COLONNES = [
        'annee_universitaire' => 'Année',
        'numero_convention' => 'N° convention',
        'niveau' => 'Niveau',
        'specialite' => 'Spécialité',
        'structure_nom' => 'Structure',
        'service_commune' => 'Commune',
        'service_pays' => 'Pays',
        'date_debut' => 'Début',
        'date_fin' => 'Fin',
        'duree_semaines' => 'Semaines',
        'thematique' => 'Thématique',
        'sujet' => 'Sujet',
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
            'lignes' => Db::lignes(self::requete($where) . " LIMIT $parPage OFFSET $decalage", $params),
        ];
    }

    public static function tout(array $criteres): array
    {
        [$where, $params] = Filtres::versSql($criteres);
        return Db::lignes(self::requete($where), $params);
    }

    private static function requete(string $where): string
    {
        return "SELECT annee_universitaire, numero_convention, niveau, specialite, structure_nom,
                       service_commune, service_pays, to_char(date_debut, 'DD/MM/YYYY') AS date_debut,
                       to_char(date_fin, 'DD/MM/YYYY') AS date_fin, round(duree_semaines, 1) AS duree_semaines,
                       thematique, sujet
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
            'type_experience' => ['CDD', 'CDI', 'Intérim', 'Bénévolat'],
        ];
    }
}
