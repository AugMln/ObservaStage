# Notes orales : soutenance intermédiaire du 21/10/2026

10 minutes de présentation, puis 5 minutes de questions. Le public (jury de plusieurs tuteurs, autres étudiants) ne connaît pas le sujet : partir du problème, pas de la technique.

## Minutage

| Slide | Contenu | Durée | Cumul |
|---|---|---|---|
| 1 | Titre | 0:15 | 0:15 |
| 2 | Contexte | 0:55 | 1:10 |
| 3 | Objectif, difficulté centrale | 0:40 | 1:50 |
| 4 | Les données | 0:50 | 2:40 |
| 5 | Cahier des charges | 0:55 | 3:35 |
| 6 | Architecture | 0:35 | 4:10 |
| 7 | Mapping configurable | 1:00 | 5:10 |
| 8 | Fiabilité, RGPD | 0:45 | 5:55 |
| 9 | **Démo en direct** | 1:45 | 7:40 |
| 10 | Avancement, difficultés | 0:50 | 8:30 |
| 11 | Gantt | 0:40 | 9:10 |
| 12 | Recadrage, suite, ISIA | 0:45 | 9:55 |

Repère : si j'arrive à la slide 9 après 6:15, je raccourcis la démo (tableau de bord + mapping seulement).

## Ce que je dis

**1. Titre (0:15).** « Bonjour, je présente ObservaStage, un outil pour valoriser les données de stages de Polytech, commandé par la Direction des Relations Entreprises. »

**2. Contexte (0:55).**
- La DRE gère les conventions de stage de toutes les formations : un peu plus de 1 000 par an.
- Depuis l'an dernier, tout passe par EsupStage, l'outil national. Il gère très bien une convention, mais pas l'ensemble : la seule sortie est un fichier Excel de 71 colonnes.
- Conséquence : 80 % des questions posées à la DRE restent sans réponse, et chaque réponse demande environ 30 minutes de manipulations.
- Phrase clé : « Les données existent, mais personne ne peut les exploiter. »

**3. Objectif (0:40).**
- Importer, contrôler, structurer, explorer.
- L'utilisatrice n'est pas informaticienne.
- Difficulté centrale : le format du fichier change d'une année à l'autre, donc l'import doit s'adapter sans toucher au code.
- Montrer les exemples de questions en bas de la slide : c'est ce qu'on veut pouvoir répondre en quelques clics.

**4. Données (0:50).** J'ai d'abord analysé la base réelle anonymisée. Quatre pièges :
- les intitulés sont trompeurs : même pas cohérents dans un seul fichier, et « Code NAF » ne contient pas un code ;
- il faut dériver le niveau, la région, le secteur ;
- la qualité est hétérogène (jusqu'à 175 h par semaine) ;
- surtout, il n'existe aucune autre année : c'est la première année d'EsupStage. Je ne peux donc pas tester l'évolution sur du réel, d'où des fichiers de test modifiés.

**5. Cahier des charges (0:55).**
- Périmètre validé avec la DRE le 12/10 : 5 fonctionnalités indispensables, dont l'export CSV/XLS, ajouté par Dorothée Bernard.
- Critères de validation, mesurables : import autonome, indicateurs identiques à une référence, recherche, export.
- Contraintes : stack imposée (standard de l'école), RGPD (en production, les fichiers seront nominatifs).

**6. Architecture (0:35).**
- Une chaîne classique de traitement de données. La brique clé est le mapping.
- Les règles métier sont des paramètres en base, pas du code.
- L'import est idempotent : réimporter ne crée pas de doublon.

**7. Mapping (1:00), le cœur technique.**
- Quatre niveaux, du plus sûr au moins sûr : profil connu, colonne déjà vue, alias, similarité.
- L'utilisatrice valide avec des exemples de valeurs.
- Sur le fichier de test : sur 69 colonnes, 34 sont reconnues, 2 seulement sont à vérifier et 3 sont nouvelles. « Seules les différences sont à traiter. »

**8. Fiabilité (0:45).**
- Chaque règle est explicite et validée.
- Pour être sûr des chiffres : un script Python indépendant recalcule tout, et un second compare. 48 sur 48 identiques.
- RGPD : seules les colonnes utiles sont stockées (pas d'adresses ni de téléphones), accès réservé à la DRE avec authentification.

**9. Démo (1:45).** Voir le script ci-dessous.

**10. Avancement (0:50).**
- Frise : cadrage, réunion technique, analyse, règles métier, ébauche.
- Organisation : Trello, mail hebdo, comptes rendus, Git.
- Difficultés : accès aux données, pas d'historique, données sales.
- « Aujourd'hui, je suis en avance : l'ébauche va déjà de l'import à l'export. »

**11. Gantt (0:40).**
- Montrer la ligne rouge du 21/10.
- Novembre et début décembre : consolidation et déploiement. Tests avec la DRE début décembre. Rapport le 18/12, démo le 08/01, soutenance le 29/01.
- Insister sur le contact DSI dès S44.

**12. Suite (0:45).**
- Recadrage à la hausse (fonctionnalités souhaitables) et à la baisse (type d'expérience, partiel).
- Métier : **data engineer**. Le cœur est un pipeline d'ingestion de données dont le schéma évolue, avec qualité, modélisation et RGPD.
- « Merci, je suis prêt pour vos questions. »

## Script de démo (1:45)

Préparation, **avant** la séance :
1. `cd app ; docker compose up -d`, puis vérifier http://localhost:8080.
2. Base remplie avec le fichier 2025-2026 (déjà fait ; sinon onglet Import, valider, Importer).
3. Générer le fichier de test s'il n'existe pas : `.\venv\Scripts\python.exe exploration\generer_fichier_test.py`.
4. Navigateur en plein écran, zoom 125 %, onglet Tableau de bord ouvert.
5. **Plan B** : si la démo ne marche pas, rester sur la slide 9 et commenter la capture.

Déroulé :
1. **Tableau de bord** (30 s) : « 884 stages validés, 14,4 semaines en moyenne, un quart à l'étranger. » Choisir Niveau = 4A : « 60 % à l'étranger en 4A ».
2. **Recherche** (30 s) : Localisation = Étranger, mot-clé « robot » → 8 stages. Cliquer sur XLS : le fichier se télécharge.
3. **Import** (45 s) : choisir `donnees/tests/esupstage_test_colonnes_modifiees.xlsx`, puis Analyser. « Colonnes renommées et déplacées : l'outil reconnaît 34 colonnes, m'en propose 2 à vérifier, et 3 sont nouvelles. Je les associe : Domaine d'activité → Thématique, Temps de travail hebdo → Heures hebdomadaires. » Option, si le temps le permet : cliquer sur Importer, montrer l'année 2026/2027 dans les filtres, puis **supprimer l'import** dans l'historique et **supprimer le profil** créé, pour remettre la démo dans son état initial. Sinon, ne pas importer.

## Ne pas montrer

- Les noms apparaissent dans la recherche (étudiants pseudonymisés, enseignants réels mais déjà publics au sein de l'école). Rester sur le tableau de bord et l'écran de mapping si possible.
