<?php

namespace ObservaStage;

final class Indicateurs
{
    public static function calculer(array $criteres): array
    {
        [$where, $params] = Filtres::versSql($criteres);

        $global = Db::ligne(
            "SELECT count(*) AS stages,
                    round(avg(duree_jours), 1) AS jours,
                    round(avg(duree_semaines), 1) AS semaines,
                    round(avg(duree_heures_valide), 1) AS heures,
                    round(percentile_cont(0.5) WITHIN GROUP (ORDER BY duree_jours)::numeric, 1) AS jours_median,
                    count(*) FILTER (WHERE etranger) AS etranger,
                    count(*) FILTER (WHERE NOT etranger) AS france,
                    count(*) FILTER (WHERE hauts_de_france) AS hauts_de_france,
                    count(*) FILTER (WHERE recherche) AS recherche,
                    count(*) FILTER (WHERE duree_heures_valide IS NULL) AS exclus_heures
             FROM v_stage $where",
            $params
        );

        $parNiveau = Db::lignes(
            "SELECT niveau, count(*) AS stages,
                    round(avg(duree_jours), 1) AS jours,
                    round(avg(duree_semaines), 1) AS semaines,
                    round(avg(duree_heures_valide), 1) AS heures,
                    round(100.0 * count(*) FILTER (WHERE etranger) / count(*), 1) AS pct_etranger,
                    round(100.0 * count(*) FILTER (WHERE hauts_de_france) / count(*), 1) AS pct_hdf
             FROM v_stage $where
             GROUP BY niveau, niveau_ordre ORDER BY niveau_ordre",
            $params
        );

        return [
            'regle_validation' => Db::valeur("SELECT param('regle_validation')"),
            'importees' => (int) Db::valeur('SELECT count(*) FROM stage'),
            'global' => $global,
            'par_niveau' => $parNiveau,
            'par_annee' => self::repartition('annee_universitaire', $where, $params),
            'top_pays' => self::repartition("upper(service_pays)", $where . ($where ? ' AND' : 'WHERE') . ' etranger', $params, 10),
            'thematiques' => self::repartition('thematique', $where, $params, 10),
            'type_experience' => self::repartition('type_experience',
                $where . ($where ? ' AND' : 'WHERE') . ' type_experience IS NOT NULL', $params),
        ];
    }

    private static function repartition(string $expression, string $where, array $params, ?int $limite = null): array
    {
        return Db::lignes(
            "SELECT coalesce($expression, 'Non renseigné') AS libelle, count(*) AS stages
             FROM v_stage $where GROUP BY 1 ORDER BY 2 DESC, 1" . ($limite ? " LIMIT $limite" : ''),
            $params
        );
    }

    /** Lignes à plat pour l'export des indicateurs. */
    public static function versTableau(array $resultat): array
    {
        $g = $resultat['global'];
        $lignes = [
            ['Indicateur', 'Valeur'],
            ['Conventions retenues', $g['stages']],
            ['Durée moyenne (jours)', $g['jours']],
            ['Durée moyenne (semaines)', $g['semaines']],
            ['Durée moyenne (heures)', $g['heures']],
            ['Stages à l\'étranger', $g['etranger']],
            ['Stages en France', $g['france']],
            ['Stages en Hauts-de-France', $g['hauts_de_france']],
            ['Stages « recherche » (sujet)', $g['recherche']],
            [],
            ['Niveau', 'Stages', 'Jours', 'Semaines', 'Heures', '% étranger', '% Hauts-de-France'],
        ];
        foreach ($resultat['par_niveau'] as $n) {
            $lignes[] = array_values($n);
        }
        return $lignes;
    }
}
