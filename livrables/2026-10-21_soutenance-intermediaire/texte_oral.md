# Texte complet : soutenance intermédiaire du 21/10/2026

Texte à dire, slide par slide, calé sur le minutage de `notes_orales.md` (10 minutes, environ 140 mots par minute). Les indications entre crochets sont des actions, pas du texte.

Ne pas l'apprendre par cœur : le lire à voix haute deux ou trois fois, puis parler à partir des notes orales. Seules la première phrase de chaque slide et les phrases clés (en gras) méritent d'être mémorisées.

Version revue après la soutenance blanche du 19/10 : plus de besoin et de gestion de projet, moins de technique. Les chiffres sont ceux de la règle du 12/10 (884 conventions validées) : la règle des doublons, décidée le 19/10, sera appliquée en S44.

---

## Slide 1 : Titre (0:15)

Bonjour à toutes et à tous. Je vais vous présenter ObservaStage, un outil pour valoriser et suivre les données de stages de Polytech Lille. C'est un projet commandé par la Direction des Relations Entreprises, avec Dorothée Bernard comme référente.

## Slide 2 : Contexte (0:45)

La Direction des Relations Entreprises, la DRE, gère les conventions de stage de toutes les formations de l'école : un peu plus de mille par an.

Depuis 2025-2026, elles passent toutes par EsupStage, l'outil national. EsupStage gère très bien une convention, mais il ne permet pas d'analyser l'ensemble : on ne peut en sortir qu'un fichier Excel de 71 colonnes.

Résultat : 80 % des questions posées à la DRE sur les stages restent sans réponse, et les autres demandent environ une demi-heure de manipulations chacune. **Les données existent, mais personne ne peut les exploiter.**

## Slide 3 : Comprendre le besoin (1:00)

Avant d'écrire du code, j'ai voulu comprendre exactement ce que la DRE attend. Je suis parti de ses vraies questions.

[Montrer le tableau] Prenons « combien de stages à l'étranger ? ». La question paraît simple, mais il faut préciser : le pays du siège de l'entreprise, ou celui du lieu où se déroule le stage ? Même chose pour « combien de stages ? » : faut-il compter toutes les conventions, même celles qui ne sont pas validées ?

Chaque question cache une règle implicite. Pour chacune, j'ai chiffré les options sur les données réelles, puis je les ai présentées à Dorothée Bernard pour qu'elle tranche.

[Montrer l'encadré] Ce travail s'est fait en trois réunions : le cadrage, les règles métier le 12 octobre, et la validation lundi dernier. Chaque réunion est préparée et suivie d'un compte rendu. Résultat : **quatorze règles explicites et validées, un périmètre priorisé avec elle, et des critères de validation qu'elle a acceptés.**

## Slide 4 : Objectif (0:35)

L'objectif est donc un outil qui importe le fichier EsupStage de l'année (le pré-traitement et les contrôles font partie de l'import), qui structure les données dans une base historisée, puis qui permet de les exploiter : indicateurs, recherche et export.

L'utilisatrice, Dorothée Bernard, doit pouvoir importer chaque année sans aide.

Et le format du fichier peut changer d'une année à l'autre. **La force de l'outil est d'être adaptable : les colonnes, les règles métier et les référentiels s'ajustent par paramétrage, sans toucher au code.**

## Slide 5 : Les données (0:40)

J'ai analysé la base réelle anonymisée : 1 053 conventions, 71 colonnes.

Les intitulés sont trompeurs : ils ne sont même pas cohérents dans un seul fichier, et « Code NAF » contient un libellé. Plusieurs informations sont à déduire, comme le niveau et la spécialité, fusionnés dans un seul libellé. La base a aussi ses particularités : les structures étrangères n'ont pas de SIRET, ce qui est normal, mais il faut une autre façon de les identifier. La qualité est hétérogène, avec jusqu'à 175 heures par semaine. Et surtout, c'est la première année d'EsupStage : je teste l'évolution des colonnes sur des fichiers modifiés.

## Slide 6 : Cahier des charges (0:40)

Le périmètre a été priorisé avec la DRE. L'indispensable : l'import avec un mapping configurable, les contrôles, les indicateurs, la recherche multicritère, et l'export en CSV et XLS, ajouté à sa demande. Viennent ensuite le souhaitable et les bonus.

J'ai proposé quatre critères de validation mesurables, **qu'elle a validés lundi** : un import autonome sans doublon, des indicateurs identiques à des chiffres de référence, la recherche, et l'export.

Les technologies m'ont été imposées par l'école, et les données de production seront nominatives, donc soumises au RGPD.

## Slide 7 : La chaîne de la donnée (1:00)

Voici la chaîne complète. L'import regroupe la lecture du fichier, l'association des colonnes, le mapping, et le pré-traitement. Les données vont ensuite dans PostgreSQL, puis une API en PHP les sert à l'interface. Ces technologies sont le standard des applications de Polytech, et elles m'ont été imposées.

La brique qui rend la chaîne adaptable, c'est le mapping. Chaque colonne est reconnue en quatre étapes, de la plus sûre à la moins sûre : un profil connu, une colonne déjà rencontrée, un alias connu, puis une similarité, qui donne une proposition « à vérifier ».

[Montrer la capture] Sur un fichier de test aux colonnes modifiées, l'outil en reconnaît 34, en propose 2 à vérifier et en signale 3 nouvelles. **Seules les différences sont à traiter, et l'utilisatrice valide toujours.**

## Slide 8 : Fiabilité et RGPD (0:40)

Pour que les chiffres soient fiables, chaque règle est explicite et validée. Lundi, la DRE a précisé la règle de comptage : on ne retire que les doublons non validés d'un même étudiant. Elle sera appliquée la semaine prochaine.

Pour vérifier les chiffres, je fais une vérification croisée : un script Python indépendant recalcule tous les indicateurs à partir du fichier brut, et les compare à ceux de l'application. **48 sur 48 sont identiques.**

Côté RGPD : les colonnes inutiles ne sont pas stockées, le fichier est supprimé après l'import, et les noms ne seront accessibles qu'à l'utilisatrice, avec une authentification.

## Slide 9 : Démonstration (1:30)

Je vous montre la V1, qui fonctionne déjà sur la base réelle anonymisée.

[Basculer sur le navigateur, onglet Tableau de bord]

Voici le tableau de bord : 884 stages validés, 14,4 semaines en moyenne, un quart à l'étranger.

[Filtre Niveau = 4A] En 4e année, environ 60 % des stages sont à l'étranger. C'est exactement le type de question qui restait sans réponse.

[Onglet Recherche, Localisation = Étranger, mot-clé « robot », puis XLS] Les stages à l'étranger qui parlent de robotique : 8. Je peux les exporter dans Excel.

[Onglet Import, fichier de test, Analyser] Enfin, l'import d'un fichier aux colonnes modifiées : l'outil ne me demande de décider que pour celles qui ont changé. « Domaine d'activité », c'est la thématique ; « Temps de travail hebdo », les heures par semaine.

[Revenir aux slides]

Avant la mise en service, il reste l'authentification, les écrans de paramètres et la nouvelle règle des doublons.

> Plan B si la démo ne marche pas : lancer la vidéo `demo_observastage.mp4` (voir `video_demo.md`) et la commenter. Si la vidéo ne marche pas non plus, rester sur la slide 9 et commenter la capture.
> Si j'arrive à la slide 9 après 6:00 : ne montrer que le tableau de bord et l'écran de mapping.

## Slide 10 : Gestion de projet (0:50)

Côté gestion de projet, j'ai suivi une démarche itérative, avec une validation à chaque étape : le cadrage, une réunion technique pour l'architecture, l'analyse des données, puis les règles métier, la V1, et la validation lundi.

Chaque réunion suit le même cycle : une préparation, avec les décisions à prendre et les options chiffrées, puis un compte rendu et des actions. J'envoie un mail récapitulatif chaque semaine, et le Trello est partagé. Le périmètre est priorisé : si je prends du retard, je coupe dans le souhaitable, jamais dans l'indispensable.

Les difficultés : les données sont arrivées tard, il n'y a pas d'historique, et les règles implicites ont dû être explicitées une par une.

**Aujourd'hui, je suis en avance : la V1 va déjà de l'import à l'export.**

## Slide 11 : Planning (0:30)

Le planning, en grandes phases. Le cadrage et la V1 sont faits. Jusqu'à mi-décembre, je consolide l'outil : l'import, l'authentification, puis les fonctionnalités souhaitables, en parallèle de l'hébergement, qui est le principal risque. Tests avec Dorothée Bernard début décembre, puis la session démo le 8 janvier et la soutenance fin janvier. Le détail est en annexe.

## Slide 12 : Usage de l'IA (0:50)

J'ai utilisé l'IA, Claude Code, pour écrire le code et une partie des documents. Mais je l'ai encadrée.

D'abord, je cadre avant : l'outil a un fichier de contexte, que je tiens à jour, avec les règles validées, les contraintes RGPD et les pièges des données. Les décisions sont prises avec la DRE avant d'être codées. Et je travaille une tâche à la fois, en lui demandant d'exposer son plan et ses questions avant d'agir. [Montrer la consigne] Voici une consigne réelle, d'hier.

Ensuite, je vérifie après : les chiffres sont calculés deux fois de façon indépendante, chaque modification est relue avant d'être enregistrée, et testée.

Enfin, je garde la main : l'IA ne voit que la base anonymisée, et les décisions restent les miennes.

**Le temps gagné sur l'écriture, je l'ai réinvesti dans la compréhension du besoin et dans la vérification.**

## Slide 13 : Suite du projet (0:40)

Pour la suite, le périmètre est revu à la hausse grâce à l'avance : recherche plein texte, rapprochement des structures, graphiques, historique. Il est revu à la baisse sur le type d'expérience, qui restera partiel.

Prochaines étapes : la règle des doublons, un rapport d'import enrichi, l'authentification, le contact avec la DSI, et les tests avec la DRE.

Ce projet correspond au métier de **data engineer** : son cœur est la chaîne complète de la donnée, du fichier brut à l'indicateur.

Merci de votre attention, je suis prêt à répondre à vos questions.
