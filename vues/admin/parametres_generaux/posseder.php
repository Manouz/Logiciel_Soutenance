

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Relations "Posséder"</h2>
        <p>Gérer les attributions de permissions et de rôles</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter une relation
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher..." id="searchInput">
    <select class="filter-select" id="typeFilter">
        <option value="">Tous les types</option>
        <option value="utilisateur">Utilisateur</option>
        <option value="groupe">Groupe</option>
        <option value="role">Rôle</option>
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
                <th>Entité source</th>
                <th>Type source</th>
                <th>Entité cible</th>
                <th>Type cible</th>
                <th>Date attribution</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="possederTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier une relation -->
<div class="modal" id="possederModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter une relation</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="possederForm">
                <input type="hidden" id="possederId" name="id">
                <div class="form-group">
                    <label for="entite_source">Entité source *</label>
                    <select id="entite_source" name="entite_source" required>
                        <option value="">Sélectionner une entité source</option>
                        <!-- Options chargées dynamiquement -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="type_source">Type source *</label>
                    <select id="type_source" name="type_source" required>
                        <option value="">Sélectionner un type</option>
                        <option value="utilisateur">Utilisateur</option>
                        <option value="groupe">Groupe</option>
                        <option value="role">Rôle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="entite_cible">Entité cible *</label>
                    <select id="entite_cible" name="entite_cible" required>
                        <option value="">Sélectionner une entité cible</option>
                        <!-- Options chargées dynamiquement -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="type_cible">Type cible *</label>
                    <select id="type_cible" name="type_cible" required>
                        <option value="">Sélectionner un type</option>
                        <option value="permission">Permission</option>
                        <option value="role">Rôle</option>
                        <option value="groupe">Groupe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_attribution">Date d'attribution</label>
                    <input type="date" id="date_attribution" name="date_attribution">
                </div>
                <div class="form-group">
                    <label for="commentaire">Commentaire</label>
                    <textarea id="commentaire" name="commentaire" rows="3"></textarea>
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
            <button class="btn btn-primary" onclick="savePosseder()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let possederData = [];
let currentEditId = null;
let entitesData = {
    utilisateurs: [],
    groupes: [],
    roles: [],
    permissions: []
};

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadEntites();
    loadPosseder();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterPosseder);
    typeFilter.addEventListener('change', filterPosseder);
    statusFilter.addEventListener('change', filterPosseder);
    
    // Événements pour les changements de type
    document.getElementById('type_source').addEventListener('change', updateEntiteSourceOptions);
    document.getElementById('type_cible').addEventListener('change', updateEntiteCibleOptions);
}

// Charger les entités
function loadEntites() {
    // Simulation de données entités
    entitesData.utilisateurs = [
        { id: 1, nom: 'Jean Dupont', email: 'jean.dupont@example.com' },
        { id: 2, nom: 'Marie Martin', email: 'marie.martin@example.com' },
        { id: 3, nom: 'Pierre Durand', email: 'pierre.durand@example.com' }
    ];
    
    entitesData.groupes = [
        { id: 1, nom: 'Administrateurs système' },
        { id: 2, nom: 'Enseignants' },
        { id: 3, nom: 'Étudiants L3' }
    ];
    
    entitesData.roles = [
        { id: 1, nom: 'Administrateur' },
        { id: 2, nom: 'Professeur' },
        { id: 3, nom: 'Étudiant' }
    ];
    
    entitesData.permissions = [
        { id: 1, nom: 'Lecture' },
        { id: 2, nom: 'Écriture' },
        { id: 3, nom: 'Modification' },
        { id: 4, nom: 'Suppression' }
    ];
}

// Charger les relations "Posséder"
function loadPosseder() {
    // Simulation de données relations
    possederData = [
        { 
            id: 1, 
            entite_source: 1, 
            type_source: 'utilisateur',
            entite_cible: 1, 
            type_cible: 'role',
            date_attribution: '2024-01-15',
            commentaire: 'Attribution initiale',
            statut: 'actif' 
        },
        { 
            id: 2, 
            entite_source: 2, 
            type_source: 'utilisateur',
            entite_cible: 2, 
            type_cible: 'role',
            date_attribution: '2024-01-20',
            commentaire: 'Nouveau professeur',
            statut: 'actif' 
        },
        { 
            id: 3, 
            entite_source: 1, 
            type_source: 'groupe',
            entite_cible: 1, 
            type_cible: 'permission',
            date_attribution: '2024-01-10',
            commentaire: 'Permissions administrateur',
            statut: 'actif' 
        },
        { 
            id: 4, 
            entite_source: 3, 
            type_source: 'groupe',
            entite_cible: 3, 
            type_cible: 'role',
            date_attribution: '2024-02-01',
            commentaire: 'Rôle étudiant pour le groupe',
            statut: 'inactif' 
        }
    ];
    
    renderPossederTable(possederData);
}

// Afficher les données dans le tableau
function renderPossederTable(data) {
    const tbody = document.getElementById('possederTableBody');
    tbody.innerHTML = '';
    
    data.forEach(relation => {
        const sourceEntite = getEntiteById(relation.entite_source, relation.type_source);
        const cibleEntite = getEntiteById(relation.entite_cible, relation.type_cible);
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${sourceEntite ? sourceEntite.nom : 'N/A'}</td>
            <td><span class="badge badge-${relation.type_source}">${relation.type_source}</span></td>
            <td>${cibleEntite ? cibleEntite.nom : 'N/A'}</td>
            <td><span class="badge badge-${relation.type_cible}">${relation.type_cible}</span></td>
            <td>${formatDate(relation.date_attribution)}</td>
            <td><span class="badge ${relation.statut === 'actif' ? 'status-active' : 'status-closed'}">${relation.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editPosseder(${relation.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deletePosseder(${relation.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Obtenir une entité par ID et type
function getEntiteById(id, type) {
    const entites = entitesData[type + 's'] || [];
    return entites.find(e => e.id === id);
}

// Formater une date
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

// Filtrer les relations
function filterPosseder() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = possederData.filter(relation => {
        const sourceEntite = getEntiteById(relation.entite_source, relation.type_source);
        const cibleEntite = getEntiteById(relation.entite_cible, relation.type_cible);
        
        const matchesSearch = (sourceEntite && sourceEntite.nom.toLowerCase().includes(searchTerm)) || 
                            (cibleEntite && cibleEntite.nom.toLowerCase().includes(searchTerm));
        const matchesType = !typeFilter || relation.type_source === typeFilter || relation.type_cible === typeFilter;
        const matchesStatus = !statusFilter || relation.statut === statusFilter;
        
        return matchesSearch && matchesType && matchesStatus;
    });
    
    renderPossederTable(filteredData);
}

// Mettre à jour les options de l'entité source
function updateEntiteSourceOptions() {
    const typeSource = document.getElementById('type_source').value;
    const select = document.getElementById('entite_source');
    
    select.innerHTML = '<option value="">Sélectionner une entité source</option>';
    
    if (typeSource) {
        const entites = entitesData[typeSource + 's'] || [];
        entites.forEach(entite => {
            const option = document.createElement('option');
            option.value = entite.id;
            option.textContent = entite.nom;
            select.appendChild(option);
        });
    }
}

// Mettre à jour les options de l'entité cible
function updateEntiteCibleOptions() {
    const typeCible = document.getElementById('type_cible').value;
    const select = document.getElementById('entite_cible');
    
    select.innerHTML = '<option value="">Sélectionner une entité cible</option>';
    
    if (typeCible) {
        const entites = entitesData[typeCible + 's'] || [];
        entites.forEach(entite => {
            const option = document.createElement('option');
            option.value = entite.id;
            option.textContent = entite.nom;
            select.appendChild(option);
        });
    }
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter une relation';
    document.getElementById('possederForm').reset();
    document.getElementById('date_attribution').value = new Date().toISOString().split('T')[0];
    document.getElementById('possederModal').classList.add('active');
}

// Éditer une relation
function editPosseder(id) {
    const relation = possederData.find(r => r.id === id);
    if (!relation) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier la relation';
    document.getElementById('possederId').value = relation.id;
    
    // Mettre à jour les options avant de définir les valeurs
    document.getElementById('type_source').value = relation.type_source;
    updateEntiteSourceOptions();
    document.getElementById('entite_source').value = relation.entite_source;
    
    document.getElementById('type_cible').value = relation.type_cible;
    updateEntiteCibleOptions();
    document.getElementById('entite_cible').value = relation.entite_cible;
    
    document.getElementById('date_attribution').value = relation.date_attribution || '';
    document.getElementById('commentaire').value = relation.commentaire || '';
    document.getElementById('statut').value = relation.statut;
    
    document.getElementById('possederModal').classList.add('active');
}

// Sauvegarder une relation
function savePosseder() {
    const form = document.getElementById('possederForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('entite_source') || !formData.get('type_source') || 
        !formData.get('entite_cible') || !formData.get('type_cible')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const possederData = {
        entite_source: parseInt(formData.get('entite_source')),
        type_source: formData.get('type_source'),
        entite_cible: parseInt(formData.get('entite_cible')),
        type_cible: formData.get('type_cible'),
        date_attribution: formData.get('date_attribution'),
        commentaire: formData.get('commentaire'),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = possederData.findIndex(r => r.id === currentEditId);
        if (index !== -1) {
            possederData[index] = { ...possederData[index], ...possederData };
        }
        showNotification('Relation modifiée avec succès', 'success');
    } else {
        // Ajout
        possederData.id = Date.now();
        possederData.push(possederData);
        showNotification('Relation ajoutée avec succès', 'success');
    }
    
    closeModal();
    renderPossederTable(possederData);
}

// Supprimer une relation
function deletePosseder(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette relation ?')) {
        possederData = possederData.filter(r => r.id !== id);
        renderPossederTable(possederData);
        showNotification('Relation supprimée avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('possederModal').classList.remove('active');
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
.badge-utilisateur {
    background: #dbeafe;
    color: #1e40af;
}

.badge-groupe {
    background: #dcfce7;
    color: #166534;
}

.badge-role {
    background: #fef3c7;
    color: #92400e;
}

.badge-permission {
    background: #f3e8ff;
    color: #7c3aed;
}
</style> 