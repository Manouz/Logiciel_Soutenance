<?php
/**
 * Configuration de la base de données
 * Système de Validation Académique - UFHB Cocody
 * Fichier: config/database.php
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Configuration de la base de données
    private $host = 'localhost';
    private $database = 'validation_soutenance';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    private function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->database;charset=$this->charset";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Erreur de connexion BDD: " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Méthode pour exécuter des requêtes préparées
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Erreur SQL: " . $e->getMessage() . " - SQL: " . $sql);
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
    }
    
    /**
     * Méthode pour obtenir un seul enregistrement
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    /**
     * Méthode pour obtenir tous les enregistrements
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Méthode pour les insertions avec retour de l'ID
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }
    
    /**
     * Méthode pour les mises à jour
     */
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Méthode pour les suppressions
     */
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Vérifier si une table existe
     */
    public function tableExists($tableName) {
        $sql = "SHOW TABLES LIKE ?";
        $result = $this->fetch($sql, [$tableName]);
        return !empty($result);
    }
    
    /**
     * Obtenir la structure d'une table
     */
    public function getTableStructure($tableName) {
        $sql = "DESCRIBE $tableName";
        return $this->fetchAll($sql);
    }
}

/**
 * Classe de gestion des sessions
 */
class SessionManager {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public static function getUserId() {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function getUserRole() {
        self::start();
        return $_SESSION['user_role'] ?? null;
    }
    
    public static function getUserRoles() {
        self::start();
        return $_SESSION['user_roles'] ?? [];
    }
    
    public static function getUserName() {
        self::start();
        return $_SESSION['user_name'] ?? 'Utilisateur';
    }
    
    public static function getUserEmail() {
        self::start();
        return $_SESSION['user_email'] ?? '';
    }
    
    public static function hasRole($role) {
        $roles = self::getUserRoles();
        return in_array($role, $roles);
    }
    
    public static function setUserData($userData) {
        self::start();
        $_SESSION['user_id'] = $userData['utilisateur_id'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_name'] = $userData['nom'] . ' ' . $userData['prenoms'];
        $_SESSION['user_role'] = $userData['role_principal'];
        $_SESSION['user_roles'] = $userData['tous_roles'] ?? [];
        $_SESSION['login_time'] = time();
        
        // Log de connexion
        self::logUserAction('LOGIN', 'Connexion réussie');
    }
    
    public static function destroy() {
        self::start();
        
        // Log de déconnexion
        self::logUserAction('LOGOUT', 'Déconnexion');
        
        session_destroy();
        session_unset();
    }
    
    public static function regenerateId() {
        self::start();
        session_regenerate_id(true);
    }
    
    private static function logUserAction($action, $description) {
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO logs_audit (utilisateur_id, type_action, table_cible, commentaire, adresse_ip, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $params = [
                self::getUserId(),
                $action,
                'session',
                $description,
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];
            $db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Erreur log session: " . $e->getMessage());
        }
    }
}

/**
 * Constantes globales
 */
define('BASE_URL', '/validation_academique/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('PAGES_URL', BASE_URL . 'pages/');
define('API_URL', BASE_URL . 'api/');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// Configuration de l'application
define('APP_NAME', 'Système de Validation Académique');
define('APP_VERSION', '1.0.0');
define('UNIVERSITY_NAME', 'Université Félix Houphouët-Boigny de Cocody');
define('UNIVERSITY_SHORT', 'UFHB Cocody');

// Rôles utilisateurs
define('ROLE_ADMIN', 'Administrateur');
define('ROLE_RESPONSABLE_SCOLARITE', 'Responsable Scolarité');
define('ROLE_CHARGE_COMMUNICATION', 'Chargé Communication');
define('ROLE_COMMISSION', 'Commission');
define('ROLE_SECRETAIRE', 'Secrétaire');
define('ROLE_ENSEIGNANT', 'Enseignant');
define('ROLE_PERSONNEL_ADMIN', 'Personnel Administratif');
define('ROLE_ETUDIANT', 'Étudiant');

// Statuts
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

/**
 * Fonctions utilitaires
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

function generateUniqueCode($prefix = '', $length = 8) {
    return $prefix . strtoupper(bin2hex(random_bytes($length / 2)));
}

function calculateAge($birthdate) {
    if (empty($birthdate)) return null;
    $diff = date_diff(date_create($birthdate), date_create('today'));
    return $diff->y;
}

function timeAgo($datetime) {
    if (empty($datetime)) return '';
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'À l\'instant';
    if ($time < 3600) return floor($time/60) . ' min';
    if ($time < 86400) return floor($time/3600) . ' h';
    if ($time < 2592000) return floor($time/86400) . ' j';
    if ($time < 31536000) return floor($time/2592000) . ' mois';
    
    return date('d/m/Y', strtotime($datetime));
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function redirectTo($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function logError($message, $file = '', $line = '') {
    $log = date('[Y-m-d H:i:s] ') . $message;
    if ($file) $log .= " in $file";
    if ($line) $log .= " at line $line";
    error_log($log);
}

/**
 * Gestion des erreurs globales
 */
set_error_handler(function($severity, $message, $file, $line) {
    logError("PHP Error: $message", $file, $line);
});

set_exception_handler(function($exception) {
    logError("Uncaught Exception: " . $exception->getMessage(), $exception->getFile(), $exception->getLine());
});

// Configuration des erreurs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('Africa/Abidjan');
?>