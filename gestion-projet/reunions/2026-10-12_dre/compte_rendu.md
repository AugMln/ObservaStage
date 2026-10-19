# Compte rendu de la réunion du 12/10/2026

Projet ingénieur 2026-2027 : Conception d'un outil de valorisation et de suivi des données de stages
Participants : Dorothée Bernard (Direction des Relations Entreprises), Augustin Mélon
Objet : contexte de la DRE, règles métier pour les indicateurs, périmètre de la V1, préparation de la soutenance intermédiaire du 21/10/2026

Les chiffres cités portent sur la base anonymisée `base-anonym.xls` (extraction 2025-2026, 1053 conventions, 71 colonnes). Ils ont été calculés après la réunion pour répondre aux questions soulevées.

---

## 1. Contexte de la DRE

- **Organisation** : Dorothée Bernard gère seule les conventions de stage. Elle est entourée de 2 personnes qui aident les étudiants à trouver des stages, de 10 assistants pédagogiques et d'un responsable de stage par code étape.
- **Situation actuelle** : il n'existe aucun outil ni travail antérieur. **80 % des questions** posées à la DRE sur les stages restent sans réponse faute d'outil. Pour les 20 % restants, Dorothée fait elle-même une dizaine d'extractions, à raison d'environ **30 minutes par extraction**.
- **Bénéfice attendu** : une vision globale des stages et des réponses beaucoup plus rapides.
- **Historique** : 2025-2026 est la **première année d'utilisation d'EsupStage**. Il n'existe donc pas d'extraction d'une autre année.

## 2. Décisions sur les règles métier

| # | Sujet | Décision |
|---|---|---|
| 1 | Conventions prises en compte | **Uniquement les conventions validées**, au moins administrativement (« Validation convention = Oui »). Les autres sont exclues des indicateurs. |
| 2 | Durée moyenne | Calculée et affichée **en jours, en semaines et en heures**. |
| 3 | Unité de « Durée du stage » | Des heures, ce que les données confirment (cohérent avec semaines × heures hebdomadaires). |
| 4 | Étranger | Pays du **service d'accueil**. |
| 5 | Hauts-de-France | Déduit du **département du code postal** (02, 59, 60, 62, 80). |
| 6 | Stage « recherche » | Recherche des termes « recherche » et « R&D » **uniquement dans le sujet du stage**. |
| 7 | Secteur | La **Thématique** suffit. |
| 8 | CDD / intérim / bénévolat | L'information se trouve dans la colonne **« Commentaire durée travail »**, qui est anonymisée dans la base de test. Il faut demander la colonne non anonymisée à Mme Kessaci. |
| 9 | Périmètre des formations | **Toutes les formations** sont suivies (PEIP, L1 à L3, M1, M2, Mastère, cycle ingénieur, cycle RI). |
| 10 | Niveau et spécialité | Table de correspondance entre code étape et libellé étape, d'où on déduit le niveau et la spécialité. |
| 11 | Valeurs aberrantes | **Signalées** dans le rapport d'anomalies et **exclues des calculs**. |
| 12 | Préparation du fichier | La base a seulement été anonymisée : aucune colonne n'a été supprimée. |
| 13 | Unicité du N° de convention | **Pas garantie** d'une année sur l'autre. |
| 14 | Utilisateurs | Dorothée Bernard seule pour l'instant. Les autres profils seront étudiés plus tard. |

## 3. Périmètre de la V1

Le classement proposé est validé, avec une modification : l'**export des résultats en CSV et en XLS devient indispensable**.

| Priorité | Fonctionnalité |
|---|---|
| **Indispensable** | Import d'un fichier EsupStage (.xls) avec mapping configurable et profil réutilisable |
| **Indispensable** | Contrôles à l'import et rapport d'anomalies |
| **Indispensable** | Indicateurs : nombre de stages, durée moyenne (jours, semaines, heures), France/étranger, Hauts-de-France, par spécialité, par niveau et par année |
| **Indispensable** | Recherche multicritère |
| **Indispensable** | **Export des résultats en CSV et XLS** |
| Souhaitable | Historique sur plusieurs années |
| Souhaitable | Recherche plein texte dans le sujet et les fonctions et tâches |
| Souhaitable | Graphiques |
| Souhaitable | Rapprochement des structures sans SIRET |
| Bonus | Indicateurs « recherche » et « secteur » |
| Bonus | Type d'expérience (CDD, intérim, bénévolat), selon le contenu de la colonne non anonymisée |
| Hors V1 | Gratification, gestion de plusieurs profils d'utilisateurs |

**Livrables** : en plus du prototype, une **documentation** et un **guide utilisateur** sont attendus.

**Critères de validation** : pas abordés en réunion. Une proposition sera envoyée par mail à Dorothée Bernard (section 6).

## 4. Réponses aux questions soulevées en réunion

### 4.1 Un type de convention est-il majoritairement vide ?

Non. Le taux de cellules vides est le même pour tous les types de convention :

| Type convention | Conventions | Cellules vides en moyenne |
|---|---|---|
| FORMATION INITIALE | 1041 | 17,2 % |
| FORMATION CONT. REPRISE D'ETU. FINANCEES | 10 | 15,4 % |
| Apprentissage | 2 | 16,2 % |

Il n'y a pas non plus d'écart notable entre conventions validées (17,0 %) et non validées (17,9 %). Les vides se concentrent dans quelques colonnes (7 entièrement vides, plus Site web, Détail du projet, Gratification), quel que soit le type de convention.

### 4.2 Y a-t-il un lien entre avenant et interruption ?

Le lien est faible :

| | Interruption = Oui | Interruption = Non | Interruption vide | Total |
|---|---|---|---|---|
| Avenant = Oui | 2 | 11 | 75 | 88 |
| Avenant = Non | 35 | 59 | 871 | 965 |

- Sur 37 stages avec interruption, **seulement 2 ont un avenant**.
- 75 des 88 avenants concernent des stages où le champ Interruption est vide.
- Les dates d'interruption sont vides pour tous les stages : on ne peut pas mesurer les interruptions.

Les avenants ont donc probablement d'autres motifs (changement de dates, de tuteur ou de sujet), que le fichier ne précise pas.

### 4.3 Lien entre code étape et libellé étape

La correspondance est exacte : **48 codes étape et 48 libellés, un pour un**. La table de référence code étape → libellé → niveau → spécialité peut donc être construite à partir du fichier lui-même. Les niveaux sont déduits des préfixes du libellé (« L1 », « 1an Ing », « 2an Ing », « 3an Ing », « M2 »…).

## 5. Effet des décisions sur les chiffres

### 5.1 Exclusion des conventions non validées

- **884 conventions validées** sur 1053, pour 811 étudiants.
- **Point d'attention** : sur les 169 conventions non validées, **106 concernent la PEIP / L1**, soit environ **64 % des stages de PEIP exclus**. La plupart commencent en juin (84) et juillet (47), c'est-à-dire juste avant ou après l'extraction (dernière modification le 13/07/2026). 139 d'entre elles sont déjà validées pédagogiquement.
- Il est donc probable qu'elles étaient **en cours de validation au moment de l'extraction**, et non abandonnées. Il faut le confirmer avec Dorothée Bernard. Sinon, les indicateurs de la PEIP seront fortement sous-estimés pour 2025-2026.

### 5.2 Indicateurs recalculés (conventions validées uniquement)

- **Durée moyenne** : **101 jours**, soit **14,4 semaines** (médianes : 96 jours, 13,7 semaines). Durée déclarée : **494 heures** en moyenne (médiane 455 h).
- **Étranger** (pays du service d'accueil) : **218 stages (24,7 %)**, France : 666.
- **Hauts-de-France** (code postal du service d'accueil) : **346 stages (39 % du total, 52 % des stages en France)**.
- **Recherche** (« recherche » ou « R&D » dans le sujet) : **27 stages** seulement (16 avec « recherche », 11 avec « R&D »). C'est beaucoup moins que la Thématique « Recherche/Innovation/R&D » (94 sur l'ensemble de la base). À montrer à Dorothée Bernard pour vérifier que la règle correspond à ce qu'elle attend.

Durée par niveau :

| Niveau | Stages validés | Jours | Semaines | Heures |
|---|---|---|---|---|
| L2 | 3 | 29 | 4,2 | 154 |
| 3A (1re année du cycle ingénieur) | 227 | 41 | 5,9 | 210 |
| PEIP / L1 | 60 | 43 | 6,1 | 204 |
| L3 | 52 | 61 | 8,7 | 284 |
| M1 | 9 | 63 | 8,9 | 308 |
| 4A | 256 | 102 | 14,6 | 512 |
| Autres (Mastère, cycle RI) | 13 | 129 | 18,5 | 561 |
| M2 | 116 | 172 | 24,5 | 834 |
| 5A | 148 | 174 | 24,9 | 828 |

### 5.3 Valeurs aberrantes (conventions validées)

Seuil proposé : **48 h par semaine**, la durée maximale légale du travail.

| Anomalie | Nombre |
|---|---|
| Heures hebdomadaires manquantes | 11 |
| Heures hebdomadaires supérieures à 48 h (50, 60, 60, 175) | 4 |
| Heures hebdomadaires à 0 | 1 |
| Durée déclarée à 0 | 3 |

Ces valeurs seront signalées dans le rapport d'anomalies et exclues des calculs en heures.

### 5.4 Colonne « Commentaire durée travail »

Dans la base anonymisée initiale, elle était remplie pour les 1053 conventions, avec des valeurs toutes différentes : elle avait été entièrement remplacée lors de l'anonymisation. La version non anonymisée a été reçue le 12/10.

- La colonne n'est renseignée que pour **164 conventions** (117 validées). Il s'agit surtout d'horaires et de précisions sur le temps de travail.
- Les types d'expérience y apparaissent par mots-clés. Sur les conventions validées : **39 CDD, 4 CDI, 4 intérim, 1 bénévolat** (« WWOOFing »).
- Ces mentions concernent surtout la **PEIP / L1** et la **3A**, ce qui correspond à la demande initiale. L'indicateur est donc faisable, mais repose sur un texte libre : il ne couvre que les conventions où l'information a été saisie.

« Commentaire stage » reste entièrement remplacée dans la base de test.

## 6. Conséquences sur la conception

- **Clé d'import** : le N° de convention n'étant pas garanti unique entre années, l'import utilisera la clé **(année universitaire, N° de convention)**.
- **Validation de l'import multi-années** : aucune autre extraction réelle n'existe. L'adaptation aux changements de colonnes sera testée avec des **fichiers de test modifiés** à partir de la base 2025-2026 (colonnes renommées, réordonnées, ajoutées ou supprimées).
- **Statut de validation** : toutes les conventions sont importées, mais les indicateurs ne retiennent que les conventions validées. Ainsi, une convention validée plus tard sera prise en compte au réimport. La règle de prise en compte sera un **paramètre** de l'outil.
- **Indicateurs par mots-clés** (recherche, type d'expérience) : recherche insensible à la casse et aux accents. Les listes de mots-clés doivent être modifiables sans toucher au code.
- **Export** : CSV et XLS pour les résultats de recherche et les indicateurs.

## 7. Points restant ouverts

1. Critères de validation du prototype (proposition ci-dessous).
2. Règle de prise en compte : validation administrative seule (**884** conventions) ou validation administrative **ou** pédagogique (**1023**) ? La seconde règle réintègre la plupart des stages de PEIP (section 5.1).
3. Règle « recherche » : 27 stages avec la règle retenue, contre 84 avec la Thématique « Recherche/Innovation/R&D » sur les conventions validées. Est-ce le résultat attendu ?
4. Hébergement final et contact à la DSI.

## 8. Actions

| Action | Responsable | Échéance | État |
|---|---|---|---|
| Demander la colonne « Commentaire durée travail » non anonymisée | Augustin → Mme Kessaci | 12/10 | Fait (reçue le 12/10) |
| Envoyer ce compte rendu et la proposition de critères de validation | Augustin | 16/10 | |
| Faire valider la table code étape → niveau → spécialité | Augustin | 19/10 | Table construite |
| Construire le Gantt (pas de 2e année ; export indispensable) | Augustin | 18/10 | |
| Rédiger le rapport intermédiaire et le support de soutenance | Augustin | 21/10, 10h | |
| Soutenance blanche avec Mme Kessaci | Augustin | 19/10, 11h | Planifiée |

### Proposition de critères de validation (à faire valider par Dorothée Bernard)

1. **Import** : Dorothée Bernard importe seule un fichier EsupStage, sans intervention dans le code, en moins de 10 minutes. Un fichier dont les colonnes ont été renommées, réordonnées ou ajoutées s'importe en ajustant seulement le mapping dans l'interface. Un réimport ne crée aucun doublon.
2. **Indicateurs** : les indicateurs indispensables sont identiques aux chiffres de référence calculés indépendamment (script d'analyse).
3. **Recherche** : on peut rechercher et filtrer les stages par année, niveau, spécialité, localisation (France / étranger / Hauts-de-France, pays) et thématique.
4. **Export** : les résultats de recherche et les indicateurs s'exportent en CSV et en XLS et s'ouvrent correctement dans Excel.

Contraintes non fonctionnelles : accès protégé par authentification ; aucune donnée personnelle inutile stockée.
