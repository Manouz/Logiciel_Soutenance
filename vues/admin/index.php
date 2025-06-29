<?php 
include "../../config/database.php";
include "../../classes/Etudiant.php";
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Gestion des Soutenances</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<style>
/* Tous vos styles CSS existants */
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

html,
body {
    height: 100%;
    overflow: hidden;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--gray-50);
    color: var(--gray-900);
    line-height: 1.6;
}

.admin-container {
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
    padding: 0.3rem 1rem 0.5rem 1rem;
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

.sidebar-menu::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
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
    background: rgba(255, 255, 255, 0.3);
    padding: 1.5rem 2rem;
    border-bottom: 1px solid rgba(229, 231, 235, 0.3);
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
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

.user-menu-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--gray-600);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: var(--transition);
}

.user-menu-btn:hover {
    background-color: var(--gray-100);
    color: var(--primary-color);
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

.content-body::-webkit-scrollbar-thumb:hover {
    background: var(--gray-400);
}

.content-section {
    display: none;
}

.content-section.active {
    display: block;
}

/* Dashboard Styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
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

.stat-card .stat-icon {
    background: linear-gradient(200deg, var(--primary-color), var(--secondary-color));
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

/* Settings Grid */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.settings-card {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid var(--gray-200);
    cursor: pointer;
    position: relative;
}

.settings-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
}

.settings-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.settings-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.settings-card-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--white);
}

/* Couleurs des icônes */
.icon-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.icon-green { background: linear-gradient(135deg, #10b981, #34d399); }
.icon-purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
.icon-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
.icon-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
.icon-yellow { background: linear-gradient(135deg, #eab308, #ca8a04); }
.icon-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.icon-pink { background: linear-gradient(135deg, #ec4899, #db2777); }
.icon-teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }
.icon-cyan { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.icon-emerald { background: linear-gradient(135deg, #10b981, #059669); }
.icon-violet { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.icon-rose { background: linear-gradient(135deg, #f43f5e, #e11d48); }
.icon-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
.icon-slate { background: linear-gradient(135deg, #64748b, #475569); }
.icon-stone { background: linear-gradient(135deg, #78716c, #57534e); }

.settings-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
    transition: var(--transition);
}

.settings-card:hover .settings-card-title {
    color: var(--secondary-color);
}

.settings-card-description {
    color: var(--gray-600);
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.btn-configure {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: var(--white);
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
    cursor: pointer;
}

.btn-configure:hover {
    background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
    transform: translateY(-1px);
    box-shadow: var(--shadow-lg);
}

/* Search Section */
.search-section {
    margin-bottom: 2rem;
}

.search-container {
    position: relative;
    max-width: 400px;
}

.search-container .search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400);
    z-index: 1;
}

.search-container .search-input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 2.75rem;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    font-size: 0.9rem;
    transition: var(--transition);
    background: var(--white);
}

.search-container .search-input:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.no-results {
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 3rem 2rem;
    text-align: center;
    border: 1px solid var(--gray-200);
}

.no-results-content {
    max-width: 400px;
    margin: 0 auto;
}

.no-results-icon {
    font-size: 3rem;
    color: var(--gray-400);
    margin-bottom: 1rem;
}

.no-results h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}

.no-results p {
    color: var(--gray-600);
    line-height: 1.6;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

.badge-secondary {
    background: #f3f4f6;
    color: #6b7280;
}

/* Animation pour les cartes */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.settings-card {
    animation: fadeIn 0.5s ease-out;
}

/* Responsive Design */
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

    .settings-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo" id="logoToggle">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="logo-text">MaSoutenance</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="sidebar-menu">
                <ul class="menu-list">
                    <li class="menu-item <?= (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : '' ?>">
                        <a href="?page=dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>TABLEAU DE BORD</span>
                        </a>
                    </li>
                    <li class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'etudiant') ? 'active' : '' ?>">
                        <a href="?page=etudiant">
                            <i class="fas fa-users"></i>
                            <span>ETUDIANT</span>
                        </a>
                    </li>
                    <li class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'enseignant') ? 'active' : '' ?>">
                        <a href="?page=enseignant">
                            <i class="fas fa-user-graduate"></i>
                            <span>ENSEIGNANT</span>
                        </a>
                    </li>
                    <li class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'rapport') ? 'active' : '' ?>">
                        <a href="?page=rapport">
                            <i class="fas fa-book"></i>
                            <span>RAPPORT</span>
                        </a>
                    </li>
                    <li class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'comission') ? 'active' : '' ?>">
                        <a href="?page=comission">
                            <i class="fas fa-gavel"></i>
                            <span>COMMISSION VALIDATION</span>
                        </a>
                    </li>
                    <li class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'personnel') ? 'active' : '' ?>">
                        <a href="?page=personnel">
                            <i class="fas fa-file-alt"></i>
                            <span>PERSONNEL ADMINISTRATIF</span>
                        </a>
                    </li>
                    <li class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'cr') ? 'active' : '' ?>">
                        <a href="?page=cr">
                            <i class="fas fa-bell"></i>
                            <span>COMPTE RENDU</span>
                        </a>
                    </li>
                    <li class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'pisteaudit') ? 'active' : '' ?>">
                        <a href="?page=pisteaudit">
                            <i class="fas fa-chart-bar"></i>
                            <span>PISTE D'AUDIT</span>
                        </a>
                    </li>
                    <li class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'reglement') ? 'active' : '' ?>">
                        <a href="?page=reglement">
                            <i class="fas fa-money-bill"></i>
                            <span>SCOLARITE</span>
                        </a>
                    </li>
                    <li class="menu-item <?= (isset($_GET['page']) && $_GET['page'] == 'parametres') ? 'active' : '' ?>">
                        <a href="?page=parametres">
                            <i class="fas fa-cog"></i>
                            <span>PARAMETRES</span>
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
                        <span class="user-name">Administrateur</span>
                        <span class="user-role">Super Admin</span>
                    </div>
                </div>
                <button class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

                switch ($page) {
                    case 'dashboard':
                        echo '<h2 id="pageTitle">Tableau de bord</h2>';
                        break;
                    case 'parametres':
                        echo '<h2 id="pageTitle">Configuration des paramètres</h2>';
                        break;
                    case 'niveau-approbation':
                        echo '<h2 id="pageTitle">Niveau d\'approbation</h2>';
                        break;
                    case 'users':
                        echo '<h2 id="pageTitle">Utilisateur</h2>';
                        break;
                    case 'reglement':
                        echo '<h2 id="pageTitle">Scolarité</h2>';
                        break;
                    case 'pisteaudit':
                        echo '<h2 id="pageTitle">Piste D\'Audit</h2>';
                        break;
                    case 'cr':
                        echo '<h2 id="pageTitle">Compte Rendu</h2>';
                        break;
                    case 'personnel':
                        echo '<h2 id="pageTitle">Personnel Administratif</h2>';
                        break;
                    case 'comission':
                        echo '<h2 id="pageTitle">Commission Validation</h2>';
                        break;
                    case 'rapport':
                        echo '<h2 id="pageTitle">Rapport</h2>';
                        break;
                    case 'enseignant':
                        echo '<h2 id="pageTitle">Enseignants</h2>';
                        break; 
                    case 'etudiant':
                        echo '<h2 id="pageTitle">Etudiants</h2>';
                        break;
                    default:
                        echo '<h2 id="pageTitle">Tableau de bord</h2>';
                        break;
                }
                ?>
                <div class="header-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="user-menu">
                        <button class="user-menu-btn">
                            <i class="fas fa-user-circle"></i>
                        </button>
                    </div>
                </div>
            </header>

            <div class="content-body">
                <!-- Dashboard Section -->
                <section id="dashboard" class="content-section active">
                    <?php
                    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                    
                    switch ($page) {
                        case 'dashboard':
                            include("dashboard.php");
                            break;
                            
                        case 'parametres':
                            // Page des paramètres avec cartes
                            ?>
                            <!-- Search Bar -->
                            <div class="search-section">
                                <div class="search-container">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" id="searchInput" placeholder="Rechercher un paramètre..." class="search-input">
                                </div>
                            </div>

                            <!-- Settings Grid -->
                            <div class="settings-grid" id="settingsGrid">
                                <?php
                                $settingsCards = [
                                    [
                                        'id' => 1,
                                        'title' => 'Niveau d\'approbation',
                                        'description' => 'Configuration des niveaux d\'approbation pour les utilisateurs',
                                        'icon' => 'fas fa-award',
                                        'color' => 'blue',
                                        'status' => 'Actif',
                                        'page' => 'niveau-approbation'
                                    ],
                                    [
                                        'id' => 2,
                                        'title' => 'UE',
                                        'description' => 'Gestion des Unités d\'Enseignement et de leur configuration',
                                        'icon' => 'fas fa-book-open',
                                        'color' => 'green',
                                        'status' => 'Actif',
                                        'page' => 'ue'
                                    ],
                                    [
                                        'id' => 3,
                                        'title' => 'ECUE',
                                        'description' => 'Gestion des Éléments Constitutifs d\'Unités d\'Enseignement',
                                        'icon' => 'fas fa-award',
                                        'color' => 'blue',
                                        'status' => 'Actif',
                                        'page' => 'ecue'
                                    ],
                                    [
                                        'id' => 4,
                                        'title' => 'Fonction',
                                        'description' => 'Configuration des fonctions et rôles dans le système',
                                        'icon' => 'fas fa-user-check',
                                        'color' => 'purple',
                                        'status' => 'Actif',
                                        'page' => 'fonction'
                                    ],
                                    [
                                        'id' => 5,
                                        'title' => 'Grade',
                                        'description' => 'Gestion des grades académiques et professionnels',
                                        'icon' => 'fas fa-graduation-cap',
                                        'color' => 'orange',
                                        'status' => 'Actif',
                                        'page' => 'grade'
                                    ],
                                    [
                                        'id' => 6,
                                        'title' => 'Année Académique',
                                        'description' => 'Configuration des années académiques et périodes d\'étude',
                                        'icon' => 'fas fa-calendar',
                                        'color' => 'red',
                                        'status' => 'Actif',
                                        'page' => 'annee-academique'
                                    ],
                                    [
                                        'id' => 7,
                                        'title' => 'Niveau d\'étude',
                                        'description' => 'Définition des niveaux d\'étude (Licence, Master, Doctorat)',
                                        'icon' => 'fas fa-bookmark',
                                        'color' => 'yellow',
                                        'status' => 'Actif',
                                        'page' => 'niveau-etude'
                                    ],
                                    [
                                        'id' => 8,
                                        'title' => 'Entreprise',
                                        'description' => 'Gestion des entreprises partenaires pour les stages',
                                        'icon' => 'fas fa-building',
                                        'color' => 'indigo',
                                        'status' => 'Actif',
                                        'page' => 'entreprise'
                                    ],
                                    [
                                        'id' => 9,
                                        'title' => 'Type utilisateur',
                                        'description' => 'Configuration des types d\'utilisateurs du système',
                                        'icon' => 'fas fa-users',
                                        'color' => 'pink',
                                        'status' => 'Actif',
                                        'page' => 'type-utilisateur'
                                    ],
                                    [
                                        'id' => 10,
                                        'title' => 'Utilisateur',
                                        'description' => 'Gestion des comptes utilisateurs et leurs paramètres',
                                        'icon' => 'fas fa-user',
                                        'color' => 'teal',
                                        'status' => 'Actif',
                                        'page' => 'utilisateur'
                                    ],
                                    [
                                        'id' => 11,
                                        'title' => 'Groupe Utilisateur',
                                        'description' => 'Configuration des groupes d\'utilisateurs et permissions',
                                        'icon' => 'fas fa-users-cog',
                                        'color' => 'cyan',
                                        'status' => 'Actif',
                                        'page' => 'groupe-utilisateur'
                                    ],
                                    [
                                        'id' => 12,
                                        'title' => 'Traitement',
                                        'description' => 'Configuration des processus de traitement des dossiers',
                                        'icon' => 'fas fa-cogs',
                                        'color' => 'emerald',
                                        'status' => 'Actif',
                                        'page' => 'traitement'
                                    ],
                                    [
                                        'id' => 13,
                                        'title' => 'Posséder',
                                        'description' => 'Gestion des relations de possession entre entités',
                                        'icon' => 'fas fa-database',
                                        'color' => 'violet',
                                        'status' => 'Actif',
                                        'page' => 'posseder'
                                    ],
                                    [
                                        'id' => 14,
                                        'title' => 'Statut Jury',
                                        'description' => 'Configuration des statuts des membres de jury',
                                        'icon' => 'fas fa-balance-scale',
                                        'color' => 'rose',
                                        'status' => 'Actif',
                                        'page' => 'statut-jury'
                                    ],
                                    [
                                        'id' => 15,
                                        'title' => 'Spécialité',
                                        'description' => 'Gestion des spécialités académiques et professionnelles',
                                        'icon' => 'fas fa-star',
                                        'color' => 'amber',
                                        'status' => 'Actif',
                                        'page' => 'specialite'
                                    ],
                                    [
                                        'id' => 16,
                                        'title' => 'Niveau d\'accès aux données',
                                        'description' => 'Configuration des niveaux d\'accès et permissions',
                                        'icon' => 'fas fa-shield-alt',
                                        'color' => 'slate',
                                        'status' => 'Actif',
                                        'page' => 'niveau-acces'
                                    ],
                                    [
                                        'id' => 17,
                                        'title' => 'Action',
                                        'description' => 'Configuration des actions disponibles dans le système',
                                        'icon' => 'fas fa-lock',
                                        'color' => 'stone',
                                        'status' => 'Actif',
                                        'page' => 'action'
                                    ]
                                ];
                                
                                foreach ($settingsCards as $card): ?>
                                    <div class="settings-card" data-title="<?= strtolower($card['title']) ?>" data-description="<?= strtolower($card['description']) ?>">
                                        <div class="settings-card-header">
                                            <div class="settings-card-icon icon-<?= $card['color'] ?>">
                                                <i class="<?= $card['icon'] ?>"></i>
                                            </div>
                                            <span class="badge badge-<?= strtolower($card['status']) === 'actif' ? 'success' : 'secondary' ?>">
                                                <?= $card['status'] ?>
                                            </span>
                                        </div>
                                        <div class="settings-card-content">
                                            <h3 class="settings-card-title"><?= $card['title'] ?></h3>
                                            <p class="settings-card-description"><?= $card['description'] ?></p>
                                            <a href="?page=<?= $card['page'] ?>" class="btn-configure">
                                                <i class="fas fa-cog"></i>
                                                Configurer
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- No Results Message -->
                            <div class="no-results" id="noResults" style="display: none;">
                                <div class="no-results-content">
                                    <i class="fas fa-search no-results-icon"></i>
                                    <h3>Aucun paramètre trouvé</h3>
                                    <p>Essayez de modifier votre recherche ou parcourez tous les paramètres disponibles.</p>
                                </div>
                            </div>
                            <?php
                            break;

                        // Inclusion des pages existantes selon le paramètre page
                        case 'niveau-approbation':
                            include("parametres_generaux/niveau_approbation.php");
                            break;

                        case 'ue':
                            include("parametres_generaux/ue.php");
                            break;

                        case 'ecue':
                            include("parametres_generaux/ecue.php");
                            break;

                        case 'fonction':
                            include("parametres_generaux/fonction.php");
                            break;

                        case 'grade':
                            include("parametres_generaux/grade.php");
                            break;

                        case 'annee-academique':
                            include("parametres_generaux/annee_academique.php");
                            break;

                        case 'niveau-etude':
                            include("parametres_generaux/niveau_etude.php");
                            break;

                        case 'entreprise':
                            include("parametres_generaux/entreprise.php");
                            break;

                        case 'type-utilisateur':
                            include("parametres_generaux/type_utilisateur.php");
                            break;

                        case 'utilisateur':
                            include("parametres_generaux/utilisateurs.php");
                            break;

                        case 'groupe-utilisateur':
                            include("parametres_generaux/groupe_utilisateur.php");
                            break;

                        case 'traitement':
                            include("parametres_generaux/traitement.php");
                            break;

                        case 'posseder':
                            include("parametres_generaux/posseder.php");
                            break;

                        case 'statut-jury':
                            include("parametres_generaux/statut_jury.php");
                            break;

                        case 'specialite':
                            include("parametres_generaux/specialite.php");
                            break;

                        case 'niveau-acces':
                            include("parametres_generaux/niveau_acces_donnees.php");
                            break;

                        case 'action':
                            include("parametres_generaux/action.php");
                            break;

                        // Pages existantes dans votre système
                        case 'users':
                            include("parametres_generaux/utilisateurs.php");
                            break;

                        case 'cr':
                            include("parametres_specifiques/compte_rendu.php");
                            break;

                        case 'enseignant':
                            include("parametres_specifiques/enseignant.php");
                            break;

                        case 'etudiant':
                            include("parametres_specifiques/etudiant.php");
                            break;

                        case 'rapport':
                            include("parametres_specifiques/rapports.php");
                            break;

                        case 'comission':
                            include("parametres_specifiques/comission.php");
                            break;

                        case 'personnel':
                            include("parametres_specifiques/personnel.php");
                            break;

                        case 'pisteaudit':
                            include("parametres_de_travail/audit.php");
                            break;

                        case 'reglement':
                            include("inscription_scol.php");
                            break;

                        case 'Ressources':
                            include("ressources.php");
                            break;

                        case 'CompteRendu':
                            include("cpt_rendu.php");
                            break;

                        case 'Habilitations':
                            include("habilitations.php");
                            break;

                        case 'Confidentialites':
                            include("confidentialités.php");
                            break;

                        default:
                            include('dashboard.php');
                            break;
                    }
                    ?>
                </section>
            </div>
        </main>
    </div>

<script>
        // Variables globales
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoToggle = document.getElementById('logoToggle');
        const searchInput = document.getElementById('searchInput');
        const settingsGrid = document.getElementById('settingsGrid');
        const noResults = document.getElementById('noResults');

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebar();
            initializeSearch();
            initializeCards();
        });

        // Initialisation de la sidebar
        function initializeSidebar() {
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleSidebar();
                });
            }

            if (logoToggle) {
                logoToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (sidebar.classList.contains('collapsed')) {
                        expandSidebar();
                    }
                });
            }
        }

        // Fonctions de gestion de la sidebar
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
        }

        function expandSidebar() {
            sidebar.classList.remove('collapsed');
        }

        function collapseSidebar() {
            sidebar.classList.add('collapsed');
        }

        // Initialisation de la recherche
        function initializeSearch() {
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    filterCards(searchTerm);
                });

                // Effacer la recherche avec Escape
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        this.value = '';
                        filterCards('');
                    }
                });
            }
        }

        // Filtrage des cartes
        function filterCards(searchTerm) {
            if (!settingsGrid) return;
            
            const cards = document.querySelectorAll('.settings-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const description = card.getAttribute('data-description') || '';

                const isVisible = title.includes(searchTerm) || description.includes(searchTerm);

                if (isVisible) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.3s ease-out';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Afficher/masquer le message "Aucun résultat"
            if (noResults) {
                if (visibleCount === 0 && searchTerm !== '') {
                    noResults.style.display = 'block';
                    settingsGrid.style.display = 'none';
                } else {
                    noResults.style.display = 'none';
                    settingsGrid.style.display = 'grid';
                }
            }
        }

        // Initialisation des cartes avec effets visuels uniquement
        function initializeCards() {
            const cards = document.querySelectorAll('.settings-card');

            cards.forEach((card, index) => {
                // Animation d'entrée échelonnée
                card.style.animationDelay = `${index * 0.1}s`;

                // Effet hover amélioré
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });

                // Effet de clic sur le bouton configurer
                const configureBtn = card.querySelector('.btn-configure');
                if (configureBtn) {
                    configureBtn.addEventListener('click', function(e) {
                        // Animation visuelle seulement
                        this.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                        }, 150);
                        // Le lien href gère la navigation
                    });
                }
            });
        }

        // Gestion du bouton de déconnexion
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    window.location.href = 'logout.php';
                }
            });
        }

        // Gestion responsive
        function handleResize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('mobile');
            } else {
                sidebar.classList.remove('mobile');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();

        // Raccourcis clavier
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K pour focus sur la recherche
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                }
            }

            // Échap pour fermer la sidebar sur mobile
            if (e.key === 'Escape' && window.innerWidth <= 768) {
                sidebar.classList.remove('open');
            }
        });
</script>
    <script>
// Variables globales
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const logoToggle = document.getElementById('logoToggle');

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
});

// Initialisation de la sidebar
function initializeSidebar() {
    // Toggle sidebar avec le bouton burger
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });
    }

    // Expand sidebar en cliquant sur le logo quand collapsed
    if (logoToggle) {
        logoToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (sidebar.classList.contains('collapsed')) {
                expandSidebar();
            }
        });
    }
}

// 1. Fonction pour basculer l'état de la sidebar
function toggleSidebar() {
    sidebar.classList.toggle('collapsed');
}

// 2. Fonction pour forcer l'expansion de la sidebar
function expandSidebar() {
    sidebar.classList.remove('collapsed');
}

// 3. Fonction pour forcer la réduction de la sidebar
function collapseSidebar() {
    sidebar.classList.add('collapsed');
}
</script>
</body>

</html>