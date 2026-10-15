# Préparation de la réunion avec Dorothée Bernard

Projet ingénieur 2026-2027 : Conception d'un outil de valorisation et de suivi des données de stages
Participants : Dorothée Bernard (DRE, tutrice référente), Augustin Mélon
Date : 12/10/2026
Objectif : obtenir ce qui manque pour la soutenance intermédiaire du 21/10 (contexte, cahier des charges, critères de validation, périmètre de la V1), trancher les règles métier et présenter les premiers résultats sur la base anonymisée.

---

## 0. Pourquoi cette réunion (rappel pour moi)

La soutenance intermédiaire (10 min + 5 min de questions, devant un jury qui ne connaît pas le sujet) et le rapport de 5 pages doivent couvrir :

| Partie demandée | Ce qui me manque et que seule Dorothée peut fournir |
|---|---|
| Contexte | Présentation de la DRE, usage actuel des données, motivation concrète du projet |
| Cahier des charges | Objectifs classés par priorité, contraintes, livrables attendus, **critères de validation** |
| Gestion de projet | Disponibilités, rythme des réunions, dates d'obtention des données manquantes |
| Mise en œuvre + Gantt | Accord sur le périmètre de la V1 et sur le calendrier proposé (section 5) |
| Points techniques | Règles métier pour les indicateurs (section 3) |
| Compétences ISIA | Rien à demander : métier visé **data engineer** (import, mapping, qualité, modèle de données), avec une composante ingénierie logicielle |

---

## 1. Ordre du jour (environ 1 h)

| # | Sujet | Durée |
|---|---|---|
| 1 | Point d'étape : réception de la base, premiers résultats | 10 min |
| 2 | Contexte de la DRE et usages actuels des données | 10 min |
| 3 | Règles métier à trancher | 20 min |
| 4 | Périmètre de la V1, priorités et critères de validation | 10 min |
| 5 | Organisation et calendrier | 5 min |
| 6 | Récapitulatif des décisions et des actions | 5 min |

---

## 2. Premiers résultats sur la base anonymisée (à présenter)

Fichier `base-anonym.xls` : une feuille, **71 colonnes, 1053 lignes**, année universitaire **2025/2026** uniquement. Toutes les valeurs sont stockées en texte.

### 2.1 Volume et qualité générale

- **1053 conventions**, 1053 numéros de convention distincts : aucun doublon de convention et aucune ligne dupliquée.
- **939 étudiants distincts**. 101 étudiants ont plusieurs stages dans l'année (jusqu'à 4).
- 17 % des cellules sont vides au total.
- **7 colonnes entièrement vides** : Code département, Code ELP, Date début interruption, Date fin interruption, Elément pédagogique, Mail perso étudiant, Nbre de jours de travail.
- Colonnes très peu remplies : Site web (0,2 % renseigné), Détail du projet (10,5 %), Mail de la structure (43,6 %), Gratification (49,6 %).
- Aucune incohérence de dates : aucune date manquante ou illisible, aucun stage qui finit avant de commencer.

### 2.2 Dates et durées

- Début des stages : du 03/09/2025 au 31/08/2026. Pics en **juin (312)** et en **avril (269)**, puis février (137).
- Fin des stages : du 07/11/2025 au 13/11/2026.
- Conventions créées entre le 20/01/2025 et le 12/07/2026. Dernière modification le 13/07/2026, ce qui donne probablement la date d'extraction.
- **Durée calculée à partir des dates** : moyenne 91 jours (13 semaines), médiane 75 jours (10,7 semaines), de 2 à 214 jours.
- **« Durée du stage » déclarée = des heures (vérifié)** : elle vaut en médiane 96 % de (nombre de semaines × heures hebdomadaires), et 80 % des stages sont entre 93 % et 106 %. Médiane 357 h, maximum 2275 h. 6 stages ont une durée déclarée de 0.
- **Heures hebdomadaires** : 35 h pour 807 stages (77 %), puis 40 h (68), 39 h (28), 38 h (24). Valeurs aberrantes : minimum 0, **maximum 175 h**. 15 valeurs sont manquantes.

Durée calculée par niveau. Le niveau est déduit pour l'instant du libellé d'étape ; il faudra une vraie table de correspondance.

| Niveau | Stages | Durée moyenne (jours) | Médiane (jours) |
|---|---|---|---|
| PEIP / L1 | 166 | 34 | 30 |
| 3A (1re année du cycle ingénieur) | 275 | 41 | 40 |
| L3 | 52 | 61 | 54 |
| 4A | 261 | 102 | 110 |
| 5A | 150 | 173 | 180 |
| M2 | 119 | 170 | 180 |
| Autres (M1, L2, Mastère, Cycle ingénieur RI…) | 30 | — | — |

C'est un premier exemple concret d'indicateur que l'outil produira.

### 2.3 Interruptions : incohérence

- La colonne « Interruption » vaut **Oui pour 37 stages**, Non pour 70 et est vide pour 946.
- Mais les dates d'interruption sont **vides pour tous les stages**. On ne peut donc pas déduire les interruptions de la durée.

### 2.4 Formations

- **48 codes étape distincts**, chacun avec un seul libellé. Code étape et libellé sont cohérents.
- Le libellé mélange le niveau et la spécialité, avec des formats hétérogènes : « 1an Ing Polytech Génie civil », « 1Dip Ing Spécialité génie biologique… », « L3 Génie civil », « M2 Infrastructure en génie civil », « L1 Portail SVTE - option PEIP ».
- La base ne contient pas que les élèves ingénieurs : il y a aussi des **L1/L2 PEIP, L3, M1, M2, un Mastère et un cycle Relations internationales**.
- Code étape le plus fréquent : 1XIGYT (L1 Portail PEIP, 109 stages).

### 2.5 Localisation

- **Pays de la structure** : France 809 (76,8 %), étranger **244 (23,2 %)**, 46 pays. Principaux : Belgique 75, Maroc 22, Canada 13, Tunisie 12, Espagne 11.
- Le pays de la structure et le pays du service d'accueil ne diffèrent que pour **4 stages**. Le choix entre les deux a donc peu d'impact en pratique.
- **Hauts-de-France**, déduit du code postal de la structure (départements 02, 59, 60, 62, 80) : **426 stages, soit 40 % du total et 53 % des stages en France**. Nord : 354, Pas-de-Calais : 53. Île-de-France : 166.
- Tous les codes postaux français ont 5 chiffres, sans anomalie.
- 567 communes distinctes.

### 2.6 Structures d'accueil

- **840 noms de structure distincts** et 664 SIRET distincts. Plusieurs établissements d'un même groupe ont des SIRET différents.
- **SIRET vide pour 221 stages (21 %)**, dont seulement 5 en France : le SIRET manque essentiellement pour les structures étrangères.
- Type de structure : entreprise privée 71 %, structure publique 12,8 %, administration 6,2 %, « Autre sans SIRET » 5,2 %, association 3,3 %.
- Statut juridique : SAS 42 %, SA 13 %, SARL 11 %, public 9 %, EPST 5,5 %, valeur manquante 7,5 %.
- **Effectif : deux nomenclatures coexistent** (« 200 à 999 » et « 2 000 à 4 999 salariés », « 10 à 49 » et « 10 à 19 salariés »), plus « Non défini » et « 0 ». C'est un cas concret où le dictionnaire de valeurs de l'import sera nécessaire.

### 2.7 Secteur et recherche

- **Thématique** : 30 valeurs, toujours renseignée. Principales : Génie civil/BTP 27 %, Informatique/ESN 9,4 %, Recherche/Innovation/R&D 8,9 %, Mécanique 6,4 %, Électronique/automatique 5,7 %, Agroalimentaire 5,6 %.
- **Code NAF** : contient en fait le **libellé** de l'activité, pas le code. 268 libellés distincts, vide dans 21,8 % des cas. Libellés les plus fréquents : Enseignement supérieur (71), Ingénierie, études techniques (67), Construction d'autres bâtiments (60).
- **Stages « recherche » : trois critères possibles qui se recoupent peu.**
  - Thématique « RECHERCHE… » : 94 stages
  - Statut juridique EPST : 58 stages
  - NAF « Recherche » ou « Enseignement supérieur » : 112 stages
  - Au moins un des trois : 187 stages. Thématique R&D **et** EPST : seulement 22.

  Il faut une définition métier.

### 2.8 Type de convention et validation

- Type convention : FORMATION INITIALE 1041 (98,9 %), formation continue 10, apprentissage 2. **Aucune information sur CDD, intérim ou bénévolat.**
- Validation convention : **Non pour 169 stages (16 %)**. Validation pédagogique : Non pour 31 (2,9 %).
- 139 conventions sont validées pédagogiquement mais pas administrativement.
- Avenants : 88 conventions (8,4 %).

### 2.9 Textes libres et autres champs

- Sujet : renseigné pour 1051 stages ; Fonctions et tâches : 1050. Ils sont exploitables pour une recherche plein texte (structures et thématiques).
- Détail du projet : seulement 111.
- Gratification : renseignée pour 522 stages (environ la moitié, ce qui correspond aux stages de plus de 2 mois). Hors périmètre de la V1.
- Sexe étudiant : F 523, M 530.

### 2.10 Données personnelles dans la base anonymisée

- Les champs nominatifs sont **remplis à 100 %** : nom, prénom, adresse, mail universitaire, téléphones de l'étudiant, tuteurs, signataires. Les valeurs ont été remplacées (par exemple, tous les mails des tuteurs sont en `@mail.com`).
- Il faudra vérifier avec Marie-Éléonore Kessaci que le **N° étudiant pseudonymisé reste stable d'une année sur l'autre**.

---

## 3. Questions pour Dorothée

### 3.1 Contexte (pour la partie « Contexte » de la soutenance)

1. Quelle est la mission de la DRE et la composition de l'équipe ? Combien de personnes s'occupent des stages ? -> dorothée est seule partie convention de stage (2 autre pour trouver un stage). En lien avec il y a les responsables de stage (1 par code étape). Assistante pédagogique 10.
2. Aujourd'hui, comment utilisez-vous les extractions EsupStage (Excel, tableaux croisés) ? Combien de temps cela prend-il par an ? -> 80% des questions qu'on lui pose ne sont pas répondues par manque d'outil. Parmi les 20 % restant : une dizaine d'extraction elle m^me et prend 30 minute par extraction.
3. Qui vous pose des questions sur les stages (direction, responsables de formation, enseignants, étudiants, partenaires) ? Quelles sont les questions les plus fréquentes ?
4. Y a-t-il des échéances où ces chiffres sont indispensables (rapports annuels, accréditation CTI, conseils, salons entreprises) ?
5. Existe-t-il des outils ou des travaux antérieurs (fichiers de suivi, tableaux de bord, un autre projet étudiant) ?
6. Si l'outil fonctionne, qu'est-ce qui changera concrètement pour vous ?

### 3.2 Règles métier

7. **Durée** : la « Durée du stage » est bien en heures, c'est vérifié (proche de semaines × heures hebdomadaires). Pour la durée moyenne, faut-il raisonner en **jours calendaires**, en **semaines** ou en **heures** ?
8. **Interruptions** : 37 stages ont « Interruption = Oui », mais les dates d'interruption sont toujours vides. Faut-il ignorer les interruptions dans le calcul de la durée ? Ces dates sont-elles disponibles ailleurs ?
9. **Étranger** : pays de la structure ou du service d'accueil ? Seuls 4 stages diffèrent ; je propose le **service d'accueil** (le lieu réel du stage). D'accord ?
10. **Hauts-de-France** : je propose de déduire la région du département du code postal (02, 59, 60, 62, 80). Est-ce que ça convient ?
11. **Recherche** : quelle définition retenir ? Thématique R&D (94), EPST (58), NAF recherche ou enseignement supérieur (112), ou une combinaison (187) ? Un stage en laboratoire d'une entreprise privée compte-t-il ? -> tout aggréger (pas le code NAF, que dans le sujet)
12. **Secteur** : la Thématique (30 valeurs, toujours renseignée) suffit-elle, ou faut-il le NAF (268 libellés, vide dans 22 % des cas) ?
13. **CDD, intérim, bénévolat** : absents de la base (99 % « Formation initiale »). D'où vient la demande pour la PEIP1 et la 3e année ? Existe-t-il une autre source (déclaration des étudiants, Moodle, autre extraction) ? Sinon, ce besoin sort-il du périmètre ?
14. **Conventions non validées** (169, soit 16 %) : faut-il les exclure des indicateurs ? Sont-elles en cours de validation, abandonnées ou annulées ?
15. **Périmètre des formations** : la base contient aussi des L1/L2/L3, M1/M2, un Mastère et un cycle RI. Faut-il tous les suivre, ou seulement le cycle ingénieur et la PEIP ?
16. **Niveau et spécialité** : existe-t-il une liste officielle des codes étape avec le niveau et la spécialité ? Sinon, je construis la table et elle la valide.
17. **Effectif** : peut-on regrouper les deux nomenclatures en tranches communes ?
18. **Heures hebdomadaires aberrantes** (0 h, 175 h) : correction manuelle, ou simple signalement dans le rapport d'anomalies ?

### 3.3 Données

19. Quel nettoyage a été fait sur le fichier avant transmission ? Des colonnes ont-elles été supprimées ou modifiées ?
20. Quand l'extraction a-t-elle été faite ? (Dernière modification le 13/07/2026 : les conventions de l'été sont-elles complètes ?)
21. Peut-on obtenir **une extraction d'une autre année** (2024-2025 ou antérieure) pour tester l'adaptation de l'import aux changements de colonnes ? À quelle date ?
22. Combien d'années d'historique existent dans EsupStage ?
23. Le N° de convention est-il unique d'une année sur l'autre ? -> pas sûr donc disons que non

### 3.4 Utilisateurs et usages

24. En V1, vous êtes la seule utilisatrice. Qui d'autre pourrait l'utiliser plus tard (enseignants, étudiants à la recherche d'un stage, direction) ?
25. Les étudiants pourraient-ils consulter une version anonymisée (structures et thématiques, sans les noms) pour trouver un stage ?
26. Hébergement final : qui contacter à la DSI ? Avez-vous des contraintes de sécurité connues ?

### 3.5 Périmètre de la V1 et critères de validation

27. Classement des fonctionnalités (voir la proposition en section 4) : êtes-vous d'accord ?
28. **Quels critères permettront de dire que le prototype est réussi ?** (Proposition en section 4.)
29. Quels livrables attendez-vous en plus du prototype : documentation utilisateur, guide d'import ?

### 3.6 Organisation

30. Rythme des réunions : hebdomadaire, quel créneau ?
31. Disponibilité pour des **tests utilisateurs** début janvier (session démo le 08/01/2027).
32. Pouvez-vous être présente à la soutenance intermédiaire du 21/10 (après-midi) ?

---

## 4. Propositions à faire valider

### 4.1 Périmètre de la V1 (proposition)

| Priorité | Fonctionnalité |
|---|---|
| **Indispensable** | Import d'un fichier EsupStage (.xls) avec mapping configurable et profil réutilisable |
| **Indispensable** | Contrôles à l'import et rapport d'anomalies (dates, valeurs inconnues, durées incohérentes) |
| **Indispensable** | Indicateurs : nombre de stages, durée moyenne, France/étranger, Hauts-de-France, par spécialité, par niveau et par année |
| **Indispensable** | Recherche multicritère : structure, pays, ville, thématique, spécialité, niveau, année |
| Souhaitable | Historique sur plusieurs années (dès qu'une 2e extraction est disponible) |
| Souhaitable | Recherche plein texte dans le sujet et les fonctions et tâches |
| Souhaitable | Graphiques et export des résultats (CSV) |
| Souhaitable | Rapprochement des structures sans SIRET (noms proches) |
| Bonus | Indicateurs « recherche » et « secteur » (selon les règles retenues) |
| Hors V1 | Gratification, rôles multiples (enseignants, étudiants), CDD/intérim (source inconnue) |

### 4.2 Critères de validation (proposition)

1. Dorothée importe seule un fichier EsupStage, sans intervention dans le code.
2. Un fichier d'une autre année, dont les colonnes ont changé, s'importe en ajustant seulement le mapping dans l'interface.
3. Les indicateurs indispensables correspondent aux chiffres calculés à la main sur un échantillon.
4. Un réimport du même fichier ne crée aucun doublon.
5. L'accès est protégé par authentification et les données personnelles inutiles ne sont pas stockées.

### 4.3 Calendrier et Gantt (proposition)

| Période | Tâche | Jalon |
|---|---|---|
| S41-S42 (05/10-18/10) | Analyse de la base, recueil des besoins, cahier des charges | — |
| S43 (19/10) | Rapport et soutenance intermédiaire | **21/10 soutenance** |
| S43-S45 | Modèle de données, schéma PostgreSQL, tables de référence (codes étape, régions) | Modèle validé |
| S45-S48 | Module d'import : lecture .xls, mapping, profils, contrôles, rapport d'anomalies | Import fonctionnel |
| S48-S50 | API, recherche multicritère, indicateurs | — |
| S50-S51 | Visualisation, tests sur 2 années | **Rapport v1 (S51)** |
| S1 2027 | Finitions, préparation de la démo | **08/01 session démo** |
| S2 2027 | Tests utilisateurs avec Dorothée, corrections, réunion tripartite de bilan | — |
| S3-S4 2027 | Rapport final, soutenance | **29/01 soutenance finale** |

---

## 5. Notes de réunion (à remplir pendant la réunion)

**Décisions :**

-

**Réponses aux questions** (reporter le numéro de la question) :

-

**Actions :**

| Action | Responsable | Échéance |
|---|---|---|
| Envoyer le compte rendu | Augustin | 13/10 |
| Transmettre une autre extraction | Dorothée | |
| | | |
