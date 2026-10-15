# Questions probables du jury et éléments de réponse

## Sujet et besoin

**Pourquoi ne pas utiliser simplement Excel, ou un outil de BI (Power BI, Metabase) ?**
Excel demande de refaire chaque année les mêmes manipulations, environ 30 minutes par question, et ne garde pas d'historique. Un outil de BI suppose des données déjà propres et structurées : le problème est justement en amont (import d'un format qui change, nettoyage, règles métier). De plus, la stack est imposée par l'école (PHP, PostgreSQL) pour la maintenance et l'hébergement. Rien n'empêche plus tard de brancher un outil de BI sur la base PostgreSQL.

**EsupStage n'a-t-il pas déjà des statistiques ?**
EsupStage propose des extractions, pas d'analyse multi-critères ni d'historique ; c'est ce qui a motivé le sujet. *(À confirmer avec Dorothée Bernard si la question est posée précisément.)*

**Qui va maintenir l'outil après le projet ?**
Le choix de la stack standard de l'école est fait pour ça. Le paramétrage (mapping, règles, référentiels) se fait sans code. La documentation technique et le guide utilisateur font partie des livrables. Point à clarifier avec la DSI lors du contact pour l'hébergement.

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

**Pourquoi seulement les conventions validées ?**
C'est la règle demandée par la DRE : une convention non validée peut être abandonnée. Toutes sont importées, le filtre s'applique au calcul, et la règle est paramétrable (validation administrative seule : 884 ; administrative ou pédagogique : 1023). Point ouvert : les PEIP non validées étaient probablement en cours de validation au moment de l'extraction.

**Pourquoi la règle « recherche » ne donne que 27 stages, alors que la thématique « Recherche » en compte 84 ?**
C'est la règle choisie par la DRE (mots-clés dans le sujet). L'écart lui sera présenté pour qu'elle confirme. Les mots-clés sont paramétrables.

## Gestion de projet

**Vous êtes seul : comment gérez-vous la charge ?**
Environ 15 h par semaine, soit 300 h pour 12 ECTS. Le périmètre est priorisé (indispensable, souhaitable, bonus), donc si je prends du retard, je coupe dans le souhaitable, pas dans l'indispensable.

**Quel est le plus gros risque ?**
L'hébergement : sans serveur de l'école, pas d'utilisation réelle sur données nominatives. Je contacte la DSI dès la semaine prochaine. L'application est conteneurisée, donc déployable facilement.

**Vous dites être en avance : qu'allez-vous faire du temps gagné ?**
Monter en qualité sur l'indispensable (authentification, gestion des profils et référentiels dans l'interface, contrôles plus fins). Puis traiter les fonctionnalités souhaitables : recherche plein texte, rapprochement des structures sans SIRET avec `pg_trgm`, graphiques, historique. Et surtout faire tester l'outil par l'utilisatrice plus tôt.

**Pourquoi data engineer plutôt qu'ingénieur logiciel ?**
La difficulté et la valeur du projet sont dans la chaîne de données : ingestion de fichiers dont le schéma évolue, mapping, qualité, modélisation, conformité RGPD. L'interface est volontairement simple. Les compétences logicielles sont mobilisées, mais au service du pipeline.
