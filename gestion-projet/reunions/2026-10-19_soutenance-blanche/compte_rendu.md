# Compte rendu de la soutenance blanche du 19/10/2026

Projet ingénieur 2026-2027 : Conception d'un outil de valorisation et de suivi des données de stages
Participants : Marie-Éléonore Kessaci (tutrice école), Augustin Mélon
Objet : répétition de la soutenance intermédiaire du 21/10/2026 et remarques sur le support

Les numéros de slides renvoient à la version présentée le 19/10 (12 slides). Le support a été revu le 20/10 (13 slides + 2 annexes).

---

## 1. Remarques générales

- **Montrer davantage que j'ai compris le besoin.**
- **Expliquer l'usage de l'IA** : montrer qu'elle a été utilisée de manière réfléchie (vérification, garde-fous, façon de formuler les demandes), et pas comme une boîte noire.
- **Mettre davantage en avant la gestion de projet**, et moins le côté technique.
- **Préparer une vidéo de la démo**, au cas où la démo en direct ne fonctionnerait pas.

## 2. Remarques slide par slide

| Slide | Remarque | Prise en compte (20/10) |
|---|---|---|
| 3 | Remplacer « explorer » par « exploiter ». Le contrôle est en réalité un pré-traitement qui fait partie de l'import. Au lieu de parler de « difficulté centrale », mettre en avant l'adaptabilité de la chaîne. | Slide 4 : « Importer › Structurer › Exploiter », le pré-traitement est intégré à l'import, encadré « Une chaîne qui s'adapte ». Même vocabulaire dans le rapport. |
| 4 | Le SIRET absent pour les structures étrangères est normal : c'est une particularité de la base, pas un problème de qualité. | Slide 5 et rapport : le SIRET est sorti de « qualité hétérogène ». |
| 6 | Dire que les technologies m'ont été imposées. | Slides 6 et 7, rapport. |
| 8 | Ne pas parler de « validation croisée » (terme de statistiques avec un autre sens), mais de « vérification croisée ». | Slide 8, rapport (§3.4), notes, questions du jury. |
| 11 | Gantt illisible. N'afficher que les grandes phases et garder le détail en réserve. Le Gantt doit montrer que la V1 est déjà prête. | Slide 11 : Gantt simplifié (`gantt_phases.png`). Gantt détaillé en annexe, mis à jour (V1 faite, phase « Consolidation et enrichissement »). |
| 12 | Le cœur du projet n'est pas seulement le pipeline d'ingestion, mais toute la chaîne de la donnée. | Slide 13 et rapport §5 : « chaîne complète de la donnée, du fichier brut à l'indicateur ». |

## 3. Suggestion pour la suite

- **Import** : ajouter au rapport d'import des indicateurs de répartition des anomalies et des valeurs manquantes par niveau et par spécialité (par exemple, les formations où les données manquent le plus). Pas pour la soutenance du 21/10 : à faire pendant la consolidation de l'import (S44-S46).

## 4. Changements apportés au support

- Nouvelle slide 3 « Comprendre le besoin » : des questions de la DRE aux règles validées, et la démarche suivie avec l'utilisatrice.
- Nouvelle slide 12 « Usage de l'IA » : cadrer avant, vérifier après, garder la main.
- Slide 10 « Gestion de projet » enrichie : cycle préparation → compte rendu → actions, priorisation, risques suivis.
- Architecture et mapping fusionnés (slide 7) ; l'architecture détaillée passe en annexe.
- « Ébauche » remplacé par « V1 fonctionnelle ».
- Texte oral, notes orales et questions du jury mis à jour ; scénario de la vidéo de secours dans `livrables/2026-10-21_soutenance-intermediaire/video_demo.md`.

## 5. Actions

| Action | Responsable | Échéance | État |
|---|---|---|---|
| Intégrer les remarques aux slides et au rapport, régénérer les PDF | Augustin | 20/10 | Fait |
| Enregistrer la vidéo de secours de la démo | Augustin | 20/10 | À faire |
| Répéter avec le nouveau support (13 slides, 10 min) | Augustin | 20/10 | À faire |
| Déposer le rapport et le support sur Moodle | Augustin | 21/10, 10h | À faire |
| Rapport d'import : répartition des anomalies par niveau et spécialité | Augustin | S44-S46 | À faire |
