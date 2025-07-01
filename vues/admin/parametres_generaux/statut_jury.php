

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Statuts de Jury</h2>
        <p>Gérer les différents statuts des jurys de soutenance</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un statut
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un statut..." id="searchInput">
    <select class="filter-select" id="couleurFilter">
        <option value="">Toutes les couleurs</option>
        <option value="vert">Vert</option>
        <option value="orange">Orange</option>
        <option value="rouge">Rouge</option>
        <option value="bleu">Bleu</option>
        <option value="gris">Gris</option>
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
                <th>Couleur</th>
                <th>Description</th>
                <th>Ordre</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="statutJuryTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un statut de jury -->
<div class="modal" id="statutJuryModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un statut de jury</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="statutJuryForm">
                <input type="hidden" id="statutJuryId" name="id">
                <div class="form-group">
                    <label for="code">Code *</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="intitule">Intitulé *</label>
                    <input type="text" id="intitule" name="intitule" required>
                </div>
                <div class="form-group">
                    <label for="couleur">Couleur *</label>
                    <select id="couleur" name="couleur" required>
                        <option value="">Sélectionner une couleur</option>
                        <option value="vert">Vert</option>
                        <option value="orange">Orange</option>
                        <option value="rouge">Rouge</option>
                        <option value="bleu">Bleu</option>
                        <option value="gris">Gris</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ordre">Ordre d'affichage</label>
                    <input type="number" id="ordre" name="ordre" min="1" max="10">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="actions_autorisees">Actions autorisées</label>
                    <div class="actions-grid">
                        <label class="checkbox-label">
                            <input type="checkbox" name="actions[]" value="modifier">
                            <span>Modifier</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="actions[]" value="valider">
                            <span>Valider</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="actions[]" value="rejeter">
                            <span>Rejeter</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="actions[]" value="archiver">
                            <span>Archiver</span>
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
            <button class="btn btn-primary" onclick="saveStatutJury()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let statutJuryData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadStatutsJury();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const couleurFilter = document.getElementById('couleurFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterStatutsJury);
    couleurFilter.addEventListener('change', filterStatutsJury);
    statusFilter.addEventListener('change', filterStatutsJury);
}

// Charger les statuts de jury
function loadStatutsJury() {
    // Simulation de données statuts de jury
    statutJuryData = [
        { 
            id: 1, 
            code: 'EN_ATTENTE', 
            intitule: 'En attente', 
            couleur: 'orange',
            description: 'Jury en attente de constitution',
            ordre: 1,
            actions: ['modifier'],
            statut: 'actif' 
        },
        { 
            id: 2, 
            code: 'CONSTITUE', 
            intitule: 'Constitué', 
            couleur: 'bleu',
            description: 'Jury constitué et prêt',
            ordre: 2,
            actions: ['modifier', 'valider'],
            statut: 'actif' 
        },
        { 
            id: 3, 
            code: 'VALIDATION', 
            intitule: 'Validation en cours', 
            couleur: 'vert',
            description: 'Processus de validation en cours',
            ordre: 3,
            actions: ['modifier', 'valider', 'rejeter'],
            statut: 'actif' 
        },
        { 
            id: 4, 
            code: 'VALIDE', 
            intitule: 'Validé', 
            couleur: 'vert',
            description: 'Jury validé et approuvé',
            ordre: 4,
            actions: ['archiver'],
            statut: 'actif' 
        },
        { 
            id: 5, 
            code: 'REJETE', 
            intitule: 'Rejeté', 
            couleur: 'rouge',
            description: 'Jury rejeté',
            ordre: 5,
            actions: ['modifier', 'archiver'],
            statut: 'actif' 
        },
        { 
            id: 6, 
            code: 'ARCHIVE', 
            intitule: 'Archivé', 
            couleur: 'gris',
            description: 'Jury archivé',
            ordre: 6,
            actions: [],
            statut: 'inactif' 
        }
    ];
    
    renderStatutJuryTable(statutJuryData);
}

// Afficher les données dans le tableau
function renderStatutJuryTable(data) {
    const tbody = document.getElementById('statutJuryTableBody');
    tbody.innerHTML = '';
    
    data.forEach(statut => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${statut.code}</td>
            <td>${statut.intitule}</td>
            <td>
                <span class="badge badge-couleur-${statut.couleur}">
                    <i class="fas fa-circle"></i> ${statut.couleur}
                </span>
            </td>
            <td>${statut.description || '-'}</td>
            <td>${statut.ordre}</td>
            <td><span class="badge ${statut.statut === 'actif' ? 'status-active' : 'status-closed'}">${statut.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editStatutJury(${statut.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteStatutJury(${statut.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les statuts de jury
function filterStatutsJury() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const couleurFilter = document.getElementById('couleurFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = statutJuryData.filter(statut => {
        const matchesSearch = statut.code.toLowerCase().includes(searchTerm) || 
                            statut.intitule.toLowerCase().includes(searchTerm) ||
                            (statut.description && statut.description.toLowerCase().includes(searchTerm));
        const matchesCouleur = !couleurFilter || statut.couleur === couleurFilter;
        const matchesStatus = !statusFilter || statut.statut === statusFilter;
        
        return matchesSearch && matchesCouleur && matchesStatus;
    });
    
    renderStatutJuryTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un statut de jury';
    document.getElementById('statutJuryForm').reset();
    document.getElementById('statutJuryModal').classList.add('active');
}

// Éditer un statut de jury
function editStatutJury(id) {
    const statut = statutJuryData.find(s => s.id === id);
    if (!statut) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier le statut de jury';
    document.getElementById('statutJuryId').value = statut.id;
    document.getElementById('code').value = statut.code;
    document.getElementById('intitule').value = statut.intitule;
    document.getElementById('couleur').value = statut.couleur;
    document.getElementById('ordre').value = statut.ordre || '';
    document.getElementById('description').value = statut.description || '';
    document.getElementById('statut').value = statut.statut;
    
    // Cocher les actions existantes
    const checkboxes = document.querySelectorAll('input[name="actions[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = statut.actions.includes(checkbox.value);
    });
    
    document.getElementById('statutJuryModal').classList.add('active');
}

// Sauvegarder un statut de jury
function saveStatutJury() {
    const form = document.getElementById('statutJuryForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || !formData.get('couleur')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Récupérer les actions sélectionnées
    const actions = [];
    const checkboxes = document.querySelectorAll('input[name="actions[]"]:checked');
    checkboxes.forEach(checkbox => {
        actions.push(checkbox.value);
    });
    
    // Simulation de sauvegarde
    const statutJuryData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        couleur: formData.get('couleur'),
        ordre: parseInt(formData.get('ordre')) || null,
        description: formData.get('description'),
        actions: actions,
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = statutJuryData.findIndex(s => s.id === currentEditId);
        if (index !== -1) {
            statutJuryData[index] = { ...statutJuryData[index], ...statutJuryData };
        }
        showNotification('Statut de jury modifié avec succès', 'success');
    } else {
        // Ajout
        statutJuryData.id = Date.now();
        statutJuryData.push(statutJuryData);
        showNotification('Statut de jury ajouté avec succès', 'success');
    }
    
    closeModal();
    renderStatutJuryTable(statutJuryData);
}

// Supprimer un statut de jury
function deleteStatutJury(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce statut de jury ?')) {
        statutJuryData = statutJuryData.filter(s => s.id !== id);
        renderStatutJuryTable(statutJuryData);
        showNotification('Statut de jury supprimé avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('statutJuryModal').classList.remove('active');
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
.badge-couleur-vert {
    background: #dcfce7;
    color: #166534;
}

.badge-couleur-orange {
    background: #fed7aa;
    color: #c2410c;
}

.badge-couleur-rouge {
    background: #fee2e2;
    color: #991b1b;
}

.badge-couleur-bleu {
    background: #dbeafe;
    color: #1e40af;
}

.badge-couleur-gris {
    background: #f1f5f9;
    color: #475569;
}

.actions-grid {
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
</style> 