
<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Groupes d'Utilisateurs</h2>
        <p>Gérer les groupes et leurs permissions</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un groupe
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un groupe..." id="searchInput">
    <select class="filter-select" id="typeFilter">
        <option value="">Tous les types</option>
        <option value="academique">Académique</option>
        <option value="administrative">Administrative</option>
        <option value="etudiant">Étudiant</option>
        <option value="externe">Externe</option>
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
                <th>Nom du groupe</th>
                <th>Type</th>
                <th>Description</th>
                <th>Membres</th>
                <th>Permissions</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="groupeTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un groupe -->
<div class="modal" id="groupeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un groupe</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="groupeForm">
                <input type="hidden" id="groupeId" name="id">
                <div class="form-group">
                    <label for="nom">Nom du groupe *</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="type">Type *</label>
                    <select id="type" name="type" required>
                        <option value="">Sélectionner un type</option>
                        <option value="academique">Académique</option>
                        <option value="administrative">Administrative</option>
                        <option value="etudiant">Étudiant</option>
                        <option value="externe">Externe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Permissions</label>
                    <div class="permissions-grid">
                        <label class="checkbox-label">
                            <input type="checkbox" name="permissions[]" value="lecture">
                            <span>Lecture</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="permissions[]" value="ecriture">
                            <span>Écriture</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="permissions[]" value="modification">
                            <span>Modification</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="permissions[]" value="suppression">
                            <span>Suppression</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="permissions[]" value="administration">
                            <span>Administration</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="permissions[]" value="validation">
                            <span>Validation</span>
                        </label>
                    </div>
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
            <button class="btn btn-primary" onclick="saveGroupe()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let groupeData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadGroupes();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterGroupes);
    typeFilter.addEventListener('change', filterGroupes);
    statusFilter.addEventListener('change', filterGroupes);
}

// Charger les groupes
function loadGroupes() {
    // Simulation de données groupes
    groupeData = [
        { 
            id: 1, 
            nom: 'Administrateurs système', 
            type: 'administrative',
            description: 'Groupe des administrateurs système',
            membres: 3,
            permissions: ['lecture', 'ecriture', 'modification', 'suppression', 'administration'],
            statut: 'actif' 
        },
        { 
            id: 2, 
            nom: 'Enseignants', 
            type: 'academique',
            description: 'Groupe des enseignants-chercheurs',
            membres: 25,
            permissions: ['lecture', 'ecriture', 'modification', 'validation'],
            statut: 'actif' 
        },
        { 
            id: 3, 
            nom: 'Étudiants L3', 
            type: 'etudiant',
            description: 'Groupe des étudiants de Licence 3',
            membres: 120,
            permissions: ['lecture', 'ecriture'],
            statut: 'actif' 
        },
        { 
            id: 4, 
            nom: 'Secrétaires pédagogiques', 
            type: 'administrative',
            description: 'Groupe des secrétaires pédagogiques',
            membres: 8,
            permissions: ['lecture', 'ecriture', 'modification'],
            statut: 'actif' 
        },
        { 
            id: 5, 
            nom: 'Partenaires externes', 
            type: 'externe',
            description: 'Groupe des partenaires externes',
            membres: 15,
            permissions: ['lecture'],
            statut: 'inactif' 
        }
    ];
    
    renderGroupeTable(groupeData);
}

// Afficher les données dans le tableau
function renderGroupeTable(data) {
    const tbody = document.getElementById('groupeTableBody');
    tbody.innerHTML = '';
    
    data.forEach(groupe => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${groupe.nom}</td>
            <td><span class="badge badge-${groupe.type}">${groupe.type}</span></td>
            <td>${groupe.description || '-'}</td>
            <td><span class="member-count">${groupe.membres} membres</span></td>
            <td>
                <div class="permissions-badges">
                    ${groupe.permissions.map(perm => `<span class="permission-badge">${perm}</span>`).join('')}
                </div>
            </td>
            <td><span class="badge ${groupe.statut === 'actif' ? 'status-active' : 'status-closed'}">${groupe.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editGroupe(${groupe.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteGroupe(${groupe.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les groupes
function filterGroupes() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = groupeData.filter(groupe => {
        const matchesSearch = groupe.nom.toLowerCase().includes(searchTerm) || 
                            (groupe.description && groupe.description.toLowerCase().includes(searchTerm));
        const matchesType = !typeFilter || groupe.type === typeFilter;
        const matchesStatus = !statusFilter || groupe.statut === statusFilter;
        
        return matchesSearch && matchesType && matchesStatus;
    });
    
    renderGroupeTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un groupe';
    document.getElementById('groupeForm').reset();
    document.getElementById('groupeModal').classList.add('active');
}

// Éditer un groupe
function editGroupe(id) {
    const groupe = groupeData.find(g => g.id === id);
    if (!groupe) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier le groupe';
    document.getElementById('groupeId').value = groupe.id;
    document.getElementById('nom').value = groupe.nom;
    document.getElementById('type').value = groupe.type;
    document.getElementById('description').value = groupe.description || '';
    document.getElementById('statut').value = groupe.statut;
    
    // Cocher les permissions existantes
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = groupe.permissions.includes(checkbox.value);
    });
    
    document.getElementById('groupeModal').classList.add('active');
}

// Sauvegarder un groupe
function saveGroupe() {
    const form = document.getElementById('groupeForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('nom') || !formData.get('type')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Récupérer les permissions sélectionnées
    const permissions = [];
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]:checked');
    checkboxes.forEach(checkbox => {
        permissions.push(checkbox.value);
    });
    
    // Simulation de sauvegarde
    const groupeData = {
        nom: formData.get('nom'),
        type: formData.get('type'),
        description: formData.get('description'),
        permissions: permissions,
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = groupeData.findIndex(g => g.id === currentEditId);
        if (index !== -1) {
            groupeData[index] = { ...groupeData[index], ...groupeData };
        }
        showNotification('Groupe modifié avec succès', 'success');
    } else {
        // Ajout
        groupeData.id = Date.now();
        groupeData.membres = 0; // Nouveau groupe sans membres
        groupeData.push(groupeData);
        showNotification('Groupe ajouté avec succès', 'success');
    }
    
    closeModal();
    renderGroupeTable(groupeData);
}

// Supprimer un groupe
function deleteGroupe(id) {
    const groupe = groupeData.find(g => g.id === id);
    if (groupe && groupe.membres > 0) {
        if (!confirm(`Ce groupe contient ${groupe.membres} membres. Êtes-vous sûr de vouloir le supprimer ?`)) {
            return;
        }
    } else if (!confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?')) {
        return;
    }
    
    groupeData = groupeData.filter(g => g.id !== id);
    renderGroupeTable(groupeData);
    showNotification('Groupe supprimé avec succès', 'success');
}

// Fermer le modal
function closeModal() {
    document.getElementById('groupeModal').classList.remove('active');
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
.badge-academique {
    background: #dbeafe;
    color: #1e40af;
}

.badge-administrative {
    background: #dcfce7;
    color: #166534;
}

.badge-etudiant {
    background: #fef3c7;
    color: #92400e;
}

.badge-externe {
    background: #f3e8ff;
    color: #7c3aed;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    background: var(--gray-50);
    transition: var(--transition);
}

.checkbox-label:hover {
    background: var(--gray-100);
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.permissions-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.permission-badge {
    background: var(--primary-light);
    color: var(--primary-color);
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.member-count {
    font-weight: 500;
    color: var(--primary-color);
}
</style> 