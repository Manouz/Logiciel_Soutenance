<?php
require_once 'config/database.php';

/**
 * Classe de gestion de l'authentification
 */
class AuthManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authentification utilisateur avec gestion multi-rôles
     */
    public function authenticate($email, $password) {
        try {
            // Récupération des informations utilisateur avec tous ses rôles
            $sql = "SELECT 
                        u.utilisateur_id,
                        u.email,
                        u.mot_de_passe_hash,
                        u.salt,
                        u.statut_id,
                        u.compte_bloque,
                        u.tentatives_connexion_echouees,
                        ip.nom,
                        ip.prenoms,
                        ip.telephone,
                        r.nom_role as role_principal,
                        r.niveau_acces,
                        GROUP_CONCAT(DISTINCT r2.nom_role) as tous_roles
                    FROM utilisateurs u
                    INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    INNER JOIN roles r ON u.role_id = r.role_id
                    LEFT JOIN enseignants e ON u.utilisateur_id = e.utilisateur_id
                    LEFT JOIN personnel_administratif pa ON u.utilisateur_id = pa.utilisateur_id
                    LEFT JOIN etudiants et ON u.utilisateur_id = et.utilisateur_id
                    LEFT JOIN roles r2 ON (
                        (e.enseignant_id IS NOT NULL AND r2.nom_role = 'Enseignant') OR
                        (pa.personnel_id IS NOT NULL AND EXISTS(SELECT 1 FROM fonctions f WHERE pa.fonction_id = f.fonction_id AND f.code_fonction IN ('CHARGE_COM', 'COMMISSION', 'SECRETAIRE') AND r2.nom_role = f.libelle_fonction)) OR
                        (et.etudiant_id IS NOT NULL AND r2.nom_role = 'Étudiant') OR
                        r2.role_id = u.role_id
                    )
                    WHERE u.email = ? AND u.est_actif = 1
                    GROUP BY u.utilisateur_id";
            
            $user = $this->db->fetch($sql, [$email]);
            
            if (!$user) {
                $this->logFailedAttempt($email, 'Utilisateur introuvable');
                return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
            }
            
            // Vérification du compte bloqué
            if ($user['compte_bloque']) {
                $this->logFailedAttempt($email, 'Compte bloqué');
                return ['success' => false, 'message' => 'Votre compte est bloqué. Contactez l\'administrateur.'];
            }
            
            // Vérification des tentatives échouées
            if ($user['tentatives_connexion_echouees'] >= 5) {
                $this->blockUser($user['utilisateur_id']);
                return ['success' => false, 'message' => 'Compte bloqué après 5 tentatives échouées.'];
            }
            
            // Vérification du mot de passe
            if (!$this->verifyPassword($password, $user['mot_de_passe_hash'], $user['salt'])) {
                $this->incrementFailedAttempts($user['utilisateur_id']);
                $this->logFailedAttempt($email, 'Mot de passe incorrect');
                return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
            }
            
            // Authentification réussie
            $this->resetFailedAttempts($user['utilisateur_id']);
            $this->updateLastLogin($user['utilisateur_id']);
            $this->logSuccessfulLogin($email);
            
            // Préparation des données de session
            $user['tous_roles'] = explode(',', $user['tous_roles']);
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
     * Détermine l'URL de redirection selon le rôle
     */
    private function getRedirectUrl($role) {
        $redirects = [
            'Administrateur' => BASE_URL . 'pages/admin/',
            'Responsable Scolarité' => BASE_URL . 'pages/responsable_scolarite/',
            'Chargé Communication' => BASE_URL . 'pages/charge_communication/',
            'Commission' => BASE_URL . 'pages/commission/',
            'Secrétaire' => BASE_URL . 'pages/secretaire/',
            'Enseignant' => BASE_URL . 'pages/enseignant/',
            'Étudiant' => BASE_URL . 'pages/etudiant/'
        ];
        
        return $redirects[$role] ?? BASE_URL . 'pages/dashboard/';
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
     * Vérification des permissions
     */
    public static function checkPermission($requiredRole) {
        if (!SessionManager::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
        
        $userRoles = SessionManager::getUserRoles();
        
        // L'administrateur a accès à tout
        if (in_array('Administrateur', $userRoles)) {
            return true;
        }
        
        // Vérification du rôle spécifique
        if (!in_array($requiredRole, $userRoles)) {
            header('Location: ' . BASE_URL . 'pages/access_denied.php');
            exit;
        }
        
        return true;
    }
    
    /**
     * Déconnexion
     */
    public static function logout() {
        SessionManager::destroy();
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

/**
 * Middleware de vérification d'authentification
 */
function requireAuth($requiredRole = null) {
    if (!SessionManager::isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
    
    if ($requiredRole) {
        AuthManager::checkPermission($requiredRole);
    }
}

/**
 * Fonction helper pour vérifier les rôles multiples
 */
function hasAnyRole($roles) {
    $userRoles = SessionManager::getUserRoles();
    foreach ($roles as $role) {
        if (in_array($role, $userRoles)) {
            return true;
        }
    }
    return false;
}
?>