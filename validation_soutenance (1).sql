-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 29 juin 2025 à 19:31
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `validation_soutenance`
--

-- --------------------------------------------------------

--
-- Structure de la table `anneesacademiques`
--

DROP TABLE IF EXISTS `anneesacademiques`;
CREATE TABLE IF NOT EXISTS `anneesacademiques` (
  `id_annee` int NOT NULL AUTO_INCREMENT,
  `code_annee` varchar(191) NOT NULL,
  `libelle_annee` varchar(191) NOT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_annee`),
  UNIQUE KEY `code_annee` (`code_annee`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `attributionsfonction`
--

DROP TABLE IF EXISTS `attributionsfonction`;
CREATE TABLE IF NOT EXISTS `attributionsfonction` (
  `id_attribution` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int DEFAULT NULL,
  `id_fonction` int DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `actif` tinyint(1) NOT NULL,
  `commentaires` text,
  `attribue_par` int DEFAULT NULL,
  `date_attribution` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_attribution`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `configuration_systeme`
--

DROP TABLE IF EXISTS `configuration_systeme`;
CREATE TABLE IF NOT EXISTS `configuration_systeme` (
  `config_id` int NOT NULL AUTO_INCREMENT,
  `cle_configuration` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GENERAL',
  `valeur_configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_valeur` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'STRING',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `est_modifiable` tinyint(1) DEFAULT '1',
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modifie_par` int DEFAULT NULL,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `cle_configuration` (`cle_configuration`),
  KEY `modifie_par` (`modifie_par`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `creneauxhoraires`
--

DROP TABLE IF EXISTS `creneauxhoraires`;
CREATE TABLE IF NOT EXISTS `creneauxhoraires` (
  `id_creneau` int NOT NULL,
  `heure_debut` time DEFAULT NULL,
  `heure_fin` time DEFAULT NULL,
  `duree_minutes` int DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_creneau`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `elements_constitutifs`
--

DROP TABLE IF EXISTS `elements_constitutifs`;
CREATE TABLE IF NOT EXISTS `elements_constitutifs` (
  `ecue_id` int NOT NULL AUTO_INCREMENT,
  `ue_id` int NOT NULL,
  `code_ecue` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle_ecue` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nombre_credits` int NOT NULL,
  `coefficient_evaluation` decimal(3,2) DEFAULT '1.00',
  `est_actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ecue_id`),
  UNIQUE KEY `code_ecue` (`code_ecue`),
  KEY `ue_id` (`ue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `enseignants`
--

DROP TABLE IF EXISTS `enseignants`;
CREATE TABLE IF NOT EXISTS `enseignants` (
  `enseignant_id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `numero_enseignant` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade_id` int NOT NULL,
  `fonction_id` int DEFAULT NULL,
  `specialite_id` int DEFAULT NULL,
  `date_recrutement` date DEFAULT NULL,
  `est_vacataire` tinyint(1) DEFAULT '0',
  `nombre_heures_max` int DEFAULT '192',
  `taux_horaire` decimal(10,2) DEFAULT NULL,
  `cv_document` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`enseignant_id`),
  UNIQUE KEY `numero_enseignant` (`numero_enseignant`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `grade_id` (`grade_id`),
  KEY `fonction_id` (`fonction_id`),
  KEY `specialite_id` (`specialite_id`),
  KEY `grade_fonction` (`grade_id`,`fonction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

DROP TABLE IF EXISTS `entreprise`;
CREATE TABLE IF NOT EXISTS `entreprise` (
  `id_entr` int NOT NULL,
  `lib_entr` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_entr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

DROP TABLE IF EXISTS `etudiants`;
CREATE TABLE IF NOT EXISTS `etudiants` (
  `etudiant_id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `numero_etudiant` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_carte_etudiant` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `niveau_id` int NOT NULL,
  `specialite_id` int DEFAULT NULL,
  `annee_inscription` int NOT NULL,
  `date_inscription` date NOT NULL DEFAULT (curdate()),
  `moyenne_generale` decimal(4,2) DEFAULT NULL,
  `nombre_credits_valides` int DEFAULT '0',
  `nombre_credits_requis` int DEFAULT '60',
  `taux_progression` decimal(5,2) GENERATED ALWAYS AS (((`nombre_credits_valides` / `nombre_credits_requis`) * 100)) STORED,
  `statut_eligibilite` int NOT NULL,
  `date_derniere_mise_a_jour` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`etudiant_id`),
  UNIQUE KEY `numero_etudiant` (`numero_etudiant`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `niveau_id` (`niveau_id`),
  KEY `specialite_id` (`specialite_id`),
  KEY `statut_eligibilite` (`statut_eligibilite`),
  KEY `niveau_specialite` (`niveau_id`,`specialite_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `evaluation_id` int NOT NULL AUTO_INCREMENT,
  `etudiant_id` int NOT NULL,
  `ecue_id` int NOT NULL,
  `enseignant_id` int NOT NULL,
  `note` decimal(4,2) NOT NULL,
  `note_sur` decimal(4,2) NOT NULL DEFAULT '20.00',
  `coefficient` decimal(3,2) DEFAULT '1.00',
  `type_evaluation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_evaluation` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_evaluation` date NOT NULL,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `est_validee` tinyint(1) DEFAULT '0',
  `date_validation` datetime DEFAULT NULL,
  `valide_par` int DEFAULT NULL,
  PRIMARY KEY (`evaluation_id`),
  UNIQUE KEY `eval_unique` (`etudiant_id`,`ecue_id`,`type_evaluation`,`session_evaluation`),
  KEY `ecue_id` (`ecue_id`),
  KEY `enseignant_id` (`enseignant_id`),
  KEY `valide_par` (`valide_par`),
  KEY `date_evaluation` (`date_evaluation`),
  KEY `etudiant_ecue` (`etudiant_id`,`ecue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluationsrapport`
--

DROP TABLE IF EXISTS `evaluationsrapport`;
CREATE TABLE IF NOT EXISTS `evaluationsrapport` (
  `id_evaluation_rapport` int NOT NULL AUTO_INCREMENT,
  `id_rapport` int DEFAULT NULL,
  `id_evaluateur` int DEFAULT NULL,
  `type_evaluateur` enum('Président','Rapporteur','Membre') NOT NULL,
  `commentaires_positifs` text,
  `commentaires_amelioration` text,
  `recommandations` text,
  `decision` enum('Validé','Non validé') NOT NULL,
  `date_evaluation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `note_contenu` decimal(4,2) DEFAULT NULL,
  `note_methodologie` decimal(4,2) DEFAULT NULL,
  `note_redaction` decimal(4,2) DEFAULT NULL,
  `note_presentation` decimal(4,2) DEFAULT NULL,
  `note_globale` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`id_evaluation_rapport`),
  KEY `id_rapport` (`id_rapport`),
  KEY `id_evaluateur` (`id_evaluateur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fonctions`
--

DROP TABLE IF EXISTS `fonctions`;
CREATE TABLE IF NOT EXISTS `fonctions` (
  `fonction_id` int NOT NULL AUTO_INCREMENT,
  `code_fonction` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle_fonction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `niveau_responsabilite` int NOT NULL DEFAULT '1',
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`fonction_id`),
  UNIQUE KEY `code_fonction` (`code_fonction`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fonctions`
--

INSERT INTO `fonctions` (`fonction_id`, `code_fonction`, `libelle_fonction`, `niveau_responsabilite`, `description`, `est_actif`) VALUES
(1, 'DIR_FILIERE', 'Directeur de Filière', 10, NULL, 1),
(2, 'RESP_MASTER', 'Responsable Master', 9, NULL, 1),
(3, 'RESP_LICENCE', 'Responsable Licence', 8, NULL, 1),
(4, 'COORD_PEDAGO', 'Coordinateur Pédagogique', 7, NULL, 1),
(5, 'ADMIN_SCOL', 'Administrateur Scolarité', 6, NULL, 1),
(6, 'CHARGE_COM', 'Chargé de Communication', 5, NULL, 1),
(7, 'COMMISSION', 'Membre Commission', 6, NULL, 1),
(8, 'ENSEIGNANT', 'Enseignant', 5, NULL, 1),
(9, 'SECRETAIRE', 'Secrétaire', 4, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `gradesacademiques`
--

DROP TABLE IF EXISTS `gradesacademiques`;
CREATE TABLE IF NOT EXISTS `gradesacademiques` (
  `id_grade` int NOT NULL AUTO_INCREMENT,
  `code_grade` varchar(191) NOT NULL,
  `libelle_grade` varchar(255) NOT NULL,
  `niveau_hierarchique` int NOT NULL,
  `actif` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_grade`),
  UNIQUE KEY `code_grade` (`code_grade`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grades_academiques`
--

DROP TABLE IF EXISTS `grades_academiques`;
CREATE TABLE IF NOT EXISTS `grades_academiques` (
  `grade_id` int NOT NULL AUTO_INCREMENT,
  `code_grade` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle_grade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `niveau_hierarchique` int NOT NULL,
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`grade_id`),
  UNIQUE KEY `code_grade` (`code_grade`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `grades_academiques`
--

INSERT INTO `grades_academiques` (`grade_id`, `code_grade`, `libelle_grade`, `niveau_hierarchique`, `description`, `est_actif`) VALUES
(1, 'PROF', 'Professeur Titulaire', 10, NULL, 1),
(2, 'MC', 'Maître de Conférences', 8, NULL, 1),
(3, 'MA', 'Maître Assistant', 6, NULL, 1),
(4, 'AS', 'Assistant', 4, NULL, 1),
(5, 'DOC', 'Doctorant', 2, NULL, 1),
(6, 'VAC', 'Vacataire', 1, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateur`
--

DROP TABLE IF EXISTS `groupe_utilisateur`;
CREATE TABLE IF NOT EXISTS `groupe_utilisateur` (
  `id_gu` int NOT NULL,
  `lib_gu` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_gu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `informations_personnelles`
--

DROP TABLE IF EXISTS `informations_personnelles`;
CREATE TABLE IF NOT EXISTS `informations_personnelles` (
  `information_id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenoms` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `genre` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `situation_matrimoniale` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_identite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone_urgence` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ville` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_postal` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pays` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Côte d''Ivoire',
  PRIMARY KEY (`information_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
CREATE TABLE IF NOT EXISTS `inscriptions` (
  `id_inscription` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int DEFAULT NULL,
  `id_annee` int DEFAULT NULL,
  `id_niveau` int DEFAULT NULL,
  `id_specialite` int DEFAULT NULL,
  `date_inscription` date DEFAULT NULL,
  `statut_inscription` enum('En cours','Terminé','Annulé') NOT NULL,
  `montant_inscription` decimal(10,2) DEFAULT NULL,
  `montant_paye` decimal(10,2) DEFAULT NULL,
  `solde` decimal(10,2) GENERATED ALWAYS AS ((`montant_inscription` - `montant_paye`)) STORED,
  PRIMARY KEY (`id_inscription`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jurys`
--

DROP TABLE IF EXISTS `jurys`;
CREATE TABLE IF NOT EXISTS `jurys` (
  `jury_id` int NOT NULL AUTO_INCREMENT,
  `soutenance_id` int NOT NULL,
  `enseignant_id` int NOT NULL,
  `role_jury` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `est_present` tinyint(1) DEFAULT '1',
  `date_designation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note_attribuee` decimal(4,2) DEFAULT NULL,
  `commentaires` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`jury_id`),
  UNIQUE KEY `soutenance_enseignant` (`soutenance_id`,`enseignant_id`),
  KEY `enseignant_id` (`enseignant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `logs_audit`
--

DROP TABLE IF EXISTS `logs_audit`;
CREATE TABLE IF NOT EXISTS `logs_audit` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `date_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `utilisateur_id` int DEFAULT NULL,
  `type_action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_cible` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enregistrement_id` int DEFAULT NULL,
  `anciennes_valeurs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nouvelles_valeurs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nombre_modifications` int DEFAULT '1',
  `adresse_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`log_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `date_action` (`date_action`),
  KEY `type_action` (`type_action`),
  KEY `date_utilisateur` (`date_action`,`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `expediteur_id` int NOT NULL,
  `destinataire_id` int NOT NULL,
  `sujet` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `est_lu` tinyint(1) DEFAULT '0',
  `date_lecture` datetime DEFAULT NULL,
  `reponse_a` int DEFAULT NULL,
  `est_archive` tinyint(1) DEFAULT '0',
  `est_important` tinyint(1) DEFAULT '0',
  `date_envoi` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `expediteur_id` (`expediteur_id`),
  KEY `destinataire_id` (`destinataire_id`),
  KEY `reponse_a` (`reponse_a`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messagesgroupes`
--

DROP TABLE IF EXISTS `messagesgroupes`;
CREATE TABLE IF NOT EXISTS `messagesgroupes` (
  `id_message_groupe` int NOT NULL AUTO_INCREMENT,
  `id_expediteur` int DEFAULT NULL,
  `titre_diffusion` varchar(255) NOT NULL,
  `sujet` varchar(255) NOT NULL,
  `contenu` longtext NOT NULL,
  `id_type_message` int DEFAULT NULL,
  `id_priorite` int DEFAULT NULL,
  `criteres_diffusion` json DEFAULT NULL,
  `nombre_destinataires` int NOT NULL,
  `date_programmee` timestamp NULL DEFAULT NULL,
  `envoye` tinyint(1) NOT NULL,
  `date_envoi` timestamp NULL DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_message_groupe`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveaux_etude`
--

DROP TABLE IF EXISTS `niveaux_etude`;
CREATE TABLE IF NOT EXISTS `niveaux_etude` (
  `niveau_id` int NOT NULL AUTO_INCREMENT,
  `code_niveau` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle_niveau` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_annees` int DEFAULT NULL,
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`niveau_id`),
  UNIQUE KEY `code_niveau` (`code_niveau`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `niveaux_etude`
--

INSERT INTO `niveaux_etude` (`niveau_id`, `code_niveau`, `libelle_niveau`, `nombre_annees`, `description`, `est_actif`) VALUES
(5, 'M2', 'Master 2', 5, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `type_notification` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `titre_notification` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu_notification` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lien_action` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_lue` tinyint(1) DEFAULT '0',
  `date_lecture` datetime DEFAULT NULL,
  `est_envoi_email` tinyint(1) DEFAULT '0',
  `email_envoye` tinyint(1) DEFAULT '0',
  `date_envoi_email` datetime DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_expiration` datetime DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `notifications_non_lues` (`utilisateur_id`,`est_lue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `paiement_id` int NOT NULL AUTO_INCREMENT,
  `reglement_id` int NOT NULL,
  `montant_paiement` decimal(12,2) NOT NULL,
  `date_paiement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mode_paiement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_piece` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_bancaire` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_valide` tinyint(1) DEFAULT '1',
  `valide_par` int DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `commentaires` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`paiement_id`),
  KEY `reglement_id` (`reglement_id`),
  KEY `valide_par` (`valide_par`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personnel_administratif`
--

DROP TABLE IF EXISTS `personnel_administratif`;
CREATE TABLE IF NOT EXISTS `personnel_administratif` (
  `personnel_id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `numero_personnel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fonction_id` int NOT NULL,
  `service_rattachement` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_recrutement` date DEFAULT NULL,
  `salaire` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`personnel_id`),
  UNIQUE KEY `numero_personnel` (`numero_personnel`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `fonction_id` (`fonction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pieces_jointes_reclamations`
--

DROP TABLE IF EXISTS `pieces_jointes_reclamations`;
CREATE TABLE IF NOT EXISTS `pieces_jointes_reclamations` (
  `piece_jointe_id` int NOT NULL AUTO_INCREMENT,
  `reclamation_id` int NOT NULL,
  `nom_fichier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chemin_fichier` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `taille_fichier` bigint DEFAULT NULL,
  `type_mime` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uploade_par` int DEFAULT NULL,
  PRIMARY KEY (`piece_jointe_id`),
  KEY `reclamation_id` (`reclamation_id`),
  KEY `uploade_par` (`uploade_par`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `procesverbaux`
--

DROP TABLE IF EXISTS `procesverbaux`;
CREATE TABLE IF NOT EXISTS `procesverbaux` (
  `id_pv` int NOT NULL,
  `id_soutenance` int DEFAULT NULL,
  `contenu_pv` longtext,
  `date_redaction` timestamp NULL DEFAULT NULL,
  `redige_par` int DEFAULT NULL,
  `valide` tinyint(1) DEFAULT NULL,
  `date_validation` timestamp NULL DEFAULT NULL,
  `valide_par` int DEFAULT NULL,
  `fichier_pv` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_pv`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapports`
--

DROP TABLE IF EXISTS `rapports`;
CREATE TABLE IF NOT EXISTS `rapports` (
  `rapport_id` int NOT NULL AUTO_INCREMENT,
  `etudiant_id` int NOT NULL,
  `titre` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_rapport` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `encadreur_id` int NOT NULL,
  `co_encadreur_id` int DEFAULT NULL,
  `resume` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mots_cles` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cadre_reference_texte` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `introduction_texte` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `problematique_texte` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `objectif_general_texte` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `objectifs_specifiques_texte` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `methodologie_texte` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `entreprise_stage` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maitre_stage_nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maitre_stage_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maitre_stage_poste` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lieu_stage` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_debut_stage` date DEFAULT NULL,
  `date_fin_stage` date DEFAULT NULL,
  `fichier_rapport` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille_fichier` bigint DEFAULT NULL,
  `nombre_pages` int DEFAULT NULL,
  `nombre_mots` int DEFAULT NULL,
  `version_document` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '1.0',
  `est_confidentiel` tinyint(1) DEFAULT '0',
  `duree_confidentialite` int DEFAULT NULL,
  `statut_id` int NOT NULL,
  `date_depot` datetime DEFAULT NULL,
  `date_limite_depot` datetime DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rapport_id`),
  KEY `etudiant_id` (`etudiant_id`),
  KEY `encadreur_id` (`encadreur_id`),
  KEY `co_encadreur_id` (`co_encadreur_id`),
  KEY `statut_id` (`statut_id`),
  KEY `date_depot` (`date_depot`),
  KEY `etudiant_statut` (`etudiant_id`,`statut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rattacher`
--

DROP TABLE IF EXISTS `rattacher`;
CREATE TABLE IF NOT EXISTS `rattacher` (
  `id_gu` int NOT NULL,
  `id_traitement` int NOT NULL,
  PRIMARY KEY (`id_gu`,`id_traitement`),
  KEY `id_traitement` (`id_traitement`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reclamations`
--

DROP TABLE IF EXISTS `reclamations`;
CREATE TABLE IF NOT EXISTS `reclamations` (
  `reclamation_id` int NOT NULL AUTO_INCREMENT,
  `etudiant_id` int NOT NULL,
  `numero_reclamation` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sujet` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_reclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `priorite_id` int NOT NULL,
  `statut_id` int NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_echeance` datetime DEFAULT NULL,
  `date_traitement` datetime DEFAULT NULL,
  `date_fermeture` datetime DEFAULT NULL,
  `traite_par` int DEFAULT NULL,
  `reponse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `satisfaction_client` int DEFAULT NULL,
  PRIMARY KEY (`reclamation_id`),
  UNIQUE KEY `numero_reclamation` (`numero_reclamation`),
  KEY `etudiant_id` (`etudiant_id`),
  KEY `priorite_id` (`priorite_id`),
  KEY `statut_id` (`statut_id`),
  KEY `traite_par` (`traite_par`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reglements`
--

DROP TABLE IF EXISTS `reglements`;
CREATE TABLE IF NOT EXISTS `reglements` (
  `reglement_id` int NOT NULL AUTO_INCREMENT,
  `etudiant_id` int NOT NULL,
  `numero_reglement` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_reglement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `montant_total` decimal(12,2) NOT NULL,
  `montant_paye` decimal(12,2) NOT NULL DEFAULT '0.00',
  `montant_restant` decimal(12,2) GENERATED ALWAYS AS ((`montant_total` - `montant_paye`)) STORED,
  `statut_id` int NOT NULL,
  `date_echeance` date DEFAULT NULL,
  `annee_academique` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentaires` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reglement_id`),
  UNIQUE KEY `numero_reglement` (`numero_reglement`),
  KEY `etudiant_id` (`etudiant_id`),
  KEY `statut_id` (`statut_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `nom_role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `niveau_acces` int NOT NULL DEFAULT '1',
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `nom_role` (`nom_role`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`role_id`, `nom_role`, `niveau_acces`, `description`, `est_actif`, `date_creation`, `date_modification`) VALUES
(1, 'Administrateur', 10, 'Administrateur système avec tous les droits', 1, '2025-06-27 01:40:31', '2025-06-27 01:40:31'),
(2, 'Responsable Scolarité', 8, 'Responsable de la scolarité Master 2', 1, '2025-06-27 01:40:31', '2025-06-27 01:40:31'),
(3, 'Chargé Communication', 7, 'Chargé de communication et validation', 1, '2025-06-27 01:40:31', '2025-06-27 01:40:31'),
(4, 'Commission', 7, 'Membre de la commission de validation', 1, '2025-06-27 01:40:31', '2025-06-27 01:40:31'),
(5, 'Secrétaire', 6, 'Secrétaire administrative', 1, '2025-06-27 01:40:31', '2025-06-27 01:40:31'),
(6, 'Enseignant', 5, 'Enseignant universitaire', 1, '2025-06-27 01:40:31', '2025-06-27 01:40:31'),
(7, 'Personnel Administratif', 4, 'Personnel administratif général', 1, '2025-06-27 01:40:31', '2025-06-27 01:40:31'),
(8, 'Étudiant', 3, 'Étudiant Master 2', 1, '2025-06-27 01:40:31', '2025-06-27 01:40:31');

-- --------------------------------------------------------

--
-- Structure de la table `salles`
--

DROP TABLE IF EXISTS `salles`;
CREATE TABLE IF NOT EXISTS `salles` (
  `salle_id` int NOT NULL AUTO_INCREMENT,
  `code_salle` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_salle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batiment` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `etage` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacite` int DEFAULT NULL,
  `type_salle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `est_disponible` tinyint(1) DEFAULT '1',
  `est_actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`salle_id`),
  UNIQUE KEY `code_salle` (`code_salle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions_utilisateurs`
--

DROP TABLE IF EXISTS `sessions_utilisateurs`;
CREATE TABLE IF NOT EXISTS `sessions_utilisateurs` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `token_session` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_debut` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_expiration` datetime NOT NULL,
  `date_derniere_activite` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `est_active` tinyint(1) DEFAULT '1',
  `adresse_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `token_session` (`token_session`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `soutenances`
--

DROP TABLE IF EXISTS `soutenances`;
CREATE TABLE IF NOT EXISTS `soutenances` (
  `soutenance_id` int NOT NULL AUTO_INCREMENT,
  `rapport_id` int NOT NULL,
  `type_soutenance` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_prevue` datetime DEFAULT NULL,
  `date_reelle` datetime DEFAULT NULL,
  `duree_prevue` int DEFAULT '60',
  `duree_reelle` int DEFAULT NULL,
  `salle_id` int DEFAULT NULL,
  `statut_id` int NOT NULL,
  `note_final` decimal(4,2) DEFAULT NULL,
  `mention` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commentaires` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `proces_verbal` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_publique` tinyint(1) DEFAULT '1',
  `lien_visioconference` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`soutenance_id`),
  KEY `rapport_id` (`rapport_id`),
  KEY `salle_id` (`salle_id`),
  KEY `statut_id` (`statut_id`),
  KEY `date_prevue` (`date_prevue`),
  KEY `rapport_statut` (`rapport_id`,`statut_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `specialites`
--

DROP TABLE IF EXISTS `specialites`;
CREATE TABLE IF NOT EXISTS `specialites` (
  `specialite_id` int NOT NULL AUTO_INCREMENT,
  `code_specialite` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle_specialite` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `niveau_id` int NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`specialite_id`),
  UNIQUE KEY `code_specialite` (`code_specialite`),
  KEY `niveau_id` (`niveau_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `specialites`
--

INSERT INTO `specialites` (`specialite_id`, `code_specialite`, `libelle_specialite`, `niveau_id`, `description`, `est_actif`) VALUES
(1, 'GENIE_LOG', 'Génie Logiciel', 5, NULL, 1),
(2, 'IA', 'Intelligence Artificielle', 5, NULL, 1),
(3, 'INFO', 'Informatique', 5, NULL, 1),
(4, 'RESEAUX', 'Réseaux et Sécurité', 5, NULL, 1),
(5, 'TELECOM', 'Télécommunications', 5, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `statuts`
--

DROP TABLE IF EXISTS `statuts`;
CREATE TABLE IF NOT EXISTS `statuts` (
  `statut_id` int NOT NULL AUTO_INCREMENT,
  `type_statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_statut` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle_statut` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `couleur_affichage` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre_affichage` int DEFAULT '0',
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`statut_id`),
  UNIQUE KEY `type_code_statut` (`type_statut`,`code_statut`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `statuts`
--

INSERT INTO `statuts` (`statut_id`, `type_statut`, `code_statut`, `libelle_statut`, `couleur_affichage`, `ordre_affichage`, `description`, `est_actif`) VALUES
(1, 'Utilisateur', 'ACTIF', 'Actif', '#28a745', 1, 'Utilisateur actif', 1),
(2, 'Utilisateur', 'INACTIF', 'Inactif', '#6c757d', 2, 'Utilisateur inactif', 1),
(3, 'Utilisateur', 'BLOQUE', 'Bloqué', '#dc3545', 3, 'Utilisateur bloqué', 1),
(4, 'Utilisateur', 'SUSPENDU', 'Suspendu', '#ffc107', 4, 'Utilisateur suspendu temporairement', 1),
(5, 'Etudiant', 'ELIGIBLE', 'Éligible', '#28a745', 1, 'Étudiant éligible à la soutenance', 1),
(6, 'Etudiant', 'NON_ELIGIBLE', 'Non éligible', '#dc3545', 2, 'Étudiant non éligible', 1),
(7, 'Etudiant', 'EN_ATTENTE', 'En attente de vérification', '#ffc107', 3, 'Vérification en cours', 1),
(8, 'Rapport', 'BROUILLON', 'Brouillon', '#6c757d', 1, 'Rapport en cours de rédaction', 1),
(9, 'Rapport', 'DEPOSE', 'Déposé', '#17a2b8', 2, 'Rapport déposé par l\'étudiant', 1),
(10, 'Rapport', 'EN_VERIFICATION', 'En vérification', '#ffc107', 3, 'Rapport en cours de vérification', 1),
(11, 'Rapport', 'VALIDE', 'Validé', '#28a745', 4, 'Rapport validé par la commission', 1),
(12, 'Rapport', 'REJETE', 'Rejeté', '#dc3545', 5, 'Rapport rejeté', 1),
(13, 'Rapport', 'EN_REVISION', 'En révision', '#fd7e14', 6, 'Rapport à réviser', 1),
(14, 'Soutenance', 'PROGRAMMEE', 'Programmée', '#17a2b8', 1, 'Soutenance programmée', 1),
(15, 'Soutenance', 'CONFIRMEE', 'Confirmée', '#28a745', 2, 'Soutenance confirmée', 1),
(16, 'Soutenance', 'REPORTEE', 'Reportée', '#ffc107', 3, 'Soutenance reportée', 1),
(17, 'Soutenance', 'TERMINEE', 'Terminée', '#6c757d', 4, 'Soutenance terminée', 1),
(18, 'Soutenance', 'ANNULEE', 'Annulée', '#dc3545', 5, 'Soutenance annulée', 1),
(19, 'Reglement', 'PAYE', 'Payé', '#28a745', 1, 'Règlement soldé', 1),
(20, 'Reglement', 'PARTIEL', 'Partiel', '#ffc107', 2, 'Règlement partiel', 1),
(21, 'Reglement', 'NON_PAYE', 'Non payé', '#dc3545', 3, 'Règlement en attente', 1),
(22, 'Reglement', 'ECHU', 'Échu', '#6f42c1', 4, 'Règlement échu', 1),
(23, 'Reclamation', 'OUVERTE', 'Ouverte', '#17a2b8', 1, 'Réclamation ouverte', 1),
(24, 'Reclamation', 'EN_COURS', 'En cours', '#ffc107', 2, 'Réclamation en traitement', 1),
(25, 'Reclamation', 'RESOLUE', 'Résolue', '#28a745', 3, 'Réclamation résolue', 1),
(26, 'Reclamation', 'FERMEE', 'Fermée', '#6c757d', 4, 'Réclamation fermée', 1),
(27, 'Priorite', 'BASSE', 'Basse', '#28a745', 1, 'Priorité basse', 1),
(28, 'Priorite', 'NORMALE', 'Normale', '#17a2b8', 2, 'Priorité normale', 1),
(29, 'Priorite', 'HAUTE', 'Haute', '#ffc107', 3, 'Priorité haute', 1),
(30, 'Priorite', 'URGENTE', 'Urgente', '#dc3545', 4, 'Priorité urgente', 1);

-- --------------------------------------------------------

--
-- Structure de la table `suivireclamations`
--

DROP TABLE IF EXISTS `suivireclamations`;
CREATE TABLE IF NOT EXISTS `suivireclamations` (
  `id_suivi` int NOT NULL AUTO_INCREMENT,
  `id_reclamation` int DEFAULT NULL,
  `id_utilisateur` int DEFAULT NULL,
  `ancien_statut` int DEFAULT NULL,
  `nouveau_statut` int DEFAULT NULL,
  `action` varchar(191) NOT NULL,
  `commentaire` text,
  `date_action` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_suivi`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tentativesconnexion`
--

DROP TABLE IF EXISTS `tentativesconnexion`;
CREATE TABLE IF NOT EXISTS `tentativesconnexion` (
  `id_tentative` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `succes` tinyint(1) NOT NULL,
  `raison_echec` varchar(255) DEFAULT NULL,
  `date_tentative` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tentative`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--

DROP TABLE IF EXISTS `traitement`;
CREATE TABLE IF NOT EXISTS `traitement` (
  `id_trait` int NOT NULL AUTO_INCREMENT,
  `lib_trait` varchar(100) NOT NULL,
  PRIMARY KEY (`id_trait`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `traitement`
--

INSERT INTO `traitement` (`id_trait`, `lib_trait`) VALUES
(1, 'AZ'),
(2, 'AA');

-- --------------------------------------------------------

--
-- Structure de la table `typesmessage`
--

DROP TABLE IF EXISTS `typesmessage`;
CREATE TABLE IF NOT EXISTS `typesmessage` (
  `id_type_message` int NOT NULL AUTO_INCREMENT,
  `code_type` varchar(191) NOT NULL,
  `libelle_type` varchar(255) NOT NULL,
  `description` text,
  `icone` varchar(255) DEFAULT NULL,
  `couleur` varchar(50) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_type_message`),
  UNIQUE KEY `code_type` (`code_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `typesnotification`
--

DROP TABLE IF EXISTS `typesnotification`;
CREATE TABLE IF NOT EXISTS `typesnotification` (
  `id_type_notification` int NOT NULL AUTO_INCREMENT,
  `code_type` varchar(255) NOT NULL,
  `libelle_type` varchar(255) NOT NULL,
  `description` text,
  `template_titre` varchar(255) NOT NULL,
  `template_contenu` text NOT NULL,
  `icone` varchar(255) DEFAULT NULL,
  `couleur` varchar(50) DEFAULT NULL,
  `canal_email` tinyint(1) NOT NULL,
  `canal_systeme` tinyint(1) NOT NULL,
  `canal_sms` tinyint(1) NOT NULL,
  `actif` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_type_notification`),
  UNIQUE KEY `code_type` (`code_type`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `typesreclamation`
--

DROP TABLE IF EXISTS `typesreclamation`;
CREATE TABLE IF NOT EXISTS `typesreclamation` (
  `id_type_reclamation` int NOT NULL AUTO_INCREMENT,
  `code_type` varchar(100) NOT NULL,
  `libelle_type` varchar(191) NOT NULL,
  `description` text,
  `delai_traitement_jours` int NOT NULL,
  `actif` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_type_reclamation`),
  UNIQUE KEY `code_type` (`code_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `typesutilisateur`
--

DROP TABLE IF EXISTS `typesutilisateur`;
CREATE TABLE IF NOT EXISTS `typesutilisateur` (
  `id_type` int NOT NULL AUTO_INCREMENT,
  `code_type` varchar(191) NOT NULL,
  `libelle_type` varchar(255) NOT NULL,
  `description` text,
  `actif` tinyint(1) NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_type`),
  UNIQUE KEY `code_type` (`code_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `unites_enseignement`
--

DROP TABLE IF EXISTS `unites_enseignement`;
CREATE TABLE IF NOT EXISTS `unites_enseignement` (
  `ue_id` int NOT NULL AUTO_INCREMENT,
  `code_ue` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle_ue` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nombre_credits` int NOT NULL,
  `niveau_id` int NOT NULL,
  `specialite_id` int DEFAULT NULL,
  `est_obligatoire` tinyint(1) DEFAULT '1',
  `prerequis_ue_id` int DEFAULT NULL,
  `est_actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ue_id`),
  UNIQUE KEY `code_ue` (`code_ue`),
  KEY `niveau_id` (`niveau_id`),
  KEY `specialite_id` (`specialite_id`),
  KEY `prerequis_ue_id` (`prerequis_ue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `utilisateur_id` int NOT NULL AUTO_INCREMENT,
  `code_utilisateur` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int NOT NULL,
  `statut_id` int NOT NULL,
  `mot_de_passe_hash` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `salt` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tentatives_connexion_echouees` int DEFAULT '0',
  `compte_bloque` tinyint(1) DEFAULT '0',
  `date_blocage` datetime DEFAULT NULL,
  `token_recuperation_mdp` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_expiration_token` datetime DEFAULT NULL,
  `photo_profil` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `derniere_connexion` datetime DEFAULT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`utilisateur_id`),
  UNIQUE KEY `code_utilisateur` (`code_utilisateur`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `statut_id` (`statut_id`),
  KEY `derniere_connexion` (`derniere_connexion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `versions_rapports`
--

DROP TABLE IF EXISTS `versions_rapports`;
CREATE TABLE IF NOT EXISTS `versions_rapports` (
  `version_id` int NOT NULL AUTO_INCREMENT,
  `rapport_id` int NOT NULL,
  `numero_version` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fichier_version` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille_fichier` bigint DEFAULT NULL,
  `commentaire_version` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cree_par_id` int DEFAULT NULL,
  PRIMARY KEY (`version_id`),
  KEY `rapport_id` (`rapport_id`),
  KEY `cree_par_id` (`cree_par_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_etudiants_complets`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `v_etudiants_complets`;
CREATE TABLE IF NOT EXISTS `v_etudiants_complets` (
`etudiant_id` int
,`utilisateur_id` int
,`numero_etudiant` varchar(20)
,`numero_carte_etudiant` varchar(30)
,`nom` varchar(100)
,`prenoms` varchar(100)
,`email` varchar(100)
,`telephone` varchar(20)
,`libelle_niveau` varchar(100)
,`libelle_specialite` varchar(100)
,`annee_inscription` int
,`moyenne_generale` decimal(4,2)
,`nombre_credits_valides` int
,`nombre_credits_requis` int
,`taux_progression` decimal(5,2)
,`statut_eligibilite` varchar(100)
,`est_actif` tinyint(1)
,`date_creation` timestamp
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_rapports_complets`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `v_rapports_complets`;
CREATE TABLE IF NOT EXISTS `v_rapports_complets` (
`rapport_id` int
,`titre` varchar(300)
,`type_rapport` varchar(50)
,`date_depot` datetime
,`date_limite_depot` datetime
,`statut_rapport` varchar(100)
,`couleur_affichage` varchar(7)
,`numero_etudiant` varchar(20)
,`nom_complet_etudiant` varchar(201)
,`nom_complet_encadreur` varchar(201)
,`entreprise_stage` varchar(200)
,`maitre_stage_nom` varchar(100)
,`nombre_pages` int
,`nombre_mots` int
,`est_confidentiel` tinyint(1)
,`date_creation` timestamp
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_etudiants_complets`
--
DROP TABLE IF EXISTS `v_etudiants_complets`;

DROP VIEW IF EXISTS `v_etudiants_complets`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_etudiants_complets`  AS SELECT `e`.`etudiant_id` AS `etudiant_id`, `u`.`utilisateur_id` AS `utilisateur_id`, `e`.`numero_etudiant` AS `numero_etudiant`, `e`.`numero_carte_etudiant` AS `numero_carte_etudiant`, `ip`.`nom` AS `nom`, `ip`.`prenoms` AS `prenoms`, `u`.`email` AS `email`, `ip`.`telephone` AS `telephone`, `ne`.`libelle_niveau` AS `libelle_niveau`, `sp`.`libelle_specialite` AS `libelle_specialite`, `e`.`annee_inscription` AS `annee_inscription`, `e`.`moyenne_generale` AS `moyenne_generale`, `e`.`nombre_credits_valides` AS `nombre_credits_valides`, `e`.`nombre_credits_requis` AS `nombre_credits_requis`, `e`.`taux_progression` AS `taux_progression`, `s`.`libelle_statut` AS `statut_eligibilite`, `u`.`est_actif` AS `est_actif`, `u`.`date_creation` AS `date_creation` FROM (((((`etudiants` `e` join `utilisateurs` `u` on((`e`.`utilisateur_id` = `u`.`utilisateur_id`))) join `informations_personnelles` `ip` on((`u`.`utilisateur_id` = `ip`.`utilisateur_id`))) join `niveaux_etude` `ne` on((`e`.`niveau_id` = `ne`.`niveau_id`))) left join `specialites` `sp` on((`e`.`specialite_id` = `sp`.`specialite_id`))) join `statuts` `s` on((`e`.`statut_eligibilite` = `s`.`statut_id`))) WHERE (`u`.`est_actif` = 1) ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_rapports_complets`
--
DROP TABLE IF EXISTS `v_rapports_complets`;

DROP VIEW IF EXISTS `v_rapports_complets`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_rapports_complets`  AS SELECT `r`.`rapport_id` AS `rapport_id`, `r`.`titre` AS `titre`, `r`.`type_rapport` AS `type_rapport`, `r`.`date_depot` AS `date_depot`, `r`.`date_limite_depot` AS `date_limite_depot`, `s`.`libelle_statut` AS `statut_rapport`, `s`.`couleur_affichage` AS `couleur_affichage`, `e`.`numero_etudiant` AS `numero_etudiant`, concat(`ip_etd`.`nom`,' ',`ip_etd`.`prenoms`) AS `nom_complet_etudiant`, concat(`ip_enc`.`nom`,' ',`ip_enc`.`prenoms`) AS `nom_complet_encadreur`, `r`.`entreprise_stage` AS `entreprise_stage`, `r`.`maitre_stage_nom` AS `maitre_stage_nom`, `r`.`nombre_pages` AS `nombre_pages`, `r`.`nombre_mots` AS `nombre_mots`, `r`.`est_confidentiel` AS `est_confidentiel`, `r`.`date_creation` AS `date_creation` FROM (((((((`rapports` `r` join `etudiants` `e` on((`r`.`etudiant_id` = `e`.`etudiant_id`))) join `utilisateurs` `u_etd` on((`e`.`utilisateur_id` = `u_etd`.`utilisateur_id`))) join `informations_personnelles` `ip_etd` on((`u_etd`.`utilisateur_id` = `ip_etd`.`utilisateur_id`))) join `enseignants` `ens` on((`r`.`encadreur_id` = `ens`.`enseignant_id`))) join `utilisateurs` `u_enc` on((`ens`.`utilisateur_id` = `u_enc`.`utilisateur_id`))) join `informations_personnelles` `ip_enc` on((`u_enc`.`utilisateur_id` = `ip_enc`.`utilisateur_id`))) join `statuts` `s` on((`r`.`statut_id` = `s`.`statut_id`))) ;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `configuration_systeme`
--
ALTER TABLE `configuration_systeme`
  ADD CONSTRAINT `fk_config_modificateur` FOREIGN KEY (`modifie_par`) REFERENCES `utilisateurs` (`utilisateur_id`);

--
-- Contraintes pour la table `elements_constitutifs`
--
ALTER TABLE `elements_constitutifs`
  ADD CONSTRAINT `fk_ecue_ue` FOREIGN KEY (`ue_id`) REFERENCES `unites_enseignement` (`ue_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `enseignants`
--
ALTER TABLE `enseignants`
  ADD CONSTRAINT `fk_enseignants_fonction` FOREIGN KEY (`fonction_id`) REFERENCES `fonctions` (`fonction_id`),
  ADD CONSTRAINT `fk_enseignants_grade` FOREIGN KEY (`grade_id`) REFERENCES `grades_academiques` (`grade_id`),
  ADD CONSTRAINT `fk_enseignants_specialite` FOREIGN KEY (`specialite_id`) REFERENCES `specialites` (`specialite_id`),
  ADD CONSTRAINT `fk_enseignants_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `etudiants`
--
ALTER TABLE `etudiants`
  ADD CONSTRAINT `fk_etudiants_niveau` FOREIGN KEY (`niveau_id`) REFERENCES `niveaux_etude` (`niveau_id`),
  ADD CONSTRAINT `fk_etudiants_specialite` FOREIGN KEY (`specialite_id`) REFERENCES `specialites` (`specialite_id`),
  ADD CONSTRAINT `fk_etudiants_statut_eligibilite` FOREIGN KEY (`statut_eligibilite`) REFERENCES `statuts` (`statut_id`),
  ADD CONSTRAINT `fk_etudiants_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `logs_audit`
--
ALTER TABLE `logs_audit`
  ADD CONSTRAINT `fk_logs_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`);

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_destinataire` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`utilisateur_id`),
  ADD CONSTRAINT `fk_messages_expediteur` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`utilisateur_id`),
  ADD CONSTRAINT `fk_messages_reponse` FOREIGN KEY (`reponse_a`) REFERENCES `messages` (`message_id`);

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `personnel_administratif`
--
ALTER TABLE `personnel_administratif`
  ADD CONSTRAINT `fk_personnel_fonction` FOREIGN KEY (`fonction_id`) REFERENCES `fonctions` (`fonction_id`),
  ADD CONSTRAINT `fk_personnel_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rapports`
--
ALTER TABLE `rapports`
  ADD CONSTRAINT `fk_rapports_co_encadreur` FOREIGN KEY (`co_encadreur_id`) REFERENCES `enseignants` (`enseignant_id`),
  ADD CONSTRAINT `fk_rapports_encadreur` FOREIGN KEY (`encadreur_id`) REFERENCES `enseignants` (`enseignant_id`),
  ADD CONSTRAINT `fk_rapports_etudiant` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`etudiant_id`),
  ADD CONSTRAINT `fk_rapports_statut` FOREIGN KEY (`statut_id`) REFERENCES `statuts` (`statut_id`);

--
-- Contraintes pour la table `sessions_utilisateurs`
--
ALTER TABLE `sessions_utilisateurs`
  ADD CONSTRAINT `fk_sessions_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `specialites`
--
ALTER TABLE `specialites`
  ADD CONSTRAINT `fk_specialites_niveau` FOREIGN KEY (`niveau_id`) REFERENCES `niveaux_etude` (`niveau_id`);

--
-- Contraintes pour la table `unites_enseignement`
--
ALTER TABLE `unites_enseignement`
  ADD CONSTRAINT `fk_ue_niveau` FOREIGN KEY (`niveau_id`) REFERENCES `niveaux_etude` (`niveau_id`),
  ADD CONSTRAINT `fk_ue_prerequis` FOREIGN KEY (`prerequis_ue_id`) REFERENCES `unites_enseignement` (`ue_id`),
  ADD CONSTRAINT `fk_ue_specialite` FOREIGN KEY (`specialite_id`) REFERENCES `specialites` (`specialite_id`);

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_utilisateurs_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`),
  ADD CONSTRAINT `fk_utilisateurs_statut` FOREIGN KEY (`statut_id`) REFERENCES `statuts` (`statut_id`);

--
-- Contraintes pour la table `versions_rapports`
--
ALTER TABLE `versions_rapports`
  ADD CONSTRAINT `fk_versions_createur` FOREIGN KEY (`cree_par_id`) REFERENCES `utilisateurs` (`utilisateur_id`),
  ADD CONSTRAINT `fk_versions_rapport` FOREIGN KEY (`rapport_id`) REFERENCES `rapports` (`rapport_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
