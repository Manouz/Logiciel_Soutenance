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
    overflow: hidden; /* Empêche le scroll global */
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--gray-50);
    color: var(--gray-900);
    line-height: 1.6;
}

.admin-container {
    display: flex;
    height: 100vh; /* Hauteur fixe de la fenêtre */
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
    height: 100vh; /* Hauteur fixe */
    overflow: hidden; /* Empêche le scroll global de la sidebar */
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
    flex-shrink: 0; /* Empêche la compression */
}

/* Modification pour le logo cliquable quand collapsed */
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

/* Centrer le logo quand collapsed */
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

/* Cacher le bouton burger quand collapsed */
.sidebar.collapsed .sidebar-toggle {
    display: none;
}

.sidebar-menu {
    flex: 1;
    padding: 1rem 0;
    overflow-y: auto; /* Scroll uniquement dans le menu */
    overflow-x: hidden;
}

/* Personnalisation de la scrollbar pour sidebar-menu */
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

/* Centrer les icônes du menu quand collapsed */
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
    flex-shrink: 0; /* Empêche la compression */
}

/* Modifications pour le footer quand collapsed */
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

/* Cacher le profil utilisateur quand collapsed */
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

/* Agrandir le bouton logout quand collapsed */
.sidebar.collapsed .logout-btn {
    font-size: 1.3rem;
    padding: 0.75rem;
}

/* Main Content Styles */
.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100vh; /* Hauteur fixe */
    overflow: hidden;
}

.content-header {
    background: rgba(255, 255, 255, 0.3); /* Fond semi-transparent */
    padding: 1.5rem 2rem;
    border-bottom: 1px solid rgba(229, 231, 235, 0.3); /* Bordure plus subtile */
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); /* Ombre plus douce */
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
    overflow-y: auto; /* Scroll uniquement dans le contenu */
    overflow-x: hidden;
}

/* Personnalisation de la scrollbar pour content-body */
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
    grid-template-columns: 1fr;
    gap: 1.5rem;
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

.settings-card:nth-child(1) .settings-card-icon {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.settings-card:nth-child(2) .settings-card-icon {
    background: linear-gradient(135deg, var(--success-color), var(--accent-color));
}

.settings-card:nth-child(3) .settings-card-icon {
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
}

.settings-card:nth-child(4) .settings-card-icon {
    background: linear-gradient(135deg, #f97316, #ea580c);
}

.settings-card:nth-child(5) .settings-card-icon {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.settings-card:nth-child(6) .settings-card-icon {
    background: linear-gradient(135deg, #eab308, #ca8a04);
}

.settings-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}

.settings-card-description {
    color: var(--gray-600);
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

/* Configuration Pages Common Styles */
.config-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.back-btn {
    background: none;
    border: none;
    color: var(--gray-600);
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: var(--transition);
}

.back-btn:hover {
    background-color: var(--gray-100);
    color: var(--primary-color);
}

.config-title {
    flex: 1;
}

.config-title h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.config-title p {
    color: var(--gray-600);
    font-size: 0.9rem;
}

.config-table {
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-top: 2rem;
}

.config-table h3 {
    padding: 1.5rem;
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.config-table table {
    width: 100%;
    border-collapse: collapse;
}

.config-table th,
.config-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
}

.config-table th {
    background: var(--gray-50);
    font-weight: 600;
    color: var(--gray-700);
    font-size: 0.875rem;
}

.config-table tr:hover {
    background: var(--gray-50);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-closed {
    background: #fee2e2;
    color: #991b1b;
}

.status-enabled {
    background: #dcfce7;
    color: #166534;
}

.status-disabled {
    background: #fee2e2;
    color: #991b1b;
}

.action-btn {
    background: none;
    border: none;
    color: var(--error-color);
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.action-btn:hover {
    background: var(--error-color);
    color: var(--white);
}

.action-btn.btn-edit {
    color: var(--primary-color);
}

.action-btn.btn-edit:hover {
    background: var(--primary-color);
    color: var(--white);
}

/* Configuration Cards */
.config-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.config-card {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
}

.config-card h4 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}

.config-card p {
    color: var(--gray-600);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.config-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.config-option:last-child {
    border-bottom: none;
}

.config-option label {
    font-weight: 500;
    color: var(--gray-700);
}

.toggle-switch {
    position: relative;
    width: 50px;
    height: 24px;
    background: var(--gray-300);
    border-radius: 12px;
    cursor: pointer;
    transition: var(--transition);
}

.toggle-switch.active {
    background: var(--success-color);
}

.toggle-switch::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: var(--white);
    border-radius: 50%;
    transition: var(--transition);
}

.toggle-switch.active::after {
    transform: translateX(26px);
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
    transform: translateY(-1px);
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

/* Filters Bar */
.filters-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: center;
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

.status-active {
    color: var(--success-color);
    font-weight: 500;
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
    max-width: 500px;
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
.form-group textarea:focus{
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-light);
}
.form-group textarea {
    resize: vertical;
    min-height: 100px;
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

/* Additional responsive styles */
@media (max-width: 480px) {
    .notification {
        left: 10px;
        right: 10px;
        min-width: auto;
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
                    <span class="logo-text">SoutenanceAdmin</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="sidebar-menu">
                <ul class="menu-list">
                    <li class="menu-item active" data-section="dashboard">
                        <a href="#dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="users">
                        <a href="#users">
                            <i class="fas fa-users"></i>
                            <span>Gestion des utilisateurs</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="enrollments">
                        <a href="#enrollments">
                            <i class="fas fa-user-graduate"></i>
                            <span>Gestion des inscriptions</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="modules">
                        <a href="#modules">
                            <i class="fas fa-book"></i>
                            <span>Modules & Référentiels</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="commissions">
                        <a href="#commissions">
                            <i class="fas fa-gavel"></i>
                            <span>Commissions de validation</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="reports">
                        <a href="#reports">
                            <i class="fas fa-file-alt"></i>
                            <span>Gestion des rapports</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="communication">
                        <a href="#communication">
                            <i class="fas fa-bell"></i>
                            <span>Communication</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="statistics">
                        <a href="#statistics">
                            <i class="fas fa-chart-bar"></i>
                            <span>Statistiques</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="settings">
                        <a href="#settings">
                            <i class="fas fa-cog"></i>
                            <span>Paramétrage</span>
                        </a>
                    </li>
                    <!-- Ajout d'éléments supplémentaires pour tester le scroll -->
                    <!--li class="menu-item" data-section="test1">
                        <a href="#test1">
                            <i class="fas fa-test-tube"></i>
                            <span>Test 1</span>
                        </a>
                    </!--li>
                    <li class="menu-item" data-section="test2">
                        <a href="#test2">
                            <i class="fas fa-flask"></i>
                            <span>Test 2</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="test3">
                        <a href="#test3">
                            <i class="fas fa-vial"></i>
                            <span>Test 3</span>
                        </a>
                    </li>
                    <li class="menu-item" data-section="test4">
                        <a href="#test4">
                            <i class="fas fa-microscope"></i>
                            <span>Test 4</span>
                        </a>
                    </li>
                    <li-- class="menu-item" data-section="test5">
                        <a href="#test5">
                            <i class="fas fa-atom"></i>
                            <span>Test 5</span>
                        </a>
                    </li-->
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
                <h1 id="pageTitle">Tableau de bord</h1>
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
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3>1,247</h3>
                                <p>Utilisateurs actifs</p>
                            </div>
                        </div>
                        <div class="stat-card">
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
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3>156</h3>
                                <p>Rapports validés</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stat-info">
                                <h3>12</h3>
                                <p>Commissions planifiées</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-widgets">
                        <div class="widget">
                            <h3>Activité récente</h3>
                            <div class="activity-list">
                                <div class="activity-item">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Nouvel utilisateur inscrit: Marie Dupont</span>
                                    <small>Il y a 2 heures</small>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-file-upload"></i>
                                    <span>Rapport déposé par Jean Martin</span>
                                    <small>Il y a 4 heures</small>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-check"></i>
                                    <span>Rapport validé par Commission A</span>
                                    <small>Il y a 6 heures</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu supplémentaire pour tester le scroll -->
                    <div class="widget">
                        <h3>Contenu supplémentaire pour tester le scroll</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                        <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
                        <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
                        <p>Totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt.</p>
                        <p>Explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.</p>
                        <p>Sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>
                        <p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.</p>
                        <p>Sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</p>
                    </div>
                </section>

                <!-- Users Management Section -->
                <section id="users" class="content-section">
                    <div class="section-header">
                        <h2>Gestion des utilisateurs</h2>
                        <button class="btn btn-primary" onclick="openModal('addUserModal')">
                            <i class="fas fa-plus"></i> Ajouter un utilisateur
                        </button>
                    </div>

                    <div class="filters-bar">
                        <select class="filter-select">
                            <option value="">Tous les rôles</option>
                            <option value="student">Étudiant</option>
                            <option value="teacher">Enseignant</option>
                            <option value="admin">Administrateur</option>
                        </select>
                        <input type="text" class="search-input" placeholder="Rechercher un utilisateur...">
                        <button class="btn btn-secondary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Dernière connexion</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <tr>
                                    <td>Jean Dupont</td>
                                    <td>jean.dupont@univ.fr</td>
                                    <td><span class="badge badge-student">Étudiant</span></td>
                                    <td><span class="status-active">Actif</span></td>
                                    <td>2024-01-15 14:30</td>
                                    <td>
                                        <button class="btn-icon" onclick="editUser(1)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon" onclick="resetPassword(1)">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="btn-icon btn-danger" onclick="deleteUser(1)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Other sections -->
                <section id="enrollments" class="content-section">
                    <h2>Gestion des inscriptions</h2>
                    <p>Section en cours de développement...</p>
                </section>

                <section id="modules" class="content-section">
                    <h2>Modules & Référentiels</h2>
                    <p>Section en cours de développement...</p>
                </section>

                <section id="commissions" class="content-section">
                    <h2>Commissions de validation</h2>
                    <p>Section en cours de développement...</p>
                </section>

                <section id="reports" class="content-section">
                    <h2>Gestion des rapports</h2>
                    <p>Section en cours de développement...</p>
                </section>

                <section id="communication" class="content-section">
                    <h2>Communication & Notifications</h2>
                    <p>Section en cours de développement...</p>
                </section>

                <section id="statistics" class="content-section">
                    <h2>Suivi et statistiques</h2>
                    <p>Section en cours de développement...</p>
                </section>

                <!-- Settings Section -->
                <section id="settings" class="content-section">
                    <!-- Main Settings View -->
                    <div id="settingsMain">
                        <div class="section-header">
                            <h2>Paramétrage</h2>
                        </div>
                        <p style="color: var(--gray-600); margin-bottom: 2rem;">Configurez les différents aspects du système</p>
                        
                        <div class="settings-grid">
                            <div class="settings-card" onclick="openConfigSection('academicYears')">
                                <div class="settings-card-header">
                                    <div class="settings-card-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <h3 class="settings-card-title">Années académiques</h3>
                                    </div>
                                </div>
                                <p class="settings-card-description">Créer, gérer et clôturer les années académiques</p>
                                <button class="btn btn-primary">
                                    Configurer
                                </button>
                            </div>

                            <div class="settings-card" onclick="openConfigSection('roles')">
                                <div class="settings-card-header">
                                    <div class="settings-card-icon">
                                        <i class="fas fa-users-cog"></i>
                                    </div>
                                    <div>
                                        <h3 class="settings-card-title">Rôles et permissions</h3>
                                    </div>
                                </div>
                                <p class="settings-card-description">Configurer les rôles utilisateurs et leurs permissions</p>
                                <button class="btn btn-primary">
                                    Configurer
                                </button>
                            </div>

                            <div class="settings-card" onclick="openConfigSection('system')">
                                <div class="settings-card-header">
                                    <div class="settings-card-icon">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <div>
                                        <h3 class="settings-card-title">Paramètres système</h3>
                                    </div>
                                </div>
                                <p class="settings-card-description">Configuration générale du système</p>
                                <button class="btn btn-primary">
                                    Configurer
                                </button>
                            </div>

                            <div class="settings-card" onclick="openConfigSection('database')">
                                <div class="settings-card-header">
                                    <div class="settings-card-icon">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div>
                                        <h3 class="settings-card-title">Base de données</h3>
                                    </div>
                                </div>
                                <p class="settings-card-description">Sauvegarde et maintenance de la base de données</p>
                                <button class="btn btn-primary">
                                    Configurer
                                </button>
                            </div>

                            <div class="settings-card" onclick="openConfigSection('notifications')">
                                <div class="settings-card-header">
                                    <div class="settings-card-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <h3 class="settings-card-title">Notifications</h3>
                                    </div>
                                </div>
                                <p class="settings-card-description">Configuration des notifications et emails</p>
                                <button class="btn btn-primary">
                                    Configurer
                                </button>
                            </div>

                            <div class="settings-card" onclick="openConfigSection('security')">
                                <div class="settings-card-header">
                                    <div class="settings-card-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <h3 class="settings-card-title">Sécurité</h3>
                                    </div>
                                </div>
                                <p class="settings-card-description">Paramètres de sécurité et authentification</p>
                                <button class="btn btn-primary">
                                    Configurer
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Years Configuration -->
                    <div id="academicYearsSection" class="config-section" style="display: none;">
                        <div class="config-header">
                            <button class="back-btn" onclick="backToSettings()">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div class="config-title">
                                <h2>Gestion des années académiques</h2>
                                <p>Créez, gérez et clôturez les années académiques</p>
                            </div>
                            <button class="btn btn-success" onclick="openModal('addAcademicYearModal')">
                                <i class="fas fa-plus"></i> Créer une année académique
                            </button>
                        </div>

                        <div class="config-table">
                            <h3>
                                <i class="fas fa-calendar-alt"></i>
                                Années académiques
                            </h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Année académique</th>
                                        <th>Période</th>
                                        <th>Statut</th>
                                        <th>Description</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="academicYearsTableBody">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Roles and Permissions Configuration -->
                    <div id="rolesSection" class="config-section" style="display: none;">
                        <div class="config-header">
                            <button class="back-btn" onclick="backToSettings()">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div class="config-title">
                                <h2>Rôles et permissions</h2>
                                <p>Configurez les rôles utilisateurs et leurs permissions</p>
                            </div>
                            <button class="btn btn-success" onclick="openModal('addRoleModal')">
                                <i class="fas fa-plus"></i> Créer un rôle
                            </button>
                        </div>

                        <div class="config-table">
                            <h3>
                                <i class="fas fa-users-cog"></i>
                                Rôles système
                            </h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom du rôle</th>
                                        <th>Description</th>
                                        <th>Permissions</th>
                                        <th>Utilisateurs</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="rolesTableBody">
                                    <tr>
                                        <td><strong>Administrateur</strong></td>
                                        <td>Accès complet au système</td>
                                        <td>Toutes les permissions</td>
                                        <td>3</td>
                                        <td><span class="status-badge status-active">Actif</span></td>
                                        <td>
                                            <button class="action-btn btn-edit">
                                                <i class="fas fa-edit"></i>
                                                Modifier
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Enseignant</strong></td>
                                        <td>Gestion des étudiants et rapports</td>
                                        <td>Lecture, Écriture rapports</td>
                                        <td>45</td>
                                        <td><span class="status-badge status-active">Actif</span></td>
                                        <td>
                                            <button class="action-btn btn-edit">
                                                <i class="fas fa-edit"></i>
                                                Modifier
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Étudiant</strong></td>
                                        <td>Consultation et soumission</td>
                                        <td>Lecture limitée, Soumission</td>
                                        <td>1247</td>
                                        <td><span class="status-badge status-active">Actif</span></td>
                                        <td>
                                            <button class="action-btn btn-edit">
                                                <i class="fas fa-edit"></i>
                                                Modifier
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- System Parameters Configuration -->
                    <div id="systemSection" class="config-section" style="display: none;">
                        <div class="config-header">
                            <button class="back-btn" onclick="backToSettings()">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div class="config-title">
                                <h2>Paramètres système</h2>
                                <p>Configuration générale du système</p>
                            </div>
                        </div>

                        <div class="config-cards">
                            <div class="config-card">
                                <h4>Paramètres généraux</h4>
                                <p>Configuration de base du système</p>
                                <div class="config-option">
                                    <label>Nom de l'application</label>
                                    <input type="text" value="SoutenanceAdmin" class="form-control">
                                </div>
                                <div class="config-option">
                                    <label>Langue par défaut</label>
                                    <select class="form-control">
                                        <option value="fr">Français</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>
                                <div class="config-option">
                                    <label>Fuseau horaire</label>
                                    <select class="form-control">
                                        <option value="Europe/Paris">Europe/Paris</option>
                                        <option value="UTC">UTC</option>
                                    </select>
                                </div>
                            </div>

                            <div class="config-card">
                                <h4>Limites système</h4>
                                <p>Configuration des limites et quotas</p>
                                <div class="config-option">
                                    <label>Taille max fichier (MB)</label>
                                    <input type="number" value="50" class="form-control">
                                </div>
                                <div class="config-option">
                                    <label>Nombre max utilisateurs</label>
                                    <input type="number" value="5000" class="form-control">
                                </div>
                                <div class="config-option">
                                    <label>Durée session (minutes)</label>
                                    <input type="number" value="120" class="form-control">
                                </div>
                            </div>

                            <div class="config-card">
                                <h4>Options d'affichage</h4>
                                <p>Personnalisation de l'interface</p>
                                <div class="config-option">
                                    <label>Mode sombre par défaut</label>
                                    <div class="toggle-switch" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Sidebar réduite par défaut</label>
                                    <div class="toggle-switch" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Notifications desktop</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: center; margin-top: 2rem;">
                            <button class="btn btn-primary" onclick="saveSystemSettings()">
                                <i class="fas fa-save"></i> Sauvegarder les paramètres
                            </button>
                        </div>
                    </div>

                    <!-- Database Configuration -->
                    <div id="databaseSection" class="config-section" style="display: none;">
                        <div class="config-header">
                            <button class="back-btn" onclick="backToSettings()">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div class="config-title">
                                <h2>Base de données</h2>
                                <p>Sauvegarde et maintenance de la base de données</p>
                            </div>
                        </div>

                        <div class="config-cards">
                            <div class="config-card">
                                <h4>Sauvegarde automatique</h4>
                                <p>Configuration des sauvegardes automatiques</p>
                                <div class="config-option">
                                    <label>Sauvegarde quotidienne</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Heure de sauvegarde</label>
                                    <input type="time" value="02:00" class="form-control">
                                </div>
                                <div class="config-option">
                                    <label>Rétention (jours)</label>
                                    <input type="number" value="30" class="form-control">
                                </div>
                                <button class="btn btn-primary" style="margin-top: 1rem;">
                                    <i class="fas fa-download"></i> Créer une sauvegarde maintenant
                                </button>
                            </div>

                            <div class="config-card">
                                <h4>Maintenance</h4>
                                <p>Outils de maintenance de la base de données</p>
                                <div class="config-option">
                                    <label>Dernière optimisation</label>
                                    <span>15/01/2024 02:30</span>
                                </div>
                                <div class="config-option">
                                    <label>Taille de la base</label>
                                    <span>2.4 GB</span>
                                </div>
                                <div class="config-option">
                                    <label>Nombre d'enregistrements</label>
                                    <span>125,847</span>
                                </div>
                                <button class="btn btn-secondary" style="margin-top: 1rem;">
                                    <i class="fas fa-tools"></i> Optimiser la base
                                </button>
                            </div>

                            <div class="config-card">
                                <h4>Historique des sauvegardes</h4>
                                <p>Liste des dernières sauvegardes</p>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <div class="config-option">
                                        <label>20/01/2024 02:00</label>
                                        <span>2.3 GB</span>
                                    </div>
                                    <div class="config-option">
                                        <label>19/01/2024 02:00</label>
                                        <span>2.3 GB</span>
                                    </div>
                                    <div class="config-option">
                                        <label>18/01/2024 02:00</label>
                                        <span>2.2 GB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Configuration -->
                    <div id="notificationsSection" class="config-section" style="display: none;">
                        <div class="config-header">
                            <button class="back-btn" onclick="backToSettings()">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div class="config-title">
                                <h2>Notifications</h2>
                                <p>Configuration des notifications et emails</p>
                            </div>
                        </div>

                        <div class="config-cards">
                            <div class="config-card">
                                <h4>Configuration Email</h4>
                                <p>Paramètres du serveur SMTP</p>
                                <div class="config-option">
                                    <label>Serveur SMTP</label>
                                    <input type="text" value="smtp.univ.fr" class="form-control">
                                </div>
                                <div class="config-option">
                                    <label>Port</label>
                                    <input type="number" value="587" class="form-control">
                                </div>
                                <div class="config-option">
                                    <label>Sécurité</label>
                                    <select class="form-control">
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                    </select>
                                </div>
                                <div class="config-option">
                                    <label>Email expéditeur</label>
                                    <input type="email" value="noreply@univ.fr" class="form-control">
                                </div>
                            </div>

                            <div class="config-card">
                                <h4>Types de notifications</h4>
                                <p>Activation des différents types de notifications</p>
                                <div class="config-option">
                                    <label>Nouveaux utilisateurs</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Rapports soumis</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Commissions planifiées</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Rappels échéances</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                            </div>

                            <div class="config-card">
                                <h4>Modèles d'emails</h4>
                                <p>Personnalisation des modèles d'emails</p>
                                <div class="config-option">
                                    <label>Email de bienvenue</label>
                                    <button class="btn btn-secondary btn-sm">Modifier</button>
                                </div>
                                <div class="config-option">
                                    <label>Notification rapport</label>
                                    <button class="btn btn-secondary btn-sm">Modifier</button>
                                </div>
                                <div class="config-option">
                                    <label>Rappel échéance</label>
                                    <button class="btn btn-secondary btn-sm">Modifier</button>
                                </div>
                                <div class="config-option">
                                    <label>Confirmation commission</label>
                                    <button class="btn btn-secondary btn-sm">Modifier</button>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: center; margin-top: 2rem;">
                            <button class="btn btn-primary" onclick="saveNotificationSettings()">
                                <i class="fas fa-save"></i> Sauvegarder la configuration
                            </button>
                            <button class="btn btn-secondary" onclick="testEmailSettings()">
                                <i class="fas fa-paper-plane"></i> Tester l'envoi d'email
                            </button>
                        </div>
                    </div>

                    <!-- Security Configuration -->
                    <div id="securitySection" class="config-section" style="display: none;">
                        <div class="config-header">
                            <button class="back-btn" onclick="backToSettings()">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div class="config-title">
                                <h2>Sécurité</h2>
                                <p>Paramètres de sécurité et authentification</p>
                            </div>
                        </div>

                        <div class="config-cards">
                            <div class="config-card">
                                <h4>Politique des mots de passe</h4>
                                <p>Règles de sécurité pour les mots de passe</p>
                                <div class="config-option">
                                    <label>Longueur minimale</label>
                                    <input type="number" value="8" class="form-control">
                                </div>
                                <div class="config-option">
                                    <label>Caractères spéciaux obligatoires</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Majuscules obligatoires</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Chiffres obligatoires</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Expiration (jours)</label>
                                    <input type="number" value="90" class="form-control">
                                </div>
                            </div>

                            <div class="config-card">
                                <h4>Authentification</h4>
                                <p>Options d'authentification et de sécurité</p>
                                <div class="config-option">
                                    <label>Authentification à deux facteurs</label>
                                    <div class="toggle-switch" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Verrouillage après échecs</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Nombre d'échecs max</label>
                                    <input type="number" value="5" class="form-control">
                                </div>
                                <div class="config-option">
                                    <label>Durée verrouillage (minutes)</label>
                                    <input type="number" value="30" class="form-control">
                                </div>
                            </div>

                            <div class="config-card">
                                <h4>Sessions et accès</h4>
                                <p>Gestion des sessions utilisateurs</p>
                                <div class="config-option">
                                    <label>Sessions multiples autorisées</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Déconnexion automatique</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Inactivité max (minutes)</label>
                                    <input type="number" value="60" class="form-control">
                                </div>
                                <div class="config-option">
                                    <label>Journalisation des connexions</label>
                                    <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                            </div>

                            <div class="config-card">
                                <h4>Restrictions d'accès</h4>
                                <p>Contrôle d'accès par IP et horaires</p>
                                <div class="config-option">
                                    <label>Restriction par IP</label>
                                    <div class="toggle-switch" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>IPs autorisées</label>
                                    <textarea class="form-control" placeholder="192.168.1.0/24&#10;10.0.0.0/8"></textarea>
                                </div>
                                <div class="config-option">
                                    <label>Restriction horaire</label>
                                    <div class="toggle-switch" onclick="toggleSwitch(this)">
                                    </div>
                                </div>
                                <div class="config-option">
                                    <label>Heures d'accès</label>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <input type="time" value="08:00" class="form-control">
                                        <span>à</span>
                                        <input type="time" value="18:00" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: center; margin-top: 2rem;">
                            <button class="btn btn-primary" onclick="saveSecuritySettings()">
                                <i class="fas fa-save"></i> Sauvegarder la configuration
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un nouvel utilisateur</h3>
                <button class="modal-close" onclick="closeModal('addUserModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="form-group">
                        <label for="firstName">Prénom</label>
                        <input type="text" id="firstName" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Nom</label>
                        <input type="text" id="lastName" name="lastName" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Rôle</label>
                        <select id="role" name="role" required>
                            <option value="">Sélectionner un rôle</option>
                            <option value="student">Étudiant</option>
                            <option value="teacher">Enseignant</option>
                            <option value="admin_n1">Personnel administratif N1</option>
                            <option value="admin_n2">Personnel administratif N2</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe temporaire</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('addUserModal')">Annuler</button>
                <button class="btn btn-primary" onclick="submitAddUser()">Ajouter</button>
            </div>
        </div>
    </div>
    <!-- Add Academic Year Modal -->
    <div id="addAcademicYearModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Créer une nouvelle année académique</h3>
                <button class="modal-close" onclick="closeModal('addAcademicYearModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addAcademicYearForm">
                    <div class="form-group">
                        <label for="academicYear">Année académique</label>
                        <input type="text" id="academicYear" name="academicYear" placeholder="ex: 2025-2026" required>
                    </div>
                    <div class="form-group">
                        <label for="startDate">Date de début</label>
                        <input type="date" id="startDate" name="startDate" required>
                    </div>
                    <div class="form-group">
                        <label for="endDate">Date de fin</label>
                        <input type="date" id="endDate" name="endDate" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" placeholder="Description de l'année académique" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('addAcademicYearModal')">Annuler</button>
                <button class="btn btn-success" onclick="submitAddAcademicYear()">Créer</button>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div id="addRoleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Créer un nouveau rôle</h3>
                <button class="modal-close" onclick="closeModal('addRoleModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addRoleForm">
                    <div class="form-group">
                        <label for="roleName">Nom du rôle</label>
                        <input type="text" id="roleName" name="roleName" required>
                    </div>
                    <div class="form-group">
                        <label for="roleDescription">Description</label>
                        <textarea id="roleDescription" name="roleDescription" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Permissions</label>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; margin-top: 0.5rem;">
                            <label><input type="checkbox" name="permissions" value="read"> Lecture</label>
                            <label><input type="checkbox" name="permissions" value="write"> Écriture</label>
                            <label><input type="checkbox" name="permissions" value="delete"> Suppression</label>
                            <label><input type="checkbox" name="permissions" value="admin"> Administration</label>
                            <label><input type="checkbox" name="permissions" value="reports"> Gestion rapports</label>
                            <label><input type="checkbox" name="permissions" value="users"> Gestion utilisateurs</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('addRoleModal')">Annuler</button>
                <button class="btn btn-success" onclick="submitAddRole()">Créer</button>
            </div>
        </div>
    <script>
// Global variables
let currentSection = 'dashboard';
let users = [
    {
        id: 1,
        firstName: 'Jean',
        lastName: 'Dupont',
        email: 'jean.dupont@univ.fr',
        role: 'student',
        status: 'active',
        lastLogin: '2024-01-15 14:30'
    }
];

// DOM Elements
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const logoToggle = document.getElementById('logoToggle');
const pageTitle = document.getElementById('pageTitle');
const menuItems = document.querySelectorAll('.menu-item');
const contentSections = document.querySelectorAll('.content-section');

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    loadSection('dashboard');
});

// Event Listeners
function initializeEventListeners() {
    // Sidebar toggle avec le bouton burger
    sidebarToggle.addEventListener('click', toggleSidebar);
    
    // Logo toggle quand la sidebar est collapsed
    logoToggle.addEventListener('click', function() {
        if (sidebar.classList.contains('collapsed')) {
            toggleSidebar();
        }
    });

    // Menu navigation
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;
            loadSection(section);
            setActiveMenuItem(this);
        });
    });

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });

    // Form submissions
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAddUser();
    });
    document.getElementById('addAcademicYearForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAddAcademicYear();
    });

    document.getElementById('addRoleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAddRole();
    });
}

// Sidebar functions
function toggleSidebar() {
    sidebar.classList.toggle('collapsed');
}

// Navigation functions
function loadSection(sectionName) {
    // Hide all sections
    contentSections.forEach(section => {
        section.classList.remove('active');
    });

    // Show selected section
    const targetSection = document.getElementById(sectionName);
    if (targetSection) {
        targetSection.classList.add('active');
        currentSection = sectionName;
        updatePageTitle(sectionName);
    }
 // Reset settings view when leaving settings
    if (sectionName !== 'settings') {
        backToSettings();
    }
    // Load section-specific data
    switch(sectionName) {
        case 'users':
            loadUsersData();
            break;
        case 'dashboard':
            loadDashboardData();
            break;
        // Add other cases as needed
    }
}

function setActiveMenuItem(activeItem) {
    menuItems.forEach(item => {
        item.classList.remove('active');
    });
    activeItem.classList.add('active');
}

function updatePageTitle(sectionName) {
    const titles = {
        'dashboard': 'Tableau de bord',
        'users': 'Gestion des utilisateurs',
        'enrollments': 'Gestion des inscriptions',
        'modules': 'Modules & Référentiels',
        'commissions': 'Commissions de validation',
        'reports': 'Gestion des rapports',
        'communication': 'Communication & Notifications',
        'statistics': 'Suivi et statistiques',
        'settings': 'Paramétrage',
        'test1': 'Test 1',
        'test2': 'Test 2',
        'test3': 'Test 3',
        'test4': 'Test 4',
        'test5': 'Test 5'
    };
    
    pageTitle.textContent = titles[sectionName] || 'Administration';
}
// Settings functions
function loadSettingsData() {
    // Reset to main settings view
    backToSettings();
}

function openConfigSection(sectionName) {
    // Hide main settings
    document.getElementById('settingsMain').style.display = 'none';
    
    // Hide all config sections
    const configSections = document.querySelectorAll('.config-section');
    configSections.forEach(section => {
        section.style.display = 'none';
    });
    
    // Show selected config section
    const targetSection = document.getElementById(sectionName + 'Section');
    if (targetSection) {
        targetSection.style.display = 'block';
        currentConfigSection = sectionName;
        
        // Update page title based on section
        const titles = {
            'academicYears': 'Gestion des années académiques',
            'roles': 'Rôles et permissions',
            'system': 'Paramètres système',
            'database': 'Base de données',
            'notifications': 'Notifications',
            'security': 'Sécurité'
        };
        
        pageTitle.textContent = titles[sectionName] || 'Configuration';
        
        // Load section-specific data
        switch(sectionName) {
            case 'academicYears':
                loadAcademicYearsData();
                break;
            case 'roles':
                loadRolesData();
                break;
        }
    }
}

function backToSettings() {
    // Show main settings
    document.getElementById('settingsMain').style.display = 'block';
    
    // Hide all config sections
    const configSections = document.querySelectorAll('.config-section');
    configSections.forEach(section => {
        section.style.display = 'none';
    });
    
    currentConfigSection = null;
    pageTitle.textContent = 'Paramétrage';
}

function loadAcademicYearsData() {
    const tbody = document.getElementById('academicYearsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    academicYears.forEach(year => {
        const row = createAcademicYearRow(year);
        tbody.appendChild(row);
    });
}

function createAcademicYearRow(year) {
    const row = document.createElement('tr');
    
    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    };

    const statusClass = year.status === 'active' ? 'status-active' : 'status-closed';
    const statusText = year.status === 'active' ? 'Active' : 'Clôturée';

    row.innerHTML = `
        <td><strong>${year.year}</strong></td>
        <td>${formatDate(year.startDate)} - ${formatDate(year.endDate)}</td>
        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
        <td>${year.description}</td>
        <td>${formatDate(year.createdDate)}</td>
        <td>
            <button class="action-btn" onclick="closeAcademicYear(${year.id})" ${year.status === 'closed' ? 'disabled' : ''}>
                <i class="fas fa-lock"></i>
                Clôturer
            </button>
        </td>
    `;
    
    return row;
}

function loadRolesData() {
    // This function would load roles data if needed
    console.log('Loading roles data...');
}

function submitAddAcademicYear() {
    const form = document.getElementById('addAcademicYearForm');
    const formData = new FormData(form);
    
    const newAcademicYear = {
        id: academicYears.length + 1,
        year: formData.get('academicYear'),
        startDate: formData.get('startDate'),
        endDate: formData.get('endDate'),
        status: 'active',
        description: formData.get('description'),
        createdDate: new Date().toISOString().split('T')[0]
    };

    // Validate form data
    if (!newAcademicYear.year || !newAcademicYear.startDate || !newAcademicYear.endDate || !newAcademicYear.description) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    // Check if academic year already exists
    if (academicYears.some(year => year.year === newAcademicYear.year)) {
        showNotification('Cette année académique existe déjà', 'error');
        return;
    }

    // Set previous years as closed
    academicYears.forEach(year => {
        if (year.status === 'active') {
            year.status = 'closed';
        }
    });

    // Add academic year to the list
    academicYears.push(newAcademicYear);
    
    // Refresh the academic years table
    loadAcademicYearsData();
    
    // Close modal and show success message
    closeModal('addAcademicYearModal');
    showNotification('Année académique créée avec succès', 'success');
}

function submitAddRole() {
    const form = document.getElementById('addRoleForm');
    const formData = new FormData(form);
    
    const roleName = formData.get('roleName');
    const roleDescription = formData.get('roleDescription');
    const permissions = formData.getAll('permissions');

    // Validate form data
    if (!roleName || !roleDescription) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    if (permissions.length === 0) {
        showNotification('Veuillez sélectionner au moins une permission', 'error');
        return;
    }

    // Close modal and show success message
    closeModal('addRoleModal');
    showNotification('Rôle créé avec succès', 'success');
}

function closeAcademicYear(yearId) {
    const year = academicYears.find(y => y.id === yearId);
    if (year) {
        if (confirm(`Êtes-vous sûr de vouloir clôturer l'année académique ${year.year} ?`)) {
            year.status = 'closed';
            loadAcademicYearsData();
            showNotification('Année académique clôturée avec succès', 'success');
        }
    }
}

// Toggle switch function
function toggleSwitch(element) {
    element.classList.toggle('active');
}

// Save functions for different sections
function saveSystemSettings() {
    showNotification('Paramètres système sauvegardés avec succès', 'success');
}

function saveNotificationSettings() {
    showNotification('Configuration des notifications sauvegardée avec succès', 'success');
}

function testEmailSettings() {
    showNotification('Email de test envoyé avec succès', 'success');
}

function saveSecuritySettings() {
    showNotification('Paramètres de sécurité sauvegardés avec succès', 'success');
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

// User management functions
function loadUsersData() {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    users.forEach(user => {
        const row = createUserRow(user);
        tbody.appendChild(row);
    });
}

function createUserRow(user) {
    const row = document.createElement('tr');
    
    const roleLabels = {
        'student': 'Étudiant',
        'teacher': 'Enseignant',
        'admin_n1': 'Admin N1',
        'admin_n2': 'Admin N2',
        'admin': 'Administrateur'
    };

    const roleBadgeClasses = {
        'student': 'badge-student',
        'teacher': 'badge-teacher',
        'admin_n1': 'badge-admin',
        'admin_n2': 'badge-admin',
        'admin': 'badge-admin'
    };

    row.innerHTML = `
        <td>${user.firstName} ${user.lastName}</td>
        <td>${user.email}</td>
        <td><span class="badge ${roleBadgeClasses[user.role] || 'badge-student'}">${roleLabels[user.role] || user.role}</span></td>
        <td><span class="status-${user.status}">${user.status === 'active' ? 'Actif' : 'Inactif'}</span></td>
        <td>${user.lastLogin}</td>
        <td>
            <button class="btn-icon" onclick="editUser(${user.id})" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn-icon" onclick="resetPassword(${user.id})" title="Réinitialiser le mot de passe">
                <i class="fas fa-key"></i>
            </button>
            <button class="btn-icon btn-danger" onclick="deleteUser(${user.id})" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

function submitAddUser() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);
    
    const newUser = {
        id: users.length + 1,
        firstName: formData.get('firstName'),
        lastName: formData.get('lastName'),
        email: formData.get('email'),
        role: formData.get('role'),
        status: 'active',
        lastLogin: 'Jamais connecté'
    };

    // Validate form data
    if (!newUser.firstName || !newUser.lastName || !newUser.email || !newUser.role) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    // Check if email already exists
    if (users.some(user => user.email === newUser.email)) {
        showNotification('Cette adresse email est déjà utilisée', 'error');
        return;
    }

    // Add user to the list
    users.push(newUser);
    
    // Refresh the users table
    loadUsersData();
    
    // Close modal and show success message
    closeModal('addUserModal');
    showNotification('Utilisateur ajouté avec succès', 'success');
}

function editUser(userId) {
    const user = users.find(u => u.id === userId);
    if (user) {
        console.log('Edit user:', user);
        showNotification('Fonction d\'édition en cours de développement', 'info');
    }
}

function resetPassword(userId) {
    const user = users.find(u => u.id === userId);
    if (user) {
        if (confirm(`Êtes-vous sûr de vouloir réinitialiser le mot de passe de ${user.firstName} ${user.lastName} ?`)) {
            console.log('Reset password for user:', user);
            showNotification('Mot de passe réinitialisé avec succès', 'success');
        }
    }
}

function deleteUser(userId) {
    const user = users.find(u => u.id === userId);
    if (user) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.firstName} ${user.lastName} ?`)) {
            users = users.filter(u => u.id !== userId);
            loadUsersData();
            showNotification('Utilisateur supprimé avec succès', 'success');
        }
    }
}

// Dashboard functions
function loadDashboardData() {
    updateDashboardStats();
    loadRecentActivity();
}

function updateDashboardStats() {
    const stats = {
        activeUsers: users.length,
        pendingReports: 89,
        validatedReports: 156,
        scheduledCommissions: 12
    };
    console.log('Dashboard stats updated:', stats);
}

function loadRecentActivity() {
    const activities = [
        {
            icon: 'fas fa-user-plus',
            text: 'Nouvel utilisateur inscrit: Marie Dupont',
            time: 'Il y a 2 heures'
        },
        {
            icon: 'fas fa-file-upload',
            text: 'Rapport déposé par Jean Martin',
            time: 'Il y a 4 heures'
        },
        {
            icon: 'fas fa-check',
            text: 'Rapport validé par Commission A',
            time: 'Il y a 6 heures'
        }
    ];
    console.log('Recent activity loaded:', activities);
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
    return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
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

// Search functionality
const searchInput = document.querySelector('.search-input');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function(e) {
        const searchTerm = e.target.value.toLowerCase();
        filterUsers(searchTerm);
    }, 300));
}

function filterUsers(searchTerm) {
    const filteredUsers = users.filter(user => 
        user.firstName.toLowerCase().includes(searchTerm) ||
        user.lastName.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm)
    );
    
    displayFilteredUsers(filteredUsers);
}

function displayFilteredUsers(filteredUsers) {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    filteredUsers.forEach(user => {
        const row = createUserRow(user);
        tbody.appendChild(row);
    });
}
    </script>
</body>

</html>
