<?php
/**
 * Script d'authentification pour le système de validation académique
 * Université Félix Houphouët-Boigny de Cocody
 */

session_start();

// Configuration et includes
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';
require_once 'classes/User.php';
require_once 'classes/Logger.php';

// Variables globales
$error = '';
$redirect_url = '';

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    redirectToUserDashboard($_SESSION['user_role']);
    exit();
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération et validation des données
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validation des champs requis
        if (empty($email) || empty($password)) {
            throw new Exception('Veuillez remplir tous les champs.');
        }
        
        // Validation de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Adresse email invalide.');
        }
        
        // Vérification des tentatives de connexion
        if (isBlocked($email)) {
            throw new Exception('Trop de tentatives de connexion. Réessayez dans 15 minutes.');
        }
        
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();
        
        // Requête pour récupérer l'utilisateur
        $sql = "SELECT 
                    u.utilisateur_id,
                    u.code_utilisateur,
                    u.email,
                    u.role_id,
                    u.statut_id,
                    u.mot_de_passe_hash,
                    u.salt,
                    u.tentatives_connexion_echouees,
                    u.compte_bloque,
                    u.est_actif,
                    r.nom_role,
                    r.niveau_acces,
                    s.libelle_statut,
                    ip.nom,
                    ip.prenoms,
                    ip.photo_profil
                FROM utilisateurs u
                INNER JOIN roles r ON u.role_id = r.role_id
                INNER JOIN statuts s ON u.statut_id = s.statut_id
                LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                WHERE u.email = ? AND u.est_actif = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Enregistrer la tentative échouée
            logFailedAttempt($email, 'Utilisateur inexistant');
            throw new Exception('Email ou mot de passe incorrect.');
        }
        
        // Vérifier si le compte est bloqué
        if ($user['compte_bloque']) {
            logFailedAttempt($email, 'Compte bloqué');
            throw new Exception('Votre compte est bloqué. Contactez l\'administrateur.');
        }
        
        // Vérifier le statut du compte
        if ($user['statut_id'] != 1) { // 1 = Actif
            logFailedAttempt($email, 'Compte inactif');
            throw new Exception('Votre compte est inactif.');
        }
        
        // Vérifier le mot de passe
        $password_hash = hash('sha256', $password . $user['salt']);
        
        if (!hash_equals($user['mot_de_passe_hash'], $password_hash)) {
            // Incrémenter le compteur de tentatives échouées
            incrementFailedAttempts($user['utilisateur_id']);
            logFailedAttempt($email, 'Mot de passe incorrect');
            throw new Exception('Email ou mot de passe incorrect.');
        }
        
        // Connexion réussie - Réinitialiser les tentatives échouées
        resetFailedAttempts($user['utilisateur_id']);
        
        // Mettre à jour la dernière connexion
        updateLastLogin($user['utilisateur_id']);
        
        // Créer la session utilisateur
        createUserSession($user, $remember);
        
        // Log de connexion réussie
        Logger::log('LOGIN_SUCCESS', 'utilisateurs', $user['utilisateur_id'], 
                   null, json_encode(['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]));
        
        // Redirection vers le dashboard approprié
        $redirect_url = getUserDashboardUrl($user['nom_role']);
        header('Location: ' . $redirect_url);
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        // Log de l'erreur
        error_log("Erreur de connexion: " . $error . " - IP: " . $_SERVER['REMOTE_ADDR']);
        
        // Redirection avec message d'erreur
        $error_code = getErrorCode($error);
        header('Location: login.php?error=' . $error_code);
        exit();
    }
}

/**
 * Vérifier si une IP est bloquée temporairement
 */
function isBlocked($email) {
    $pdo = Database::getInstance()->getConnection();
    
    $sql = "SELECT COUNT(*) as attempts 
            FROM tentativesconnexion 
            WHERE email = ? 
            AND succes = 0 
            AND date_tentative > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $result = $stmt->fetch();
    
    return $result['attempts'] >= 5;
}

/**
 * Enregistrer une tentative de connexion échouée
 */
function logFailedAttempt($email, $reason) {
    $pdo = Database::getInstance()->getConnection();
    
    $sql = "INSERT INTO tentativesconnexion 
            (email, ip_address, user_agent, succes, raison_echec) 
            VALUES (?, ?, ?, 0, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $email,
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        $reason
    ]);
}

/**
 * Incrémenter le compteur de tentatives échouées
 */
function incrementFailedAttempts($user_id) {
    $pdo = Database::getInstance()->getConnection();
    
    $sql = "UPDATE utilisateurs 
            SET tentatives_connexion_echouees = tentatives_connexion_echouees + 1,
                compte_bloque = CASE 
                    WHEN tentatives_connexion_echouees >= 4 THEN 1 
                    ELSE 0 
                END,
                date_blocage = CASE 
                    WHEN tentatives_connexion_echouees >= 4 THEN NOW() 
                    ELSE date_blocage 
                END
            WHERE utilisateur_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
}

/**
 * Réinitialiser les tentatives échouées
 */
function resetFailedAttempts($user_id) {
    $pdo = Database::getInstance()->getConnection();
    
    $sql = "UPDATE utilisateurs 
            SET tentatives_connexion_echouees = 0,
                compte_bloque = 0,
                date_blocage = NULL
            WHERE utilisateur_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
}

/**
 * Mettre à jour la dernière connexion
 */
function updateLastLogin($user_id) {
    $pdo = Database::getInstance()->getConnection();
    
    $sql = "UPDATE utilisateurs 
            SET derniere_connexion = NOW() 
            WHERE utilisateur_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
}

/**
 * Créer la session utilisateur
 */
function createUserSession($user, $remember = false) {
    // Régénérer l'ID de session pour sécurité
    session_regenerate_id(true);
    
    // Variables de session principales
    $_SESSION['user_id'] = $user['utilisateur_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['nom_role'];
    $_SESSION['user_role_id'] = $user['role_id'];
    $_SESSION['user_name'] = trim($user['prenoms'] . ' ' . $user['nom']);
    $_SESSION['user_code'] = $user['code_utilisateur'];
    $_SESSION['niveau_acces'] = $user['niveau_acces'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Token de session sécurisé
    $session_token = bin2hex(random_bytes(32));
    $_SESSION['session_token'] = $session_token;
    
    // Enregistrer la session en base
    $pdo = Database::getInstance()->getConnection();
    
    $sql = "INSERT INTO sessions_utilisateurs 
            (utilisateur_id, token_session, date_expiration, adresse_ip, user_agent) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $user['utilisateur_id'],
        $session_token,
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);
    
    // Cookie "Se souvenir de moi"
    if ($remember) {
        $remember_token = bin2hex(random_bytes(32));
        setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
        
        // Stocker le token en base (vous pouvez ajouter une table remember_tokens)
        $_SESSION['remember_token'] = $remember_token;
    }
}

/**
 * Obtenir l'URL du dashboard selon le rôle
 */
function getUserDashboardUrl($role) {
    $dashboards = [
        'Administrateur' => 'pages/admin/index.php',
        'Responsable Scolarité' => 'pages/responsable_scolarite/index.php',
        'Chargé Communication' => 'pages/charge_communication/index.php',
        'Commission' => 'pages/commission/index.php',
        'Secrétaire' => 'pages/secretaire/index.php',
        'Enseignant' => 'pages/enseignant/index.php',
        'Étudiant' => 'pages/etudiant/index.php',
        'Personnel Administratif' => 'pages/personnel/index.php'
    ];
    
    return $dashboards[$role] ?? 'pages/default/index.php';
}

/**
 * Rediriger vers le dashboard de l'utilisateur connecté
 */
function redirectToUserDashboard($role) {
    $url = getUserDashboardUrl($role);
    header('Location: ' . $url);
    exit();
}

/**
 * Obtenir le code d'erreur pour l'URL
 */
function getErrorCode($error) {
    $error_codes = [
        'Email ou mot de passe incorrect.' => 'invalid_credentials',
        'Votre compte est bloqué. Contactez l\'administrateur.' => 'account_blocked',
        'Votre compte est inactif.' => 'account_inactive',
        'Trop de tentatives de connexion. Réessayez dans 15 minutes.' => 'too_many_attempts'
    ];
    
    return $error_codes[$error] ?? 'unknown_error';
}

// Si aucune action POST, rediriger vers la page de login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}
?>