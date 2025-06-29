<?php
/**
 * Barre de navigation principale
 * Système de Validation Académique - Université Félix Houphouët-Boigny
 */

// Vérifier que l'utilisateur est connecté
if (!class_exists('SessionManager') || !SessionManager::isLoggedIn()) {
    return;
}

/**
 * Fonction pour récupérer les notifications non lues
 */
if (!function_exists('getUnreadNotifications')) {
    function getUnreadNotifications($userId, $limit = 5) {
        if (!$userId) {
            return [];
        }
        
        try {
            if (class_exists('Database')) {
                $pdo = Database::getInstance()->getConnection();
                
                $sql = "SELECT 
                            notification_id,
                            titre_notification,
                            contenu_notification,
                            lien_action,
                            date_creation
                        FROM notifications 
                        WHERE utilisateur_id = ? 
                        AND est_lu = 0 
                        AND est_actif = 1
                        ORDER BY date_creation DESC 
                        LIMIT ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId, $limit]);
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des notifications: " . $e->getMessage());
        }
        
        return [];
    }
}

/**
 * Définir les constantes manquantes
 */
if (!defined('APP_SHORT_NAME')) define('APP_SHORT_NAME', 'SVA');

$currentUser = getCurrentUser();
$userRole = $currentUser['nom_role'] ?? $currentUser['role'] ?? 'Utilisateur';
$userName = trim(($currentUser['prenoms'] ?? '') . ' ' . ($currentUser['nom'] ?? '')) ?: $currentUser['email'] ?? 'Utilisateur';
$userLevel = $currentUser['niveau_acces'] ?? 1;
$userId = $currentUser['utilisateur_id'] ?? $currentUser['id'] ?? null;

// Obtenir les notifications non lues
$notifications = getUnreadNotifications($userId, 5);
$notificationCount = count($notifications);

// Navigation selon le rôle
$navigationItems = [];

switch ($userRole) {
    case 'Administrateur':
        $navigationItems = [
            ['title' => 'Dashboard', 'url' => 'vues/admin/index.php', 'icon' => 'fas fa-tachometer-alt'],
            ['title' => 'Utilisateurs', 'url' => 'vues/admin/users/', 'icon' => 'fas fa-users', 'submenu' => [
                ['title' => 'Liste', 'url' => 'vues/admin/users/liste.php'],
                ['title' => 'Ajouter', 'url' => 'vues/admin/users/ajouter.php'],
                ['title' => 'Rôles', 'url' => 'vues/admin/users/roles.php']
            ]],
            ['title' => 'Enseignants', 'url' => 'vues/admin/enseignants/', 'icon' => 'fas fa-chalkboard-teacher', 'submenu' => [
                ['title' => 'Liste', 'url' => 'vues/admin/enseignants/liste.php'],
                ['title' => 'Ajouter', 'url' => 'vues/admin/enseignants/ajouter.php']
            ]],
            ['title' => 'Étudiants', 'url' => 'vues/admin/etudiants/', 'icon' => 'fas fa-user-graduate', 'submenu' => [
                ['title' => 'Liste', 'url' => 'vues/admin/etudiants/liste.php'],
                ['title' => 'Ajouter', 'url' => 'vues/admin/etudiants/ajouter.php'],
                ['title' => 'Imports', 'url' => 'vues/admin/etudiants/import.php']
            ]],
            ['title' => 'Système', 'url' => 'vues/admin/system/', 'icon' => 'fas fa-cogs', 'submenu' => [
                ['title' => 'Configuration', 'url' => 'vues/admin/system/config.php'],
                ['title' => 'Logs', 'url' => 'vues/admin/system/logs.php'],
                ['title' => 'Backup', 'url' => 'vues/admin/system/backup.php']
            ]]
        ];
        break;
        
    case 'Responsable Scolarité':
        $navigationItems = [
            ['title' => 'Dashboard', 'url' => 'vues/responsable_scolarite/index.php', 'icon' => 'fas fa-tachometer-alt'],
            ['title' => 'Étudiants', 'url' => 'vues/responsable_scolarite/etudiants/', 'icon' => 'fas fa-user-graduate', 'submenu' => [
                ['title' => 'Gestion', 'url' => 'vues/responsable_scolarite/etudiants/gestion.php'],
                ['title' => 'Éligibilité', 'url' => 'vues/responsable_scolarite/etudiants/eligibilite.php']
            ]],
            ['title' => 'Notes', 'url' => 'vues/responsable_scolarite/notes/', 'icon' => 'fas fa-chart-line', 'submenu' => [
                ['title' => 'Saisie', 'url' => 'vues/responsable_scolarite/notes/saisie.php'],
                ['title' => 'Consultation', 'url' => 'vues/responsable_scolarite/notes/consultation.php'],
                ['title' => 'Validation', 'url' => 'vues/responsable_scolarite/notes/validation.php']
            ]],
            ['title' => 'Rapports', 'url' => 'vues/responsable_scolarite/rapports/', 'icon' => 'fas fa-file-alt', 'submenu' => [
                ['title' => 'Suivi', 'url' => 'vues/responsable_scolarite/rapports/suivi.php'],
                ['title' => 'Planification', 'url' => 'vues/responsable_scolarite/rapports/planification.php']
            ]]
        ];
        break;
        
    case 'Chargé Communication':
        $navigationItems = [
            ['title' => 'Dashboard', 'url' => 'vues/charge_communication/index.php', 'icon' => 'fas fa-tachometer-alt'],
            ['title' => 'Rapports', 'url' => 'vues/charge_communication/rapports/', 'icon' => 'fas fa-file-alt', 'submenu' => [
                ['title' => 'Vérification', 'url' => 'vues/charge_communication/rapports/verification.php'],
                ['title' => 'Validation', 'url' => 'vues/charge_communication/rapports/validation.php']
            ]],
            ['title' => 'Commission', 'url' => 'vues/charge_communication/commission/', 'icon' => 'fas fa-users', 'submenu' => [
                ['title' => 'Envoi', 'url' => 'vues/charge_communication/commission/envoi.php'],
                ['title' => 'Suivi', 'url' => 'vues/charge_communication/commission/suivi.php']
            ]],
            ['title' => 'Notifications', 'url' => 'vues/charge_communication/notifications.php', 'icon' => 'fas fa-bell']
        ];
        break;
        
    case 'Commission':
        $navigationItems = [
            ['title' => 'Dashboard', 'url' => 'vues/commission/index.php', 'icon' => 'fas fa-tachometer-alt'],
            ['title' => 'Rapports', 'url' => 'vues/commission/rapports/', 'icon' => 'fas fa-file-alt', 'submenu' => [
                ['title' => 'À évaluer', 'url' => 'vues/commission/rapports/evaluation.php'],
                ['title' => 'Évalués', 'url' => 'vues/commission/rapports/evalues.php']
            ]],
            ['title' => 'Jurys', 'url' => 'vues/commission/jurys/', 'icon' => 'fas fa-gavel', 'submenu' => [
                ['title' => 'Constitution', 'url' => 'vues/commission/jurys/constitution.php'],
                ['title' => 'Planning', 'url' => 'vues/commission/jurys/planning.php']
            ]]
        ];
        break;
        
    case 'Secrétaire':
        $navigationItems = [
            ['title' => 'Dashboard', 'url' => 'vues/secretaire/index.php', 'icon' => 'fas fa-tachometer-alt'],
            ['title' => 'Étudiants', 'url' => 'vues/secretaire/etudiants/', 'icon' => 'fas fa-user-graduate', 'submenu' => [
                ['title' => 'Liste', 'url' => 'vues/secretaire/etudiants/liste.php'],
                ['title' => 'Export', 'url' => 'vues/secretaire/etudiants/export.php']
            ]],
            ['title' => 'Soutenances', 'url' => 'vues/secretaire/soutenances/', 'icon' => 'fas fa-calendar', 'submenu' => [
                ['title' => 'Planning', 'url' => 'vues/secretaire/soutenances/planning.php'],
                ['title' => 'Calendrier', 'url' => 'vues/secretaire/soutenances/calendrier.php']
            ]]
        ];
        break;
        
    case 'Enseignant':
        $navigationItems = [
            ['title' => 'Dashboard', 'url' => 'vues/enseignant/index.php', 'icon' => 'fas fa-tachometer-alt'],
            ['title' => 'Encadrements', 'url' => 'vues/enseignant/encadrements/', 'icon' => 'fas fa-users', 'submenu' => [
                ['title' => 'Mes étudiants', 'url' => 'vues/enseignant/encadrements/etudiants.php'],
                ['title' => 'Rapports', 'url' => 'vues/enseignant/encadrements/rapports.php']
            ]],
            ['title' => 'Jurys', 'url' => 'vues/enseignant/jurys/', 'icon' => 'fas fa-gavel', 'submenu' => [
                ['title' => 'Mes jurys', 'url' => 'vues/enseignant/jurys/mes_jurys.php'],
                ['title' => 'Évaluations', 'url' => 'vues/enseignant/jurys/evaluations.php']
            ]]
        ];
        break;
        
    case 'Étudiant':
        $navigationItems = [
            ['title' => 'Dashboard', 'url' => 'vues/etudiant/index.php', 'icon' => 'fas fa-tachometer-alt'],
            ['title' => 'Mon Rapport', 'url' => 'vues/etudiant/rapport/', 'icon' => 'fas fa-file-alt', 'submenu' => [
                ['title' => 'Rédaction', 'url' => 'vues/etudiant/rapport/redaction.php'],
                ['title' => 'Soumission', 'url' => 'vues/etudiant/rapport/soumission.php'],
                ['title' => 'Historique', 'url' => 'vues/etudiant/rapport/historique.php']
            ]],
            ['title' => 'Réclamations', 'url' => 'vues/etudiant/reclamations/', 'icon' => 'fas fa-exclamation-circle', 'submenu' => [
                ['title' => 'Soumettre', 'url' => 'vues/etudiant/reclamations/soumettre.php'],
                ['title' => 'Suivi', 'url' => 'vues/etudiant/reclamations/suivi.php']
            ]],
            ['title' => 'Ma Soutenance', 'url' => 'vues/etudiant/soutenance.php', 'icon' => 'fas fa-presentation']
        ];
        break;
        
    default:
        $navigationItems = [
            ['title' => 'Dashboard', 'url' => 'vues/dashboard.php', 'icon' => 'fas fa-tachometer-alt']
        ];
        break;
}
?>

<!-- Navbar Top -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm" style="height: var(--navbar-height); z-index: 1030;">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="<?= page('dashboard.php') ?>">
            <img src="<?= asset('images/logos/ufhb-logo.png') ?>" alt="UFHB" height="40" class="me-2">
            <div class="d-none d-md-block">
                <div class="fw-bold"><?= UNIVERSITY_SHORT_NAME ?></div>
                <small class="opacity-75"><?= APP_SHORT_NAME ?></small>
            </div>
        </a>

        <!-- Toggle Sidebar -->
        <button class="btn btn-link text-white d-lg-none" type="button" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navbar Right -->
        <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
            <!-- Search -->
            <div class="nav-item me-3 d-none d-md-block">
                <form class="d-flex" role="search">
                    <div class="input-group input-group-sm">
                        <input type="search" class="form-control" placeholder="Rechercher..." aria-label="Search">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notifications -->
            <div class="nav-item dropdown me-3">
                <a class="nav-link text-white position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fa-lg"></i>
                    <?php if ($notificationCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $notificationCount > 9 ? '9+' : $notificationCount ?>
                            <span class="visually-hidden">notifications non lues</span>
                        </span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <?php if ($notificationCount > 0): ?>
                            <button class="btn btn-sm btn-link p-0" onclick="markAllNotificationsRead()">
                                <small>Tout marquer comme lu</small>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="notification-list">
                        <?php if (empty($notifications)): ?>
                            <div class="dropdown-item-text text-center text-muted py-3">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Aucune notification
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a href="<?= escape($notification['lien_action'] ?: '#') ?>" class="dropdown-item notification-item" data-id="<?= $notification['notification_id'] ?>">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-circle text-primary" style="font-size: 0.5rem;"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <div class="fw-semibold"><?= escape($notification['titre_notification']) ?></div>
                                            <div class="small text-muted"><?= escape(substr($notification['contenu_notification'], 0, 80)) ?>...</div>
                                            <div class="small text-muted"><?= formatDateFR($notification['date_creation'], 'd/m à H:i') ?></div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?= page('notifications.php') ?>" class="dropdown-item text-center">
                                <small>Voir toutes les notifications</small>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="nav-item dropdown">
                <a class="nav-link text-white dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                    <img src="<?= asset('images/avatars/default-avatar.png') ?>" alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                    <span class="d-none d-md-inline"><?= escape($userName) ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="dropdown-header">
                        <div class="fw-bold"><?= escape($userName) ?></div>
                        <small class="text-muted"><?= escape($userRole) ?></small>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= page('profile.php') ?>">
                        <i class="fas fa-user me-2"></i> Mon Profil
                    </a>
                    <a class="dropdown-item" href="<?= page('settings.php') ?>">
                        <i class="fas fa-cog me-2"></i> Paramètres
                    </a>
                    <a class="dropdown-item" href="<?= page('help.php') ?>">
                        <i class="fas fa-question-circle me-2"></i> Aide
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<nav class="sidebar bg-white shadow-sm" id="sidebar">
    <div class="sidebar-header p-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="sidebar-brand-icon">
                <i class="fas fa-graduation-cap text-primary fa-2x"></i>
            </div>
            <div class="sidebar-brand-text ms-3">
                <div class="fw-bold text-primary"><?= APP_SHORT_NAME ?></div>
                <small class="text-muted"><?= escape($userRole) ?></small>
            </div>
        </div>
    </div>

    <div class="sidebar-content">
        <ul class="nav nav-sidebar flex-column" id="sidebarNav">
            <?php foreach ($navigationItems as $item): ?>
                <li class="nav-item">
                    <?php if (isset($item['submenu'])): ?>
                        <!-- Item avec sous-menu -->
                        <a class="nav-link d-flex align-items-center collapsed" 
                           href="#submenu-<?= md5($item['title']) ?>" 
                           data-bs-toggle="collapse" 
                           role="button" 
                           aria-expanded="false">
                            <i class="<?= $item['icon'] ?> nav-icon"></i>
                            <span class="nav-text"><?= escape($item['title']) ?></span>
                            <i class="fas fa-chevron-right ms-auto collapse-icon"></i>
                        </a>
                        <div class="collapse" id="submenu-<?= md5($item['title']) ?>">
                            <ul class="nav nav-submenu">
                                <?php foreach ($item['submenu'] as $subitem): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= escape($subitem['url']) ?>">
                                            <span class="nav-text"><?= escape($subitem['title']) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Item simple -->
                        <a class="nav-link d-flex align-items-center" href="<?= escape($item['url']) ?>">
                            <i class="<?= $item['icon'] ?> nav-icon"></i>
                            <span class="nav-text"><?= escape($item['title']) ?></span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer p-3 border-top">
        <div class="d-flex align-items-center justify-content-between">
            <div class="small text-muted">
                <div>Version <?= APP_VERSION ?></div>
                <div><?= formatDateFR(date('Y-m-d'), 'd/m/Y') ?></div>
            </div>
            <button class="btn btn-sm btn-outline-secondary" id="sidebarCollapseBtn" title="Réduire la sidebar">
                <i class="fas fa-angle-left"></i>
            </button>
        </div>
    </div>
</nav>

<style>
/* Styles pour la sidebar */
.sidebar {
    position: fixed;
    top: var(--navbar-height);
    left: 0;
    width: var(--sidebar-width);
    height: calc(100vh - var(--navbar-height));
    z-index: 1020;
    overflow-y: auto;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
}

.sidebar.collapsed {
    width: 80px;
}

.sidebar.collapsed .sidebar-brand-text,
.sidebar.collapsed .nav-text {
    display: none;
}

.sidebar.collapsed .collapse-icon {
    display: none;
}

.sidebar-content {
    flex: 1;
    overflow-y: auto;
}

.nav-sidebar {
    padding: 1rem 0;
}

.nav-sidebar .nav-item {
    margin-bottom: 0.25rem;
}

.nav-sidebar .nav-link {
    color: var(--dark-color);
    padding: 0.75rem 1.5rem;
    border-radius: 0;
    transition: var(--transition);
    text-decoration: none;
}

.nav-sidebar .nav-link:hover {
    background-color: rgba(26, 84, 144, 0.1);
    color: var(--primary-color);
}

.nav-sidebar .nav-link.active {
    background-color: var(--primary-color);
    color: white;
}

.nav-icon {
    width: 1.25rem;
    margin-right: 0.75rem;
    text-align: center;
}

.collapse-icon {
    font-size: 0.75rem;
    transition: transform 0.3s ease;
}

.nav-link[aria-expanded="true"] .collapse-icon {
    transform: rotate(90deg);
}

.nav-submenu {
    background-color: rgba(0, 0, 0, 0.02);
    padding: 0.5rem 0;
}

.nav-submenu .nav-link {
    padding: 0.5rem 1.5rem 0.5rem 3.5rem;
    font-size: 0.9rem;
}

/* Notification dropdown */
.notification-dropdown {
    width: 350px;
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    border-bottom: 1px solid #f8f9fa;
    padding: 0.75rem 1rem;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item:last-child {
    border-bottom: none;
}

/* Mobile styles */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        width: var(--sidebar-width);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .content-wrapper {
        margin-left: 0;
    }
}

/* Overlay pour mobile */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1019;
}

.sidebar-overlay.show {
    display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
    const mainContent = document.getElementById('mainContent');
    
    // Toggle sidebar on mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            
            // Add/remove overlay
            let overlay = document.querySelector('.sidebar-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                document.body.appendChild(overlay);
                
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }
            
            overlay.classList.toggle('show');
        });
    }
    
    // Collapse sidebar
    if (sidebarCollapseBtn) {
        sidebarCollapseBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.classList.toggle('sidebar-collapsed');
            }
            
            // Rotate icon
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.replace('fa-angle-left', 'fa-angle-right');
            } else {
                icon.classList.replace('fa-angle-right', 'fa-angle-left');
            }
            
            // Save state
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
    
    // Restore sidebar state
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        if (mainContent) {
            mainContent.classList.add('sidebar-collapsed');
        }
        const icon = sidebarCollapseBtn?.querySelector('i');
        if (icon) {
            icon.classList.replace('fa-angle-left', 'fa-angle-right');
        }
    }
    
    // Highlight active nav item
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-sidebar .nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href !== '#' && currentPath.includes(href)) {
            link.classList.add('active');
            
            // Expand parent if it's a submenu
            const collapse = link.closest('.collapse');
            if (collapse) {
                collapse.classList.add('show');
                const toggleBtn = document.querySelector(`[href="#${collapse.id}"]`);
                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-expanded', 'true');
                }
            }
        }
    });
    
    // Auto-close mobile sidebar on link click
    document.querySelectorAll('.nav-sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('show');
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) {
                    overlay.classList.remove('show');
                }
            }
        });
    });
});

// Functions for notifications
function markNotificationRead(notificationId) {
    if (!window.APP_CONFIG || !window.APP_CONFIG.csrfToken) {
        console.error('Configuration manquante pour les notifications');
        return;
    }
    
    fetch('api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.APP_CONFIG.csrfToken
        },
        body: JSON.stringify({
            action: 'mark_read',
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update notification item
            const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.add('read');
            }
            
            // Update badge
            updateNotificationBadge();
        }
    })
    .catch(console.error);
}

function markAllNotificationsRead() {
    if (!window.APP_CONFIG || !window.APP_CONFIG.csrfToken) {
        console.error('Configuration manquante pour les notifications');}}