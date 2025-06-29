<?php
/*
session_start();

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responsable_scolarite') {
    header('Location: ../../../login.php');
    exit();
}

// Inclusion des fichiers de configuration
require_once '../../../config/database.php';
require_once '../../../config/constants.php';
require_once '../../../includes/functions.php';*/
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'Éligibilité - Responsable Scolarité</title>
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

        .btn-danger {
            background: var(--error-color);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-icon {
            padding: 0.5rem;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 6px;
            transition: var(--transition);
            color: var(--gray-600);
        }

        .btn-icon:hover {
            background: var(--gray-100);
            color: var(--primary-color);
        }

        .btn-icon.btn-danger:hover {
            background: var(--error-color);
            color: var(--white);
        }

        /* Section Header */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Filters Bar */
        .filters-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select,
        .search-input {
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .filter-select:focus,
        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .search-input {
            flex: 1;
            max-width: 300px;
        }

        /* Data Table */
        .data-table {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
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

        .badge-eligible {
            background: #dcfce7;
            color: #166534;
        }

        .badge-not-eligible {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-master {
            background: #ddd6fe;
            color: #7c3aed;
        }

        .badge-licence {
            background: #fed7d7;
            color: #c53030;
        }

        .status-paid {
            color: var(--success-color);
            font-weight: 500;
        }

        .status-unpaid {
            color: var(--error-color);
            font-weight: 500;
        }

        .status-partial {
            color: var(--warning-color);
            font-weight: 500;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card.eligible h3 {
            color: var(--success-color);
        }

        .stat-card.not-eligible h3 {
            color: var(--error-color);
        }

        .stat-card.pending h3 {
            color: var(--warning-color);
        }

        .stat-card.total h3 {
            color: var(--primary-color);
        }

        .stat-card p {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-xl);
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--gray-500);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .modal-close:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        /* Eligibility Details */
        .eligibility-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .detail-section {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 8px;
        }

        .detail-section h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: var(--gray-700);
        }

        .detail-value {
            font-weight: 600;
        }

        .detail-value.success {
            color: var(--success-color);
        }

        .detail-value.error {
            color: var(--error-color);
        }

        .detail-value.warning {
            color: var(--warning-color);
        }

        /* Progress Bar */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }

        .progress-fill {
            height: 100%;
            background: var(--success-color);
            transition: width 0.3s ease;
        }

        .progress-fill.warning {
            background: var(--warning-color);
        }

        .progress-fill.error {
            background: var(--error-color);
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 300px;
            max-width: 500px;
            z-index: 3000;
            animation: slideInRight 0.3s ease-out;
            border-left: 4px solid var(--primary-color);
        }

        .notification-success {
            border-left-color: var(--success-color);
        }

        .notification-error {
            border-left-color: var(--error-color);
        }

        .notification-warning {
            border-left-color: var(--warning-color);
        }

        .notification-info {
            border-left-color: var(--primary-color);
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .notification-success .notification-content i {
            color: var(--success-color);
        }

        .notification-error .notification-content i {
            color: var(--error-color);
        }

        .notification-warning .notification-content i {
            color: var(--warning-color);
        }

        .notification-info .notification-content i {
            color: var(--primary-color);
        }

        .notification-close {
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: var(--transition);
        }

        .notification-close:hover {
            background: var(--gray-100);
            color: var(--gray-700);
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

            .filters-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input {
                max-width: none;
            }

            .data-table {
                overflow-x: auto;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .eligibility-details {
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
                        <a href="gestion.php">
                            <i class="fas fa-users"></i>
                            <span>Gestion Étudiants</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="../notes/saisie.php">
                            <i class="fas fa-edit"></i>
                            <span>Saisie Notes</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="../notes/consultation.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Consultation Notes</span>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="eligibilite.php">
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
                <h1>Vérification d'Éligibilité</h1>
                <div class="header-actions">
                    <button class="btn btn-success" onclick="checkAllEligibility()">
                        <i class="fas fa-check-double"></i>
                        Vérifier Tous
                    </button>
                </div>
            </header>

            <div class="content-body">
                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card total">
                        <h3 id="totalStudents">1,247</h3>
                        <p>Total Étudiants</p>
                    </div>
                    <div class="stat-card eligible">
                        <h3 id="eligibleStudents">1,024</h3>
                        <p>Étudiants Éligibles</p>
                    </div>
                    <div class="stat-card not-eligible">
                        <h3 id="notEligibleStudents">156</h3>
                        <p>Non Éligibles</p>
                    </div>
                    <div class="stat-card pending">
                        <h3 id="pendingStudents">67</h3>
                        <p>En Cours de Vérification</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-bar">
                    <select class="filter-select" id="filterEligibility">
                        <option value="">Tous les statuts</option>
                        <option value="eligible">Éligible</option>
                        <option value="not-eligible">Non Éligible</option>
                        <option value="pending">En Cours</option>
                    </select>
                    <select class="filter-select" id="filterLevel">
                        <option value="">Tous les niveaux</option>
                        <option value="licence3">Licence 3</option>
                        <option value="master1">Master 1</option>
                        <option value="master2">Master 2</option>
                    </select>
                    <select class="filter-select" id="filterSpecialty">
                        <option value="">Toutes les spécialités</option>
                        <option value="informatique">Informatique</option>
                        <option value="mathematiques">Mathématiques</option>
                        <option value="physique">Physique</option>
                        <option value="chimie">Chimie</option>
                    </select>
                    <input type="text" class="search-input" id="searchInput" placeholder="Rechercher un étudiant...">
                    <button class="btn btn-secondary" onclick="exportEligibility()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                </div>

                <!-- Eligibility Table -->
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Niveau</th>
                                <th>Spécialité</th>
                                <th>UE Validées</th>
                                <th>Moyenne Générale</th>
                                <th>Frais Scolarité</th>
                                <th>Statut Éligibilité</th>
                                <th>Dernière Vérification</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="eligibilityTableBody">
                            <!-- Les données seront chargées dynamiquement -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Eligibility Details Modal -->
    <div id="eligibilityDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Détails d'Éligibilité</h3>
                <button class="modal-close" onclick="closeModal('eligibilityDetailsModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="eligibilityDetailsContent">
                    <!-- Le contenu sera chargé dynamiquement -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('eligibilityDetailsModal')">Fermer</button>
                <button class="btn btn-primary" onclick="printEligibilityDetails()">
                    <i class="fas fa-print"></i>
                    Imprimer
                </button>
            </div>
        </div>
    </div>

    <!-- Force Eligibility Modal -->
    <div id="forceEligibilityModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Forcer l'Éligibilité</h3>
                <button class="modal-close" onclick="closeModal('forceEligibilityModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="background: var(--warning-color); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Attention:</strong> Cette action forcera l'éligibilité malgré les conditions non remplies.
                </div>
                <div id="forceEligibilityContent">
                    <!-- Le contenu sera chargé dynamiquement -->
                </div>
                <div style="margin-top: 1rem;">
                    <label for="forceReason" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Motif de la dérogation *</label>
                    <textarea id="forceReason" rows="4" style="width: 100%; padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: 8px;" placeholder="Veuillez justifier cette dérogation..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('forceEligibilityModal')">Annuler</button>
                <button class="btn btn-warning" onclick="submitForceEligibility()">
                    <i class="fas fa-exclamation-triangle"></i>
                    Forcer l'Éligibilité
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let students = [
            {
                id: 1,
                studentNumber: 'ETU2024001',
                firstName: 'Jean-Baptiste',
                lastName: 'KOUAME',
                level: 'master2',
                specialty: 'informatique',
                validatedUE: 8,
                totalUE: 10,
                average: 14.5,
                feesPaid: true,
                eligibilityStatus: 'not-eligible',
                lastCheck: '2024-01-15',
                conditions: {
                    ueValidated: false,
                    averageOk: true,
                    feesPaid: true
                }
            },
            {
                id: 2,
                studentNumber: 'ETU2024002',
                firstName: 'Marie-Claire',
                lastName: 'TRAORE',
                level: 'master2',
                specialty: 'mathematiques',
                validatedUE: 10,
                totalUE: 10,
                average: 16.2,
                feesPaid: true,
                eligibilityStatus: 'eligible',
                lastCheck: '2024-01-14',
                conditions: {
                    ueValidated: true,
                    averageOk: true,
                    feesPaid: true
                }
            },
            {
                id: 3,
                studentNumber: 'ETU2024003',
                firstName: 'Amadou',
                lastName: 'DIALLO',
                level: 'licence3',
                specialty: 'physique',
                validatedUE: 7,
                totalUE: 8,
                average: 8.7,
                feesPaid: false,
                eligibilityStatus: 'not-eligible',
                lastCheck: '2024-01-13',
                conditions: {
                    ueValidated: false,
                    averageOk: false,
                    feesPaid: false
                }
            },
            {
                id: 4,
                studentNumber: 'ETU2024004',
                firstName: 'Fatou',
                lastName: 'KONE',
                level: 'master1',
                specialty: 'chimie',
                validatedUE: 9,
                totalUE: 10,
                average: 12.8,
                feesPaid: true,
                eligibilityStatus: 'pending',
                lastCheck: '2024-01-12',
                conditions: {
                    ueValidated: false,
                    averageOk: true,
                    feesPaid: true
                }
            }
        ];

        let filteredStudents = [...students];

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoToggle = document.getElementById('logoToggle');
        const searchInput = document.getElementById('searchInput');
        const filterEligibility = document.getElementById('filterEligibility');
        const filterLevel = document.getElementById('filterLevel');
        const filterSpecialty = document.getElementById('filterSpecialty');

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            loadEligibilityData();
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

            // Search and filter listeners
            searchInput.addEventListener('input', debounce(filterStudents, 300));
            filterEligibility.addEventListener('change', filterStudents);
            filterLevel.addEventListener('change', filterStudents);
            filterSpecialty.addEventListener('change', filterStudents);

            // Close modal when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal')) {
                    closeModal(e.target.id);
                }
            });
        }

        // Sidebar functions
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
        }

        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }

        // Eligibility management functions
        function loadEligibilityData() {
            const tbody = document.getElementById('eligibilityTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            filteredStudents.forEach(student => {
                const row = createEligibilityRow(student);
                tbody.appendChild(row);
            });
        }

        function createEligibilityRow(student) {
            const row = document.createElement('tr');
            
            const levelLabels = {
                'licence3': 'Licence 3',
                'master1': 'Master 1',
                'master2': 'Master 2'
            };

            const specialtyLabels = {
                'informatique': 'Informatique',
                'mathematiques': 'Mathématiques',
                'physique': 'Physique',
                'chimie': 'Chimie'
            };

            const statusLabels = {
                'eligible': 'Éligible',
                'not-eligible': 'Non Éligible',
                'pending': 'En Cours'
            };

            const feesStatus = student.feesPaid ? 'Réglés' : 'Non Réglés';
            const feesClass = student.feesPaid ? 'status-paid' : 'status-unpaid';

            row.innerHTML = `
                <td><strong>${student.firstName} ${student.lastName}</strong><br><small>${student.studentNumber}</small></td>
                <td><span class="badge badge-${student.level.includes('licence') ? 'licence' : 'master'}">${levelLabels[student.level]}</span></td>
                <td>${specialtyLabels[student.specialty]}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span><strong>${student.validatedUE}/${student.totalUE}</strong></span>
                        <div class="progress-bar" style="flex: 1;">
                            <div class="progress-fill ${student.validatedUE >= student.totalUE ? '' : (student.validatedUE >= student.totalUE * 0.8 ? 'warning' : 'error')}" 
                                 style="width: ${(student.validatedUE / student.totalUE) * 100}%"></div>
                        </div>
                    </div>
                </td>
                <td><strong>${student.average}/20</strong></td>
                <td><span class="${feesClass}">${feesStatus}</span></td>
                <td><span class="badge badge-${student.eligibilityStatus}">${statusLabels[student.eligibilityStatus]}</span></td>
                <td>${formatDate(student.lastCheck)}</td>
                <td>
                    <button class="btn-icon" onclick="viewEligibilityDetails(${student.id})" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" onclick="checkEligibility(${student.id})" title="Vérifier">
                        <i class="fas fa-sync"></i>
                    </button>
                    ${student.eligibilityStatus === 'not-eligible' ? 
                        `<button class="btn-icon" onclick="forceEligibility(${student.id})" title="Forcer éligibilité" style="color: var(--warning-color);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </button>` : ''
                    }
                </td>
            `;
            
            return row;
        }

        function filterStudents() {
            const searchTerm = searchInput.value.toLowerCase();
            const eligibilityFilter = filterEligibility.value;
            const levelFilter = filterLevel.value;
            const specialtyFilter = filterSpecialty.value;

            filteredStudents = students.filter(student => {
                const matchesSearch = !searchTerm || 
                    student.firstName.toLowerCase().includes(searchTerm) ||
                    student.lastName.toLowerCase().includes(searchTerm) ||
                    student.studentNumber.toLowerCase().includes(searchTerm);

                const matchesEligibility = !eligibilityFilter || student.eligibilityStatus === eligibilityFilter;
                const matchesLevel = !levelFilter || student.level === levelFilter;
                const matchesSpecialty = !specialtyFilter || student.specialty === specialtyFilter;

                return matchesSearch && matchesEligibility && matchesLevel && matchesSpecialty;
            });

            loadEligibilityData();
            updateStatistics();
        }

        function updateStatistics() {
            document.getElementById('totalStudents').textContent = students.length.toLocaleString();
            document.getElementById('eligibleStudents').textContent = students.filter(s => s.eligibilityStatus === 'eligible').length.toLocaleString();
            document.getElementById('notEligibleStudents').textContent = students.filter(s => s.eligibilityStatus === 'not-eligible').length.toLocaleString();
            document.getElementById('pendingStudents').textContent = students.filter(s => s.eligibilityStatus === 'pending').length.toLocaleString();
        }

        function checkEligibility(studentId) {
            const student = students.find(s => s.id === studentId);
            if (!student) return;

            // Simulate eligibility check
            showNotification('Vérification de l\'éligibilité en cours...', 'info');
            
            setTimeout(() => {
                // Update conditions
                student.conditions.ueValidated = student.validatedUE >= student.totalUE;
                student.conditions.averageOk = student.average >= 10;
                student.conditions.feesPaid = student.feesPaid;

                // Determine eligibility
                if (student.conditions.ueValidated && student.conditions.averageOk && student.conditions.feesPaid) {
                    student.eligibilityStatus = 'eligible';
                } else {
                    student.eligibilityStatus = 'not-eligible';
                }

                student.lastCheck = new Date().toISOString().split('T')[0];

                loadEligibilityData();
                updateStatistics();
                showNotification(`Éligibilité vérifiée: ${student.eligibilityStatus === 'eligible' ? 'Éligible' : 'Non éligible'}`, 
                    student.eligibilityStatus === 'eligible' ? 'success' : 'warning');
            }, 2000);
        }

        function checkAllEligibility() {
            showNotification('Vérification de l\'éligibilité de tous les étudiants...', 'info');
            
            setTimeout(() => {
                students.forEach(student => {
                    student.conditions.ueValidated = student.validatedUE >= student.totalUE;
                    student.conditions.averageOk = student.average >= 10;
                    student.conditions.feesPaid = student.feesPaid;

                    if (student.conditions.ueValidated && student.conditions.averageOk && student.conditions.feesPaid) {
                        student.eligibilityStatus = 'eligible';
                    } else {
                        student.eligibilityStatus = 'not-eligible';
                    }

                    student.lastCheck = new Date().toISOString().split('T')[0];
                });

                loadEligibilityData();
                updateStatistics();
                showNotification('Vérification terminée pour tous les étudiants', 'success');
            }, 3000);
        }

        function viewEligibilityDetails(studentId) {
            const student = students.find(s => s.id === studentId);
            if (!student) return;

            const levelLabels = {
                'licence3': 'Licence 3',
                'master1': 'Master 1',
                'master2': 'Master 2'
            };

            const specialtyLabels = {
                'informatique': 'Informatique',
                'mathematiques': 'Mathématiques',
                'physique': 'Physique',
                'chimie': 'Chimie'
            };

            const detailsContent = `
                <div style="margin-bottom: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Informations de l'étudiant</h4>
                    <p><strong>Nom complet:</strong> ${student.firstName} ${student.lastName}</p>
                    <p><strong>Numéro étudiant:</strong> ${student.studentNumber}</p>
                    <p><strong>Niveau:</strong> ${levelLabels[student.level]}</p>
                    <p><strong>Spécialité:</strong> ${specialtyLabels[student.specialty]}</p>
                </div>

                <div class="eligibility-details">
                    <div class="detail-section">
                        <h4>Conditions Académiques</h4>
                        <div class="detail-item">
                            <span class="detail-label">UE Validées</span>
                            <span class="detail-value ${student.conditions.ueValidated ? 'success' : 'error'}">
                                ${student.validatedUE}/${student.totalUE} ${student.conditions.ueValidated ? '✓' : '✗'}
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Moyenne Générale</span>
                            <span class="detail-value ${student.conditions.averageOk ? 'success' : 'error'}">
                                ${student.average}/20 ${student.conditions.averageOk ? '✓' : '✗'}
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Seuil requis</span>
                            <span class="detail-value">≥ 10/20</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Conditions Administratives</h4>
                        <div class="detail-item">
                            <span class="detail-label">Frais de Scolarité</span>
                            <span class="detail-value ${student.conditions.feesPaid ? 'success' : 'error'}">
                                ${student.feesPaid ? 'Réglés ✓' : 'Non réglés ✗'}
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Statut Inscription</span>
                            <span class="detail-value success">Actif ✓</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Documents</span>
                            <span class="detail-value success">Complets ✓</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem; padding: 1rem; background: ${student.eligibilityStatus === 'eligible' ? 'var(--success-color)' : 'var(--error-color)'}; color: white; border-radius: 8px; text-align: center;">
                    <h4 style="margin: 0;">
                        ${student.eligibilityStatus === 'eligible' ? 
                            '<i class="fas fa-check-circle"></i> ÉTUDIANT ÉLIGIBLE' : 
                            '<i class="fas fa-times-circle"></i> ÉTUDIANT NON ÉLIGIBLE'
                        }
                    </h4>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                        ${student.eligibilityStatus === 'eligible' ? 
                            'Cet étudiant peut déposer son rapport de mémoire/stage' : 
                            'Cet étudiant ne remplit pas toutes les conditions requises'
                        }
                    </p>
                </div>

                <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--gray-600);">
                    <p><strong>Dernière vérification:</strong> ${formatDate(student.lastCheck)}</p>
                </div>
            `;

            document.getElementById('eligibilityDetailsContent').innerHTML = detailsContent;
            openModal('eligibilityDetailsModal');
        }

        function forceEligibility(studentId) {
            const student = students.find(s => s.id === studentId);
            if (!student) return;

            const forceContent = `
                <div style="margin-bottom: 1rem;">
                    <h4 style="color: var(--primary-color);">Étudiant: ${student.firstName} ${student.lastName}</h4>
                    <p><strong>Numéro:</strong> ${student.studentNumber}</p>
                </div>

                <div style="background: var(--gray-50); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <h5 style="color: var(--error-color); margin-bottom: 0.5rem;">Conditions non remplies:</h5>
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        ${!student.conditions.ueValidated ? `<li>UE validées: ${student.validatedUE}/${student.totalUE} (insuffisant)</li>` : ''}
                        ${!student.conditions.averageOk ? `<li>Moyenne générale: ${student.average}/20 (< 10)</li>` : ''}
                        ${!student.conditions.feesPaid ? '<li>Frais de scolarité non réglés</li>' : ''}
                    </ul>
                </div>
            `;

            document.getElementById('forceEligibilityContent').innerHTML = forceContent;
            document.getElementById('forceReason').value = '';
            openModal('forceEligibilityModal');
        }

        function submitForceEligibility() {
            const reason = document.getElementById('forceReason').value.trim();
            
            if (!reason) {
                showNotification('Veuillez saisir un motif pour la dérogation', 'error');
                return;
            }

            // Here you would typically send the data to the server
            // For now, we'll just simulate the action
            
            showNotification('Éligibilité forcée avec succès. Dérogation enregistrée.', 'warning');
            closeModal('forceEligibilityModal');
            
            // Update the student's status (in a real app, this would be done server-side)
            // This is just for demonstration
        }

        function exportEligibility() {
            // Simple CSV export
            const headers = ['Numéro', 'Nom', 'Prénom', 'Niveau', 'Spécialité', 'UE Validées', 'Moyenne', 'Frais Réglés', 'Statut Éligibilité'];
            const csvContent = [
                headers.join(','),
                ...filteredStudents.map(student => [
                    student.studentNumber,
                    student.lastName,
                    student.firstName,
                    student.level,
                    student.specialty,
                    `${student.validatedUE}/${student.totalUE}`,
                    student.average,
                    student.feesPaid ? 'Oui' : 'Non',
                    student.eligibilityStatus
                ].join(','))
            ].join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `eligibilite_etudiants_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            
            showNotification('Export de l\'éligibilité terminé', 'success');
        }

        function printEligibilityDetails() {
            window.print();
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
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        }

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