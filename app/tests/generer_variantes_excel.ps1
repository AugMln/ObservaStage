# Ouvre l'extraction EsupStage dans Excel et l'enregistre comme le ferait l'utilisatrice
# (.xls, .xlsx, CSV Excel, CSV UTF-8) dans donnees/tests/excel/. Nécessite Excel.
$racine = Resolve-Path "$PSScriptRoot\..\.."
$src = Join-Path $racine "donnees\esupstage_2025-2026_anonymise.xls"
$out = Join-Path $racine "donnees\tests\excel"
New-Item -ItemType Directory -Force $out | Out-Null
$x = New-Object -ComObject Excel.Application
$x.DisplayAlerts = $false
try {
  foreach ($v in @(@("resauve.xls", 56), @("resauve.xlsx", 51), @("resauve_csv_excel.csv", 6), @("resauve_csv_utf8.csv", 62))) {
    $wb = $x.Workbooks.Open($src)
    $m = [Type]::Missing
    $wb.SaveAs((Join-Path $out $v[0]), $v[1], $m, $m, $m, $m, 1, $m, $m, $m, $m, $true)
    $wb.Close($false)
  }
} finally {
  $x.Quit()
  [System.Runtime.InteropServices.Marshal]::ReleaseComObject($x) | Out-Null
}
Get-ChildItem $out | Select-Object Name, Length
