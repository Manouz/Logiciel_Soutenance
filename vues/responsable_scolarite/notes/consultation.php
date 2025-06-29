<?php
/*
session_start();
require_once '../../../config/database.php';
require_once '../../../config/session.php';

// Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responsable_scolarite') {
    header('Location: ../../../login.php');
    exit();
}*/
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation des Notes - Responsable Scolarité</title>
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
        .search-input {
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
            min-width: 150px;
        }

        .filter-select:focus,
        .search-input:focus {
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

        /* Data Table */
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
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table tr:hover {
            background: var(--gray-50);
        }

        /* Badges */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .grade-excellent {
            background: #dcfce7;
            color: #166534;
        }

        .grade-good {
            background: #dbeafe;
            color: #1e40af;
        }

        .grade-average {
            background: #fef3c7;
            color: #92400e;
        }

        .grade-poor {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-validated {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-draft {
            background: #f3f4f6;
            color: #374151;
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

        /* Progress Bar */
        .progress-container {
            background: var(--gray-200);
            border-radius: 10px;
            overflow: hidden;
            height: 8px;
            margin: 0.5rem 0;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transition: width 0.3s ease;
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
            .search-input {
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
                        <a href="../index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="../etudiants/gestion.php">
                            <i class="fas fa-users"></i>
                            <span>Gestion Étudiants</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="saisie.php">
                            <i class="fas fa-edit"></i>
                            <span>Saisie Notes</span>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="consultation.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Consultation Notes</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="../etudiants/eligibilite.php">
                            <i class="fas fa-check-circle"></i>
                            <span>Vérification Éligibilité</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="../rapports/suivi.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Suivi Rapports</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="../evaluation.php">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Évaluation</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="../statistiques.php">
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
                <h1>Consultation des Notes</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="exportNotes()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="btn btn-info" onclick="generateBulletins()">
                        <i class="fas fa-file-pdf"></i>
                        Bulletins
                    </button>
                    <button class="btn btn-primary" onclick="openModal('analysisModal')">
                        <i class="fas fa-chart-bar"></i>
                        Analyse avancée
                    </button>
                </div>
            </header>

            <div class="content-body">
                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalStudents">1,247</h3>
                            <p>Étudiants évalués</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="averageGrade">12.8</h3>
                            <p>Moyenne générale</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="successRate">87.3%</h3>
                            <p>Taux de réussite</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="excellentGrades">156</h3>
                            <p>Mentions TB/B</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="failingGrades">89</h3>
                            <p>Échecs (&lt;10)</p>
                        </div>
                    </div>
                </div>

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
                        <label>UE/Matière</label>
                        <select class="filter-select" id="filterSubject">
                            <option value="">Toutes les UE</option>
                            <option value="algo">Algorithmique</option>
                            <option value="bd">Base de données</option>
                            <option value="web">Développement Web</option>
                            <option value="math">Mathématiques</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Recherche</label>
                        <input type="text" class="search-input" id="searchStudents" placeholder="Nom, prénom...">
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('overview')">
                        <i class="fas fa-chart-pie"></i>
                        Vue d'ensemble
                    </button>
                    <button class="tab" onclick="switchTab('detailed')">
                        <i class="fas fa-table"></i>
                        Notes détaillées
                    </button>
                    <button class="tab" onclick="switchTab('analysis')">
                        <i class="fas fa-chart-line"></i>
                        Analyse comparative
                    </button>
                    <button class="tab" onclick="switchTab('bulletins')">
                        <i class="fas fa-file-alt"></i>
                        Bulletins
                    </button>
                </div>

                <!-- Overview Tab -->
                <div id="overviewTab" class="tab-content active">
                    <div class="charts-grid">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Distribution des notes</h3>
                                <div class="chart-actions">
                                    <button class="chart-btn active" onclick="changeChartType('gradesChart', 'bar')">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                    <button class="chart-btn" onclick="changeChartType('gradesChart', 'line')">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                </div>
                            </div>
                            <canvas id="gradesChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Répartition par mention</h3>
                            </div>
                            <canvas id="mentionsChart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    <div class="charts-grid">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Performance par UE</h3>
                            </div>
                            <canvas id="subjectsChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Évolution temporelle</h3>
                            </div>
                            <canvas id="evolutionChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Notes Tab -->
                <div id="detailedTab" class="tab-content">
                    <div class="data-table">
                        <div class="table-header">
                            <h3>Notes détaillées par étudiant</h3>
                            <div style="display: flex; gap: 1rem;">
                                <button class="btn btn-secondary" onclick="exportDetailedNotes()">
                                    <i class="fas fa-file-excel"></i>
                                    Export Excel
                                </button>
                                <button class="btn btn-info" onclick="printNotes()">
                                    <i class="fas fa-print"></i>
                                    Imprimer
                                </button>
                            </div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Niveau</th>
                                    <th>UE/Matière</th>
                                    <th>CC</th>
                                    <th>TP</th>
                                    <th>Examen</th>
                                    <th>Moyenne UE</th>
                                    <th>Coefficient</th>
                                    <th>Mention</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody id="detailedNotesTableBody">
                                <!-- Les données seront chargées dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Analysis Tab -->
                <div id="analysisTab" class="tab-content">
                    <div class="charts-grid">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Comparaison par niveau</h3>
                            </div>
                            <canvas id="levelComparisonChart" width="400" height="200"></canvas>
                        </div>
                        <div class="chart-container">
                            <div class="chart-header">
                                <h3>Comparaison par spécialité</h3>
                            </div>
                            <canvas id="specialtyComparisonChart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Analysis Table -->
                    <div class="data-table">
                        <div class="table-header">
                            <h3>Analyse statistique</h3>
                            <button class="btn btn-primary" onclick="generateAnalysisReport()">
                                <i class="fas fa-chart-bar"></i>
                                Rapport d'analyse
                            </button>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Critère</th>
                                    <th>Moyenne</th>
                                    <th>Médiane</th>
                                    <th>Écart-type</th>
                                    <th>Min</th>
                                    <th>Max</th>
                                    <th>Taux réussite</th>
                                </tr>
                            </thead>
                            <tbody id="analysisTableBody">
                                <!-- Les données seront chargées dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bulletins Tab -->
                <div id="bulletinsTab" class="tab-content">
                    <div class="data-table">
                        <div class="table-header">
                            <h3>Génération de bulletins</h3>
                            <div style="display: flex; gap: 1rem;">
                                <button class="btn btn-success" onclick="generateAllBulletins()">
                                    <i class="fas fa-file-pdf"></i>
                                    Tous les bulletins
                                </button>
                                <button class="btn btn-primary" onclick="generateSelectedBulletins()">
                                    <i class="fas fa-check-square"></i>
                                    Bulletins sélectionnés
                                </button>
                            </div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>Étudiant</th>
                                    <th>Niveau</th>
                                    <th>Moyenne générale</th>
                                    <th>Rang</th>
                                    <th>Mention</th>
                                    <th>UE validées</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="bulletinsTableBody">
                                <!-- Les données seront chargées dynamiquement -->
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
        let notesData = [
            {
                id: 1,
                studentName: 'KOUAME Jean-Baptiste',
                studentNumber: 'ETU2024001',
                level: 'master2',
                specialty: 'informatique',
                subject: 'Algorithmique',
                subjectCode: 'ALGO201',
                cc: 14.5,
                tp: 16.0,
                exam: 13.0,
                average: 14.2,
                coefficient: 3,
                mention: 'Bien',
                status: 'validated',
                rank: 5
            },
            {
                id: 2,
                studentName: 'TRAORE Marie-Claire',
                studentNumber: 'ETU2024002',
                level: 'master1',
                specialty: 'mathematiques',
                subject: 'Statistiques',
                subjectCode: 'STAT101',
                cc: 16.0,
                tp: 15.5,
                exam: 17.0,
                average: 16.2,
                coefficient: 4,
                mention: 'Très Bien',
                status: 'validated',
                rank: 1
            },
            {
                id: 3,
                studentName: 'DIALLO Amadou',
                studentNumber: 'ETU2024003',
                level: 'licence3',
                specialty: 'physique',
                subject: 'Mécanique',
                subjectCode: 'MECA301',
                cc: 8.5,
                tp: 9.0,
                exam: 7.5,
                average: 8.3,
                coefficient: 3,
                mention: 'Ajourné',
                status: 'pending',
                rank: 45
            }
        ];

        let filteredNotes = [...notesData];

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoToggle = document.getElementById('logoToggle');

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            initializeCharts();
            loadDetailedNotesData();
            loadAnalysisData();
            loadBulletinsData();
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
            document.getElementById('filterSubject')?.addEventListener('change', applyFilters);
            document.getElementById('searchStudents')?.addEventListener('input', debounce(applyFilters, 300));
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
                case 'analysis':
                    initializeAnalysisCharts();
                    break;
            }
        }

        function initializeOverviewCharts() {
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

            // Mentions chart
            const mentionsCtx = document.getElementById('mentionsChart');
            if (mentionsCtx && !charts.mentionsChart) {
                charts.mentionsChart = new Chart(mentionsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Très Bien', 'Bien', 'Assez Bien', 'Passable', 'Ajourné'],
                        datasets: [{
                            data: [67, 156, 298, 345, 89],
                            backgroundColor: [
                                'rgba(139, 92, 246, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(239, 68, 68, 0.8)'
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

            // Subjects performance chart
            const subjectsCtx = document.getElementById('subjectsChart');
            if (subjectsCtx && !charts.subjectsChart) {
                charts.subjectsChart = new Chart(subjectsCtx, {
                    type: 'radar',
                    data: {
                        labels: ['Algorithmique', 'Base de données', 'Développement Web', 'Mathématiques', 'Statistiques'],
                        datasets: [{
                            label: 'Moyenne par UE',
                            data: [13.2, 12.8, 14.1, 11.9, 13.5],
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
                                max: 20
                            }
                        }
                    }
                });
            }

            // Evolution chart
            const evolutionCtx = document.getElementById('evolutionChart');
            if (evolutionCtx && !charts.evolutionChart) {
                charts.evolutionChart = new Chart(evolutionCtx, {
                    type: 'line',
                    data: {
                        labels: ['Sept', 'Oct', 'Nov', 'Déc', 'Jan', 'Fév'],
                        datasets: [{
                            label: 'Moyenne générale',
                            data: [11.8, 12.1, 12.5, 12.8, 13.0, 12.8],
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
                                beginAtZero: false,
                                min: 10,
                                max: 15
                            }
                        }
                    }
                });
            }
        }

        function initializeAnalysisCharts() {
            // Level comparison chart
            const levelCtx = document.getElementById('levelComparisonChart');
            if (levelCtx && !charts.levelComparisonChart) {
                charts.levelComparisonChart = new Chart(levelCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2'],
                        datasets: [{
                            label: 'Moyenne',
                            data: [11.2, 11.8, 12.5, 13.1, 13.8],
                            backgroundColor: 'rgba(0, 51, 41, 0.8)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                min: 10,
                                max: 15
                            }
                        }
                    }
                });
            }

            // Specialty comparison chart
            const specialtyCtx = document.getElementById('specialtyComparisonChart');
            if (specialtyCtx && !charts.specialtyComparisonChart) {
                charts.specialtyComparisonChart = new Chart(specialtyCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Informatique', 'Mathématiques', 'Physique', 'Chimie'],
                        datasets: [{
                            label: 'Moyenne',
                            data: [13.2, 12.8, 12.1, 11.9],
                            backgroundColor: [
                                'rgba(0, 51, 41, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(245, 158, 11, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                min: 10,
                                max: 15
                            }
                        }
                    }
                });
            }
        }

        // Data loading functions
        function loadDetailedNotesData() {
            const tbody = document.getElementById('detailedNotesTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            filteredNotes.forEach(note => {
                const row = createDetailedNoteRow(note);
                tbody.appendChild(row);
            });
        }

        function createDetailedNoteRow(note) {
            const row = document.createElement('tr');
            
            const levelLabels = {
                'licence1': 'Licence 1',
                'licence2': 'Licence 2',
                'licence3': 'Licence 3',
                'master1': 'Master 1',
                'master2': 'Master 2'
            };

            const getGradeClass = (grade) => {
                if (grade >= 16) return 'grade-excellent';
                if (grade >= 14) return 'grade-good';
                if (grade >= 10) return 'grade-average';
                return 'grade-poor';
            };

            row.innerHTML = `
                <td>
                    <strong>${note.studentName}</strong><br>
                    <small>${note.studentNumber}</small>
                </td>
                <td>${levelLabels[note.level]}</td>
                <td>
                    <strong>${note.subject}</strong><br>
                    <small>${note.subjectCode}</small>
                </td>
                <td><span class="badge ${getGradeClass(note.cc)}">${note.cc}/20</span></td>
                <td><span class="badge ${getGradeClass(note.tp)}">${note.tp}/20</span></td>
                <td><span class="badge ${getGradeClass(note.exam)}">${note.exam}/20</span></td>
                <td><strong class="${getGradeClass(note.average)}">${note.average}/20</strong></td>
                <td>${note.coefficient}</td>
                <td><span class="badge ${getGradeClass(note.average)}">${note.mention}</span></td>
                <td><span class="badge status-${note.status}">${note.status === 'validated' ? 'Validé' : 'En attente'}</span></td>
            `;
            
            return row;
        }

        function loadAnalysisData() {
            const tbody = document.getElementById('analysisTableBody');
            if (!tbody) return;

            const analysisData = [
                {
                    criteria: 'Informatique',
                    average: 13.2,
                    median: 13.0,
                    stdDev: 2.1,
                    min: 8.5,
                    max: 18.0,
                    successRate: 89.5
                },
                {
                    criteria: 'Mathématiques',
                    average: 12.8,
                    median: 12.5,
                    stdDev: 2.3,
                    min: 7.0,
                    max: 19.0,
                    successRate: 85.2
                },
                {
                    criteria: 'Physique',
                    average: 12.1,
                    median: 12.0,
                    stdDev: 2.5,
                    min: 6.5,
                    max: 17.5,
                    successRate: 82.8
                }
            ];

            tbody.innerHTML = '';
            
            analysisData.forEach(data => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><strong>${data.criteria}</strong></td>
                    <td>${data.average.toFixed(2)}</td>
                    <td>${data.median.toFixed(2)}</td>
                    <td>${data.stdDev.toFixed(2)}</td>
                    <td>${data.min.toFixed(1)}</td>
                    <td>${data.max.toFixed(1)}</td>
                    <td><span class="badge grade-${data.successRate >= 85 ? 'excellent' : data.successRate >= 75 ? 'good' : 'average'}">${data.successRate}%</span></td>
                `;
                tbody.appendChild(row);
            });
        }

        function loadBulletinsData() {
            const tbody = document.getElementById('bulletinsTableBody');
            if (!tbody) return;

            // Group notes by student
            const studentsBulletins = {};
            filteredNotes.forEach(note => {
                if (!studentsBulletins[note.studentNumber]) {
                    studentsBulletins[note.studentNumber] = {
                        studentName: note.studentName,
                        studentNumber: note.studentNumber,
                        level: note.level,
                        notes: [],
                        totalAverage: 0,
                        rank: note.rank,
                        mention: '',
                        validatedUE: 0
                    };
                }
                studentsBulletins[note.studentNumber].notes.push(note);
            });

            tbody.innerHTML = '';
            
            Object.values(studentsBulletins).forEach(student => {
                // Calculate overall average
                const totalPoints = student.notes.reduce((sum, note) => sum + (note.average * note.coefficient), 0);
                const totalCoeff = student.notes.reduce((sum, note) => sum + note.coefficient, 0);
                student.totalAverage = totalPoints / totalCoeff;
                
                // Determine mention
                if (student.totalAverage >= 16) student.mention = 'Très Bien';
                else if (student.totalAverage >= 14) student.mention = 'Bien';
                else if (student.totalAverage >= 12) student.mention = 'Assez Bien';
                else if (student.totalAverage >= 10) student.mention = 'Passable';
                else student.mention = 'Ajourné';

                // Count validated UE
                student.validatedUE = student.notes.filter(note => note.average >= 10).length;

                const row = createBulletinRow(student);
                tbody.appendChild(row);
            });
        }

        function createBulletinRow(student) {
            const row = document.createElement('tr');
            
            const levelLabels = {
                'licence1': 'Licence 1',
                'licence2': 'Licence 2',
                'licence3': 'Licence 3',
                'master1': 'Master 1',
                'master2': 'Master 2'
            };

            const getGradeClass = (grade) => {
                if (grade >= 16) return 'grade-excellent';
                if (grade >= 14) return 'grade-good';
                if (grade >= 10) return 'grade-average';
                return 'grade-poor';
            };

            row.innerHTML = `
                <td>
                    <input type="checkbox" class="student-checkbox" value="${student.studentNumber}">
                </td>
                <td>
                    <strong>${student.studentName}</strong><br>
                    <small>${student.studentNumber}</small>
                </td>
                <td>${levelLabels[student.level]}</td>
                <td><strong class="${getGradeClass(student.totalAverage)}">${student.totalAverage.toFixed(2)}/20</strong></td>
                <td>${student.rank}</td>
                <td><span class="badge ${getGradeClass(student.totalAverage)}">${student.mention}</span></td>
                <td>${student.validatedUE}/${student.notes.length}</td>
                <td><span class="badge status-${student.totalAverage >= 10 ? 'validated' : 'pending'}">${student.totalAverage >= 10 ? 'Admis' : 'Ajourné'}</span></td>
                <td>
                    <button class="btn-icon" onclick="generateIndividualBulletin('${student.studentNumber}')" title="Bulletin individuel">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                    <button class="btn-icon" onclick="viewStudentDetails('${student.studentNumber}')" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            `;
            
            return row;
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

        // Filter functions
        function applyFilters() {
            const year = document.getElementById('filterYear').value;
            const semester = document.getElementById('filterSemester').value;
            const level = document.getElementById('filterLevel').value;
            const specialty = document.getElementById('filterSpecialty').value;
            const subject = document.getElementById('filterSubject').value;
            const searchTerm = document.getElementById('searchStudents').value.toLowerCase();

            filteredNotes = notesData.filter(note => {
                const matchesLevel = !level || note.level === level;
                const matchesSpecialty = !specialty || note.specialty === specialty;
                const matchesSubject = !subject || note.subject.toLowerCase().includes(subject.toLowerCase());
                const matchesSearch = !searchTerm || 
                    note.studentName.toLowerCase().includes(searchTerm) ||
                    note.studentNumber.toLowerCase().includes(searchTerm);

                return matchesLevel && matchesSpecialty && matchesSubject && matchesSearch;
            });

            loadDetailedNotesData();
            loadBulletinsData();
            updateStatistics();
        }

        // Statistics functions
        function updateStatistics() {
            const totalStudents = new Set(filteredNotes.map(note => note.studentNumber)).size;
            const averageGrade = filteredNotes.reduce((sum, note) => sum + note.average, 0) / filteredNotes.length;
            const successfulNotes = filteredNotes.filter(note => note.average >= 10);
            const successRate = (successfulNotes.length / filteredNotes.length) * 100;
            const excellentGrades = filteredNotes.filter(note => note.average >= 14).length;
            const failingGrades = filteredNotes.filter(note => note.average < 10).length;

            document.getElementById('totalStudents').textContent = totalStudents;
            document.getElementById('averageGrade').textContent = averageGrade.toFixed(1);
            document.getElementById('successRate').textContent = successRate.toFixed(1) + '%';
            document.getElementById('excellentGrades').textContent = excellentGrades;
            document.getElementById('failingGrades').textContent = failingGrades;
        }

        // Export and generation functions
        function exportNotes() {
            const headers = ['Étudiant', 'Numéro', 'Niveau', 'UE', 'CC', 'TP', 'Examen', 'Moyenne', 'Mention'];
            const csvContent = [
                headers.join(','),
                ...filteredNotes.map(note => [
                    `"${note.studentName}"`,
                    note.studentNumber,
                    note.level,
                    `"${note.subject}"`,
                    note.cc,
                    note.tp,
                    note.exam,
                    note.average,
                    `"${note.mention}"`
                ].join(','))
            ].join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `notes_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            
            showNotification('Export des notes terminé', 'success');
        }

        function exportDetailedNotes() {
            showNotification('Export Excel en cours...', 'info');
            
            setTimeout(() => {
                showNotification('Export Excel terminé avec succès', 'success');
            }, 2000);
        }

        function printNotes() {
            window.print();
        }

        function generateBulletins() {
            showNotification('Génération des bulletins en cours...', 'info');
            
            setTimeout(() => {
                showNotification('Bulletins générés avec succès', 'success');
            }, 3000);
        }

        function generateAllBulletins() {
            if (confirm('Générer tous les bulletins ? Cette opération peut prendre du temps.')) {
                showNotification('Génération de tous les bulletins en cours...', 'info');
                
                setTimeout(() => {
                    showNotification('Tous les bulletins ont été générés avec succès', 'success');
                }, 5000);
            }
        }

        function generateSelectedBulletins() {
            const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
            
            if (selectedCheckboxes.length === 0) {
                showNotification('Veuillez sélectionner au moins un étudiant', 'warning');
                return;
            }

            showNotification(`Génération de ${selectedCheckboxes.length} bulletin(s) en cours...`, 'info');
            
            setTimeout(() => {
                showNotification(`${selectedCheckboxes.length} bulletin(s) généré(s) avec succès`, 'success');
            }, 3000);
        }

        function generateIndividualBulletin(studentNumber) {
            showNotification(`Génération du bulletin pour ${studentNumber}...`, 'info');
            
            setTimeout(() => {
                showNotification('Bulletin généré avec succès', 'success');
            }, 2000);
        }

        function generateAnalysisReport() {
            showNotification('Génération du rapport d\'analyse en cours...', 'info');
            
            setTimeout(() => {
                showNotification('Rapport d\'analyse généré avec succès', 'success');
            }, 3000);
        }

        // Utility functions
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.student-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        function viewStudentDetails(studentNumber) {
            showNotification('Fonctionnalité de détails étudiant en cours de développement', 'info');
        }

        function logout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = '../../../logout.php';
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

        // Utility functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

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
