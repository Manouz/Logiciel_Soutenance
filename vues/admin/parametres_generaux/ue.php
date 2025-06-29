

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des UE</h2>
        <p>Gérer les Unités d'Enseignement</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter une UE
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher une UE..." id="searchInput">
    <select class="filter-select" id="niveauFilter">
        <option value="">Tous les niveaux</option>
        <option value="L1">Licence 1</option>
        <option value="L2">Licence 2</option>
        <option value="L3">Licence 3</option>
        <option value="M1">Master 1</option>
        <option value="M2">Master 2</option>
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
                <th>Code UE</th>
                <th>Intitulé</th>
                <th>Niveau</th>
                <th>Crédits totaux</th>
                <th>Semestre</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="ueTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier une UE -->
<div class="modal" id="ueModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter une UE</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="ueForm">
                <input type="hidden" id="ueId" name="id">
                <div class="form-group">
                    <label for="code">Code UE *</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="intitule">Intitulé *</label>
                    <input type="text" id="intitule" name="intitule" required>
                </div>
                <div class="form-group">
                    <label for="niveau">Niveau *</label>
                    <select id="niveau" name="niveau" required>
                        <option value="">Sélectionner un niveau</option>
                        <option value="L1">Licence 1</option>
                        <option value="L2">Licence 2</option>
                        <option value="L3">Licence 3</option>
                        <option value="M1">Master 1</option>
                        <option value="M2">Master 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="semestre">Semestre *</label>
                    <select id="semestre" name="semestre" required>
                        <option value="">Sélectionner un semestre</option>
                        <option value="S1">Semestre 1</option>
                        <option value="S2">Semestre 2</option>
                        <option value="S3">Semestre 3</option>
                        <option value="S4">Semestre 4</option>
                        <option value="S5">Semestre 5</option>
                        <option value="S6">Semestre 6</option>
                        <option value="S7">Semestre 7</option>
                        <option value="S8">Semestre 8</option>
                        <option value="S9">Semestre 9</option>
                        <option value="S10">Semestre 10</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="credits">Crédits totaux *</label>
                    <input type="number" id="credits" name="credits" min="1" max="60" required>
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
            <button class="btn btn-primary" onclick="saveUE()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let ueData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadUEs();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const niveauFilter = document.getElementById('niveauFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterUEs);
    niveauFilter.addEventListener('change', filterUEs);
    statusFilter.addEventListener('change', filterUEs);
}

// Charger les UE
function loadUEs() {
    // Simulation de données UE
    ueData = [
        { id: 1, code: 'UE1', intitule: 'Fondamentaux informatiques', niveau: 'L1', semestre: 'S1', credits: 12, statut: 'actif' },
        { id: 2, code: 'UE2', intitule: 'Développement web', niveau: 'L2', semestre: 'S3', credits: 9, statut: 'actif' },
        { id: 3, code: 'UE3', intitule: 'Bases de données', niveau: 'L2', semestre: 'S4', credits: 6, statut: 'actif' },
        { id: 4, code: 'UE4', intitule: 'Projet de fin d\'études', niveau: 'M2', semestre: 'S10', credits: 30, statut: 'actif' }
    ];
    
    renderUETable(ueData);
}

// Afficher les données dans le tableau
function renderUETable(data) {
    const tbody = document.getElementById('ueTableBody');
    tbody.innerHTML = '';
    
    data.forEach(ue => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${ue.code}</td>
            <td>${ue.intitule}</td>
            <td>${ue.niveau}</td>
            <td>${ue.credits}</td>
            <td>${ue.semestre}</td>
            <td><span class="badge ${ue.statut === 'actif' ? 'status-active' : 'status-closed'}">${ue.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editUE(${ue.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteUE(${ue.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les UE
function filterUEs() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const niveauFilter = document.getElementById('niveauFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = ueData.filter(ue => {
        const matchesSearch = ue.code.toLowerCase().includes(searchTerm) || 
                            ue.intitule.toLowerCase().includes(searchTerm);
        const matchesNiveau = !niveauFilter || ue.niveau === niveauFilter;
        const matchesStatus = !statusFilter || ue.statut === statusFilter;
        
        return matchesSearch && matchesNiveau && matchesStatus;
    });
    
    renderUETable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter une UE';
    document.getElementById('ueForm').reset();
    document.getElementById('ueModal').classList.add('active');
}

// Éditer une UE
function editUE(id) {
    const ue = ueData.find(u => u.id === id);
    if (!ue) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier l\'UE';
    document.getElementById('ueId').value = ue.id;
    document.getElementById('code').value = ue.code;
    document.getElementById('intitule').value = ue.intitule;
    document.getElementById('niveau').value = ue.niveau;
    document.getElementById('semestre').value = ue.semestre;
    document.getElementById('credits').value = ue.credits;
    document.getElementById('statut').value = ue.statut;
    
    document.getElementById('ueModal').classList.add('active');
}

// Sauvegarder une UE
function saveUE() {
    const form = document.getElementById('ueForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || !formData.get('niveau') || 
        !formData.get('semestre') || !formData.get('credits')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const ueData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        niveau: formData.get('niveau'),
        semestre: formData.get('semestre'),
        credits: parseInt(formData.get('credits')),
        description: formData.get('description'),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = ueData.findIndex(u => u.id === currentEditId);
        if (index !== -1) {
            ueData[index] = { ...ueData[index], ...ueData };
        }
        showNotification('UE modifiée avec succès', 'success');
    } else {
        // Ajout
        ueData.id = Date.now();
        ueData.push(ueData);
        showNotification('UE ajoutée avec succès', 'success');
    }
    
    closeModal();
    renderUETable(ueData);
}

// Supprimer une UE
function deleteUE(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette UE ?')) {
        ueData = ueData.filter(u => u.id !== id);
        renderUETable(ueData);
        showNotification('UE supprimée avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('ueModal').classList.remove('active');
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