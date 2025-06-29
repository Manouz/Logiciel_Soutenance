<?php

session_start();
// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responsable_scolarite') {
    header('Location: ../../login.php');
    exit;
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
        }

        .quick-stat-label {
            font-weight: 500;
            color: var(--gray-700);
        }

        .quick-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
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
                        <span class="user-name"><?= htmlspecialchars($_SESSION['nom'] ?? 'Responsable') ?></span>
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
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">5</span>
                    </button>
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
                            <h3>1,247</h3>
                            <p>Étudiants inscrits</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="navigateTo('etudiants/eligibilite.php')">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>23</h3>
                            <p>Étudiants non éligibles</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="navigateTo('notes/saisie.php')">
                        <div class="stat-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="stat-info">
                            <h3>156</h3>
                            <p>Notes saisies aujourd'hui</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="navigateTo('rapports/suivi.php')">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3>89</h3>
                            <p>Rapports en attente</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>45</h3>
                            <p>Soutenances planifiées</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3>12.5</h3>
                            <p>Moyenne générale</p>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Widgets -->
                <div class="dashboard-widgets">
                    <div class="widget">
                        <h3>Activités récentes</h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <i class="fas fa-user-plus"></i>
                                <span>Nouvel étudiant inscrit: KOUAME Jean-Baptiste</span>
                                <small>Il y a 15 minutes</small>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-edit"></i>
                                <span>Notes saisies pour UE Algorithmique - Master 1</span>
                                <small>Il y a 1 heure</small>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-file-upload"></i>
                                <span>Rapport déposé par TRAORE Marie-Claire</span>
                                <small>Il y a 2 heures</small>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Soutenance planifiée pour DIALLO Amadou</span>
                                <small>Il y a 3 heures</small>
                            </div>
                            <div class="activity-item">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Alerte: Étudiant non éligible détecté</span>
                                <small>Il y a 4 heures</small>
                            </div>
                        </div>
                    </div>

                    <div class="widget">
                        <h3>Statistiques rapides</h3>
                        <div class="quick-stats">
                            <div class="quick-stat">
                                <span class="quick-stat-label">Taux de réussite</span>
                                <span class="quick-stat-value">87%</span>
                            </div>
                            <div class="quick-stat">
                                <span class="quick-stat-label">UE validées</span>
                                <span class="quick-stat-value">2,456</span>
                            </div>
                            <div class="quick-stat">
                                <span class="quick-stat-label">Sessions rattrapage</span>
                                <span class="quick-stat-value">34</span>
                            </div>
                            <div class="quick-stat">
                                <span class="quick-stat-label">Moyenne promotion</span>
                                <span class="quick-stat-value">12.8</span>
                            </div>
                            <div class="quick-stat">
                                <span class="quick-stat-label">Encadrants actifs</span>
                                <span class="quick-stat-value">67</span>
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

        // Auto-refresh stats every 5 minutes
        setInterval(function() {
            // Refresh statistics without full page reload
            console.log('Refreshing statistics...');
        }, 300000);

        // Responsive sidebar for mobile
        function checkScreenSize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
            }
        }

        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();
    </script>
</body>
</html>