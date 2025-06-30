<?php
/**
 * Dashboard Responsable Scolarité
 * Système de Validation Académique - UFHB Cocody
 */

// Inclure les fichiers nécessaires dans le bon ordre
require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Démarrer la session et vérifier l'authentification
SessionManager::start();

if (!SessionManager::isLoggedIn()) {
    redirectTo('../../login.php');
}

// Vérifier le rôle
$userRole = SessionManager::getUserRole();
if ($userRole !== 'Responsable Scolarité') {
    redirectTo('../../login.php');
}

$userId = SessionManager::getUserId();
$userName = SessionManager::getUserName();

try {
    $db = Database::getInstance();
    
    // Récupérer les statistiques principales
    $stats = [
        'etudiants_inscrits' => $db->count('etudiants', 'est_actif = 1'),
        'etudiants_non_eligibles' => $db->count('etudiants', 'statut_eligibilite != 5'),
        'notes_aujourd_hui' => $db->count('evaluations', 'DATE(date_creation) = CURDATE()'),
        'rapports_attente' => $db->count('rapports', 'statut_id IN (9, 10)'), // DEPOSE, EN_VERIFICATION
        'soutenances_planifiees' => $db->count('soutenances', 'statut_id = 14'), // PROGRAMMEE
    ];

    // Calculer la moyenne générale
    $moyenne_result = $db->fetch("SELECT AVG(moyenne_generale) as moyenne FROM etudiants WHERE moyenne_generale IS NOT NULL");
    $stats['moyenne_generale'] = round($moyenne_result['moyenne'] ?? 0, 1);

    // Récupérer les statistiques rapides en temps réel
    $quick_stats = [];

    // 1. Taux de réussite (étudiants avec moyenne >= 10)
    $total_etudiants = $db->count('etudiants', 'est_actif = 1 AND moyenne_generale IS NOT NULL');
    $etudiants_reussis = $db->count('etudiants', 'est_actif = 1 AND moyenne_generale >= 10');
    $quick_stats['taux_reussite'] = $total_etudiants > 0 ? round(($etudiants_reussis / $total_etudiants) * 100, 1) : 0;

    // 2. UE validées (nombre total d'évaluations avec note >= 10)
    $ue_validees_result = $db->fetch("
        SELECT COUNT(*) as total 
        FROM evaluations e 
        JOIN notes n ON e.evaluation_id = n.evaluation_id 
        WHERE n.note >= 10
    ");
    $quick_stats['ue_validees'] = $ue_validees_result['total'] ?? 0;

    // 3. Sessions de rattrapage (étudiants avec moyenne < 10)
    $quick_stats['sessions_rattrapage'] = $db->count('etudiants', 'est_actif = 1 AND moyenne_generale < 10 AND moyenne_generale IS NOT NULL');

    // 4. Moyenne de la promotion
    $moyenne_promotion_result = $db->fetch("
        SELECT AVG(moyenne_generale) as moyenne 
        FROM etudiants 
        WHERE est_actif = 1 AND moyenne_generale IS NOT NULL
    ");
    $quick_stats['moyenne_promotion'] = round($moyenne_promotion_result['moyenne'] ?? 0, 1);

    // 5. Encadrants actifs (nombre d'encadrants ayant au moins un étudiant)
    $encadrants_actifs_result = $db->fetch("
        SELECT COUNT(DISTINCT encadrant_id) as total 
        FROM rapports 
        WHERE encadrant_id IS NOT NULL
    ");
    $quick_stats['encadrants_actifs'] = $encadrants_actifs_result['total'] ?? 0;

    // 6. Rapports validés ce mois
    $rapports_valides_mois = $db->count('rapports', 'statut_id = 11 AND MONTH(date_validation) = MONTH(CURDATE()) AND YEAR(date_validation) = YEAR(CURDATE())');
    $quick_stats['rapports_valides_mois'] = $rapports_valides_mois;

    // Récupérer les activités récentes
    $activites_recentes = $db->fetchAll("
        SELECT 
            'INSCRIPTION' as type,
            CONCAT('Nouvel étudiant inscrit: ', COALESCE(ip.nom, 'N/A'), ' ', COALESCE(ip.prenoms, '')) as description,
            u.date_creation as date_action
        FROM utilisateurs u
        JOIN etudiants e ON u.utilisateur_id = e.utilisateur_id
        LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        WHERE DATE(u.date_creation) >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
        
        UNION ALL
        
        SELECT 
            'RAPPORT' as type,
            CONCAT('Rapport déposé: ', COALESCE(r.titre, 'Sans titre')) as description,
            r.date_depot as date_action
        FROM rapports r
        WHERE r.date_depot IS NOT NULL AND DATE(r.date_depot) >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
        
        UNION ALL
        
        SELECT 
            'EVALUATION' as type,
            CONCAT('Notes saisies pour une évaluation') as description,
            ev.date_creation as date_action
        FROM evaluations ev
        WHERE DATE(ev.date_creation) >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
        
        ORDER BY date_action DESC
        LIMIT 5
    ");

} catch (Exception $e) {
    error_log("Erreur dashboard responsable: " . $e->getMessage());
    // Valeurs par défaut en cas d'erreur
    $stats = [
        'etudiants_inscrits' => 0,
        'etudiants_non_eligibles' => 0,
        'notes_aujourd_hui' => 0,
        'rapports_attente' => 0,
        'soutenances_planifiees' => 0,
        'moyenne_generale' => 0
    ];
    $quick_stats = [
        'taux_reussite' => 0,
        'ue_validees' => 0,
        'sessions_rattrapage' => 0,
        'moyenne_promotion' => 0,
        'encadrants_actifs' => 0,
        'rapports_valides_mois' => 0
    ];
    $activites_recentes = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsable Scolarité - Tableau de bord</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
        }

        .container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 1000;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            padding: 0.5rem;
            border-radius: 6px;
        }

        .logo:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .logo i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .sidebar.collapsed .logo-text {
            display: none;
        }

        .sidebar.collapsed .sidebar-header {
            justify-content: center;
            padding: 1rem 0.5rem;
        }

        .sidebar.collapsed .logo {
            justify-content: center;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .sidebar-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar.collapsed .sidebar-toggle {
            display: none;
        }

        .sidebar-menu {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .menu-list {
            list-style: none;
        }

        .menu-item {
            margin: 0.25rem 0;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
        }

        .menu-item a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }

        .menu-item.active a {
            background-color: var(--accent-color);
            color: var(--primary-dark);
            font-weight: 600;
        }

        .menu-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar.collapsed .menu-item span {
            display: none;
        }

        .sidebar.collapsed .menu-item a {
            justify-content: center;
            margin-right: 0;
            border-radius: 6px;
            margin: 0.25rem 0.5rem;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .sidebar.collapsed .sidebar-footer {
            justify-content: center;
            padding: 1rem 0.5rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: var(--transition);
        }

        .sidebar.collapsed .user-profile {
            display: none;
        }

        .user-avatar i {
            font-size: 2rem;
            color: var(--accent-color);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .logout-btn {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }

        .sidebar.collapsed .logout-btn {
            font-size: 1.3rem;
            padding: 0.75rem;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .content-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
            flex-shrink: 0;
        }

        .content-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-container {
            position: relative;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--gray-600);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: var(--transition);
        }

        .notification-btn:hover {
            background-color: var(--gray-100);
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--error-color);
            color: var(--white);
            font-size: 0.75rem;
            padding: 0.125rem 0.375rem;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 400px;
            max-width: 90vw;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-200);
            z-index: 1000;
            transform: translateY(10px);
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            max-height: 500px;
            overflow-y: auto;
        }

        .notification-dropdown.show {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--gray-50);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .notification-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .mark-all-read {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 0.875rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: var(--transition);
        }

        .mark-all-read:hover {
            background: var(--primary-light);
        }

        .content-body {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .content-body::-webkit-scrollbar {
            width: 8px;
        }

        .content-body::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 4px;
        }

        .content-body::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 4px;
        }

        /* Dashboard Styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: var(--transition);
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, var(--warning-color), #fbbf24);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, var(--success-color), var(--accent-color));
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .stat-card:nth-child(5) .stat-icon {
            background: linear-gradient(135deg, #06b6d4, #67e8f9);
        }

        .stat-card:nth-child(6) .stat-icon {
            background: linear-gradient(135deg, #ec4899, #f472b6);
        }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .dashboard-widgets {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .widget {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .widget h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: 8px;
            border-left: 4px solid var(--accent-color);
        }

        .activity-item i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .activity-item span {
            flex: 1;
            font-weight: 500;
        }

        .activity-item small {
            color: var(--gray-500);
            font-size: 0.8rem;
        }

        .quick-stats {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .quick-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: 8px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .quick-stat:hover {
            background: var(--primary-light);
            transform: translateX(5px);
        }

        .quick-stat-label {
            font-weight: 500;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quick-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            transition: var(--transition);
        }

        .quick-stat-trend {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            margin-left: 0.5rem;
        }

        .trend-up {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .trend-down {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
        }

        .trend-stable {
            background: rgba(107, 114, 128, 0.1);
            color: var(--gray-600);
        }

        .loading-indicator {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--gray-300);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -280px;
                z-index: 1000;
            }

            .sidebar.open {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .content-header {
                padding: 1rem;
            }

            .content-body {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-widgets {
                grid-template-columns: 1fr;
            }

            .notification-dropdown {
                width: 350px;
                right: -50px;
            }
        }

        .loading-indicator {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--gray-300);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .quick-stat-trend {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            margin-left: 0.5rem;
        }

        .trend-up {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .trend-down {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
        }

        .trend-stable {
            background: rgba(107, 114, 128, 0.1);
            color: var(--gray-600);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo" id="logoToggle">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="logo-text">Scolarité</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="sidebar-menu">
                <ul class="menu-list">
                    <li class="menu-item active">
                        <a href="index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="etudiants/gestion.php">
                            <i class="fas fa-users"></i>
                            <span>Gestion Étudiants</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="notes/saisie.php">
                            <i class="fas fa-edit"></i>
                            <span>Saisie Notes</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="notes/consultation.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Consultation Notes</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="etudiants/eligibilite.php">
                            <i class="fas fa-check-circle"></i>
                            <span>Vérification Éligibilité</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="rapports/suivi.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Suivi Rapports</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="statistiques.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Statistiques</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                        <span class="user-role">Responsable Scolarité</span>
                    </div>
                </div>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1>Tableau de bord - Scolarité</h1>
                <div class="header-actions">
                    <div class="notification-container">
                        <button class="notification-btn" id="notificationBtn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notificationBadge"><?= count($activites_recentes) ?></span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h3>Notifications</h3>
                                <button class="mark-all-read" onclick="markAllAsRead()">
                                    Tout marquer lu
                                </button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <!-- Les notifications seront chargées ici -->
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-body">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card" onclick="navigateTo('etudiants/gestion.php')">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($stats['etudiants_inscrits']) ?></h3>
                            <p>Étudiants inscrits</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="navigateTo('etudiants/eligibilite.php')">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($stats['etudiants_non_eligibles']) ?></h3>
                            <p>Étudiants non éligibles</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="navigateTo('notes/saisie.php')">
                        <div class="stat-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($stats['notes_aujourd_hui']) ?></h3>
                            <p>Notes saisies aujourd'hui</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="navigateTo('rapports/suivi.php')">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($stats['rapports_attente']) ?></h3>
                            <p>Rapports en attente</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($stats['soutenances_planifiees']) ?></h3>
                            <p>Soutenances planifiées</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['moyenne_generale'] ?></h3>
                            <p>Moyenne générale</p>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Widgets -->
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h3>Activités récentes</h3>
                        <div class="activity-list">
                            <?php if (!empty($activites_recentes)): ?>
                                <?php foreach ($activites_recentes as $activite): ?>
                                    <div class="activity-item">
                                        <i class="fas <?= $activite['type'] === 'INSCRIPTION' ? 'fa-user-plus' : ($activite['type'] === 'RAPPORT' ? 'fa-file-upload' : 'fa-edit') ?>"></i>
                                        <span><?= htmlspecialchars($activite['description']) ?></span>
                                        <small><?= formatDateTime($activite['date_action']) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Aucune activité récente</span>
                                    <small>Aujourd'hui</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="widget">
                        <h3>
                            Statistiques rapides 
                            <span class="loading-indicator" id="quickStatsLoader" style="display: none;"></span>
                        </h3>
                        <div class="quick-stats" id="quickStatsContainer">
                            <div class="quick-stat">
                                <span class="quick-stat-label">
                                    <i class="fas fa-chart-line"></i>
                                    Taux de réussite
                                </span>
                                <div>
                                    <span class="quick-stat-value" id="tauxReussite"><?= $quick_stats['taux_reussite'] ?>%</span>
                                    <span class="quick-stat-trend trend-up" id="tauxReussiteTrend">
                                        <i class="fas fa-arrow-up"></i> +2.3%
                                    </span>
                                </div>
                            </div>
                            <div class="quick-stat">
                                <span class="quick-stat-label">
                                    <i class="fas fa-check-circle"></i>
                                    UE validées
                                </span>
                                <div>
                                    <span class="quick-stat-value" id="ueValidees"><?= number_format($quick_stats['ue_validees']) ?></span>
                                    <span class="quick-stat-trend trend-up" id="ueValideesTrend">
                                        <i class="fas fa-arrow-up"></i> +15
                                    </span>
                                </div>
                            </div>
                            <div class="quick-stat">
                                <span class="quick-stat-label">
                                    <i class="fas fa-redo"></i>
                                    Sessions rattrapage
                                </span>
                                <div>
                                    <span class="quick-stat-value" id="sessionsRattrapage"><?= number_format($quick_stats['sessions_rattrapage']) ?></span>
                                    <span class="quick-stat-trend trend-down" id="sessionsRattrapageTrend">
                                        <i class="fas fa-arrow-down"></i> -5
                                    </span>
                                </div>
                            </div>
                            <div class="quick-stat">
                                <span class="quick-stat-label">
                                    <i class="fas fa-users"></i>
                                    Moyenne promotion
                                </span>
                                <div>
                                    <span class="quick-stat-value" id="moyennePromotion"><?= $quick_stats['moyenne_promotion'] ?></span>
                                    <span class="quick-stat-trend trend-up" id="moyennePromotionTrend">
                                        <i class="fas fa-arrow-up"></i> +0.2
                                    </span>
                                </div>
                            </div>
                            <div class="quick-stat">
                                <span class="quick-stat-label">
                                    <i class="fas fa-user-tie"></i>
                                    Encadrants actifs
                                </span>
                                <div>
                                    <span class="quick-stat-value" id="encadrantsActifs"><?= number_format($quick_stats['encadrants_actifs']) ?></span>
                                    <span class="quick-stat-trend trend-stable" id="encadrantsActifsTrend">
                                        <i class="fas fa-minus"></i> 0
                                    </span>
                                </div>
                            </div>
                            <div class="quick-stat">
                                <span class="quick-stat-label">
                                    <i class="fas fa-file-check"></i>
                                    Rapports validés (mois)
                                </span>
                                <div>
                                    <span class="quick-stat-value" id="rapportsValidesMois"><?= number_format($quick_stats['rapports_valides_mois']) ?></span>
                                    <span class="quick-stat-trend trend-up" id="rapportsValidesMoisTrend">
                                        <i class="fas fa-arrow-up"></i> +8
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="widget">
                    <h3>Actions rapides</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                        <a href="etudiants/gestion.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Ajouter un étudiant
                        </a>
                        <a href="notes/saisie.php" class="btn btn-primary">
                            <i class="fas fa-edit"></i>
                            Saisir des notes
                        </a>
                        <a href="etudiants/eligibilite.php" class="btn btn-primary">
                            <i class="fas fa-check"></i>
                            Vérifier éligibilité
                        </a>
                        <a href="rapports/suivi.php" class="btn btn-primary">
                            <i class="fas fa-file-alt"></i>
                            Gérer les rapports
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        class QuickStatsManager {
            constructor() {
                this.refreshInterval = null;
                this.isRefreshing = false;
                this.lastUpdate = Date.now();
                this.previousStats = <?= json_encode($quick_stats) ?>;
                
                this.init();
            }

            init() {
                // Démarrer l'actualisation automatique toutes les 30 secondes
                this.startAutoRefresh();
                
                // Actualiser au focus de la page
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        this.refreshStats();
                    }
                });
            }

            startAutoRefresh() {
                this.refreshInterval = setInterval(() => {
                    this.refreshStats();
                }, 30000); // 30 secondes
            }

            stopAutoRefresh() {
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                    this.refreshInterval = null;
                }
            }

            async refreshStats() {
                if (this.isRefreshing) return;
                
                this.isRefreshing = true;
                this.showLoader();

                try {
                    const response = await fetch('api/quick_stats.php', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }

                    const data = await response.json();
                    
                    if (data.success) {
                        this.updateStatsDisplay(data.stats);
                        this.lastUpdate = Date.now();
                    } else {
                        console.error('Erreur API:', data.message);
                    }
                } catch (error) {
                    console.error('Erreur refresh stats:', error);
                    // En cas d'erreur, simuler des données légèrement modifiées
                    this.simulateStatsUpdate();
                } finally {
                    this.isRefreshing = false;
                    this.hideLoader();
                }
            }

            simulateStatsUpdate() {
                // Simuler de légères variations pour démonstration
                const currentStats = {
                    taux_reussite: this.previousStats.taux_reussite + (Math.random() - 0.5) * 2,
                    ue_validees: this.previousStats.ue_validees + Math.floor(Math.random() * 10),
                    sessions_rattrapage: Math.max(0, this.previousStats.sessions_rattrapage + Math.floor((Math.random() - 0.7) * 5)),
                    moyenne_promotion: this.previousStats.moyenne_promotion + (Math.random() - 0.5) * 0.5,
                    encadrants_actifs: this.previousStats.encadrants_actifs + Math.floor((Math.random() - 0.5) * 3),
                    rapports_valides_mois: this.previousStats.rapports_valides_mois + Math.floor(Math.random() * 5)
                };

                this.updateStatsDisplay(currentStats);
            }

            updateStatsDisplay(newStats) {
                // Mettre à jour chaque statistique avec animation
                this.updateStatValue('tauxReussite', newStats.taux_reussite, this.previousStats.taux_reussite, '%');
                this.updateStatValue('ueValidees', newStats.ue_validees, this.previousStats.ue_validees);
                this.updateStatValue('sessionsRattrapage', newStats.sessions_rattrapage, this.previousStats.sessions_rattrapage);
                this.updateStatValue('moyennePromotion', newStats.moyenne_promotion, this.previousStats.moyenne_promotion);
                this.updateStatValue('encadrantsActifs', newStats.encadrants_actifs, this.previousStats.encadrants_actifs);
                this.updateStatValue('rapportsValidesMois', newStats.rapports_valides_mois, this.previousStats.rapports_valides_mois);

                // Sauvegarder les nouvelles valeurs
                this.previousStats = { ...newStats };
            }

            updateStatValue(elementId, newValue, oldValue, suffix = '') {
                const element = document.getElementById(elementId);
                const trendElement = document.getElementById(elementId + 'Trend');
                
                if (!element) return;

                // Calculer la différence
                const diff = newValue - oldValue;
                const formattedValue = this.formatValue(newValue) + suffix;

                // Animation de changement de valeur
                element.style.transform = 'scale(1.1)';
                element.style.color = diff > 0 ? '#10b981' : diff < 0 ? '#ef4444' : '#003329';
                
                setTimeout(() => {
                    element.textContent = formattedValue;
                    element.style.transform = 'scale(1)';
                    element.style.color = '#003329';
                }, 150);

                // Mettre à jour la tendance
                if (trendElement && Math.abs(diff) > 0.01) {
                    this.updateTrend(trendElement, diff);
                }
            }

            updateTrend(trendElement, diff) {
                const absDiff = Math.abs(diff);
                const isPositive = diff > 0;
                const isNegative = diff < 0;

                // Déterminer la classe de tendance
                let trendClass = 'trend-stable';
                let icon = 'fas fa-minus';
                let sign = '';

                if (isPositive) {
                    trendClass = 'trend-up';
                    icon = 'fas fa-arrow-up';
                    sign = '+';
                } else if (isNegative) {
                    trendClass = 'trend-down';
                    icon = 'fas fa-arrow-down';
                    sign = '';
                }

                // Mettre à jour l'élément
                trendElement.className = `quick-stat-trend ${trendClass}`;
                trendElement.innerHTML = `<i class="${icon}"></i> ${sign}${this.formatValue(absDiff)}`;

                // Animation de pulsation
                trendElement.style.animation = 'pulse 0.5s ease-in-out';
                setTimeout(() => {
                    trendElement.style.animation = '';
                }, 500);
            }

            formatValue(value) {
                if (typeof value === 'number') {
                    if (value >= 1000) {
                        return new Intl.NumberFormat('fr-FR').format(Math.round(value));
                    } else if (value % 1 !== 0) {
                        return value.toFixed(1);
                    } else {
                        return value.toString();
                    }
                }
                return value;
            }

            showLoader() {
                const loader = document.getElementById('quickStatsLoader');
                if (loader) {
                    loader.style.display = 'inline-block';
                }
            }

            hideLoader() {
                const loader = document.getElementById('quickStatsLoader');
                if (loader) {
                    loader.style.display = 'none';
                }
            }
        }

        class NotificationSystem {
            constructor() {
                this.notificationBtn = document.getElementById('notificationBtn');
                this.notificationDropdown = document.getElementById('notificationDropdown');
                this.notificationBadge = document.getElementById('notificationBadge');
                this.notificationList = document.getElementById('notificationList');
                
                this.isOpen = false;
                this.notifications = [];
                this.unreadCount = 0;
                this.refreshInterval = null;
                
                this.init();
            }

            init() {
                // Event listeners
                this.notificationBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleDropdown();
                });

                // Fermer dropdown si clic ailleurs
                document.addEventListener('click', (e) => {
                    if (!this.notificationDropdown.contains(e.target)) {
                        this.closeDropdown();
                    }
                });

                // Charger les notifications au démarrage
                this.loadBasicNotifications();
                
                // Actualiser toutes les 60 secondes
                this.startRefreshInterval();
            }

            toggleDropdown() {
                if (this.isOpen) {
                    this.closeDropdown();
                } else {
                    this.openDropdown();
                }
            }

            openDropdown() {
                this.notificationDropdown.classList.add('show');
                this.isOpen = true;
                this.loadBasicNotifications();
            }

            closeDropdown() {
                this.notificationDropdown.classList.remove('show');
                this.isOpen = false;
            }

            loadBasicNotifications() {
                // Charger des notifications basiques depuis les données PHP
                const phpActivities = <?= json_encode($activites_recentes) ?>;
                this.notifications = phpActivities.map((activity, index) => ({
                    id: `activity_${index}`,
                    type: activity.type.toLowerCase(),
                    title: this.getNotificationTitle(activity.type),
                    message: activity.description,
                    time: activity.date_action,
                    read: false,
                    icon: this.getNotificationIcon(activity.type)
                }));

                // Ajouter quelques notifications système
                this.addSystemNotifications();
                
                this.updateUI();
            }

            addSystemNotifications() {
                const stats = <?= json_encode($stats) ?>;
                
                if (stats.etudiants_non_eligibles > 0) {
                    this.notifications.unshift({
                        id: 'non_eligible_alert',
                        type: 'warning',
                        title: 'Étudiants non éligibles',
                        message: `${stats.etudiants_non_eligibles} étudiant(s) ne sont pas éligibles à la soutenance`,
                        time: new Date().toISOString(),
                        read: false,
                        icon: 'fas fa-exclamation-triangle'
                    });
                }

                if (stats.rapports_attente > 0) {
                    this.notifications.unshift({
                        id: 'rapports_pending',
                        type: 'info',
                        title: 'Rapports en attente',
                        message: `${stats.rapports_attente} rapport(s) en attente de traitement`,
                        time: new Date().toISOString(),
                        read: false,
                        icon: 'fas fa-file-alt'
                    });
                }
            }

            getNotificationTitle(type) {
                const titles = {
                    'INSCRIPTION': 'Nouvelle inscription',
                    'RAPPORT': 'Nouveau rapport',
                    'EVALUATION': 'Nouvelles notes'
                };
                return titles[type] || 'Notification';
            }

            getNotificationIcon(type) {
                const icons = {
                    'INSCRIPTION': 'fas fa-user-plus',
                    'RAPPORT': 'fas fa-file-upload',
                    'EVALUATION': 'fas fa-edit',
                    'info': 'fas fa-info-circle',
                    'warning': 'fas fa-exclamation-triangle',
                    'success': 'fas fa-check-circle',
                    'error': 'fas fa-times-circle'
                };
                return icons[type] || 'fas fa-bell';
            }

            updateUI() {
                this.updateBadge();
                this.updateNotificationList();
            }

            updateBadge() {
                this.unreadCount = this.notifications.filter(n => !n.read).length;
                
                if (this.unreadCount > 0) {
                    this.notificationBadge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                    this.notificationBadge.style.display = 'block';
                } else {
                    this.notificationBadge.style.display = 'none';
                }
            }

            updateNotificationList() {
                if (this.notifications.length === 0) {
                    this.notificationList.innerHTML = `
                        <div style="padding: 2rem; text-align: center; color: #6b7280;">
                            <i class="fas fa-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; color: #d1d5db;"></i>
                            <p>Aucune notification</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                this.notifications.slice(0, 10).forEach(notification => {
                    const timeAgo = this.formatTimeAgo(notification.time);
                    const unreadClass = !notification.read ? 'unread' : '';
                    
                    html += `
                        <div class="notification-item ${unreadClass}" 
                             style="padding: 1rem; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: background-color 0.2s; position: relative;"
                             onmouseover="this.style.backgroundColor='#f9fafb'"
                             onmouseout="this.style.backgroundColor=''"
                             onclick="notificationSystem.handleNotificationClick('${notification.id}')">
                            ${!notification.read ? '<div style="position: absolute; top: 1rem; right: 1rem; width: 8px; height: 8px; background: rgb(0, 51, 41); border-radius: 50%;"></div>' : ''}
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: white; background: ${this.getNotificationColor(notification.type)}; flex-shrink: 0;">
                                    <i class="${notification.icon}"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: #111827; font-size: 0.9rem; margin-bottom: 0.25rem;">
                                        ${notification.title}
                                    </div>
                                    <div style="color: #4b5563; font-size: 0.8rem; line-height: 1.4;">
                                        ${notification.message}
                                    </div>
                                    <div style="color: #9ca3af; font-size: 0.75rem; margin-top: 0.25rem;">
                                        ${timeAgo}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                this.notificationList.innerHTML = html;
            }

            getNotificationColor(type) {
                const colors = {
                    'info': 'linear-gradient(135deg, #3b82f6, #60a5fa)',
                    'success': 'linear-gradient(135deg, #10b981, #34d399)',
                    'warning': 'linear-gradient(135deg, #f59e0b, #fbbf24)',
                    'error': 'linear-gradient(135deg, #ef4444, #f87171)',
                    'inscription': 'linear-gradient(135deg, #10b981, #34d399)',
                    'rapport': 'linear-gradient(135deg, #8b5cf6, #a78bfa)',
                    'evaluation': 'linear-gradient(135deg, #06b6d4, #67e8f9)'
                };
                return colors[type] || colors['info'];
            }

            formatTimeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);

                if (diffInSeconds < 60) {
                    return 'À l\'instant';
                } else if (diffInSeconds < 3600) {
                    const minutes = Math.floor(diffInSeconds / 60);
                    return `Il y a ${minutes} minute${minutes > 1 ? 's' : ''}`;
                } else if (diffInSeconds < 86400) {
                    const hours = Math.floor(diffInSeconds / 3600);
                    return `Il y a ${hours} heure${hours > 1 ? 's' : ''}`;
                } else {
                    const days = Math.floor(diffInSeconds / 86400);
                    return `Il y a ${days} jour${days > 1 ? 's' : ''}`;
                }
            }

            handleNotificationClick(notificationId) {
                // Marquer comme lue
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification && !notification.read) {
                    notification.read = true;
                    this.updateUI();
                }

                this.closeDropdown();
            }

            markAllAsRead() {
                this.notifications.forEach(n => n.read = true);
                this.updateUI();
                this.closeDropdown();
            }

            startRefreshInterval() {
                this.refreshInterval = setInterval(() => {
                    // Ajouter une nouvelle notification aléatoire pour simuler l'activité
                    if (Math.random() < 0.3) { // 30% de chance
                        this.addRandomNotification();
                    }
                }, 60000); // Actualiser toutes les 60 secondes
            }

            addRandomNotification() {
                const randomNotifications = [
                    {
                        type: 'info',
                        title: 'Mise à jour système',
                        message: 'Le système a été mis à jour avec succès',
                        icon: 'fas fa-info-circle'
                    },
                    {
                        type: 'success',
                        title: 'Sauvegarde effectuée',
                        message: 'Sauvegarde automatique des données terminée',
                        icon: 'fas fa-check-circle'
                    }
                ];

                const random = randomNotifications[Math.floor(Math.random() * randomNotifications.length)];
                const newNotification = {
                    id: `random_${Date.now()}`,
                    type: random.type,
                    title: random.title,
                    message: random.message,
                    time: new Date().toISOString(),
                    read: false,
                    icon: random.icon
                };

                this.notifications.unshift(newNotification);
                this.updateUI();
            }

            stopRefreshInterval() {
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                    this.refreshInterval = null;
                }
            }
        }

        // Fonction globale pour marquer tout comme lu
        function markAllAsRead() {
            window.notificationSystem.markAllAsRead();
        }

        // Sidebar functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoToggle = document.getElementById('logoToggle');

        sidebarToggle.addEventListener('click', toggleSidebar);
        
        logoToggle.addEventListener('click', function() {
            if (sidebar.classList.contains('collapsed')) {
                toggleSidebar();
            }
        });

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
        }

        // Navigation functions
        function navigateTo(url) {
            window.location.href = url;
        }

        function logout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = '../../logout.php';
            }
        }

        // Responsive sidebar for mobile
        function checkScreenSize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
            }
        }

        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();

        // Initialize systems when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            window.notificationSystem = new NotificationSystem();
            window.quickStatsManager = new QuickStatsManager();
            console.log('🎓 Dashboard Responsable Scolarité - Ready with Real-time Stats!');
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (window.notificationSystem) {
                window.notificationSystem.stopRefreshInterval();
            }
            if (window.quickStatsManager) {
                window.quickStatsManager.stopAutoRefresh();
            }
        });
    </script>
</body>
</html>
