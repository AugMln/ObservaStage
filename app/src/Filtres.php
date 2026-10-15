<?php

namespace ObservaStage;

/**
 * Traduit les critères de recherche (paramètres GET) en clause WHERE sur v_stage.
 * Les mêmes filtres servent à la recherche, aux indicateurs et aux exports.
 */
final class Filtres
{
    public const CRITERES = ['annee', 'niveau', 'specialite', 'localisation', 'pays', 'thematique',
        'type_experience', 'recherche', 'texte', 'perimetre'];

    /** @return array{0: string, 1: array<string, mixed>} */
    public static function versSql(array $criteres): array
    {
        $conditions = [];
        $params = [];

        // Par défaut, seules les conventions retenues (règle de validation paramétrée).
        if (($criteres['perimetre'] ?? 'retenues') !== 'toutes') {
            $conditions[] = 'retenu';
        }

        foreach (['annee' => 'annee_universitaire', 'niveau' => 'niveau', 'specialite' => 'specialite',
                  'pays' => 'upper(service_pays)', 'thematique' => 'thematique',
                  'type_experience' => 'type_experience'] as $critere => $colonne) {
            if (($criteres[$critere] ?? '') !== '') {
                $conditions[] = "$colonne = :$critere";
                $params[$critere] = $critere === 'pays' ? mb_strtoupper($criteres[$critere]) : $criteres[$critere];
            }
        }

        switch ($criteres['localisation'] ?? '') {
            case 'france':
                $conditions[] = 'NOT etranger';
                break;
            case 'etranger':
                $conditions[] = 'etranger';
                break;
            case 'hdf':
                $conditions[] = 'hauts_de_france';
                break;
            case 'france_hors_hdf':
                $conditions[] = 'NOT etranger AND NOT hauts_de_france';
                break;
        }

        if (($criteres['recherche'] ?? '') === '1') {
            $conditions[] = 'recherche';
        }

        if (trim($criteres['texte'] ?? '') !== '') {
            $conditions[] = "unaccent(concat_ws(' ', sujet, fonctions_taches, structure_nom, service_nom, service_commune))
                             ILIKE unaccent(:texte)";
            $params['texte'] = '%' . trim($criteres['texte']) . '%';
        }

        return [$conditions ? 'WHERE ' . implode(' AND ', $conditions) : '', $params];
    }
}
