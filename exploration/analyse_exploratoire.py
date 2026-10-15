from pathlib import Path
import json
import re
import unicodedata

import pandas as pd


# ============================================================
# CONFIGURATION
# ============================================================

ROOT_DIR = Path(__file__).resolve().parent.parent
INPUT_FILE = ROOT_DIR / "donnees" / "esupstage_2025-2026_anonymise.xls"  # .csv, .xls ou .xlsx
OUTPUT_DIR = Path(__file__).resolve().parent / "resultats"

# Nombre maximal de catégories affichées par colonne
TOP_N = 30

# Colonnes à examiner en détail : noms tels qu'ils apparaissent
# dans l'export EsupStage.
CATEGORICAL_COLUMNS = [
    "Code étape",
    "Libellé étape",
    "Année universitaire",
    "Thématique",
    "Type convention",
    "Type de structure",
    "Statut juridique",
    "Pays",
    "Service d'accueil - Pays",
    "Commune",
    "Service d'accueil - Commune",
    "Code NAF",
    "Effectif",
    "Validation convention",
    "validation pédagogique",
    "Code sexe étudiant",
]

DATE_COLUMNS = [
    "Date création convention",
    "Date début du stage",
    "Date début interruption",
    "Date fin du stage",
    "Date fin interruption",
    "Date modification convention",
]

# Colonnes contenant potentiellement des informations personnelles.
# Le script ne produit pas de fréquence détaillée pour celles-ci.
SENSITIVE_COLUMNS = [
    "Nom étudiant",
    "Prénom étudiant",
    "Adresse étudiant",
    "Mail perso étudiant",
    "Mail universitaire étudiant",
    "Téléphone perso étudiant",
    "Téléphone portable étudiant",
    "Mail",
    "Mail tuteur professionnel",
    "Mail enseignant référent",
    "Mail signataire",
    "Téléphone",
    "Téléphone tuteur professionnel",
    "Tuteur professionnel (Nom Prénom)",
    "Enseignant référent (NOM Prénom)",
    "Signataire (NOM Prénom)",
]


# ============================================================
# LECTURE DU FICHIER
# ============================================================

def read_csv_robustly(path):
    """Essaie plusieurs encodages pour lire un CSV."""
    errors = []

    for encoding in ["utf-8-sig", "utf-8", "cp1252", "latin1"]:
        try:
            return pd.read_csv(
                path,
                sep=None,            # Détection automatique du séparateur
                engine="python",
                encoding=encoding,
                dtype=str,
                keep_default_na=True,
                on_bad_lines="warn",
            )
        except (UnicodeDecodeError, pd.errors.ParserError) as exc:
            errors.append(f"{encoding}: {exc}")

    raise RuntimeError(
        "Impossible de lire le CSV.\n" + "\n".join(errors)
    )


def load_data(path):
    """Charge un fichier CSV ou Excel sans convertir les identifiants en nombres."""
    if not path.exists():
        raise FileNotFoundError(
            f"Fichier introuvable : {path.resolve()}"
        )

    suffix = path.suffix.lower()

    if suffix == ".csv":
        df = read_csv_robustly(path)

    elif suffix in [".xls", ".xlsx", ".xlsm"]:
        # Pour .xls : installer xlrd.
        # Pour .xlsx/.xlsm : installer openpyxl.
        df = pd.read_excel(
            path,
            sheet_name=0,
            dtype=str,
        )

    else:
        raise ValueError(
            "Format non pris en charge. Utilise .csv, .xls ou .xlsx."
        )

    # Nettoyage léger des noms de colonnes, sans modifier leur sens.
    df.columns = [
        str(col).strip() for col in df.columns
    ]

    # Les chaînes vides et les espaces seuls sont considérés comme manquants.
    df = df.replace(r"^\s*$", pd.NA, regex=True)

    return df


# ============================================================
# OUTILS
# ============================================================

def normalize_name(value):
    """Normalise un nom pour rechercher une colonne sans dépendre des accents."""
    value = str(value).strip().lower()
    value = unicodedata.normalize("NFKD", value)
    value = "".join(
        char for char in value
        if not unicodedata.combining(char)
    )
    value = re.sub(r"[^a-z0-9]+", " ", value)
    return re.sub(r"\s+", " ", value).strip()


def find_column(df, expected_name):
    """Retrouve une colonne même si les accents ou la casse diffèrent."""
    target = normalize_name(expected_name)

    for col in df.columns:
        if normalize_name(col) == target:
            return col

    return None


def save_csv(df, filename):
    df.to_csv(
        OUTPUT_DIR / filename,
        index=False,
        encoding="utf-8-sig",
    )


def write_text(filename, content):
    (OUTPUT_DIR / filename).write_text(
        content,
        encoding="utf-8",
    )


# ============================================================
# ANALYSE PRINCIPALE
# ============================================================

def main():
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    df = load_data(INPUT_FILE)

    n_rows, n_cols = df.shape

    print("=" * 65)
    print("ANALYSE EXPLORATOIRE DE LA BASE ESUPSTAGE")
    print("=" * 65)
    print(f"Fichier             : {INPUT_FILE.resolve()}")
    print(f"Nombre de lignes    : {n_rows}")
    print(f"Nombre de colonnes  : {n_cols}")
    print(f"Nombre de doublons exacts : {df.duplicated().sum()}")
    print(f"Dossier de résultats : {OUTPUT_DIR.resolve()}")

    # --------------------------------------------------------
    # 1. Structure et qualité générale
    # --------------------------------------------------------

    missing_count = df.isna().sum()
    missing_pct = (missing_count / max(n_rows, 1) * 100).round(2)

    structure = pd.DataFrame({
        "colonne": df.columns,
        "type_pandas": [
            str(df[col].dtype) for col in df.columns
        ],
        "valeurs_manquantes": [
            int(missing_count[col]) for col in df.columns
        ],
        "pourcentage_manquant": [
            float(missing_pct[col]) for col in df.columns
        ],
        "valeurs_distinctes": [
            int(df[col].nunique(dropna=True)) for col in df.columns
        ],
        "colonne_constante": [
            df[col].nunique(dropna=True) <= 1 for col in df.columns
        ],
    })

    save_csv(structure, "01_structure_et_qualite.csv")

    # Résumé général.
    summary = {
        "fichier": INPUT_FILE.name,
        "nombre_lignes": int(n_rows),
        "nombre_colonnes": int(n_cols),
        "doublons_exacts": int(df.duplicated().sum()),
        "cellules_manquantes": int(df.isna().sum().sum()),
        "pourcentage_cellules_manquantes": round(
            float(df.isna().sum().sum() / max(n_rows * n_cols, 1) * 100),
            2,
        ),
        "colonnes_entierement_vides": [
            col for col in df.columns if df[col].isna().all()
        ],
        "colonnes_constantes": [
            col for col in df.columns
            if df[col].nunique(dropna=True) <= 1
        ],
    }

    write_text(
        "00_resume_general.json",
        json.dumps(summary, ensure_ascii=False, indent=2),
    )

    # --------------------------------------------------------
    # 2. Valeurs fréquentes des colonnes catégorielles
    # --------------------------------------------------------

    category_rows = []

    for requested_col in CATEGORICAL_COLUMNS:
        col = find_column(df, requested_col)

        if col is None:
            print(f"[À vérifier] Colonne absente : {requested_col}")
            continue

        counts = (
            df[col]
            .fillna("[VALEUR MANQUANTE]")
            .value_counts(dropna=False)
            .head(TOP_N)
        )

        for value, count in counts.items():
            category_rows.append({
                "colonne": col,
                "valeur": str(value),
                "effectif": int(count),
                "pourcentage_lignes": round(
                    float(count / max(n_rows, 1) * 100),
                    2,
                ),
            })

    save_csv(
        pd.DataFrame(category_rows),
        "02_repartition_categories.csv",
    )

    # --------------------------------------------------------
    # 3. Analyse des dates
    # --------------------------------------------------------

    date_rows = []
    parsed_dates = {}

    for requested_col in DATE_COLUMNS:
        col = find_column(df, requested_col)

        if col is None:
            continue

        # Les dates du fichier sont généralement au format jj/mm/aaaa.
        parsed = pd.to_datetime(
            df[col],
            format="%d/%m/%Y",
            errors="coerce",
        )

        # Une valeur présente mais non interprétable est signalée.
        present = df[col].notna()
        invalid = present & parsed.isna()

        parsed_dates[requested_col] = parsed

        valid_dates = parsed.dropna()

        date_rows.append({
            "colonne": col,
            "valeurs_renseignees": int(present.sum()),
            "dates_valides": int(parsed.notna().sum()),
            "dates_invalides": int(invalid.sum()),
            "date_min": (
                valid_dates.min().strftime("%Y-%m-%d")
                if not valid_dates.empty else None
            ),
            "date_max": (
                valid_dates.max().strftime("%Y-%m-%d")
                if not valid_dates.empty else None
            ),
        })

    save_csv(
        pd.DataFrame(date_rows),
        "03_analyse_dates.csv",
    )

    # --------------------------------------------------------
    # 4. Durée entre dates et interruptions
    # --------------------------------------------------------

    start_col = find_column(df, "Date début du stage")
    end_col = find_column(df, "Date fin du stage")
    interruption_start_col = find_column(df, "Date début interruption")
    interruption_end_col = find_column(df, "Date fin interruption")

    if start_col and end_col:
        start = pd.to_datetime(
            df[start_col], format="%d/%m/%Y", errors="coerce"
        )
        end = pd.to_datetime(
            df[end_col], format="%d/%m/%Y", errors="coerce"
        )

        # Durée calendaire inclusive, exprimée en jours.
        duration_days = (end - start).dt.days + 1

        # Calcul indicatif de la durée hors interruption.
        # Hypothèse : une seule interruption par ligne.
        interruption_days = pd.Series(0, index=df.index, dtype="float64")

        if interruption_start_col and interruption_end_col:
            int_start = pd.to_datetime(
                df[interruption_start_col],
                format="%d/%m/%Y",
                errors="coerce",
            )
            int_end = pd.to_datetime(
                df[interruption_end_col],
                format="%d/%m/%Y",
                errors="coerce",
            )

            valid_interruption = int_start.notna() & int_end.notna()
            interruption_days.loc[valid_interruption] = (
                int_end.loc[valid_interruption]
                - int_start.loc[valid_interruption]
            ).dt.days + 1

        net_days = duration_days - interruption_days

        duration_df = pd.DataFrame({
            "date_debut_valide": start.notna(),
            "date_fin_valide": end.notna(),
            "duree_calendaire_jours": duration_days,
            "jours_interruption_detectes": interruption_days,
            "duree_hors_interruption_jours": net_days,
        })

        # Le rapport contient uniquement des statistiques agrégées.
        duration_summary = {}

        for col in [
            "duree_calendaire_jours",
            "jours_interruption_detectes",
            "duree_hors_interruption_jours",
        ]:
            values = duration_df[col].dropna()

            duration_summary[col] = {
                "nombre_valeurs": int(values.count()),
                "moyenne": (
                    round(float(values.mean()), 2)
                    if not values.empty else None
                ),
                "mediane": (
                    round(float(values.median()), 2)
                    if not values.empty else None
                ),
                "minimum": (
                    float(values.min()) if not values.empty else None
                ),
                "maximum": (
                    float(values.max()) if not values.empty else None
                ),
                "valeurs_negatives": int((values < 0).sum()),
                "valeurs_nulles": int((values == 0).sum()),
            }

        write_text(
            "04_resume_durees.json",
            json.dumps(duration_summary, ensure_ascii=False, indent=2),
        )

        # Vérifie aussi la durée déclarée, sans supposer son unité.
        declared_col = find_column(df, "Durée du stage")

        if declared_col:
            declared = pd.to_numeric(
                df[declared_col].astype("string").str.replace(
                    ",", ".", regex=False
                ),
                errors="coerce",
            )

            declared_summary = {
                "colonne": declared_col,
                "valeurs_renseignees": int(df[declared_col].notna().sum()),
                "valeurs_numeriques_interpretees": int(declared.notna().sum()),
                "minimum": (
                    float(declared.min()) if declared.notna().any() else None
                ),
                "mediane": (
                    float(declared.median()) if declared.notna().any() else None
                ),
                "maximum": (
                    float(declared.max()) if declared.notna().any() else None
                ),
                "note": (
                    "Unité inconnue : ne pas comparer directement "
                    "à la durée calculée à partir des dates."
                ),
            }

            write_text(
                "05_duree_declaree.json",
                json.dumps(declared_summary, ensure_ascii=False, indent=2),
            )

        # Anomalies de dates, sans exporter les lignes concernées.
        date_issues = {
            "debut_apres_fin": int(
                (start.notna() & end.notna() & (start > end)).sum()
            ),
            "date_debut_manquante": int(start.isna().sum()),
            "date_fin_manquante": int(end.isna().sum()),
            "interruption_debut_sans_fin": 0,
            "interruption_fin_sans_debut": 0,
            "interruption_hors_stage": 0,
        }

        if interruption_start_col and interruption_end_col:
            int_start = pd.to_datetime(
                df[interruption_start_col],
                format="%d/%m/%Y",
                errors="coerce",
            )
            int_end = pd.to_datetime(
                df[interruption_end_col],
                format="%d/%m/%Y",
                errors="coerce",
            )

            date_issues["interruption_debut_sans_fin"] = int(
                (int_start.notna() & int_end.isna()).sum()
            )
            date_issues["interruption_fin_sans_debut"] = int(
                (int_start.isna() & int_end.notna()).sum()
            )
            date_issues["interruption_hors_stage"] = int(
                (
                    int_start.notna()
                    & int_end.notna()
                    & start.notna()
                    & end.notna()
                    & (
                        (int_start < start)
                        | (int_end > end)
                        | (int_start > int_end)
                    )
                ).sum()
            )

        write_text(
            "06_anomalies_dates.json",
            json.dumps(date_issues, ensure_ascii=False, indent=2),
        )

    # --------------------------------------------------------
    # 5. Doublons potentiels par numéro de convention
    # --------------------------------------------------------

    convention_col = find_column(df, "N° de la convention")

    if convention_col:
        conventions = df[convention_col].dropna().astype(str).str.strip()
        duplicated_conventions = conventions[
            conventions.duplicated(keep=False)
        ]

        duplicate_summary = {
            "conventions_renseignees": int(conventions.nunique()),
            "lignes_sans_numero_convention": int(
                df[convention_col].isna().sum()
            ),
            "lignes_concernant_un_numero_repeté": int(
                duplicated_conventions.shape[0]
            ),
            "numeros_repetes_distincts": int(
                duplicated_conventions.nunique()
            ),
        }

        write_text(
            "07_doublons_conventions.json",
            json.dumps(duplicate_summary, ensure_ascii=False, indent=2),
        )

    # --------------------------------------------------------
    # 6. Résumé des champs personnels : présence seulement
    # --------------------------------------------------------

    personal_rows = []

    for requested_col in SENSITIVE_COLUMNS:
        col = find_column(df, requested_col)

        if col:
            personal_rows.append({
                "colonne": col,
                "valeurs_renseignees": int(df[col].notna().sum()),
                "pourcentage_renseigne": round(
                    float(df[col].notna().mean() * 100), 2
                ),
            })

    save_csv(
        pd.DataFrame(personal_rows),
        "08_presence_champs_personnels.csv",
    )

    # --------------------------------------------------------
    # 7. Rapport lisible pour le rendez-vous
    # --------------------------------------------------------

    report = [
        "# Résumé de l'analyse exploratoire",
        "",
        f"- Fichier : `{INPUT_FILE.name}`",
        f"- Nombre de lignes : {n_rows}",
        f"- Nombre de colonnes : {n_cols}",
        f"- Doublons de lignes exactes : {df.duplicated().sum()}",
        f"- Cellules manquantes : {df.isna().sum().sum()}",
        "",
        "## Colonnes les plus incomplètes",
        "",
    ]

    most_missing = structure.sort_values(
        "pourcentage_manquant", ascending=False
    ).head(15)

    for _, row in most_missing.iterrows():
        report.append(
            f"- {row['colonne']} : {row['pourcentage_manquant']} % "
            f"de valeurs manquantes"
        )

    report += [
        "",
        "## Points d'attention",
        "",
        "- Vérifier la signification des champs de durée.",
        "- Confirmer les règles de calcul des interruptions.",
        "- Confirmer les règles métier pour la localisation, le secteur "
        "et la recherche.",
        "- Vérifier la correspondance entre codes étape, niveaux et spécialités.",
        "- Ne pas considérer automatiquement toute répétition de convention "
        "comme une erreur : une convention peut avoir été modifiée.",
        "- Examiner les colonnes constantes ou vides avant de les supprimer.",
        "",
        "Les résultats détaillés sont dans les fichiers CSV et JSON du dossier.",
    ]

    write_text("09_resume_pour_reunion.md", "\n".join(report))

    print("\nAnalyse terminée.")
    print(f"Résultats disponibles dans : {OUTPUT_DIR.resolve()}")


if __name__ == "__main__":
    main()