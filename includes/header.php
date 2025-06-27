<?php
/**
 * Header commun pour toutes les pages
 * Fichier: includes/header.php
 */

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}

// Variables par défaut
$page_title = $page_title ?? 'Dashboard';
$page_description = $page_description ?? 'Système de Validation Académique - UFHB Cocody';
$current_user = SessionManager::isLoggedIn() ? SessionManager::getUserName() : null;
$current_role = SessionManager::isLoggedIn() ? SessionManager::getUserRole() : null;
$user_roles = SessionManager::isLoggedIn() ? SessionManager::getUserRoles() : [];

// Génération du token CSRF
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="author" content="<?= UNIVERSITY_NAME ?>">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= $csrf_token ?>">
    
    <title><?= htmlspecialchars($page_title) ?> - <?= APP_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= ASSETS_URL ?>images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- CSS Global -->
    <link href="<?= ASSETS_URL ?>css/common/global-style.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>css/common/responsive.css" rel="stylesheet">
    
    <?php if (SessionManager::isLoggedIn()): ?>
        <?php if (hasRole(ROLE_ADMIN)): ?>
            <link href="<?= ASSETS_URL ?>css/admin/admin-style.css" rel="stylesheet">
            <link href="<?= ASSETS_URL ?>css/admin/admin-dashboard.css" rel="stylesheet">
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_RESPONSABLE_SCOLARITE)): ?>
            <link href="<?= ASSETS_URL ?>css/responsable_scolarite/responsable-style.css" rel="stylesheet">
            <link href="<?= ASSETS_URL ?>css/responsable_scolarite/dashboard-responsable.css" rel="stylesheet">
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_CHARGE_COMMUNICATION)): ?>
            <link href="<?= ASSETS_URL ?>css/charge_communication/communication-style.css" rel="stylesheet">
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_COMMISSION)): ?>
            <link href="<?= ASSETS_URL ?>css/commission/commission-style.css" rel="stylesheet">
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_SECRETAIRE)): ?>
            <link href="<?= ASSETS_URL ?>css/secretaire/secretaire-style.css" rel="stylesheet">
        <?php endif; ?>
        
        <?php if (hasRole(ROLE_ETUDIANT)): ?>
            <link href="<?= ASSETS_URL ?>css/etudiant/etudiant-style.css" rel="stylesheet">
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- CSS Custom pour la page courante -->
    <?php if (isset($custom_css)): ?>
        <?php foreach ($custom_css as $css): ?>
            <link href="<?= ASSETS_URL ?>css/<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .sidebar-nav {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: calc(100vh - 56px);
            width: 250px;
            position: fixed;
            left: 0;
            top: 56px;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar-nav.collapsed {
            transform: translateX(-100%);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        @media (max-width: 768px) {
            .sidebar-nav {
                transform: translateX(-100%);
            }
            
            .sidebar-nav.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation principale -->
    <?php if (SessionManager::isLoggedIn()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <i class="fas fa-university"></i>
                <?= UNIVERSITY_SHORT ?>
            </a>
            
            <!-- Toggle pour mobile -->
            <button class="navbar-toggler" type="button" id="sidebarToggle">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Menu de navigation -->
            <div class="navbar-nav ms-auto">
                <!-- Notifications -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger notifications-count" id="notificationsCount">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="notificationsList">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><div class="text-center p-3"><small class="text-muted">Aucune notification</small></div></li>
                    </ul>
                </div>
                
                <!-- Profil utilisateur -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="<?= ASSETS_URL ?>images/avatars/default.png" alt="Avatar" class="rounded-circle" width="25" height="25">
                        <?= htmlspecialchars($current_user) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><?= htmlspecialchars($current_role) ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i class="fas fa-user"></i> Mon Profil
                        </a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">
                            <i class="fas fa-cog"></i> Paramètres
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar Navigation -->
    <nav class="sidebar-nav" id="sidebar">
        <div class="p-3">
            <div class="text-center mb-4">
                <img src="<?= ASSETS_URL ?>images/avatars/default.png" alt="Avatar" class="rounded-circle mb-2" width="60" height="60">
                <h6 class="text-white"><?= htmlspecialchars($current_user) ?></h6>
                <small class="text-light"><?= htmlspecialchars($current_role) ?></small>
            </div>
            
            <ul class="nav nav-pills flex-column">
                <?php if (hasRole(ROLE_ADMIN)): ?>
                <!-- Menu Administrateur -->
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= PAGES_URL ?>admin/">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" data-bs-toggle="collapse" href="#usersMenu">
                        <i class="fas fa-users"></i> Gestion Utilisateurs
                        <i class="fas fa-chevron-down float-end"></i>
                    </a>
                    <div class="collapse" id="usersMenu">
                        <ul class="nav nav-pills flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link text-light" href="<?= PAGES_URL ?>admin/etudiants/liste.php">
                                    <i class="fas fa-graduation-cap"></i> Étudiants
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-light" href="<?= PAGES_URL ?>admin/enseignants/liste.php">
                                    <i class="fas fa-chalkboard-teacher"></i> Enseignants
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-light" href="<?= PAGES_URL ?>admin/personnel/liste.php">
                                    <i class="fas fa-users-cog"></i> Personnel
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= PAGES_URL ?>admin/parametres/configuration.php">
                        <i class="fas fa-cogs"></i> Configuration
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= PAGES_URL ?>admin/logs.php">
                        <i class="fas fa-clipboard-list"></i> Logs d'Audit
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasRole(ROLE_RESPONSABLE_SCOLARITE)): ?>
                <!-- Menu Responsable Scolarité -->
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= PAGES_URL ?>responsable_scolarite/">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= PAGES_URL ?>responsable_scolarite/etudiants/gestion.php">
                        <i class="fas fa-users"></i> Gestion Étudiants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= PAGES_URL ?>responsable_scolarite/notes/saisie.php">
                        <i class="fas fa-edit"></i> Gestion Notes
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasRole(ROLE_ETUDIANT)): ?>
                <!-- Menu Étudiant -->
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= PAGES_URL ?>etudiant/">
                        <i class="fas fa-home"></i> Mon Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= PAGES_URL ?>etudiant/rapport/redaction.php">
                        <i class="fas fa-file-alt"></i> Mon Rapport
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= PAGES_URL ?>etudiant/reclamations/soumettre.php">
                        <i class="fas fa-headset"></i> Réclamations
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <!-- Contenu principal -->
    <main class="main-content" id="mainContent">
    <?php else: ?>
    <!-- Page non authentifiée -->
    <main>
    <?php endif; ?>
    
    <!-- Conteneur pour les notifications toast -->
    <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 11000;"></div>
    
    <!-- Scripts de base -->
    <script>
        // Configuration globale
        window.CONFIG = {
            BASE_URL: '<?= BASE_URL ?>',
            API_URL: '<?= API_URL ?>',
            CSRF_TOKEN: '<?= $csrf_token ?>',
            USER_ROLE: '<?= $current_role ?>',
            USER_ROLES: <?= json_encode($user_roles) ?>
        };
        
        // Fonction globale pour afficher les notifications
        window.showNotification = function(message, type = 'info', duration = 5000) {
            const toastContainer = document.getElementById('toastContainer');
            const toastId = 'toast_' + Date.now();
            
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-times-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };
            
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `alert alert-${type} alert-dismissible fade show`;
            toast.innerHTML = `
                <i class="fas ${icons[type]} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                const toastElement = document.getElementById(toastId);
                if (toastElement) {
                    toastElement.remove();
                }
            }, duration);
        };
        
        // Fonction de déconnexion
        window.logout = function() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = '<?= BASE_URL ?>logout.php';
            }
        };
    </script>