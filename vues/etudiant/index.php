<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soutenance Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
/* General Styles */
:root {
    --body-bg: #18181b;
    --sidebar-bg: #242526;
    --accent-color: #3b82f6;
    --text-color: #fff;
    --white: #fff;
    --transition: all 0.3s ease;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--body-bg);
    color: var(--text-color);
    margin: 0;
    padding: 0;
    height: 100vh;
    display: flex;
}

/* Sidebar Styles */
.sidebar {
    width: 250px;
    height: 100vh;
    background-color: var(--sidebar-bg);
    padding: 1rem;
    display: flex;
    flex-direction: column;
    transition: var(--transition);
}

.sidebar.collapsed {
    width: 70px;
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: var(--transition);
}

.sidebar.collapsed .sidebar-header {
    padding: 1rem;
    justify-content: center;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 700;
    transition: var(--transition);
}

.logo i {
    font-size: 1.5rem;
    color: var(--accent-color);
}

.sidebar.collapsed .logo {
    justify-content: center;
}

.sidebar.collapsed .logo-text {
    display: none;
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

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: var(--transition);
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

.sidebar.collapsed .user-info {
    display: none;
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

/* Menu Styles */
.menu {
    flex-grow: 1;
    margin-top: 1rem;
}

.menu-item {
    list-style: none;
}

.menu-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--white);
    text-decoration: none;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    transition: var(--transition);
}

.menu-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.menu-link i {
    font-size: 1.1rem;
}

.menu-link span {
    font-weight: 500;
}

.sidebar.collapsed .menu-link span {
    display: none;
}

.sidebar.collapsed .menu-link {
    padding: 0.75rem;
    justify-content: center;
}

/* Main Content Styles */
.main-content {
    flex-grow: 1;
    padding: 2rem;
}

.dashboard-title {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.content {
    background-color: var(--sidebar-bg);
    padding: 2rem;
    border-radius: 8px;
}
</style>
<body>

    <div class="wrapper">

        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <div class="logo" id="logoToggle">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="logo-text">SoutenanceAdmin</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li>
                    <a href="etudiants.php"><i class="fas fa-user-graduate"></i> Étudiants</a>
                </li>
                <li>
                    <a href="professeurs.php"><i class="fas fa-chalkboard-teacher"></i> Professeurs</a>
                </li>
                <li>
                    <a href="soutenances.php"><i class="fas fa-file-alt"></i> Soutenances</a>
                </li>
                <li>
                    <a href="jury.php"><i class="fas fa-users"></i> Jury</a>
                </li>
                <li>
                    <a href="import.php"><i class="fas fa-upload"></i> Import</a>
                </li>
                <li>
                    <a href="parametres.php"><i class="fas fa-cog"></i> Paramètres</a>
                </li>
            </ul>

            <ul class="list-unstyled CTAs">
                <li>
                    <a href="#" class="article">Contact</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">

            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">

                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-align-left"></i>
                        <span>Toggle Sidebar</span>
                    </button>
                    <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-align-justify"></i>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="nav navbar-nav ml-auto">
                            <li class="nav-item active">
                                <a class="nav-link" href="#">Page</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Page</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Page</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Page</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <h2>Bienvenue dans l'interface d'administration des soutenances</h2>
            <p>Ceci est un modèle de base avec une barre latérale et une zone de contenu.</p>
        </div>
    </div>

    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Custom Script -->
    <script>// Sidebar functions
const sidebar = document.querySelector('.sidebar'); // Declare sidebar variable
function toggleSidebar() {
    sidebar.classList.toggle('collapsed');
}

// Ajouter cette fonction après toggleSidebar()
function handleLogoClick() {
    if (sidebar.classList.contains('collapsed')) {
        toggleSidebar();
    }
}

// Initialize event listeners
const sidebarToggle = document.querySelector('.sidebar-toggle'); // Declare sidebarToggle variable
function initializeEventListeners() {
    // Sidebar toggle
    sidebarToggle.addEventListener('click', toggleSidebar);
    
    // Logo click when collapsed
    const logo = document.querySelector('.logo');
    logo.addEventListener('click', handleLogoClick);
    logo.style.cursor = 'pointer';

    // Menu navigation
    const menuItems = document.querySelectorAll('.menu-item'); // Declare menuItems variable
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;
            loadSection(section); // Declare loadSection variable
            setActiveMenuItem(this); // Declare setActiveMenuItem variable
        });
    });

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id); // Declare closeModal variable
        }
    });

    // Form submissions
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAddUser(); // Declare submitAddUser variable
    });
}

function loadSection(section) {
    // Implementation for loading section
}

function setActiveMenuItem(item) {
    // Implementation for setting active menu item
}

function closeModal(modalId) {
    // Implementation for closing modal
}

function submitAddUser() {
    // Implementation for submitting add user form
}</script>
</body>

</html>