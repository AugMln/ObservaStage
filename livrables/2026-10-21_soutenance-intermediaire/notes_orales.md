# Notes orales : soutenance intermédiaire du 21/10/2026

10 minutes de présentation, puis 5 minutes de questions. Le public (jury de plusieurs tuteurs, autres étudiants) ne connaît pas le sujet : partir du problème, pas de la technique. Texte complet, slide par slide : [texte_oral.md](texte_oral.md).

Fil rouge, d'après la soutenance blanche du 19/10 : **j'ai compris le besoin, je pilote le projet, l'IA est un outil encadré**. La technique vient en appui.

## Minutage

| Slide | Contenu | Durée | Cumul |
|---|---|---|---|
| 1 | Titre | 0:15 | 0:15 |
| 2 | Contexte | 0:45 | 1:00 |
| 3 | **Comprendre le besoin** | 1:00 | 2:00 |
| 4 | Objectif, chaîne adaptable | 0:35 | 2:35 |
| 5 | Les données | 0:40 | 3:15 |
| 6 | Cahier des charges | 0:40 | 3:55 |
| 7 | Chaîne de la donnée, mapping | 1:00 | 4:55 |
| 8 | Fiabilité, RGPD | 0:40 | 5:35 |
| 9 | **Démo en direct** | 1:30 | 7:05 |
| 10 | **Gestion de projet** | 0:50 | 7:55 |
| 11 | Planning (Gantt simplifié) | 0:30 | 8:25 |
| 12 | **Usage de l'IA** | 0:50 | 9:15 |
| 13 | Suite, data engineer | 0:40 | 9:55 |
| Annexes | Gantt détaillé, architecture détaillée | si question | |

Repère : si j'arrive à la slide 9 après 6:00, je raccourcis la démo (tableau de bord + mapping seulement).

## Vocabulaire (remarques de Mme Kessaci)

- Dire **exploiter**, pas « explorer ».
- Le contrôle est un **pré-traitement**, qui fait partie de l'**import**.
- Parler d'**adaptabilité** de la chaîne, pas de « difficulté centrale ».
- **Vérification croisée**, jamais « validation croisée » (terme de statistiques).
- SIRET absent pour l'étranger : une **particularité de la base**, pas un défaut de qualité.
- Les technologies m'ont été **imposées**.
- Le cœur du projet est la **chaîne complète de la donnée**, pas seulement l'ingestion.
- L'outil est une **V1 fonctionnelle**, pas une « ébauche ».

## Ce que je dis

**1. Titre (0:15).** « Bonjour, je présente ObservaStage, un outil pour valoriser les données de stages de Polytech, commandé par la Direction des Relations Entreprises. »

**2. Contexte (0:45).**
- La DRE gère environ 1 000 conventions par an, toutes formations confondues.
- EsupStage gère une convention, pas l'ensemble : la seule sortie est un Excel de 71 colonnes.
- 80 % des questions restent sans réponse, 30 minutes pour les autres.
- Phrase clé : « Les données existent, mais personne ne peut les exploiter. »

**3. Comprendre le besoin (1:00).**
- Partir de ses vraies questions. Exemple : « combien à l'étranger ? » → pays du siège ou du lieu du stage ?
- Chaque question cache une règle implicite. Je chiffre les options, elle tranche.
- 3 réunions, chacune préparée et suivie d'un compte rendu. 14 règles validées, périmètre priorisé, critères validés le 19/10.

**4. Objectif (0:35).**
- Importer (avec pré-traitement), structurer, exploiter.
- Utilisatrice non informaticienne, autonome.
- Force de l'outil : **adaptable**, tout s'ajuste par paramétrage, sans toucher au code.

**5. Données (0:40).** Intitulés trompeurs ; informations à dériver (niveau, spécialité) ; particularité : pas de SIRET à l'étranger ; qualité hétérogène (175 h/semaine) ; aucune autre année → fichiers de test modifiés.

**6. Cahier des charges (0:40).**
- Périmètre priorisé avec la DRE ; export CSV/XLS ajouté à sa demande.
- Critères de validation **validés par elle le 19/10**.
- Technologies imposées par l'école, RGPD.

**7. Chaîne de la donnée (1:00).**
- Import = lecture + mapping + pré-traitement → PostgreSQL → API → interface. Technologies imposées.
- Le mapping rend la chaîne adaptable : profil connu, colonne déjà vue, alias, similarité « à vérifier ».
- Fichier de test : 34 reconnues, 2 à vérifier, 3 nouvelles. « Seules les différences sont à traiter. »

**8. Fiabilité (0:40).**
- Règles validées ; règle des doublons précisée le 19/10, appliquée en S44.
- **Vérification croisée** : 48 sur 48 identiques.
- RGPD : minimisation, fichier supprimé, authentification.

**9. Démo (1:30).** Voir le script ci-dessous.

**10. Gestion de projet (0:50).**
- Démarche itérative, validation à chaque étape (frise).
- Cycle de réunion : préparation → compte rendu → actions. Mail hebdo, Trello.
- Priorisation : je coupe dans le souhaitable, jamais dans l'indispensable.
- « Aujourd'hui, je suis en avance : la V1 va de l'import à l'export. »

**11. Planning (0:30).** Cadrage et V1 faits ; consolidation, souhaitable, hébergement (risque principal), tests début décembre ; démo 08/01, soutenance 29/01. Détail en annexe.

**12. IA (0:50).**
- Cadrer avant : fichier de contexte, décisions prises avec la DRE avant le code, une tâche à la fois, plan et questions d'abord. Montrer la consigne réelle.
- Vérifier après : double calcul, relecture avant chaque commit, tests.
- Garder la main : base anonymisée seulement, décisions à moi.
- « Le temps gagné est réinvesti dans le besoin et la vérification. »

**13. Suite (0:40).**
- Recadrage à la hausse et à la baisse.
- Doublons, rapport d'import, authentification, DSI, tests.
- **Data engineer** : la chaîne complète de la donnée, du fichier brut à l'indicateur.
- « Merci, je suis prêt pour vos questions. »

## Script de démo (1:30)

Préparation, **avant** la séance :
1. `cd app ; docker compose up -d`, puis vérifier http://localhost:8080.
2. Base remplie avec le fichier 2025-2026 (déjà fait ; sinon onglet Import, valider, Importer).
3. Générer le fichier de test s'il n'existe pas : `.\venv\Scripts\python.exe exploration\generer_fichier_test.py`.
4. Navigateur en plein écran, zoom 125 %, onglet Tableau de bord ouvert.
5. **Plan B** : la vidéo `demo_observastage.mp4` ouverte dans un lecteur, prête à lancer (voir [video_demo.md](video_demo.md)). Plan C : commenter la capture de la slide 9.

Déroulé :
1. **Tableau de bord** (30 s) : « 884 stages validés, 14,4 semaines en moyenne, un quart à l'étranger. » Choisir Niveau = 4A : « 60 % à l'étranger en 4A ».
2. **Recherche** (25 s) : Localisation = Étranger, mot-clé « robot » → 8 stages. Cliquer sur XLS : le fichier se télécharge.
3. **Import** (35 s) : choisir `donnees/tests/esupstage_test_colonnes_modifiees.xlsx`, puis Analyser. « L'outil reconnaît 34 colonnes, m'en propose 2 à vérifier, et 3 sont nouvelles. Domaine d'activité → Thématique, Temps de travail hebdo → Heures hebdomadaires. » Ne pas importer.

## Ne pas montrer

- Les noms apparaissent dans la recherche (étudiants pseudonymisés, enseignants réels mais déjà publics au sein de l'école). Rester sur le tableau de bord et l'écran de mapping si possible.
