<?php
// Vérification de l'authentification et des permissions
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
?>

<div class="page-header">
    <h1><i class="fas fa-calendar-alt"></i> Gestion des Années Académiques</h1>
    <p>Créer, modifier et gérer les années académiques du système</p>
</div>

<div class="page-content">
    <div class="config-section">
        <div class="config-header">
            <div class="config-info">
                <h3>Années Académiques</h3>
                <p>Gérer les périodes académiques et leurs paramètres</p>
            </div>
            <button class="btn btn-success" onclick="openModal('addAnneeModal')">
                <i class="fas fa-plus"></i> Créer une année
            </button>
        </div>
        
        <div class="config-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Libellé</th>
                        <th>Date Début</th>
                        <th>Date Fin</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="anneesTableBody">
                    <!-- Les données seront chargées dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une année académique -->
<div id="addAnneeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Créer une Année Académique</h3>
            <span class="close" onclick="closeModal('addAnneeModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addAnneeForm">
                <div class="form-group">
                    <label for="code_annee">Code de l'année *</label>
                    <input type="text" id="code_annee" name="code_annee" required 
                           placeholder="Ex: 2024-2025" maxlength="9">
                </div>
                
                <div class="form-group">
                    <label for="libelle_annee">Libellé *</label>
                    <input type="text" id="libelle_annee" name="libelle_annee" required 
                           placeholder="Ex: Année académique 2024-2025">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_debut">Date de début</label>
                        <input type="date" id="date_debut" name="date_debut">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_fin">Date de fin</label>
                        <input type="date" id="date_fin" name="date_fin">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="active" name="active" value="1">
                        <span class="checkmark"></span>
                        Année active
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('addAnneeModal')">
                Annuler
            </button>
            <button type="button" class="btn btn-success" onclick="submitAddAnnee()">
                <i class="fas fa-save"></i> Créer
            </button>
        </div>
    </div>
</div>

<script>
// Variables globales
let annees = [];

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadAnneesData();
});

function loadAnneesData() {
    const tbody = document.getElementById('anneesTableBody');
    if (!tbody) return;

    // Afficher un loader pendant le chargement
    tbody.innerHTML = `
        <tr>
            <td colspan="7" style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                <br>Chargement des années académiques...
            </td>
        </tr>
    `;

    // Simuler le chargement depuis la base de données
    setTimeout(() => {
        // Données simulées - à remplacer par un appel AJAX vers la base
        annees = [
            { id_annee: 1, code_annee: '2024-2025', libelle_annee: 'Année académique 2024-2025', 
              date_debut: '2024-09-01', date_fin: '2025-08-31', active: 1 },
            { id_annee: 2, code_annee: '2023-2024', libelle_annee: 'Année académique 2023-2024', 
              date_debut: '2023-09-01', date_fin: '2024-08-31', active: 0 }
        ];
        
        displayAnneesInTable();
    }, 1000);
}

function displayAnneesInTable() {
    const tbody = document.getElementById('anneesTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    if (annees.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                <i class="fas fa-calendar-times" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                Aucune année académique trouvée
            </td>
        `;
        tbody.appendChild(row);
        return;
    }
    
    annees.forEach(annee => {
        const row = createAnneeRow(annee);
        tbody.appendChild(row);
    });
}

function createAnneeRow(annee) {
    const row = document.createElement('tr');
    
    const statusBadge = annee.active ? 
        '<span class="badge badge-success">Active</span>' : 
        '<span class="badge badge-secondary">Inactive</span>';
    
    row.innerHTML = `
        <td>${annee.id_annee}</td>
        <td><strong>${annee.code_annee}</strong></td>
        <td>${annee.libelle_annee}</td>
        <td>${annee.date_debut || '-'}</td>
        <td>${annee.date_fin || '-'}</td>
        <td>${statusBadge}</td>
        <td>
            <button class="action-btn btn-edit" onclick="editAnnee(${annee.id_annee})" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn btn-delete" onclick="deleteAnnee(${annee.id_annee})" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

function submitAddAnnee() {
    const form = document.getElementById('addAnneeForm');
    const formData = new FormData(form);
    
    const code_annee = formData.get('code_annee');
    const libelle_annee = formData.get('libelle_annee');

    // Validation
    if (!code_annee || !libelle_annee) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    // Simuler l'ajout - à remplacer par un appel AJAX
    const newAnnee = {
        id_annee: annees.length + 1,
        code_annee: code_annee,
        libelle_annee: libelle_annee,
        date_debut: formData.get('date_debut') || null,
        date_fin: formData.get('date_fin') || null,
        active: formData.get('active') ? 1 : 0
    };
    
    annees.push(newAnnee);
    
    // Fermer le modal et réinitialiser le formulaire
    closeModal('addAnneeModal');
    form.reset();
    
    // Afficher le message de succès
    showNotification('Année académique créée avec succès', 'success');
    
    // Recharger le tableau
    displayAnneesInTable();
}

function editAnnee(anneeId) {
    const annee = annees.find(a => a.id_annee === anneeId);
    if (annee) {
        showNotification(`Édition de l'année: ${annee.libelle_annee}`, 'info');
        console.log('Edit annee:', annee);
        // Ici vous pourrez implémenter un modal d'édition
    }
}

function deleteAnnee(anneeId) {
    const annee = annees.find(a => a.id_annee === anneeId);
    if (annee) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'année "${annee.libelle_annee}" ?`)) {
            annees = annees.filter(a => a.id_annee !== anneeId);
            displayAnneesInTable();
            showNotification('Année académique supprimée avec succès', 'success');
        }
    }
}
</script> 