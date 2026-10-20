# Questions probables du jury et éléments de réponse

## Sujet et besoin

**Pourquoi ne pas utiliser simplement Excel, ou un outil de BI (Power BI, Metabase) ?**
Excel demande de refaire chaque année les mêmes manipulations, environ 30 minutes par question, et ne garde pas d'historique. Un outil de BI suppose des données déjà propres et structurées : le problème est justement en amont (import d'un format qui change, nettoyage, règles métier). De plus, la stack est imposée par l'école (PHP, PostgreSQL) pour la maintenance et l'hébergement. Rien n'empêche plus tard de brancher un outil de BI sur la base PostgreSQL.

**EsupStage n'a-t-il pas déjà des statistiques ?**
EsupStage propose des extractions, pas d'analyse multi-critères ni d'historique ; c'est ce qui a motivé le sujet. *(À confirmer avec Dorothée Bernard si la question est posée précisément.)*

**Qui va maintenir l'outil après le projet ?**
Le choix de la stack standard de l'école est fait pour ça. Le paramétrage (mapping, règles, référentiels) se fait sans code. La documentation technique et le guide utilisateur font partie des livrables. Point à clarifier avec la DSI lors du contact pour l'hébergement.

**Comment avez-vous compris le besoin ?**
En partant des questions réelles posées à la DRE, puis en explicitant pour chacune la règle cachée (quelles conventions, quel pays, quelle unité). Pour chaque règle, j'ai chiffré les options sur les données réelles et c'est elle qui a tranché, en trois réunions préparées, chacune suivie d'un compte rendu. Le périmètre a été priorisé avec elle, et elle a validé les critères de validation le 19/10.

## Technique

**Comment le mapping reconnaît-il une colonne renommée ?**
Il procède par étapes, de la plus sûre à la moins sûre : profil identique, colonne déjà rencontrée, alias connus après normalisation, puis similarité (proportion de mots communs et similarité de chaînes, seuil 0,78). Ce qui est incertain est marqué « à vérifier » : l'humain valide toujours. Rien n'est fusionné automatiquement sous le seuil.

**Et si une colonne change de contenu sans changer de nom ?**
C'est pour ça que l'écran affiche des valeurs d'exemple. Les contrôles à l'import (dates illisibles, Oui/Non inattendu, codes étape inconnus) signalent aussi un contenu anormal dans le rapport d'anomalies.

**Pourquoi une API JSON plutôt que des pages PHP classiques ?**
Elle sépare la logique métier de l'affichage, permet de tester les calculs indépendamment (c'est ce qu'utilise le script de vérification) et laisse ouverte une autre interface (profil enseignant, étudiant).

**Pourquoi les règles métier dans une vue SQL ?**
Il y a un seul endroit où elles sont définies. Les indicateurs, la recherche et les exports utilisent la même définition, donc ils ne peuvent pas diverger. Les paramètres se modifient en base, sans redéploiement.

**Comment garantissez-vous que les chiffres sont justes ?**
Par une double implémentation indépendante : Python (pandas) sur le fichier brut, SQL dans l'application. Un script compare les deux : 48 sur 48 identiques. Les écarts trouvés en cours de route (arrondi, règle de comptage des CDD/CDI) ont été tranchés explicitement.

**Que se passe-t-il si on importe deux fois le même fichier ?**
Rien n'est dupliqué : la clé est (année universitaire, N° de convention), et la convention est mise à jour. Je l'ai testé : second import, 0 création et 1053 mises à jour.

**Le N° de convention est-il vraiment unique ?**
Il est unique dans le fichier 2025-2026, mais l'unicité entre années n'est pas garantie. D'où la clé composée avec l'année.

**Comment testez-vous l'évolution des colonnes sans autre année ?**
Avec un script qui génère des fichiers dérivés de la base réelle : colonnes renommées (5), supprimées (3), ajoutée (1), ordre changé. D'autres variantes suivront (types modifiés, fichier repassé par Excel).

**Performances ?**
1053 lignes importées en environ 3 s. Le volume est d'environ 1000 conventions par an, donc aucun enjeu de volumétrie, même sur 10 ans.

## Données et RGPD

**Vous travaillez sur des données personnelles ?**
En développement, sur une base anonymisée par ma tutrice école. En production, Dorothée Bernard importera des fichiers nominatifs : l'outil conserve les noms (étudiants, enseignants référents, tuteurs), dont elle a besoin, mais n'importe pas les colonnes inutiles (adresses, téléphones, e-mails personnels). Le fichier n'est pas conservé après import, et l'authentification sera en place avant toute mise en service. L'hébergement sera sur un serveur de l'école.

**Quelles conventions sont comptées ?**
Toutes sont importées, le filtre s'applique au calcul. Règle initiale (12/10) : conventions validées seulement, soit 884. Je lui ai montré que cela retirait environ 64 % des stages de PEIP, probablement en cours de validation au moment de l'extraction. Le 19/10, elle a précisé la règle : on ne retire que les **doublons non validés**, c'est-à-dire les conventions non validées d'un étudiant qui a déjà une convention validée. Cela donne 1 023 conventions. La règle est un paramètre ; elle sera appliquée en S44 et vérifiée avec le script de référence. Les chiffres présentés aujourd'hui sont ceux de la règle du 12/10.

**Pourquoi la règle « recherche » ne donne que 27 stages, alors que la thématique « Recherche » en compte 84 ?**
C'est la règle choisie par la DRE (mots-clés dans le sujet). Je lui ai présenté l'écart le 19/10 et elle a confirmé la règle. Les mots-clés sont paramétrables.

## Usage de l'IA

**Quelle part du travail a été faite par l'IA ?**
Claude Code a écrit une grande partie du code et m'a aidé sur les documents. Le travail qui ne se délègue pas, je l'ai fait moi-même : comprendre le besoin, faire trancher les règles par la DRE, choisir l'architecture avec l'expert technique, décider du périmètre, vérifier les résultats. L'IA exécute des décisions déjà prises.

**Comment être sûr que le code produit est juste ?**
Par la vérification croisée : un script Python indépendant recalcule tous les indicateurs depuis le fichier brut et les compare à l'application (48 sur 48). Chaque modification est relue avant d'être enregistrée dans Git, et l'outil est testé sur la base réelle et sur des fichiers aux colonnes modifiées.

**Comment l'avez-vous utilisée concrètement ?**
Avec un fichier de contexte que je tiens à jour (règles métier validées, contraintes RGPD, pièges des données, commandes), lu par l'outil à chaque session. Je demande une tâche à la fois et je lui fais d'abord exposer son plan et ses questions, avant toute modification. Exemple réel : « Dis-moi ce que tu vas faire et pose-moi des questions avant de faire quoi que ce soit. »

**Et les données personnelles ?**
L'IA ne travaille que sur la base anonymisée. Les scripts d'analyse ne produisent que des agrégats, jamais de lignes individuelles.

**Sauriez-vous maintenir le code sans l'IA ?**
*(À formuler avec mes mots.)* Piste : l'architecture est volontairement simple et documentée (une vue SQL pour les règles, une API, une interface sans framework), et je relis chaque modification.

## Gestion de projet

**Vous êtes seul : comment gérez-vous la charge ?**
Environ 15 h par semaine, soit 300 h pour 12 ECTS. Le périmètre est priorisé (indispensable, souhaitable, bonus), donc si je prends du retard, je coupe dans le souhaitable, pas dans l'indispensable.

**Quel est le plus gros risque ?**
L'hébergement : sans serveur de l'école, pas d'utilisation réelle sur données nominatives. Je contacte la DSI dès la semaine prochaine. L'application est conteneurisée, donc déployable facilement.

**Vous dites être en avance : qu'allez-vous faire du temps gagné ?**
Monter en qualité sur l'indispensable (règle des doublons, authentification, gestion des profils et référentiels dans l'interface, rapport d'import enrichi par niveau et spécialité). Puis traiter les fonctionnalités souhaitables : recherche plein texte, rapprochement des structures sans SIRET avec `pg_trgm`, graphiques, historique. Et surtout faire tester l'outil par l'utilisatrice plus tôt.

**Pourquoi data engineer plutôt qu'ingénieur logiciel ?**
La difficulté et la valeur du projet sont dans la chaîne complète de la donnée : ingestion de fichiers dont le schéma évolue, mapping, pré-traitement et qualité, modélisation, conformité RGPD, jusqu'à l'exploitation (indicateurs, export). L'interface est volontairement simple. Les compétences logicielles sont mobilisées, mais au service du pipeline.
