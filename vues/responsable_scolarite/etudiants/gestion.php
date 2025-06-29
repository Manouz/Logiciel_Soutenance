<?php
/*
session_start();
// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responsable_scolarite') {
    header('Location: ../../../login.php');
    exit;
}*/
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants - Responsable Scolarité</title>
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

        .badge-student {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        .badge-master {
            background: #ddd6fe;
            color: #7c3aed;
        }

        .badge-licence {
            background: #fed7d7;
            color: #c53030;
        }

        .status-active {
            color: var(--success-color);
            font-weight: 500;
        }

        .status-inactive {
            color: var(--error-color);
            font-weight: 500;
        }

        .status-eligible {
            background: #dcfce7;
            color: #166534;
        }

        .status-not-eligible {
            background: #fee2e2;
            color: #991b1b;
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
            max-width: 600px;
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
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: var(--gray-600);
            font-size: 0.9rem;
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
                    <li class="menu-item active">
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
                    <li class="menu-item">
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
                <h1>Gestion des Étudiants</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openModal('addStudentModal')">
                        <i class="fas fa-user-plus"></i>
                        Ajouter un étudiant
                    </button>
                </div>
            </header>

            <div class="content-body">
                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3 id="totalStudents">1,247</h3>
                        <p>Étudiants inscrits</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="activeStudents">1,198</h3>
                        <p>Étudiants actifs</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="eligibleStudents">1,024</h3>
                        <p>Étudiants éligibles</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="averageGrade">12.5</h3>
                        <p>Moyenne générale</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-bar">
                    <select class="filter-select" id="filterLevel">
                        <option value="">Tous les niveaux</option>
                        <option value="licence1">Licence 1</option>
                        <option value="licence2">Licence 2</option>
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
                    <select class="filter-select" id="filterStatus">
                        <option value="">Tous les statuts</option>
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                        <option value="suspended">Suspendu</option>
                    </select>
                    <input type="text" class="search-input" id="searchInput" placeholder="Rechercher par nom, numéro ou email...">
                    <button class="btn btn-secondary" onclick="exportStudents()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                </div>

                <!-- Students Table -->
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Nom & Prénom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Niveau</th>
                                <th>Spécialité</th>
                                <th>Statut</th>
                                <th>Éligibilité</th>
                                <th>Moyenne</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            <!-- Les données seront chargées dynamiquement -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un nouvel étudiant</h3>
                <button class="modal-close" onclick="closeModal('addStudentModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="studentNumber">Numéro étudiant *</label>
                            <input type="text" id="studentNumber" name="studentNumber" required>
                        </div>
                        <div class="form-group">
                            <label for="firstName">Prénom *</label>
                            <input type="text" id="firstName" name="firstName" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="lastName">Nom *</label>
                            <input type="text" id="lastName" name="lastName" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="birthDate">Date de naissance</label>
                            <input type="date" id="birthDate" name="birthDate">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="level">Niveau *</label>
                            <select id="level" name="level" required>
                                <option value="">Sélectionner un niveau</option>
                                <option value="licence1">Licence 1</option>
                                <option value="licence2">Licence 2</option>
                                <option value="licence3">Licence 3</option>
                                <option value="master1">Master 1</option>
                                <option value="master2">Master 2</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="specialty">Spécialité *</label>
                            <select id="specialty" name="specialty" required>
                                <option value="">Sélectionner une spécialité</option>
                                <option value="informatique">Informatique</option>
                                <option value="mathematiques">Mathématiques</option>
                                <option value="physique">Physique</option>
                                <option value="chimie">Chimie</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Adresse</label>
                        <textarea id="address" name="address" placeholder="Adresse complète de l'étudiant"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('addStudentModal')">Annuler</button>
                <button class="btn btn-primary" onclick="submitAddStudent()">Ajouter l'étudiant</button>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier les informations de l'étudiant</h3>
                <button class="modal-close" onclick="closeModal('editStudentModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editStudentForm">
                    <input type="hidden" id="editStudentId" name="studentId">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editStudentNumber">Numéro étudiant *</label>
                            <input type="text" id="editStudentNumber" name="studentNumber" required>
                        </div>
                        <div class="form-group">
                            <label for="editFirstName">Prénom *</label>
                            <input type="text" id="editFirstName" name="firstName" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editLastName">Nom *</label>
                            <input type="text" id="editLastName" name="lastName" required>
                        </div>
                        <div class="form-group">
                            <label for="editEmail">Email *</label>
                            <input type="email" id="editEmail" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editPhone">Téléphone</label>
                            <input type="tel" id="editPhone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="editBirthDate">Date de naissance</label>
                            <input type="date" id="editBirthDate" name="birthDate">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editLevel">Niveau *</label>
                            <select id="editLevel" name="level" required>
                                <option value="">Sélectionner un niveau</option>
                                <option value="licence1">Licence 1</option>
                                <option value="licence2">Licence 2</option>
                                <option value="licence3">Licence 3</option>
                                <option value="master1">Master 1</option>
                                <option value="master2">Master 2</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editSpecialty">Spécialité *</label>
                            <select id="editSpecialty" name="specialty" required>
                                <option value="">Sélectionner une spécialité</option>
                                <option value="informatique">Informatique</option>
                                <option value="mathematiques">Mathématiques</option>
                                <option value="physique">Physique</option>
                                <option value="chimie">Chimie</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editAddress">Adresse</label>
                        <textarea id="editAddress" name="address" placeholder="Adresse complète de l'étudiant"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('editStudentModal')">Annuler</button>
                <button class="btn btn-primary" onclick="submitEditStudent()">Sauvegarder les modifications</button>
            </div>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div id="studentDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Détails de l'étudiant</h3>
                <button class="modal-close" onclick="closeModal('studentDetailsModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="studentDetailsContent">
                    <!-- Le contenu sera chargé dynamiquement -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('studentDetailsModal')">Fermer</button>
                <button class="btn btn-primary" onclick="printStudentDetails()">
                    <i class="fas fa-print"></i>
                    Imprimer
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
                email: 'jean.kouame@etudiant.univ.ci',
                phone: '+225 07 12 34 56 78',
                birthDate: '2001-03-15',
                level: 'master1',
                specialty: 'informatique',
                status: 'active',
                eligible: true,
                average: 14.5,
                address: 'Cocody Angré, Abidjan'
            },
            {
                id: 2,
                studentNumber: 'ETU2024002',
                firstName: 'Marie-Claire',
                lastName: 'TRAORE',
                email: 'marie.traore@etudiant.univ.ci',
                phone: '+225 05 87 65 43 21',
                birthDate: '2000-07-22',
                level: 'master2',
                specialty: 'mathematiques',
                status: 'active',
                eligible: true,
                average: 16.2,
                address: 'Plateau, Abidjan'
            },
            {
                id: 3,
                studentNumber: 'ETU2024003',
                firstName: 'Amadou',
                lastName: 'DIALLO',
                email: 'amadou.diallo@etudiant.univ.ci',
                phone: '+225 01 23 45 67 89',
                birthDate: '2002-11-08',
                level: 'licence3',
                specialty: 'physique',
                status: 'active',
                eligible: false,
                average: 8.7,
                address: 'Yopougon, Abidjan'
            }
        ];

        let filteredStudents = [...students];

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoToggle = document.getElementById('logoToggle');
        const searchInput = document.getElementById('searchInput');
        const filterLevel = document.getElementById('filterLevel');
        const filterSpecialty = document.getElementById('filterSpecialty');
        const filterStatus = document.getElementById('filterStatus');

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            loadStudentsData();
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
            filterLevel.addEventListener('change', filterStudents);
            filterSpecialty.addEventListener('change', filterStudents);
            filterStatus.addEventListener('change', filterStudents);

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
                
                // Reset form if it exists
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                }
            }
        }

        // Student management functions
        function loadStudentsData() {
            const tbody = document.getElementById('studentsTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            filteredStudents.forEach(student => {
                const row = createStudentRow(student);
                tbody.appendChild(row);
            });
        }

        function createStudentRow(student) {
            const row = document.createElement('tr');
            
            const levelLabels = {
                'licence1': 'Licence 1',
                'licence2': 'Licence 2',
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
                'active': 'Actif',
                'inactive': 'Inactif',
                'suspended': 'Suspendu'
            };

            row.innerHTML = `
                <td>${student.studentNumber}</td>
                <td><strong>${student.firstName} ${student.lastName}</strong></td>
                <td>${student.email}</td>
                <td>${student.phone || '-'}</td>
                <td><span class="badge badge-${student.level.includes('licence') ? 'licence' : 'master'}">${levelLabels[student.level]}</span></td>
                <td>${specialtyLabels[student.specialty]}</td>
                <td><span class="status-${student.status}">${statusLabels[student.status]}</span></td>
                <td><span class="badge ${student.eligible ? 'status-eligible' : 'status-not-eligible'}">${student.eligible ? 'Éligible' : 'Non éligible'}</span></td>
                <td><strong>${student.average}/20</strong></td>
                <td>
                    <button class="btn-icon" onclick="viewStudentDetails(${student.id})" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" onclick="editStudent(${student.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="viewStudentHistory(${student.id})" title="Historique">
                        <i class="fas fa-history"></i>
                    </button>
                    <button class="btn-icon btn-danger" onclick="deleteStudent(${student.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            return row;
        }

        function filterStudents() {
            const searchTerm = searchInput.value.toLowerCase();
            const levelFilter = filterLevel.value;
            const specialtyFilter = filterSpecialty.value;
            const statusFilter = filterStatus.value;

            filteredStudents = students.filter(student => {
                const matchesSearch = !searchTerm || 
                    student.firstName.toLowerCase().includes(searchTerm) ||
                    student.lastName.toLowerCase().includes(searchTerm) ||
                    student.email.toLowerCase().includes(searchTerm) ||
                    student.studentNumber.toLowerCase().includes(searchTerm);

                const matchesLevel = !levelFilter || student.level === levelFilter;
                const matchesSpecialty = !specialtyFilter || student.specialty === specialtyFilter;
                const matchesStatus = !statusFilter || student.status === statusFilter;

                return matchesSearch && matchesLevel && matchesSpecialty && matchesStatus;
            });

            loadStudentsData();
            updateStatistics();
        }

        function updateStatistics() {
            document.getElementById('totalStudents').textContent = students.length.toLocaleString();
            document.getElementById('activeStudents').textContent = students.filter(s => s.status === 'active').length.toLocaleString();
            document.getElementById('eligibleStudents').textContent = students.filter(s => s.eligible).length.toLocaleString();
            
            const totalAverage = students.reduce((sum, student) => sum + student.average, 0) / students.length;
            document.getElementById('averageGrade').textContent = totalAverage.toFixed(1);
        }

        function submitAddStudent() {
            const form = document.getElementById('addStudentForm');
            const formData = new FormData(form);
            
            const newStudent = {
                id: students.length + 1,
                studentNumber: formData.get('studentNumber'),
                firstName: formData.get('firstName'),
                lastName: formData.get('lastName'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                birthDate: formData.get('birthDate'),
                level: formData.get('level'),
                specialty: formData.get('specialty'),
                address: formData.get('address'),
                status: 'active',
                eligible: false,
                average: 0
            };

            // Validate form data
            if (!newStudent.studentNumber || !newStudent.firstName || !newStudent.lastName || !newStudent.email || !newStudent.level || !newStudent.specialty) {
                showNotification('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }

            // Check if student number already exists
            if (students.some(student => student.studentNumber === newStudent.studentNumber)) {
                showNotification('Ce numéro étudiant existe déjà', 'error');
                return;
            }

            // Check if email already exists
            if (students.some(student => student.email === newStudent.email)) {
                showNotification('Cette adresse email est déjà utilisée', 'error');
                return;
            }

            // Add student to the list
            students.push(newStudent);
            filteredStudents = [...students];
            
            // Refresh the table and statistics
            loadStudentsData();
            updateStatistics();
            
            // Close modal and show success message
            closeModal('addStudentModal');
            showNotification('Étudiant ajouté avec succès', 'success');
        }

        function editStudent(studentId) {
            const student = students.find(s => s.id === studentId);
            if (!student) return;

            // Populate edit form
            document.getElementById('editStudentId').value = student.id;
            document.getElementById('editStudentNumber').value = student.studentNumber;
            document.getElementById('editFirstName').value = student.firstName;
            document.getElementById('editLastName').value = student.lastName;
            document.getElementById('editEmail').value = student.email;
            document.getElementById('editPhone').value = student.phone || '';
            document.getElementById('editBirthDate').value = student.birthDate || '';
            document.getElementById('editLevel').value = student.level;
            document.getElementById('editSpecialty').value = student.specialty;
            document.getElementById('editAddress').value = student.address || '';

            openModal('editStudentModal');
        }

        function submitEditStudent() {
            const form = document.getElementById('editStudentForm');
            const formData = new FormData(form);
            const studentId = parseInt(formData.get('studentId'));
            
            const studentIndex = students.findIndex(s => s.id === studentId);
            if (studentIndex === -1) return;

            const updatedStudent = {
                ...students[studentIndex],
                studentNumber: formData.get('studentNumber'),
                firstName: formData.get('firstName'),
                lastName: formData.get('lastName'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                birthDate: formData.get('birthDate'),
                level: formData.get('level'),
                specialty: formData.get('specialty'),
                address: formData.get('address')
            };

            // Validate form data
            if (!updatedStudent.studentNumber || !updatedStudent.firstName || !updatedStudent.lastName || !updatedStudent.email || !updatedStudent.level || !updatedStudent.specialty) {
                showNotification('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }

            // Check if student number already exists (excluding current student)
            if (students.some(student => student.studentNumber === updatedStudent.studentNumber && student.id !== studentId)) {
                showNotification('Ce numéro étudiant existe déjà', 'error');
                return;
            }

            // Check if email already exists (excluding current student)
            if (students.some(student => student.email === updatedStudent.email && student.id !== studentId)) {
                showNotification('Cette adresse email est déjà utilisée', 'error');
                return;
            }

            // Update student
            students[studentIndex] = updatedStudent;
            filteredStudents = [...students];
            
            // Refresh the table
            loadStudentsData();
            
            // Close modal and show success message
            closeModal('editStudentModal');
            showNotification('Informations de l\'étudiant mises à jour avec succès', 'success');
        }

        function viewStudentDetails(studentId) {
            const student = students.find(s => s.id === studentId);
            if (!student) return;

            const levelLabels = {
                'licence1': 'Licence 1',
                'licence2': 'Licence 2',
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
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Informations personnelles</h4>
                        <p><strong>Numéro étudiant:</strong> ${student.studentNumber}</p>
                        <p><strong>Nom complet:</strong> ${student.firstName} ${student.lastName}</p>
                        <p><strong>Email:</strong> ${student.email}</p>
                        <p><strong>Téléphone:</strong> ${student.phone || 'Non renseigné'}</p>
                        <p><strong>Date de naissance:</strong> ${student.birthDate ? new Date(student.birthDate).toLocaleDateString('fr-FR') : 'Non renseignée'}</p>
                        <p><strong>Adresse:</strong> ${student.address || 'Non renseignée'}</p>
                    </div>
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Informations académiques</h4>
                        <p><strong>Niveau:</strong> ${levelLabels[student.level]}</p>
                        <p><strong>Spécialité:</strong> ${specialtyLabels[student.specialty]}</p>
                        <p><strong>Statut:</strong> <span class="status-${student.status}">${student.status === 'active' ? 'Actif' : 'Inactif'}</span></p>
                        <p><strong>Éligibilité:</strong> <span class="badge ${student.eligible ? 'status-eligible' : 'status-not-eligible'}">${student.eligible ? 'Éligible' : 'Non éligible'}</span></p>
                        <p><strong>Moyenne générale:</strong> <strong>${student.average}/20</strong></p>
                        <p><strong>Date d'inscription:</strong> ${new Date().toLocaleDateString('fr-FR')}</p>
                    </div>
                </div>
                <div style="margin-top: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Historique récent</h4>
                    <div style="background: var(--gray-50); padding: 1rem; border-radius: 8px;">
                        <p><i class="fas fa-info-circle"></i> Aucun historique académique disponible pour le moment.</p>
                    </div>
                </div>
            `;

            document.getElementById('studentDetailsContent').innerHTML = detailsContent;
            openModal('studentDetailsModal');
        }

        function viewStudentHistory(studentId) {
            showNotification('Fonctionnalité d\'historique en cours de développement', 'info');
        }

        function deleteStudent(studentId) {
            const student = students.find(s => s.id === studentId);
            if (!student) return;

            if (confirm(`Êtes-vous sûr de vouloir supprimer l'étudiant ${student.firstName} ${student.lastName} ?\n\nCette action est irréversible.`)) {
                students = students.filter(s => s.id !== studentId);
                filteredStudents = [...students];
                
                loadStudentsData();
                updateStatistics();
                showNotification('Étudiant supprimé avec succès', 'success');
            }
        }

        function exportStudents() {
            // Simple CSV export
            const headers = ['Numéro', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Niveau', 'Spécialité', 'Statut', 'Éligible', 'Moyenne'];
            const csvContent = [
                headers.join(','),
                ...filteredStudents.map(student => [
                    student.studentNumber,
                    student.firstName,
                    student.lastName,
                    student.email,
                    student.phone || '',
                    student.level,
                    student.specialty,
                    student.status,
                    student.eligible ? 'Oui' : 'Non',
                    student.average
                ].join(','))
            ].join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `etudiants_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            
            showNotification('Export des étudiants terminé', 'success');
        }

        function printStudentDetails() {
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