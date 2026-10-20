# Vidéo de secours de la démonstration

Demandée par Mme Kessaci lors de la soutenance blanche (19/10) : si la démo en direct ne marche pas, lancer cette vidéo et la commenter en direct. Durée visée : **1 min 30**, sans son (je commente pendant la diffusion).

Fichier attendu : `livrables/2026-10-21_soutenance-intermediaire/demo_observastage.mp4`. Ne pas le versionner (taille), le copier sur la clé USB avec les PDF.

## Préparation (10 min)

1. `cd app ; docker compose up -d`, puis ouvrir http://localhost:8080 dans Chrome.
2. Base remplie avec le fichier 2025-2026. Onglet Import : supprimer l'import et le profil du fichier test s'ils existent, pour que l'écran de mapping montre bien des colonnes « à vérifier ».
3. Fichier de test présent : `donnees/tests/esupstage_test_colonnes_modifiees.xlsx` (sinon `.\venv\Scripts\python.exe exploration\generer_fichier_test.py`).
4. Chrome en plein écran (F11), zoom 125 %, onglet Tableau de bord. Fermer les autres onglets et couper les notifications Windows (mode Ne pas déranger).

## Enregistrement

Avec la **Xbox Game Bar** de Windows : `Win + Alt + R` pour démarrer et arrêter (vidéo dans `Vidéos\Captures`). Ou avec OBS Studio, en capture de la fenêtre Chrome.

Faire des gestes lents, laisser chaque écran affiché 2 à 3 secondes : je dois pouvoir parler dessus.

| Temps | Action | Ce que je dirai par-dessus |
|---|---|---|
| 0:00-0:10 | Tableau de bord, sans filtre. Survoler les cartes. | « 884 stages validés, 14,4 semaines en moyenne, un quart à l'étranger. » |
| 0:10-0:25 | Filtre Niveau = 4A. Pause sur la carte « À l'étranger ». | « En 4A, environ 60 % des stages sont à l'étranger. » |
| 0:25-0:30 | Réinitialiser les filtres. | |
| 0:30-0:50 | Onglet Recherche : Localisation = Étranger, mot-clé « robot », Rechercher. Pause sur les résultats (8). | « Les stages à l'étranger qui parlent de robotique. » |
| 0:50-0:55 | Cliquer sur XLS, montrer le téléchargement. | « Export dans Excel. » |
| 0:55-1:25 | Onglet Import : choisir le fichier de test, Analyser. Faire défiler lentement la liste des colonnes, s'arrêter sur les lignes « à vérifier » et « nouvelle ». Associer « Domaine d'activité » → Thématique et « Temps de travail hebdo » → Heures hebdomadaires. **Ne pas importer.** | « Colonnes modifiées : l'outil n'en signale que quelques-unes, je décide seulement pour celles-là. » |
| 1:25-1:30 | Revenir au tableau de bord. | |

Éviter de montrer des noms (résultats de recherche : ne pas ouvrir de détail, rester sur la vue liste et défiler peu).

## Après

- [ ] Recouper la vidéo si besoin (application Photos ou Clipchamp de Windows : « Découper »).
- [ ] La regarder en entier une fois, en commentant à voix haute avec le chronomètre.
- [ ] La copier sur la clé USB et la tester sur la machine de la soutenance (lecteur par défaut).
- [ ] Remettre l'application dans son état initial (supprimer le profil créé s'il a été enregistré).
