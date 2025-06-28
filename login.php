<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Université Félix Houphouët-Boigny</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a5490;
            --secondary-color: #f8b500;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-bg: #f8f9fa;
            --gradient-primary: linear-gradient(135deg, #1a5490 0%, #2980b9 100%);
            --gradient-secondary: linear-gradient(135deg, #f8b500 0%, #f39c12 100%);
            --shadow-soft: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Arrière-plan animé */
        .bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .bg-shapes::before,
        .bg-shapes::after {
            content: '';
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .bg-shapes::before {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
            animation-delay: 0s;
        }

        .bg-shapes::after {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Container principal */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            margin: 0 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 3rem 2.5rem;
            transition: all 0.3s ease;
        }

        .login-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-5px);
        }

        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .university-logo {
            width: 80px;
            height: 80px;
            background: var(--gradient-secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: var(--shadow-soft);
        }

        .university-logo i {
            font-size: 2.5rem;
            color: white;
        }

        .login-title {
            color: var(--dark-color);
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #6c757d;
            font-weight: 400;
            font-size: 0.95rem;
        }

        /* Formulaire */
        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem 1rem 1rem 3rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 84, 144, 0.15);
            background: white;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 5;
        }

        .form-label {
            color: #6c757d;
            font-weight: 500;
            font-size: 0.9rem;
            left: 3rem;
        }

        /* Options de connexion */
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-input {
            border-radius: 6px;
            border: 2px solid #dee2e6;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        /* Bouton de connexion */
        .btn-login {
            background: var(--gradient-primary);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
            background: var(--gradient-primary);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            pointer-events: none;
        }

        .btn-login .spinner {
            display: none;
        }

        .btn-login.loading .spinner {
            display: inline-block;
        }

        .btn-login.loading .btn-text {
            display: none;
        }

        /* Messages d'alerte */
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1.5rem;
            padding: 0.875rem 1.25rem;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .copyright {
            color: #6c757d;
            font-size: 0.85rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .university-logo {
                width: 70px;
                height: 70px;
            }

            .university-logo i {
                font-size: 2rem;
            }
        }

        /* Animation d'entrée */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            animation: slideInUp 0.6s ease-out;
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <div class="bg-shapes"></div>
    
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="university-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="login-title">Connexion</h1>
                <p class="login-subtitle">Université Félix Houphouët-Boigny de Cocody</p>
            </div>

            <!-- Messages d'alerte -->
            <div id="alertContainer"></div>

            <!-- Formulaire de connexion -->
            <form id="loginForm" method="POST" action="authenticate.php">
                <div class="form-floating">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="Adresse email" 
                           required>
                    <label for="email">Adresse email</label>
                </div>

                <div class="form-floating">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Mot de passe" 
                           required>
                    <label for="password">Mot de passe</label>
                    <button type="button" 
                            class="btn btn-link position-absolute end-0 top-50 translate-middle-y me-3 p-0"
                            id="togglePassword"
                            style="z-index: 10; border: none; background: none;">
                        <i class="fas fa-eye text-muted"></i>
                    </button>
                </div>

                <div class="login-options">
                    <div class="remember-me">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="remember" 
                               name="remember">
                        <label class="form-check-label" for="remember">
                            Se souvenir de moi
                        </label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">
                        Mot de passe oublié ?
                    </a>
                </div>

                <button type="submit" class="btn btn-login">
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Se connecter
                    </span>
                    <span class="spinner">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Connexion...
                    </span>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p class="copyright">
                    © 2025 Université Félix Houphouët-Boigny de Cocody<br>
                    <small>Système de Validation Académique</small>
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const submitBtn = document.querySelector('.btn-login');
            const alertContainer = document.getElementById('alertContainer');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });

            // Form submission
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Clear previous alerts
                alertContainer.innerHTML = '';
                
                // Validation
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value.trim();
                
                if (!email || !password) {
                    showAlert('Veuillez remplir tous les champs.', 'danger');
                    return;
                }
                
                if (!isValidEmail(email)) {
                    showAlert('Veuillez entrer une adresse email valide.', 'danger');
                    return;
                }
                
                // Show loading state
                submitBtn.classList.add('loading');
                
                // Simulate form submission (replace with actual AJAX call)
                setTimeout(() => {
                    // Remove loading state
                    submitBtn.classList.remove('loading');
                    
                    // Submit form
                    this.submit();
                }, 1000);
            });

            // Email validation
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Show alert function
            function showAlert(message, type) {
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show`;
                alert.innerHTML = `
                    <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : 'check-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                alertContainer.appendChild(alert);
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            }

            // Handle URL parameters for messages
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            const success = urlParams.get('success');
            
            if (error) {
                let message = 'Une erreur est survenue.';
                switch(error) {
                    case 'invalid_credentials':
                        message = 'Email ou mot de passe incorrect.';
                        break;
                    case 'account_blocked':
                        message = 'Votre compte est bloqué. Contactez l\'administrateur.';
                        break;
                    case 'account_inactive':
                        message = 'Votre compte est inactif.';
                        break;
                    case 'too_many_attempts':
                        message = 'Trop de tentatives de connexion. Réessayez plus tard.';
                        break;
                }
                showAlert(message, 'danger');
            }
            
            if (success) {
                let message = 'Opération réussie.';
                switch(success) {
                    case 'logout':
                        message = 'Vous avez été déconnecté avec succès.';
                        break;
                    case 'password_reset':
                        message = 'Votre mot de passe a été réinitialisé.';
                        break;
                }
                showAlert(message, 'success');
            }

            // Auto-focus on email field
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>