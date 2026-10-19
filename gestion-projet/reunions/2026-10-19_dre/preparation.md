# Préparation de la réunion avec Dorothée Bernard

Projet ingénieur 2026-2027 : Conception d'un outil de valorisation et de suivi des données de stages
Participants : Dorothée Bernard (DRE, tutrice référente), Augustin Mélon
Date : 19/10/2026, avant la soutenance blanche de 11h
Objectif : trancher les points restés ouverts le 12/10 avant le dépôt du rapport et des slides (mercredi 21/10, 10h), montrer l'ébauche de l'outil, fixer la suite (hébergement, tests, tripartite).

---

## 0. Avant la réunion

- [ ] Imprimer le compte rendu du 12/10 et la table `conception/referentiels/ref_etape.csv` : **le mail du 16/10 n'est pas parti**, Dorothée ne les a pas encore lus.
- [ ] Application lancée (`cd app ; docker compose up -d`), onglet Tableau de bord ouvert, profil et import du fichier de test supprimés (onglet Import).
- [ ] Imprimer cette fiche pour cocher les décisions en direct.

---

## 1. Ordre du jour (environ 45 min)

| # | Sujet | Durée |
|---|---|---|
| 1 | Démonstration de l'ébauche | 10 min |
| 2 | Décisions à prendre (section 3) | 15 min |
| 3 | Ce qui sera dit en soutenance (section 4) | 5 min |
| 4 | Suite du projet et organisation (section 5) | 10 min |
| 5 | Récapitulatif des décisions et des actions | 5 min |

---

## 2. Démonstration (10 min)

C'est la première fois qu'elle voit l'outil : partir de **ses** questions, pas de la technique.

1. **Tableau de bord** : 884 stages validés, 14,4 semaines en moyenne, 24,7 % à l'étranger, 39,1 % en Hauts-de-France. Filtrer Niveau = 4A : 60 % à l'étranger.
2. **Recherche** : Localisation = Étranger, mot-clé « robot ». Exporter en XLS et ouvrir le fichier dans Excel.
3. **Import** : fichier `donnees/tests/esupstage_test_colonnes_modifiees.xlsx`. Montrer que l'outil reconnaît les colonnes et ne demande de décider que pour celles qui ont changé (34 reconnues, 2 à vérifier, 3 nouvelles). Ne pas importer, ou supprimer l'import et le profil après.
4. **Rapport d'anomalies** : heures hebdomadaires aberrantes signalées et exclues.

À lui demander : **est-ce que cela répond à ce qu'elle imaginait ?** Qu'est-ce qui manque en premier ?

---

## 3. Décisions à prendre

### 3.1 Conventions prises en compte

Règle actuelle : validation administrative seule. Toutes les conventions sont importées, le filtre ne s'applique qu'aux calculs ; la règle est un paramètre.

| | Administrative seule (actuel) | Administrative **ou** pédagogique |
|---|---|---|
| Conventions retenues | **884** | **1023** |
| Stages de PEIP / L1 | 60 | 154 |
| Durée moyenne | 14,4 semaines (101 jours) | 13,2 semaines (92 jours) |
| À l'étranger | 24,7 % | 23,8 % |
| Hauts-de-France | 39,1 % | 40,8 % |
| Stages « recherche » | 27 | 31 |

Argument : 106 des 169 conventions non validées sont des PEIP / L1, dont la plupart commencent en juin-juillet, juste autour de la date d'extraction (13/07/2026). Elles étaient **probablement en cours de validation**, pas abandonnées. Avec la règle actuelle, environ 64 % des stages de PEIP sont exclus.

Question : **quelle règle retenir ?** Et confirmer l'hypothèse : une convention validée pédagogiquement mais pas encore administrativement est-elle en cours de traitement ?

Décision : [ ] 884 (administrative)  [ ] 1023 (administrative ou pédagogique)  [ ] autre : ____________

> Si elle choisit 1023 : tout changer lundi après-midi pour que la démo et les slides montrent les mêmes chiffres (voir section 7).

### 3.2 Stages « recherche »

Règle validée le 12/10 : mots « recherche » ou « R&D » dans le **sujet** → **27 stages** (884 conventions). La thématique « Recherche/Innovation/R&D » en compte **84**.

Question : le résultat correspond-il à ce qu'elle attend ? Faut-il plutôt la thématique, ou les deux combinés ?

Décision : [ ] sujet (27)  [ ] thématique (84)  [ ] sujet OU thématique  [ ] autre : ____________

### 3.3 Critères de validation du prototype

Proposition (section 7 du compte rendu) :

1. **Import** : elle importe seule un fichier EsupStage, sans intervention dans le code, en moins de 10 minutes. Un fichier aux colonnes renommées, réordonnées ou ajoutées s'importe en ajustant seulement le mapping. Un réimport ne crée aucun doublon.
2. **Indicateurs** : identiques aux chiffres de référence calculés indépendamment.
3. **Recherche** : par année, niveau, spécialité, localisation (France / étranger / Hauts-de-France, pays) et thématique.
4. **Export** : résultats et indicateurs en CSV et XLS, lisibles dans Excel.

Contraintes : accès protégé par authentification, aucune donnée personnelle inutile stockée.

Question : ces critères lui conviennent-ils ? Un critère à ajouter (délai de réponse à une question type, par exemple) ?

Décision : [ ] validés tels quels  [ ] modifiés : ____________

> Si validés : remplacer « Critères de validation (proposés) » par « (validés le 19/10) » dans la slide 5 et le rapport.

### 3.4 Table code étape → niveau → spécialité

48 codes, construite à partir des libellés du fichier. Lui faire parcourir la table imprimée.

Cas douteux : **2XIGAP**, « Portail L1 adaptée maths-physique-chimie-SI », mais codé comme une 2e année. Actuellement classé en **L2**. Faut-il le classer en PEIP / L1 ?

Décision : [ ] table validée  [ ] 2XIGAP → ____________  [ ] autres corrections : ____________

---

## 4. Ce qui sera dit en soutenance (21/10)

Les slides et le rapport la citent. Vérifier qu'elle est d'accord pour que ce soit dit devant le jury et les autres étudiants :

- « **80 %** des questions posées à la DRE sur les stages restent sans réponse ».
- « **30 minutes** de traitement manuel pour chaque extraction ».
- « 1 053 conventions · **une seule personne** pour les gérer ».
- Slide 3 : « Dorothée Bernard (DRE), **non informaticienne** ». Proposer « utilisatrice non spécialiste » si la formulation la gêne.

Accord : [ ] oui  [ ] reformuler : ____________

Viendra-t-elle à la soutenance (mercredi 21/10, 15h) ? [ ] oui  [ ] non

---

## 5. Suite du projet

- **Hébergement (principal risque)** : qui contacter à la DSI ? Peut-elle faire le lien ou appuyer la demande ? Premier contact prévu en S44 (semaine du 26/10).
  Contact : ____________
- **Authentification** : se connecter avec son compte université (CAS) ou un compte propre à l'outil ? Préférence de sa part ?
- **Tests utilisateurs** : début décembre (S49-S50), environ 1 h. Disponibilités : ____________
- **Guide utilisateur** : sous quelle forme (PDF, aide intégrée à l'outil, courte vidéo) ?
- **Réunion hebdomadaire** : fixer un créneau régulier (exigence du dispositif). Créneau : ____________
- **Réunion tripartite intermédiaire** (S43, à mon initiative) : proposer 2 ou 3 créneaux, puis faire valider l'un d'eux par Mme Kessaci à 11h.
  Créneaux possibles pour elle : ____________

---

## 6. Récapitulatif à faire en fin de réunion

Relire à voix haute les décisions cochées et les actions ci-dessous, pour qu'elle confirme.

| Action | Responsable | Échéance |
|---|---|---|
| Envoyer le compte rendu du 12/10 et celui-ci, avec les décisions | Augustin | 19/10 |
| Intégrer les décisions au rapport, aux slides et à l'outil | Augustin | 20/10 |
| Contacter la DSI | Augustin (+ Dorothée ?) | S44 |
| Organiser la tripartite intermédiaire | Augustin | S43 |

---

## 7. Après la réunion

- [ ] Envoyer le mail hebdomadaire (en retard) et le compte rendu du 12/10, avec une ligne sur les décisions du jour (brouillons : `gestion-projet/mails/2026-10-16_brouillons.md`).
- [ ] Rédiger `compte_rendu.md` dans ce dossier.
- [ ] Mettre à jour `A_FAIRE.md` (points ouverts tranchés).
- [ ] **Si la règle passe à 1023** :
  - paramètre `regle_validation` → `administrative_ou_pedagogique` (`app/sql/02_referentiels.sql` et en base) ;
  - `VALIDATION_COLUMNS` dans `exploration/indicateurs_reference.py`, puis relancer le script et `verifier_application.py` (48/48 attendu) ;
  - chiffres : slide 8 (« ex. 884 stages validés… »), rapport (section des résultats, ligne 141), `notes_orales.md` (script de démo), `questions_jury.md` ;
  - refaire la capture `figures/app_tableau.png` (slide 9), puis régénérer les deux PDF.
- [ ] Si la règle « recherche » change : paramètre des mots-clés ou règle dans la vue `v_stage`, et report dans `indicateurs_reference.py`.
- [ ] Si 2XIGAP change de niveau : `ref_etape.csv` et `app/sql/02_referentiels.sql` (garder les deux cohérents).
