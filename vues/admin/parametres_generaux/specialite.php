

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Spécialités</h2>
        <p>Gérer les spécialités et domaines d'étude</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter une spécialité
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher une spécialité..." id="searchInput">
    <select class="filter-select" id="domaineFilter">
        <option value="">Tous les domaines</option>
        <option value="informatique">Informatique</option>
        <option value="mathematiques">Mathématiques</option>
        <option value="physique">Physique</option>
        <option value="chimie">Chimie</option>
        <option value="biologie">Biologie</option>
        <option value="economie">Économie</option>
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
                <th>Domaine</th>
                <th>Description</th>
                <th>Responsable</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="specialiteTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier une spécialité -->
<div class="modal" id="specialiteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter une spécialité</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="specialiteForm">
                <input type="hidden" id="specialiteId" name="id">
                <div class="form-group">
                    <label for="code">Code *</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="intitule">Intitulé *</label>
                    <input type="text" id="intitule" name="intitule" required>
                </div>
                <div class="form-group">
                    <label for="domaine">Domaine *</label>
                    <select id="domaine" name="domaine" required>
                        <option value="">Sélectionner un domaine</option>
                        <option value="informatique">Informatique</option>
                        <option value="mathematiques">Mathématiques</option>
                        <option value="physique">Physique</option>
                        <option value="chimie">Chimie</option>
                        <option value="biologie">Biologie</option>
                        <option value="economie">Économie</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="responsable">Responsable</label>
                    <select id="responsable" name="responsable">
                        <option value="">Sélectionner un responsable</option>
                        <!-- Options chargées dynamiquement -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="objectifs">Objectifs</label>
                    <textarea id="objectifs" name="objectifs" rows="3"></textarea>
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
            <button class="btn btn-primary" onclick="saveSpecialite()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let specialiteData = [];
let responsablesData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadResponsables();
    loadSpecialites();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const domaineFilter = document.getElementById('domaineFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterSpecialites);
    domaineFilter.addEventListener('change', filterSpecialites);
    statusFilter.addEventListener('change', filterSpecialites);
}

// Charger les responsables
function loadResponsables() {
    // Simulation de données responsables
    responsablesData = [
        { id: 1, nom: 'Dr. Jean Dupont', email: 'jean.dupont@example.com' },
        { id: 2, nom: 'Prof. Marie Martin', email: 'marie.martin@example.com' },
        { id: 3, nom: 'Dr. Pierre Durand', email: 'pierre.durand@example.com' },
        { id: 4, nom: 'Prof. Sophie Bernard', email: 'sophie.bernard@example.com' }
    ];
    
    // Remplir le select des responsables
    const responsableSelect = document.getElementById('responsable');
    responsablesData.forEach(resp => {
        const option = document.createElement('option');
        option.value = resp.id;
        option.textContent = resp.nom;
        responsableSelect.appendChild(option);
    });
}

// Charger les spécialités
function loadSpecialites() {
    // Simulation de données spécialités
    specialiteData = [
        { 
            id: 1, 
            code: 'INFO-DEV', 
            intitule: 'Développement informatique', 
            domaine: 'informatique',
            responsable: 1,
            description: 'Spécialité en développement logiciel et applications',
            objectifs: 'Former des développeurs compétents',
            prerequis: 'Bases en programmation',
            statut: 'actif' 
        },
        { 
            id: 2, 
            code: 'INFO-RESEAU', 
            intitule: 'Réseaux et télécommunications', 
            domaine: 'informatique',
            responsable: 2,
            description: 'Spécialité en réseaux informatiques',
            objectifs: 'Former des experts en réseaux',
            prerequis: 'Connaissances en informatique',
            statut: 'actif' 
        },
        { 
            id: 3, 
            code: 'MATH-ANA', 
            intitule: 'Analyse mathématique', 
            domaine: 'mathematiques',
            responsable: 3,
            description: 'Spécialité en analyse mathématique avancée',
            objectifs: 'Former des mathématiciens',
            prerequis: 'Solides bases en mathématiques',
            statut: 'actif' 
        },
        { 
            id: 4, 
            code: 'PHYS-QUANT', 
            intitule: 'Physique quantique', 
            domaine: 'physique',
            responsable: 4,
            description: 'Spécialité en physique quantique',
            objectifs: 'Former des physiciens quantiques',
            prerequis: 'Bases en physique',
            statut: 'inactif' 
        }
    ];
    
    renderSpecialiteTable(specialiteData);
}

// Afficher les données dans le tableau
function renderSpecialiteTable(data) {
    const tbody = document.getElementById('specialiteTableBody');
    tbody.innerHTML = '';
    
    data.forEach(specialite => {
        const responsable = responsablesData.find(r => r.id === specialite.responsable);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${specialite.code}</td>
            <td>${specialite.intitule}</td>
            <td><span class="badge badge-${specialite.domaine}">${specialite.domaine}</span></td>
            <td>${specialite.description || '-'}</td>
            <td>${responsable ? responsable.nom : '-'}</td>
            <td><span class="badge ${specialite.statut === 'actif' ? 'status-active' : 'status-closed'}">${specialite.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editSpecialite(${specialite.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteSpecialite(${specialite.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les spécialités
function filterSpecialites() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const domaineFilter = document.getElementById('domaineFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = specialiteData.filter(specialite => {
        const matchesSearch = specialite.code.toLowerCase().includes(searchTerm) || 
                            specialite.intitule.toLowerCase().includes(searchTerm) ||
                            (specialite.description && specialite.description.toLowerCase().includes(searchTerm));
        const matchesDomaine = !domaineFilter || specialite.domaine === domaineFilter;
        const matchesStatus = !statusFilter || specialite.statut === statusFilter;
        
        return matchesSearch && matchesDomaine && matchesStatus;
    });
    
    renderSpecialiteTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter une spécialité';
    document.getElementById('specialiteForm').reset();
    document.getElementById('specialiteModal').classList.add('active');
}

// Éditer une spécialité
function editSpecialite(id) {
    const specialite = specialiteData.find(s => s.id === id);
    if (!specialite) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier la spécialité';
    document.getElementById('specialiteId').value = specialite.id;
    document.getElementById('code').value = specialite.code;
    document.getElementById('intitule').value = specialite.intitule;
    document.getElementById('domaine').value = specialite.domaine;
    document.getElementById('responsable').value = specialite.responsable || '';
    document.getElementById('description').value = specialite.description || '';
    document.getElementById('objectifs').value = specialite.objectifs || '';
    document.getElementById('prerequis').value = specialite.prerequis || '';
    document.getElementById('statut').value = specialite.statut;
    
    document.getElementById('specialiteModal').classList.add('active');
}

// Sauvegarder une spécialité
function saveSpecialite() {
    const form = document.getElementById('specialiteForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || !formData.get('domaine')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const specialiteData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        domaine: formData.get('domaine'),
        responsable: parseInt(formData.get('responsable')) || null,
        description: formData.get('description'),
        objectifs: formData.get('objectifs'),
        prerequis: formData.get('prerequis'),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = specialiteData.findIndex(s => s.id === currentEditId);
        if (index !== -1) {
            specialiteData[index] = { ...specialiteData[index], ...specialiteData };
        }
        showNotification('Spécialité modifiée avec succès', 'success');
    } else {
        // Ajout
        specialiteData.id = Date.now();
        specialiteData.push(specialiteData);
        showNotification('Spécialité ajoutée avec succès', 'success');
    }
    
    closeModal();
    renderSpecialiteTable(specialiteData);
}

// Supprimer une spécialité
function deleteSpecialite(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette spécialité ?')) {
        specialiteData = specialiteData.filter(s => s.id !== id);
        renderSpecialiteTable(specialiteData);
        showNotification('Spécialité supprimée avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('specialiteModal').classList.remove('active');
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
.badge-informatique {
    background: #dbeafe;
    color: #1e40af;
}

.badge-mathematiques {
    background: #dcfce7;
    color: #166534;
}

.badge-physique {
    background: #fef3c7;
    color: #92400e;
}

.badge-chimie {
    background: #f3e8ff;
    color: #7c3aed;
}

.badge-biologie {
    background: #ecfdf5;
    color: #065f46;
}

.badge-economie {
    background: #fef2f2;
    color: #991b1b;
}

.badge-autre {
    background: #f1f5f9;
    color: #475569;
}
</style> 