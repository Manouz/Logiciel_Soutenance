<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/logo_ufrmi.png">
    <link rel="stylesheet" href="style/style.css">
    <title>Gestion de soutenances</title>
</head>
<style>
/* Reset et base */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f5f5;
  color: #333;
  line-height: 1.6;
}

.bodyAdministrateur {
  display: flex;
  min-height: 100vh;
}

/* Overlay */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: none;
}

.overlay.active {
  display: block;
}

/* Sidebar */
.sidebar {
  width: 280px;
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: white;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  overflow-y: auto;
  transition: transform 0.3s ease;
  z-index: 1000;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-logo img {
  width: 40px;
  height: 40px;
  border-radius: 8px;
}

.sidebar-menu {
  flex: 1;
  padding: 20px 0;
}

.menu-category {
  padding: 10px 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 20px;
}

.menu-category:first-child {
  margin-top: 0;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
}

.menu-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  border-left-color: #3498db;
}

.menu-item.active {
  background-color: rgba(52, 152, 219, 0.2);
  color: white;
  border-left-color: #3498db;
}

.menu-icon-item {
  width: 20px;
  height: 20px;
  margin-right: 12px;
  flex-shrink: 0;
}

.sidebar-footer {
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  margin-bottom: 10px;
}

.btn-primary {
  background-color: #e74c3c;
  color: white;
}

.btn-primary:hover {
  background-color: #c0392b;
}

.sidebar-footer h5 {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.5);
}

/* Header */
.headerAdministrateur {
  position: fixed;
  top: 0;
  left: 280px;
  right: 0;
  height: 70px;
  background: white;
  border-bottom: 1px solid #e0e0e0;
  z-index: 998;
  transition: left 0.3s ease;
}

.header-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
  padding: 0 20px;
}

.header-left {
  display: flex;
  align-items: center;
}

.menu-icon {
  display: none;
  flex-direction: column;
  cursor: pointer;
  margin-right: 15px;
}

.menu-icon span {
  width: 25px;
  height: 3px;
  background-color: #333;
  margin: 3px 0;
  transition: 0.3s;
}

.title {
  font-size: 24px;
  font-weight: 600;
  color: #2c3e50;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Notifications et Messages */
.notifications-container,
.messages-container {
  position: relative;
}

.notification-icon,
.message-icon {
  position: relative;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background-color 0.3s ease;
}

.notification-icon:hover,
.message-icon:hover {
  background-color: #f0f0f0;
}

.notification-badge,
.message-badge {
  position: absolute;
  top: 0;
  right: 0;
  background-color: #e74c3c;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-dropdown,
.message-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  width: 320px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  display: none;
  z-index: 1001;
}

.notification-dropdown.active,
.message-dropdown.active {
  display: block;
}

.notification-header,
.message-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notification-title,
.message-title {
  font-weight: 600;
  color: #2c3e50;
}

.view-all {
  color: #3498db;
  text-decoration: none;
  font-size: 14px;
}

.notification-list,
.message-list {
  max-height: 300px;
  overflow-y: auto;
}

.notification-item,
.message-item {
  padding: 15px 20px;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.notification-icon-item,
.message-icon-item {
  width: 20px;
  height: 20px;
  color: #3498db;
  flex-shrink: 0;
}

.notification-content,
.message-content {
  flex: 1;
}

.notification-text,
.message-text {
  font-size: 14px;
  color: #333;
  margin-bottom: 4px;
}

.notification-time,
.message-time {
  font-size: 12px;
  color: #666;
}

.notification-footer,
.message-footer {
  padding: 15px 20px;
  border-top: 1px solid #e0e0e0;
  text-align: center;
}

/* Profil */
.profile-section {
  display: flex;
  align-items: center;
  gap: 10px;
}

.profile-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
}

.profile-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 14px;
  font-weight: 500;
  color: #2c3e50;
}

.user-title {
  font-size: 12px;
  color: #666;
}

/* Main Content */
.main {
  margin-left: 280px;
  margin-top: 70px;
  padding: 30px;
  min-height: calc(100vh - 70px);
  transition: margin-left 0.3s ease;
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard-header h1 {
  font-size: 32px;
  color: #2c3e50;
  margin-bottom: 8px;
}

.dashboard-header p {
  color: #666;
  font-size: 16px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 20px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #3498db, #2980b9);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-content h3 {
  font-size: 28px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 4px;
}

.stat-content p {
  color: #666;
  font-size: 14px;
}

/* Dashboard Content */
.dashboard-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
}

.content-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.section-header {
  padding: 20px 25px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.section-header h2 {
  font-size: 20px;
  color: #2c3e50;
}

.view-all-btn {
  color: #3498db;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}

/* Table */
.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background-color: #f8f9fa;
  padding: 15px 25px;
  text-align: left;
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 1px solid #e0e0e0;
}

.data-table td {
  padding: 15px 25px;
  border-bottom: 1px solid #f0f0f0;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-success {
  background-color: #d4edda;
  color: #155724;
}

.status-pending {
  background-color: #fff3cd;
  color: #856404;
}

.status-scheduled {
  background-color: #d1ecf1;
  color: #0c5460;
}

/* Activity List */
.activity-list {
  padding: 25px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  padding: 15px 0;
  border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  background-color: #f8f9fa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #3498db;
  flex-shrink: 0;
}

.activity-icon svg {
  width: 20px;
  height: 20px;
}

.activity-content p {
  font-size: 14px;
  color: #333;
  margin-bottom: 4px;
}

.activity-time {
  font-size: 12px;
  color: #666;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar.active {
    transform: translateX(0);
  }

  .headerAdministrateur {
    left: 0;
  }

  .main {
    margin-left: 0;
  }

  .menu-icon {
    display: flex;
  }

  .dashboard-content {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .profile-info {
    display: none;
  }

  .notification-dropdown,
  .message-dropdown {
    width: 280px;
  }
}

@media (max-width: 480px) {
  .main {
    padding: 15px;
  }

  .stat-card {
    padding: 20px;
  }

  .dashboard-header h1 {
    font-size: 24px;
  }

  .data-table th,
  .data-table td {
    padding: 10px 15px;
  }
}

</style>
<body class="bodyAdministrateur">
    <!-- Overlay pour fermer le menu -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="/placeholder.svg?height=40&width=40" alt="logo_ufr">
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <div class="menu-category">Menu Principal</div>
            
            <a href="?page=Tableau_bord" class="menu-item active">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="#ffffff" d="M64 256l0-96 160 0 0 96L64 256zm0 64l160 0 0 96L64 416l0-96zm224 96l0-96 160 0 0 96-160 0zM448 256l-160 0 0-96 160 0 0 96zM64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32z"/>
                </svg>
                Tableau de bord
            </a>
            
            <a href="?page=CompteRendu" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="#ffffff" d="M152.1 38.2c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 113C-2.3 103.6-2.3 88.4 7 79s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zm0 160c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM48 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"/>
                </svg>
                Compte Rendu
            </a>
            
            <a href="?page=InsriptionScolarite" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                    <path fill="#ffffff" d="M64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32zm80 256l64 0c44.2 0 80 35.8 80 80c0 8.8-7.2 16-16 16L80 384c-8.8 0-16-7.2-16-16c0-44.2 35.8-80 80-80zm-32-96a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zm256-32l128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-128 0c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64l128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-128 0c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64l128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-128 0c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/>
                </svg>
                Inscription Scolarité
            </a>
            
            <div class="menu-category">Gestion</div>
            
            <a href="?page=Soutenances" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path fill="#ffffff" d="M184 0c30.9 0 56 25.1 56 56l0 400c0 30.9-25.1 56-56 56c-28.9 0-52.7-21.9-55.7-50.1c-5.2 1.4-10.7 2.1-16.3 2.1c-35.3 0-64-28.7-64-64c0-7.4 1.3-14.6 3.6-21.2C21.4 367.4 0 338.2 0 304c0-31.9 18.7-59.5 45.8-72.3C37.1 220.8 32 207 32 192c0-30.7 21.6-56.3 50.4-62.6C80.8 123.9 80 118 80 112c0-29.9 20.6-55.1 48.3-62.1C131.3 21.9 155.1 0 184 0zM328 0c28.9 0 52.6 21.9 55.7 49.9c27.8 7 48.3 32.1 48.3 62.1c0 6-.8 11.9-2.4 17.4c28.8 6.2 50.4 31.9 50.4 62.6c0 15-5.1 28.8-13.8 39.7C493.3 244.5 512 272.1 512 304c0 34.2-21.4 63.4-51.6 74.8c2.3 6.6 3.6 13.8 3.6 21.2c0 35.3-28.7 64-64 64c-5.6 0-11.1-.7-16.3-2.1c-3 28.2-26.8 50.1-55.7 50.1c-30.9 0-56-25.1-56-56l0-400c0-30.9 25.1-56 56-56z"/>
                </svg>
                Soutenances
            </a>
            
            <a href="?page=Enseignants" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                    <path fill="#ffffff" d="M160 64c0-35.3 28.7-64 64-64L576 0c35.3 0 64 28.7 64 64l0 288c0 35.3-28.7 64-64 64l-239.2 0c-11.8-25.5-29.9-47.5-52.4-64l99.6 0 0-32c0-17.7 14.3-32 32-32l64 0c17.7 0 32 14.3 32 32l0 32 64 0 0-288L224 64l0 49.1C205.2 102.2 183.3 96 160 96l0-32zm0 64a96 96 0 1 1 0 192 96 96 0 1 1 0-192zM133.3 352l53.3 0C260.3 352 320 411.7 320 485.3c0 14.7-11.9 26.7-26.7 26.7L26.7 512C11.9 512 0 500.1 0 485.3C0 411.7 59.7 352 133.3 352z"/>
                </svg>
                Enseignants
            </a>
            
            <a href="?page=Etudiants" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                    <path fill="#ffffff" d="M219.3 .5c3.1-.6 6.3-.6 9.4 0l200 40C439.9 42.7 448 52.6 448 64s-8.1 21.3-19.3 23.5L352 102.9l0 57.1c0 70.7-57.3 128-128 128s-128-57.3-128-128l0-57.1L48 93.3l0 65.1 15.7 78.4c.9 4.7-.3 9.6-3.3 13.3s-7.6 5.9-12.4 5.9l-32 0c-4.8 0-9.3-2.1-12.4-5.9s-4.3-8.6-3.3-13.3L16 158.4l0-71.8C6.5 83.3 0 74.3 0 64C0 52.6 8.1 42.7 19.3 40.5l200-40zM111.9 327.7c10.5-3.4 21.8 .4 29.4 8.5l71 75.5c6.3 6.7 17 6.7 23.3 0l71-75.5c7.6-8.1 18.9-11.9 29.4-8.5C401 348.6 448 409.4 448 481.3c0 17-13.8 30.7-30.7 30.7L30.7 512C13.8 512 0 498.2 0 481.3c0-71.9 47-132.7 111.9-153.6z"/>
                </svg>
                Etudiants
            </a>
            
            <a href="?page=Personnel_Administratif" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                    <path fill="#ffffff" d="M96 128a128 128 0 1 0 256 0A128 128 0 1 0 96 128zm94.5 200.2l18.6 31L175.8 483.1l-36-146.9c-2-8.1-9.8-13.4-17.9-11.3C51.9 342.4 0 405.8 0 481.3c0 17 13.8 30.7 30.7 30.7l131.7 0c0 0 0 0 .1 0l5.5 0 112 0 5.5 0c0 0 0 0 .1 0l131.7 0c17 0 30.7-13.8 30.7-30.7c0-75.5-51.9-138.9-121.9-156.4c-8.1-2-15.9 3.3-17.9 11.3l-36 146.9L238.9 359.2l18.6-31c6.4-10.7-1.3-24.2-13.7-24.2L224 304l-19.7 0c-12.4 0-20.1 13.6-13.7 24.2z"/>
                </svg>
                Personnel Administratif
            </a>
            
            <a href="?page=Ressources" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                Ressources
            </a>
            
            <a href="?page=Rapports" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                    <path fill="#ffffff" d="M88.7 223.8L0 375.8 0 96C0 60.7 28.7 32 64 32l117.5 0c17 0 33.3 6.7 45.3 18.7l26.5 26.5c12 12 28.3 18.7 45.3 18.7L416 96c35.3 0 64 28.7 64 64l0 32-336 0c-22.8 0-43.8 12.1-55.3 31.8zm27.6 16.1C122.1 230 132.6 224 144 224l400 0c11.5 0 22 6.1 27.7 16.1s5.7 22.2-.1 32.1l-112 192C453.9 474 443.4 480 432 480L32 480c-11.5 0-22-6.1-27.7-16.1s-5.7-22.2 .1-32.1l112-192z"/>
                </svg>
                Rapport d'étudiants
            </a>
            
            <a href="?page=Utilisateurs" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                    <path fill="#ffffff" d="M0 24C0 10.7 10.7 0 24 0L616 0c13.3 0 24 10.7 24 24s-10.7 24-24 24L24 48C10.7 48 0 37.3 0 24zM0 488c0-13.3 10.7-24 24-24l592 0c13.3 0 24 10.7 24 24s-10.7 24-24 24L24 512c-13.3 0-24-10.7-24-24zM83.2 160a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zM32 320c0-35.3 28.7-64 64-64l96 0c12.2 0 23.7 3.4 33.4 9.4c-37.2 15.1-65.6 47.2-75.8 86.6L64 352c-17.7 0-32-14.3-32-32zm461.6 32c-10.3-40.1-39.6-72.6-77.7-87.4c9.4-5.5 20.4-8.6 32.1-8.6l96 0c35.3 0 64 28.7 64 64c0 17.7-14.3 32-32 32l-82.4 0zM391.2 290.4c32.1 7.4 58.1 30.9 68.9 61.6c3.5 10 5.5 20.8 5.5 32c0 17.7-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32c0-11.2 1.9-22 5.5-32c10.5-29.7 35.3-52.8 66.1-60.9c7.8-2.1 16-3.1 24.5-3.1l96 0c7.4 0 14.7 .8 21.6 2.4zm44-130.4a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zM321.6 96a80 80 0 1 1 0 160 80 80 0 1 1 0-160z"/>
                </svg>
                Utilisateurs
            </a>
            
            <a href="?page=NouveauxUtil" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                    <path fill="#ffffff" d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304l91.4 0C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM504 312l0-64-64 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l64 0 0-64c0-13.3 10.7-24 24-24s24 10.7 24 24l0 64 64 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-64 0 0 64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"/>
                </svg>
                Nouveaux utilisateurs
            </a>
            
            <a href="?page=Audit" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                    <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                </svg>
                Piste d'Audit
            </a>
            
            <div class="menu-category">Configurations</div>
            
            <a href="?page=Parametres" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                Paramètres généraux
            </a>
            
            <a href="?page=Habilitations" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                Habilitations
            </a>
            
            <a href="?page=Confidentialites" class="menu-item">
                <svg class="menu-icon-item" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                Confidentialités
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="btn btn-primary">
                <h4>Déconnexion</h4>
            </div>
            <h5>by KAB Consulting</h5>
        </div>
    </div>
    
    <!-- Header -->
    <header class="headerAdministrateur">
        <div class="header-container">
            <div class="header-left">
                <div class="menu-icon" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="title-container">
                    <div class="title" id="pageTitle">Tableau de bord</div>
                </div>
            </div>
            
            <div class="header-right">
                <!-- Notifications -->
                <div class="notifications-container">
                    <div class="notification-icon" id="notificationToggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="notification-badge">3</span>
                    </div>
                    
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <div class="notification-title">Notifications</div>
                            <a href="#" class="view-all">Voir tout</a>
                        </div>
                        <div class="notification-list">
                            <div class="notification-item">
                                <div class="notification-icon-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    </svg>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-text">Nouvelle soutenance programmée</div>
                                    <div class="notification-time">Il y a 5 minutes</div>
                                </div>
                            </div>
                            <div class="notification-item">
                                <div class="notification-icon-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    </svg>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-text">Mise à jour du système</div>
                                    <div class="notification-time">Il y a 1 heure</div>
                                </div>
                            </div>
                        </div>
                        <div class="notification-footer">
                            <a href="#">Voir toutes les notifications</a>
                        </div>
                    </div>
                </div>
                
                <!-- Messages -->
                <div class="messages-container">
                    <div class="message-icon" id="messageToggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span class="message-badge">2</span>
                    </div>
                    
                    <div class="message-dropdown" id="messageDropdown">
                        <div class="message-header">
                            <div class="message-title">Messages</div>
                            <a href="#" class="view-all">Voir tout</a>
                        </div>
                        <div class="message-list">
                            <div class="message-item">
                                <div class="message-icon-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div class="message-content">
                                    <div class="message-text">Dr. Konan: Bonjour, pouvez-vous confirmer la date...</div>
                                    <div class="message-time">Il y a 10 minutes</div>
                                </div>
                            </div>
                            <div class="message-item">
                                <div class="message-icon-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div class="message-content">
                                    <div class="message-text">Pr. Koffi: Merci pour votre réponse...</div>
                                    <div class="message-time">Il y a 30 minutes</div>
                                </div>
                            </div>
                        </div>
                        <div class="message-footer">
                            <a href="#">Voir tous les messages</a>
                        </div>
                    </div>
                </div>
                
                <!-- Profil -->
                <div class="profile-section">
                    <img src="/placeholder.svg?height=32&width=32" alt="Avatar" class="profile-avatar" />
                    <div class="profile-info">
                        <div class="user-name">Administrateur</div>
                        <div class="user-title">Statut</div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main content -->
    <main class="main" id="mainContent">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Tableau de bord</h1>
                <p>Vue d'ensemble de la gestion des soutenances</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>1,234</h3>
                        <p>Étudiants inscrits</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>89</h3>
                        <p>Enseignants</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>45</h3>
                        <p>Soutenances programmées</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>92%</h3>
                        <p>Taux de réussite</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="content-section">
                    <div class="section-header">
                        <h2>Soutenances récentes</h2>
                        <a href="?page=Soutenances" class="view-all-btn">Voir tout</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Sujet</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Jean Dupont</td>
                                    <td>Intelligence Artificielle en Médecine</td>
                                    <td>15/01/2024</td>
                                    <td><span class="status-badge status-success">Validée</span></td>
                                </tr>
                                <tr>
                                    <td>Marie Martin</td>
                                    <td>Développement Web Moderne</td>
                                    <td>18/01/2024</td>
                                    <td><span class="status-badge status-pending">En attente</span></td>
                                </tr>
                                <tr>
                                    <td>Pierre Durand</td>
                                    <td>Cybersécurité et Blockchain</td>
                                    <td>20/01/2024</td>
                                    <td><span class="status-badge status-scheduled">Programmée</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="content-section">
                    <div class="section-header">
                        <h2>Activités récentes</h2>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p><strong>Nouvel étudiant inscrit:</strong> Sophie Leblanc</p>
                                <span class="activity-time">Il y a 2 heures</span>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p><strong>Soutenance programmée:</strong> Analyse de données - Thomas Petit</p>
                                <span class="activity-time">Il y a 4 heures</span>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14,2 14,8 20,8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10,9 9,9 8,9"></polyline>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p><strong>Rapport généré:</strong> Statistiques mensuelles</p>
                                <span class="activity-time">Il y a 6 heures</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>document.addEventListener("DOMContentLoaded", () => {
  // Elements
  const sidebar = document.getElementById("sidebar")
  const overlay = document.getElementById("overlay")
  const menuToggle = document.getElementById("menuToggle")
  const notificationToggle = document.getElementById("notificationToggle")
  const notificationDropdown = document.getElementById("notificationDropdown")
  const messageToggle = document.getElementById("messageToggle")
  const messageDropdown = document.getElementById("messageDropdown")
  const menuItems = document.querySelectorAll(".menu-item")
  const pageTitle = document.getElementById("pageTitle")

  // Sidebar toggle functionality
  function toggleSidebar() {
    sidebar.classList.toggle("active")
    overlay.classList.toggle("active")
  }

  // Menu toggle click
  if (menuToggle) {
    menuToggle.addEventListener("click", (e) => {
      e.stopPropagation()
      toggleSidebar()
    })
  }

  // Overlay click to close sidebar
  if (overlay) {
    overlay.addEventListener("click", () => {
      sidebar.classList.remove("active")
      overlay.classList.remove("active")
    })
  }

  // Notification dropdown
  if (notificationToggle && notificationDropdown) {
    notificationToggle.addEventListener("click", (e) => {
      e.stopPropagation()
      notificationDropdown.classList.toggle("active")
      if (messageDropdown) {
        messageDropdown.classList.remove("active")
      }
    })
  }

  // Message dropdown
  if (messageToggle && messageDropdown) {
    messageToggle.addEventListener("click", (e) => {
      e.stopPropagation()
      messageDropdown.classList.toggle("active")
      if (notificationDropdown) {
        notificationDropdown.classList.remove("active")
      }
    })
  }

  // Close dropdowns when clicking outside
  document.addEventListener("click", (e) => {
    if (notificationDropdown && !notificationDropdown.contains(e.target) && !notificationToggle.contains(e.target)) {
      notificationDropdown.classList.remove("active")
    }
    if (messageDropdown && !messageDropdown.contains(e.target) && !messageToggle.contains(e.target)) {
      messageDropdown.classList.remove("active")
    }
  })

  // Menu item navigation
  menuItems.forEach((item) => {
    item.addEventListener("click", function (e) {
      // Remove active class from all items
      menuItems.forEach((menuItem) => {
        menuItem.classList.remove("active")
      })

      // Add active class to clicked item
      this.classList.add("active")

      // Update page title based on menu item
      const itemText = this.textContent.trim()
      if (pageTitle) {
        pageTitle.textContent = itemText
      }

      // Close sidebar on mobile after selection
      if (window.innerWidth <= 768) {
        sidebar.classList.remove("active")
        overlay.classList.remove("active")
      }
    })
  })

  // Handle window resize
  window.addEventListener("resize", () => {
    if (window.innerWidth > 768) {
      sidebar.classList.remove("active")
      overlay.classList.remove("active")
    }
  })

  // Smooth scrolling for internal links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Auto-hide notifications after some time
  function autoHideNotifications() {
    setTimeout(() => {
      if (notificationDropdown && notificationDropdown.classList.contains("active")) {
        notificationDropdown.classList.remove("active")
      }
      if (messageDropdown && messageDropdown.classList.contains("active")) {
        messageDropdown.classList.remove("active")
      }
    }, 10000) // Hide after 10 seconds
  }

  // Initialize auto-hide when dropdowns are opened
  if (notificationToggle) {
    notificationToggle.addEventListener("click", autoHideNotifications)
  }
  if (messageToggle) {
    messageToggle.addEventListener("click", autoHideNotifications)
  }

  // Add loading states for better UX
  function showLoading(element) {
    element.style.opacity = "0.6"
    element.style.pointerEvents = "none"
  }

  function hideLoading(element) {
    element.style.opacity = "1"
    element.style.pointerEvents = "auto"
  }

  // Simulate loading for menu items (can be replaced with actual API calls)
  menuItems.forEach((item) => {
    item.addEventListener("click", () => {
      const mainContent = document.getElementById("mainContent")
      if (mainContent) {
        showLoading(mainContent)
        setTimeout(() => {
          hideLoading(mainContent)
        }, 500)
      }
    })
  })

  // Initialize tooltips for icons (optional enhancement)
  const icons = document.querySelectorAll(".menu-icon-item, .stat-icon, .activity-icon")
  icons.forEach((icon) => {
    icon.addEventListener("mouseenter", function () {
      this.style.transform = "scale(1.1)"
    })

    icon.addEventListener("mouseleave", function () {
      this.style.transform = "scale(1)"
    })
  })

  // Add keyboard navigation support
  document.addEventListener("keydown", (e) => {
    // ESC key to close dropdowns and sidebar
    if (e.key === "Escape") {
      if (notificationDropdown) notificationDropdown.classList.remove("active")
      if (messageDropdown) messageDropdown.classList.remove("active")
      sidebar.classList.remove("active")
      overlay.classList.remove("active")
    }

    // Ctrl/Cmd + B to toggle sidebar
    if ((e.ctrlKey || e.metaKey) && e.key === "b") {
      e.preventDefault()
      toggleSidebar()
    }
  })

  console.log("Dashboard initialized successfully")
})
</script>
</body>
</html>
