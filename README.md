# ObservaStage

Outil de valorisation et de suivi des données de stages de Polytech Lille, à partir des extractions EsupStage.

Projet ingénieur IS5 ISIA 2026-2027 — Augustin Mélon.
Commanditaire : Direction des Relations Entreprises (Dorothée Bernard). Tutrice école : Marie-Éléonore Kessaci. Expert technique : Bastien Cazaux.

## Contenu du dépôt

| Dossier | Contenu |
|---|---|
| `app/` | Application ObservaStage (PHP, PostgreSQL, Docker) — voir `app/README.md` |
| `conception/` | Document de conception, tables de référence (`referentiels/`) |
| `exploration/` | Scripts Python d'analyse exploratoire et indicateurs de référence ; `resultats/` (agrégats uniquement) |
| `gestion-projet/` | Contexte du projet, comptes rendus de réunions, planning (Gantt) |
| `livrables/` | Rapports et supports de soutenance, un dossier par jalon |
| `donnees/` | Extractions EsupStage — **non versionné** (données personnelles) |

## Exploration

```powershell
.\venv\Scripts\python.exe exploration\analyse_exploratoire.py
.\venv\Scripts\python.exe exploration\indicateurs_reference.py
```
