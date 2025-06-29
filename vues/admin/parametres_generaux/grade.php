

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Grades</h2>
        <p>Gérer les grades et échelons du personnel</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un grade
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un grade..." id="searchInput">
    <select class="filter-select" id="categorieFilter">
        <option value="">Toutes les catégories</option>
        <option value="enseignement">Enseignement</option>
        <option value="administration">Administration</option>
        <option value="technique">Technique</option>
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
                <th>Échelon</th>
                <th>Indice</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="gradeTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un grade -->
<div class="modal" id="gradeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un grade</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="gradeForm">
                <input type="hidden" id="gradeId" name="id">
                <div class="form-group">
                    <label for="code">Code grade *</label>
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
                        <option value="enseignement">Enseignement</option>
                        <option value="administration">Administration</option>
                        <option value="technique">Technique</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="echelon">Échelon</label>
                    <input type="number" id="echelon" name="echelon" min="1" max="10">
                </div>
                <div class="form-group">
                    <label for="indice">Indice</label>
                    <input type="number" id="indice" name="indice" min="100" max="1000">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
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
            <button class="btn btn-primary" onclick="saveGrade()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let gradeData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadGrades();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const categorieFilter = document.getElementById('categorieFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterGrades);
    categorieFilter.addEventListener('change', filterGrades);
    statusFilter.addEventListener('change', filterGrades);
}

// Charger les grades
function loadGrades() {
    // Simulation de données grades
    gradeData = [
        { 
            id: 1, 
            code: 'PROF', 
            intitule: 'Professeur', 
            categorie: 'enseignement', 
            echelon: 1,
            indice: 801,
            description: 'Grade le plus élevé dans l\'enseignement supérieur',
            statut: 'actif' 
        },
        { 
            id: 2, 
            code: 'MCF', 
            intitule: 'Maître de Conférences', 
            categorie: 'enseignement', 
            echelon: 1,
            indice: 701,
            description: 'Enseignant-chercheur',
            statut: 'actif' 
        },
        { 
            id: 3, 
            code: 'ADMIN', 
            intitule: 'Administrateur', 
            categorie: 'administration', 
            echelon: 2,
            indice: 450,
            description: 'Personnel administratif',
            statut: 'actif' 
        },
        { 
            id: 4, 
            code: 'TECH', 
            intitule: 'Technicien', 
            categorie: 'technique', 
            echelon: 3,
            indice: 350,
            description: 'Personnel technique',
            statut: 'actif' 
        }
    ];
    
    renderGradeTable(gradeData);
}

// Afficher les données dans le tableau
function renderGradeTable(data) {
    const tbody = document.getElementById('gradeTableBody');
    tbody.innerHTML = '';
    
    data.forEach(grade => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${grade.code}</td>
            <td>${grade.intitule}</td>
            <td><span class="badge badge-${grade.categorie}">${grade.categorie}</span></td>
            <td>${grade.echelon || '-'}</td>
            <td>${grade.indice || '-'}</td>
            <td><span class="badge ${grade.statut === 'actif' ? 'status-active' : 'status-closed'}">${grade.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editGrade(${grade.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteGrade(${grade.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrer les grades
function filterGrades() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categorieFilter = document.getElementById('categorieFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = gradeData.filter(grade => {
        const matchesSearch = grade.code.toLowerCase().includes(searchTerm) || 
                            grade.intitule.toLowerCase().includes(searchTerm);
        const matchesCategorie = !categorieFilter || grade.categorie === categorieFilter;
        const matchesStatus = !statusFilter || grade.statut === statusFilter;
        
        return matchesSearch && matchesCategorie && matchesStatus;
    });
    
    renderGradeTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un grade';
    document.getElementById('gradeForm').reset();
    document.getElementById('gradeModal').classList.add('active');
}

// Éditer un grade
function editGrade(id) {
    const grade = gradeData.find(g => g.id === id);
    if (!grade) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier le grade';
    document.getElementById('gradeId').value = grade.id;
    document.getElementById('code').value = grade.code;
    document.getElementById('intitule').value = grade.intitule;
    document.getElementById('categorie').value = grade.categorie;
    document.getElementById('echelon').value = grade.echelon || '';
    document.getElementById('indice').value = grade.indice || '';
    document.getElementById('description').value = grade.description || '';
    document.getElementById('statut').value = grade.statut;
    
    document.getElementById('gradeModal').classList.add('active');
}

// Sauvegarder un grade
function saveGrade() {
    const form = document.getElementById('gradeForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('code') || !formData.get('intitule') || !formData.get('categorie')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const gradeData = {
        code: formData.get('code'),
        intitule: formData.get('intitule'),
        categorie: formData.get('categorie'),
        echelon: parseInt(formData.get('echelon')) || null,
        indice: parseInt(formData.get('indice')) || null,
        description: formData.get('description'),
        statut: formData.get('statut')
    };
    
    if (currentEditId) {
        // Modification
        const index = gradeData.findIndex(g => g.id === currentEditId);
        if (index !== -1) {
            gradeData[index] = { ...gradeData[index], ...gradeData };
        }
        showNotification('Grade modifié avec succès', 'success');
    } else {
        // Ajout
        gradeData.id = Date.now();
        gradeData.push(gradeData);
        showNotification('Grade ajouté avec succès', 'success');
    }
    
    closeModal();
    renderGradeTable(gradeData);
}

// Supprimer un grade
function deleteGrade(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce grade ?')) {
        gradeData = gradeData.filter(g => g.id !== id);
        renderGradeTable(gradeData);
        showNotification('Grade supprimé avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('gradeModal').classList.remove('active');
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
.badge-enseignement {
    background: #dbeafe;
    color: #1e40af;
}

.badge-administration {
    background: #dcfce7;
    color: #166534;
}

.badge-technique {
    background: #fef3c7;
    color: #92400e;
}
</style> 