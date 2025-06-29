<?php
/**
 * Page de connexion
 * Système de Validation Académique - UFHB Cocody
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Démarrer la session
SessionManager::start();

// Rediriger si déjà connecté
if (SessionManager::isLoggedIn()) {
    // Redirection selon le rôle
    $userRole = SessionManager::getUserRole();
    $redirectMap = [
        'Administrateur' => 'vues/admin/index.php',
        'Responsable Scolarité' => 'vues/responsable_scolarite/index.php',
        'Chargé Communication' => 'vues/charge_communication/index.php',
        'Commission' => 'vues/commission/index.php',
        'Secrétaire' => 'vues/secretaire/index.php',
        'Enseignant' => 'vues/enseignant/index.php',
        'Étudiant' => 'vues/etudiant/index.php'
    ];
    
    $redirectUrl = $redirectMap[$userRole] ?? 'vues/etudiant/index.php';
    redirectTo($redirectUrl);
}

$error = '';
$success = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } elseif (!validateEmail($email)) {
        $error = 'Format d\'email invalide';
    } else {
        try {
            $auth = new Auth();
            $result = $auth->authenticate($email, $password);
            
            if ($result['success']) {
                // Définir les données de session
                SessionManager::setUserData($result['user']);
                
                // Gestion du "Se souvenir de moi"
                if ($remember) {
                    $token = generateToken();
                    setcookie('remember_token', $token, time() + (30 * 24 * 3600), '/', '', false, true);
                    // Ici vous pourriez sauvegarder le token en base pour une reconnexion automatique
                }
                
                // Redirection selon le rôle
                $userRole = $result['user']['role_principal'];
                $redirectMap = [
                    'Administrateur' => 'vues/admin/index.php',
                    'Responsable Scolarité' => 'vues/responsable_scolarite/index.php',
                    'Chargé Communication' => 'vues/charge_communication/index.php',
                    'Commission' => 'vues/commission/index.php',
                    'Secrétaire' => 'vues/secretaire/index.php',
                    'Enseignant' => 'vues/enseignant/index.php',
                    'Étudiant' => 'vues/etudiant/index.php'
                ];
                
                $redirectUrl = $redirectMap[$userRole] ?? 'vues/etudiant/index.php';
                
                // Si c'est une requête AJAX
                if (isAjaxRequest()) {
                    jsonResponse([
                        'success' => true,
                        'message' => $result['message'],
                        'redirect' => $redirectUrl
                    ]);
                } else {
                    redirectTo($redirectUrl);
                }
            } else {
                $error = $result['message'];
                
                // Réponse AJAX
                if (isAjaxRequest()) {
                    jsonResponse([
                        'success' => false,
                        'message' => $error,
                        'locked' => $result['locked'] ?? false
                    ], 400);
                }
            }
        } catch (Exception $e) {
            error_log("Erreur d'authentification: " . $e->getMessage());
            $error = 'Erreur du système. Veuillez réessayer.';
            
            if (isAjaxRequest()) {
                jsonResponse([
                    'success' => false,
                    'message' => $error
                ], 500);
            }
        }
    }
}
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

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(16, 185, 129, 0.2);
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

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4"/>
                        <circle cx="12" cy="12" r="10"/>
                    </svg>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form class="login-form" id="loginForm" method="POST" action="">
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
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
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
                    <a href="forgot-password.php" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn" id="submitBtn">
                    Se connecter
                </button>
            </form>
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

                // Form submission avec AJAX
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
                    // Envoyer la requête AJAX
                    const formData = new FormData(this.form);
                    
                    const response = await fetch('login.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showNotification(result.message, 'success');
                        
                        // Redirection après succès
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 1500);
                    } else {
                        this.showNotification(result.message, 'error');
                        
                        if (result.locked) {
                            this.submitBtn.disabled = true;
                            setTimeout(() => {
                                this.submitBtn.disabled = false;
                            }, 30000); // 30 secondes
                        }
                    }
                    
                } catch (error) {
                    console.error('Erreur:', error);
                    this.showNotification('Erreur de connexion au serveur', 'error');
                } finally {
                    this.setLoading(false);
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

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            window.loginForm = new LoginForm();
            
            // Auto-focus first input
            setTimeout(() => {
                document.getElementById('email').focus();
            }, 500);
            
            console.log('🎓 MaSoutenance Login - Ready!');
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
    </script>
</body>
</html>