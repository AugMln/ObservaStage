<?php

namespace ObservaStage;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as DateExcel;
use RuntimeException;

/**
 * Lit la première feuille d'un fichier EsupStage (.xls, .xlsx, .csv).
 * Toutes les valeurs sont rendues sous forme de texte, sans conversion implicite
 * (codes postaux, SIRET et numéros gardent leurs zéros initiaux).
 */
final class LecteurFichier
{
    /** @var list<string> */
    public array $entetes = [];

    /** @var list<list<string>> */
    public array $lignes = [];

    public function __construct(string $chemin, string $nomOriginal)
    {
        $extension = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xls', 'xlsx', 'csv'], true)) {
            throw new RuntimeException('Format non pris en charge : utiliser un fichier .xls, .xlsx ou .csv.');
        }

        $lecteur = IOFactory::createReader(ucfirst($extension));
        $lecteur->setReadDataOnly(true);
        $feuille = $lecteur->load($chemin)->getSheet(0);

        $premiere = true;
        foreach ($feuille->getRowIterator() as $ligne) {
            $cellules = $ligne->getCellIterator();
            $cellules->setIterateOnlyExistingCells(false);
            $valeurs = [];
            foreach ($cellules as $cellule) {
                $valeur = $cellule->getValue();
                if (is_float($valeur) || is_int($valeur)) {
                    $valeur = $this->nombreVersTexte($valeur, $premiere ? '' : ($this->entetes[count($valeurs)] ?? ''));
                }
                $valeurs[] = trim((string) $valeur);
            }
            if ($premiere) {
                $this->entetes = $valeurs;
                $premiere = false;
                continue;
            }
            if (implode('', $valeurs) !== '') {
                $this->lignes[] = array_pad($valeurs, count($this->entetes), '');
            }
        }

        if ($this->entetes === []) {
            throw new RuntimeException('Le fichier est vide.');
        }
    }

    /**
     * Un fichier repassé par Excel peut contenir des nombres au lieu de textes :
     * les dates sont remises au format jj/mm/aaaa, les entiers sans décimale.
     */
    private function nombreVersTexte(float|int $valeur, string $entete): string
    {
        if (str_contains(Texte::normaliser($entete), 'date') && $valeur > 20000 && $valeur < 80000) {
            return DateExcel::excelToDateTimeObject($valeur)->format('d/m/Y');
        }
        return floor($valeur) == $valeur ? number_format($valeur, 0, '', '') : (string) $valeur;
    }

    /** @return list<string> jusqu'à $n valeurs non vides et distinctes de la colonne */
    public function exemples(int $colonne, int $n = 3): array
    {
        $exemples = [];
        foreach ($this->lignes as $ligne) {
            $valeur = $ligne[$colonne] ?? '';
            if ($valeur !== '' && !in_array($valeur, $exemples, true)) {
                $exemples[] = mb_strimwidth($valeur, 0, 60, '…');
                if (count($exemples) >= $n) {
                    break;
                }
            }
        }
        return $exemples;
    }
}
