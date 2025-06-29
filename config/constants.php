<?php
/**
 * Constantes globales du système
 * Système de Validation Académique - Université Félix Houphouët-Boigny
 */

// Version de l'application
define('APP_VERSION', '1.0.0');
define('APP_NAME', 'Système de Validation Académique');
define('APP_SHORT_NAME', 'SVA-UFHB');

// Configuration de l'université
define('UNIVERSITY_NAME', 'Université Félix Houphouët-Boigny de Cocody');
define('UNIVERSITY_SHORT_NAME', 'UFHB');
define('UNIVERSITY_ADDRESS', 'BP V 34 Abidjan, Côte d\'Ivoire');
define('UNIVERSITY_PHONE', '+225 22 44 08 95');
define('UNIVERSITY_EMAIL', 'info@ufhb.edu.ci');
define('UNIVERSITY_WEBSITE', 'https://www.ufhb.edu.ci');

// Chemins et URLs
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads');
define('ASSETS_PATH', '/assets');
define('PAGES_PATH', '/pages');

// Configuration des uploads
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_DOCUMENT_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif'
]);

// Configuration des sessions
define('SESSION_TIMEOUT', 7200); // 2 heures
define('SESSION_CHECK_IP', false); // Peut causer des problèmes avec certains proxies
define('REMEMBER_ME_DURATION', 30 * 24 * 60 * 60); // 30 jours

// Configuration de sécurité
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_BLOCK_DURATION', 15); // minutes
define('CSRF_TOKEN_EXPIRY', 3600); // 1 heure

// Pagination
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// Configuration email
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@ufhb.edu.ci');
define('SMTP_FROM_NAME', 'Système de Validation Académique');

// Niveaux d'accès (correspondant aux rôles)
define('ACCESS_LEVEL_STUDENT', 3);
define('ACCESS_LEVEL_STAFF', 4);
define('ACCESS_LEVEL_TEACHER', 5);
define('ACCESS_LEVEL_SECRETARY', 6);
define('ACCESS_LEVEL_COMMISSION', 7);
define('ACCESS_LEVEL_COMMUNICATION', 7);
define('ACCESS_LEVEL_ACADEMIC_MANAGER', 8);
define('ACCESS_LEVEL_ADMIN', 10);

// IDs des rôles (basés sur votre base de données)
define('ROLE_ADMIN', 1);
define('ROLE_RESPONSABLE_SCOLARITE', 2);
define('ROLE_CHARGE_COMMUNICATION', 3);
define('ROLE_COMMISSION', 4);
define('ROLE_SECRETAIRE', 5);
define('ROLE_ENSEIGNANT', 6);
define('ROLE_PERSONNEL_ADMIN', 7);
define('ROLE_ETUDIANT', 8);

// IDs des statuts (basés sur votre base de données)
define('STATUT_ACTIF', 1);
define('STATUT_INACTIF', 2);
define('STATUT_BLOQUE', 3);
define('STATUT_SUSPENDU', 4);

// Statuts des étudiants
define('ETUDIANT_ELIGIBLE', 5);
define('ETUDIANT_NON_ELIGIBLE', 6);
define('ETUDIANT_EN_ATTENTE', 7);

// Statuts des rapports
define('RAPPORT_BROUILLON', 8);
define('RAPPORT_DEPOSE', 9);
define('RAPPORT_EN_VERIFICATION', 10);
define('RAPPORT_VALIDE', 11);
define('RAPPORT_REJETE', 12);
define('RAPPORT_EN_REVISION', 13);

// Statuts des soutenances
define('SOUTENANCE_PROGRAMMEE', 14);
define('SOUTENANCE_CONFIRMEE', 15);
define('SOUTENANCE_REPORTEE', 16);
define('SOUTENANCE_TERMINEE', 17);
define('SOUTENANCE_ANNULEE', 18);

// Statuts des règlements
define('REGLEMENT_PAYE', 19);
define('REGLEMENT_PARTIEL', 20);
define('REGLEMENT_NON_PAYE', 21);
define('REGLEMENT_ECHU', 22);

// Statuts des réclamations
define('RECLAMATION_OUVERTE', 23);
define('RECLAMATION_EN_COURS', 24);
define('RECLAMATION_RESOLUE', 25);
define('RECLAMATION_FERMEE', 26);

// Priorités
define('PRIORITE_BASSE', 27);
define('PRIORITE_NORMALE', 28);
define('PRIORITE_HAUTE', 29);
define('PRIORITE_URGENTE', 30);

// Niveau Master 2 (basé sur votre base de données)
define('NIVEAU_MASTER2', 5);

// Spécialités Master 2
define('SPECIALITE_GENIE_LOGICIEL', 1);
define('SPECIALITE_IA', 2);
define('SPECIALITE_INFORMATIQUE', 3);
define('SPECIALITE_RESEAUX', 4);
define('SPECIALITE_TELECOM', 5);

// Grades académiques
define('GRADE_PROFESSEUR', 1);
define('GRADE_MAITRE_CONFERENCES', 2);
define('GRADE_MAITRE_ASSISTANT', 3);
define('GRADE_ASSISTANT', 4);
define('GRADE_DOCTORANT', 5);
define('GRADE_VACATAIRE', 6);

// Fonctions
define('FONCTION_DIRECTEUR_FILIERE', 1);
define('FONCTION_RESPONSABLE_MASTER', 2);
define('FONCTION_RESPONSABLE_LICENCE', 3);
define('FONCTION_COORDINATEUR_PEDAGOGIQUE', 4);
define('FONCTION_ADMIN_SCOLARITE', 5);
define('FONCTION_CHARGE_COMMUNICATION', 6);
define('FONCTION_COMMISSION_MEMBRE', 7);
define('FONCTION_ENSEIGNANT', 8);
define('FONCTION_SECRETAIRE', 9);

// Configuration de l'année académique
// Dans constants.php, avant la ligne 157
define('CURRENT_ACADEMIC_YEAR', getCurrentAcademicYear());
define('ACADEMIC_YEAR_START_MONTH', 9); // Septembre
define('ACADEMIC_YEAR_END_MONTH', 8);   // Août

// Dates importantes
define('RAPPORT_DEADLINE_MONTH', 6); // Juin
define('RAPPORT_DEADLINE_DAY', 30);
define('SOUTENANCE_PERIOD_START_MONTH', 7); // Juillet
define('SOUTENANCE_PERIOD_END_MONTH', 9);   // Septembre

// Configuration des notes
define('NOTE_MIN', 0);
define('NOTE_MAX', 20);
define('NOTE_PASSAGE', 10);
define('MOYENNE_ELIGIBILITE', 10.0);
define('CREDITS_MASTER2_REQUIRED', 60);

// Types de rapports
define('TYPE_RAPPORT_STAGE', 'Stage');
define('TYPE_RAPPORT_MEMOIRE', 'Mémoire');
define('TYPE_RAPPORT_PROJET', 'Projet');

// Durées en minutes
define('DUREE_SOUTENANCE_STANDARD', 60);
define('DUREE_SOUTENANCE_EXTENDED', 90);

// Configuration des logs
define('LOG_RETENTION_DAYS', 365); // 1 an
define('LOG_ARCHIVE_AFTER_DAYS', 90); // 3 mois

// Messages système
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// Formats de date
define('DATE_FORMAT_DISPLAY', 'd/m/Y');
define('DATE_FORMAT_FULL', 'd/m/Y à H:i');
define('DATE_FORMAT_SQL', 'Y-m-d');
define('DATETIME_FORMAT_SQL', 'Y-m-d H:i:s');

// Langue et localisation
define('APP_LOCALE', 'fr_FR');
define('APP_TIMEZONE', 'Africa/Abidjan');
define('APP_CURRENCY', 'FCFA');

// Configuration Debug (à désactiver en production)
define('DEBUG_MODE', true);
define('SHOW_SQL_ERRORS', true);
define('LOG_SQL_QUERIES', false);

// Configuration de maintenance
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Le système est temporairement en maintenance. Veuillez réessayer plus tard.');

// Configuration backup
define('BACKUP_ENABLED', true);
define('BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly
define('BACKUP_RETENTION_DAYS', 30);

// Types MIME autorisés pour les documents
define('MIME_TYPES', [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'rtf' => 'application/rtf',
    'txt' => 'text/plain',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif'
]);

// Extensions de fichiers autorisées
define('ALLOWED_EXTENSIONS', [
    'documents' => ['pdf', 'doc', 'docx', 'rtf'],
    'images' => ['jpg', 'jpeg', 'png', 'gif'],
    'all' => ['pdf', 'doc', 'docx', 'rtf', 'jpg', 'jpeg', 'png', 'gif']
]);

// Configuration des notifications
define('NOTIFICATION_TYPES', [
    'RAPPORT_SOUMIS' => 'Rapport soumis',
    'RAPPORT_VALIDE' => 'Rapport validé',
    'RAPPORT_REJETE' => 'Rapport rejeté',
    'SOUTENANCE_PROGRAMMEE' => 'Soutenance programmée',
    'SOUTENANCE_RAPPEL' => 'Rappel de soutenance',
    'NOTES_SAISIES' => 'Notes saisies',
    'RECLAMATION_REPONSE' => 'Réponse à réclamation',
    'SYSTEME_INFO' => 'Information système'
]);

// Couleurs pour les statuts (CSS classes)
define('STATUS_COLORS', [
    'success' => '#28a745',
    'warning' => '#ffc107',
    'danger' => '#dc3545',
    'info' => '#17a2b8',
    'secondary' => '#6c757d',
    'primary' => '#007bff'
]);

// Configuration API (si nécessaire)
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requêtes par heure
define('API_TOKEN_EXPIRY', 3600); // 1 heure

// Mensajes d'erreur standard
define('ERROR_MESSAGES', [
    'INVALID_CREDENTIALS' => 'Email ou mot de passe incorrect.',
    'ACCOUNT_BLOCKED' => 'Votre compte est bloqué. Contactez l\'administrateur.',
    'ACCOUNT_INACTIVE' => 'Votre compte est inactif.',
    'SESSION_EXPIRED' => 'Votre session a expiré. Veuillez vous reconnecter.',
    'ACCESS_DENIED' => 'Accès refusé. Vous n\'avez pas les permissions nécessaires.',
    'FILE_TOO_LARGE' => 'Le fichier est trop volumineux.',
    'INVALID_FILE_TYPE' => 'Type de fichier non autorisé.',
    'UPLOAD_FAILED' => 'Échec du téléchargement du fichier.',
    'DATABASE_ERROR' => 'Erreur de base de données.',
    'VALIDATION_ERROR' => 'Erreur de validation des données.',
    'NOT_FOUND' => 'Ressource non trouvée.',
    'DUPLICATE_ENTRY' => 'Cette entrée existe déjà.',
    'OPERATION_FAILED' => 'L\'opération a échoué.'
]);

// Messages de succès standard
define('SUCCESS_MESSAGES', [
    'CREATED' => 'Créé avec succès.',
    'UPDATED' => 'Mis à jour avec succès.',
    'DELETED' => 'Supprimé avec succès.',
    'UPLOADED' => 'Fichier téléchargé avec succès.',
    'SUBMITTED' => 'Soumis avec succès.',
    'VALIDATED' => 'Validé avec succès.',
    'SENT' => 'Envoyé avec succès.',
    'SAVED' => 'Sauvegardé avec succès.'
]);

// Configuration du cache (si implémenté)
define('CACHE_ENABLED', false);
define('CACHE_DURATION', 3600); // 1 heure
define('CACHE_PREFIX', 'sva_');

/**
 * Fonction utilitaire pour obtenir l'année académique actuelle
 */
function getCurrentAcademicYear() {
    $currentYear = date('Y');
    $currentMonth = date('n');
    define(ACADEMIC_YEAR_START_MONTH,9);
    if ($currentMonth >= ACADEMIC_YEAR_START_MONTH) {
        return $currentYear . '-' . ($currentYear + 1);
    } else {
        return ($currentYear - 1) . '-' . $currentYear;
    }
}

/**
 * Vérifier si nous sommes en mode maintenance
 */
function isMaintenanceMode() {
    return MAINTENANCE_MODE && !isAdmin();
}

/**
 * Vérifier si l'utilisateur actuel est admin
 */
function isAdmin() {
    return isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == ROLE_ADMIN;
}

/**
 * Obtenir le chemin complet d'un fichier
 */
function getFullPath($relativePath) {
    return BASE_PATH . '/' . ltrim($relativePath, '/');
}

/**
 * Obtenir l'URL complète d'un asset
 */
function asset($path) {
    return ASSETS_PATH . '/' . ltrim($path, '/');
}

/**
 * Obtenir l'URL d'une page
 */
function page($path) {
    return PAGES_PATH . '/' . ltrim($path, '/');
}

// Définir le fuseau horaire
date_default_timezone_set(APP_TIMEZONE);

// Configuration des erreurs selon le mode debug
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Configuration PHP
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');

// Vérification de la version PHP
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Ce système nécessite PHP 7.4 ou supérieur. Version actuelle: ' . PHP_VERSION);
}

// Vérification des extensions PHP requises
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'session'];
foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        die("Extension PHP requise manquante: $extension");
    }
}
?>