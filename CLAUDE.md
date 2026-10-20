# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Projet

Projet ingénieur Polytech Lille (IS5, ISIA, 2026-2027), mené seul par Augustin Mélon : **ObservaStage**, outil de valorisation et de suivi des données de stages extraites d'EsupStage, pour la Direction des Relations Entreprises (référente : Dorothée Bernard). Tout le projet (code, docs, livrables) est rédigé en français.

## Arborescence

```
donnees/          Extractions EsupStage (non versionnées, .gitignore)
exploration/      Scripts Python d'exploration + resultats/ (agrégats uniquement)
conception/       Document de conception + referentiels/ (tables de référence CSV)
app/              Application ObservaStage (PHP, PostgreSQL, Docker)
gestion-projet/   Contexte, comptes rendus de réunions (un dossier par réunion daté), planning
livrables/        Un dossier par jalon (rapport, support de soutenance)
```

## Commandes

Environnement virtuel Python 3.13 dans `venv/` (pandas, xlrd pour `.xls`, openpyxl pour `.xlsx`). Pas de tests ni de linter.

```powershell
.\venv\Scripts\python.exe exploration\analyse_exploratoire.py    # régénère exploration/resultats/00_ à 09_
.\venv\Scripts\python.exe exploration\indicateurs_reference.py   # régénère 10_indicateurs_reference.json
```

Les chemins sont calculés à partir de l'emplacement des scripts : ils se lancent depuis n'importe quel dossier.

Application (Docker requis, voir `app/README.md`) :

```powershell
cd app; docker compose up -d --build                             # http://localhost:8080
docker compose down -v; docker compose up -d                     # repartir d'une base vide
.\venv\Scripts\python.exe exploration\verifier_application.py    # indicateurs appli == référence (48/48)
.\venv\Scripts\python.exe exploration\generer_fichier_test.py    # fichier aux colonnes modifiées (donnees/tests/)
```

`app/public` et `app/src` sont montés en volume : les modifications PHP/JS sont prises en compte sans reconstruire. Les fichiers `app/sql/*.sql` ne sont chargés qu'à la création du volume (`down -v` pour les rejouer). `app/sql/02_referentiels.sql` est généré à partir de `conception/referentiels/ref_etape.csv` : garder les deux cohérents.

## Application (ObservaStage)

- `src/` : logique métier PHP (espace de noms `ObservaStage`, autoload Composer). `public/api/*.php` : API JSON. `public/index.html` + `assets/` : interface sans framework.
- Toutes les règles métier dérivées (retenu, durées, étranger, Hauts-de-France, recherche, type d'expérience) sont dans **la vue SQL `v_stage`**, pilotée par la table `parametre`. Indicateurs, recherche et exports passent par `Filtres::versSql()` : ne pas dupliquer une règle ailleurs.
- Toute modification d'une règle doit être reportée dans `exploration/indicateurs_reference.py`, puis vérifiée avec `verifier_application.py`.
- Mapping (`Mapping.php`) : profil (signature des en-têtes) → colonnes déjà vues → alias exacts normalisés → similarité (seuil 0,78, « à vérifier »). Normalisation PHP (`Texte::normaliser`) équivalente à `normalize_name()` en Python.
- Données nominatives : l'outil **inclut** les noms et e-mails universitaires des étudiants, enseignants référents et tuteurs (besoin de la DRE ; anonymisation seulement pour le développement). Minimisation : adresses, téléphones, e-mails personnels, sexe, signataire ne sont pas des champs cibles. Colonnes ignorées jamais lues ; fichier téléversé supprimé après import (purge après 1 h sinon). Authentification à ajouter avant toute mise en service.
- Type d'expérience : CDD, CDI, intérim, bénévolat par mots-clés ; **« Stage » si rien n'est précisé**.
- Suppression d'un import (`api/lot_supprimer.php`) : retire les conventions dont ce lot est la dernière version (cascade), puis les structures, étudiants et encadrants orphelins. Profils supprimables (`api/profils.php`).

## Livrables

`livrables/<date>_<jalon>/` : sources HTML (`rapport_intermediaire.html`, `support_soutenance.html`) et PDF générés par Chrome headless (impression, fonds activés). Figures dans `figures/` (captures de l'application en thème clair, `gantt.png` et `gantt_phases.png` copiés depuis `gestion-projet/planning/`). Le Gantt se modifie dans `gestion-projet/planning/gantt.html` : tableau `TACHES` (vue détaillée) et `PHASES` (vue simplifiée pour les slides, `gantt.html?vue=phases`). Captures : Chrome headless `--screenshot`, `--force-device-scale-factor=2`, fenêtre 1604×970 (détaillé) ou 1225×640 (phases).

Vocabulaire des livrables (remarques de la tutrice, 19/10) : « exploiter » (pas « explorer ») ; le contrôle est un **pré-traitement** inclus dans l'import ; « adaptabilité de la chaîne » (pas « difficulté centrale ») ; « **vérification** croisée » (jamais « validation croisée ») ; SIRET absent à l'étranger = particularité de la base, pas un défaut de qualité ; technologies **imposées** ; « chaîne complète de la donnée » (pas seulement l'ingestion) ; « V1 » (pas « ébauche »). Mettre en avant la compréhension du besoin et la gestion de projet plus que la technique.

## Scripts d'exploration

- Tout est lu en `dtype=str` (aucune conversion implicite : codes postaux, SIRET, N° étudiant gardent leurs zéros). Les chaînes vides deviennent `NA`.
- Les colonnes sont retrouvées via `find_column()` / `normalize_name()` (insensible aux accents, casse, ponctuation), car les en-têtes EsupStage varient (tirets courts/longs, apostrophes typographiques, casse).
- Dates au format `jj/mm/aaaa`, toujours parsées avec `format="%d/%m/%Y"`.
- `indicateurs_reference.py` applique les règles métier validées et produit les **chiffres de référence** auxquels ceux de l'application doivent être identiques (critère de validation).
- **Contrainte RGPD** : les sorties ne contiennent que des agrégats. Les colonnes de `SENSITIVE_COLUMNS` ne sont rapportées qu'en taux de remplissage, jamais en fréquences de valeurs ; aucune ligne individuelle n'est exportée. Conserver ce principe pour toute nouvelle analyse.

## Données

- `donnees/esupstage_2025-2026_anonymise.xls` : extraction EsupStage anonymisée 2025-2026 (1 feuille, 71 colonnes, 1053 lignes). Un fichier = une année universitaire. 2025-2026 est la première année d'EsupStage : **il n'existe aucune autre année**, les tests multi-années se font sur des fichiers modifiés à partir de celui-ci.
- Anonymisation seulement, aucune colonne supprimée. « Commentaire durée travail » est **non anonymisée** (version reçue le 12/10/2026, 164 lignes renseignées : CDD, CDI, intérim, bénévolat, horaires). « Commentaire stage » reste entièrement remplacée.
- En production, l'utilisatrice importera des fichiers **non anonymisés** : le code doit fonctionner à l'identique sur les deux.
- Pièges connus (détaillés dans `conception/conception_technique.md` §4) : les intitulés de colonnes ne garantissent pas le contenu (« Code NAF » contient un libellé), pas de colonne région / secteur / recherche (à dériver), niveau + spécialité fusionnés dans « Libellé étape » (correspondance 1:1 avec « Code étape », 48 valeurs, table `conception/referentiels/ref_etape.csv`), SIRET souvent vide (structures étrangères), colonne « Interruption » renseignée mais dates d'interruption toujours vides.

## Règles métier validées avec Dorothée Bernard (réunion du 12/10/2026)

Détail et chiffres : `gestion-projet/reunions/2026-10-12_dre/compte_rendu.md`.

- Indicateurs calculés **uniquement sur les conventions validées** (« Validation convention = Oui », 884 sur 1053) ; toutes sont importées, le filtre s'applique au calcul. **Précisée le 19/10, pas encore implémentée** (S44) : ne retirer que les **doublons non validés** (même N° étudiant, même année, avec au moins une convention validée) ; un étudiant sans convention validée garde toutes ses conventions → 1023. À ajouter comme valeur de `regle_validation`. Le sens de « doublon » reste à confirmer (26 des 30 conventions retirées ont une autre date de début). Détail : `gestion-projet/reunions/2026-10-19_dre/compte_rendu.md`.
- Durée moyenne affichée **en jours, semaines et heures**. « Durée du stage » est en heures (vérifié).
- Étranger = pays du **service d'accueil**. Hauts-de-France = département du code postal ∈ {02, 59, 60, 62, 80}.
- Recherche = mots-clés « recherche » / « R&D » **dans le sujet uniquement** (confirmé le 19/10). Secteur = **Thématique**.
- Critères de validation du prototype validés le 19/10 (import autonome, indicateurs = référence, recherche, export). Code étape 2XIGAP (classé L2) en attente de confirmation par mail.
- Type d'expérience (CDD, CDI, intérim, bénévolat) : mots-clés dans « Commentaire durée travail ».
- Toutes les formations sont suivies (PEIP, L, M, cycle ingénieur…).
- Valeurs aberrantes (ex. heures hebdo ≤ 0 ou > 48) : signalées et exclues des calculs, jamais corrigées.
- N° de convention pas forcément unique entre années → clé d'import (année universitaire, N° de convention).
- Export CSV et XLS des résultats : **indispensable** en V1. Utilisatrice unique : Dorothée Bernard.

## Architecture cible (actée en réunion technique du 29/09/2026)

```
Fichiers EsupStage → Module d'import → Mapping configurable → Nettoyage/contrôle → PostgreSQL → API JSON (PHP) → Interface Web (HTML/JS)
```

- Stack **imposée** : PHP, HTML, JavaScript, PostgreSQL (standard des applications Polytech). Python ne sert qu'à l'exploration.
- Difficulté centrale : les colonnes des extractions changent d'une année à l'autre → import par **mapping configurable stocké en base** (profils de mapping, alias, dictionnaires de valeurs), sans modification du code.
- Import idempotent sur (année universitaire, N° de convention) ; minimisation des données (colonnes ignorées non stockées, staging purgé).
- Modèle de données, tables techniques d'import et mapping proposé des 71 colonnes : `conception/conception_technique.md` (§5, §6, annexe A). Les éléments marqués **[Proposition]** ne sont pas encore validés.

## Documents de référence

- `gestion-projet/contexte_projet.md` : cadre du projet, jalons, livrables, contacts.
- `conception/conception_technique.md` : document de conception de référence (constats sur les données, architecture, modèle, points ouverts).
- `gestion-projet/reunions/2026-10-12_dre/compte_rendu.md` : règles métier validées, périmètre V1, chiffres. Prévaut sur `conception_technique.md` en cas de contradiction.
- `gestion-projet/reunions/<date>_<objet>/` : `preparation.md`, `compte_rendu.md`, supports.
