<?php
// Vérification de l'authentification et des permissions
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../login.php');
    exit();
}*/
?>

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Entreprises</h2>
        <p>Gérer les entreprises partenaires et d'accueil</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter une entreprise
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher une entreprise..." id="searchInput">
    <select class="filter-select" id="secteurFilter">
        <option value="">Tous les secteurs</option>
        <option value="informatique">Informatique</option>
        <option value="finance">Finance</option>
        <option value="sante">Santé</option>
        <option value="education">Éducation</option>
        <option value="industrie">Industrie</option>
        <option value="commerce">Commerce</option>
    </select>
    <select class="filter-select" id="statusFilter">
        <option value="">Tous les statuts</option>
        <option value="actif">Actif</option>
        <option value="inactif">Inactif</option>
    </select>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>Raison sociale</th>
                <th>Secteur</th>
                <th>Ville</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="entrepriseTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier une entreprise -->
<div class="modal" id="entrepriseModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter une entreprise</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="entrepriseForm">
                <input type="hidden" id="entrepriseId" name="id">
                <div class="form-group">
                    <label for="raison_sociale">Raison sociale *</label>
                    <input type="text" id="raison_sociale" name="raison_sociale" required>
                </div>
                <div class="form-group">
                    <label for="secteur">Secteur d'activité *</label>
                    <select id="secteur" name="secteur" required>
                        <option value="">Sélectionner un secteur</option>
                        <option value="informatique">Informatique</option>
                        <option value="finance">Finance</option>
                        <option value="sante">Santé</option>
                        <option value="education">Éducation</option>
                        <option value="industrie">Industrie</option>
                        <option value="commerce">Commerce</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <textarea id="adresse" name="adresse" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" required>
                </div>
                <div class="form-group">
                    <label for="code_postal">Code postal</label>
                    <input type="text" id="code_postal" name="code_postal">
                </div>
                <div class="form-group">
                    <label for="pays">Pays</label>
                    <input type="text" id="pays" name="pays" value="France">
                </div>
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="site_web">Site web</label>
                    <input type="url" id="site_web" name="site_web">
                </div>
                <div class="form-group">
                    <label for="siret">Numéro SIRET</label>
                    <input type="text" id="siret" name="siret">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
            <button class="btn btn-primary" onclick="saveEntreprise()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let entrepriseData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadEntreprises();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const secteurFilter = document.getElementById('secteurFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterEntreprises);
    secteurFilter.addEventListener('change', filterEntreprises);
    statusFilter.addEventListener('change', filterEntreprises);
}

// Charger les entreprises
function loadEntreprises() {
    // Simulation de données entreprises
    entrepriseData = [
        { 
            id: 1, 
            raison_sociale: 'Tech Solutions SARL', 
            secteur: 'informatique', 
            ville: 'Paris',
            telephone: '01 23 45 67 89',
            email: 'contact@techsolutions.fr',
            statut: 'actif' 
        },
        { 
            id: 2, 
            raison_sociale: 'Banque Nationale', 
            secteur: 'finance', 
            ville: 'Lyon',
            telephone: '04 78 90 12 34',
            email: 'info@banquenationale.fr',
            statut: 'actif' 
        },
        { 
            id: 3, 
            raison_sociale: 'Hôpital Central', 
            secteur: 'sante', 
            ville: 'Marseille',
            telephone: '04 91 23 45 67',
            email: 'contact@hopitalcentral.fr',
            statut: 'actif' 
        },
        { 
            id: 4, 
            raison_sociale: 'Startup Innovante', 
            secteur: 'informatique', 
            ville: 'Toulouse',
            telephone: '05 61 23 45 67',
            email: 'hello@startupinnovante.fr',
            statut: 'inactif' 
        }
    ];
    
    renderEntrepriseTable(entrepriseData);
}

// Afficher les données dans le tableau
function renderEntrepriseTable(data) {
    const tbody = document.getElementById('entrepriseTableBody');
    tbody.innerHTML = '';
    
    data.forEach(entreprise => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${entreprise.raison_sociale}</td>
            <td><span class="badge badge-${entreprise.secteur}">${entreprise.secteur}</span></td>
            <td>${entreprise.ville}</td>
            <td>${entreprise.telephone || '-'}</td>
            <td>${entreprise.email || '-'}</td>
            <td><span class="badge ${entreprise.statut === 'actif' ? 'status-active' : 'status-closed'}">${entreprise.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editEntreprise(${entreprise.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteEntreprise(${entreprise.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les entreprises
function filterEntreprises() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const secteurFilter = document.getElementById('secteurFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = entrepriseData.filter(entreprise => {
        const matchesSearch = entreprise.raison_sociale.toLowerCase().includes(searchTerm) || 
                            entreprise.ville.toLowerCase().includes(searchTerm) ||
                            (entreprise.email && entreprise.email.toLowerCase().includes(searchTerm));
        const matchesSecteur = !secteurFilter || entreprise.secteur === secteurFilter;
        const matchesStatus = !statusFilter || entreprise.statut === statusFilter;
        
        return matchesSearch && matchesSecteur && matchesStatus;
    });
    
    renderEntrepriseTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter une entreprise';
    document.getElementById('entrepriseForm').reset();
    document.getElementById('pays').value = 'France';
    document.getElementById('entrepriseModal').classList.add('active');
}

// Éditer une entreprise
function editEntreprise(id) {
    const entreprise = entrepriseData.find(e => e.id === id);
    if (!entreprise) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier l\'entreprise';
    document.getElementById('entrepriseId').value = entreprise.id;
    document.getElementById('raison_sociale').value = entreprise.raison_sociale;
    document.getElementById('secteur').value = entreprise.secteur;
    document.getElementById('adresse').value = entreprise.adresse || '';
    document.getElementById('ville').value = entreprise.ville;
    document.getElementById('code_postal').value = entreprise.code_postal || '';
    document.getElementById('pays').value = entreprise.pays || 'France';
    document.getElementById('telephone').value = entreprise.telephone || '';
    document.getElementById('email').value = entreprise.email || '';
    document.getElementById('site_web').value = entreprise.site_web || '';
    document.getElementById('siret').value = entreprise.siret || '';
    document.getElementById('description').value = entreprise.description || '';
    document.getElementById('statut').value = entreprise.statut;
    
    document.getElementById('entrepriseModal').classList.add('active');
}

// Sauvegarder une entreprise
function saveEntreprise() {
    const form = document.getElementById('entrepriseForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('raison_sociale') || !formData.get('secteur') || !formData.get('ville')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Validation email si fourni
    const email = formData.get('email');
    if (email && !isValidEmail(email)) {
        showNotification('Veuillez saisir une adresse email valide', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const entrepriseData = {
        raison_sociale: formData.get('raison_sociale'),
        secteur: formData.get('secteur'),
        adresse: formData.get('adresse'),
        ville: formData.get('ville'),
        code_postal: formData.get('code_postal'),
        pays: formData.get('pays'),
        telephone: formData.get('telephone'),
        email: formData.get('email'),
        site_web: formData.get('site_web'),
        siret: formData.get('siret'),
        description: formData.get('description'),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = entrepriseData.findIndex(e => e.id === currentEditId);
        if (index !== -1) {
            entrepriseData[index] = { ...entrepriseData[index], ...entrepriseData };
        }
        showNotification('Entreprise modifiée avec succès', 'success');
    } else {
        // Ajout
        entrepriseData.id = Date.now();
        entrepriseData.push(entrepriseData);
        showNotification('Entreprise ajoutée avec succès', 'success');
    }
    
    closeModal();
    renderEntrepriseTable(entrepriseData);
}

// Validation email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Supprimer une entreprise
function deleteEntreprise(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?')) {
        entrepriseData = entrepriseData.filter(e => e.id !== id);
        renderEntrepriseTable(entrepriseData);
        showNotification('Entreprise supprimée avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('entrepriseModal').classList.remove('active');
    currentEditId = null;
}

// Afficher une notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
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
</script>

<style>
.badge-informatique {
    background: #dbeafe;
    color: #1e40af;
}

.badge-finance {
    background: #dcfce7;
    color: #166534;
}

.badge-sante {
    background: #fee2e2;
    color: #991b1b;
}

.badge-education {
    background: #fef3c7;
    color: #92400e;
}

.badge-industrie {
    background: #f3e8ff;
    color: #7c3aed;
}

.badge-commerce {
    background: #ecfdf5;
    color: #065f46;
}

.badge-autre {
    background: #f1f5f9;
    color: #475569;
}
</style> 