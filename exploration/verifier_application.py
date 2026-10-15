"""Compare les indicateurs de l'application aux chiffres de référence.

Critère de validation : les indicateurs de l'application sont identiques
aux chiffres calculés indépendamment par indicateurs_reference.py.

Prérequis : application démarrée (docker compose up) et fichier importé.
Usage : python exploration/verifier_application.py [URL de l'application]
"""

import json
import sys
import urllib.request

from analyse_exploratoire import OUTPUT_DIR

URL = sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8080"


def main():
    reference = json.loads((OUTPUT_DIR / "10_indicateurs_reference.json").read_text(encoding="utf-8"))
    with urllib.request.urlopen(f"{URL}/api/indicateurs.php") as response:
        app = json.load(response)

    g = app["global"]
    checks = [
        ("Conventions retenues", reference["conventions_retenues"], g["stages"]),
        ("Durée moyenne (jours)", reference["duree_moyenne"]["jours"], g["jours"]),
        ("Durée moyenne (semaines)", reference["duree_moyenne"]["semaines"], g["semaines"]),
        ("Durée moyenne (heures)", reference["duree_moyenne"]["heures"], g["heures"]),
        ("Stages à l'étranger", reference["etranger"]["stages"], g["etranger"]),
        ("Stages en Hauts-de-France", reference["hauts_de_france"]["stages"], g["hauts_de_france"]),
        ("Stages « recherche »", reference["recherche_dans_sujet"], g["recherche"]),
    ]
    app_levels = {n["niveau"]: n for n in app["par_niveau"]}
    for level in reference["par_niveau"]:
        other = app_levels.get(level["niveau"], {})
        for key in ["stages", "jours", "semaines", "heures"]:
            checks.append((f"{level['niveau']} : {key}", level[key], other.get(key)))
    app_experience = {e["libelle"]: e["stages"] for e in app["type_experience"]}
    for label, count in reference["type_experience_commentaire"].items():
        checks.append((f"Type d'expérience : {label}", count, app_experience.get(label)))

    failures = 0
    for label, expected, actual in checks:
        ok = actual is not None and float(expected) == float(actual)
        failures += not ok
        print(f"{'OK ' if ok else 'ÉCART'}  {label:<35} référence={expected!s:<8} application={actual}")

    print(f"\n{len(checks) - failures}/{len(checks)} indicateurs identiques.")
    sys.exit(1 if failures else 0)


if __name__ == "__main__":
    main()
