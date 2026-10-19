# Texte complet : soutenance intermédiaire du 21/10/2026

Texte à dire, slide par slide, calé sur le minutage de `notes_orales.md` (10 minutes, environ 140 mots par minute). Les indications entre crochets sont des actions, pas du texte.

Ne pas l'apprendre par cœur : le lire à voix haute deux ou trois fois, puis parler à partir des notes orales. Seules la première phrase de chaque slide et les phrases clés (en gras) méritent d'être mémorisées.

Chiffres calculés avec la règle actuelle (884 conventions validées). Si la règle change le 19/10, mettre à jour les passages marqués ⚠.

---

## Slide 1 : Titre (0:15)

Bonjour à toutes et à tous. Je vais vous présenter ObservaStage, un outil pour valoriser et suivre les données de stages de Polytech Lille. C'est un projet commandé par la Direction des Relations Entreprises, avec Dorothée Bernard comme référente.

## Slide 2 : Contexte (0:55)

La Direction des Relations Entreprises, la DRE, gère les conventions de stage de toutes les formations de l'école : PEIP, licences, masters et cycle ingénieur. Cela représente un peu plus de mille conventions par an.

Depuis 2025-2026, toutes ces conventions passent par EsupStage, l'outil national de gestion des conventions. EsupStage gère très bien une convention, mais il ne permet pas d'analyser l'ensemble. La seule chose qu'on peut en sortir, c'est un fichier Excel de 71 colonnes par année.

Résultat : aujourd'hui, 80 % des questions posées à la DRE sur les stages restent sans réponse. Pour les autres, chaque réponse demande environ une demi-heure de manipulations, et une seule personne gère toutes les conventions.

**Autrement dit, les données existent, mais personne ne peut les exploiter.** Et elles intéresseraient pourtant la direction, les enseignants et les futurs stagiaires.

## Slide 3 : Objectif (0:40)

L'objectif est donc de construire un outil qui permette d'importer le fichier EsupStage de l'année, de contrôler les données, de les structurer dans une base historisée, puis de les explorer : indicateurs, recherche et export.

L'utilisatrice de la première version est Dorothée Bernard. Elle doit pouvoir importer chaque année sans aide.

La difficulté centrale, c'est que le format du fichier change d'une année à l'autre : des colonnes sont renommées, déplacées, ajoutées ou supprimées. **L'import doit donc s'adapter sans qu'on touche au code.**

[Montrer le bas de la slide] Voici le type de questions auxquelles on veut pouvoir répondre en quelques clics.

## Slide 4 : Les données (0:50)

J'ai commencé par analyser la base réelle, anonymisée : 1 053 conventions, 71 colonnes. J'y ai trouvé quatre pièges.

D'abord, les intitulés sont trompeurs : ils ne sont même pas cohérents dans un seul fichier, et la colonne « Code NAF » contient en fait un libellé.

Ensuite, il faut déduire des informations absentes, comme le niveau ou la région. Le niveau et la spécialité, par exemple, sont fusionnés dans un seul libellé.

Troisième piège, la qualité est hétérogène : 18 % de cellules vides, et jusqu'à 175 heures de travail par semaine.

Et surtout, il n'y a aucun historique : c'est la première année d'EsupStage. Pour tester l'évolution des colonnes, j'utilise donc des fichiers modifiés à partir de la base réelle.

## Slide 5 : Cahier des charges (0:55)

Le périmètre de la première version a été validé avec la DRE le 12 octobre. Il est classé par priorité.

L'indispensable : un import avec un mapping configurable, des contrôles avec un rapport d'anomalies, les indicateurs (la durée moyenne en jours, semaines et heures, la part à l'étranger, en Hauts-de-France, par niveau et par spécialité), une recherche multicritère, et l'export en CSV et en XLS, qui a été ajouté à la demande de Dorothée Bernard.

Viennent ensuite le souhaitable, comme l'historique sur plusieurs années ou les graphiques, puis des bonus.

J'ai proposé quatre critères de validation mesurables : un import autonome et sans doublon, des indicateurs identiques à des chiffres de référence, la recherche, et l'export lisible dans Excel.

Côté contraintes, la stack est imposée par l'école, et les données de production seront nominatives, donc soumises au RGPD.

## Slide 6 : Architecture (0:35)

L'architecture est une chaîne classique de traitement de données : le fichier est lu, les colonnes sont associées par le mapping, les données sont contrôlées, stockées dans PostgreSQL, puis servies par une API en PHP à une interface web.

Trois choix importants. Les règles métier sont des paramètres stockés en base et appliqués à un seul endroit, on les modifie sans toucher au code. L'import est idempotent : réimporter un fichier met à jour les conventions, sans créer de doublon. Et tout tourne dans Docker, pour être déployé tel quel sur un serveur de l'école.

## Slide 7 : Mapping configurable (1:00)

Le mapping, c'est le cœur technique du projet. Quand un fichier arrive, chaque colonne est reconnue en quatre étapes, de la plus sûre à la moins sûre.

Un : si le fichier a exactement les mêmes colonnes qu'un import précédent, le profil connu est appliqué directement.
Deux : si une colonne a déjà été rencontrée, la décision passée est reprise.
Trois : on compare l'intitulé à une liste d'alias connus, après avoir retiré les accents, la casse et la ponctuation.
Quatre : sinon, on calcule une similarité entre les intitulés, et on fait une proposition marquée « à vérifier ».

L'utilisatrice valide en voyant des valeurs d'exemple, et son choix devient un profil réutilisable l'année suivante.

Sur le fichier de test, aux colonnes modifiées, l'outil en reconnaît 34, en propose 2 à vérifier et en signale 3 nouvelles. **Seules les différences sont à traiter.**

## Slide 8 : Fiabilité et RGPD (0:45)

Pour que les chiffres soient fiables, chaque règle est explicite et validée avec la DRE : seules les conventions validées sont comptées, l'étranger se définit par le pays du service d'accueil, et les valeurs aberrantes sont signalées et exclues, jamais corrigées.

Pour vérifier les chiffres, un script Python indépendant recalcule tous les indicateurs à partir du fichier brut, puis les compare à ceux de l'application. **48 indicateurs sur 48 sont identiques.**

Côté RGPD : les colonnes inutiles, comme les adresses ou les téléphones, ne sont pas stockées, le fichier est supprimé après l'import, et les noms ne seront accessibles qu'à l'utilisatrice habilitée, avec une authentification et un hébergement à l'école.

## Slide 9 : Démonstration (1:45)

Je vous montre maintenant l'ébauche, qui fonctionne déjà sur la base réelle anonymisée.

[Basculer sur le navigateur, onglet Tableau de bord]

Voici le tableau de bord. ⚠ Sur les 884 stages validés, la durée moyenne est de 14,4 semaines, et un quart des stages se déroulent à l'étranger.

[Filtre Niveau = 4A] Si je filtre sur la 4e année, on voit qu'environ 60 % des stages sont à l'étranger. C'est exactement le type de question qui restait sans réponse.

[Onglet Recherche, Localisation = Étranger, mot-clé « robot »] Dans la recherche, je cherche les stages à l'étranger qui parlent de robotique : 8 stages. [Cliquer sur XLS] Et je peux exporter ce résultat dans Excel.

[Onglet Import, choisir le fichier de test, Analyser] Enfin, l'import. J'ouvre un fichier dont les colonnes ont été modifiées. L'outil reconnaît la plupart des colonnes, et me demande de décider uniquement pour celles qui ont changé. Par exemple, « Domaine d'activité » correspond en fait à la thématique, et « Temps de travail hebdo » au nombre d'heures par semaine. Je les associe, et le profil est enregistré pour la prochaine fois.

[Revenir aux slides]

Ce qui manque encore : l'authentification, les écrans de paramètres, le rapprochement des structures et les graphiques.

> Plan B si la démo ne marche pas : rester sur la slide 9 et commenter la capture (« Voici le tableau de bord… »).
> Si j'arrive à la slide 9 après 6:15 : ne montrer que le tableau de bord et l'écran de mapping.

## Slide 10 : Avancement et difficultés (0:50)

Côté gestion de projet : après le lancement, le cadrage tripartite et une réunion technique fin septembre, j'ai analysé les données, puis validé quatorze règles métier avec la DRE le 12 octobre.

Pour m'organiser : un Trello partagé, un mail récapitulatif chaque semaine, un compte rendu pour chaque réunion, et Git.

Trois difficultés : la base complète n'est arrivée que début octobre, il n'y a pas d'historique, et les données sont peu propres, avec des règles implicites à expliciter une par une.

**Aujourd'hui, je suis en avance** : l'ébauche va déjà de l'import à l'export, alors que le calendrier initial prévoyait le module d'import en novembre.

## Slide 11 : Gantt (0:40)

Voici le planning, sur environ 15 heures par semaine, soit 300 heures. La ligne rouge marque aujourd'hui.

En novembre et début décembre, je consolide l'import, j'ajoute l'authentification et les fonctionnalités d'exploration, et je prépare le déploiement. Des tests avec Dorothée Bernard sont prévus début décembre. Ensuite viennent le rapport, mi-décembre, la session démo le 8 janvier et la soutenance finale le 29 janvier.

Le point d'attention, c'est l'hébergement : je contacte la DSI dès la semaine prochaine.

## Slide 12 : Suite du projet (0:45)

Pour la suite, le périmètre est revu à la hausse, grâce à l'avance : recherche plein texte, rapprochement des structures, graphiques, historique. Et à la baisse sur le type d'expérience, CDD ou intérim : saisi en texte libre pour seulement 164 conventions, l'indicateur restera partiel.

Prochaines étapes : consolider l'import, ajouter l'authentification, contacter la DSI, et faire tester l'outil par la DRE avant la démo de janvier.

Enfin, ce projet correspond au métier de **data engineer** : son cœur est un pipeline d'ingestion de données dont le schéma évolue, avec du mapping, de la qualité de données, de la modélisation et de la conformité RGPD.

Merci de votre attention, je suis prêt à répondre à vos questions.
