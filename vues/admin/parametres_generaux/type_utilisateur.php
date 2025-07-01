

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Types d'Utilisateurs</h2>
        <p>Gérer les différents types d'utilisateurs du système</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un type
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un type..." id="searchInput">
    <select class="filter-select" id="categorieFilter">
        <option value="">Toutes les catégories</option>
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
                <th>Code</th>
                <th>Intitulé</th>
                <th>Catégorie</th>
                <th>Description</th>
                <th>Permissions</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="typeTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un type d'utilisateur -->
<div class="modal" id="typeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un type d'utilisateur</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="typeForm">
                <input type="hidden" id="typeId" name="id">
                <div class="form-group">
                    <label for="code">Code *</label>
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
            <button class="btn btn-primary" onclick="saveType()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let typeData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadTypes();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const categorieFilter = document.getElementById('categorieFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterTypes);
    categorieFilter.addEventListener('change', filterTypes);
    statusFilter.addEventListener('change', filterTypes);
}

// Charger les types d'utilisateurs
function loadTypes() {
    // Simulation de données types d'utilisateurs
    typeData = [
        { 
            id: 1, 
            code: 'ADMIN', 
            intitule: 'Administrateur', 
            categorie: 'administrative',
            description: 'Accès complet au système',
            permissions: ['lecture', 'ecriture', 'modification', 'suppression', 'administration'],
            statut: 'actif' 
        },
        { 
            id: 2, 
            code: 'PROF', 
            intitule: 'Professeur', 
            categorie: 'academique',
            description: 'Enseignant-chercheur',
            permissions: ['lecture', 'ecriture', 'modification', 'validation'],
            statut: 'actif' 
        },
        { 
            id: 3, 
            code: 'ETUD', 
            intitule: 'Étudiant', 
            categorie: 'etudiant',
            description: 'Étudiant inscrit',
            permissions: ['lecture', 'ecriture'],
            statut: 'actif' 
        },
        { 
            id: 4, 
            code: 'SEC', 
            intitule: 'Secrétaire', 
            categorie: 'administrative',
            description: 'Personnel administratif',
            permissions: ['lecture', 'ecriture', 'modification'],
            statut: 'actif' 
        },
        { 
            id: 5, 
            code: 'EXT', 
            intitule: 'Utilisateur externe', 
            categorie: 'externe',
            description: 'Partenaire ou intervenant externe',
            permissions: ['lecture'],
            statut: 'inactif' 
        }
    ];
    
    renderTypeTable(typeData);
}

// Afficher les données dans le tableau
function renderTypeTable(data) {
    const tbody = document.getElementById('typeTableBody');
    tbody.innerHTML = '';
    
    data.forEach(type => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${type.code}</td>
            <td>${type.intitule}</td>
            <td><span class="badge badge-${type.categorie}">${type.categorie}</span></td>
            <td>${type.description || '-'}</td>
            <td>
                <div class="permissions-badges">
                    ${type.permissions.map(perm => `<span class="permission-badge">${perm}</span>`).join('')}
                </div>
            </td>
            <td><span class="badge ${type.statut === 'actif' ? 'status-active' : 'status-closed'}">${type.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editType(${type.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteType(${type.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les types d'utilisateurs
function filterTypes() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categorieFilter = document.getElementById('categorieFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = typeData.filter(type => {
        const matchesSearch = type.code.toLowerCase().includes(searchTerm) || 
                            type.intitule.toLowerCase().includes(searchTerm);
        const matchesCategorie = !categorieFilter || type.categorie === categorieFilter;
        const matchesStatus = !statusFilter || type.statut === statusFilter;
        
        return matchesSearch && matchesCategorie && matchesStatus;
    });
    
    renderTypeTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un type d\'utilisateur';
    document.getElementById('typeForm').reset();
    document.getElementById('typeModal').classList.add('active');
}

// Éditer un type d'utilisateur
function editType(id) {
    const type = typeData.find(t => t.id === id);
    if (!type) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier le type d\'utilisateur';
    document.getElementById('typeId').value = type.id;
    document.getElementById('code').value = type.code;
    document.getElementById('intitule').value = type.intitule;
    document.getElementById('categorie').value = type.categorie;
    document.getElementById('description').value = type.description || '';
    document.getElementById('statut').value = type.statut;
    
    // Cocher les permissions existantes
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = type.permissions.includes(checkbox.value);
    });
    
    document.getElementById('typeModal').classList.add('active');
}

// Sauvegarder un type d'utilisateur
function saveType() {
    const form = document.getElementById('typeForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || !formData.get('categorie')) {
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
    const typeData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        categorie: formData.get('categorie'),
        description: formData.get('description'),
        permissions: permissions,
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = typeData.findIndex(t => t.id === currentEditId);
        if (index !== -1) {
            typeData[index] = { ...typeData[index], ...typeData };
        }
        showNotification('Type d\'utilisateur modifié avec succès', 'success');
    } else {
        // Ajout
        typeData.id = Date.now();
        typeData.push(typeData);
        showNotification('Type d\'utilisateur ajouté avec succès', 'success');
    }
    
    closeModal();
    renderTypeTable(typeData);
}

// Supprimer un type d'utilisateur
function deleteType(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce type d\'utilisateur ?')) {
        typeData = typeData.filter(t => t.id !== id);
        renderTypeTable(typeData);
        showNotification('Type d\'utilisateur supprimé avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('typeModal').classList.remove('active');
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
</style> 