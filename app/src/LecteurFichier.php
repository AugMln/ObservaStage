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

    /** @var list<string> problèmes de format détectés à la lecture, à montrer à l'utilisatrice */
    public array $avertissements = [];

    public function __construct(string $chemin, string $nomOriginal)
    {
        $extension = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xls', 'xlsx', 'csv'], true)) {
            throw new RuntimeException('Format non pris en charge : utiliser un fichier .xls, .xlsx ou .csv.');
        }

        $lecteur = IOFactory::createReader(ucfirst($extension));
        if ($extension === 'csv') {
            // « Enregistrer sous CSV » dans Excel produit du Windows-1252, pas de l'UTF-8.
            if (!mb_check_encoding(file_get_contents($chemin), 'UTF-8')) {
                $lecteur->setInputEncoding('CP1252');
                $this->avertissements[] = 'Fichier CSV enregistré par Excel (encodage Windows) : certains caractères '
                    . 'étrangers peuvent avoir été perdus par Excel (ex. « Wrocław » → « Wroc?aw »). '
                    . "Préférer le fichier .xls d'origine ou un enregistrement en .xlsx.";
            }
        } else {
            // Styles lus pour retrouver les zéros initiaux affichés par un format « 0000000000 ».
            $lecteur->setReadDataOnly(false);
        }
        $feuille = $lecteur->load($chemin)->getSheet(0);

        $premiere = true;
        foreach ($feuille->getRowIterator() as $ligne) {
            $cellules = $ligne->getCellIterator();
            $cellules->setIterateOnlyExistingCells(false);
            $valeurs = [];
            foreach ($cellules as $cellule) {
                $valeur = $cellule->getValue();
                if ($valeur instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $valeur = $valeur->getPlainText();
                }
                if (is_float($valeur) || is_int($valeur)) {
                    $format = $extension === 'csv' ? '' : $cellule->getStyle()->getNumberFormat()->getFormatCode();
                    $valeur = $this->nombreVersTexte($valeur, $premiere ? '' : ($this->entetes[count($valeurs)] ?? ''), $format);
                }
                // Retours chariot : « _x000D_ » dans les .xlsx, \r\n ailleurs.
                $valeur = str_replace(['_x000D_', "\r\n", "\r"], ['', "\n", "\n"], (string) $valeur);
                $valeurs[] = trim($valeur);
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
     * les dates sont remises au format jj/mm/aaaa, les entiers sans décimale,
     * et les zéros initiaux d'un format « 00000 » (codes postaux, téléphones) sont rétablis.
     */
    private function nombreVersTexte(float|int $valeur, string $entete, string $format = ''): string
    {
        if (str_contains(Texte::normaliser($entete), 'date') && $valeur > 20000 && $valeur < 80000) {
            return DateExcel::excelToDateTimeObject($valeur)->format('d/m/Y');
        }
        if (preg_match('/^0+$/', $format) && floor($valeur) == $valeur) {
            return str_pad(number_format($valeur, 0, '', ''), strlen($format), '0', STR_PAD_LEFT);
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
