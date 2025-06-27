<?php
/**
 * Système d'authentification complet
 * Fichier: includes/auth.php
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Classe de gestion de l'authentification
 */
class AuthManager {
    private $db;
    private $maxAttempts = 5;
    private $lockoutTime = 1800; // 30 minutes
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authentification utilisateur avec gestion multi-rôles
     */
    public function authenticate($email, $password, $remember = false) {
        try {
            // Vérification du blocage IP
            if ($this->isIpBlocked()) {
                return ['success' => false, 'message' => 'Adresse IP temporairement bloquée'];
            }
            
            // Récupération des informations utilisateur
            $sql = "SELECT 
                        u.utilisateur_id,
                        u.email,
                        u.mot_de_passe_hash,
                        u.salt,
                        u.statut_id,
                        u.compte_bloque,
                        u.tentatives_connexion_echouees,
                        u.date_blocage,
                        ip.nom,
                        ip.prenoms,
                        ip.telephone,
                        r.nom_role as role_principal,
                        r.niveau_acces,
                        u.photo_profil
                    FROM utilisateurs u
                    INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    INNER JOIN roles r ON u.role_id = r.role_id
                    WHERE u.email = ? AND u.est_actif = 1";
            
            $user = $this->db->fetch($sql, [$email]);
            
            if (!$user) {
                $this->logFailedAttempt($email, 'Utilisateur introuvable');
                return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
            }
            
            // Vérification du compte bloqué
            if ($user['compte_bloque']) {
                // Vérifier si le blocage a expiré
                if ($user['date_blocage'] && 
                    (time() - strtotime($user['date_blocage'])) > $this->lockoutTime) {
                    $this->unblockUser($user['utilisateur_id']);
                } else {
                    $this->logFailedAttempt($email, 'Compte bloqué');
                    return ['success' => false, 'message' => 'Votre compte est bloqué. Contactez l\'administrateur.'];
                }
            }
            
            // Vérification des tentatives échouées
            if ($user['tentatives_connexion_echouees'] >= $this->maxAttempts) {
                $this->blockUser($user['utilisateur_id']);
                return ['success' => false, 'message' => 'Compte bloqué après 5 tentatives échouées.'];
            }
            
            // Vérification du mot de passe
            if (!$this->verifyPassword($password, $user['mot_de_passe_hash'], $user['salt'])) {
                $this->incrementFailedAttempts($user['utilisateur_id']);
                $this->logFailedAttempt($email, 'Mot de passe incorrect');
                return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
            }
            
            // Récupération de tous les rôles de l'utilisateur
            $allRoles = $this->getUserRoles($user['utilisateur_id']);
            $user['tous_roles'] = $allRoles;
            
            // Authentification réussie
            $this->resetFailedAttempts($user['utilisateur_id']);
            $this->updateLastLogin($user['utilisateur_id']);
            $this->logSuccessfulLogin($email);
            
            // Gestion du "Se souvenir de moi"
            if ($remember) {
                $this->setRememberToken($user['utilisateur_id']);
            }
            
            // Préparation des données de session
            SessionManager::setUserData($user);
            
            return [
                'success' => true, 
                'message' => 'Connexion réussie',
                'user' => $user,
                'redirect_url' => $this->getRedirectUrl($user['role_principal'])
            ];
            
        } catch (Exception $e) {
            error_log("Erreur authentification: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la connexion'];
        }
    }
    
    /**
     * Récupération de tous les rôles d'un utilisateur
     */
    private function getUserRoles($userId) {
        $roles = [$this->db->fetch("SELECT nom_role FROM roles r INNER JOIN utilisateurs u ON r.role_id = u.role_id WHERE u.utilisateur_id = ?", [$userId])['nom_role']];
        
        // Vérifier les rôles supplémentaires selon le type d'utilisateur
        $enseignant = $this->db->fetch("SELECT enseignant_id FROM enseignants WHERE utilisateur_id = ?", [$userId]);
        if ($enseignant) {
            $roles[] = ROLE_ENSEIGNANT;
        }
        
        $etudiant = $this->db->fetch("SELECT etudiant_id FROM etudiants WHERE utilisateur_id = ?", [$userId]);
        if ($etudiant) {
            $roles[] = ROLE_ETUDIANT;
        }
        
        $personnel = $this->db->fetch("
            SELECT pa.personnel_id, f.libelle_fonction 
            FROM personnel_administratif pa 
            INNER JOIN fonctions f ON pa.fonction_id = f.fonction_id 
            WHERE pa.utilisateur_id = ?", [$userId]);
        if ($personnel) {
            $roles[] = $personnel['libelle_fonction'];
        }
        
        return array_unique($roles);
    }
    
    /**
     * Vérification du mot de passe avec salt
     */
    private function verifyPassword($password, $hash, $salt) {
        return hash('sha256', $password . $salt) === $hash;
    }
    
    /**
     * Hachage du mot de passe avec salt
     */
    public function hashPassword($password) {
        $salt = bin2hex(random_bytes(32));
        $hash = hash('sha256', $password . $salt);
        return ['hash' => $hash, 'salt' => $salt];
    }
    
    /**
     * Vérification si l'IP est bloquée
     */
    private function isIpBlocked() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $sql = "SELECT COUNT(*) as attempts FROM tentativesconnexion 
                WHERE ip_address = ? AND succes = 0 AND date_tentative > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        $result = $this->db->fetch($sql, [$ip]);
        return $result['attempts'] >= 10;
    }
    
    /**
     * Détermine l'URL de redirection selon le rôle
     */
    private function getRedirectUrl($role) {
        $redirects = [
            ROLE_ADMIN => PAGES_URL . 'admin/',
            ROLE_RESPONSABLE_SCOLARITE => PAGES_URL . 'responsable_scolarite/',
            ROLE_CHARGE_COMMUNICATION => PAGES_URL . 'charge_communication/',
            ROLE_COMMISSION => PAGES_URL . 'commission/',
            ROLE_SECRETAIRE => PAGES_URL . 'secretaire/',
            ROLE_ENSEIGNANT => PAGES_URL . 'enseignant/',
            ROLE_ETUDIANT => PAGES_URL . 'etudiant/',
            ROLE_PERSONNEL_ADMIN => PAGES_URL . 'personnel/'
        ];
        
        return $redirects[$role] ?? PAGES_URL . 'dashboard/';
    }
    
    /**
     * Gestion des tentatives échouées
     */
    private function incrementFailedAttempts($userId) {
        $sql = "UPDATE utilisateurs 
                SET tentatives_connexion_echouees = tentatives_connexion_echouees + 1 
                WHERE utilisateur_id = ?";
        $this->db->query($sql, [$userId]);
    }
    
    private function resetFailedAttempts($userId) {
        $sql = "UPDATE utilisateurs 
                SET tentatives_connexion_echouees = 0 
                WHERE utilisateur_id = ?";
        $this->db->query($sql, [$userId]);
    }
    
    private function blockUser($userId) {
        $sql = "UPDATE utilisateurs 
                SET compte_bloque = 1, date_blocage = NOW() 
                WHERE utilisateur_id = ?";
        $this->db->query($sql, [$userId]);
    }
    
    private function unblockUser($userId) {
        $sql = "UPDATE utilisateurs 
                SET compte_bloque = 0, date_blocage = NULL, tentatives_connexion_echouees = 0 
                WHERE utilisateur_id = ?";
        $this->db->query($sql, [$userId]);
    }
    
    /**
     * Mise à jour de la dernière connexion
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE utilisateurs 
                SET derniere_connexion = NOW() 
                WHERE utilisateur_id = ?";
        $this->db->query($sql, [$userId]);
    }
    
    /**
     * Gestion du token "Se souvenir de moi"
     */
    private function setRememberToken($userId) {
        $token = generateToken(64);
        $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 3600)); // 30 jours
        
        // Stocker le token en base
        $sql = "UPDATE utilisateurs SET token_recuperation_mdp = ?, date_expiration_token = ? WHERE utilisateur_id = ?";
        $this->db->query($sql, [$token, $expiry, $userId]);
        
        // Créer le cookie
        setcookie('remember_token', $token, time() + (30 * 24 * 3600), '/', '', true, true);
    }
    
    /**
     * Vérification du token de souvenir
     */
    public function checkRememberToken() {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        $token = $_COOKIE['remember_token'];
        $sql = "SELECT utilisateur_id FROM utilisateurs 
                WHERE token_recuperation_mdp = ? 
                AND date_expiration_token > NOW() 
                AND est_actif = 1";
        
        $user = $this->db->fetch($sql, [$token]);
        
        if ($user) {
            // Reconnecter automatiquement l'utilisateur
            $this->autoLogin($user['utilisateur_id']);
            return true;
        }
        
        // Token invalide, supprimer le cookie
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }
    
    /**
     * Connexion automatique
     */
    private function autoLogin($userId) {
        $sql = "SELECT 
                    u.utilisateur_id,
                    u.email,
                    ip.nom,
                    ip.prenoms,
                    r.nom_role as role_principal
                FROM utilisateurs u
                INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                INNER JOIN roles r ON u.role_id = r.role_id
                WHERE u.utilisateur_id = ?";
        
        $user = $this->db->fetch($sql, [$userId]);
        if ($user) {
            $user['tous_roles'] = $this->getUserRoles($userId);
            SessionManager::setUserData($user);
        }
    }
    
    /**
     * Logging des tentatives de connexion
     */
    private function logFailedAttempt($email, $reason) {
        $sql = "INSERT INTO tentativesconnexion (email, ip_address, user_agent, succes, raison_echec) 
                VALUES (?, ?, ?, 0, ?)";
        $this->db->query($sql, [
            $email,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $reason
        ]);
    }
    
    private function logSuccessfulLogin($email) {
        $sql = "INSERT INTO tentativesconnexion (email, ip_address, user_agent, succes) 
                VALUES (?, ?, ?, 1)";
        $this->db->query($sql, [
            $email,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }
    
    /**
     * Réinitialisation du mot de passe
     */
    public function requestPasswordReset($email) {
        $user = $this->db->fetch("SELECT utilisateur_id FROM utilisateurs WHERE email = ? AND est_actif = 1", [$email]);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email non trouvé'];
        }
        
        $token = generateToken(32);
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 heure
        
        $sql = "UPDATE utilisateurs SET token_recuperation_mdp = ?, date_expiration_token = ? WHERE utilisateur_id = ?";
        $this->db->query($sql, [$token, $expiry, $user['utilisateur_id']]);
        
        // Ici, vous pourriez envoyer un email avec le token
        // Pour le moment, on retourne le token (à des fins de test)
        
        return ['success' => true, 'message' => 'Email de réinitialisation envoyé', 'token' => $token];
    }
    
    /**
     * Réinitialisation effective du mot de passe
     */
    public function resetPassword($token, $newPassword) {
        $sql = "SELECT utilisateur_id FROM utilisateurs 
                WHERE token_recuperation_mdp = ? 
                AND date_expiration_token > NOW()";
        
        $user = $this->db->fetch($sql, [$token]);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Token invalide ou expiré'];
        }
        
        $passwordData = $this->hashPassword($newPassword);
        
        $sql = "UPDATE utilisateurs 
                SET mot_de_passe_hash = ?, salt = ?, token_recuperation_mdp = NULL, date_expiration_token = NULL 
                WHERE utilisateur_id = ?";
        
        $this->db->query($sql, [$passwordData['hash'], $passwordData['salt'], $user['utilisateur_id']]);
        
        return ['success' => true, 'message' => 'Mot de passe réinitialisé avec succès'];
    }
    
    /**
     * Vérification des permissions
     */
    public static function checkPermission($requiredRole) {
        if (!SessionManager::isLoggedIn()) {
            redirectTo(BASE_URL . 'login.php');
        }
        
        $userRoles = SessionManager::getUserRoles();
        
        // L'administrateur a accès à tout
        if (in_array(ROLE_ADMIN, $userRoles)) {
            return true;
        }
        
        // Vérification du rôle spécifique
        if (!in_array($requiredRole, $userRoles)) {
            redirectTo(BASE_URL . 'pages/access_denied.php');
        }
        
        return true;
    }
    
    /**
     * Déconnexion
     */
    public static function logout() {
        // Supprimer le cookie de souvenir s'il existe
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        SessionManager::destroy();
        redirectTo(BASE_URL . 'login.php');
    }
    
    /**
     * Changement de mot de passe
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $sql = "SELECT mot_de_passe_hash, salt FROM utilisateurs WHERE utilisateur_id = ?";
        $user = $this->db->fetch($sql, [$userId]);
        
        if (!$user || !$this->verifyPassword($currentPassword, $user['mot_de_passe_hash'], $user['salt'])) {
            return ['success' => false, 'message' => 'Mot de passe actuel incorrect'];
        }
        
        $passwordData = $this->hashPassword($newPassword);
        
        $sql = "UPDATE utilisateurs SET mot_de_passe_hash = ?, salt = ? WHERE utilisateur_id = ?";
        $this->db->query($sql, [$passwordData['hash'], $passwordData['salt'], $userId]);
        
        // Log de l'action
        $this->logUserAction($userId, 'PASSWORD_CHANGE', 'Changement de mot de passe');
        
        return ['success' => true, 'message' => 'Mot de passe modifié avec succès'];
    }
    
    /**
     * Log des actions utilisateur
     */
    private function logUserAction($userId, $action, $description) {
        try {
            $sql = "INSERT INTO logs_audit (utilisateur_id, type_action, table_cible, commentaire, adresse_ip, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $params = [
                $userId,
                $action,
                'utilisateurs',
                $description,
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];
            $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Erreur log action: " . $e->getMessage());
        }
    }
}

/**
 * Middleware de vérification d'authentification
 */
function requireAuth($requiredRole = null) {
    // Vérifier le token de souvenir si pas connecté
    if (!SessionManager::isLoggedIn()) {
        $authManager = new AuthManager();
        if (!$authManager->checkRememberToken()) {
            redirectTo(BASE_URL . 'login.php');
        }
    }
    
    if ($requiredRole) {
        AuthManager::checkPermission($requiredRole);
    }
}

/**
 * Fonction helper pour vérifier les rôles multiples
 */
function hasAnyRole($roles) {
    if (!SessionManager::isLoggedIn()) {
        return false;
    }
    
    $userRoles = SessionManager::getUserRoles();
    foreach ($roles as $role) {
        if (in_array($role, $userRoles)) {
            return true;
        }
    }
    return false;
}

/**
 * Fonction helper pour vérifier un rôle spécifique
 */
function hasRole($role) {
    if (!SessionManager::isLoggedIn()) {
        return false;
    }
    
    return in_array($role, SessionManager::getUserRoles());
}

/**
 * Fonction pour obtenir les permissions d'un rôle
 */
function getRolePermissions($role) {
    $permissions = [
        ROLE_ADMIN => [
            'users_create', 'users_read', 'users_update', 'users_delete',
            'students_create', 'students_read', 'students_update', 'students_delete',
            'teachers_create', 'teachers_read', 'teachers_update', 'teachers_delete',
            'staff_create', 'staff_read', 'staff_update', 'staff_delete',
            'reports_read', 'reports_update', 'reports_delete',
            'system_config', 'audit_logs', 'all_access'
        ],
        ROLE_RESPONSABLE_SCOLARITE => [
            'students_read', 'students_update',
            'grades_create', 'grades_read', 'grades_update',
            'reports_read', 'reports_update',
            'eligibility_check', 'statistics_view'
        ],
        ROLE_CHARGE_COMMUNICATION => [
            'reports_read', 'reports_update',
            'communications_send', 'notifications_manage'
        ],
        ROLE_COMMISSION => [
            'reports_read', 'reports_evaluate',
            'jury_manage', 'defenses_schedule'
        ],
        ROLE_SECRETAIRE => [
            'students_read', 'defenses_read',
            'schedules_read', 'exports_generate'
        ],
        ROLE_ENSEIGNANT => [
            'students_read', 'grades_create', 'grades_read',
            'reports_read', 'supervise_students'
        ],
        ROLE_ETUDIANT => [
            'profile_read', 'profile_update',
            'reports_create', 'reports_read', 'reports_update',
            'complaints_create', 'complaints_read'
        ]
    ];
    
    return $permissions[$role] ?? [];
}

/**
 * Vérification d'une permission spécifique
 */
function hasPermission($permission) {
    if (!SessionManager::isLoggedIn()) {
        return false;
    }
    
    $userRoles = SessionManager::getUserRoles();
    
    foreach ($userRoles as $role) {
        $rolePermissions = getRolePermissions($role);
        if (in_array($permission, $rolePermissions) || in_array('all_access', $rolePermissions)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Fonction pour générer un token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken(32);
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérification du token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Classe pour la gestion de la sécurité
 */
class SecurityManager {
    
    /**
     * Validation de la force du mot de passe
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre";
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial";
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
    
    /**
     * Nettoyage des données d'entrée
     */
    public static function sanitizeData($data, $type = 'string') {
        switch ($type) {
            case 'email':
                return filter_var(trim($data), FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var(trim($data), FILTER_SANITIZE_URL);
            case 'html':
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
            default:
                return sanitizeInput($data);
        }
    }
    
    /**
     * Validation de l'upload de fichier
     */
    public static function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
        $allowedTypes = $allowedTypes ?? ALLOWED_FILE_TYPES;
        $maxSize = $maxSize ?? MAX_FILE_SIZE;
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Erreur lors de l\'upload du fichier'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Le fichier est trop volumineux'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            return ['valid' => false, 'error' => 'Type de fichier non autorisé'];
        }
        
        // Vérification MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        ];
        
        if (!isset($allowedMimes[$extension]) || $mimeType !== $allowedMimes[$extension]) {
            return ['valid' => false, 'error' => 'Type MIME non autorisé'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Protection contre les attaques par déni de service
     */
    public static function rateLimitCheck($identifier, $maxRequests = 60, $timeWindow = 3600) {
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($identifier);
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            
            if ($data['timestamp'] > time() - $timeWindow) {
                if ($data['requests'] >= $maxRequests) {
                    return false;
                }
                $data['requests']++;
            } else {
                $data = ['timestamp' => time(), 'requests' => 1];
            }
        } else {
            $data = ['timestamp' => time(), 'requests' => 1];
        }
        
        file_put_contents($cacheFile, json_encode($data));
        return true;
    }
}

// Démarrage automatique de la session si nécessaire
SessionManager::start();

// Vérification automatique du token de souvenir
if (!SessionManager::isLoggedIn() && isset($_COOKIE['remember_token'])) {
    $authManager = new AuthManager();
    $authManager->checkRememberToken();
}
?>