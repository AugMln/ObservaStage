# Compte rendu de la réunion du 19/10/2026

Projet ingénieur 2026-2027 : Conception d'un outil de valorisation et de suivi des données de stages
Participants : Dorothée Bernard (Direction des Relations Entreprises), Augustin Mélon
Objet : trancher les points restés ouverts le 12/10 (règle de comptage, règle « recherche », critères de validation, table des codes étape) avant la soutenance intermédiaire

Les chiffres portent sur la base anonymisée 2025-2026 (1053 conventions, 939 étudiants). Ceux de la section 1 ont été calculés après la réunion.

---

## 1. Conventions prises en compte

**Décision** : ne retirer des indicateurs que les **doublons non validés**. Un doublon est une convention du **même étudiant** (même N° étudiant, même année universitaire).

Règle retenue :
- une convention validée (validation administrative, « Validation convention = Oui ») est toujours comptée ;
- une convention non validée est retirée si le même étudiant a au moins une convention validée la même année ;
- une convention non validée d'un étudiant qui n'a aucune convention validée est comptée (on ne peut pas dire laquelle serait le « bon » stage).

| | Règle du 12/10 (validées seules) | Règle du 19/10 (doublons non validés retirés) |
|---|---|---|
| Conventions comptées | 884 | **1023** |
| Conventions non validées retirées | 169 | 30 |
| Conventions non validées conservées | 0 | 139 (128 étudiants) |

Les 139 conventions conservées concernent surtout les PEIP / L1 (89, codes étape en 1), puis la 3e année (39).

> **Point à vérifier avec Dorothée Bernard.** Parmi les 30 conventions retirées, **4 seulement** commencent à la même date qu'une convention validée du même étudiant. Les 26 autres correspondent probablement à **d'autres stages** du même étudiant (autre période, autre structure), et pas à une saisie en double. Il faudra lui montrer ces chiffres, pour qu'elle confirme que ces conventions doivent bien être retirées. Sinon, on peut définir le doublon comme « même étudiant et même période ».
>
> À l'inverse, 8 étudiants sans aucune convention validée ont plusieurs conventions non validées (19 conventions). Certaines semblent être de vrais doublons (même structure, mêmes dates), mais elles sont toutes conservées par la règle actuelle.

**Mise en œuvre** : après la soutenance (S44). Nouvelle valeur du paramètre `regle_validation` dans la vue `v_stage`, report dans `exploration/indicateurs_reference.py`, puis vérification avec `verifier_application.py`. Les chiffres de la soutenance du 21/10 restent ceux de la règle du 12/10 (884).

## 2. Stages « recherche »

**Décision** : la règle est **maintenue**. On cherche les mots-clés « recherche » et « R&D » dans le sujet du stage (27 stages sur 884), et non la thématique (84).

## 3. Critères de validation du prototype

**Décision** : les critères proposés sont **validés** tels quels.

1. **Import** : elle importe seule un fichier EsupStage, sans intervention dans le code. Un fichier aux colonnes renommées, réordonnées ou ajoutées s'importe en ajustant seulement le mapping. Un réimport ne crée aucun doublon.
2. **Indicateurs** : identiques aux chiffres de référence calculés indépendamment.
3. **Recherche** : par année, niveau, spécialité, localisation (France / étranger / Hauts-de-France, pays) et thématique.
4. **Export** : résultats et indicateurs en CSV et XLS, lisibles dans Excel.

## 4. Table code étape → niveau → spécialité

**Décision** : à faire vérifier par mail. Seul le code **2XIGAP** pose question : son libellé est « Portail L1 adaptée Mathématiques, physique, Chimie, Sciences de l'ingénieur », mais son code commence par 2, comme une 2e année. Il est actuellement classé en **L2** (1 convention en 2025-2026). Mail en brouillon : `gestion-projet/mails/2026-10-20_brouillons.md`.

## 5. Actions

| Action | Responsable | Échéance |
|---|---|---|
| Envoyer le mail sur le code 2XIGAP | Augustin | 20/10 |
| Présenter l'analyse des 30 conventions retirées (section 1) | Augustin | prochaine réunion DRE |
| Mettre en œuvre la règle des doublons, mettre à jour les chiffres de référence | Augustin | S44 |
| Corriger le niveau de 2XIGAP si besoin (`ref_etape.csv` et `app/sql/02_referentiels.sql`) | Augustin | après sa réponse |

*Points de la préparation non consignés dans mes notes (présence à la soutenance, accord sur les chiffres cités, contact DSI, créneau hebdomadaire, tests de décembre) : à compléter.*
