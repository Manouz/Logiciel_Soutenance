<?php 
/**
 * Page d'accueil du Système de Validation Académique
 * Université Félix Houphouët-Boigny
 */

session_start();

// Inclure les fichiers de configuration
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirection automatique si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    header('Location: authenticate.php');
    exit();
}

// Configuration de la page
$pageTitle = "Accueil - Système de Validation Académique";
$pageDescription = "Plateforme de gestion des validations académiques pour les étudiants de Master 2";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="keywords" content="université, validation académique, master 2, soutenance, rapport">
    <meta name="author" content="Université Félix Houphouët-Boigny">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/logos/favicon.ico">
    
    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1a5490;
            --secondary-color: #f8b500;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --dark-color: #2c3e50;
            --light-bg: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            pointer-events: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 2rem;
            font-weight: 300;
        }
        
        .university-logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 2rem;
            filter: brightness(0) invert(1);
        }
        
        /* Login Card */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-title {
            color: var(--primary-color);
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        /* Buttons */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 500;
            text-transform: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(26, 84, 144, 0.4);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 84, 144, 0.6);
            background: linear-gradient(135deg, #2980b9 0%, var(--primary-color) 100%);
        }
        
        .btn-outline-custom {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 12px;
            padding: 10px 28px;
            font-weight: 500;
            background: transparent;
            transition: all 0.3s ease;
        }
        
        .btn-outline-custom:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Features Section */
        .features-section {
            padding: 5rem 0;
            background: white;
        }
        
        .feature-card {
            text-align: center;
            padding: 2rem 1rem;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), #3498db);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }
        
        .feature-title {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .feature-description {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        /* Footer */
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-link:hover {
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .login-card {
                margin: 2rem 1rem;
                padding: 2rem;
            }
            
            .hero-section {
                padding: 2rem 0;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
        
        /* Floating elements */
        .floating-shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-shape:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-shape:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .floating-shape:nth-child(3) {
            bottom: 20%;
            left: 15%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Floating shapes -->
        <div class="floating-shape">
            <i class="fas fa-graduation-cap fa-3x"></i>
        </div>
        <div class="floating-shape">
            <i class="fas fa-university fa-2x"></i>
        </div>
        <div class="floating-shape">
            <i class="fas fa-book fa-2x"></i>
        </div>
        
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <!-- Hero Content -->
                <div class="col-lg-7 col-md-6">
                    <div class="hero-content fade-in-up">
                        <img src="assets/images/logos/ufhb-logo.png" alt="UFHB Logo" class="university-logo">
                        <h1 class="hero-title">Système de Validation Académique</h1>
                        <p class="hero-subtitle">
                            Plateforme numérique dédiée à la gestion des validations académiques 
                            pour les étudiants de Master 2 de l'Université Félix Houphouët-Boigny
                        </p>
                        
                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a href="#features" class="btn btn-outline-custom">
                                <i class="fas fa-info-circle me-2"></i>
                                En savoir plus
                            </a>
                            <a href="#contact" class="btn btn-outline-custom">
                                <i class="fas fa-phone me-2"></i>
                                Contact
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Login Card -->
                <div class="col-lg-5 col-md-6">
                    <div class="login-card fade-in-up">
                        <div class="login-header">
                            <h2 class="login-title">Connexion</h2>
                            <p class="login-subtitle">Accédez à votre espace personnel</p>
                        </div>
                        
                        <!-- Messages d'erreur/succès -->
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php
                                switch ($_GET['error']) {
                                    case 'invalid_credentials':
                                        echo 'Email ou mot de passe incorrect.';
                                        break;
                                    case 'account_blocked':
                                        echo 'Votre compte a été bloqué. Contactez l\'administration.';
                                        break;
                                    case 'account_inactive':
                                        echo 'Votre compte est inactif. Contactez l\'administration.';
                                        break;
                                    case 'session_expired':
                                        echo 'Votre session a expiré. Veuillez vous reconnecter.';
                                        break;
                                    case 'access_denied':
                                        echo 'Accès refusé. Permissions insuffisantes.';
                                        break;
                                    default:
                                        echo 'Une erreur est survenue lors de la connexion.';
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['success']) && $_GET['success'] === 'logout'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                Vous avez été déconnecté avec succès.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="login.php" id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           placeholder="votre.email@ufhb.edu.ci"
                                           required
                                           value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Votre mot de passe"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                    <label class="form-check-label" for="remember_me">
                                        Se souvenir de moi
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Se connecter
                            </button>
                            
                            <div class="text-center">
                                <a href="forgot-password.php" class="text-decoration-none">
                                    <small>Mot de passe oublié ?</small>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
   
    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold text-primary mb-3">Fonctionnalités de la plateforme</h2>
                    <p class="lead text-muted">Un système complet pour la gestion de vos validations académiques</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="feature-title">Gestion des Rapports</h3>
                        <p class="feature-description">
                            Déposez, modifiez et suivez l'état de vos rapports de stage et mémoires 
                            en temps réel avec un éditeur intégré.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="feature-title">Suivi Collaboratif</h3>
                        <p class="feature-description">
                            Communication fluide entre étudiants, encadreurs, et commission 
                            pour un suivi optimal de votre progression.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="feature-title">Planning Intégré</h3>
                        <p class="feature-description">
                            Planification automatique des soutenances avec gestion 
                            des créneaux et notifications des échéances.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Sécurité</h3>
                        <p class="feature-description">
                            Protection de vos données avec chiffrement et système 
                            d'authentification sécurisé basé sur les rôles.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">Tableaux de Bord</h3>
                        <p class="feature-description">
                            Visualisation claire de votre progression avec statistiques 
                            et indicateurs personnalisés selon votre profil.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">Responsive</h3>
                        <p class="feature-description">
                            Accès depuis n'importe quel appareil avec une interface 
                            adaptative optimisée pour mobile et tablette.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">Contact</h5>
                    <p class="mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        22 BP 582 Abidjan 22, Côte d'Ivoire
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-phone me-2"></i>
                        +225 27 22 44 08 79
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        info@ufhb.edu.ci
                    </p>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">Liens Utiles</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="https://ufhb.edu.ci" class="footer-link" target="_blank">
                                Site officiel UFHB
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="footer-link">Guide d'utilisation</a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="footer-link">FAQ</a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="footer-link">Support technique</a>
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-12 mb-4">
                    <h5 class="fw-bold mb-3">Système de Validation</h5>
                    <p class="mb-3">
                        Plateforme officielle de gestion des validations académiques 
                        pour les étudiants de Master 2.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="footer-link">
                            <i class="fab fa-facebook fa-lg"></i>
                        </a>
                        <a href="#" class="footer-link">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <a href="#" class="footer-link">
                            <i class="fab fa-linkedin fa-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">
                        &copy; <?= date('Y') ?> Université Félix Houphouët-Boigny. 
                        Tous droits réservés.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        Version 1.0.0 - Développé avec ❤️ pour l'UFHB
                    </small>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs requis.');
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Veuillez saisir une adresse email valide.');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion...';
            submitBtn.disabled = true;
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Add floating animation to shapes
        document.addEventListener('DOMContentLoaded', function() {
            const shapes = document.querySelectorAll('.floating-shape');
            shapes.forEach((shape, index) => {
                shape.style.animationDelay = (index * 2) + 's';
            });
        });
        
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
                    