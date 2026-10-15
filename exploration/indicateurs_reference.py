"""Calcul des indicateurs de référence selon les règles métier validées.

Ces chiffres servent de référence pour vérifier ceux de l'application
(critère de validation : indicateurs identiques aux calculs de référence).
Seuls des agrégats sont produits (contrainte RGPD).
"""

from pathlib import Path
import json
import re

import pandas as pd

from analyse_exploratoire import INPUT_FILE, OUTPUT_DIR, find_column, load_data, normalize_name


# ============================================================
# RÈGLES MÉTIER (CR du 12/10/2026)
# ============================================================

ROOT_DIR = Path(__file__).resolve().parent.parent
REF_ETAPE_FILE = ROOT_DIR / "conception" / "referentiels" / "ref_etape.csv"

# Conventions retenues : « Validation convention = Oui ».
VALIDATION_COLUMNS = ["Validation convention"]

DEPARTEMENTS_HDF = {"02", "59", "60", "62", "80"}
HEURES_HEBDO_MAX = 48

MOTS_CLES_RECHERCHE = ["recherche", "r&d", "r & d"]

# Type d'expérience, détecté dans « Commentaire durée travail ».
MOTS_CLES_EXPERIENCE = {
    "CDD": [r"\bcdd\b"],
    "CDI": [r"\bcdi\b"],
    "Intérim": [r"\binterim\b"],
    "Bénévolat": [r"benevol", r"woof"],
}

ORDRE_NIVEAUX = ["PEIP / L1", "L2", "L3", "3A", "M1", "4A", "M2", "5A", "Autres"]


def to_number(series):
    return pd.to_numeric(
        series.astype("string").str.replace(",", ".", regex=False),
        errors="coerce",
    )


def contains_any(series, patterns):
    text = series.fillna("").map(normalize_name_keep_symbols)
    mask = pd.Series(False, index=series.index)
    for pattern in patterns:
        mask |= text.str.contains(pattern, regex=True)
    return mask


def normalize_name_keep_symbols(value):
    """Minuscules sans accents, en gardant « & » (pour « R&D »)."""
    return " ".join(
        normalize_name(part) if part != "&" else "&"
        for part in re.split(r"(&)", str(value))
    ).replace(" & ", "&")


def round1(value):
    return None if pd.isna(value) else round(float(value), 1)


def main():
    df = load_data(INPUT_FILE)
    col = {name: find_column(df, name) for name in [
        "Validation convention", "Code étape", "Date début du stage",
        "Date fin du stage", "Durée du stage", "Nbre d'heures hebdomadaire",
        "Service d'accueil - Pays", "Service d'accueil - Code postal",
        "Sujet", "Commentaire durée travail", "Thématique",
    ]}
    missing = [name for name, found in col.items() if found is None]
    if missing:
        raise KeyError(f"Colonnes introuvables : {missing}")

    total = len(df)
    validated = pd.Series(False, index=df.index)
    for name in VALIDATION_COLUMNS:
        validated |= df[find_column(df, name)].eq("Oui")
    df = df[validated].copy()

    ref = pd.read_csv(REF_ETAPE_FILE, dtype=str)
    df = df.merge(ref[["code_etape", "niveau", "specialite"]],
                  left_on=col["Code étape"], right_on="code_etape", how="left")
    df["niveau"] = df["niveau"].fillna("Inconnu")

    # Durées : jours calendaires inclusifs, heures déclarées.
    start = pd.to_datetime(df[col["Date début du stage"]], format="%d/%m/%Y", errors="coerce")
    end = pd.to_datetime(df[col["Date fin du stage"]], format="%d/%m/%Y", errors="coerce")
    df["jours"] = (end - start).dt.days + 1
    df["semaines"] = df["jours"] / 7

    hebdo = to_number(df[col["Nbre d'heures hebdomadaire"]])
    declared = to_number(df[col["Durée du stage"]])
    anomalies = {
        "heures_hebdo_manquantes": int(hebdo.isna().sum()),
        f"heures_hebdo_superieures_{HEURES_HEBDO_MAX}": int((hebdo > HEURES_HEBDO_MAX).sum()),
        "heures_hebdo_nulles_ou_negatives": int((hebdo <= 0).sum()),
        "duree_declaree_nulle": int((declared == 0).sum()),
        "dates_invalides_ou_fin_avant_debut": int((df["jours"].isna() | (df["jours"] <= 0)).sum()),
    }
    hours_ok = hebdo.between(0, HEURES_HEBDO_MAX, inclusive="right") & (declared > 0)
    df["heures"] = declared.where(hours_ok)

    # Localisation : service d'accueil.
    pays = df[col["Service d'accueil - Pays"]].fillna("").str.strip().str.upper()
    cp = df[col["Service d'accueil - Code postal"]].fillna("").str.strip()
    df["etranger"] = pays.ne("FRANCE") & pays.ne("")
    df["hdf"] = pays.eq("FRANCE") & cp.str[:2].isin(DEPARTEMENTS_HDF)

    df["recherche"] = contains_any(df[col["Sujet"]], [re.escape(k) for k in MOTS_CLES_RECHERCHE])

    comment = df[col["Commentaire durée travail"]]
    experience = {
        label: int(contains_any(comment, patterns).sum())
        for label, patterns in MOTS_CLES_EXPERIENCE.items()
    }

    def duree(frame):
        return {
            "jours": round1(frame["jours"].mean()),
            "semaines": round1(frame["semaines"].mean()),
            "heures": round1(frame["heures"].mean()),
        }

    by_level = []
    for niveau in ORDRE_NIVEAUX + ["Inconnu"]:
        part = df[df["niveau"] == niveau]
        if part.empty:
            continue
        by_level.append({
            "niveau": niveau,
            "stages": int(len(part)),
            **duree(part),
            "etranger_pct": round1(part["etranger"].mean() * 100),
        })

    n = len(df)
    result = {
        "fichier": INPUT_FILE.name,
        "regle_validation": " OU ".join(f"« {c} = Oui »" for c in VALIDATION_COLUMNS),
        "conventions_total": total,
        "conventions_retenues": n,
        "duree_moyenne": duree(df),
        "duree_mediane": {
            "jours": round1(df["jours"].median()),
            "semaines": round1(df["semaines"].median()),
            "heures": round1(df["heures"].median()),
        },
        "etranger": {"stages": int(df["etranger"].sum()), "pct": round1(df["etranger"].mean() * 100)},
        "hauts_de_france": {
            "stages": int(df["hdf"].sum()),
            "pct_total": round1(df["hdf"].mean() * 100),
            "pct_france": round1(df["hdf"].sum() / max((~df["etranger"]).sum(), 1) * 100),
        },
        "recherche_dans_sujet": int(df["recherche"].sum()),
        "type_experience_commentaire": experience,
        "commentaire_duree_renseigne": int(comment.notna().sum()),
        "pays_etrangers_top10": pays[df["etranger"]].value_counts().head(10).to_dict(),
        "thematiques_top10": df[col["Thématique"]].value_counts().head(10).to_dict(),
        "par_niveau": by_level,
        "anomalies_exclues_des_heures": anomalies,
    }

    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    (OUTPUT_DIR / "10_indicateurs_reference.json").write_text(
        json.dumps(result, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    print(json.dumps(result, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
