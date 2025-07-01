<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="" href="../../../assets/css/admin/admin-style.css">
</head>
<style>
.badge-enseignement {
    background: #dbeafe;
    color: #1e40af;
}

.badge-administration {
    background: #dcfce7;
    color: #166534;
}

.badge-technique {
    background: #fef3c7;
    color: #92400e;
}

.badge-direction {
    background: #f3e8ff;
    color: #7c3aed;
}
</style> 
<body>
    <div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Fonctions</h2>
        <p>Gérer les fonctions et postes au sein de l'établissement</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter une fonction
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher une fonction..." id="searchInput">
    <select class="filter-select" id="categorieFilter">
        <option value="">Toutes les catégories</option>
        <option value="enseignement">Enseignement</option>
        <option value="administration">Administration</option>
        <option value="technique">Technique</option>
        <option value="direction">Direction</option>
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
                <th>Code</th>
                <th>Intitulé</th>
                <th>Catégorie</th>
                <th>Description</th>
                <th>Niveau hiérarchique</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="fonctionTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier une fonction -->
<div class="modal" id="fonctionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter une fonction</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="fonctionForm">
                <input type="hidden" id="fonctionId" name="id">
                <div class="form-group">
                    <label for="code">Code fonction *</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="intitule">Intitulé *</label>
                    <input type="text" id="intitule" name="intitule" required>
                </div>
                <div class="form-group">
                    <label for="categorie">Catégorie *</label>
                    <select id="categorie" name="categorie" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="enseignement">Enseignement</option>
                        <option value="administration">Administration</option>
                        <option value="technique">Technique</option>
                        <option value="direction">Direction</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="niveau_hierarchique">Niveau hiérarchique</label>
                    <select id="niveau_hierarchique" name="niveau_hierarchique">
                        <option value="">Sélectionner un niveau</option>
                        <option value="1">Niveau 1 - Direction</option>
                        <option value="2">Niveau 2 - Cadre</option>
                        <option value="3">Niveau 3 - Agent</option>
                        <option value="4">Niveau 4 - Assistant</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="responsabilites">Responsabilités</label>
                    <textarea id="responsabilites" name="responsabilites" rows="3"></textarea>
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
            <button class="btn btn-primary" onclick="saveFonction()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let fonctionData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadFonctions();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const categorieFilter = document.getElementById('categorieFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterFonctions);
    categorieFilter.addEventListener('change', filterFonctions);
    statusFilter.addEventListener('change', filterFonctions);
}

// Charger les fonctions
function loadFonctions() {
    // Simulation de données fonctions
    fonctionData = [
        { 
            id: 1, 
            code: 'PROF', 
            intitule: 'Professeur', 
            categorie: 'enseignement', 
            niveau_hierarchique: 2,
            description: 'Enseignant-chercheur',
            responsabilites: 'Enseignement, recherche, encadrement',
            statut: 'actif' 
        },
        { 
            id: 2, 
            code: 'MCF', 
            intitule: 'Maître de Conférences', 
            categorie: 'enseignement', 
            niveau_hierarchique: 2,
            description: 'Enseignant-chercheur',
            responsabilites: 'Enseignement, recherche',
            statut: 'actif' 
        },
        { 
            id: 3, 
            code: 'DIR', 
            intitule: 'Directeur', 
            categorie: 'direction', 
            niveau_hierarchique: 1,
            description: 'Direction de l\'établissement',
            responsabilites: 'Gestion administrative, stratégie',
            statut: 'actif' 
        },
        { 
            id: 4, 
            code: 'ADMIN', 
            intitule: 'Administrateur', 
            categorie: 'administration', 
            niveau_hierarchique: 3,
            description: 'Gestion administrative',
            responsabilites: 'Gestion des dossiers étudiants',
            statut: 'actif' 
        }
    ];
    
    renderFonctionTable(fonctionData);
}

// Afficher les données dans le tableau
function renderFonctionTable(data) {
    const tbody = document.getElementById('fonctionTableBody');
    tbody.innerHTML = '';
    
    data.forEach(fonction => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${fonction.code}</td>
            <td>${fonction.intitule}</td>
            <td><span class="badge badge-${fonction.categorie}">${fonction.categorie}</span></td>
            <td>${fonction.description || '-'}</td>
            <td>Niveau ${fonction.niveau_hierarchique}</td>
            <td><span class="badge ${fonction.statut === 'actif' ? 'status-active' : 'status-closed'}">${fonction.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editFonction(${fonction.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteFonction(${fonction.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les fonctions
function filterFonctions() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categorieFilter = document.getElementById('categorieFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = fonctionData.filter(fonction => {
        const matchesSearch = fonction.code.toLowerCase().includes(searchTerm) || 
                            fonction.intitule.toLowerCase().includes(searchTerm) ||
                            (fonction.description && fonction.description.toLowerCase().includes(searchTerm));
        const matchesCategorie = !categorieFilter || fonction.categorie === categorieFilter;
        const matchesStatus = !statusFilter || fonction.statut === statusFilter;
        
        return matchesSearch && matchesCategorie && matchesStatus;
    });
    
    renderFonctionTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter une fonction';
    document.getElementById('fonctionForm').reset();
    document.getElementById('fonctionModal').classList.add('active');
}

// Éditer une fonction
function editFonction(id) {
    const fonction = fonctionData.find(f => f.id === id);
    if (!fonction) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier la fonction';
    document.getElementById('fonctionId').value = fonction.id;
    document.getElementById('code').value = fonction.code;
    document.getElementById('intitule').value = fonction.intitule;
    document.getElementById('categorie').value = fonction.categorie;
    document.getElementById('niveau_hierarchique').value = fonction.niveau_hierarchique;
    document.getElementById('description').value = fonction.description || '';
    document.getElementById('responsabilites').value = fonction.responsabilites || '';
    document.getElementById('statut').value = fonction.statut;
    
    document.getElementById('fonctionModal').classList.add('active');
}

// Sauvegarder une fonction
function saveFonction() {
    const form = document.getElementById('fonctionForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || !formData.get('categorie')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const fonctionData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        categorie: formData.get('categorie'),
        niveau_hierarchique: parseInt(formData.get('niveau_hierarchique')) || null,
        description: formData.get('description'),
        responsabilites: formData.get('responsabilites'),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = fonctionData.findIndex(f => f.id === currentEditId);
        if (index !== -1) {
            fonctionData[index] = { ...fonctionData[index], ...fonctionData };
        }
        showNotification('Fonction modifiée avec succès', 'success');
    } else {
        // Ajout
        fonctionData.id = Date.now();
        fonctionData.push(fonctionData);
        showNotification('Fonction ajoutée avec succès', 'success');
    }
    
    closeModal();
    renderFonctionTable(fonctionData);
}

// Supprimer une fonction
function deleteFonction(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette fonction ?')) {
        fonctionData = fonctionData.filter(f => f.id !== id);
        renderFonctionTable(fonctionData);
        showNotification('Fonction supprimée avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('fonctionModal').classList.remove('active');
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
</body>
</html>


