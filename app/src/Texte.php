<?php

namespace ObservaStage;

use Normalizer;

final class Texte
{
    /**
     * Normalise un intitulé pour la comparaison : minuscules, sans accents,
     * ponctuation (tirets courts/longs, apostrophes typographiques) remplacée par des espaces.
     */
    public static function normaliser(?string $valeur): string
    {
        $valeur = mb_strtolower(trim((string) $valeur));
        $valeur = Normalizer::normalize($valeur, Normalizer::FORM_KD);
        $valeur = preg_replace('/\p{Mn}+/u', '', $valeur);
        $valeur = preg_replace('/[^a-z0-9]+/', ' ', $valeur);
        return trim($valeur);
    }

    /** Similarité entre 0 et 1 de deux intitulés normalisés. */
    public static function similarite(string $a, string $b): float
    {
        if ($a === '' || $b === '') {
            return 0.0;
        }
        similar_text($a, $b, $pourcentage);
        // Mots communs rapportés à l'ensemble des mots, indépendamment de l'ordre
        // (« Pays du service d'accueil » ≈ « Service d'accueil – Pays », mais pas « Service d'accueil – Adresse »).
        $motsA = array_unique(explode(' ', $a));
        $motsB = array_unique(explode(' ', $b));
        $communs = count(array_intersect($motsA, $motsB));
        $mots = $communs / (count($motsA) + count($motsB) - $communs);
        return max(0.9 * $pourcentage / 100, $mots);
    }
}
