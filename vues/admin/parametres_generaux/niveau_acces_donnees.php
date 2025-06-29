
<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Niveaux d'Accès aux Données</h2>
        <p>Gérer les niveaux de sécurité et d'accès aux données</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un niveau
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un niveau..." id="searchInput">
    <select class="filter-select" id="securiteFilter">
        <option value="">Tous les niveaux de sécurité</option>
        <option value="public">Public</option>
        <option value="interne">Interne</option>
        <option value="confidentiel">Confidentiel</option>
        <option value="secret">Secret</option>
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
                <th>Niveau de sécurité</th>
                <th>Description</th>
                <th>Permissions</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="niveauAccesTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un niveau d'accès -->
<div class="modal" id="niveauAccesModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un niveau d'accès</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="niveauAccesForm">
                <input type="hidden" id="niveauAccesId" name="id">
                <div class="form-group">
                    <label for="code">Code *</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="intitule">Intitulé *</label>
                    <input type="text" id="intitule" name="intitule" required>
                </div>
                <div class="form-group">
                    <label for="niveau_securite">Niveau de sécurité *</label>
                    <select id="niveau_securite" name="niveau_securite" required>
                        <option value="">Sélectionner un niveau</option>
                        <option value="public">Public</option>
                        <option value="interne">Interne</option>
                        <option value="confidentiel">Confidentiel</option>
                        <option value="secret">Secret</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Permissions d'accès</label>
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
                            <input type="checkbox" name="permissions[]" value="export">
                            <span>Export</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="permissions[]" value="impression">
                            <span>Impression</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="restrictions">Restrictions</label>
                    <textarea id="restrictions" name="restrictions" rows="2"></textarea>
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
            <button class="btn btn-primary" onclick="saveNiveauAcces()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let niveauAccesData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadNiveauxAcces();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const securiteFilter = document.getElementById('securiteFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterNiveauxAcces);
    securiteFilter.addEventListener('change', filterNiveauxAcces);
    statusFilter.addEventListener('change', filterNiveauxAcces);
}

// Charger les niveaux d'accès
function loadNiveauxAcces() {
    // Simulation de données niveaux d'accès
    niveauAccesData = [
        { 
            id: 1, 
            code: 'PUBLIC', 
            intitule: 'Accès public', 
            niveau_securite: 'public',
            description: 'Données accessibles à tous les utilisateurs',
            permissions: ['lecture'],
            restrictions: 'Aucune restriction',
            statut: 'actif' 
        },
        { 
            id: 2, 
            code: 'INTERNE', 
            intitule: 'Accès interne', 
            niveau_securite: 'interne',
            description: 'Données accessibles au personnel interne',
            permissions: ['lecture', 'ecriture', 'modification'],
            restrictions: 'Accès limité au personnel autorisé',
            statut: 'actif' 
        },
        { 
            id: 3, 
            code: 'CONFIDENTIEL', 
            intitule: 'Accès confidentiel', 
            niveau_securite: 'confidentiel',
            description: 'Données confidentielles',
            permissions: ['lecture', 'ecriture', 'modification', 'export'],
            restrictions: 'Accès strictement contrôlé',
            statut: 'actif' 
        },
        { 
            id: 4, 
            code: 'SECRET', 
            intitule: 'Accès secret', 
            niveau_securite: 'secret',
            description: 'Données hautement sensibles',
            permissions: ['lecture', 'ecriture', 'modification', 'suppression', 'export'],
            restrictions: 'Accès très restreint, audit obligatoire',
            statut: 'actif' 
        },
        { 
            id: 5, 
            code: 'ARCHIVE', 
            intitule: 'Accès archivé', 
            niveau_securite: 'interne',
            description: 'Données archivées en lecture seule',
            permissions: ['lecture'],
            restrictions: 'Lecture seule, pas de modification',
            statut: 'inactif' 
        }
    ];
    
    renderNiveauAccesTable(niveauAccesData);
}

// Afficher les données dans le tableau
function renderNiveauAccesTable(data) {
    const tbody = document.getElementById('niveauAccesTableBody');
    tbody.innerHTML = '';
    
    data.forEach(niveau => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${niveau.code}</td>
            <td>${niveau.intitule}</td>
            <td>
                <span class="badge badge-securite-${niveau.niveau_securite}">
                    <i class="fas fa-shield-alt"></i> ${niveau.niveau_securite}
                </span>
            </td>
            <td>${niveau.description || '-'}</td>
            <td>
                <div class="permissions-badges">
                    ${niveau.permissions.map(perm => `<span class="permission-badge">${perm}</span>`).join('')}
                </div>
            </td>
            <td><span class="badge ${niveau.statut === 'actif' ? 'status-active' : 'status-closed'}">${niveau.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editNiveauAcces(${niveau.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteNiveauAcces(${niveau.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les niveaux d'accès
function filterNiveauxAcces() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const securiteFilter = document.getElementById('securiteFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = niveauAccesData.filter(niveau => {
        const matchesSearch = niveau.code.toLowerCase().includes(searchTerm) || 
                            niveau.intitule.toLowerCase().includes(searchTerm) ||
                            (niveau.description && niveau.description.toLowerCase().includes(searchTerm));
        const matchesSecurite = !securiteFilter || niveau.niveau_securite === securiteFilter;
        const matchesStatus = !statusFilter || niveau.statut === statusFilter;
        
        return matchesSearch && matchesSecurite && matchesStatus;
    });
    
    renderNiveauAccesTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un niveau d\'accès';
    document.getElementById('niveauAccesForm').reset();
    document.getElementById('niveauAccesModal').classList.add('active');
}

// Éditer un niveau d'accès
function editNiveauAcces(id) {
    const niveau = niveauAccesData.find(n => n.id === id);
    if (!niveau) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier le niveau d\'accès';
    document.getElementById('niveauAccesId').value = niveau.id;
    document.getElementById('code').value = niveau.code;
    document.getElementById('intitule').value = niveau.intitule;
    document.getElementById('niveau_securite').value = niveau.niveau_securite;
    document.getElementById('description').value = niveau.description || '';
    document.getElementById('restrictions').value = niveau.restrictions || '';
    document.getElementById('statut').value = niveau.statut;
    
    // Cocher les permissions existantes
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = niveau.permissions.includes(checkbox.value);
    });
    
    document.getElementById('niveauAccesModal').classList.add('active');
}

// Sauvegarder un niveau d'accès
function saveNiveauAcces() {
    const form = document.getElementById('niveauAccesForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || !formData.get('niveau_securite')) {
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
    const niveauAccesData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        niveau_securite: formData.get('niveau_securite'),
        description: formData.get('description'),
        permissions: permissions,
        restrictions: formData.get('restrictions'),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = niveauAccesData.findIndex(n => n.id === currentEditId);
        if (index !== -1) {
            niveauAccesData[index] = { ...niveauAccesData[index], ...niveauAccesData };
        }
        showNotification('Niveau d\'accès modifié avec succès', 'success');
    } else {
        // Ajout
        niveauAccesData.id = Date.now();
        niveauAccesData.push(niveauAccesData);
        showNotification('Niveau d\'accès ajouté avec succès', 'success');
    }
    
    closeModal();
    renderNiveauAccesTable(niveauAccesData);
}

// Supprimer un niveau d'accès
function deleteNiveauAcces(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce niveau d\'accès ?')) {
        niveauAccesData = niveauAccesData.filter(n => n.id !== id);
        renderNiveauAccesTable(niveauAccesData);
        showNotification('Niveau d\'accès supprimé avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('niveauAccesModal').classList.remove('active');
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
.badge-securite-public {
    background: #dcfce7;
    color: #166534;
}

.badge-securite-interne {
    background: #dbeafe;
    color: #1e40af;
}

.badge-securite-confidentiel {
    background: #fef3c7;
    color: #92400e;
}

.badge-securite-secret {
    background: #fee2e2;
    color: #991b1b;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
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