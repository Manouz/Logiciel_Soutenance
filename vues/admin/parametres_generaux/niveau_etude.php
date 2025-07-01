
<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Niveaux d'Étude</h2>
        <p>Gérer les niveaux d'étude et leurs caractéristiques</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un niveau
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un niveau..." id="searchInput">
    <select class="filter-select" id="cycleFilter">
        <option value="">Tous les cycles</option>
        <option value="licence">Licence</option>
        <option value="master">Master</option>
        <option value="doctorat">Doctorat</option>
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
                <th>Cycle</th>
                <th>Durée (années)</th>
                <th>Crédits requis</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="niveauTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un niveau d'étude -->
<div class="modal" id="niveauModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un niveau d'étude</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="niveauForm">
                <input type="hidden" id="niveauId" name="id">
                <div class="form-group">
                    <label for="code">Code niveau *</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="intitule">Intitulé *</label>
                    <input type="text" id="intitule" name="intitule" required>
                </div>
                <div class="form-group">
                    <label for="cycle">Cycle *</label>
                    <select id="cycle" name="cycle" required>
                        <option value="">Sélectionner un cycle</option>
                        <option value="licence">Licence</option>
                        <option value="master">Master</option>
                        <option value="doctorat">Doctorat</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="duree">Durée (années) *</label>
                    <input type="number" id="duree" name="duree" min="1" max="8" required>
                </div>
                <div class="form-group">
                    <label for="credits_requis">Crédits requis</label>
                    <input type="number" id="credits_requis" name="credits_requis" min="0" max="300">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="prerequis">Prérequis</label>
                    <textarea id="prerequis" name="prerequis" rows="2"></textarea>
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
            <button class="btn btn-primary" onclick="saveNiveau()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let niveauData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadNiveaux();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const cycleFilter = document.getElementById('cycleFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterNiveaux);
    cycleFilter.addEventListener('change', filterNiveaux);
    statusFilter.addEventListener('change', filterNiveaux);
}

// Charger les niveaux d'étude
function loadNiveaux() {
    // Simulation de données niveaux d'étude
    niveauData = [
        { 
            id: 1, 
            code: 'L1', 
            intitule: 'Licence 1', 
            cycle: 'licence', 
            duree: 1,
            credits_requis: 0,
            description: 'Première année de licence',
            prerequis: 'Baccalauréat',
            statut: 'actif' 
        },
        { 
            id: 2, 
            code: 'L2', 
            intitule: 'Licence 2', 
            cycle: 'licence', 
            duree: 1,
            credits_requis: 60,
            description: 'Deuxième année de licence',
            prerequis: 'L1 validée',
            statut: 'actif' 
        },
        { 
            id: 3, 
            code: 'L3', 
            intitule: 'Licence 3', 
            cycle: 'licence', 
            duree: 1,
            credits_requis: 120,
            description: 'Troisième année de licence',
            prerequis: 'L2 validée',
            statut: 'actif' 
        },
        { 
            id: 4, 
            code: 'M1', 
            intitule: 'Master 1', 
            cycle: 'master', 
            duree: 1,
            credits_requis: 180,
            description: 'Première année de master',
            prerequis: 'Licence validée',
            statut: 'actif' 
        },
        { 
            id: 5, 
            code: 'M2', 
            intitule: 'Master 2', 
            cycle: 'master', 
            duree: 1,
            credits_requis: 240,
            description: 'Deuxième année de master',
            prerequis: 'M1 validé',
            statut: 'actif' 
        },
        { 
            id: 6, 
            code: 'D', 
            intitule: 'Doctorat', 
            cycle: 'doctorat', 
            duree: 3,
            credits_requis: 300,
            description: 'Formation doctorale',
            prerequis: 'Master validé',
            statut: 'actif' 
        }
    ];
    
    renderNiveauTable(niveauData);
}

// Afficher les données dans le tableau
function renderNiveauTable(data) {
    const tbody = document.getElementById('niveauTableBody');
    tbody.innerHTML = '';
    
    data.forEach(niveau => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${niveau.code}</td>
            <td>${niveau.intitule}</td>
            <td><span class="badge badge-${niveau.cycle}">${niveau.cycle}</span></td>
            <td>${niveau.duree}</td>
            <td>${niveau.credits_requis || '-'}</td>
            <td><span class="badge ${niveau.statut === 'actif' ? 'status-active' : 'status-closed'}">${niveau.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editNiveau(${niveau.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteNiveau(${niveau.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les niveaux d'étude
function filterNiveaux() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const cycleFilter = document.getElementById('cycleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = niveauData.filter(niveau => {
        const matchesSearch = niveau.code.toLowerCase().includes(searchTerm) || 
                            niveau.intitule.toLowerCase().includes(searchTerm);
        const matchesCycle = !cycleFilter || niveau.cycle === cycleFilter;
        const matchesStatus = !statusFilter || niveau.statut === statusFilter;
        
        return matchesSearch && matchesCycle && matchesStatus;
    });
    
    renderNiveauTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un niveau d\'étude';
    document.getElementById('niveauForm').reset();
    document.getElementById('niveauModal').classList.add('active');
}

// Éditer un niveau d'étude
function editNiveau(id) {
    const niveau = niveauData.find(n => n.id === id);
    if (!niveau) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier le niveau d\'étude';
    document.getElementById('niveauId').value = niveau.id;
    document.getElementById('code').value = niveau.code;
    document.getElementById('intitule').value = niveau.intitule;
    document.getElementById('cycle').value = niveau.cycle;
    document.getElementById('duree').value = niveau.duree;
    document.getElementById('credits_requis').value = niveau.credits_requis || '';
    document.getElementById('description').value = niveau.description || '';
    document.getElementById('prerequis').value = niveau.prerequis || '';
    document.getElementById('statut').value = niveau.statut;
    
    document.getElementById('niveauModal').classList.add('active');
}

// Sauvegarder un niveau d'étude
function saveNiveau() {
    const form = document.getElementById('niveauForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || 
        !formData.get('cycle') || !formData.get('duree')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const niveauData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        cycle: formData.get('cycle'),
        duree: parseInt(formData.get('duree')),
        credits_requis: parseInt(formData.get('credits_requis')) || null,
        description: formData.get('description'),
        prerequis: formData.get('prerequis'),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = niveauData.findIndex(n => n.id === currentEditId);
        if (index !== -1) {
            niveauData[index] = { ...niveauData[index], ...niveauData };
        }
        showNotification('Niveau d\'étude modifié avec succès', 'success');
    } else {
        // Ajout
        niveauData.id = Date.now();
        niveauData.push(niveauData);
        showNotification('Niveau d\'étude ajouté avec succès', 'success');
    }
    
    closeModal();
    renderNiveauTable(niveauData);
}

// Supprimer un niveau d'étude
function deleteNiveau(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce niveau d\'étude ?')) {
        niveauData = niveauData.filter(n => n.id !== id);
        renderNiveauTable(niveauData);
        showNotification('Niveau d\'étude supprimé avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('niveauModal').classList.remove('active');
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
.badge-licence {
    background: #dbeafe;
    color: #1e40af;
}

.badge-master {
    background: #dcfce7;
    color: #166534;
}

.badge-doctorat {
    background: #fef3c7;
    color: #92400e;
}
</style> 