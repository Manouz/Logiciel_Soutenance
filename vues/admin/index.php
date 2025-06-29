<?php
/**
 * Dashboard Administrateur Principal
 * Fichier: pages/admin/index.php
 */

require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Vérification des permissions
requireAuth(ROLE_ADMIN);

$db = Database::getInstance();

try {
    // Statistiques générales du système
    $stats = [
        'total_utilisateurs' => $db->fetch("SELECT COUNT(*) as count FROM utilisateurs WHERE est_actif = 1")['count'],
        'total_etudiants' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM etudiants e 
            INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id 
            WHERE u.est_actif = 1
        ")['count'],
        'total_enseignants' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM enseignants e 
            INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id 
            WHERE u.est_actif = 1
        ")['count'],
        'total_personnel' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM personnel_administratif p 
            INNER JOIN utilisateurs u ON p.utilisateur_id = u.utilisateur_id 
            WHERE u.est_actif = 1
        ")['count'],
        'total_rapports' => $db->fetch("SELECT COUNT(*) as count FROM rapports")['count'],
        'rapports_en_attente' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM rapports 
            WHERE statut_id IN (?, ?)
        ", [RAPPORT_DEPOSE, RAPPORT_EN_VERIFICATION])['count'],
        'soutenances_programmees' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM soutenances 
            WHERE statut_id = ? AND date_prevue >= CURDATE()
        ", [SOUTENANCE_PROGRAMMEE])['count'],
        'utilisateurs_bloques' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM utilisateurs 
            WHERE compte_bloque = 1 OR statut_id = ?
        ", [STATUT_BLOQUE])['count']
    ];
    
    // Activités récentes système
    $activites_recentes = $db->fetchAll("
        SELECT 
            l.type_action,
            l.table_cible,
            l.date_action,
            l.commentaire,
            COALESCE(CONCAT(ip.nom, ' ', ip.prenoms), 'Système') as utilisateur_nom,
            r.nom_role
        FROM logs_audit l
        LEFT JOIN utilisateurs u ON l.utilisateur_id = u.utilisateur_id
        LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        LEFT JOIN roles r ON u.role_id = r.role_id
        ORDER BY l.date_action DESC
        LIMIT 15
    ");
    
    // Statistiques par rôle
    $stats_roles = $db->fetchAll("
        SELECT 
            r.nom_role,
            r.niveau_acces,
            COUNT(u.utilisateur_id) as nombre_utilisateurs,
            COUNT(CASE WHEN u.est_actif = 1 THEN 1 END) as actifs,
            COUNT(CASE WHEN u.compte_bloque = 1 THEN 1 END) as bloques
        FROM roles r
        LEFT JOIN utilisateurs u ON r.role_id = u.role_id
        WHERE r.est_actif = 1
        GROUP BY r.role_id, r.nom_role, r.niveau_acces
        ORDER BY r.niveau_acces DESC
    ");
    
    // Tentatives de connexion récentes
    $tentatives_connexion = $db->fetchAll("
        SELECT 
            email,
            ip_address,
            succes,
            date_tentative,
            raison_echec
        FROM tentativesconnexion
        ORDER BY date_tentative DESC
        LIMIT 10
    ");
    
    // Analyse des erreurs système
    $erreurs_recentes = $db->fetchAll("
        SELECT 
            COUNT(*) as nombre,
            table_cible,
            type_action,
            DATE(date_action) as date_erreur
        FROM logs_audit
        WHERE type_action LIKE '%ERROR%' 
        AND date_action >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY table_cible, type_action, DATE(date_action)
        ORDER BY date_erreur DESC, nombre DESC
        LIMIT 10
    ");
    
    // Performance du système (requêtes, temps de réponse, etc.)
    $performance_stats = [
        'db_size' => $db->fetch("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
            FROM information_schema.tables 
            WHERE table_schema = 'validation_soutenance'
        ")['size_mb'] ?? 0,
        'total_logs' => $db->fetch("SELECT COUNT(*) as count FROM logs_audit")['count'],
        'connexions_24h' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM tentativesconnexion 
            WHERE date_tentative >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
            AND succes = 1
        ")['count']
    ];
    
} catch (Exception $e) {
    error_log("Erreur dashboard admin: " . $e->getMessage());
    $stats = array_fill_keys([
        'total_utilisateurs', 'total_etudiants', 'total_enseignants', 'total_personnel',
        'total_rapports', 'rapports_en_attente', 'soutenances_programmees', 'utilisateurs_bloques'
    ], 0);
    $activites_recentes = [];
    $stats_roles = [];
    $tentatives_connexion = [];
    $erreurs_recentes = [];
    $performance_stats = ['db_size' => 0, 'total_logs' => 0, 'connexions_24h' => 0];
}

$page_title = "Dashboard Administrateur";
$custom_css = ['admin/admin-dashboard.css'];
$custom_js = ['admin/admin-dashboard.js', 'admin/crud-operations.js'];

include '../../includes/header.php';
?>

<div class="admin-dashboard">
    <!-- Header du dashboard -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="dashboard-title">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard Administrateur
                    </h1>
                    <p class="dashboard-subtitle">
                        Supervision complète du système - <?= UNIVERSITY_NAME ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-actions">
                        <button class="btn btn-light btn-sm" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i>
                            Actualiser
                        </button>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-download"></i>
                                Exporter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportStats('pdf')">
                                    <i class="fas fa-file-pdf"></i> Rapport PDF
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportStats('excel')">
                                    <i class="fas fa-file-excel"></i> Fichier Excel
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportLogs()">
                                    <i class="fas fa-clipboard-list"></i> Logs système
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Cartes de statistiques principales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-card-body">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-counter="<?= $stats['total_utilisateurs'] ?>">0</div>
                            <div class="stat-label">Utilisateurs Actifs</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up text-success"></i>
                                <span class="text-success">+12% ce mois</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-footer">
                        <a href="utilisateurs/liste.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Gérer
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card stat-card-success">
                    <div class="stat-card-body">
                        <div class="stat-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-counter="<?= $stats['total_etudiants'] ?>">0</div>
                            <div class="stat-label">Étudiants Master 2</div>
                            <div class="stat-percentage">
                                <?= $stats['total_utilisateurs'] > 0 ? round(($stats['total_etudiants'] / $stats['total_utilisateurs']) * 100, 1) : 0 ?>% du total
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-footer">
                        <a href="etudiants/liste.php" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-list"></i> Voir tous
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card stat-card-info">
                    <div class="stat-card-body">
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-counter="<?= $stats['total_enseignants'] ?>">0</div>
                            <div class="stat-label">Enseignants</div>
                            <div class="stat-detail">
                                <?= $stats['total_personnel'] ?> personnel admin
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-footer">
                        <a href="enseignants/liste.php" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-users-cog"></i> Gérer
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card stat-card-warning">
                    <div class="stat-card-body">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-counter="<?= $stats['rapports_en_attente'] ?>">0</div>
                            <div class="stat-label">Actions Requises</div>
                            <div class="stat-alert">
                                <?php if ($stats['utilisateurs_bloques'] > 0): ?>
                                <span class="text-danger">
                                    <i class="fas fa-user-lock"></i>
                                    <?= $stats['utilisateurs_bloques'] ?> compte(s) bloqué(s)
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-footer">
                        <a href="logs.php" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-clipboard-list"></i> Audit
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de performance système -->
        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="performance-card">
                    <div class="card-header">
                        <h6 class="card-title">
                            <i class="fas fa-database"></i>
                            Performance Base de Données
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="performance-metric">
                            <span class="metric-label">Taille BD</span>
                            <span class="metric-value"><?= $performance_stats['db_size'] ?> MB</span>
                        </div>
                        <div class="performance-metric">
                            <span class="metric-label">Total logs</span>
                            <span class="metric-value"><?= number_format($performance_stats['total_logs']) ?></span>
                        </div>
                        <div class="performance-metric">
                            <span class="metric-label">Connexions 24h</span>
                            <span class="metric-value text-success"><?= $performance_stats['connexions_24h'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="security-card">
                    <div class="card-header">
                        <h6 class="card-title">
                            <i class="fas fa-shield-alt"></i>
                            Sécurité Système
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="security-status">
                            <div class="status-item">
                                <span class="status-indicator bg-success"></span>
                                <span>Firewall actif</span>
                            </div>
                            <div class="status-item">
                                <span class="status-indicator bg-success"></span>
                                <span>SSL/TLS configuré</span>
                            </div>
                            <div class="status-item">
                                <span class="status-indicator bg-warning"></span>
                                <span><?= count($tentatives_connexion) ?> tentatives récentes</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="system-health-card">
                    <div class="card-header">
                        <h6 class="card-title">
                            <i class="fas fa-heartbeat"></i>
                            État du Système
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="health-metric">
                            <div class="health-circle" data-percentage="92">
                                <span class="health-value">92%</span>
                            </div>
                            <span class="health-label">Disponibilité</span>
                        </div>
                        <div class="health-details">
                            <small class="text-success">
                                <i class="fas fa-check"></i> Tous les services fonctionnent
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides administrateur -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="quick-actions-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-rocket"></i>
                            Actions Rapides Administrateur
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <a href="etudiants/ajouter.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-primary">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Nouvel Étudiant</div>
                                        <div class="quick-action-desc">Ajouter un étudiant</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <a href="enseignants/ajouter.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-success">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Nouvel Enseignant</div>
                                        <div class="quick-action-desc">Ajouter un enseignant</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <a href="personnel/ajouter.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-info">
                                        <i class="fas fa-users-cog"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Nouveau Personnel</div>
                                        <div class="quick-action-desc">Ajouter du personnel</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <a href="parametres/configuration.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-warning">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Configuration</div>
                                        <div class="quick-action-desc">Paramètres système</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <a href="logs.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-danger">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Logs d'Audit</div>
                                        <div class="quick-action-desc">Consulter l'historique</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <a href="#" onclick="runSystemMaintenance()" class="quick-action-btn">
                                    <div class="quick-action-icon bg-secondary">
                                        <i class="fas fa-tools"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Maintenance</div>
                                        <div class="quick-action-desc">Optimiser le système</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal en colonnes -->
        <div class="row">
            <!-- Statistiques par rôle -->
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="fas fa-chart-pie"></i>
                            Répartition par Rôles
                        </h5>
                        <span class="badge bg-primary"><?= count($stats_roles) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="roles-stats">
                            <?php foreach ($stats_roles as $role): ?>
                            <div class="role-stat-item">
                                <div class="role-info">
                                    <span class="role-name"><?= htmlspecialchars($role['nom_role']) ?></span>
                                    <div class="role-badges">
                                        <span class="role-count total"><?= $role['nombre_utilisateurs'] ?></span>
                                        <?php if ($role['actifs'] != $role['nombre_utilisateurs']): ?>
                                        <span class="role-count actifs"><?= $role['actifs'] ?> actifs</span>
                                        <?php endif; ?>
                                        <?php if ($role['bloques'] > 0): ?>
                                        <span class="role-count bloques"><?= $role['bloques'] ?> bloqués</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="role-progress">
                                    <div class="progress">
                                        <div class="progress-bar bg-<?= $role['niveau_acces'] >= 8 ? 'danger' : ($role['niveau_acces'] >= 6 ? 'warning' : 'info') ?>" 
                                             style="width: <?= $stats['total_utilisateurs'] > 0 ? ($role['nombre_utilisateurs'] / $stats['total_utilisateurs'] * 100) : 0 ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button class="btn btn-sm btn-outline-primary" onclick="showRoleManagement()">
                                <i class="fas fa-users-cog"></i> Gérer les rôles
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activités récentes -->
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="fas fa-history"></i>
                            Activités Récentes
                        </h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshActivities()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="activity-timeline" id="activityTimeline">
                            <?php foreach (array_slice($activites_recentes, 0, 10) as $activite): ?>
                            <div class="activity-item">
                                <div class="activity-icon <?= getActivityIconClass($activite['type_action']) ?>">
                                    <i class="fas fa-<?= getActivityIcon($activite['type_action']) ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">
                                        <strong><?= htmlspecialchars($activite['utilisateur_nom']) ?></strong>
                                        <?= getActivityText($activite['type_action'], $activite['table_cible']) ?>
                                        <?php if (!empty($activite['nom_role'])): ?>
                                        <span class="role-badge"><?= htmlspecialchars($activite['nom_role']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-time">
                                        <i class="fas fa-clock"></i>
                                        <?= timeAgo($activite['date_action']) ?>
                                    </div>
                                    <?php if (!empty($activite['commentaire'])): ?>
                                    <div class="activity-comment">
                                        <?= htmlspecialchars(substr($activite['commentaire'], 0, 100)) ?>
                                        <?= strlen($activite['commentaire']) > 100 ? '...' : '' ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="logs.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-list"></i> Voir tous les logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tentatives de connexion et sécurité -->
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-shield-alt"></i>
                            Sécurité et Connexions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="security-section">
                            <h6 class="section-title">Dernières Connexions</h6>
                            <div class="connection-list">
                                <?php foreach (array_slice($tentatives_connexion, 0, 8) as $tentative): ?>
                                <div class="connection-item">
                                    <div class="connection-status">
                                        <?php if ($tentative['succes']): ?>
                                            <i class="fas fa-check-circle text-success"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle text-danger"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="connection-details">
                                        <div class="connection-email">
                                            <?= htmlspecialchars($tentative['email']) ?>
                                        </div>
                                        <div class="connection-info">
                                            <span class="connection-ip"><?= htmlspecialchars($tentative['ip_address']) ?></span>
                                            <span class="connection-time"><?= timeAgo($tentative['date_tentative']) ?></span>
                                        </div>
                                        <?php if (!$tentative['succes'] && $tentative['raison_echec']): ?>
                                        <div class="connection-error">
                                            <?= htmlspecialchars($tentative['raison_echec']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($erreurs_recentes)): ?>
                        <div class="security-section mt-3">
                            <h6 class="section-title">Erreurs Récentes</h6>
                            <div class="error-list">
                                <?php foreach (array_slice($erreurs_recentes, 0, 5) as $erreur): ?>
                                <div class="error-item">
                                    <span class="error-count"><?= $erreur['nombre'] ?></span>
                                    <span class="error-details">
                                        <?= htmlspecialchars($erreur['table_cible']) ?> - 
                                        <?= htmlspecialchars($erreur['type_action']) ?>
                                    </span>
                                    <span class="error-date"><?= formatDate($erreur['date_erreur']) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques et analyses -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Analyses et Tendances
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="systemAnalyticsChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-doughnut-bite"></i>
                            Répartition Utilisateurs
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="usersDistributionChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Fonctions utilitaires pour l'affichage
 */
function getActivityIcon($action) {
    $icons = [
        'CREATE' => 'plus-circle',
        'UPDATE' => 'edit',
        'DELETE' => 'trash',
        'LOGIN' => 'sign-in-alt',
        'LOGOUT' => 'sign-out-alt',
        'BLOCK' => 'lock',
        'UNBLOCK' => 'unlock',
        'ERROR' => 'exclamation-triangle',
        'WARNING' => 'exclamation-circle'
    ];
    return $icons[$action] ?? 'info-circle';
}

function getActivityIconClass($action) {
    $classes = [
        'CREATE' => 'success',
        'UPDATE' => 'primary',
        'DELETE' => 'danger',
        'LOGIN' => 'info',
        'LOGOUT' => 'secondary',
        'BLOCK' => 'warning',
        'UNBLOCK' => 'success',
        'ERROR' => 'danger',
        'WARNING' => 'warning'
    ];
    return $classes[$action] ?? 'info';
}

function getActivityText($action, $table) {
    $actions = [
        'CREATE' => 'a créé un enregistrement dans',
        'UPDATE' => 'a modifié un enregistrement dans',
        'DELETE' => 'a supprimé un enregistrement de',
        'LOGIN' => 's\'est connecté au système',
        'LOGOUT' => 's\'est déconnecté du système',
        'BLOCK' => 'a bloqué un utilisateur',
        'UNBLOCK' => 'a débloqué un utilisateur'
    ];
    
    $text = $actions[$action] ?? 'a effectué une action sur';
    return in_array($action, ['LOGIN', 'LOGOUT', 'BLOCK', 'UNBLOCK']) ? $text : $text . ' ' . $table;
}

include '../../includes/footer.php';
?>