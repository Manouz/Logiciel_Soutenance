

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Traitements</h2>
        <p>Gérer les différents types de traitements du système</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un traitement
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un traitement..." id="searchInput">
    <select class="filter-select" id="categorieFilter">
        <option value="">Toutes les catégories</option>
        <option value="validation">Validation</option>
        <option value="evaluation">Évaluation</option>
        <option value="notification">Notification</option>
        <option value="generation">Génération</option>
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
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Description</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="traitementTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un traitement -->
<div class="modal" id="traitementModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un traitement</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="traitementForm">
                <input type="hidden" id="traitementId" name="id">
                <div class="form-group">
                    <label for="code">Code *</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="categorie">Catégorie *</label>
                    <select id="categorie" name="categorie" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="validation">Validation</option>
                        <option value="evaluation">Évaluation</option>
                        <option value="notification">Notification</option>
                        <option value="generation">Génération</option>
                        <option value="archivage">Archivage</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priorite">Priorité</label>
                    <select id="priorite" name="priorite">
                        <option value="basse">Basse</option>
                        <option value="normale" selected>Normale</option>
                        <option value="haute">Haute</option>
                        <option value="critique">Critique</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="delai_traitement">Délai de traitement (minutes)</label>
                    <input type="number" id="delai_traitement" name="delai_traitement" min="1" max="1440">
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
            <button class="btn btn-primary" onclick="saveTraitement()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let traitementData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadTraitements();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const categorieFilter = document.getElementById('categorieFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterTraitements);
    categorieFilter.addEventListener('change', filterTraitements);
    statusFilter.addEventListener('change', filterTraitements);
}

// Charger les traitements
function loadTraitements() {
    // Simulation de données traitements
    traitementData = [
        { 
            id: 1, 
            code: 'VAL_RAPPORT', 
            nom: 'Validation de rapport', 
            categorie: 'validation',
            description: 'Validation automatique des rapports soumis',
            priorite: 'haute',
            delai_traitement: 30,
            statut: 'actif' 
        },
        { 
            id: 2, 
            code: 'EVAL_SOUTENANCE', 
            nom: 'Évaluation de soutenance', 
            categorie: 'evaluation',
            description: 'Traitement des évaluations de soutenance',
            priorite: 'normale',
            delai_traitement: 60,
            statut: 'actif' 
        },
        { 
            id: 3, 
            code: 'NOTIF_RESULTAT', 
            nom: 'Notification de résultat', 
            categorie: 'notification',
            description: 'Envoi des notifications de résultats',
            priorite: 'normale',
            delai_traitement: 15,
            statut: 'actif' 
        },
        { 
            id: 4, 
            code: 'GEN_ATTESTATION', 
            nom: 'Génération d\'attestation', 
            categorie: 'generation',
            description: 'Génération automatique des attestations',
            priorite: 'basse',
            delai_traitement: 120,
            statut: 'actif' 
        },
        { 
            id: 5, 
            code: 'ARCH_ANCIEN', 
            nom: 'Archivage ancien', 
            categorie: 'archivage',
            description: 'Archivage des anciens documents',
            priorite: 'basse',
            delai_traitement: 240,
            statut: 'inactif' 
        }
    ];
    
    renderTraitementTable(traitementData);
}

// Afficher les données dans le tableau
function renderTraitementTable(data) {
    const tbody = document.getElementById('traitementTableBody');
    tbody.innerHTML = '';
    
    data.forEach(traitement => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${traitement.code}</td>
            <td>${traitement.nom}</td>
            <td><span class="badge badge-${traitement.categorie}">${traitement.categorie}</span></td>
            <td>${traitement.description || '-'}</td>
            <td><span class="badge badge-priorite-${traitement.priorite}">${traitement.priorite}</span></td>
            <td><span class="badge ${traitement.statut === 'actif' ? 'status-active' : 'status-closed'}">${traitement.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editTraitement(${traitement.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteTraitement(${traitement.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les traitements
function filterTraitements() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categorieFilter = document.getElementById('categorieFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = traitementData.filter(traitement => {
        const matchesSearch = traitement.code.toLowerCase().includes(searchTerm) || 
                            traitement.nom.toLowerCase().includes(searchTerm) ||
                            (traitement.description && traitement.description.toLowerCase().includes(searchTerm));
        const matchesCategorie = !categorieFilter || traitement.categorie === categorieFilter;
        const matchesStatus = !statusFilter || traitement.statut === statusFilter;
        
        return matchesSearch && matchesCategorie && matchesStatus;
    });
    
    renderTraitementTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un traitement';
    document.getElementById('traitementForm').reset();
    document.getElementById('priorite').value = 'normale';
    document.getElementById('traitementModal').classList.add('active');
}

// Éditer un traitement
function editTraitement(id) {
    const traitement = traitementData.find(t => t.id === id);
    if (!traitement) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier le traitement';
    document.getElementById('traitementId').value = traitement.id;
    document.getElementById('code').value = traitement.code;
    document.getElementById('nom').value = traitement.nom;
    document.getElementById('categorie').value = traitement.categorie;
    document.getElementById('priorite').value = traitement.priorite;
    document.getElementById('description').value = traitement.description || '';
    document.getElementById('delai_traitement').value = traitement.delai_traitement || '';
    document.getElementById('statut').value = traitement.statut;
    
    document.getElementById('traitementModal').classList.add('active');
}

// Sauvegarder un traitement
function saveTraitement() {
    const form = document.getElementById('traitementForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('nom') || !formData.get('categorie')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const traitementData = {
        code: formData.get('code'),
        nom: formData.get('nom'),
        categorie: formData.get('categorie'),
        priorite: formData.get('priorite'),
        description: formData.get('description'),
        delai_traitement: parseInt(formData.get('delai_traitement')) || null,
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = traitementData.findIndex(t => t.id === currentEditId);
        if (index !== -1) {
            traitementData[index] = { ...traitementData[index], ...traitementData };
        }
        showNotification('Traitement modifié avec succès', 'success');
    } else {
        // Ajout
        traitementData.id = Date.now();
        traitementData.push(traitementData);
        showNotification('Traitement ajouté avec succès', 'success');
    }
    
    closeModal();
    renderTraitementTable(traitementData);
}

// Supprimer un traitement
function deleteTraitement(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce traitement ?')) {
        traitementData = traitementData.filter(t => t.id !== id);
        renderTraitementTable(traitementData);
        showNotification('Traitement supprimé avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('traitementModal').classList.remove('active');
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
.badge-validation {
    background: #dcfce7;
    color: #166534;
}

.badge-evaluation {
    background: #dbeafe;
    color: #1e40af;
}

.badge-notification {
    background: #fef3c7;
    color: #92400e;
}

.badge-generation {
    background: #f3e8ff;
    color: #7c3aed;
}

.badge-archivage {
    background: #f1f5f9;
    color: #475569;
}

.badge-priorite-basse {
    background: #dcfce7;
    color: #166534;
}

.badge-priorite-normale {
    background: #dbeafe;
    color: #1e40af;
}

.badge-priorite-haute {
    background: #fef3c7;
    color: #92400e;
}

.badge-priorite-critique {
    background: #fee2e2;
    color: #991b1b;
}
</style> 