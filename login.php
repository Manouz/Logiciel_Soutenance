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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MaSoutenance</title>
    <style>
        :root {
            --primary-color: rgb(0, 51, 41);
            --primary-light: rgba(0, 51, 41, 0.1);
            --primary-dark: rgb(0, 35, 28);
            --secondary-color: #10b981;
            --accent-color: #34d399;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        /* Background Animation */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(16, 185, 129, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(52, 211, 153, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: backgroundFloat 20s ease-in-out infinite;
        }

        @keyframes backgroundFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }

        /* Back to Home Button */
        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--white);
            text-decoration: none;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            z-index: 10;
        }

        .back-home:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .back-home svg {
            transition: transform 0.3s ease;
        }

        .back-home:hover svg {
            transform: translateX(-3px);
        }

        /* Login Container */
        .login-container {
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
            z-index: 2;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Left Panel - Branding */
        .login-branding {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .login-branding::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="25" cy="75" r="1" fill="white" opacity="0.05"/><circle cx="75" cy="25" r="1" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 30s linear infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-10px, -10px) rotate(360deg); }
        }

        .brand-logo {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 2;
            position: relative;
        }

        .brand-logo::before {
            content: "🎓";
            font-size: 3rem;
        }

        .brand-tagline {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            z-index: 2;
            position: relative;
        }

        .brand-features {
            list-style: none;
            text-align: left;
            z-index: 2;
            position: relative;
        }

        .brand-features li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 500;
            opacity: 0.9;
        }

        .brand-features li::before {
            content: "✓";
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }

        /* Right Panel - Form */
        .login-form-panel {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h1 {
            font-size: 2rem;
            color: var(--gray-900);
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--gray-600);
            font-size: 1rem;
        }

        /* Form Styles */
        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: var(--gray-50);
            position: relative;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--white);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-input::placeholder {
            color: var(--gray-400);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            transition: var(--transition);
            pointer-events: none;
        }

        .form-group:focus-within .input-icon {
            color: var(--primary-color);
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--primary-color);
            background: var(--primary-light);
        }

        /* Form Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
            cursor: pointer;
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Loading State */
        .submit-btn.loading {
            color: transparent;
        }

        .submit-btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Sign Up Link */
        .signup-link {
            text-align: center;
            color: var(--gray-600);
            font-size: 0.95rem;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .signup-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Error States */
        .form-input.error {
            border-color: var(--error-color);
            background: rgba(239, 68, 68, 0.05);
        }

        .form-input.error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .error-message {
            color: var(--error-color);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Success States */
        .form-input.success {
            border-color: var(--success-color);
            background: rgba(16, 185, 129, 0.05);
        }

        /* Social Login */
        .social-login {
            margin: 1.5rem 0;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--gray-200);
            z-index: 1;
        }

        .divider span {
            background: var(--white);
            padding: 0 1rem;
            position: relative;
            z-index: 2;
        }

        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            background: var(--white);
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .social-btn:hover {
            border-color: var(--gray-300);
            background: var(--gray-50);
            transform: translateY(-1px);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
            
            .login-branding {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .back-home {
                top: 1rem;
                left: 1rem;
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }
            
            .login-form-panel {
                padding: 2rem 1.5rem;
            }
            
            .form-header h1 {
                font-size: 1.75rem;
            }
            
            .social-buttons {
                grid-template-columns: 1fr;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        .form-input:focus,
        .submit-btn:focus,
        .social-btn:focus,
        .back-home:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(100%);
            transition: var(--transition);
            max-width: 350px;
            box-shadow: var(--shadow-lg);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: var(--success-color);
        }

        .notification.error {
            background: var(--error-color);
        }

        .notification.info {
            background: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Back to Home Button -->
    <a href="index.php" class="back-home">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m12 19-7-7 7-7"/>
            <path d="M19 12H5"/>
        </svg>
        Retour à l'accueil
    </a>

    <!-- Login Container -->
    <div class="login-container">
        <!-- Left Panel - Branding -->
        <div class="login-branding">
            <div class="brand-logo">MaSoutenance</div>
            <p class="brand-tagline">Simplifiez la gestion de vos soutenances académiques</p>
            
            <ul class="brand-features">
                <li>Planification automatisée</li>
                <li>Gestion des jurys</li>
                <li>Suivi en temps réel</li>
                <li>Rapports détaillés</li>
            </ul>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="login-form-panel">
            <div class="form-header">
                <h1>Bienvenue !</h1>
                <p>Connectez-vous à votre compte pour continuer</p>
            </div>

            <form class="login-form" id="loginForm">
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <div style="position: relative;">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input"
                            placeholder="votre@email.com"
                            required
                            autocomplete="email"
                        >
                    </div>
                    <div class="error-message" id="emailError" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <span></span>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div style="position: relative;">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <circle cx="12" cy="16" r="1"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="error-message" id="passwordError" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <span></span>
                    </div>
                </div>

                <!-- Form Options -->
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <span>Se souvenir de moi</span>
                    </label>
                    <a href="#forgot" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn" id="submitBtn">
                    Se connecter
                </button>

                <!-- Divider -->
                <!--div-- class="divider">
                    <span>ou continuer avec</span>
                </!--div-->

                <!-- Social Login -->
                <!--div-- class="social-buttons">
                    <a href="#google" class="social-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Google
                    </a>
                    <a href="#microsoft" class="social-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#f25022" d="M1 1h10v10H1z"/>
                            <path fill="#00a4ef" d="M13 1h10v10H13z"/>
                            <path fill="#7fba00" d="M1 13h10v10H1z"/>
                            <path fill="#ffb900" d="M13 13h10v10H13z"/>
                        </svg>
                        Microsoft
                    </a>
                </!--div-->
            </form>

            <!-- Sign Up Link -->
            <!--div-- class="signup-link">
                Pas encore de compte ? <a href="register.html">Créer un compte</a>
            </-div-->
        </div>
    </div>

    <script>
        class LoginForm {
            constructor() {
                this.form = document.getElementById('loginForm');
                this.emailInput = document.getElementById('email');
                this.passwordInput = document.getElementById('password');
                this.passwordToggle = document.getElementById('passwordToggle');
                this.submitBtn = document.getElementById('submitBtn');
                
                this.init();
            }

            init() {
                // Password toggle functionality
                this.passwordToggle.addEventListener('click', () => {
                    this.togglePasswordVisibility();
                });

                // Form validation
                this.emailInput.addEventListener('blur', () => {
                    this.validateEmail();
                });

                this.passwordInput.addEventListener('input', () => {
                    this.validatePassword();
                });

                // Form submission
                this.form.addEventListener('submit', (e) => {
                    this.handleSubmit(e);
                });

                // Real-time validation
                this.emailInput.addEventListener('input', () => {
                    this.clearError('email');
                });

                this.passwordInput.addEventListener('input', () => {
                    this.clearError('password');
                });

                // Social login handlers
                document.querySelectorAll('.social-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.handleSocialLogin(btn.getAttribute('href').slice(1));
                    });
                });
            }

            togglePasswordVisibility() {
                const type = this.passwordInput.type === 'password' ? 'text' : 'password';
                this.passwordInput.type = type;
                
                // Update icon
                const icon = type === 'password' ? 
                    '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>' :
                    '<path d="m1 1 22 22"/><path d="M6.71 6.71C4.68 8.1 3 10.73 3 12s1.68 3.9 3.71 5.29"/><path d="M17.29 17.29C19.32 15.9 21 13.27 21 12s-1.68-3.9-3.71-5.29"/><circle cx="12" cy="12" r="3"/>';
                
                this.passwordToggle.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icon}</svg>`;
            }

            validateEmail() {
                const email = this.emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!email) {
                    this.showError('email', 'L\'adresse email est requise');
                    return false;
                } else if (!emailRegex.test(email)) {
                    this.showError('email', 'Format d\'email invalide');
                    return false;
                } else {
                    this.showSuccess('email');
                    return true;
                }
            }

            validatePassword() {
                const password = this.passwordInput.value;
                
                if (!password) {
                    this.showError('password', 'Le mot de passe est requis');
                    return false;
                } else if (password.length < 6) {
                    this.showError('password', 'Le mot de passe doit contenir au moins 6 caractères');
                    return false;
                } else {
                    this.showSuccess('password');
                    return true;
                }
            }

            showError(field, message) {
                const input = document.getElementById(field);
                const errorDiv = document.getElementById(`${field}Error`);
                
                input.classList.add('error');
                input.classList.remove('success');
                errorDiv.querySelector('span').textContent = message;
                errorDiv.style.display = 'flex';
            }

            showSuccess(field) {
                const input = document.getElementById(field);
                const errorDiv = document.getElementById(`${field}Error`);
                
                input.classList.remove('error');
                input.classList.add('success');
                errorDiv.style.display = 'none';
            }

            clearError(field) {
                const input = document.getElementById(field);
                const errorDiv = document.getElementById(`${field}Error`);
                
                input.classList.remove('error', 'success');
                errorDiv.style.display = 'none';
            }

            async handleSubmit(e) {
                e.preventDefault();
                
                // Validate all fields
                const isEmailValid = this.validateEmail();
                const isPasswordValid = this.validatePassword();
                
                if (!isEmailValid || !isPasswordValid) {
                    this.showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
                    return;
                }

                // Show loading state
                this.setLoading(true);

                try {
                    // Simulate API call
                    await this.simulateLogin();
                    
                    this.showNotification('Connexion réussie ! Redirection en cours...', 'success');
                    
                    // Simulate redirect after success
                    setTimeout(() => {
                        console.log('Redirection vers dashboard.php');
                        // window.location.href = 'dashboard.php';
                    }, 1500);
                    
                } catch (error) {
                    this.showNotification(error.message || 'Erreur lors de la connexion', 'error');
                } finally {
                    this.setLoading(false);
                }
            }

            async simulateLogin() {
                // Simulate network delay
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                const email = this.emailInput.value.trim();
                const password = this.passwordInput.value;
                
                // Simulate different scenarios
                if (email === 'demo@masoutenance.com' && password === 'demo123') {
                    return { success: true, message: 'Connexion réussie' };
                } else if (email === 'error@test.com') {
                    throw new Error('Compte temporairement suspendu');
                } else if (password === 'wrongpass') {
                    throw new Error('Mot de passe incorrect');
                } else {
                    // Default success for demo
                    return { success: true, message: 'Connexion réussie' };
                }
            }

            setLoading(isLoading) {
                if (isLoading) {
                    this.submitBtn.classList.add('loading');
                    this.submitBtn.disabled = true;
                } else {
                    this.submitBtn.classList.remove('loading');
                    this.submitBtn.disabled = false;
                }
            }

            handleSocialLogin(provider) {
                this.showNotification(`Connexion avec ${provider} en cours...`, 'info');
                
                // Simulate social login
                setTimeout(() => {
                    this.showNotification(`Fonctionnalité ${provider} bientôt disponible !`, 'info');
                }, 1000);
            }

            showNotification(message, type = 'info') {
                // Remove existing notification
                const existingNotification = document.querySelector('.notification');
                if (existingNotification) {
                    existingNotification.remove();
                }

                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                // Trigger show animation
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);
                
                // Auto remove after 4 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(notification)) {
                            document.body.removeChild(notification);
                        }
                    }, 300);
                }, 4000);
            }
        }

        // Enhanced form interactions
        class FormEnhancements {
            constructor() {
                this.init();
            }

            init() {
                // Auto-focus first input
                setTimeout(() => {
                    document.getElementById('email').focus();
                }, 500);

                // Keyboard shortcuts
                document.addEventListener('keydown', (e) => {
                    // Enter to submit form when focused on input
                    if (e.key === 'Enter' && (e.target.tagName === 'INPUT')) {
                        const form = document.getElementById('loginForm');
                        const submitBtn = document.getElementById('submitBtn');
                        if (!submitBtn.disabled) {
                            form.dispatchEvent(new Event('submit'));
                        }
                    }
                    
                    // Escape to clear form
                    if (e.key === 'Escape') {
                        this.clearForm();
                    }
                });

                // Enhanced input interactions
                this.addInputAnimations();
                
                // Demo credentials helper
                //this.addDemoHelper();
            }

            addInputAnimations() {
                const inputs = document.querySelectorAll('.form-input');
                
                inputs.forEach(input => {
                    input.addEventListener('focus', () => {
                        input.parentElement.style.transform = 'scale(1.02)';
                    });
                    
                    input.addEventListener('blur', () => {
                        input.parentElement.style.transform = 'scale(1)';
                    });
                });
            }

            /*addDemoHelper() {
                // Create demo helper button
                const demoBtn = document.createElement('button');
                demoBtn.type = 'button';
                demoBtn.className = 'demo-btn';
                demoBtn.textContent = '✨ Utiliser les identifiants de démo';
                demoBtn.style.cssText = `
                    position: absolute;
                    bottom: 2rem;
                    left: 50%;
                    transform: translateX(-50%);
                    background: rgba(255, 255, 255, 0.1);
                    color: white;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    padding: 0.5rem 1rem;
                    border-radius: 6px;
                    font-size: 0.85rem;
                    cursor: pointer;
                    backdrop-filter: blur(10px);
                    transition: all 0.3s ease;
                    z-index: 10;
                `;
                
                demoBtn.addEventListener('click', () => {
                    document.getElementById('email').value = 'demo@masoutenance.com';
                    document.getElementById('password').value = 'demo123';
                    
                    // Trigger validation
                    document.getElementById('email').dispatchEvent(new Event('blur'));
                    document.getElementById('password').dispatchEvent(new Event('input'));
                    
                    // Show notification
                    window.loginForm.showNotification('Identifiants de démo chargés !', 'success');
                });
                
                demoBtn.addEventListener('mouseenter', () => {
                    demoBtn.style.background = 'rgba(255, 255, 255, 0.2)';
                    demoBtn.style.transform = 'translateX(-50%) translateY(-2px)';
                });
                
                demoBtn.addEventListener('mouseleave', () => {
                    demoBtn.style.background = 'rgba(255, 255, 255, 0.1)';
                    demoBtn.style.transform = 'translateX(-50%)';
                });
                
                document.body.appendChild(demoBtn);
            }*/

            clearForm() {
                document.getElementById('loginForm').reset();
                document.querySelectorAll('.form-input').forEach(input => {
                    input.classList.remove('error', 'success');
                });
                document.querySelectorAll('.error-message').forEach(error => {
                    error.style.display = 'none';
                });
            }
        }

        // Progressive enhancement for better UX
        class ProgressiveEnhancement {
            constructor() {
                this.init();
            }

            init() {
                // Add smooth page transitions
                this.addPageTransitions();
                
                // Add connection status indicator
                this.addConnectionStatus();
                
                // Prefetch critical resources
                this.prefetchResources();
                
                // Add form persistence
                this.addFormPersistence();
            }

            addPageTransitions() {
                document.body.style.opacity = '0';
                document.body.style.transform = 'translateY(20px)';
                
                window.addEventListener('load', () => {
                    setTimeout(() => {
                        document.body.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        document.body.style.opacity = '1';
                        document.body.style.transform = 'translateY(0)';
                    }, 100);
                });
            }

            addConnectionStatus() {
                window.addEventListener('online', () => {
                    window.loginForm?.showNotification('Connexion rétablie', 'success');
                });
                
                window.addEventListener('offline', () => {
                    window.loginForm?.showNotification('Connexion perdue - mode hors ligne', 'warning');
                });
            }

            prefetchResources() {
                // Prefetch dashboard resources
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = 'dashboard.html';
                document.head.appendChild(link);
            }

            addFormPersistence() {
                const emailInput = document.getElementById('email');
                
                // Load saved email
                const savedEmail = localStorage.getItem('masoutenance_email');
                if (savedEmail) {
                    emailInput.value = savedEmail;
                }
                
                // Save email on input
                emailInput.addEventListener('input', () => {
                    localStorage.setItem('masoutenance_email', emailInput.value);
                });
            }
        }

        // Initialize everything when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            window.loginForm = new LoginForm();
            new FormEnhancements();
            new ProgressiveEnhancement();
            
            console.log('🎓 MaSoutenance Login - Ready!');
            console.log('💡 Astuce: Utilisez demo@masoutenance.com / demo123 pour tester');
        });

        // Handle back button
        document.querySelector('.back-home').addEventListener('click', (e) => {
            e.preventDefault();
            
            // Add exit animation
            document.body.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            document.body.style.opacity = '0';
            document.body.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 300);
        });

        // Easter egg - Konami code for admin access
        let konamiCode = [];
        const konamiSequence = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'KeyB', 'KeyA'];

        document.addEventListener('keydown', (e) => {
            konamiCode.push(e.code);
            if (konamiCode.length > konamiSequence.length) {
                konamiCode.shift();
            }
            
            if (konamiCode.join('') === konamiSequence.join('')) {
                document.getElementById('email').value = 'admin@masoutenance.com';
                document.getElementById('password').value = 'admin2024';
                window.loginForm.showNotification('🔑 Mode Admin activé !', 'success');
                konamiCode = [];
            }
        });
    </script>
</body>
</html>