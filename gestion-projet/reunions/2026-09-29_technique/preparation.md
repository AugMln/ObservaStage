
## 1. Contexte


> « J'ai eu ma réunion de cadrage hier. Le projet consiste à concevoir un prototype d'application permettant d'importer, contrôler, structurer et explorer des données historiques de stages provenant d'EsupStage.
>
> Le principal problème technique identifié est que les exports peuvent évoluer d'une année à l'autre : les colonnes peuvent changer de nom, d'ordre ou apparaître/disparaître. Je dois donc éviter de faire un import complètement codé en dur.
>
> J'aurai bientôt une base anonymisée pour travailler dessus et Dorothée doit également me transmettre des exemples de requêtes qu'elle aimerait pouvoir faire avec l'outil.
>
> Je voudrais donc profiter de ce rendez-vous pour réfléchir avec toi à l'architecture et aux choix techniques avant de commencer réellement le développement. »

---

# 2. Architecture globale

```text
Fichiers EsupStage
        ↓
Module d'import
        ↓
Mapping configurable
        ↓
Nettoyage / contrôle
        ↓
Base de données
        ↓
API
        ↓
Interface Web
```

> **« Est-ce que cette architecture te paraît cohérente pour un prototype de ce type ? Qu'est-ce que tu changerais ? »**

### Questions

* Est-ce qu'une API est réellement pertinente ou est-ce que ce serait surdimensionné pour le prototype ?
* Est-ce qu'il vaut mieux séparer fortement le module d'import du reste de l'application ?
* Où placer la logique de nettoyage et de validation ?
* Qu'est-ce qui devrait être fait à l'import et qu'est-ce qui devrait être fait dans la base ?
* Comment éviter que la logique métier soit trop liée à l'interface ?

php html js

---

# 3. Import configurable

> « J'ai potentiellement un fichier 2024, un fichier 2025 et un fichier 2026 qui ne possèdent pas exactement les mêmes colonnes. Je voudrais que mon application puisse absorber ces différences sans devoir modifier le code à chaque nouvel export. »

> **« Comment est-ce que tu concevrais le système de mapping ? »**

Exemple :

```text
"Nom étudiant" → student.last_name
"Prénom étudiant" → student.first_name
"Spécialité" → formation.specialite
"Date début" → stage.date_debut
"Date fin" → stage.date_fin
```

### Questions

* Est-ce que le mapping devrait être stocké en base ?
* Est-ce qu'il devrait être défini dans un fichier de configuration ?
* Est-ce qu'on devrait avoir une interface permettant à l'administrateur de faire le mapping ?
* Comment gérer une colonne absente ?
* Comment détecter qu'une colonne a changé de nom ?
* Comment gérer une nouvelle colonne inconnue ?
* Est-ce qu'on doit refuser l'import ou simplement générer un avertissement ?

---

# 4. Gestion des erreurs

> « Imaginons que j'importe 5000 lignes et que 120 contiennent une date invalide, 30 une spécialité inconnue et 15 une colonne obligatoire vide. Tu ferais quoi ? »

### Erreur bloquante

Impossible d'importer.

Exemple :

```text
La colonne "Date début" est absente.
```

### Erreur sur une ligne

La ligne ne peut pas être intégrée.

```text
Ligne 1842 :
Date début invalide
```

### Avertissement

La donnée est importable mais potentiellement problématique.

```text
Attention :
12 établissements inconnus.
```

---

# 5. Modèle de données

> **« Est-ce que tu me conseillerais de partir directement de la structure de l'export pour construire ma base, ou de concevoir un modèle métier indépendant de l'export ? »**

```text
Étudiant
   │
   ├── Formation
   │
   └── Stage
          │
          ├── Structure
          │
          ├── Encadrants
          │
          ├── Thématique
          │
          └── Dates
```


> **« Est-ce qu'on doit transformer l'export tabulaire en modèle relationnel normalisé ? Jusqu'à quel niveau ? »**

> **« Est-ce que tu vois des risques à vouloir trop normaliser le modèle pour un prototype ? »**

---

# 6. Requêtes

Quand tu auras reçu ses requêtes, montre-lui quelques exemples.

Tu en as déjà :

* durée moyenne des stages ;
* recherche ou non ;
* France / étranger ;
* Hauts-de-France / hors Hauts-de-France ;
* secteur de l'entreprise ;
* CDD / stage / intérim / bénévolat ;
* comparaison par spécialité ;
* comparaison par année.

> « Je veux que les utilisateurs puissent croiser ces critères. Par exemple : durée moyenne des stages des étudiants d'une spécialité donnée, sur une période donnée, à l'étranger. »

> **« Est-ce que ça change la manière dont tu concevrais la base ? »**

> **« Est-ce qu'il y a des choix de modèle de données que je devrais faire maintenant pour éviter de rendre ces requêtes compliquées plus tard ? »**

---

# 7. SQL / base de données

> Quelle base utiliserais-tu pour ce projet ?

Par exemple :

* PostgreSQL ?
* autre ?

Puis :

* Est-ce qu'il vaut mieux faire les calculs directement en SQL ?
* Quand utiliser des vues ?
* Est-ce que certaines statistiques devraient être pré-calculées ?
* Comment gérer les filtres multicritères ?
* Comment éviter des requêtes trop lentes si l'historique devient important ?

---

# 8. Données historiques

> **« Si les fichiers des années précédentes ne sont pas parfaitement compatibles, est-ce que tu conseillerais de transformer chaque fichier vers un schéma commun avant de l'insérer dans la base ? »**

Tu peux réfléchir à cette logique :

```text
Export 2024 ──┐
Export 2025 ──┼──→ Mapping / normalisation ──→ Schéma commun
Export 2026 ──┘
```

---

# 9. Technologies

> **« Pour un prototype de ce type, avec une base de données, une API et une interface web, quelle stack te semblerait pertinente compte tenu de mes compétences et des contraintes du projet ? »**

### Backend

* Python / FastAPI ?
* PHP ?
* autre ?

### Traitement de données

* pandas ?
* traitement SQL ?
* autre ?

### Frontend

* HTML/CSS/JS ?
* framework JS ?
* autre ?

### Base

* PostgreSQL ?

### Visualisation

* Plotly ?
* autre ?

---

# 10. Hébergement / déploiement

> « Si le prototype doit éventuellement être présenté ou utilisé par Polytech, comment anticiper le déploiement dès maintenant ? »

Puis :

* Où héberger ?
* Docker ?
* serveur Polytech ?
* authentification ?
* HTTPS ?
* sauvegarde de la base ?
* variables de configuration ?
* gestion des secrets ?

---

# 11. RGPD

> « D'un point de vue technique, comment concevoir l'application pour que les droits d'accès soient correctement séparés ? »

Par exemple :

```text
Administrateur
      ↓
Toutes les données

DRE
      ↓
Données de suivi / indicateurs

Enseignant
      ↓
Données pertinentes pour sa formation

Étudiant
      ↓
Données publiques / anonymisées
```

> **« Est-ce qu'il vaut mieux gérer les droits au niveau de l'application, de la base de données, ou des deux ? »**

---

# 12. Ce que je lui demanderais personnellement en priorité

Si tu n'as qu'une heure, je ferais cette liste :

### Priorité 1 — Import

**Comment concevoir un système de mapping configurable et robuste face à l'évolution des exports ?**

### Priorité 2 — Modèle de données

**Comment passer d'un export EsupStage probablement assez plat à un modèle de données propre et adapté aux requêtes futures ?**

### Priorité 3 — Architecture

**Quelle architecture est suffisamment propre et évolutive sans être surdimensionnée pour un prototype étudiant ?**

### Priorité 4 — Requêtes

**Comment concevoir la BDD pour permettre facilement les croisements spécialité × année × localisation × secteur × type de stage ?**

### Priorité 5 — Stack

**Quelle stack serait cohérente avec le projet, mes compétences et les contraintes de Polytech ?**

### Priorité 6 — Qualité

**Comment concevoir le pipeline de validation/nettoyage et le rapport d'erreurs ?**

### Priorité 7 — Déploiement

**Comment anticiper l'hébergement et l'authentification ?**

---

# 13. Fin du rendez-vous

Tu n'as pas besoin de ressortir avec toute l'architecture définitive.

Mais idéalement, tu dois avoir des réponses sur :

```text
             PROJET
                │
       ┌────────┴────────┐
       ↓                 ↓
   Fonctionnel        Technique
       │                 │
       ↓                 ↓
 Requêtes          Architecture
 Utilisateurs      Modèle BDD
 Indicateurs       Import
                   Validation
                   Stack
                   Déploiement
```

> **« Si tu devais me dire les deux ou trois principaux pièges techniques à éviter sur ce projet, ce seraient lesquels ? »**

pouvoir taguer

P4 : toute la partie ingestion