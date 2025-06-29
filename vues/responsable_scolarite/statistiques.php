<?php

session_start();
require_once '../../config/database.php';
require_once '../../config/session.php';

// Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responsable_scolarite') {
    header('Location: ../../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Responsable Scolarité</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --info-color: #3b82f6;
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
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
        }

        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-warning {
            background: var(--warning-color);
            color: var(--white);
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-info {
            background: var(--info-color);
            color: var(--white);
        }

        .btn-info:hover {
            background: #2563eb;
        }

        /* Stats Cards */
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
            background: linear-gradient(135deg, var(--info-color), #60a5fa);
        }

        .stat-card:nth-child(5) .stat-icon {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
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

        .stat-trend {
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .trend-up {
            color: var(--success-color);
        }

        .trend-down {
            color: var(--error-color);
        }

        .trend-stable {
            color: var(--gray-500);
        }

        /* Filters Bar */
        .filters-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: center;
            flex-wrap: wrap;
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--gray-600);
        }

        .filter-select,
        .filter-input {
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
            min-width: 150px;
        }

        .filter-select:focus,
        .filter-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        /* Charts Container */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .chart-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .chart-actions {
            display: flex;
            gap: 0.5rem;
        }

        .chart-btn {
            padding: 0.5rem;
            border: none;
            background: var(--gray-100);
            color: var(--gray-600);
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
        }

        .chart-btn:hover {
            background: var(--gray-200);
            color: var(--primary-color);
        }

        .chart-btn.active {
            background: var(--primary-color);
            color: var(--white);
        }

        /* Tables */
        .data-table {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
        }

        .data-table tr:hover {
            background: var(--gray-50);
        }

        /* Progress Bars */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transition: width 0.3s ease;
        }

        /* Badges */
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

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 2rem;
            background: var(--white);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            box-shadow: var(--shadow);
        }

        .tab {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray-600);
            border-bottom: 2px solid transparent;
            transition: var(--transition);
        }

        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab:hover {
            color: var(--primary-color);
            background: var(--gray-50);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

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

            .filters-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                width: 100%;
            }

            .filter-select,
            .filter-input {
                min-width: auto;
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-grid {
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
                    <li class="menu-item">
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
                    <li class="menu-item active">
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
                <h1>Statistiques et Rapports</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="exportStatistics()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="btn btn-primary" onclick="generateReport()">
                        <i class="fas fa-file-pdf"></i>
                        Générer rapport
                    </button>
                </div>
            </header>

            <div class="content-body">
                <!-- Filters -->
                <div class="filters-bar">
                    <div class="filter-group">
                        <label>Année académique</label>
                        <select class="filter-select" id="filterYear">
                            <option value="2023-2024" selected>2023-2024</option>
                            <option value="2022-2023">2022-2023</option>
                            <option value="2021-2022">2021-2022</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Semestre</label>
                        <select class="filter-select" id="filterSemester">
                            <option value="">Tous les semestres</option>
                            <option value="1">Semestre 1</option>
                            <option value="2">Semestre 2</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Niveau</label>
                        <select class="filter-select" id="filterLevel">
                            <option value="">Tous les niveaux</option>
                            <option value="licence1">Licence 1</option>
                            <option value="licence2">Licence 2</option>
                            <option value="licence3">Licence 3</option>
                            <option value="master1">Master 1</option>
                            <option value="master2">Master 2</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Spécialité</label>
                        <select class="filter-select" id="filterSpecialty">
                            <option value="">Toutes les spécialités</option>
                            <option value="informatique">Informatique</option>
                            <option value="mathematiques">Mathématiques</option>
                            <option value="physique">Physique</option>
                            <option value="chimie">Chimie</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Période</label>
                        <select class="filter-select" id="filterPeriod">
                            <option value="current">Période actuelle</option>
                            <option value="last-month">Dernier mois</option>
                            <option value="last-quarter">Dernier trimestre</option>
                            <option value="last-year">Dernière année</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-filter"></i>
                            Appliquer
                        </button>
                    </div>
                </div>

                <!-- Key Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalStudents">1,247</h3>
                            <p>Étudiants inscrits</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i> +5.2% vs année précédente
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="successRate">87.3%</h3>
                            <p>Taux de réussite</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i> +2.1% vs année précédente
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="averageGrade">12.8</h3>
                            <p>Moyenne générale</p>
                            <div class="stat-trend trend-stable">
                                <i class="fas fa-minus"></i> Stable vs année précédente
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="reportsSubmitted">234</h3>
                            <p>Rapports soumis</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i> +12.5% vs année précédente
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="defensesCompleted">189</h3>
                            <p>Soutenances réalisées</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i> +8.7% vs année précédente
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="avgProcessingTime">12.5</h3>
                            <p>Jours de traitement moyen</p>
                            <div class="stat-trend trend-down">
                                <i class="fas fa-arrow-down"></i> -15.3% vs année précédente
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('overview')">
                        <i class="fas fa-chart-pie"></i>
                        Vue d'ensemble
                    </button>
                    <button class="tab" onclick="switchTab('academic')">
                        <i class="fas fa-graduation-cap"></i>
                        Performance académique
                    </button>
                    <button class="tab" onclick="switchTab('reports')">
                        <i class="fas fa-file-chart-line"></i>
                        Rapports et soutenances
                    </button>
                    <button class="tab" onclick="switchTab('trends')">
                        <i class="fas fa-chart-line"></i>
                        Tendances
                    </button>
                </div>

                <!-- Overview Tab -->
                <div id="overviewTab" class="tab-content active">
                    <div class="charts-grid">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Répartition des étudiants par niveau</h3>
                                <div class="chart-actions">
                                    <button class="chart-btn active" onclick="changeChartType('studentsChart', 'doughnut')">
                                        <i class="fas fa-chart-pie"></i>
                                    </button>
                                    <button class="chart-btn" onclick="changeChartType('studentsChart', 'bar')">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                </div>
                            </div>
                            <canvas id="studentsChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Statistiques rapides</h3>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Étudiants actifs</span>
                                    <strong>1,198</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Étudiants éligibles</span>
                                    <strong>1,024</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>UE validées</span>
                                    <strong>2,456</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Sessions rattrapage</span>
                                    <strong>156</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Encadrants actifs</span>
                                    <strong>67</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="charts-grid">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Répartition par spécialité</h3>
                            </div>
                            <canvas id="specialtyChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Évolution des inscriptions</h3>
                            </div>
                            <canvas id="enrollmentChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Academic Performance Tab -->
                <div id="academicTab" class="tab-content">
                    <div class="charts-grid">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Distribution des notes</h3>
                                <div class="chart-actions">
                                    <button class="chart-btn active" onclick="changeGradesPeriod('current')">Actuel</button>
                                    <button class="chart-btn" onclick="changeGradesPeriod('previous')">Précédent</button>
                                </div>
                            </div>
                            <canvas id="gradesChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Taux de réussite par niveau</h3>
                            </div>
                            <canvas id="successRateChart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Performance Table -->
                    <div class="data-table">
                        <div class="table-header">
                            <h3>Performance par spécialité</h3>
                            <button class="btn btn-secondary" onclick="exportPerformanceData()">
                                <i class="fas fa-download"></i>
                                Exporter
                            </button>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Spécialité</th>
                                    <th>Étudiants</th>
                                    <th>Moyenne</th>
                                    <th>Taux de réussite</th>
                                    <th>Mentions</th>
                                    <th>Progression</th>
                                </tr>
                            </thead>
                            <tbody id="performanceTableBody">
                                <tr>
                                    <td><strong>Informatique</strong></td>
                                    <td>456</td>
                                    <td><strong>13.2</strong></td>
                                    <td>
                                        <span class="badge badge-success">89.5%</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.8rem;">
                                            TB: 23% | B: 34% | AB: 32%
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 89.5%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Mathématiques</strong></td>
                                    <td>298</td>
                                    <td><strong>12.8</strong></td>
                                    <td>
                                        <span class="badge badge-success">85.2%</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.8rem;">
                                            TB: 18% | B: 29% | AB: 38%
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 85.2%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Physique</strong></td>
                                    <td>267</td>
                                    <td><strong>12.1</strong></td>
                                    <td>
                                        <span class="badge badge-warning">82.8%</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.8rem;">
                                            TB: 15% | B: 25% | AB: 43%
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 82.8%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Chimie</strong></td>
                                    <td>226</td>
                                    <td><strong>11.9</strong></td>
                                    <td>
                                        <span class="badge badge-warning">79.6%</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.8rem;">
                                            TB: 12% | B: 22% | AB: 46%
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 79.6%"></div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reports and Defenses Tab -->
                <div id="reportsTab" class="tab-content">
                    <div class="charts-grid">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Statut des rapports</h3>
                            </div>
                            <canvas id="reportsStatusChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Soutenances par mois</h3>
                            </div>
                            <canvas id="defensesChart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Reports Statistics Table -->
                    <div class="data-table">
                        <div class="table-header">
                            <h3>Statistiques des encadrants</h3>
                            <button class="btn btn-secondary" onclick="exportSupervisorData()">
                                <i class="fas fa-download"></i>
                                Exporter
                            </button>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Encadrant</th>
                                    <th>Département</th>
                                    <th>Rapports encadrés</th>
                                    <th>Soutenances</th>
                                    <th>Note moyenne</th>
                                    <th>Taux de réussite</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Dr. YAPI Marie</strong></td>
                                    <td>Informatique</td>
                                    <td>12</td>
                                    <td>10</td>
                                    <td><strong>14.2</strong></td>
                                    <td><span class="badge badge-success">95%</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Prof. KONE Abdoulaye</strong></td>
                                    <td>Informatique</td>
                                    <td>18</td>
                                    <td>15</td>
                                    <td><strong>13.8</strong></td>
                                    <td><span class="badge badge-success">92%</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Dr. BAMBA Fatou</strong></td>
                                    <td>Mathématiques</td>
                                    <td>8</td>
                                    <td>7</td>
                                    <td><strong>13.5</strong></td>
                                    <td><span class="badge badge-success">88%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Trends Tab -->
                <div id="trendsTab" class="tab-content">
                    <div class="charts-grid">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Évolution des performances</h3>
                                <div class="chart-actions">
                                    <button class="chart-btn active" onclick="changeTrendPeriod('year')">Année</button>
                                    <button class="chart-btn" onclick="changeTrendPeriod('semester')">Semestre</button>
                                    <button class="chart-btn" onclick="changeTrendPeriod('month')">Mois</button>
                                </div>
                            </div>
                            <canvas id="trendsChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Prédictions</h3>
                            </div>
                            <canvas id="predictionsChart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Trends Analysis -->
                    <div class="data-table">
                        <div class="table-header">
                            <h3>Analyse des tendances</h3>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Indicateur</th>
                                    <th>Valeur actuelle</th>
                                    <th>Tendance</th>
                                    <th>Prédiction</th>
                                    <th>Recommandation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Taux de réussite</strong></td>
                                    <td>87.3%</td>
                                    <td><span class="badge badge-success">↗ +2.1%</span></td>
                                    <td>89.1%</td>
                                    <td>Maintenir les efforts actuels</td>
                                </tr>
                                <tr>
                                    <td><strong>Moyenne générale</strong></td>
                                    <td>12.8</td>
                                    <td><span class="badge badge-info">→ Stable</span></td>
                                    <td>12.9</td>
                                    <td>Renforcer l'accompagnement</td>
                                </tr>
                                <tr>
                                    <td><strong>Taux d'abandon</strong></td>
                                    <td>8.2%</td>
                                    <td><span class="badge badge-success">↘ -1.5%</span></td>
                                    <td>7.1%</td>
                                    <td>Continuer les mesures préventives</td>
                                </tr>
                                <tr>
                                    <td><strong>Délai de traitement</strong></td>
                                    <td>12.5 jours</td>
                                    <td><span class="badge badge-success">↘ -15.3%</span></td>
                                    <td>10.8 jours</td>
                                    <td>Optimiser davantage les processus</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Global variables
        let charts = {};
        let currentTab = 'overview';

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoToggle = document.getElementById('logoToggle');

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            initializeCharts();
            updateStatistics();
        });

        // Event Listeners
        function initializeEventListeners() {
            sidebarToggle.addEventListener('click', toggleSidebar);
            
            logoToggle.addEventListener('click', function() {
                if (sidebar.classList.contains('collapsed')) {
                    toggleSidebar();
                }
            });

            // Filter listeners
            document.getElementById('filterYear')?.addEventListener('change', applyFilters);
            document.getElementById('filterSemester')?.addEventListener('change', applyFilters);
            document.getElementById('filterLevel')?.addEventListener('change', applyFilters);
            document.getElementById('filterSpecialty')?.addEventListener('change', applyFilters);
            document.getElementById('filterPeriod')?.addEventListener('change', applyFilters);
        }

        // Sidebar functions
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
        }

        // Tab functions
        function switchTab(tabName) {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            // Add active class to selected tab and content
            event.target.classList.add('active');
            document.getElementById(tabName + 'Tab').classList.add('active');

            currentTab = tabName;

            // Initialize charts for the active tab if needed
            setTimeout(() => {
                initializeTabCharts(tabName);
            }, 100);
        }

        // Charts initialization
        function initializeCharts() {
            initializeOverviewCharts();
        }

        function initializeTabCharts(tabName) {
            switch(tabName) {
                case 'overview':
                    initializeOverviewCharts();
                    break;
                case 'academic':
                    initializeAcademicCharts();
                    break;
                case 'reports':
                    initializeReportsCharts();
                    break;
                case 'trends':
                    initializeTrendsCharts();
                    break;
            }
        }

        function initializeOverviewCharts() {
            // Students distribution chart
            const studentsCtx = document.getElementById('studentsChart');
            if (studentsCtx && !charts.studentsChart) {
                charts.studentsChart = new Chart(studentsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2'],
                        datasets: [{
                            data: [245, 298, 267, 234, 203],
                            backgroundColor: [
                                'rgba(0, 51, 41, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(52, 211, 153, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(139, 92, 246, 0.8)'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Specialty distribution chart
            const specialtyCtx = document.getElementById('specialtyChart');
            if (specialtyCtx && !charts.specialtyChart) {
                charts.specialtyChart = new Chart(specialtyCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Informatique', 'Mathématiques', 'Physique', 'Chimie'],
                        datasets: [{
                            label: 'Nombre d\'étudiants',
                            data: [456, 298, 267, 226],
                            backgroundColor: 'rgba(0, 51, 41, 0.8)',
                            borderColor: 'rgba(0, 51, 41, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Enrollment evolution chart
            const enrollmentCtx = document.getElementById('enrollmentChart');
            if (enrollmentCtx && !charts.enrollmentChart) {
                charts.enrollmentChart = new Chart(enrollmentCtx, {
                    type: 'line',
                    data: {
                        labels: ['2019-20', '2020-21', '2021-22', '2022-23', '2023-24'],
                        datasets: [{
                            label: 'Inscriptions',
                            data: [1089, 1156, 1203, 1185, 1247],
                            borderColor: 'rgba(0, 51, 41, 1)',
                            backgroundColor: 'rgba(0, 51, 41, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            }
        }

        function initializeAcademicCharts() {
            // Grades distribution chart
            const gradesCtx = document.getElementById('gradesChart');
            if (gradesCtx && !charts.gradesChart) {
                charts.gradesChart = new Chart(gradesCtx, {
                    type: 'bar',
                    data: {
                        labels: ['0-5', '5-8', '8-10', '10-12', '12-14', '14-16', '16-18', '18-20'],
                        datasets: [{
                            label: 'Nombre d\'étudiants',
                            data: [23, 45, 89, 234, 345, 298, 156, 67],
                            backgroundColor: [
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(245, 158, 11, 0.6)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(16, 185, 129, 0.9)',
                                'rgba(139, 92, 246, 0.8)',
                                'rgba(139, 92, 246, 0.9)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Success rate by level chart
            const successRateCtx = document.getElementById('successRateChart');
            if (successRateCtx && !charts.successRateChart) {
                charts.successRateChart = new Chart(successRateCtx, {
                    type: 'radar',
                    data: {
                        labels: ['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2'],
                        datasets: [{
                            label: 'Taux de réussite (%)',
                            data: [78.5, 82.3, 85.7, 89.2, 91.8],
                            borderColor: 'rgba(0, 51, 41, 1)',
                            backgroundColor: 'rgba(0, 51, 41, 0.2)',
                            pointBackgroundColor: 'rgba(0, 51, 41, 1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
        }

        function initializeReportsCharts() {
            // Reports status chart
            const reportsStatusCtx = document.getElementById('reportsStatusChart');
            if (reportsStatusCtx && !charts.reportsStatusChart) {
                charts.reportsStatusChart = new Chart(reportsStatusCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Déposés', 'En examen', 'Validés', 'Rejetés', 'Soutenus'],
                        datasets: [{
                            data: [89, 34, 45, 12, 156],
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(139, 92, 246, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Defenses by month chart
            const defensesCtx = document.getElementById('defensesChart');
            if (defensesCtx && !charts.defensesChart) {
                charts.defensesChart = new Chart(defensesCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                        datasets: [{
                            label: 'Soutenances',
                            data: [12, 15, 23, 28, 34, 45, 38, 42, 35, 29, 18, 14],
                            borderColor: 'rgba(16, 185, 129, 1)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        function initializeTrendsCharts() {
            // Trends chart
            const trendsCtx = document.getElementById('trendsChart');
            if (trendsCtx && !charts.trendsChart) {
                charts.trendsChart = new Chart(trendsCtx, {
                    type: 'line',
                    data: {
                        labels: ['2020', '2021', '2022', '2023', '2024'],
                        datasets: [
                            {
                                label: 'Taux de réussite (%)',
                                data: [82.1, 84.3, 85.2, 87.3, 89.1],
                                borderColor: 'rgba(16, 185, 129, 1)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                yAxisID: 'y'
                            },
                            {
                                label: 'Moyenne générale',
                                data: [11.8, 12.1, 12.5, 12.8, 12.9],
                                borderColor: 'rgba(59, 130, 246, 1)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                min: 70,
                                max: 100
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                min: 10,
                                max: 15,
                                grid: {
                                    drawOnChartArea: false,
                                }
                            }
                        }
                    }
                });
            }

            // Predictions chart
            const predictionsCtx = document.getElementById('predictionsChart');
            if (predictionsCtx && !charts.predictionsChart) {
                charts.predictionsChart = new Chart(predictionsCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
                        datasets: [
                            {
                                label: 'Réel',
                                data: [87.3, 87.8, 88.1, 88.5, null, null],
                                borderColor: 'rgba(0, 51, 41, 1)',
                                backgroundColor: 'rgba(0, 51, 41, 0.1)',
                                borderDash: [0, 0, 0, 0, 5, 5]
                            },
                            {
                                label: 'Prédiction',
                                data: [null, null, null, 88.5, 89.2, 89.8],
                                borderColor: 'rgba(245, 158, 11, 1)',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                borderDash: [5, 5]
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                min: 85,
                                max: 92
                            }
                        }
                    }
                });
            }
        }

        // Chart interaction functions
        function changeChartType(chartId, type) {
            if (charts[chartId]) {
                charts[chartId].config.type = type;
                charts[chartId].update();
                
                // Update button states
                const container = charts[chartId].canvas.closest('.chart-container');
                container.querySelectorAll('.chart-btn').forEach(btn => btn.classList.remove('active'));
                event.target.classList.add('active');
            }
        }

        function changeGradesPeriod(period) {
            // Update grades chart data based on period
            if (charts.gradesChart) {
                const currentData = [23, 45, 89, 234, 345, 298, 156, 67];
                const previousData = [28, 52, 95, 221, 332, 285, 148, 61];
                
                charts.gradesChart.data.datasets[0].data = period === 'current' ? currentData : previousData;
                charts.gradesChart.update();
                
                // Update button states
                event.target.parentElement.querySelectorAll('.chart-btn').forEach(btn => btn.classList.remove('active'));
                event.target.classList.add('active');
            }
        }

        function changeTrendPeriod(period) {
            // Update trends chart data based on period
            if (charts.trendsChart) {
                let labels, successData, gradeData;
                
                switch(period) {
                    case 'year':
                        labels = ['2020', '2021', '2022', '2023', '2024'];
                        successData = [82.1, 84.3, 85.2, 87.3, 89.1];
                        gradeData = [11.8, 12.1, 12.5, 12.8, 12.9];
                        break;
                    case 'semester':
                        labels = ['S1 2023', 'S2 2023', 'S1 2024', 'S2 2024'];
                        successData = [85.2, 87.3, 88.1, 89.1];
                        gradeData = [12.5, 12.8, 12.9, 13.1];
                        break;
                    case 'month':
                        labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'];
                        successData = [87.3, 87.8, 88.1, 88.5, 88.9, 89.1];
                        gradeData = [12.8, 12.9, 12.9, 13.0, 13.0, 13.1];
                        break;
                }
                
                charts.trendsChart.data.labels = labels;
                charts.trendsChart.data.datasets[0].data = successData;
                charts.trendsChart.data.datasets[1].data = gradeData;
                charts.trendsChart.update();
                
                // Update button states
                event.target.parentElement.querySelectorAll('.chart-btn').forEach(btn => btn.classList.remove('active'));
                event.target.classList.add('active');
            }
        }

        // Filter and data functions
        function applyFilters() {
            const year = document.getElementById('filterYear').value;
            const semester = document.getElementById('filterSemester').value;
            const level = document.getElementById('filterLevel').value;
            const specialty = document.getElementById('filterSpecialty').value;
            const period = document.getElementById('filterPeriod').value;

            // Simulate data filtering and update
            updateStatistics();
            updateCharts();
            
            showNotification('Filtres appliqués avec succès', 'success');
        }

        function updateStatistics() {
            // Simulate real-time statistics update
            const stats = {
                totalStudents: Math.floor(Math.random() * 100) + 1200,
                successRate: (Math.random() * 10 + 80).toFixed(1),
                averageGrade: (Math.random() * 2 + 11).toFixed(1),
                reportsSubmitted: Math.floor(Math.random() * 50) + 200,
                defensesCompleted: Math.floor(Math.random() * 40) + 150,
                avgProcessingTime: (Math.random() * 5 + 10).toFixed(1)
            };

            // Update DOM elements
            document.getElementById('totalStudents').textContent = stats.totalStudents.toLocaleString();
            document.getElementById('successRate').textContent = stats.successRate + '%';
            document.getElementById('averageGrade').textContent = stats.averageGrade;
            document.getElementById('reportsSubmitted').textContent = stats.reportsSubmitted;
            document.getElementById('defensesCompleted').textContent = stats.defensesCompleted;
            document.getElementById('avgProcessingTime').textContent = stats.avgProcessingTime;
        }

        function updateCharts() {
            // Update all charts with new data
            Object.keys(charts).forEach(chartKey => {
                if (charts[chartKey]) {
                    // Simulate data update
                    charts[chartKey].update();
                }
            });
        }

        // Export functions
        function exportStatistics() {
            // Simulate statistics export
            const data = {
                date: new Date().toISOString().split('T')[0],
                totalStudents: document.getElementById('totalStudents').textContent,
                successRate: document.getElementById('successRate').textContent,
                averageGrade: document.getElementById('averageGrade').textContent,
                reportsSubmitted: document.getElementById('reportsSubmitted').textContent,
                defensesCompleted: document.getElementById('defensesCompleted').textContent
            };

            const csvContent = [
                'Indicateur,Valeur',
                `Date d'export,${data.date}`,
                `Étudiants inscrits,${data.totalStudents}`,
                `Taux de réussite,${data.successRate}`,
                `Moyenne générale,${data.averageGrade}`,
                `Rapports soumis,${data.reportsSubmitted}`,
                `Soutenances réalisées,${data.defensesCompleted}`
            ].join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `statistiques_${data.date}.csv`;
            link.click();
            
            showNotification('Export des statistiques terminé', 'success');
        }

        function exportPerformanceData() {
            showNotification('Export des données de performance en cours...', 'info');
            
            setTimeout(() => {
                showNotification('Export terminé avec succès', 'success');
            }, 2000);
        }

        function exportSupervisorData() {
            showNotification('Export des données d\'encadrants en cours...', 'info');
            
            setTimeout(() => {
                showNotification('Export terminé avec succès', 'success');
            }, 2000);
        }

        function generateReport() {
            showNotification('Génération du rapport PDF en cours...', 'info');
            
            setTimeout(() => {
                showNotification('Rapport PDF généré avec succès', 'success');
            }, 3000);
        }

        function logout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = '../../logout.php';
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        function getNotificationIcon(type) {
            const icons = {
                'success': 'check-circle',
                'error': 'exclamation-circle',
                'warning': 'exclamation-triangle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
        }

        // Auto-refresh statistics every 5 minutes
        setInterval(function() {
            updateStatistics();
            console.log('Statistiques mises à jour automatiquement');
        }, 300000);

        // Responsive sidebar for mobile
        function checkScreenSize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
            }
        }

        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();

        // Initialize tooltips and other UI enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to stat cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>
