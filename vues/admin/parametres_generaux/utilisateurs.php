

<div class="config-header">
    <button class="back-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="config-title">
        <h2>Gestion des Utilisateurs</h2>
        <p>Gérer les utilisateurs du système et leurs permissions</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Ajouter un utilisateur
    </button>
</div>

<div class="filters-bar">
    <input type="text" class="search-input" placeholder="Rechercher un utilisateur..." id="searchInput">
    <select class="filter-select" id="roleFilter">
        <option value="">Tous les rôles</option>
        <option value="admin">Administrateur</option>
        <option value="etudiant">Étudiant</option>
        <option value="enseignant">Enseignant</option>
        <option value="responsable_scolarite">Responsable Scolarité</option>
        <option value="secretaire">Secrétaire</option>
        <option value="charge_communication">Chargé Communication</option>
        <option value="commission">Commission</option>
    </select>
    <select class="filter-select" id="statusFilter">
        <option value="">Tous les statuts</option>
        <option value="actif">Actif</option>
        <option value="inactif">Inactif</option>
        <option value="bloque">Bloqué</option>
    </select>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom complet</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Dernière connexion</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="utilisateursTableBody">
            <!-- Les données seront chargées dynamiquement -->
        </tbody>
    </table>
</div>

<!-- Modal pour ajouter/modifier un utilisateur -->
<div class="modal" id="utilisateurModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Ajouter un utilisateur</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="utilisateurForm">
                <input type="hidden" id="utilisateurId" name="id">
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone">
                </div>
                <div class="form-group">
                    <label for="role">Rôle *</label>
                    <select id="role" name="role" required>
                        <option value="">Sélectionner un rôle</option>
                        <option value="admin">Administrateur</option>
                        <option value="etudiant">Étudiant</option>
                        <option value="enseignant">Enseignant</option>
                        <option value="responsable_scolarite">Responsable Scolarité</option>
                        <option value="secretaire">Secrétaire</option>
                        <option value="charge_communication">Chargé Communication</option>
                        <option value="commission">Commission</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="matricule">Matricule</label>
                    <input type="text" id="matricule" name="matricule">
                </div>
                <div class="form-group">
                    <label for="departement">Département</label>
                    <input type="text" id="departement" name="departement">
                </div>
                <div class="form-group">
                    <label for="fonction">Fonction</label>
                    <input type="text" id="fonction" name="fonction">
                </div>
                <div class="form-group">
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance">
                </div>
                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <textarea id="adresse" name="adresse" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                        <option value="bloque">Bloqué</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
            <button class="btn btn-primary" onclick="saveUtilisateur()">Enregistrer</button>
        </div>
    </div>
</div>

<script>
// Variables globales
let utilisateursData = [];
let currentEditId = null;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadUtilisateurs();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    searchInput.addEventListener('input', filterUtilisateurs);
    roleFilter.addEventListener('change', filterUtilisateurs);
    statusFilter.addEventListener('change', filterUtilisateurs);
}

// Charger les utilisateurs
function loadUtilisateurs() {
    // Simulation de données utilisateurs
    utilisateursData = [
        { 
            id: 1, 
            nom: 'Dupont', 
            prenom: 'Jean', 
            email: 'jean.dupont@ufrmi.com',
            telephone: '+225 0123456789',
            role: 'admin',
            matricule: 'ADM001',
            departement: 'Informatique',
            fonction: 'Administrateur système',
            date_naissance: '1985-03-15',
            adresse: '123 Rue de la Paix, Abidjan',
            statut: 'actif',
            derniere_connexion: '2024-01-15 14:30:00',
            notes: 'Administrateur principal du système'
        },
        { 
            id: 2, 
            nom: 'Martin', 
            prenom: 'Marie', 
            email: 'marie.martin@ufrmi.com',
            telephone: '+225 0123456790',
            role: 'enseignant',
            matricule: 'ENS001',
            departement: 'Informatique',
            fonction: 'Maître de conférences',
            date_naissance: '1980-07-22',
            adresse: '456 Avenue des Sciences, Abidjan',
            statut: 'actif',
            derniere_connexion: '2024-01-14 09:15:00',
            notes: 'Spécialiste en bases de données'
        },
        { 
            id: 3, 
            nom: 'Bernard', 
            prenom: 'Sophie', 
            email: 'sophie.bernard@ufrmi.com',
            telephone: '+225 0123456791',
            role: 'etudiant',
            matricule: 'ETU2024001',
            departement: 'Informatique',
            fonction: 'Étudiante',
            date_naissance: '2000-11-08',
            adresse: '789 Boulevard de l\'Université, Abidjan',
            statut: 'actif',
            derniere_connexion: '2024-01-15 16:45:00',
            notes: 'Étudiante en Master 2'
        }
    ];
    
    renderUtilisateursTable(utilisateursData);
}

// Afficher les données dans le tableau
function renderUtilisateursTable(data) {
    const tbody = document.getElementById('utilisateursTableBody');
    tbody.innerHTML = '';
    
    data.forEach(utilisateur => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${utilisateur.id}</td>
            <td>${utilisateur.nom} ${utilisateur.prenom}</td>
            <td>${utilisateur.email}</td>
            <td><span class="badge role-${utilisateur.role}">${getRoleLabel(utilisateur.role)}</span></td>
            <td>${formatDate(utilisateur.derniere_connexion)}</td>
            <td><span class="badge ${getStatusClass(utilisateur.statut)}">${utilisateur.statut}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editUtilisateur(${utilisateur.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-danger" onclick="deleteUtilisateur(${utilisateur.id})">
                    <i class="fas fa-trash"></i>
                </button>
                <button class="btn-icon btn-info" onclick="viewUtilisateur(${utilisateur.id})">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Obtenir le libellé du rôle
function getRoleLabel(role) {
    const roles = {
        'admin': 'Administrateur',
        'etudiant': 'Étudiant',
        'enseignant': 'Enseignant',
        'responsable_scolarite': 'Responsable Scolarité',
        'secretaire': 'Secrétaire',
        'charge_communication': 'Chargé Communication',
        'commission': 'Commission'
    };
    return roles[role] || role;
}

// Obtenir la classe CSS du statut
function getStatusClass(statut) {
    const classes = {
        'actif': 'status-active',
        'inactif': 'status-closed',
        'bloque': 'status-blocked'
    };
    return classes[statut] || 'status-default';
}

// Formater la date
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
}

// Filtrer les utilisateurs
function filterUtilisateurs() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filteredData = utilisateursData.filter(utilisateur => {
        const matchesSearch = utilisateur.nom.toLowerCase().includes(searchTerm) || 
                            utilisateur.prenom.toLowerCase().includes(searchTerm) ||
                            utilisateur.email.toLowerCase().includes(searchTerm) ||
                            (utilisateur.matricule && utilisateur.matricule.toLowerCase().includes(searchTerm));
        const matchesRole = !roleFilter || utilisateur.role === roleFilter;
        const matchesStatus = !statusFilter || utilisateur.statut === statusFilter;
        
        return matchesSearch && matchesRole && matchesStatus;
    });
    
    renderUtilisateursTable(filteredData);
}

// Ouvrir le modal d'ajout
function openAddModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Ajouter un utilisateur';
    document.getElementById('utilisateurForm').reset();
    document.getElementById('utilisateurModal').classList.add('active');
}

// Éditer un utilisateur
function editUtilisateur(id) {
    const utilisateur = utilisateursData.find(u => u.id === id);
    if (!utilisateur) return;
    
    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Modifier l\'utilisateur';
    document.getElementById('utilisateurId').value = utilisateur.id;
    document.getElementById('nom').value = utilisateur.nom;
    document.getElementById('prenom').value = utilisateur.prenom;
    document.getElementById('email').value = utilisateur.email;
    document.getElementById('telephone').value = utilisateur.telephone || '';
    document.getElementById('role').value = utilisateur.role;
    document.getElementById('matricule').value = utilisateur.matricule || '';
    document.getElementById('departement').value = utilisateur.departement || '';
    document.getElementById('fonction').value = utilisateur.fonction || '';
    document.getElementById('date_naissance').value = utilisateur.date_naissance || '';
    document.getElementById('adresse').value = utilisateur.adresse || '';
    document.getElementById('statut').value = utilisateur.statut;
    document.getElementById('notes').value = utilisateur.notes || '';
    
    document.getElementById('utilisateurModal').classList.add('active');
}

// Voir les détails d'un utilisateur
function viewUtilisateur(id) {
    const utilisateur = utilisateursData.find(u => u.id === id);
    if (!utilisateur) return;
    
    // Afficher les détails dans une modal ou une page séparée
    alert(`Détails de ${utilisateur.nom} ${utilisateur.prenom}\n\nEmail: ${utilisateur.email}\nRôle: ${getRoleLabel(utilisateur.role)}\nDépartement: ${utilisateur.departement || 'Non spécifié'}\nFonction: ${utilisateur.fonction || 'Non spécifiée'}`);
}

// Sauvegarder un utilisateur
function saveUtilisateur() {
    const form = document.getElementById('utilisateurForm');
    const formData = new FormData(form);
    
    // Validation
    if (!formData.get('nom') || !formData.get('prenom') || !formData.get('email') || !formData.get('role')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Validation email
    const email = formData.get('email');
    if (!isValidEmail(email)) {
        showNotification('Veuillez saisir une adresse email valide', 'error');
        return;
    }
    
    // Simulation de sauvegarde - à remplacer par un appel API
    const utilisateurData = {
        id: currentEditId || Date.now(),
        nom: formData.get('nom'),
        prenom: formData.get('prenom'),
        email: formData.get('email'),
        telephone: formData.get('telephone'),
        role: formData.get('role'),
        matricule: formData.get('matricule'),
        departement: formData.get('departement'),
        fonction: formData.get('fonction'),
        date_naissance: formData.get('date_naissance'),
        adresse: formData.get('adresse'),
        statut: formData.get('statut'),
        notes: formData.get('notes'),
        derniere_connexion: new Date().toISOString()
    };
    
    if (currentEditId) {
        // Modification
        const index = utilisateursData.findIndex(u => u.id === currentEditId);
        if (index !== -1) {
            utilisateursData[index] = { ...utilisateursData[index], ...utilisateurData };
        }
        showNotification('Utilisateur modifié avec succès', 'success');
    } else {
        // Ajout
        utilisateursData.push(utilisateurData);
        showNotification('Utilisateur ajouté avec succès', 'success');
    }
    
    renderUtilisateursTable(utilisateursData);
    closeModal();
}

// Supprimer un utilisateur
function deleteUtilisateur(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        utilisateursData = utilisateursData.filter(u => u.id !== id);
        renderUtilisateursTable(utilisateursData);
        showNotification('Utilisateur supprimé avec succès', 'success');
    }
}

// Fermer le modal
function closeModal() {
    document.getElementById('utilisateurModal').classList.remove('active');
    currentEditId = null;
}

// Validation email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Afficher une notification
function showNotification(message, type = 'info') {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Ajouter au DOM
    document.body.appendChild(notification);
    
    // Supprimer automatiquement après 5 secondes
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Styles CSS pour les notifications et badges
const styles = `
<style>
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: slideIn 0.3s ease-out;
}

.notification-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.notification-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.notification-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.notification-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
}

.notification-close {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    margin-left: 12px;
    opacity: 0.7;
}

.notification-close:hover {
    opacity: 1;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.role-admin { background: #dc3545; color: white; }
.role-etudiant { background: #007bff; color: white; }
.role-enseignant { background: #28a745; color: white; }
.role-responsable_scolarite { background: #ffc107; color: black; }
.role-secretaire { background: #17a2b8; color: white; }
.role-charge_communication { background: #6f42c1; color: white; }
.role-commission { background: #fd7e14; color: white; }

.status-active { background: #28a745; color: white; }
.status-closed { background: #6c757d; color: white; }
.status-blocked { background: #dc3545; color: white; }

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
`;

// Ajouter les styles au head
document.head.insertAdjacentHTML('beforeend', styles);
</script>
