<?php

namespace ObservaStage;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

/** Envoie un tableau (première ligne = en-têtes) en CSV ou en XLS. */
final class Export
{
    public static function envoyer(array $lignes, string $nom, string $format): void
    {
        $nom .= '_' . date('Y-m-d');
        if ($format === 'xls') {
            $classeur = new Spreadsheet();
            $feuille = $classeur->getActiveSheet();
            $feuille->setTitle(mb_substr($nom, 0, 31));
            foreach ($lignes as $i => $ligne) {
                foreach (array_values($ligne) as $j => $valeur) {
                    // Écrit en texte explicite : pas de conversion des numéros ou codes postaux.
                    $feuille->setCellValueExplicit([$j + 1, $i + 1], (string) $valeur,
                        is_numeric($valeur) && !preg_match('/^0\d/', (string) $valeur) && $i > 0
                            ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                            : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment; filename=\"$nom.xls\"");
            (new Xls($classeur))->save('php://output');
            return;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$nom.csv\"");
        $sortie = fopen('php://output', 'w');
        fwrite($sortie, "\xEF\xBB\xBF"); // BOM : accents corrects à l'ouverture dans Excel
        foreach ($lignes as $ligne) {
            // Décimales à la virgule : lues comme des nombres par Excel en français.
            $ligne = array_map(fn ($v) => is_numeric($v) ? str_replace('.', ',', (string) $v) : $v, array_values($ligne));
            fputcsv($sortie, $ligne, ';', '"', '\\');
        }
        fclose($sortie);
    }
}
