# Préparation de la soutenance blanche avec Mme Kessaci

Projet ingénieur 2026-2027 : Conception d'un outil de valorisation et de suivi des données de stages
Participants : Marie-Éléonore Kessaci (tutrice école), Augustin Mélon
Date : 19/10/2026, 11h
Objectif : répéter la soutenance intermédiaire du 21/10 en conditions réelles (10 min + 5 min de questions), recueillir ses remarques sur le fond, la forme et le rapport, et régler les points d'organisation (tripartite, RGPD, DSI).

---

## 0. Avant la réunion

- [ ] Slides à jour (`livrables/2026-10-21_soutenance-intermediaire/support_soutenance.pdf`) sur l'ordinateur et sur une clé USB.
- [ ] Rapport intermédiaire imprimé (ou envoyé avant la réunion) pour qu'elle puisse le relire.
- [ ] Démo prête : `docker compose up -d`, base remplie, onglet Tableau de bord, zoom 125 %, profil et import du fichier de test supprimés.
- [ ] Chronomètre visible (téléphone posé sur la table).
- [ ] `notes_orales.md` relu ; `questions_jury.md` à portée de main.
- [ ] Décisions prises le matin avec Dorothée Bernard notées (voir `../2026-10-19_dre/preparation.md`).

---

## 1. Déroulé (environ 45 min)

| # | Sujet | Durée |
|---|---|---|
| 1 | Point rapide : décisions du matin avec Dorothée Bernard, mail de vendredi pas encore envoyé | 2 min |
| 2 | Soutenance en conditions réelles, démo comprise, **sans interruption** | 10 min |
| 3 | Questions comme le jury | 5 min |
| 4 | Ses remarques, slide par slide | 15 min |
| 5 | Rapport intermédiaire | 5 min |
| 6 | Organisation (section 5) | 5 min |

Lui demander au début de jouer le jury : un public qui ne connaît ni la DRE ni EsupStage.

---

## 2. Point du matin (2 min)

- Règle de comptage des conventions : ____________ (884 ou 1023). Si 1023 : les chiffres des slides changent d'ici mercredi.
- Règle « recherche » : ____________
- Critères de validation : ____________
- Présence de Dorothée Bernard à la soutenance : ____________

---

## 3. Grille de remarques (à remplir pendant qu'elle parle)

Temps total réalisé : ______ (objectif 10:00 ; repère : slide 9 avant 6:15).

| Slide | Contenu | Durée visée | Remarques |
|---|---|---|---|
| 1 | Titre | 0:15 | |
| 2 | Contexte | 0:55 | |
| 3 | Objectif, difficulté centrale | 0:40 | |
| 4 | Les données | 0:50 | |
| 5 | Cahier des charges | 0:55 | |
| 6 | Architecture | 0:35 | |
| 7 | Mapping configurable | 1:00 | |
| 8 | Fiabilité, RGPD | 0:45 | |
| 9 | Démo en direct | 1:45 | |
| 10 | Avancement, difficultés | 0:50 | |
| 11 | Gantt | 0:40 | |
| 12 | Recadrage, suite, ISIA | 0:45 | |

---

## 4. Questions à lui poser

### Sur la présentation

1. **Le niveau** est-il adapté à un jury qui ne connaît pas le sujet ? Les slides 6 (architecture) et 7 (mapping) sont-elles trop techniques ?
2. **Les attendus** de la soutenance intermédiaire sont-ils tous couverts : contexte, cahier des charges, objectifs, étapes clés, avancement, prochaines actions, Gantt, points techniques, compétences ISIA ? Un point trop court ou trop long ?
3. **La démo en direct** : la garder, ou passer à des captures pour limiter le risque ? Le temps de brancher l'ordinateur est-il compté dans les 10 minutes ?
4. **Le positionnement data engineer** (slide 12) est-il convaincant et suffisamment justifié ?
5. **Le message principal** : qu'a-t-elle retenu ? (Il devrait ressortir : « les données existent mais sont inexploitables ; l'import s'adapte aux changements de colonnes ; les chiffres sont vérifiés ».)

### Sur le rapport intermédiaire

6. Respecte-t-il les attentes (5 pages max, contenu, niveau de détail) ? Les annexes sont-elles autorisées ou comptées ?
7. Une partie à renforcer ou à couper ?

### Sur l'organisation

Voir section 5.

---

## 5. Organisation

- **Réunion tripartite intermédiaire** (S43, à mon initiative) : proposer les créneaux obtenus le matin auprès de Dorothée Bernard.
  Créneau retenu : ____________
- **Moodle** : le compte rendu et les slides de la tripartite de cadrage (28/09) sont-ils bien déposés et conformes ? Format attendu pour le dépôt du 21/10 (nom des fichiers, PDF) ?
- **RGPD** : elle a anonymisé la base. Le dispositif prévu lui convient-il ?
  - noms et e-mails universitaires conservés (besoin de la DRE) ;
  - adresses, téléphones, e-mails personnels, sexe, signataire non importés ;
  - fichier téléversé supprimé après import ;
  - authentification avant toute utilisation sur données réelles ;
  - hébergement sur un serveur de l'école.
  Faut-il prévenir le DPO de l'université ou inscrire l'outil au registre des traitements ?
- **Hébergement** : connaît-elle un interlocuteur à la DSI ? Premier contact prévu en S44.
- **Présentation en 180 secondes** (02/11, avec les alternants) : qu'est-ce qui est attendu ?

---

## 6. Questions qu'elle risque de poser (réponses dans `questions_jury.md`)

- Pourquoi pas Excel ou un outil de BI ?
- Comment le mapping reconnaît-il une colonne renommée ? Et si le contenu change sans le nom ?
- Comment garantir que les chiffres sont justes ? (double calcul indépendant, 48/48)
- Comment tester l'évolution des colonnes sans autre année ?
- Pourquoi seulement les conventions validées ? (réponse à adapter selon la décision du matin)
- Vous êtes seul : comment gérer la charge ? Quel est le plus gros risque ?
- Qu'allez-vous faire de l'avance prise ?
- Pourquoi data engineer ?

Noter ici les questions auxquelles j'ai mal répondu, pour compléter `questions_jury.md` :

- ____________
- ____________

---

## 7. Après la réunion

- [ ] Rédiger `compte_rendu.md` dans ce dossier (remarques et décisions).
- [ ] Intégrer ses remarques aux slides et au rapport, puis régénérer les PDF (Chrome headless).
- [ ] Mettre à jour `notes_orales.md` (minutage réel, formulations) et `questions_jury.md`.
- [ ] Envoyer l'invitation pour la tripartite intermédiaire aux deux tutrices.
- [ ] Mardi 20/10 : dernière répétition et test de la démo sur la machine de la soutenance.
- [ ] **Mercredi 21/10, avant 10h** : déposer `rapport_intermediaire.pdf` et `support_soutenance.pdf` sur Moodle.
