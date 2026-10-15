"""Génère un fichier de test simulant une extraction d'une autre année.

Aucune autre extraction réelle n'existe (2025-2026 est la première année
d'EsupStage). Ce fichier, dérivé de la base 2025-2026, sert à vérifier que
l'import s'adapte aux évolutions de colonnes en ajustant seulement le mapping :
colonnes renommées, réordonnées, ajoutées et supprimées. Toutes les dates
sont décalées d'un an pour simuler l'année universitaire 2026-2027.

Sortie : donnees/tests/esupstage_test_colonnes_modifiees.xlsx (non versionné).
"""

import pandas as pd

from analyse_exploratoire import DATE_COLUMNS, INPUT_FILE, ROOT_DIR, find_column, load_data

OUTPUT_FILE = ROOT_DIR / "donnees" / "tests" / "esupstage_test_colonnes_modifiees.xlsx"

RENOMMAGES = {
    "N° de la convention": "Numéro convention",              # proche d'un alias
    "Service d'accueil - Pays": "Pays du service d'accueil",  # mots réordonnés
    "Date début du stage": "Date de début du stage",          # alias connu
    "Nbre d'heures hebdomadaire": "Temps de travail hebdo",   # à associer à la main
    "Thématique": "Domaine d'activité du stage",               # à associer à la main
}
SUPPRESSIONS = ["Avantage nature", "Code ELP", "Libellé UFR"]
AJOUT = ("Télétravail", "Non")
ANNEE = "2026/2027"
DECALAGE = pd.DateOffset(years=1)  # toutes les dates passent en 2026-2027 (29/02 → 28/02)


def main():
    df = load_data(INPUT_FILE)
    for nom in DATE_COLUMNS:
        col = find_column(df, nom)
        dates = pd.to_datetime(df[col], format="%d/%m/%Y", errors="coerce")
        df[col] = (dates + DECALAGE).dt.strftime("%d/%m/%Y").where(dates.notna(), df[col])
    df = df.drop(columns=[find_column(df, c) for c in SUPPRESSIONS])
    df = df.rename(columns={find_column(df, old): new for old, new in RENOMMAGES.items()})
    df[find_column(df, "Année universitaire")] = ANNEE
    df[AJOUT[0]] = AJOUT[1]
    df = df[sorted(df.columns)]  # ordre des colonnes modifié (alphabétique)

    OUTPUT_FILE.parent.mkdir(parents=True, exist_ok=True)
    df.to_excel(OUTPUT_FILE, index=False)
    print(f"{OUTPUT_FILE} : {df.shape[0]} lignes, {df.shape[1]} colonnes")


if __name__ == "__main__":
    main()
