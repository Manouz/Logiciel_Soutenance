<?php

session_start();
require_once '../../../config/database.php';
require_once '../../../config/session.php';

// Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responsable_scolarite') {
    header('Location: ../../../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Rapports - Responsable Scolarité</title>
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

        .btn-danger {
            background: var(--error-color);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-info {
            background: var(--info-color);
            color: var(--white);
        }

        .btn-info:hover {
            background: #2563eb;
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

        .btn-icon.btn-success:hover {
            background: var(--success-color);
            color: var(--white);
        }

        .btn-icon.btn-warning:hover {
            background: var(--warning-color);
            color: var(--white);
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

        .status-deposited {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-validated {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-in-review {
            background: #fef3c7;
            color: #92400e;
        }

        .status-assigned {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-scheduled {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .priority-high {
            background: #fee2e2;
            color: #991b1b;
        }

        .priority-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .priority-low {
            background: #dcfce7;
            color: #166534;
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
            max-width: 800px;
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

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 2rem;
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

            .form-row {
                grid-template-columns: 1fr;
            }

            .stats-grid {
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
                    <li class="menu-item">
                        <a href="../etudiants/eligibilite.php">
                            <i class="fas fa-check-circle"></i>
                            <span>Vérification Éligibilité</span>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="suivi.php">
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
                <h1>Suivi des Rapports</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openModal('addReportModal')">
                        <i class="fas fa-plus"></i>
                        Enregistrer un rapport
                    </button>
                </div>
            </header>

            <div class="content-body">
                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card" onclick="filterByStatus('deposited')">
                        <div class="stat-icon">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalReports">89</h3>
                            <p>Rapports déposés</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="filterByStatus('in-review')">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="pendingReports">34</h3>
                            <p>En cours d'examen</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="filterByStatus('validated')">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="validatedReports">45</h3>
                            <p>Rapports validés</p>
                        </div>
                    </div>
                    <div class="stat-card" onclick="filterByStatus('scheduled')">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="scheduledDefenses">28</h3>
                            <p>Soutenances planifiées</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="availableSupervisors">67</h3>
                            <p>Encadrants disponibles</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('reports')">
                        <i class="fas fa-file-alt"></i>
                        Rapports
                    </button>
                    <button class="tab" onclick="switchTab('defenses')">
                        <i class="fas fa-calendar-alt"></i>
                        Soutenances
                    </button>
                    <button class="tab" onclick="switchTab('supervisors')">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Encadrants
                    </button>
                </div>

                <!-- Reports Tab Content -->
                <div id="reportsTab" class="tab-content active">
                    <!-- Filters -->
                    <div class="filters-bar">
                        <select class="filter-select" id="filterStatus">
                            <option value="">Tous les statuts</option>
                            <option value="deposited">Déposé</option>
                            <option value="in-review">En examen</option>
                            <option value="validated">Validé</option>
                            <option value="rejected">Rejeté</option>
                            <option value="assigned">Encadrant assigné</option>
                        </select>
                        <select class="filter-select" id="filterType">
                            <option value="">Tous les types</option>
                            <option value="memoire">Mémoire</option>
                            <option value="stage">Rapport de stage</option>
                            <option value="projet">Projet de fin d'études</option>
                        </select>
                        <select class="filter-select" id="filterLevel">
                            <option value="">Tous les niveaux</option>
                            <option value="licence3">Licence 3</option>
                            <option value="master1">Master 1</option>
                            <option value="master2">Master 2</option>
                        </select>
                        <input type="text" class="search-input" id="searchReports" placeholder="Rechercher par étudiant, titre ou encadrant...">
                        <button class="btn btn-secondary" onclick="exportReports()">
                            <i class="fas fa-download"></i>
                            Exporter
                        </button>
                    </div>

                    <!-- Reports Table -->
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Titre du rapport</th>
                                    <th>Type</th>
                                    <th>Niveau</th>
                                    <th>Date de dépôt</th>
                                    <th>Statut</th>
                                    <th>Encadrant</th>
                                    <th>Priorité</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                                <!-- Les données seront chargées dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Defenses Tab Content -->
                <div id="defensesTab" class="tab-content">
                    <!-- Filters -->
                    <div class="filters-bar">
                        <select class="filter-select" id="filterDefenseStatus">
                            <option value="">Tous les statuts</option>
                            <option value="scheduled">Planifiée</option>
                            <option value="confirmed">Confirmée</option>
                            <option value="completed">Terminée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                        <input type="date" class="filter-select" id="filterDate">
                        <input type="text" class="search-input" id="searchDefenses" placeholder="Rechercher par étudiant ou salle...">
                        <button class="btn btn-primary" onclick="openModal('scheduleDefenseModal')">
                            <i class="fas fa-calendar-plus"></i>
                            Planifier une soutenance
                        </button>
                    </div>

                    <!-- Defenses Table -->
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Titre</th>
                                    <th>Date & Heure</th>
                                    <th>Salle</th>
                                    <th>Président du jury</th>
                                    <th>Encadrant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="defensesTableBody">
                                <!-- Les données seront chargées dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Supervisors Tab Content -->
                <div id="supervisorsTab" class="tab-content">
                    <!-- Filters -->
                    <div class="filters-bar">
                        <select class="filter-select" id="filterDepartment">
                            <option value="">Tous les départements</option>
                            <option value="informatique">Informatique</option>
                            <option value="mathematiques">Mathématiques</option>
                            <option value="physique">Physique</option>
                            <option value="chimie">Chimie</option>
                        </select>
                        <select class="filter-select" id="filterAvailability">
                            <option value="">Tous</option>
                            <option value="available">Disponible</option>
                            <option value="busy">Occupé</option>
                        </select>
                        <input type="text" class="search-input" id="searchSupervisors" placeholder="Rechercher par nom ou spécialité...">
                    </div>

                    <!-- Supervisors Table -->
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom & Prénom</th>
                                    <th>Grade</th>
                                    <th>Département</th>
                                    <th>Spécialité</th>
                                    <th>Rapports encadrés</th>
                                    <th>Disponibilité</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="supervisorsTableBody">
                                <!-- Les données seront chargées dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Report Modal -->
    <div id="addReportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Enregistrer un nouveau rapport</h3>
                <button class="modal-close" onclick="closeModal('addReportModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addReportForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="studentSelect">Étudiant *</label>
                            <select id="studentSelect" name="studentId" required>
                                <option value="">Sélectionner un étudiant</option>
                                <!-- Options chargées dynamiquement -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reportType">Type de rapport *</label>
                            <select id="reportType" name="reportType" required>
                                <option value="">Sélectionner le type</option>
                                <option value="memoire">Mémoire</option>
                                <option value="stage">Rapport de stage</option>
                                <option value="projet">Projet de fin d'études</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reportTitle">Titre du rapport *</label>
                        <input type="text" id="reportTitle" name="reportTitle" required placeholder="Titre complet du rapport">
                    </div>
                    <div class="form-group">
                        <label for="reportDescription">Description/Résumé</label>
                        <textarea id="reportDescription" name="reportDescription" placeholder="Description ou résumé du rapport"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="submissionDate">Date de dépôt *</label>
                            <input type="date" id="submissionDate" name="submissionDate" required>
                        </div>
                        <div class="form-group">
                            <label for="reportPriority">Priorité</label>
                            <select id="reportPriority" name="reportPriority">
                                <option value="low">Basse</option>
                                <option value="medium" selected>Moyenne</option>
                                <option value="high">Haute</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reportFile">Fichier du rapport</label>
                        <input type="file" id="reportFile" name="reportFile" accept=".pdf,.doc,.docx">
                        <small style="color: var(--gray-500);">Formats acceptés: PDF, DOC, DOCX (Max: 10MB)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('addReportModal')">Annuler</button>
                <button class="btn btn-primary" onclick="submitAddReport()">Enregistrer le rapport</button>
            </div>
        </div>
    </div>

    <!-- Assign Supervisor Modal -->
    <div id="assignSupervisorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Assigner un encadrant</h3>
                <button class="modal-close" onclick="closeModal('assignSupervisorModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignSupervisorForm">
                    <input type="hidden" id="assignReportId" name="reportId">
                    <div class="form-group">
                        <label>Rapport sélectionné</label>
                        <div id="selectedReportInfo" style="background: var(--gray-50); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <!-- Informations du rapport sélectionné -->
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="supervisorSelect">Encadrant *</label>
                        <select id="supervisorSelect" name="supervisorId" required>
                            <option value="">Sélectionner un encadrant</option>
                            <!-- Options chargées dynamiquement -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="assignmentNotes">Notes d'assignation</label>
                        <textarea id="assignmentNotes" name="assignmentNotes" placeholder="Notes ou instructions pour l'encadrant"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('assignSupervisorModal')">Annuler</button>
                <button class="btn btn-primary" onclick="submitAssignSupervisor()">Assigner l'encadrant</button>
            </div>
        </div>
    </div>

    <!-- Schedule Defense Modal -->
    <div id="scheduleDefenseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Planifier une soutenance</h3>
                <button class="modal-close" onclick="closeModal('scheduleDefenseModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="scheduleDefenseForm">
                    <div class="form-group">
                        <label for="defenseReportSelect">Rapport *</label>
                        <select id="defenseReportSelect" name="reportId" required>
                            <option value="">Sélectionner un rapport validé</option>
                            <!-- Options chargées dynamiquement -->
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="defenseDate">Date de soutenance *</label>
                            <input type="date" id="defenseDate" name="defenseDate" required>
                        </div>
                        <div class="form-group">
                            <label for="defenseTime">Heure *</label>
                            <input type="time" id="defenseTime" name="defenseTime" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="defenseRoom">Salle *</label>
                            <select id="defenseRoom" name="defenseRoom" required>
                                <option value="">Sélectionner une salle</option>
                                <option value="amphi-a">Amphithéâtre A</option>
                                <option value="amphi-b">Amphithéâtre B</option>
                                <option value="salle-101">Salle 101</option>
                                <option value="salle-102">Salle 102</option>
                                <option value="salle-201">Salle 201</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="defenseDuration">Durée (minutes)</label>
                            <input type="number" id="defenseDuration" name="defenseDuration" value="60" min="30" max="180">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="juryPresident">Président du jury *</label>
                        <select id="juryPresident" name="juryPresident" required>
                            <option value="">Sélectionner le président</option>
                            <!-- Options chargées dynamiquement -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="juryMembers">Membres du jury</label>
                        <select id="juryMembers" name="juryMembers[]" multiple>
                            <!-- Options chargées dynamiquement -->
                        </select>
                        <small style="color: var(--gray-500);">Maintenez Ctrl pour sélectionner plusieurs membres</small>
                    </div>
                    <div class="form-group">
                        <label for="defenseNotes">Notes</label>
                        <textarea id="defenseNotes" name="defenseNotes" placeholder="Notes ou instructions particulières"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('scheduleDefenseModal')">Annuler</button>
                <button class="btn btn-primary" onclick="submitScheduleDefense()">Planifier la soutenance</button>
            </div>
        </div>
    </div>

    <!-- Report Details Modal -->
    <div id="reportDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Détails du rapport</h3>
                <button class="modal-close" onclick="closeModal('reportDetailsModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="reportDetailsContent">
                    <!-- Le contenu sera chargé dynamiquement -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('reportDetailsModal')">Fermer</button>
                <button class="btn btn-primary" onclick="printReportDetails()">
                    <i class="fas fa-print"></i>
                    Imprimer
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let reports = [
            {
                id: 1,
                studentId: 1,
                studentName: 'KOUAME Jean-Baptiste',
                studentNumber: 'ETU2024001',
                title: 'Développement d\'une application mobile de gestion des transports urbains',
                type: 'memoire',
                level: 'master2',
                submissionDate: '2024-01-15',
                status: 'validated',
                supervisorId: 1,
                supervisorName: 'Dr. YAPI Marie',
                priority: 'high',
                description: 'Application mobile permettant la gestion et l\'optimisation des transports en commun dans la ville d\'Abidjan.',
                fileUrl: '/uploads/rapports/rapport_kouame_2024.pdf'
            },
            {
                id: 2,
                studentId: 2,
                studentName: 'TRAORE Marie-Claire',
                studentNumber: 'ETU2024002',
                title: 'Analyse statistique des performances académiques',
                type: 'stage',
                level: 'master1',
                submissionDate: '2024-01-20',
                status: 'in-review',
                supervisorId: null,
                supervisorName: null,
                priority: 'medium',
                description: 'Étude statistique approfondie des facteurs influençant les performances des étudiants.',
                fileUrl: '/uploads/rapports/rapport_traore_2024.pdf'
            },
            {
                id: 3,
                studentId: 3,
                studentName: 'DIALLO Amadou',
                studentNumber: 'ETU2024003',
                title: 'Système de détection d\'intrusion basé sur l\'IA',
                type: 'projet',
                level: 'licence3',
                submissionDate: '2024-01-25',
                status: 'deposited',
                supervisorId: null,
                supervisorName: null,
                priority: 'low',
                description: 'Développement d\'un système intelligent de détection d\'intrusion utilisant des algorithmes d\'apprentissage automatique.',
                fileUrl: '/uploads/rapports/rapport_diallo_2024.pdf'
            }
        ];

        let defenses = [
            {
                id: 1,
                reportId: 1,
                studentName: 'KOUAME Jean-Baptiste',
                title: 'Développement d\'une application mobile de gestion des transports urbains',
                date: '2024-02-15',
                time: '09:00',
                room: 'amphi-a',
                juryPresident: 'Prof. KONE Abdoulaye',
                supervisor: 'Dr. YAPI Marie',
                status: 'scheduled',
                duration: 60
            },
            {
                id: 2,
                reportId: 2,
                studentName: 'TRAORE Marie-Claire',
                title: 'Analyse statistique des performances académiques',
                date: '2024-02-20',
                time: '14:00',
                room: 'salle-101',
                juryPresident: 'Dr. BAMBA Fatou',
                supervisor: 'Prof. OUATTARA Ibrahim',
                status: 'confirmed',
                duration: 45
            }
        ];

        let supervisors = [
            {
                id: 1,
                name: 'Dr. YAPI Marie',
                grade: 'Maître de Conférences',
                department: 'informatique',
                specialty: 'Génie Logiciel',
                reportsCount: 5,
                availability: 'available'
            },
            {
                id: 2,
                name: 'Prof. KONE Abdoulaye',
                grade: 'Professeur Titulaire',
                department: 'informatique',
                specialty: 'Intelligence Artificielle',
                reportsCount: 8,
                availability: 'busy'
            },
            {
                id: 3,
                name: 'Dr. BAMBA Fatou',
                grade: 'Maître Assistant',
                department: 'mathematiques',
                specialty: 'Statistiques',
                reportsCount: 3,
                availability: 'available'
            }
        ];

        let students = [
            { id: 1, name: 'KOUAME Jean-Baptiste', number: 'ETU2024001', level: 'master2' },
            { id: 2, name: 'TRAORE Marie-Claire', number: 'ETU2024002', level: 'master1' },
            { id: 3, name: 'DIALLO Amadou', number: 'ETU2024003', level: 'licence3' }
        ];

        let filteredReports = [...reports];
        let filteredDefenses = [...defenses];
        let filteredSupervisors = [...supervisors];
        let currentTab = 'reports';

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoToggle = document.getElementById('logoToggle');

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            loadReportsData();
            loadDefensesData();
            loadSupervisorsData();
            updateStatistics();
            populateSelects();
        });

        // Event Listeners
        function initializeEventListeners() {
            sidebarToggle.addEventListener('click', toggleSidebar);
            
            logoToggle.addEventListener('click', function() {
                if (sidebar.classList.contains('collapsed')) {
                    toggleSidebar();
                }
            });

            // Search and filter listeners for reports
            document.getElementById('searchReports')?.addEventListener('input', debounce(filterReports, 300));
            document.getElementById('filterStatus')?.addEventListener('change', filterReports);
            document.getElementById('filterType')?.addEventListener('change', filterReports);
            document.getElementById('filterLevel')?.addEventListener('change', filterReports);

            // Search and filter listeners for defenses
            document.getElementById('searchDefenses')?.addEventListener('input', debounce(filterDefenses, 300));
            document.getElementById('filterDefenseStatus')?.addEventListener('change', filterDefenses);
            document.getElementById('filterDate')?.addEventListener('change', filterDefenses);

            // Search and filter listeners for supervisors
            document.getElementById('searchSupervisors')?.addEventListener('input', debounce(filterSupervisors, 300));
            document.getElementById('filterDepartment')?.addEventListener('change', filterSupervisors);
            document.getElementById('filterAvailability')?.addEventListener('change', filterSupervisors);

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

        // Tab functions
        function switchTab(tabName) {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            // Add active class to selected tab and content
            event.target.classList.add('active');
            document.getElementById(tabName + 'Tab').classList.add('active');

            currentTab = tabName;
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
                
                // Reset form if it exists
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                }
            }
        }

        // Reports management functions
        function loadReportsData() {
            const tbody = document.getElementById('reportsTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            filteredReports.forEach(report => {
                const row = createReportRow(report);
                tbody.appendChild(row);
            });
        }

        function createReportRow(report) {
            const row = document.createElement('tr');
            
            const typeLabels = {
                'memoire': 'Mémoire',
                'stage': 'Rapport de stage',
                'projet': 'Projet de fin d\'études'
            };

            const levelLabels = {
                'licence3': 'Licence 3',
                'master1': 'Master 1',
                'master2': 'Master 2'
            };

            const statusLabels = {
                'deposited': 'Déposé',
                'in-review': 'En examen',
                'validated': 'Validé',
                'rejected': 'Rejeté',
                'assigned': 'Encadrant assigné'
            };

            row.innerHTML = `
                <td>
                    <strong>${report.studentName}</strong><br>
                    <small>${report.studentNumber}</small>
                </td>
                <td>
                    <strong>${report.title}</strong><br>
                    <small style="color: var(--gray-500);">${report.description ? report.description.substring(0, 50) + '...' : 'Aucune description'}</small>
                </td>
                <td>${typeLabels[report.type]}</td>
                <td>${levelLabels[report.level]}</td>
                <td>${new Date(report.submissionDate).toLocaleDateString('fr-FR')}</td>
                <td><span class="badge status-${report.status}">${statusLabels[report.status]}</span></td>
                <td>${report.supervisorName || '<em>Non assigné</em>'}</td>
                <td><span class="badge priority-${report.priority}">${report.priority === 'high' ? 'Haute' : report.priority === 'medium' ? 'Moyenne' : 'Basse'}</span></td>
                <td>
                    <button class="btn-icon" onclick="viewReportDetails(${report.id})" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${!report.supervisorId ? `<button class="btn-icon btn-success" onclick="assignSupervisor(${report.id})" title="Assigner encadrant">
                        <i class="fas fa-user-plus"></i>
                    </button>` : ''}
                    ${report.status === 'validated' ? `<button class="btn-icon btn-info" onclick="scheduleDefense(${report.id})" title="Planifier soutenance">
                        <i class="fas fa-calendar-plus"></i>
                    </button>` : ''}
                    <button class="btn-icon" onclick="downloadReport(${report.id})" title="Télécharger">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn-icon" onclick="editReportStatus(${report.id})" title="Modifier statut">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            `;
            
            return row;
        }

        function filterReports() {
            const searchTerm = document.getElementById('searchReports')?.value.toLowerCase() || '';
            const statusFilter = document.getElementById('filterStatus')?.value || '';
            const typeFilter = document.getElementById('filterType')?.value || '';
            const levelFilter = document.getElementById('filterLevel')?.value || '';

            filteredReports = reports.filter(report => {
                const matchesSearch = !searchTerm || 
                    report.studentName.toLowerCase().includes(searchTerm) ||
                    report.title.toLowerCase().includes(searchTerm) ||
                    (report.supervisorName && report.supervisorName.toLowerCase().includes(searchTerm));

                const matchesStatus = !statusFilter || report.status === statusFilter;
                const matchesType = !typeFilter || report.type === typeFilter;
                const matchesLevel = !levelFilter || report.level === levelFilter;

                return matchesSearch && matchesStatus && matchesType && matchesLevel;
            });

            loadReportsData();
        }

        function filterByStatus(status) {
            document.getElementById('filterStatus').value = status;
            filterReports();
            switchTab('reports');
        }

        // Defenses management functions
        function loadDefensesData() {
            const tbody = document.getElementById('defensesTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            filteredDefenses.forEach(defense => {
                const row = createDefenseRow(defense);
                tbody.appendChild(row);
            });
        }

        function createDefenseRow(defense) {
            const row = document.createElement('tr');
            
            const roomLabels = {
                'amphi-a': 'Amphithéâtre A',
                'amphi-b': 'Amphithéâtre B',
                'salle-101': 'Salle 101',
                'salle-102': 'Salle 102',
                'salle-201': 'Salle 201'
            };

            const statusLabels = {
                'scheduled': 'Planifiée',
                'confirmed': 'Confirmée',
                'completed': 'Terminée',
                'cancelled': 'Annulée'
            };

            row.innerHTML = `
                <td>
                    <strong>${defense.studentName}</strong>
                </td>
                <td>
                    <strong>${defense.title}</strong>
                </td>
                <td>
                    ${new Date(defense.date).toLocaleDateString('fr-FR')}<br>
                    <small>${defense.time}</small>
                </td>
                <td>${roomLabels[defense.room]}</td>
                <td>${defense.juryPresident}</td>
                <td>${defense.supervisor}</td>
                <td><span class="badge status-${defense.status}">${statusLabels[defense.status]}</span></td>
                <td>
                    <button class="btn-icon" onclick="viewDefenseDetails(${defense.id})" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" onclick="editDefense(${defense.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon btn-warning" onclick="rescheduleDefense(${defense.id})" title="Reprogrammer">
                        <i class="fas fa-calendar-alt"></i>
                    </button>
                    <button class="btn-icon btn-danger" onclick="cancelDefense(${defense.id})" title="Annuler">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            
            return row;
        }

        function filterDefenses() {
            const searchTerm = document.getElementById('searchDefenses')?.value.toLowerCase() || '';
            const statusFilter = document.getElementById('filterDefenseStatus')?.value || '';
            const dateFilter = document.getElementById('filterDate')?.value || '';

            filteredDefenses = defenses.filter(defense => {
                const matchesSearch = !searchTerm || 
                    defense.studentName.toLowerCase().includes(searchTerm) ||
                    defense.title.toLowerCase().includes(searchTerm) ||
                    roomLabels[defense.room].toLowerCase().includes(searchTerm);

                const matchesStatus = !statusFilter || defense.status === statusFilter;
                const matchesDate = !dateFilter || defense.date === dateFilter;

                return matchesSearch && matchesStatus && matchesDate;
            });

            loadDefensesData();
        }

        // Supervisors management functions
        function loadSupervisorsData() {
            const tbody = document.getElementById('supervisorsTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            filteredSupervisors.forEach(supervisor => {
                const row = createSupervisorRow(supervisor);
                tbody.appendChild(row);
            });
        }

        function createSupervisorRow(supervisor) {
            const row = document.createElement('tr');
            
            const departmentLabels = {
                'informatique': 'Informatique',
                'mathematiques': 'Mathématiques',
                'physique': 'Physique',
                'chimie': 'Chimie'
            };

            const availabilityLabels = {
                'available': 'Disponible',
                'busy': 'Occupé'
            };

            row.innerHTML = `
                <td><strong>${supervisor.name}</strong></td>
                <td>${supervisor.grade}</td>
                <td>${departmentLabels[supervisor.department]}</td>
                <td>${supervisor.specialty}</td>
                <td><strong>${supervisor.reportsCount}</strong></td>
                <td><span class="badge ${supervisor.availability === 'available' ? 'status-eligible' : 'status-not-eligible'}">${availabilityLabels[supervisor.availability]}</span></td>
                <td>
                    <button class="btn-icon" onclick="viewSupervisorDetails(${supervisor.id})" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" onclick="viewSupervisorReports(${supervisor.id})" title="Voir rapports">
                        <i class="fas fa-file-alt"></i>
                    </button>
                    <button class="btn-icon" onclick="contactSupervisor(${supervisor.id})" title="Contacter">
                        <i class="fas fa-envelope"></i>
                    </button>
                </td>
            `;
            
            return row;
        }

        function filterSupervisors() {
            const searchTerm = document.getElementById('searchSupervisors')?.value.toLowerCase() || '';
            const departmentFilter = document.getElementById('filterDepartment')?.value || '';
            const availabilityFilter = document.getElementById('filterAvailability')?.value || '';

            filteredSupervisors = supervisors.filter(supervisor => {
                const matchesSearch = !searchTerm || 
                    supervisor.name.toLowerCase().includes(searchTerm) ||
                    supervisor.specialty.toLowerCase().includes(searchTerm);

                const matchesDepartment = !departmentFilter || supervisor.department === departmentFilter;
                const matchesAvailability = !availabilityFilter || supervisor.availability === availabilityFilter;

                return matchesSearch && matchesDepartment && matchesAvailability;
            });

            loadSupervisorsData();
        }

        // Form submission functions
        function submitAddReport() {
            const form = document.getElementById('addReportForm');
            const formData = new FormData(form);
            
            const newReport = {
                id: reports.length + 1,
                studentId: parseInt(formData.get('studentId')),
                studentName: students.find(s => s.id === parseInt(formData.get('studentId')))?.name || '',
                studentNumber: students.find(s => s.id === parseInt(formData.get('studentId')))?.number || '',
                title: formData.get('reportTitle'),
                type: formData.get('reportType'),
                level: students.find(s => s.id === parseInt(formData.get('studentId')))?.level || '',
                submissionDate: formData.get('submissionDate'),
                status: 'deposited',
                supervisorId: null,
                supervisorName: null,
                priority: formData.get('reportPriority'),
                description: formData.get('reportDescription'),
                fileUrl: null
            };

            // Validate form data
            if (!newReport.studentId || !newReport.title || !newReport.type || !newReport.submissionDate) {
                showNotification('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }

            // Add report to the list
            reports.push(newReport);
            filteredReports = [...reports];
            
            // Refresh the table and statistics
            loadReportsData();
            updateStatistics();
            
            // Close modal and show success message
            closeModal('addReportModal');
            showNotification('Rapport enregistré avec succès', 'success');
        }

        function assignSupervisor(reportId) {
            const report = reports.find(r => r.id === reportId);
            if (!report) return;

            // Populate report info
            document.getElementById('assignReportId').value = reportId;
            document.getElementById('selectedReportInfo').innerHTML = `
                <p><strong>Étudiant:</strong> ${report.studentName}</p>
                <p><strong>Titre:</strong> ${report.title}</p>
                <p><strong>Type:</strong> ${report.type}</p>
                <p><strong>Date de dépôt:</strong> ${new Date(report.submissionDate).toLocaleDateString('fr-FR')}</p>
            `;

            openModal('assignSupervisorModal');
        }

        function submitAssignSupervisor() {
            const form = document.getElementById('assignSupervisorForm');
            const formData = new FormData(form);
            const reportId = parseInt(formData.get('reportId'));
            const supervisorId = parseInt(formData.get('supervisorId'));
            
            const reportIndex = reports.findIndex(r => r.id === reportId);
            const supervisor = supervisors.find(s => s.id === supervisorId);
            
            if (reportIndex === -1 || !supervisor) return;

            // Update report
            reports[reportIndex].supervisorId = supervisorId;
            reports[reportIndex].supervisorName = supervisor.name;
            reports[reportIndex].status = 'assigned';
            
            // Update supervisor reports count
            supervisor.reportsCount++;
            
            // Refresh data
            filteredReports = [...reports];
            loadReportsData();
            loadSupervisorsData();
            
            // Close modal and show success message
            closeModal('assignSupervisorModal');
            showNotification('Encadrant assigné avec succès', 'success');
        }

        function scheduleDefense(reportId) {
            const report = reports.find(r => r.id === reportId);
            if (!report) return;

            // Pre-select the report
            document.getElementById('defenseReportSelect').value = reportId;
            
            openModal('scheduleDefenseModal');
        }

        function submitScheduleDefense() {
            const form = document.getElementById('scheduleDefenseForm');
            const formData = new FormData(form);
            
            const newDefense = {
                id: defenses.length + 1,
                reportId: parseInt(formData.get('reportId')),
                studentName: reports.find(r => r.id === parseInt(formData.get('reportId')))?.studentName || '',
                title: reports.find(r => r.id === parseInt(formData.get('reportId')))?.title || '',
                date: formData.get('defenseDate'),
                time: formData.get('defenseTime'),
                room: formData.get('defenseRoom'),
                juryPresident: formData.get('juryPresident'),
                supervisor: reports.find(r => r.id === parseInt(formData.get('reportId')))?.supervisorName || '',
                status: 'scheduled',
                duration: parseInt(formData.get('defenseDuration'))
            };

            // Validate form data
            if (!newDefense.reportId || !newDefense.date || !newDefense.time || !newDefense.room || !newDefense.juryPresident) {
                showNotification('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }

            // Add defense to the list
            defenses.push(newDefense);
            filteredDefenses = [...defenses];
            
            // Update report status
            const reportIndex = reports.findIndex(r => r.id === newDefense.reportId);
            if (reportIndex !== -1) {
                reports[reportIndex].status = 'scheduled';
            }
            
            // Refresh data
            loadDefensesData();
            loadReportsData();
            updateStatistics();
            
            // Close modal and show success message
            closeModal('scheduleDefenseModal');
            showNotification('Soutenance planifiée avec succès', 'success');
        }

        // View functions
        function viewReportDetails(reportId) {
            const report = reports.find(r => r.id === reportId);
            if (!report) return;

            const typeLabels = {
                'memoire': 'Mémoire',
                'stage': 'Rapport de stage',
                'projet': 'Projet de fin d\'études'
            };

            const levelLabels = {
                'licence3': 'Licence 3',
                'master1': 'Master 1',
                'master2': 'Master 2'
            };

            const statusLabels = {
                'deposited': 'Déposé',
                'in-review': 'En examen',
                'validated': 'Validé',
                'rejected': 'Rejeté',
                'assigned': 'Encadrant assigné',
                'scheduled': 'Soutenance planifiée'
            };

            const detailsContent = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Informations générales</h4>
                        <p><strong>Étudiant:</strong> ${report.studentName}</p>
                        <p><strong>Numéro:</strong> ${report.studentNumber}</p>
                        <p><strong>Niveau:</strong> ${levelLabels[report.level]}</p>
                        <p><strong>Type de rapport:</strong> ${typeLabels[report.type]}</p>
                        <p><strong>Date de dépôt:</strong> ${new Date(report.submissionDate).toLocaleDateString('fr-FR')}</p>
                        <p><strong>Statut:</strong> <span class="badge status-${report.status}">${statusLabels[report.status]}</span></p>
                        <p><strong>Priorité:</strong> <span class="badge priority-${report.priority}">${report.priority === 'high' ? 'Haute' : report.priority === 'medium' ? 'Moyenne' : 'Basse'}</span></p>
                    </div>
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Encadrement</h4>
                        <p><strong>Encadrant:</strong> ${report.supervisorName || 'Non assigné'}</p>
                        <p><strong>Date d'assignation:</strong> ${report.supervisorId ? new Date().toLocaleDateString('fr-FR') : 'N/A'}</p>
                        <br>
                        <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Fichier</h4>
                        <p><strong>Fichier:</strong> ${report.fileUrl ? '<a href="' + report.fileUrl + '" target="_blank">Télécharger le rapport</a>' : 'Aucun fichier'}</p>
                    </div>
                </div>
                <div style="margin-top: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Titre et description</h4>
                    <p><strong>Titre:</strong> ${report.title}</p>
                    <p><strong>Description:</strong></p>
                    <div style="background: var(--gray-50); padding: 1rem; border-radius: 8px; margin-top: 0.5rem;">
                        ${report.description || 'Aucune description disponible'}
                    </div>
                </div>
            `;

            document.getElementById('reportDetailsContent').innerHTML = detailsContent;
            openModal('reportDetailsModal');
        }

        function viewDefenseDetails(defenseId) {
            showNotification('Fonctionnalité de détails de soutenance en cours de développement', 'info');
        }

        function viewSupervisorDetails(supervisorId) {
            showNotification('Fonctionnalité de détails d\'encadrant en cours de développement', 'info');
        }

        function viewSupervisorReports(supervisorId) {
            showNotification('Fonctionnalité de consultation des rapports d\'un encadrant en cours de développement', 'info');
        }

        // Utility functions
        function populateSelects() {
            // Populate student select
            const studentSelect = document.getElementById('studentSelect');
            if (studentSelect) {
                studentSelect.innerHTML = '<option value="">Sélectionner un étudiant</option>';
                students.forEach(student => {
                    studentSelect.innerHTML += `<option value="${student.id}">${student.name} (${student.number})</option>`;
                });
            }

            // Populate supervisor select
            const supervisorSelect = document.getElementById('supervisorSelect');
            if (supervisorSelect) {
                supervisorSelect.innerHTML = '<option value="">Sélectionner un encadrant</option>';
                supervisors.filter(s => s.availability === 'available').forEach(supervisor => {
                    supervisorSelect.innerHTML += `<option value="${supervisor.id}">${supervisor.name} - ${supervisor.specialty}</option>`;
                });
            }

            // Populate defense report select
            const defenseReportSelect = document.getElementById('defenseReportSelect');
            if (defenseReportSelect) {
                defenseReportSelect.innerHTML = '<option value="">Sélectionner un rapport validé</option>';
                reports.filter(r => r.status === 'validated').forEach(report => {
                    defenseReportSelect.innerHTML += `<option value="${report.id}">${report.studentName} - ${report.title}</option>`;
                });
            }

            // Populate jury president select
            const juryPresident = document.getElementById('juryPresident');
            if (juryPresident) {
                juryPresident.innerHTML = '<option value="">Sélectionner le président</option>';
                supervisors.forEach(supervisor => {
                    juryPresident.innerHTML += `<option value="${supervisor.name}">${supervisor.name} - ${supervisor.grade}</option>`;
                });
            }

            // Populate jury members select
            const juryMembers = document.getElementById('juryMembers');
            if (juryMembers) {
                juryMembers.innerHTML = '';
                supervisors.forEach(supervisor => {
                    juryMembers.innerHTML += `<option value="${supervisor.name}">${supervisor.name} - ${supervisor.specialty}</option>`;
                });
            }
        }

        function updateStatistics() {
            document.getElementById('totalReports').textContent = reports.length;
            document.getElementById('pendingReports').textContent = reports.filter(r => r.status === 'in-review').length;
            document.getElementById('validatedReports').textContent = reports.filter(r => r.status === 'validated').length;
            document.getElementById('scheduledDefenses').textContent = defenses.filter(d => d.status === 'scheduled').length;
            document.getElementById('availableSupervisors').textContent = supervisors.filter(s => s.availability === 'available').length;
        }

        function downloadReport(reportId) {
            const report = reports.find(r => r.id === reportId);
            if (!report || !report.fileUrl) {
                showNotification('Aucun fichier disponible pour ce rapport', 'warning');
                return;
            }
            
            // Simulate download
            showNotification('Téléchargement du rapport en cours...', 'info');
        }

        function editReportStatus(reportId) {
            showNotification('Fonctionnalité de modification de statut en cours de développement', 'info');
        }

        function editDefense(defenseId) {
            showNotification('Fonctionnalité de modification de soutenance en cours de développement', 'info');
        }

        function rescheduleDefense(defenseId) {
            showNotification('Fonctionnalité de reprogrammation en cours de développement', 'info');
        }

        function cancelDefense(defenseId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette soutenance ?')) {
                const defenseIndex = defenses.findIndex(d => d.id === defenseId);
                if (defenseIndex !== -1) {
                    defenses[defenseIndex].status = 'cancelled';
                    filteredDefenses = [...defenses];
                    loadDefensesData();
                    showNotification('Soutenance annulée avec succès', 'success');
                }
            }
        }

        function contactSupervisor(supervisorId) {
            showNotification('Fonctionnalité de contact en cours de développement', 'info');
        }

        function exportReports() {
            // Simple CSV export
            const headers = ['Étudiant', 'Titre', 'Type', 'Niveau', 'Date de dépôt', 'Statut', 'Encadrant', 'Priorité'];
            const csvContent = [
                headers.join(','),
                ...filteredReports.map(report => [
                    report.studentName,
                    `"${report.title}"`,
                    report.type,
                    report.level,
                    report.submissionDate,
                    report.status,
                    report.supervisorName || '',
                    report.priority
                ].join(','))
            ].join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `rapports_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            
            showNotification('Export des rapports terminé', 'success');
        }

        function printReportDetails() {
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
