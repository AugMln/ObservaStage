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

---

# 3. Import configurable


```text
"Nom étudiant" → student.last_name
"Prénom étudiant" → student.first_name
"Spécialité" → formation.specialite
"Date début" → stage.date_debut
"Date fin" → stage.date_fin
```

---

# 5. Modèle de données

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

---

# 11. RGPD

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

---

# 13. Fin du rendez-vous

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