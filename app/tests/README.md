# Tests

## Robustesse aux fichiers repassés par Excel

Vérifie que l'outil lit les mêmes valeurs dans l'extraction d'origine et dans ses versions enregistrées par Excel.

```powershell
.\app\tests\generer_variantes_excel.ps1
$c = docker compose -f app/docker-compose.yml ps -q web
docker exec $c mkdir -p /tmp/robustesse
docker cp donnees/esupstage_2025-2026_anonymise.xls "${c}:/tmp/robustesse/original.xls"
Get-ChildItem donnees/tests/excel | ForEach-Object { docker cp $_.FullName "${c}:/tmp/robustesse/" }
docker cp app/tests/robustesse_excel.php "${c}:/tmp/robustesse/"
docker exec $c php /tmp/robustesse/robustesse_excel.php
```

Résultat au 15/10/2026 :

- .xls et .xlsx réenregistrés : aucune différence.
- CSV Excel : seuls les caractères absents de Windows-1252 sont perdus par Excel (« Wrocław » → « Wroc?aw »). L'outil le signale.
- Téléphones (format Excel `0##########`, non importés) : le 0 initial n'est pas reproduit.

## Indicateurs

`exploration/verifier_application.py` compare les indicateurs de l'application aux chiffres de référence.
