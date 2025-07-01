<?php 
/*include "../../config/database.php";
session_start();*/
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration des paramètres - MaSoutenance</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="parametres-style.css">
    <link rel="stylesheet" href="modal-style.css">
</head>
<style>
/* Modal Overlay Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-overlay.active {
  opacity: 1;
  visibility: visible;
}

.modal-container {
  background: var(--white);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-xl);
  width: 95%;
  max-width: 1200px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  transform: scale(0.9) translateY(20px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.modal-overlay.active .modal-container {
  transform: scale(1) translateY(0);
}

/* Modal Header */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--gray-200);
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  color: var(--white);
  flex-shrink: 0;
}

.modal-back-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.1);
  color: var(--white);
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: var(--transition);
  backdrop-filter: blur(10px);
}

.modal-back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateX(-2px);
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  flex: 1;
  text-align: center;
}

.modal-close-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.1);
  color: var(--white);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.1rem;
  transition: var(--transition);
  backdrop-filter: blur(10px);
}

.modal-close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: rotate(90deg);
}

/* Modal Body */
.modal-body {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  background: var(--gray-50);
}

.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: var(--gray-100);
}

.modal-body::-webkit-scrollbar-thumb {
  background: var(--gray-300);
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: var(--gray-400);
}

/* Modal Loading */
.modal-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid var(--gray-200);
  border-top: 4px solid var(--secondary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.modal-loading p {
  color: var(--gray-600);
  font-size: 1rem;
  margin: 0;
}

/* Modal Content Styles */
.modal-content {
  padding: 2rem;
  background: var(--white);
  min-height: 500px;
}

.modal-content-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--gray-200);
}

.modal-content-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--primary-color);
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.modal-content-description {
  color: var(--gray-600);
  font-size: 1rem;
  line-height: 1.5;
}

/* Modal Actions */
.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--gray-200);
  background: var(--gray-50);
  flex-shrink: 0;
}

.modal-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.modal-btn-primary {
  background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
  color: var(--white);
}

.modal-btn-primary:hover {
  background: linear-gradient(135deg, #059669, #10b981);
  transform: translateY(-1px);
  box-shadow: var(--shadow-lg);
}

.modal-btn-secondary {
  background: var(--gray-200);
  color: var(--gray-700);
}

.modal-btn-secondary:hover {
  background: var(--gray-300);
}

/* Error State */
.modal-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.modal-error-icon {
  font-size: 4rem;
  color: var(--error-color);
  margin-bottom: 1rem;
}

.modal-error h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-900);
  margin-bottom: 0.5rem;
}

.modal-error p {
  color: var(--gray-600);
  margin-bottom: 2rem;
}

.modal-error .btn {
  background: var(--error-color);
  color: var(--white);
}

.modal-error .btn:hover {
  background: #dc2626;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-container {
    width: 100%;
    height: 100%;
    max-height: 100vh;
    border-radius: 0;
  }

  .modal-header {
    padding: 1rem 1.5rem;
  }

  .modal-title {
    font-size: 1.25rem;
  }

  .modal-back-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
  }

  .modal-content {
    padding: 1.5rem;
  }

  .modal-actions {
    padding: 1rem 1.5rem;
    flex-direction: column;
  }

  .modal-btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .modal-header {
    padding: 0.75rem 1rem;
  }

  .modal-title {
    font-size: 1.125rem;
  }

  .modal-content {
    padding: 1rem;
  }

  .modal-loading {
    padding: 2rem 1rem;
  }

  .loading-spinner {
    width: 40px;
    height: 40px;
  }
}

/* Animation pour l'ouverture/fermeture */
@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes modalFadeOut {
  from {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
  to {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
  }
}

.modal-overlay.closing {
  animation: modalFadeOut 0.3s ease-out forwards;
}

/* Styles pour le contenu chargé dynamiquement */
.dynamic-content {
  animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Amélioration du bouton configurer */
.btn-configure {
  position: relative;
  overflow: hidden;
}

.btn-configure::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.btn-configure:hover::before {
  left: 100%;
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
                    <li class="menu-item">
                        <a href="?page=dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="?page=etudiant">
                            <i class="fas fa-users"></i>
                            <span>Gestion des étudiants</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="?page=enseignant">
                            <i class="fas fa-user-graduate"></i>
                            <span>Gestion des enseignants</span>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="?page=parametres">
                            <i class="fas fa-cog"></i>
                            <span>Paramètres</span>
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
                <div class="header-title">
                    <h1><i class="fas fa-cog text-emerald"></i> Configuration des paramètres</h1>
                    <p>Gérez tous les paramètres de configuration du système</p>
                </div>
                <div class="header-actions">
                    <div class="badge-info">
                        <span class="badge badge-success">17 modules configurés</span>
                    </div>
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                </div>
            </header>

            <div class="content-body">
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
                            'href' => 'parametres_niveau-approbation-ecue.php'
                        ],
                        [
                            'id' => 2,
                            'title' => 'UE',
                            'description' => 'Gestion des Unités d\'Enseignement et de leur configuration',
                            'icon' => 'fas fa-book-open',
                            'color' => 'green',
                            'status' => 'Actif',
                            'href' => 'ue.php'
                        ],
                        [
                            'id' => 3,
                            'title' => 'ECUE',
                            'description' => 'Gestion des Éléments Constitutifs d\'Unité d\'Enseignement',
                            'icon' => 'fas fa-award',
                            'color' => 'blue',
                            'status' => 'Actif',
                            'href' => 'parametres_niveau-approbation-ecue.php'
                        ],
                        [
                            'id' => 4,
                            'title' => 'Fonction',
                            'description' => 'Configuration des fonctions et rôles dans le système',
                            'icon' => 'fas fa-user-check',
                            'color' => 'purple',
                            'status' => 'Actif',
                            'href' => 'fonction.php'
                        ],
                        [
                            'id' => 5,
                            'title' => 'Grade',
                            'description' => 'Gestion des grades académiques et professionnels',
                            'icon' => 'fas fa-graduation-cap',
                            'color' => 'orange',
                            'status' => 'Actif',
                            'href' => 'grade.php'
                        ],
                        [
                            'id' => 6,
                            'title' => 'Année Académique',
                            'description' => 'Configuration des années académiques et périodes d\'étude',
                            'icon' => 'fas fa-calendar',
                            'color' => 'red',
                            'status' => 'Actif',
                            'href' => 'annee-academique.php'
                        ],
                        [
                            'id' => 7,
                            'title' => 'Niveau d\'étude',
                            'description' => 'Définition des niveaux d\'étude (Licence, Master, Doctorat)',
                            'icon' => 'fas fa-bookmark',
                            'color' => 'yellow',
                            'status' => 'Actif',
                            'href' => 'niveau-etude.php'
                        ],
                        [
                            'id' => 8,
                            'title' => 'Entreprise',
                            'description' => 'Gestion des entreprises partenaires pour les stages',
                            'icon' => 'fas fa-building',
                            'color' => 'indigo',
                            'status' => 'Actif',
                            'href' => 'entreprise.php'
                        ],
                        [
                            'id' => 9,
                            'title' => 'Type utilisateur',
                            'description' => 'Configuration des types d\'utilisateurs du système',
                            'icon' => 'fas fa-users',
                            'color' => 'pink',
                            'status' => 'Actif',
                            'href' => 'type-utilisateur.php'
                        ],
                        [
                            'id' => 10,
                            'title' => 'Utilisateur',
                            'description' => 'Gestion des comptes utilisateurs et leurs paramètres',
                            'icon' => 'fas fa-user',
                            'color' => 'teal',
                            'status' => 'Actif',
                            'href' => 'utilisateur.php'
                        ],
                        [
                            'id' => 11,
                            'title' => 'Groupe Utilisateur',
                            'description' => 'Configuration des groupes d\'utilisateurs et permissions',
                            'icon' => 'fas fa-users-cog',
                            'color' => 'cyan',
                            'status' => 'Actif',
                            'href' => 'groupe-utilisateur.php'
                        ],
                        [
                            'id' => 12,
                            'title' => 'Traitement',
                            'description' => 'Configuration des processus de traitement des dossiers',
                            'icon' => 'fas fa-cogs',
                            'color' => 'emerald',
                            'status' => 'Actif',
                            'href' => 'parametres_de_travail/traitements.php'
                        ],
                        [
                            'id' => 13,
                            'title' => 'Posséder',
                            'description' => 'Gestion des relations de possession entre entités',
                            'icon' => 'fas fa-database',
                            'color' => 'violet',
                            'status' => 'Actif',
                            'href' => 'posseder.php'
                        ],
                        [
                            'id' => 14,
                            'title' => 'Statut Jury',
                            'description' => 'Configuration des statuts des membres de jury',
                            'icon' => 'fas fa-balance-scale',
                            'color' => 'rose',
                            'status' => 'Actif',
                            'href' => 'statut-jury.php'
                        ],
                        [
                            'id' => 15,
                            'title' => 'Spécialité',
                            'description' => 'Gestion des spécialités académiques et professionnelles',
                            'icon' => 'fas fa-star',
                            'color' => 'amber',
                            'status' => 'Actif',
                            'href' => 'specialite.php'
                        ],
                        [
                            'id' => 16,
                            'title' => 'Niveau d\'accès aux données',
                            'description' => 'Configuration des niveaux d\'accès et permissions',
                            'icon' => 'fas fa-shield-alt',
                            'color' => 'slate',
                            'status' => 'Actif',
                            'href' => 'niveau-acces.php'
                        ],
                        [
                            'id' => 17,
                            'title' => 'Action',
                            'description' => 'Configuration des actions disponibles dans le système',
                            'icon' => 'fas fa-lock',
                            'color' => 'stone',
                            'status' => 'Actif',
                            'href' => 'action.php'
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
                                <button class="btn btn-configure" data-modal="<?= $card['href'] ?>" data-title="<?= $card['title'] ?>">
                                    <i class="fas fa-cog"></i>
                                    Configurer
                                </button>
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
            </div>
        </main>
    </div>

    <!-- Modal Overlay -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-container">
            <div class="modal-header">
                <button class="modal-back-btn" id="modalBackBtn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Retour</span>
                </button>
                <h2 class="modal-title" id="modalTitle">Configuration</h2>
                <button class="modal-close-btn" id="modalCloseBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="modal-loading">
                    <div class="loading-spinner"></div>
                    <p>Chargement en cours...</p>
                </div>
            </div>
        </div>
    </div>

    <script>// Variables globales pour le modal
const modalOverlay = document.getElementById('modalOverlay')
const modalTitle = document.getElementById('modalTitle')
const modalBody = document.getElementById('modalBody')
const modalBackBtn = document.getElementById('modalBackBtn')
const modalCloseBtn = document.getElementById('modalCloseBtn')

// Variables globales pour la sidebar et recherche
const sidebar = document.getElementById("sidebar")
const sidebarToggle = document.getElementById("sidebarToggle")
const logoToggle = document.getElementById("logoToggle")
const searchInput = document.getElementById("searchInput")
const settingsGrid = document.getElementById("settingsGrid")
const noResults = document.getElementById("noResults")

// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
  initializeSidebar()
  initializeSearch()
  initializeCards()
  initializeModal()
})

// Initialisation du système modal
function initializeModal() {
  // Gestionnaires d'événements pour les boutons de configuration
  const configureButtons = document.querySelectorAll('.btn-configure')
  configureButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault()
      const modalUrl = this.getAttribute('data-modal')
      const modalTitleText = this.getAttribute('data-title')
      openModal(modalUrl, modalTitleText)
    })
  })

  // Gestionnaires pour fermer le modal
  modalBackBtn.addEventListener('click', closeModal)
  modalCloseBtn.addEventListener('click', closeModal)
  
  // Fermer le modal en cliquant sur l'overlay
  modalOverlay.addEventListener('click', function(e) {
    if (e.target === modalOverlay) {
      closeModal()
   
</script>
</body>
</html>
