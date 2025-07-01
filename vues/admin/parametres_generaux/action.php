<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
</head>
<style>
.badge-creation {
    background: #dcfce7;
    color: #166534;
}

.badge-modification {
    background: #dbeafe;
    color: #1e40af;
}

.badge-suppression {
    background: #fee2e2;
    color: #991b1b;
}

.badge-validation {
    background: #fef3c7;
    color: #92400e;
}

.badge-consultation {
    background: #f3e8ff;
    color: #7c3aed;
}

.badge-export {
    background: #ecfdf5;
    color: #065f46;
}

.badge-import {
    background: #fef2f2;
    color: #991b1b;
}

.badge-module-utilisateurs {
    background: #dbeafe;
    color: #1e40af;
}

.badge-module-etudiants {
    background: #dcfce7;
    color: #166534;
}

.badge-module-enseignants {
    background: #fef3c7;
    color: #92400e;
}

.badge-module-rapports {
    background: #f3e8ff;
    color: #7c3aed;
}

.badge-module-soutenances {
    background: #ecfdf5;
    color: #065f46;
}

.badge-module-jury {
    background: #fef2f2;
    color: #991b1b;
}

.badge-module-administration {
    background: #f1f5f9;
    color: #475569;
}
</style> 
<body>
    <div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Actions</h2>
        <p>Gérer les actions et opérations du système</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter une action
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher une action..." id="searchInput">
    <select class="filter-select" id="categorieFilter">
        <option value="">Toutes les catégories</option>
        <option value="creation">Création</option>
        <option value="modification">Modification</option>
        <option value="suppression">Suppression</option>
        <option value="validation">Validation</option>
        <option value="consultation">Consultation</option>
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
                <th>Module</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="actionTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier une action -->
<div class="modal" id="actionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter une action</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="actionForm">
                <input type="hidden" id="actionId" name="id">
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
                        <option value="creation">Création</option>
                        <option value="modification">Modification</option>
                        <option value="suppression">Suppression</option>
                        <option value="validation">Validation</option>
                        <option value="consultation">Consultation</option>
                        <option value="export">Export</option>
                        <option value="import">Import</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="module">Module *</label>
                    <select id="module" name="module" required>
                        <option value="">Sélectionner un module</option>
                        <option value="utilisateurs">Utilisateurs</option>
                        <option value="etudiants">Étudiants</option>
                        <option value="enseignants">Enseignants</option>
                        <option value="rapports">Rapports</option>
                        <option value="soutenances">Soutenances</option>
                        <option value="jury">Jury</option>
                        <option value="administration">Administration</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="url_action">URL de l'action</label>
                    <input type="text" id="url_action" name="url_action" placeholder="/api/action">
                </div>
                <div class="form-group">
                    <label for="parametres">Paramètres requis</label>
                    <textarea id="parametres" name="parametres" rows="2" placeholder="param1,param2,param3"></textarea>
                </div>
                <div class="form-group">
                    <label for="niveau_securite">Niveau de sécurité requis</label>
                    <select id="niveau_securite" name="niveau_securite">
                        <option value="">Aucun niveau spécifique</option>
                        <option value="public">Public</option>
                        <option value="interne">Interne</option>
                        <option value="confidentiel">Confidentiel</option>
                        <option value="secret">Secret</option>
                    </select>
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
            <button class="btn btn-primary" onclick="saveAction()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let actionData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadActions();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const categorieFilter = document.getElementById('categorieFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterActions);
    categorieFilter.addEventListener('change', filterActions);
    statusFilter.addEventListener('change', filterActions);
}

// Charger les actions
function loadActions() {
    // Simulation de données actions
    actionData = [
        { 
            id: 1, 
            code: 'CREATE_USER', 
            intitule: 'Créer un utilisateur', 
            categorie: 'creation',
            module: 'utilisateurs',
            description: 'Créer un nouvel utilisateur dans le système',
            url_action: '/api/users/create',
            parametres: 'nom,email,role',
            niveau_securite: 'confidentiel',
            statut: 'actif' 
        },
        { 
            id: 2, 
            code: 'UPDATE_USER', 
            intitule: 'Modifier un utilisateur', 
            categorie: 'modification',
            module: 'utilisateurs',
            description: 'Modifier les informations d\'un utilisateur',
            url_action: '/api/users/update',
            parametres: 'id,nom,email',
            niveau_securite: 'confidentiel',
            statut: 'actif' 
        },
        { 
            id: 3, 
            code: 'DELETE_USER', 
            intitule: 'Supprimer un utilisateur', 
            categorie: 'suppression',
            module: 'utilisateurs',
            description: 'Supprimer un utilisateur du système',
            url_action: '/api/users/delete',
            parametres: 'id',
            niveau_securite: 'secret',
            statut: 'actif' 
        },
        { 
            id: 4, 
            code: 'VALIDATE_RAPPORT', 
            intitule: 'Valider un rapport', 
            categorie: 'validation',
            module: 'rapports',
            description: 'Valider un rapport de stage ou mémoire',
            url_action: '/api/rapports/validate',
            parametres: 'rapport_id,commentaire',
            niveau_securite: 'interne',
            statut: 'actif' 
        },
        { 
            id: 5, 
            code: 'VIEW_STUDENTS', 
            intitule: 'Consulter les étudiants', 
            categorie: 'consultation',
            module: 'etudiants',
            description: 'Consulter la liste des étudiants',
            url_action: '/api/students/list',
            parametres: 'filtres',
            niveau_securite: 'interne',
            statut: 'actif' 
        },
        { 
            id: 6, 
            code: 'EXPORT_DATA', 
            intitule: 'Exporter des données', 
            categorie: 'export',
            module: 'administration',
            description: 'Exporter des données du système',
            url_action: '/api/export/data',
            parametres: 'type,format',
            niveau_securite: 'confidentiel',
            statut: 'inactif' 
        }
    ];
    
    renderActionTable(actionData);
}

// Afficher les données dans le tableau
function renderActionTable(data) {
    const tbody = document.getElementById('actionTableBody');
    tbody.innerHTML = '';
    
    data.forEach(action => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${action.code}</td>
            <td>${action.intitule}</td>
            <td><span class="badge badge-${action.categorie}">${action.categorie}</span></td>
            <td>${action.description || '-'}</td>
            <td><span class="badge badge-module-${action.module}">${action.module}</span></td>
            <td><span class="badge ${action.statut === 'actif' ? 'status-active' : 'status-closed'}">${action.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editAction(${action.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteAction(${action.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les actions
function filterActions() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categorieFilter = document.getElementById('categorieFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = actionData.filter(action => {
        const matchesSearch = action.code.toLowerCase().includes(searchTerm) || 
                            action.intitule.toLowerCase().includes(searchTerm) ||
                            (action.description && action.description.toLowerCase().includes(searchTerm));
        const matchesCategorie = !categorieFilter || action.categorie === categorieFilter;
        const matchesStatus = !statusFilter || action.statut === statusFilter;
        
        return matchesSearch && matchesCategorie && matchesStatus;
    });
    
    renderActionTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter une action';
    document.getElementById('actionForm').reset();
    document.getElementById('actionModal').classList.add('active');
}

// Éditer une action
function editAction(id) {
    const action = actionData.find(a => a.id === id);
    if (!action) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier l\'action';
    document.getElementById('actionId').value = action.id;
    document.getElementById('code').value = action.code;
    document.getElementById('intitule').value = action.intitule;
    document.getElementById('categorie').value = action.categorie;
    document.getElementById('module').value = action.module;
    document.getElementById('description').value = action.description || '';
    document.getElementById('url_action').value = action.url_action || '';
    document.getElementById('parametres').value = action.parametres || '';
    document.getElementById('niveau_securite').value = action.niveau_securite || '';
    document.getElementById('statut').value = action.statut;
    
    document.getElementById('actionModal').classList.add('active');
}

// Sauvegarder une action
function saveAction() {
    const form = document.getElementById('actionForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || 
        !formData.get('categorie') || !formData.get('module')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const actionData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        categorie: formData.get('categorie'),
        module: formData.get('module'),
        description: formData.get('description'),
        url_action: formData.get('url_action'),
        parametres: formData.get('parametres'),
        niveau_securite: formData.get('niveau_securite'),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = actionData.findIndex(a => a.id === currentEditId);
        if (index !== -1) {
            actionData[index] = { ...actionData[index], ...actionData };
        }
        showNotification('Action modifiée avec succès', 'success');
    } else {
        // Ajout
        actionData.id = Date.now();
        actionData.push(actionData);
        showNotification('Action ajoutée avec succès', 'success');
    }
    
    closeModal();
    renderActionTable(actionData);
}

// Supprimer une action
function deleteAction(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette action ?')) {
        actionData = actionData.filter(a => a.id !== id);
        renderActionTable(actionData);
        showNotification('Action supprimée avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('actionModal').classList.remove('active');
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


