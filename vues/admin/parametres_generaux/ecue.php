

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des ECUE</h2>
        <p>Gérer les Éléments Constitutifs d'Unité d'Enseignement</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un ECUE
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un ECUE..." id="searchInput">
    <select class="filter-select" id="ueFilter">
        <option value="">Toutes les UE</option>
        <!-- Options chargées dynamiquement -->
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
                <th>Code ECUE</th>
                <th>Intitulé</th>
                <th>UE parente</th>
                <th>Crédits</th>
                <th>Coefficient</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="ecueTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un ECUE -->
<div class="modal" id="ecueModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un ECUE</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="ecueForm">
                <input type="hidden" id="ecueId" name="id">
                <div class="form-group">
                    <label for="code">Code ECUE *</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="intitule">Intitulé *</label>
                    <input type="text" id="intitule" name="intitule" required>
                </div>
                <div class="form-group">
                    <label for="ue_id">UE parente *</label>
                    <select id="ue_id" name="ue_id" required>
                        <option value="">Sélectionner une UE</option>
                        <!-- Options chargées dynamiquement -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="credits">Crédits *</label>
                    <input type="number" id="credits" name="credits" min="1" max="30" required>
                </div>
                <div class="form-group">
                    <label for="coefficient">Coefficient</label>
                    <input type="number" id="coefficient" name="coefficient" min="0" step="0.1" value="1">
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
            <button class="btn btn-primary" onclick="saveEcue()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let ecueData = [];
let ueData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadUEs();
    loadECUEs();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const ueFilter = document.getElementById('ueFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterECUEs);
    ueFilter.addEventListener('change', filterECUEs);
    statusFilter.addEventListener('change', filterECUEs);
}

// Charger les UE
function loadUEs() {
    // Simulation de données UE
    ueData = [
        { id: 1, code: 'UE1', intitule: 'Fondamentaux informatiques' },
        { id: 2, code: 'UE2', intitule: 'Développement web' },
        { id: 3, code: 'UE3', intitule: 'Bases de données' }
    ];
    
    // Remplir les filtres et formulaires
    const ueFilter = document.getElementById('ueFilter');
    const ueSelect = document.getElementById('ue_id');
    
    ueData.forEach(ue => {
        ueFilter.innerHTML += `<option value="${ue.id}">${ue.code} - ${ue.intitule}</option>`;
        ueSelect.innerHTML += `<option value="${ue.id}">${ue.code} - ${ue.intitule}</option>`;
    });
}

// Charger les ECUE
function loadECUEs() {
    // Simulation de données ECUE
    ecueData = [
        { id: 1, code: 'ECUE1.1', intitule: 'Algorithmes et structures de données', ue_id: 1, credits: 6, coefficient: 1, statut: 'actif' },
        { id: 2, code: 'ECUE1.2', intitule: 'Programmation orientée objet', ue_id: 1, credits: 6, coefficient: 1, statut: 'actif' },
        { id: 3, code: 'ECUE2.1', intitule: 'HTML/CSS/JavaScript', ue_id: 2, credits: 4, coefficient: 1, statut: 'actif' }
    ];
    
    renderECUETable(ecueData);
}

// Afficher les données dans le tableau
function renderECUETable(data) {
    const tbody = document.getElementById('ecueTableBody');
    tbody.innerHTML = '';
    
    data.forEach(ecue => {
        const ue = ueData.find(u => u.id === ecue.ue_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${ecue.code}</td>
            <td>${ecue.intitule}</td>
            <td>${ue ? ue.code : '-'}</td>
            <td>${ecue.credits}</td>
            <td>${ecue.coefficient}</td>
            <td><span class="badge ${ecue.statut === 'actif' ? 'status-active' : 'status-closed'}">${ecue.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editEcue(${ecue.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteEcue(${ecue.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les ECUE
function filterECUEs() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const ueFilter = document.getElementById('ueFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = ecueData.filter(ecue => {
        const matchesSearch = ecue.code.toLowerCase().includes(searchTerm) || 
                            ecue.intitule.toLowerCase().includes(searchTerm);
        const matchesUE = !ueFilter || ecue.ue_id == ueFilter;
        const matchesStatus = !statusFilter || ecue.statut === statusFilter;
        
        return matchesSearch && matchesUE && matchesStatus;
    });
    
    renderECUETable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un ECUE';
    document.getElementById('ecueForm').reset();
    document.getElementById('ecueModal').classList.add('active');
}

// Éditer un ECUE
function editEcue(id) {
    const ecue = ecueData.find(e => e.id === id);
    if (!ecue) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier l\'ECUE';
    document.getElementById('ecueId').value = ecue.id;
    document.getElementById('code').value = ecue.code;
    document.getElementById('intitule').value = ecue.intitule;
    document.getElementById('ue_id').value = ecue.ue_id;
    document.getElementById('credits').value = ecue.credits;
    document.getElementById('coefficient').value = ecue.coefficient;
    document.getElementById('statut').value = ecue.statut;
    
    document.getElementById('ecueModal').classList.add('active');
}

// Sauvegarder un ECUE
function saveEcue() {
    const form = document.getElementById('ecueForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || !formData.get('ue_id') || !formData.get('credits')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const ecueData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        ue_id: parseInt(formData.get('ue_id')),
        credits: parseInt(formData.get('credits')),
        coefficient: parseFloat(formData.get('coefficient')) || 1,
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = ecueData.findIndex(e => e.id === currentEditId);
        if (index !== -1) {
            ecueData[index] = { ...ecueData[index], ...ecueData };
        }
        showNotification('ECUE modifié avec succès', 'success');
    } else {
        // Ajout
        ecueData.id = Date.now();
        ecueData.push(ecueData);
        showNotification('ECUE ajouté avec succès', 'success');
    }
    
    closeModal();
    renderECUETable(ecueData);
}

// Supprimer un ECUE
function deleteEcue(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet ECUE ?')) {
        ecueData = ecueData.filter(e => e.id !== id);
        renderECUETable(ecueData);
        showNotification('ECUE supprimé avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('ecueModal').classList.remove('active');
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