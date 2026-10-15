# Compte rendu de la réunion technique (version 3)

Projet ingénieur 2026-2027 : Conception d'un outil de valorisation et de suivi des données de stages

Participants : Augustin Mélon, Bastien Cazaux (expert technique)
Date : 29/09/2026

Cette version intègre l'analyse du fichier EsupStage réel (extraction 2025-2026). Les éléments marqués **[Proposition]** sont des recommandations à valider avec l'expert technique et Dorothée Bernard ; le reste est acté ou constaté dans le fichier.

## 1. Données d'entrée

- Source : un fichier Excel extrait d'EsupStage, contenant tous les stages de tous les étudiants de Polytech Lille pour **une année donnée**. Un fichier = une année ; l'historique se construit par imports successifs.
- Les étudiants restent identifiables d'une année à l'autre, y compris dans la base anonymisée.
- Deux contextes d'usage :
  - **Développement** : base anonymisée par Marie-Éléonore Kessaci (RGPD).
  - **Utilisation réelle** : Dorothée Bernard importera elle-même les fichiers, **non anonymisés**.
- Fichier disponible à ce jour : extraction 2025-2026 (« nettoyé », donc retouché après extraction), ne contenant qu'**une ligne de test**. Il donne la structure des colonnes, pas la variété ni la qualité des valeurs.

## 2. Architecture retenue

```text
Fichiers EsupStage
        ↓
Module d'import
        ↓
Mapping configurable
        ↓
Nettoyage / contrôle
        ↓
Base de données
        ↓
API
        ↓
Interface Web
```

| Brique | Rôle |
|---|---|
| Module d'import | Lit le fichier Excel |
| Mapping configurable | Associe colonnes du fichier et champs du modèle, sans modifier le code |
| Nettoyage / contrôle | Normalise les valeurs, signale les anomalies |
| Base de données | Stockage structuré et normalisé |
| API | Endpoints JSON pour l'interface (faisable en PHP) |
| Interface Web | Recherche multicritère, indicateurs, visualisation |

## 3. Technologies

- **PHP, HTML, JavaScript** : imposés par l'expert technique, car toutes les applications Polytech sont développées ainsi.
- **PostgreSQL** pour la base de données.
- API : implémentée en PHP (endpoints renvoyant du JSON), consommée par le JavaScript du navigateur.
- Lecture Excel : **[Proposition]** PhpSpreadsheet, qui sait lire l'ancien format .xls (le fichier réel est un .xls).
- Hébergement / déploiement : à traiter plus tard. Point à ne pas repousser trop longtemps, car l'utilisation sur données non anonymisées impose un hébergement maîtrisé par l'école.

## 4. Constats sur le fichier réel

Fichier : une feuille (« Données stage »), une ligne d'en-tête, **71 colonnes**.

1. **Tout est du texte.** Dates (jj/mm/aaaa), nombres et codes postaux sont des cellules de type texte. Conséquences : conversion explicite des dates (en PHP, utiliser `DateTime::createFromFormat('d/m/Y', ...)` ; `strtotime` interprète jj/mm/aaaa comme mm/jj/aaaa), et zéros initiaux des codes postaux préservés tant que le fichier reste en texte (à vérifier si un fichier repasse par Excel).
2. **Les intitulés de colonnes ne sont pas fiables comme clés.** Dans le même fichier, on trouve « Service d’accueil - Commune » (tiret court) et « Service d’accueil – Adresse » (tiret long), des apostrophes typographiques, une casse variable (« Nom Étudiant », « validation pédagogique »). Les en-têtes doivent être normalisés avant comparaison (apostrophes, tirets, casse, accents, espaces).
3. **Les intitulés ne garantissent pas le contenu.** La colonne « Code NAF » contient un libellé d'activité, pas un code, sur la ligne exemple. La colonne « Enseignant référent (NOM Prénom) » contient un nom au format Prénom Nom. Le mapping doit pouvoir s'appuyer sur des valeurs d'exemple, pas seulement sur les noms de colonnes.
4. **Clés naturelles disponibles.** N° de la convention (stage), N° étudiant (étudiant), Code étape (formation), SIRET (structure), e-mails des encadrants. Le SIRET peut être vide : la ligne exemple porte le type de structure « Autre sans SIRET » (structure étrangère). La date de modification de la convention permet de détecter les mises à jour.
5. **Formation : niveau et spécialité ne sont pas des colonnes séparées.** Le « Libellé étape » fusionne les deux (préfixe de niveau suivi de la spécialité). Le « Code étape » est la clé stable ; il faut une table de correspondance code étape → niveau + spécialité, à alimenter avec les codes de toutes les formations (dont PEIP1).
6. **Localisation : pas de colonne région.** Hauts-de-France doit être dérivé du code postal et du pays via une table de référence département → région. Il y a deux niveaux d'adresse : la **structure** (Pays, Code postal, Commune) et le **service d'accueil** (lieu du stage). Pour les groupes multinationaux, les deux peuvent différer : il faut définir lequel sert au critère « à l'étranger ». Les pays sont des libellés en majuscules (« FRANCE », « BELGIQUE ») ; utiliser le pays plutôt que le format du code postal (4 chiffres en Belgique, 5 en France).
7. **Secteur et recherche : pas de colonne dédiée.** Candidats pour le secteur : Code NAF, Thématique, Type de structure. Pour « recherche ou non » : aucune colonne évidente, à dériver (activité NAF, statut juridique, type de structure). Une règle explicite est à définir avec Dorothée Bernard.
8. **Type d'expérience (CDD / stage / intérim / bénévolat) : aucune colonne évidente.** « Type convention » vaut « FORMATION INITIALE » sur la ligne exemple. Ce fichier ne permet peut-être pas de répondre à la requête sur PEIP1 et 3e année : la source de cette information est à identifier.
9. **Durée.** « Durée du stage » vaut 690 pour un stage d'environ 19 semaines à 37,5 h par semaine : l'ordre de grandeur correspond à des **heures**, pas à des jours. L'unité n'est pas précisée dans le fichier. Le fichier contient aussi des dates d'interruption. **[Proposition]** calculer la durée à partir des dates (en jours ou semaines, interruptions déduites) et conserver la valeur déclarée pour un contrôle de cohérence.
10. **Encadrants : trois rôles.** Enseignant référent (école), tuteur professionnel, signataire. Le signataire représente la structure et n'est pas un encadrant. Les e-mails servent de clés de rapprochement.
11. **Deux niveaux d'organisation.** Le fichier distingue la structure d'accueil (organisation) et le service d'accueil (site ou entité précise), avec des noms distincts. Le modèle doit les séparer.
12. **Conventions à valider.** Les colonnes « Validation convention » et « validation pédagogique » (Oui/Non) permettent d'exclure les conventions non validées des indicateurs : règle à confirmer.
13. **Textes libres exploitables.** Sujet, Détail du projet et Fonctions et tâches (multi-lignes) alimenteraient une recherche plein texte (PostgreSQL), utile pour aider les futurs stagiaires à repérer des structures et des thématiques.
14. **Gratification.** La valeur et une mention (« Net ») sont mêlées dans un texte, l'unité est dans une autre colonne. Hors périmètre de la première version.
15. **Données personnelles.** Sur 71 colonnes, une quinzaine sont personnelles ou des coordonnées de tiers (adresse, téléphones, e-mail personnel, sexe, identité de l'étudiant, coordonnées des tuteurs et signataires). La plupart sont inutiles aux indicateurs (annexe A).

## 5. Modèle de données

### 5.1 Schéma initial (réunion)

```text
Étudiant
   ├── Formation
   └── Stage
          ├── Structure
          ├── Encadrants
          ├── Thématique
          └── Dates
```

### 5.2 Schéma métier révisé **[Proposition]**

```text
Étudiant (numéro étudiant, identifiant stable)
   └── Stage (numéro de convention = clé naturelle)
          ├── année universitaire, dates, interruption, durée, heures hebdo
          ├── validation convention / validation pédagogique
          ├── type d'expérience : à définir (cf. constat 8)
          ├── Formation : code étape → niveau + spécialité
          ├── Thématique
          ├── Structure : SIRET (facultatif), nom, NAF, statut juridique, type, pays, région (dérivée)
          │      └── Service d'accueil : nom, adresse, pays
          ├── Encadrant école (e-mail)
          └── Encadrant professionnel (e-mail)
```

Les dates sont des attributs du stage. Le secteur et le caractère « recherche » sont dérivés (constat 7), pas lus directement.

### 5.3 Tables techniques (import) **[Proposition]**

```text
import_batch      : fichier, hash, année, date, statut, utilisateur
import_staging    : batch, numéro de ligne, colonnes mappées uniquement (JSONB), purgé après validation
import_profile    : nom, signature des colonnes, date de création
mapping_rule      : profil, colonne source, action (importer / ignorer), champ cible, type, format
value_dictionary  : champ, valeur source, valeur canonique
structure_alias   : variante de nom, structure canonique
import_issue      : batch, ligne, gravité, message, statut de résolution
ref_geo           : code postal / département → région (table de référence)
ref_etape         : code étape → niveau, spécialité
```

## 6. Stratégie de mapping **[Proposition]**

Le mapping est géré dans l'application, car Dorothée Bernard fera les imports réels. Les constats sur le fichier réel précisent la conception :

1. **Catalogue de champs cibles** : chaque champ est déclaré avec type, caractère obligatoire et alias connus.
2. **Normalisation des en-têtes avant comparaison** : apostrophes, tirets, casse, accents, espaces (constat 2).
3. **Suggestion automatique** à partir des alias, avec **valeurs d'exemple affichées** à côté de chaque colonne pour que l'utilisatrice vérifie le contenu (constat 3).
4. **Trois actions par colonne** : importer vers un champ cible, **ignorer**, ou signaler pour décision. Les colonnes inconnues sont présentées à l'utilisatrice, et son choix est enregistré comme **profil de mapping** réutilisable.
5. **Année suivante** : si la signature des colonnes correspond à un profil connu, l'import est direct ; sinon seules les différences sont à traiter.
6. **Import idempotent** : rapprochement sur le numéro de convention. Un réimport met à jour ou ignore, sans doublon. Ce point suppose que le numéro est unique entre années (à confirmer).
7. **Conversions déclaratives** : type, format de date (jj/mm/aaaa), dictionnaires de valeurs. Fusion ou scission de colonnes : version ultérieure, seulement si les fichiers réels l'exigent.
8. **Minimisation des données** : les colonnes marquées « ignorer » ne sont pas stockées, y compris en table de staging. Le fichier téléversé est supprimé après import. **Correction par rapport à la version 2** : le stockage de la ligne brute complète en JSONB était en contradiction avec cette minimisation ; le staging ne contient désormais que les colonnes mappées et est purgé après validation. Contrepartie : rejouer un import demande de téléverser à nouveau le fichier.
9. **Contrôles** : champs obligatoires manquants, dates illisibles ou incohérentes (fin avant début), valeurs hors dictionnaire, durée déclarée incohérente avec les dates.

## 7. Stratégie de normalisation **[Proposition]**

### 7.1 Valeurs à liste fermée (pays, thématique, statut juridique, type de structure, code étape)

Table de correspondance `valeur source → valeur canonique`. Une valeur inconnue est signalée, l'utilisatrice la rattache une fois et la règle est conservée.

### 7.2 Structures d'accueil

1. **SIRET** comme clé forte quand il est présent (structures françaises).
2. **Nom normalisé** (minuscules, sans accents, ponctuation et formes juridiques retirées) combiné avec commune ou code postal, pour les structures sans SIRET (notamment étrangères).
3. **Similarité approchée** : extension PostgreSQL `pg_trgm` pour proposer des candidats proches.
4. **Validation humaine** : aucune fusion automatique en dessous d'un seuil élevé ; les cas douteux vont dans une file de validation ; chaque décision alimente la table d'alias.

À vérifier sur un fichier multi-lignes : taux de SIRET renseignés, qualité des noms et des adresses.

## 8. Requêtes types et faisabilité avec le fichier

| Requête | Faisabilité | Colonne(s) / dérivation |
|---|---|---|
| Durée moyenne des stages | Oui | Dates de début et de fin (interruptions déduites) |
| France / étranger | Oui, règle à définir | Pays de la structure ou du service d'accueil |
| Hauts-de-France / hors | Oui, par dérivation | Code postal + pays → département → région |
| Comparaison par année | Oui | Année universitaire |
| Comparaison par spécialité | Oui, par dérivation | Code étape → spécialité |
| Niveau d'études (3e année, PEIP1) | Oui, par dérivation | Code étape → niveau |
| Secteur de l'entreprise | Partiel | NAF, Thématique : définition à valider |
| Recherche ou non | Partiel | Aucune colonne dédiée : règle à définir |
| CDD / stage / intérim / bénévolat | Non identifié | Aucune colonne évidente : source à identifier |

## 9. Utilisateurs, droits d'accès et données personnelles

- Périmètre actuel : **un seul profil utilisateur**, Dorothée Bernard. Pas de gestion de rôles à ce stade.
- En utilisation réelle, l'outil contiendra des données nominatives : **authentification obligatoire** même avec une seule utilisatrice, HTTPS, hébergement maîtrisé par l'école.
- **[Proposition]** Table d'utilisateurs avec une colonne de rôle, sans gestion fine des droits, pour permettre l'extension (enseignants, étudiants) et documenter la réflexion demandée par le sujet.
- Minimisation : seules les colonnes utiles sont importées (annexe A). Nom, prénom et e-mail de l'étudiant sont des champs facultatifs, absents ou pseudonymisés sur la base de test.
- Le schéma et les imports doivent fonctionner **à l'identique sur base anonymisée et non anonymisée**. À valider avec Marie-Éléonore Kessaci : méthode d'anonymisation (pseudonymisation stable du numéro étudiant, colonnes supprimées ou remplacées), pour que le mapping ne casse pas au passage aux vraies données.

## 10. Points ouverts

**À demander à Dorothée Bernard :**
1. Où se trouve l'information CDD / stage / intérim / bénévolat ? Une autre extraction existe-t-elle ?
2. « À l'étranger » : pays de la structure ou du service d'accueil ?
3. Définition de « recherche » et de « secteur » : quelles colonnes, quelle règle ?
4. Unité de « Durée du stage » et règle de calcul de la durée moyenne (interruptions comprises ou non).
5. Quel nettoyage manuel a été appliqué au fichier ? Quelles colonnes ont été supprimées ou modifiées ?
6. Les conventions non validées doivent-elles compter dans les indicateurs ?
7. Fichier multi-lignes de 2025-2026 et extraction d'une autre année (pour mesurer l'évolution des colonnes).

**À valider avec l'expert technique :** mapping en base avec assistant d'import, staging minimal purgé, PhpSpreadsheet, `pg_trgm`, framework PHP éventuel ou PHP natif.

**À confirmer avec Marie-Éléonore Kessaci :** méthode d'anonymisation.

**Autres :** unicité du numéro de convention entre années ; hébergement cible.

## 11. Prochaines étapes

- Obtenir un fichier multi-lignes et une seconde année.
- Figer le catalogue de champs cibles et le modèle de données (section 5.2).
- Produire le schéma PostgreSQL (tables métier, techniques, de référence).
- Prototyper l'import : lecture .xls, mapping avec valeurs d'exemple, contrôles, rapport d'anomalies.
- Prototyper les premiers indicateurs réalisables (durée moyenne, France / étranger, Hauts-de-France, par spécialité et par année).

## Annexe A. Mapping initial proposé des 71 colonnes

Bilan : 43 colonnes à importer, 23 à ignorer, 5 à clarifier ou décider. Proposition initiale, à valider ; elle amorce le premier profil de mapping.

| N° | Colonne du fichier | Action | Champ cible / remarque |
|---|---|---|---|
| 1 | Adresse étudiant | Ignorer | Donnée personnelle, inutile aux indicateurs |
| 2 | Année universitaire | Importer | stage.annee_universitaire |
| 3 | Avantage nature | Ignorer | Texte libre lié à la gratification (v2 éventuelle) |
| 4 | Avenant(s) à la convention | Importer | stage.avenant (Oui/Non) |
| 5 | Code département | À clarifier | Code département : de qui ? (vide sur l'exemple) |
| 6 | Code ELP | Ignorer | Code ELP, sans usage identifié |
| 7 | Code étape | Importer | formation.code_etape (clé stable du niveau + spécialité) |
| 8 | Code postal étudiant | Ignorer | Donnée personnelle (adresse de l'étudiant) |
| 9 | Code sexe étudiant | À décider | etudiant.sexe (utile pour des statistiques ? sinon ignorer) |
| 10 | Code UFR | Ignorer | Constant (école) |
| 11 | Commentaire durée travail | Ignorer | Commentaire libre |
| 12 | Commentaire stage | Ignorer | Commentaire libre |
| 13 | Date création convention | Importer | stage.date_creation_convention |
| 14 | Date début du stage | Importer | stage.date_debut (texte jj/mm/aaaa à convertir) |
| 15 | Date début interruption | Importer | stage.interruption_debut |
| 16 | Date fin du stage | Importer | stage.date_fin (texte jj/mm/aaaa à convertir) |
| 17 | Date fin interruption | Importer | stage.interruption_fin |
| 18 | Date modification convention | Importer | stage.date_modification (détection des mises à jour) |
| 19 | Détail du projet | Importer | stage.detail_projet (texte, recherche plein texte) |
| 20 | Durée du stage | Importer | stage.duree_declaree (unité à confirmer, probablement des heures) |
| 21 | Elément pédagogique | Ignorer | Élément pédagogique, sans usage identifié |
| 22 | Enseignant référent (NOM Prénom) | Importer | encadrant_ecole.nom (ordre Prénom/Nom non conforme à l'intitulé) |
| 23 | Fonction signataire | Ignorer | Fonction du signataire |
| 24 | Fonctions et tâches | Importer | stage.fonctions_taches (texte multi-lignes, recherche plein texte) |
| 25 | Gratification | Ignorer | Valeur et mention mêlées dans un texte (v2 éventuelle) |
| 26 | Interruption | Importer | stage.interruption (Oui/Non) |
| 27 | Libellé étape | Importer | formation.libelle (contient niveau + spécialité, à ne pas exploiter directement) |
| 28 | Libellé UFR | Ignorer | Constant (école) |
| 29 | Mail enseignant référent | Importer | encadrant_ecole.email (clé de rapprochement) |
| 30 | Mail perso étudiant | Ignorer | Donnée personnelle |
| 31 | Mail signataire | Ignorer | Contact du signataire |
| 32 | Mail universitaire étudiant | À décider | etudiant.email (usage réel uniquement, sinon ignorer) |
| 33 | N° de la convention | Importer | stage.numero_convention (clé naturelle, base de l'upsert) |
| 34 | N° étudiant | Importer | etudiant.numero (identifiant stable entre années) |
| 35 | Nbre d’heures hebdomadaire | Importer | stage.heures_hebdo |
| 36 | Nbre de jours de travail | Importer | stage.jours_travail |
| 37 | Nom Étudiant | À décider | etudiant.nom (absent ou pseudonymisé en base de test) |
| 38 | Pays étudiant | Ignorer | Pays de résidence de l'étudiant |
| 39 | Prénom Étudiant | À décider | etudiant.prenom (absent ou pseudonymisé en base de test) |
| 40 | Signataire (NOM Prénom) | Ignorer | Signataire (représentant de la structure), pas un encadrant |
| 41 | Sujet | Importer | stage.sujet (texte, recherche plein texte) |
| 42 | Téléphone perso étudiant | Ignorer | Donnée personnelle |
| 43 | Téléphone portable étudiant | Ignorer | Donnée personnelle |
| 44 | Thématique | Importer | stage.thematique (candidat pour le secteur) |
| 45 | Type convention | Importer | stage.type_convention (valeurs à explorer, cf. type d'expérience) |
| 46 | Unité Durée gratification | Ignorer | Unité de la gratification (v2 éventuelle) |
| 47 | Validation convention | Importer | stage.convention_validee (Oui/Non) |
| 48 | validation pédagogique | Importer | stage.validation_pedagogique (Oui/Non) |
| 49 | Ville étudiant | Ignorer | Donnée personnelle |
| 50 | Adresse | Importer | structure.adresse |
| 51 | Code NAF | Importer | structure.naf (contient un libellé sur l'exemple, pas un code ; candidat pour le secteur) |
| 52 | Code postal | Importer | structure.code_postal (texte, zéros initiaux) |
| 53 | Commune | Importer | structure.commune |
| 54 | Effectif | Importer | structure.effectif |
| 55 | Fonction tuteur professionnel | Ignorer | Fonction du tuteur professionnel |
| 56 | Mail | Ignorer | Contact générique de la structure |
| 57 | Mail tuteur professionnel | Importer | encadrant_pro.email (clé de rapprochement) |
| 58 | Nom structure d’accueil | Importer | structure.nom |
| 59 | Pays | Importer | structure.pays (dictionnaire de valeurs) |
| 60 | Service d’accueil - Commune | Importer | service.commune |
| 61 | Service d’accueil – Adresse | Importer | service.adresse |
| 62 | Service d’accueil – Code postal | Importer | service.code_postal |
| 63 | Service d’accueil – Nom | Importer | service.nom |
| 64 | Service d’accueil – Pays | Importer | service.pays (dictionnaire de valeurs) |
| 65 | SIRET | Importer | structure.siret (peut être vide) |
| 66 | Site web | Importer | structure.site_web |
| 67 | Statut juridique | Importer | structure.statut_juridique (candidat pour « recherche ») |
| 68 | Téléphone | Ignorer | Contact générique de la structure |
| 69 | Téléphone tuteur professionnel | Ignorer | Donnée personnelle du tuteur |
| 70 | Tuteur professionnel (Nom Prénom) | Importer | encadrant_pro.nom |
| 71 | Type de structure | Importer | structure.type_structure (ex. structure sans SIRET) |