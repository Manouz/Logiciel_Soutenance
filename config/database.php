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
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_TIMEOUT => 30
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
    
    /**
     * Compter les enregistrements
     */
    public function count($table, $conditions = '', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM $table";
        if ($conditions) {
            $sql .= " WHERE $conditions";
        }
        $result = $this->fetch($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Vérifier l'existence d'un enregistrement
     */
    public function exists($table, $conditions, $params = []) {
        return $this->count($table, $conditions, $params) > 0;
    }
    
    /**
     * Obtenir le dernier ID inséré
     */
    public function getLastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Échapper les noms de colonnes/tables
     */
    public function escapeIdentifier($identifier) {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}

/**
 * Classe de gestion des sessions
 */
class SessionManager {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuration sécurisée des sessions
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
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
    
    public static function getUserCode() {
        self::start();
        return $_SESSION['user_code'] ?? '';
    }
    
    public static function hasRole($role) {
        $roles = self::getUserRoles();
        return in_array($role, $roles);
    }
    
    public static function setUserData($userData) {
        self::start();
        $_SESSION['user_id'] = $userData['utilisateur_id'];
        $_SESSION['user_code'] = $userData['code_utilisateur'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_name'] = $userData['nom'] . ' ' . $userData['prenoms'];
        $_SESSION['user_role'] = $userData['role_principal'];
        $_SESSION['user_roles'] = $userData['tous_roles'] ?? [$userData['role_principal']];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Régénérer l'ID de session pour la sécurité
        self::regenerateId();
        
        // Log de connexion
        self::logUserAction('LOGIN', 'Connexion réussie');
    }
    
    public static function updateLastActivity() {
        self::start();
        $_SESSION['last_activity'] = time();
    }
    
    public static function isSessionExpired($timeout = 3600) {
        self::start();
        if (isset($_SESSION['last_activity'])) {
            return (time() - $_SESSION['last_activity']) > $timeout;
        }
        return true;
    }
    
    public static function destroy() {
        self::start();
        
        // Log de déconnexion
        self::logUserAction('LOGOUT', 'Déconnexion');
        
        // Détruire toutes les données de session
        $_SESSION = array();
        
        // Détruire le cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
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
 * Classe d'authentification
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authentifier un utilisateur
     */
    public function authenticate($email, $password) {
        // Validation des entrées
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email et mot de passe requis'
            ];
        }
        
        // Vérifier si le compte est bloqué
        $blockInfo = $this->checkAccountBlock($email);
        if ($blockInfo['blocked']) {
            return [
                'success' => false,
                'message' => $blockInfo['message'],
                'locked' => true
            ];
        }
        
        // Récupérer l'utilisateur
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            $this->logFailedAttempt($email, 'Utilisateur inexistant');
            return [
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ];
        }
        
        // Vérifier le mot de passe
        $passwordHash = hash('sha256', $password . $user['salt']);
        
        if ($passwordHash !== $user['mot_de_passe_hash']) {
            $this->handleFailedLogin($user['utilisateur_id'], $email);
            return [
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ];
        }
        
        // Vérifier si l'utilisateur est actif
        if (!$user['est_actif']) {
            return [
                'success' => false,
                'message' => 'Compte désactivé. Contactez l\'administrateur'
            ];
        }
        
        // Vérifier le statut du compte
        if ($user['statut_code'] === 'BLOQUE') {
            return [
                'success' => false,
                'message' => 'Compte bloqué. Contactez l\'administrateur'
            ];
        }
        
        if ($user['statut_code'] === 'SUSPENDU') {
            return [
                'success' => false,
                'message' => 'Compte suspendu temporairement'
            ];
        }
        
        // Connexion réussie
        $this->handleSuccessfulLogin($user['utilisateur_id']);
        
        return [
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => [
                'utilisateur_id' => $user['utilisateur_id'],
                'code_utilisateur' => $user['code_utilisateur'],
                'email' => $user['email'],
                'nom' => $user['nom'],
                'prenoms' => $user['prenoms'],
                'role_principal' => $user['nom_role'],
                'role_id' => $user['role_id'],
                'tous_roles' => [$user['nom_role']]
            ]
        ];
    }
    
    /**
     * Récupérer un utilisateur par email
     */
    private function getUserByEmail($email) {
        $sql = "SELECT u.*, ip.nom, ip.prenoms, r.nom_role, s.code_statut as statut_code
                FROM utilisateurs u 
                LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                LEFT JOIN roles r ON u.role_id = r.role_id
                LEFT JOIN statuts s ON u.statut_id = s.statut_id
                WHERE u.email = ? AND u.est_actif = 1";
        
        return $this->db->fetch($sql, [$email]);
    }
    
    /**
     * Vérifier si le compte est bloqué
     */
    private function checkAccountBlock($email) {
        $sql = "SELECT tentatives_connexion_echouees, compte_bloque, date_blocage 
                FROM utilisateurs 
                WHERE email = ?";
        
        $user = $this->db->fetch($sql, [$email]);
        
        if (!$user) {
            return ['blocked' => false];
        }
        
        // Vérifier si le compte est bloqué
        if ($user['compte_bloque']) {
            $blocageTime = strtotime($user['date_blocage']);
            $now = time();
            $blocageDuration = 1 * 60; // 30 minutes
            
            if (($now - $blocageTime) < $blocageDuration) {
                $remainingTime = $blocageDuration - ($now - $blocageTime);
                $remainingMinutes = ceil($remainingTime / 60);
                
                return [
                    'blocked' => true,
                    'message' => "Compte bloqué. Réessayez dans {$remainingMinutes} minute(s)"
                ];
            } else {
                // Débloquer le compte
                $this->unblockAccount($email);
                return ['blocked' => false];
            }
        }
        
        return ['blocked' => false];
    }
    
    /**
     * Gérer une connexion échouée
     */
    private function handleFailedLogin($userId, $email) {
        $this->logFailedAttempt($email, 'Mot de passe incorrect');
        
        // Incrémenter le compteur de tentatives
        $sql = "UPDATE utilisateurs 
                SET tentatives_connexion_echouees = tentatives_connexion_echouees + 1 
                WHERE utilisateur_id = ?";
        $this->db->query($sql, [$userId]);
        
        // Vérifier si on doit bloquer le compte
        $user = $this->db->fetch("SELECT tentatives_connexion_echouees FROM utilisateurs WHERE utilisateur_id = ?", [$userId]);
        
        if ($user['tentatives_connexion_echouees'] >= 5) {
            $this->blockAccount($userId);
        }
    }
    
    /**
     * Gérer une connexion réussie
     */
    private function handleSuccessfulLogin($userId) {
        $sql = "UPDATE utilisateurs 
                SET tentatives_connexion_echouees = 0, 
                    compte_bloque = 0,
                    date_blocage = NULL,
                    derniere_connexion = NOW() 
                WHERE utilisateur_id = ?";
        $this->db->query($sql, [$userId]);
        
        // Log de connexion réussie
        $this->logSuccessfulAttempt($userId);
    }
    
    /**
     * Bloquer un compte
     */
    private function blockAccount($userId) {
        $sql = "UPDATE utilisateurs 
                SET compte_bloque = 1, date_blocage = NOW() 
                WHERE utilisateur_id = ?";
        $this->db->query($sql, [$userId]);
    }
    
    /**
     * Débloquer un compte
     */
    private function unblockAccount($email) {
        $sql = "UPDATE utilisateurs 
                SET compte_bloque = 0, 
                    date_blocage = NULL, 
                    tentatives_connexion_echouees = 0 
                WHERE email = ?";
        $this->db->query($sql, [$email]);
    }
    
    /**
     * Logger une tentative de connexion échouée
     */
    private function logFailedAttempt($email, $reason) {
        try {
            $sql = "INSERT INTO tentativesconnexion (email, ip_address, user_agent, succes, raison_echec) 
                    VALUES (?, ?, ?, 0, ?)";
            $params = [
                $email,
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                $reason
            ];
            $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Erreur log tentative échouée: " . $e->getMessage());
        }
    }
    
    /**
     * Logger une tentative de connexion réussie
     */
    private function logSuccessfulAttempt($userId) {
        try {
            $user = $this->db->fetch("SELECT email FROM utilisateurs WHERE utilisateur_id = ?", [$userId]);
            
            $sql = "INSERT INTO tentativesconnexion (email, ip_address, user_agent, succes) 
                    VALUES (?, ?, ?, 1)";
            $params = [
                $user['email'],
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];
            $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Erreur log tentative réussie: " . $e->getMessage());
        }
    }
    
    /**
     * Générer un token de récupération de mot de passe
     */
    public function generatePasswordResetToken($email) {
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Aucun compte associé à cette adresse email'
            ];
        }
        
        $token = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $sql = "UPDATE utilisateurs 
                SET token_recuperation_mdp = ?, date_expiration_token = ? 
                WHERE utilisateur_id = ?";
        
        $this->db->query($sql, [$token, $expiration, $user['utilisateur_id']]);
        
        return [
            'success' => true,
            'token' => $token,
            'user' => $user
        ];
    }
    
    /**
     * Vérifier un token de récupération
     */
    public function verifyResetToken($token) {
        $sql = "SELECT utilisateur_id, email, date_expiration_token 
                FROM utilisateurs 
                WHERE token_recuperation_mdp = ? 
                AND date_expiration_token > NOW()";
        
        return $this->db->fetch($sql, [$token]);
    }
    
    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword($token, $newPassword) {
        $user = $this->verifyResetToken($token);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ];
        }
        
        $salt = bin2hex(random_bytes(16));
        $passwordHash = hash('sha256', $newPassword . $salt);
        
        $sql = "UPDATE utilisateurs 
                SET mot_de_passe_hash = ?, 
                    salt = ?,
                    token_recuperation_mdp = NULL,
                    date_expiration_token = NULL,
                    tentatives_connexion_echouees = 0,
                    compte_bloque = 0,
                    date_blocage = NULL
                WHERE utilisateur_id = ?";
        
        $this->db->query($sql, [$passwordHash, $salt, $user['utilisateur_id']]);
        
        return [
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès'
        ];
    }
}

// Configuration des erreurs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('Africa/Abidjan');

// Créer le dossier de logs s'il n'existe pas
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
?>