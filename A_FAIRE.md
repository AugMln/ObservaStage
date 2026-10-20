# À faire : projet ObservaStage

Mis à jour le 20/10/2026. Cocher au fur et à mesure (`[x]`). Planning détaillé : [gestion-projet/planning/gantt.html](gestion-projet/planning/gantt.html).

## Routines (toutes les semaines jusqu'à fin janvier)

- **Vendredi** : mail récapitulatif à Mme Kessaci, Dorothée Bernard et Juliette Achddou (avancement, difficultés, questions). Brouillons dans `gestion-projet/mails/`.
- **Trello** : à jour avant le mail du vendredi.
- **Réunion avec Dorothée Bernard** au moins une fois par semaine : préparation et compte rendu dans `gestion-projet/reunions/<date>_dre/`.
- Consulter les mails, le wiki et Moodle (consignes, dates).
- Expert technique (Bastien Cazaux) : jusqu'à 1 h par semaine, à solliciter avant les choix techniques importants (authentification, hébergement).

## D'ici la soutenance intermédiaire (S42-S43)

| Quand | Tâche | Fait |
|---|---|---|
| Ven 16/10 | Envoyer le mail hebdomadaire | [ ] |
| Ven 16/10 | Envoyer à Dorothée Bernard le CR du 12/10 (en PDF) et la table des codes étape | [ ] |
| Week-end | Relire le rapport et les slides, reformuler avec mes mots | [ ] |
| Week-end | 2 répétitions chronométrées, démo comprise (`notes_orales.md`) | [ ] |
| Lun 19/10 | Avec Dorothée Bernard : règle de validation, règle « recherche », critères de validation, code 2XIGAP (CR : `gestion-projet/reunions/2026-10-19_dre/`) | [x] |
| Lun 19/10, 11h | Soutenance blanche avec Mme Kessaci : noter les remarques (CR : `gestion-projet/reunions/2026-10-19_soutenance-blanche/`) | [x] |
| Mar 20/10 | Intégrer les remarques et les réponses de Dorothée au rapport et aux slides, régénérer les PDF | [x] |
| Mar 20/10 | Relire les slides, le rapport, `texte_oral.md` et `questions_jury.md` (en particulier la slide IA) et reformuler avec mes mots | [ ] |
| Mar 20/10 | Envoyer le mail sur le code 2XIGAP (`gestion-projet/mails/2026-10-20_brouillons.md`) | [ ] |
| Mar 20/10 | **Enregistrer la vidéo de secours de la démo** (`livrables/2026-10-21_soutenance-intermediaire/video_demo.md`) | [ ] |
| Mar 20/10 | Version finale, répétition chronométrée du nouveau support (13 slides), test de la démo avec `app/lancer.bat` sur la machine de la soutenance (Chrome) | [ ] |
| **Mer 21/10, avant 10h** | **Déposer sur Moodle `rapport_intermediaire.pdf` et `support_soutenance.pdf`** | [ ] |
| Mer 21/10, 15h | Soutenance intermédiaire (10 min + 5 min de questions) | [ ] |
| S43 | **Organiser la réunion tripartite intermédiaire** (Mme Kessaci, Dorothée Bernard) | [ ] |
| Après | Déposer sur Moodle le CR et les slides de la tripartite intermédiaire | [ ] |
| À vérifier | CR et slides de la tripartite de cadrage (28/09) bien déposés sur Moodle | [ ] |

Avant la démo : supprimer l'import et le profil du fichier test s'ils ont été créés (onglet Import), pour que l'écran de mapping montre bien des colonnes « à vérifier ».

## Points ouverts à trancher

- [x] Règle de prise en compte des conventions : tranchée le 19/10, seuls les doublons non validés (même étudiant) sont retirés → 1023. Mise en œuvre en S44 (voir Import).
- [ ] Faire confirmer à Dorothée Bernard le sens de « doublon » : 26 des 30 conventions retirées ne commencent pas à la même date que la convention validée (autres stages ?). Alternative : même étudiant **et** même période.
- [x] Règle « recherche » : mots-clés dans le sujet, maintenue le 19/10.
- [x] Critères de validation du prototype : validés le 19/10.
- [ ] Code étape 2XIGAP (« L1 adaptée » codé en 2e année, classé L2) : attendre la réponse au mail. Le reste de la table n'est pas remis en cause.
- [ ] Hébergement : qui contacter à la DSI ? Contraintes de sécurité, accès à PostgreSQL et PHP, HTTPS.
- [ ] Authentification : système de l'université (CAS) ou comptes locaux ? (à voir avec la DSI et l'expert)
- [ ] Tests utilisateurs : dates de disponibilité de Dorothée Bernard début décembre.

## Développement de la V1 (S44-S50)

**Import (S44-S46)**
- [ ] **Règle des doublons** (décision du 19/10) : nouvelle valeur de `regle_validation` dans `v_stage` (convention validée, ou non validée d'un étudiant sans aucune convention validée la même année). Report dans `exploration/indicateurs_reference.py`, relancer `verifier_application.py`, mettre à jour les chiffres cités (1023 attendu).
- [ ] Rapport d'import : répartition des anomalies et des valeurs manquantes par niveau et par spécialité (suggestion de Mme Kessaci, 19/10)
- [x] Test de robustesse : fichier ouvert puis enregistré dans Excel (.xls, .xlsx, .csv), voir `app/tests/README.md`
- [ ] Écran d'administration des profils de mapping (renommer, consulter les règles)
- [ ] Écran des paramètres métier (règle de validation, seuil d'heures, mots-clés) et des référentiels (codes étape)
- [ ] Code étape inconnu à l'import : pouvoir le rattacher à un niveau et une spécialité depuis l'interface
- [ ] Colonnes inconnues : envisager un champ « compléments » pour ne pas perdre d'information
- [ ] Contrôles supplémentaires (durée déclarée incohérente avec les dates et les heures hebdomadaires)
- [ ] Fichiers de test supplémentaires (colonnes supprimées, types modifiés, fichier repassé par Excel)

**Sécurité (S46-S47)**
- [ ] Authentification (obligatoire avant toute utilisation sur données réelles)
- [ ] Table des utilisateurs avec un rôle (préparation des futurs profils)
- [ ] Mots de passe de la base hors du code (`.env`), HTTPS en production

**Exploration (S47-S50)**
- [ ] Comparaison entre années (une ligne par année, évolution)
- [ ] Indicateurs par spécialité (pas seulement par niveau)
- [ ] Graphiques
- [ ] Recherche plein texte (sujet, fonctions et tâches) avec l'index PostgreSQL
- [ ] Rapprochement des structures sans SIRET par similarité (`pg_trgm`), avec validation humaine
- [ ] Fiche détaillée d'un stage (clic sur une ligne de la recherche)
- [ ] Export des indicateurs enrichi (par spécialité, par année)

À chaque nouvelle règle ou nouvel indicateur : l'ajouter aussi dans `exploration/indicateurs_reference.py` et relancer `verifier_application.py`.

## Déploiement (S44-S51)

- [ ] **S44 : premier contact avec la DSI** (principal risque du projet)
- [ ] Choix du serveur et des modalités d'accès
- [ ] Déploiement sur le serveur de l'école (S50-S51)
- [ ] Premier import réel (fichier non anonymisé) par Dorothée Bernard sur le serveur

## Jalons et livrables

| Date | Jalon | Fait |
|---|---|---|
| Lun 02/11 | Présentation en 180 secondes avec les alternants (hors projet, mais à préparer) | [ ] |
| Début décembre (S49-S50) | Tests utilisateurs avec Dorothée Bernard, puis corrections | [ ] |
| S51 (semaine du 14/12) | **Envoi de la première version du rapport final** | [ ] |
| S51 à S2 | Documentation technique et **guide utilisateur** (livrables attendus par la DRE) | [ ] |
| Ven 08/01/2027 | **Session démo** | [ ] |
| S2 (semaine du 11/01) | **Réunion tripartite de bilan** (à organiser), puis CR et slides sur Moodle | [ ] |
| Avant le 29/01 | Envoi de la version finale du rapport | [ ] |
| Ven 29/01/2027, après-midi | **Soutenance finale** : 20 min avec démo + 10 min de discussion | [ ] |
| S6 2027 | Si retravail demandé : nouvelle version (avis en S7) | [ ] |

**Rapport final** (environ 15 pages hors annexes) : contexte, problématique, démarche, solutions, résultats, discussion, conclusion, perspectives. Avec un **résumé en français et en anglais**, des références, les annexes utiles, et les **compétences ISIA : compétences développées, niveau atteint et justification pour chacune** (métier visé : data engineer).

## Ménage

- [ ] Supprimer le dossier `_a_supprimer/` (ancienne base v1, fichiers vides ou redondants)
