<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Niveaux d'Approation</h2>
        <p>Gérer les différents niveaux d'approbation pour les rapports et soutenances</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un niveau
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un niveau d'approbation..." id="searchInput">
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
                <th>ID</th>
                <th>Nom du niveau</th>
                <th>Description</th>
                <th>Ordre</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="niveauxTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un niveau -->
<div class="modal" id="niveauModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un niveau d'approbation</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="niveauForm">
                <input type="hidden" id="niveauId" name="id">
                <div class="form-group">
                    <label for="nom">Nom du niveau *</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="ordre">Ordre *</label>
                    <input type="number" id="ordre" name="ordre" min="1" required>
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
let niveauxData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadNiveaux();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterNiveaux);
    statusFilter.addEventListener('change', filterNiveaux);
}

// Charger les niveaux d'approbation
function loadNiveaux() {
    // Simulation de données - à remplacer par un appel API
    niveauxData = [
        { id: 1, nom: 'Validation initiale', description: 'Première validation du rapport', ordre: 1, statut: 'actif' },
        { id: 2, nom: 'Validation du directeur', description: 'Validation par le directeur de mémoire', ordre: 2, statut: 'actif' },
        { id: 3, nom: 'Validation du jury', description: 'Validation finale par le jury', ordre: 3, statut: 'actif' }
    ];
    
    renderNiveauxTable(niveauxData);
}

// Afficher les données dans le tableau
function renderNiveauxTable(data) {
    const tbody = document.getElementById('niveauxTableBody');
    tbody.innerHTML = '';
    
    data.forEach(niveau => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${niveau.id}</td>
            <td>${niveau.nom}</td>
            <td>${niveau.description || '-'}</td>
            <td>${niveau.ordre}</td>
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

// Filtrer les niveaux
function filterNiveaux() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = niveauxData.filter(niveau => {
        const matchesSearch = niveau.nom.toLowerCase().includes(searchTerm) || 
                            (niveau.description && niveau.description.toLowerCase().includes(searchTerm));
        const matchesStatus = !statusFilter || niveau.statut === statusFilter;
        
        return matchesSearch && matchesStatus;
    });
    
    renderNiveauxTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un niveau d\'approbation';
    document.getElementById('niveauForm').reset();
    document.getElementById('niveauModal').classList.add('active');
}

// Éditer un niveau
function editNiveau(id) {
    const niveau = niveauxData.find(n => n.id === id);
    if (!niveau) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier le niveau d\'approbation';
    document.getElementById('niveauId').value = niveau.id;
    document.getElementById('nom').value = niveau.nom;
    document.getElementById('description').value = niveau.description || '';
    document.getElementById('ordre').value = niveau.ordre;
    document.getElementById('statut').value = niveau.statut;
    
    document.getElementById('niveauModal').classList.add('active');
}

// Sauvegarder un niveau
function saveNiveau() {
    const form = document.getElementById('niveauForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('nom') || !formData.get('ordre')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde - à remplacer par un appel API
    const niveauData = {
        nom: formData.get('nom'),
        description: formData.get('description'),
        ordre: parseInt(formData.get('ordre')),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = niveauxData.findIndex(n => n.id === currentEditId);
        if (index !== -1) {
            niveauxData[index] = { ...niveauxData[index], ...niveauData };
        }
        showNotification('Niveau d\'approbation modifié avec succès', 'success');
    } else {
        // Ajout
        niveauData.id = Date.now(); // Simulation d'ID
        niveauxData.push(niveauData);
        showNotification('Niveau d\'approbation ajouté avec succès', 'success');
    }
    
    closeModal();
    renderNiveauxTable(niveauxData);
}

// Supprimer un niveau
function deleteNiveau(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce niveau d\'approbation ?')) {
        niveauxData = niveauxData.filter(n => n.id !== id);
        renderNiveauxTable(niveauxData);
        showNotification('Niveau d\'approbation supprimé avec succès', 'success');
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
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>