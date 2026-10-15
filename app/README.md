# ObservaStage — application

Ébauche technique (octobre 2026) : import par mapping configurable, contrôles et rapport d'anomalies, tableau de bord d'indicateurs, recherche multicritère, export CSV / XLS.

## Lancer

Prérequis : Docker Desktop.

```powershell
cd app
docker compose up -d --build
```

- Application : http://localhost:8080
- PostgreSQL : `localhost:5432`, base / utilisateur / mot de passe `observastage` (développement uniquement).

Le schéma (`sql/01_schema.sql`) et les référentiels (`sql/02_referentiels.sql`) sont chargés à la création du volume. Pour repartir d'une base vide :

```powershell
docker compose down -v; docker compose up -d
```

## Architecture

```
public/index.html, assets/   Interface web (HTML, CSS, JavaScript sans framework)
public/api/*.php             API JSON consommée par l'interface
src/                         Logique métier (PHP, espace de noms ObservaStage)
  LecteurFichier.php         Lecture .xls/.xlsx/.csv (PhpSpreadsheet), tout en texte
  Mapping.php                Profils de mapping, suggestion (profil connu, colonnes déjà vues, alias, similarité)
  Importeur.php              Conversion, contrôles, upsert idempotent, anomalies
  Filtres.php                Critères communs à la recherche, aux indicateurs et aux exports
  Indicateurs.php, Recherche.php, Export.php
sql/                         Schéma, vue d'analyse v_stage, référentiels et paramètres
```

Les règles métier (validation retenue, seuil d'heures, départements des Hauts-de-France, mots-clés « recherche » et type d'expérience) sont dans la table `parametre` : elles se modifient sans toucher au code.

## Vérifier les indicateurs

Après import du fichier 2025-2026 :

```powershell
.\venv\Scripts\python.exe exploration\indicateurs_reference.py
.\venv\Scripts\python.exe exploration\verifier_application.py
```

## Limites de l'ébauche

- Pas d'authentification (prévue avant toute utilisation sur données réelles).
- Encadrants (enseignant référent, tuteur) non importés.
- Pas d'écran d'administration des paramètres et des référentiels (modification en base).
- Rapprochement des structures sans SIRET : nom normalisé exact uniquement (pas encore de similarité `pg_trgm`).
