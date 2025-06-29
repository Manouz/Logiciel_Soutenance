<?php
/**
 * Page de connexion - Authentification utilisateur
 * Système de Validation Académique - Université Félix Houphouët-Boigny
 */

session_start();

// Inclure les fichiers de configuration
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: authenticate.php');
    exit();
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération et validation des données
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password'] ?? '');
    $remember_me = isset($_POST['remember_me']);
    
    // Validation basique
    if (!$email || empty($password)) {
        $error = 'Veuillez remplir tous les champs requis.';
    } else {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            // Vérification de l'utilisateur
            $sql = "SELECT 
                        u.utilisateur_id,
                        u.code_utilisateur,
                        u.email,
                        u.mot_de_passe_hash,
                        u.salt,
                        u.role_id,
                        u.statut_id,
                        u.tentatives_connexion_echouees,
                        u.compte_bloque,
                        u.date_blocage,
                        u.est_actif,
                        r.nom_role,
                        r.niveau_acces,
                        s.libelle_statut,
                        ip.nom,
                        ip.prenoms
                    FROM utilisateurs u
                    INNER JOIN roles r ON u.role_id = r.role_id
                    INNER JOIN statuts s ON u.statut_id = s.statut_id
                    LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    WHERE u.email = ? AND u.est_actif = 1";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // Utilisateur non trouvé - log de tentative échouée
                logLoginAttempt($email, false, 'Utilisateur non trouvé');
                $error = 'Email ou mot de passe incorrect.';
            } else {
                // Vérifier si le compte est bloqué
                if ($user['compte_bloque']) {
                    logLoginAttempt($email, false, 'Compte bloqué');
                    $error = 'Votre compte a été temporairement bloqué. Contactez l\'administration.';
                } 
                // Vérifier si le compte est actif
                elseif (!$user['est_actif']) {
                    logLoginAttempt($email, false, 'Compte inactif');
                    $error = 'Votre compte est inactif. Contactez l\'administration.';
                }
                // Vérifier le mot de passe
                elseif (!password_verify($password . $user['salt'], $user['mot_de_passe_hash'])) {
                    // Incrémenter les tentatives échouées
                    incrementFailedAttempts($user['utilisateur_id']);
                    logLoginAttempt($email, false, 'Mot de passe incorrect');
                    $error = 'Email ou mot de passe incorrect.';
                } else {
                    // Connexion réussie
                    
                    // Réinitialiser les tentatives échouées
                    resetFailedAttempts($user['utilisateur_id']);
                    
                    // Créer la session utilisateur
                    createUserSession($user, $remember_me);
                    
                    // Mettre à jour la dernière connexion
                    updateLastLogin($user['utilisateur_id']);
                    
                    // Log de connexion réussie
                    logLoginAttempt($email, true, 'Connexion réussie');
                    
                    // Redirection vers authenticate.php pour la redirection par rôle
                    header('Location: authenticate.php');
                    exit();
                }
            }
            
        } catch (Exception $e) {
            error_log("Erreur lors de la connexion: " . $e->getMessage());
            $error = 'Une erreur technique est survenue. Veuillez réessayer.';
        }
    }
    
    // En cas d'erreur, rediriger vers index.php avec le message
    if (isset($error)) {
        $errorParam = urlencode($error === 'Email ou mot de passe incorrect.' ? 'invalid_credentials' : 'login_error');
        header("Location: index.php?error={$errorParam}&email=" . urlencode($email));
        exit();
    }
}

/**
 * Fonctions utilitaires pour l'authentification
 */

/**
 * Créer une session utilisateur sécurisée
 */
function createUserSession($user, $remember_me = false) {
    // Régénérer l'ID de session pour la sécurité
    session_regenerate_id(true);
    
    // Stocker les informations utilisateur en session
    $_SESSION['user_id'] = $user['utilisateur_id'];
    $_SESSION['user_code'] = $user['code_utilisateur'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['nom_role'];
    $_SESSION['user_role_id'] = $user['role_id'];
    $_SESSION['user_level'] = $user['niveau_acces'];
    $_SESSION['user_name'] = trim(($user['prenoms'] ?? '') . ' ' . ($user['nom'] ?? '')) ?: $user['email'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Générer un token de session sécurisé
    $session_token = bin2hex(random_bytes(32));
    $_SESSION['session_token'] = $session_token;
    
    // Enregistrer la session en base de données
    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        $expiration = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
        
        $sql = "INSERT INTO sessions_utilisateurs 
                (utilisateur_id, token_session, date_expiration, adresse_ip, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user['utilisateur_id'],
            $session_token,
            $expiration,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
        
    } catch (Exception $e) {
        error_log("Erreur lors de l'enregistrement de la session: " . $e->getMessage());
    }
    
    // Cookie de session étendue si demandé
    if ($remember_me) {
        $cookie_value = base64_encode(json_encode([
            'user_id' => $user['utilisateur_id'],
            'token' => $session_token,
            'expires' => time() + (30 * 24 * 60 * 60) // 30 jours
        ]));
        
        setcookie('remember_token', $cookie_value, time() + (30 * 24 * 60 * 60), '/', '', true, true);
    }
}

/**
 * Incrémenter les tentatives de connexion échouées
 */
function incrementFailedAttempts($userId) {
    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        $sql = "UPDATE utilisateurs 
                SET tentatives_connexion_echouees = tentatives_connexion_echouees + 1,
                    compte_bloque = CASE 
                        WHEN tentatives_connexion_echouees >= ? THEN 1 
                        ELSE 0 
                    END,
                    date_blocage = CASE 
                        WHEN tentatives_connexion_echouees >= ? THEN NOW() 
                        ELSE date_blocage 
                    END
                WHERE utilisateur_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([MAX_LOGIN_ATTEMPTS - 1, MAX_LOGIN_ATTEMPTS - 1, $userId]);
        
    } catch (Exception $e) {
        error_log("Erreur lors de l'incrémentation des tentatives: " . $e->getMessage());
    }
}

/**
 * Réinitialiser les tentatives de connexion échouées
 */
function resetFailedAttempts($userId) {
    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        $sql = "UPDATE utilisateurs 
                SET tentatives_connexion_echouees = 0,
                    compte_bloque = 0,
                    date_blocage = NULL
                WHERE utilisateur_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        
    } catch (Exception $e) {
        error_log("Erreur lors de la réinitialisation des tentatives: " . $e->getMessage());
    }
}

/**
 * Mettre à jour la dernière connexion
 */
function updateLastLogin($userId) {
    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        $sql = "UPDATE utilisateurs 
                SET derniere_connexion = NOW() 
                WHERE utilisateur_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        
    } catch (Exception $e) {
        error_log("Erreur lors de la mise à jour de la dernière connexion: " . $e->getMessage());
    }
}

/**
 * Logger les tentatives de connexion
 */
function logLoginAttempt($email, $success, $reason = '') {
    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        $sql = "INSERT INTO tentativesconnexion 
                (email, ip_address, user_agent, succes, raison_echec) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $email,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $success ? 1 : 0,
            $success ? null : $reason
        ]);
        
    } catch (Exception $e) {
        error_log("Erreur lors du logging de la tentative de connexion: " . $e->getMessage());
    }
}

// Si on arrive ici directement (GET), rediriger vers l'accueil
header('Location: index.php');
exit();
?>