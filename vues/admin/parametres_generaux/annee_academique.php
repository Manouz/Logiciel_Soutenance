
<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Années Académiques</h2>
        <p>Gérer les années académiques et leurs périodes</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter une année
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher une année..." id="searchInput">
    <select class="filter-select" id="statusFilter">
        <option value="">Tous les statuts</option>
        <option value="active">Active</option>
        <option value="fermee">Fermée</option>
        <option value="planifiee">Planifiée</option>
    </select>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>Année</th>
                <th>Période</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Statut</th>
                <th>Année courante</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="anneeTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier une année académique -->
<div class="modal" id="anneeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter une année académique</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="anneeForm">
                <input type="hidden" id="anneeId" name="id">
                <div class="form-group">
                    <label for="annee">Année académique *</label>
                    <input type="text" id="annee" name="annee" placeholder="2023-2024" required>
                </div>
                <div class="form-group">
                    <label for="periode">Période *</label>
                    <select id="periode" name="periode" required>
                        <option value="">Sélectionner une période</option>
                        <option value="S1">Semestre 1</option>
                        <option value="S2">Semestre 2</option>
                        <option value="ANNEE">Année complète</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_debut">Date de début *</label>
                    <input type="date" id="date_debut" name="date_debut" required>
                </div>
                <div class="form-group">
                    <label for="date_fin">Date de fin *</label>
                    <input type="date" id="date_fin" name="date_fin" required>
                </div>
                <div class="form-group">
                    <label for="statut">Statut *</label>
                    <select id="statut" name="statut" required>
                        <option value="">Sélectionner un statut</option>
                        <option value="planifiee">Planifiée</option>
                        <option value="active">Active</option>
                        <option value="fermee">Fermée</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="annee_courante" name="annee_courante">
                        <span>Définir comme année académique courante</span>
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
            <button class="btn btn-primary" onclick="saveAnnee()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let anneeData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadAnnees();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterAnnees);
    statusFilter.addEventListener('change', filterAnnees);
}

// Charger les années académiques
function loadAnnees() {
    // Simulation de données années académiques
    anneeData = [
        { 
            id: 1, 
            annee: '2023-2024', 
            periode: 'S1', 
            date_debut: '2023-09-01',
            date_fin: '2024-01-31',
            statut: 'active',
            annee_courante: true,
            description: 'Premier semestre 2023-2024'
        },
        { 
            id: 2, 
            annee: '2023-2024', 
            periode: 'S2', 
            date_debut: '2024-02-01',
            date_fin: '2024-06-30',
            statut: 'planifiee',
            annee_courante: false,
            description: 'Deuxième semestre 2023-2024'
        },
        { 
            id: 3, 
            annee: '2022-2023', 
            periode: 'ANNEE', 
            date_debut: '2022-09-01',
            date_fin: '2023-06-30',
            statut: 'fermee',
            annee_courante: false,
            description: 'Année académique 2022-2023'
        },
        { 
            id: 4, 
            annee: '2024-2025', 
            periode: 'S1', 
            date_debut: '2024-09-01',
            date_fin: '2025-01-31',
            statut: 'planifiee',
            annee_courante: false,
            description: 'Premier semestre 2024-2025'
        }
    ];
    
    renderAnneeTable(anneeData);
}

// Afficher les données dans le tableau
function renderAnneeTable(data) {
    const tbody = document.getElementById('anneeTableBody');
    tbody.innerHTML = '';
    
    data.forEach(annee => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${annee.annee}</td>
            <td>${annee.periode}</td>
            <td>${formatDate(annee.date_debut)}</td>
            <td>${formatDate(annee.date_fin)}</td>
            <td><span class="badge badge-${annee.statut}">${annee.statut}</span></td>
            <td>
                ${annee.annee_courante ? 
                    '<span class="badge badge-current"><i class="fas fa-star"></i> Courante</span>' : 
                    '-'
                }
            </td>
            <td>
                <button class="btn-icon btn-edit" onclick="editAnnee(${annee.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteAnnee(${annee.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Formater une date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

// Filtrer les années académiques
function filterAnnees() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = anneeData.filter(annee => {
        const matchesSearch = annee.annee.toLowerCase().includes(searchTerm) || 
                            annee.periode.toLowerCase().includes(searchTerm);
        const matchesStatus = !statusFilter || annee.statut === statusFilter;
        
        return matchesSearch && matchesStatus;
    });
    
    renderAnneeTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter une année académique';
    document.getElementById('anneeForm').reset();
    document.getElementById('anneeModal').classList.add('active');
}

// Éditer une année académique
function editAnnee(id) {
    const annee = anneeData.find(a => a.id === id);
    if (!annee) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier l\'année académique';
    document.getElementById('anneeId').value = annee.id;
    document.getElementById('annee').value = annee.annee;
    document.getElementById('periode').value = annee.periode;
    document.getElementById('date_debut').value = annee.date_debut;
    document.getElementById('date_fin').value = annee.date_fin;
    document.getElementById('statut').value = annee.statut;
    document.getElementById('description').value = annee.description || '';
    document.getElementById('annee_courante').checked = annee.annee_courante;
    
    document.getElementById('anneeModal').classList.add('active');
}

// Sauvegarder une année académique
function saveAnnee() {
    const form = document.getElementById('anneeForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('annee') || !formData.get('periode') || 
        !formData.get('date_debut') || !formData.get('date_fin') || !formData.get('statut')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Validation des dates
    const dateDebut = new Date(formData.get('date_debut'));
    const dateFin = new Date(formData.get('date_fin'));
    
    if (dateDebut >= dateFin) {
        showNotification('La date de fin doit être postérieure à la date de début', 'error');
        return;
    }
    
    // Simulation de sauvegarde
    const anneeData = {
        annee: formData.get('annee'),
        periode: formData.get('periode'),
        date_debut: formData.get('date_debut'),
        date_fin: formData.get('date_fin'),
        statut: formData.get('statut'),
        description: formData.get('description'),
        annee_courante: formData.get('annee_courante') === 'on'
    };
    
    if (currentEditId) {
        // Modification
        const index = anneeData.findIndex(a => a.id === currentEditId);
        if (index !== -1) {
            anneeData[index] = { ...anneeData[index], ...anneeData };
        }
        showNotification('Année académique modifiée avec succès', 'success');
    } else {
        // Ajout
        anneeData.id = Date.now();
        anneeData.push(anneeData);
        showNotification('Année académique ajoutée avec succès', 'success');
    }
    
    // Si c'est l'année courante, désactiver les autres
    if (anneeData.annee_courante) {
        anneeData.forEach(a => {
            if (a.id !== anneeData.id) {
                a.annee_courante = false;
            }
        });
    }
    
    closeModal();
    renderAnneeTable(anneeData);
}

// Supprimer une année académique
function deleteAnnee(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette année académique ?')) {
        anneeData = anneeData.filter(a => a.id !== id);
        renderAnneeTable(anneeData);
        showNotification('Année académique supprimée avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('anneeModal').classList.remove('active');
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
.badge-active {
    background: #dcfce7;
    color: #166534;
}

.badge-fermee {
    background: #fee2e2;
    color: #991b1b;
}

.badge-planifiee {
    background: #dbeafe;
    color: #1e40af;
}

.badge-current {
    background: #fef3c7;
    color: #92400e;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}
</style> 