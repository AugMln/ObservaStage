-- ObservaStage : schéma PostgreSQL
-- Tables métier, tables techniques d'import (mapping configurable) et paramètres.

CREATE EXTENSION IF NOT EXISTS unaccent;

-- ============================================================
-- Référentiels et paramètres
-- ============================================================

CREATE TABLE ref_etape (
    code_etape  text PRIMARY KEY,
    libelle     text,
    niveau      text NOT NULL,
    specialite  text NOT NULL
);

-- Ordre d'affichage des niveaux.
CREATE TABLE ref_niveau (
    niveau  text PRIMARY KEY,
    ordre   int NOT NULL
);

-- Règles métier modifiables sans toucher au code.
CREATE TABLE parametre (
    cle         text PRIMARY KEY,
    valeur      text NOT NULL,
    description text
);

-- ============================================================
-- Mapping configurable
-- ============================================================

-- Catalogue des champs cibles connus de l'application.
CREATE TABLE champ_cible (
    code         text PRIMARY KEY,
    libelle      text NOT NULL,
    type         text NOT NULL CHECK (type IN ('texte', 'date', 'nombre', 'booleen')),
    obligatoire  boolean NOT NULL DEFAULT false,
    alias        text[] NOT NULL DEFAULT '{}',   -- intitulés connus, normalisés
    ordre        int NOT NULL
);

-- Un profil = un format de fichier (signature = ensemble des en-têtes normalisés).
CREATE TABLE profil_mapping (
    id         serial PRIMARY KEY,
    nom        text NOT NULL,
    signature  text NOT NULL UNIQUE,
    cree_le    timestamptz NOT NULL DEFAULT now()
);

-- champ_cible NULL = colonne ignorée (non stockée).
CREATE TABLE regle_mapping (
    profil_id       int NOT NULL REFERENCES profil_mapping(id) ON DELETE CASCADE,
    colonne_source  text NOT NULL,
    champ_cible     text REFERENCES champ_cible(code),
    PRIMARY KEY (profil_id, colonne_source)
);

-- ============================================================
-- Imports
-- ============================================================

CREATE TABLE import_lot (
    id            serial PRIMARY KEY,
    fichier       text NOT NULL,
    empreinte     text NOT NULL,           -- SHA-256 du fichier
    profil_id     int REFERENCES profil_mapping(id),
    importe_le    timestamptz NOT NULL DEFAULT now(),
    nb_lignes     int NOT NULL DEFAULT 0,
    nb_crees      int NOT NULL DEFAULT 0,
    nb_mis_a_jour int NOT NULL DEFAULT 0,
    nb_rejetes    int NOT NULL DEFAULT 0
);

CREATE TABLE import_anomalie (
    id        serial PRIMARY KEY,
    lot_id    int NOT NULL REFERENCES import_lot(id) ON DELETE CASCADE,
    ligne     int NOT NULL,                -- numéro de ligne dans le fichier
    gravite   text NOT NULL CHECK (gravite IN ('bloquant', 'avertissement')),
    champ     text,
    message   text NOT NULL
);

-- ============================================================
-- Données métier
-- ============================================================

CREATE TABLE etudiant (
    id      serial PRIMARY KEY,
    numero  text NOT NULL UNIQUE
);

CREATE TABLE structure (
    id                serial PRIMARY KEY,
    cle_rapprochement text NOT NULL UNIQUE,  -- SIRET, sinon nom normalisé + pays + code postal
    siret             text,
    nom               text,
    pays              text,
    code_postal       text,
    commune           text,
    statut_juridique  text,
    type_structure    text,
    activite          text,                  -- colonne « Code NAF » (contient un libellé)
    effectif          text
);

CREATE TABLE stage (
    id                         serial PRIMARY KEY,
    annee_universitaire        text NOT NULL,
    numero_convention          text NOT NULL,
    etudiant_id                int REFERENCES etudiant(id),
    structure_id               int REFERENCES structure(id),
    code_etape                 text,
    service_nom                text,
    service_pays               text,
    service_code_postal        text,
    service_commune            text,
    date_debut                 date,
    date_fin                   date,
    duree_heures               numeric,
    heures_hebdo               numeric,
    thematique                 text,
    sujet                      text,
    fonctions_taches           text,
    type_convention            text,
    commentaire_duree_travail  text,
    validation_convention      boolean,
    validation_pedagogique     boolean,
    avenant                    boolean,
    interruption               boolean,
    date_modification          date,
    lot_id                     int REFERENCES import_lot(id),
    UNIQUE (annee_universitaire, numero_convention)
);

CREATE INDEX stage_code_etape_idx ON stage (code_etape);
CREATE INDEX stage_annee_idx ON stage (annee_universitaire);

-- ============================================================
-- Vue d'analyse : toutes les dérivations métier au même endroit
-- ============================================================

CREATE FUNCTION param(p_cle text) RETURNS text
    LANGUAGE sql STABLE AS $$ SELECT valeur FROM parametre WHERE cle = p_cle $$;

-- Recherche d'un mot-clé parmi une liste séparée par « ; », sans casse ni accents.
CREATE FUNCTION contient_mot_cle(p_texte text, p_liste text) RETURNS boolean
    LANGUAGE sql STABLE AS $$
    SELECT EXISTS (
        SELECT 1 FROM unnest(string_to_array(p_liste, ';')) AS m
        WHERE trim(m) <> ''
          AND lower(unaccent(coalesce(p_texte, ''))) LIKE '%' || lower(unaccent(trim(m))) || '%'
    )
$$;

CREATE VIEW v_stage AS
SELECT
    s.*,
    coalesce(e.niveau, 'Inconnu')                         AS niveau,
    coalesce(e.specialite, 'Inconnue')                    AS specialite,
    coalesce(n.ordre, 99)                                 AS niveau_ordre,
    st.nom                                                AS structure_nom,
    st.type_structure,
    -- Prise en compte dans les indicateurs (règle paramétrable)
    CASE param('regle_validation')
        WHEN 'administrative_ou_pedagogique'
            THEN coalesce(s.validation_convention, false) OR coalesce(s.validation_pedagogique, false)
        WHEN 'toutes' THEN true
        ELSE coalesce(s.validation_convention, false)
    END                                                   AS retenu,
    -- Durées
    (s.date_fin - s.date_debut + 1)                       AS duree_jours,
    (s.date_fin - s.date_debut + 1) / 7.0                 AS duree_semaines,
    CASE WHEN s.heures_hebdo > 0
          AND s.heures_hebdo <= param('heures_hebdo_max')::numeric
          AND s.duree_heures > 0
         THEN s.duree_heures END                          AS duree_heures_valide,
    -- Localisation (service d'accueil)
    upper(trim(s.service_pays)) <> 'FRANCE'               AS etranger,
    upper(trim(s.service_pays)) = 'FRANCE'
        AND left(trim(s.service_code_postal), 2)
            = ANY (string_to_array(param('departements_hdf'), ';')) AS hauts_de_france,
    -- Indicateurs par mots-clés
    contient_mot_cle(s.sujet, param('mots_cles_recherche'))       AS recherche,
    CASE
        WHEN contient_mot_cle(s.commentaire_duree_travail, param('mots_cles_cdd'))       THEN 'CDD'
        WHEN contient_mot_cle(s.commentaire_duree_travail, param('mots_cles_cdi'))       THEN 'CDI'
        WHEN contient_mot_cle(s.commentaire_duree_travail, param('mots_cles_interim'))   THEN 'Intérim'
        WHEN contient_mot_cle(s.commentaire_duree_travail, param('mots_cles_benevolat')) THEN 'Bénévolat'
    END                                                   AS type_experience
FROM stage s
LEFT JOIN ref_etape e  ON e.code_etape = s.code_etape
LEFT JOIN ref_niveau n ON n.niveau = e.niveau
LEFT JOIN structure st ON st.id = s.structure_id;
