<?php
// Vérification de l'authentification et des permissions
/*session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}*/
?>

<div class="page-header">
    <h1><i class="fas fa-cogs"></i> Gestion des Traitements</h1>
    <p>Créer, gérer et supprimer les traitements automatisés du système</p>
</div>

<div class="page-content">
    <div class="config-section">
        <div class="config-header">
            <div class="config-info">
                <h3>Traitements Automatisés</h3>
                <p>Gérer les processus automatisés et leurs paramètres</p>
            </div>
            <button class="btn btn-success" onclick="openModal('addTraitementModal')">
                <i class="fas fa-plus"></i> Créer un traitement
            </button>
        </div>
        
        <div class="config-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Libellé</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="traitementsTableBody">
                    <!-- Les données seront chargées dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un traitement -->
<div id="addTraitementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Créer un Traitement</h3>
            <span class="close" onclick="closeModal('addTraitementModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addTraitementForm">
                <div class="form-group">
                    <label for="lib_trait">Libellé du traitement *</label>
                    <input type="text" id="lib_trait" name="lib_trait" required 
                           placeholder="Ex: Validation automatique des rapports">
                </div>
                
                <div class="form-group">
                    <label for="description_trait">Description</label>
                    <textarea id="description_trait" name="description_trait" rows="3" 
                              placeholder="Description détaillée du traitement"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="actif" name="actif" value="1" checked>
                        <span class="checkmark"></span>
                        Traitement actif
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('addTraitementModal')">
                Annuler
            </button>
            <button type="button" class="btn btn-success" onclick="submitAddTraitement()">
                <i class="fas fa-save"></i> Créer
            </button>
        </div>
    </div>
</div>

<script>
// Variables globales
let traitements = [];

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadTraitementsData();
});

function loadTraitementsData() {
    const tbody = document.getElementById('traitementsTableBody');
    if (!tbody) return;

    // Afficher un loader pendant le chargement
    tbody.innerHTML = `
        <tr>
            <td colspan="5" style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                <br>Chargement des traitements...
            </td>
        </tr>
    `;

    // Simuler le chargement depuis la base de données
    setTimeout(() => {
        // Données simulées - à remplacer par un appel AJAX vers la base
        traitements = [
            { id_trait: 1, lib_trait: 'Validation automatique des rapports', 
              description: 'Vérification automatique des rapports déposés', actif: 1 },
            { id_trait: 2, lib_trait: 'Envoi des notifications', 
              description: 'Envoi automatique des notifications aux utilisateurs', actif: 1 },
            { id_trait: 3, lib_trait: 'Archivage des anciens documents', 
              description: 'Archivage automatique des documents obsolètes', actif: 0 }
        ];
        
        displayTraitementsInTable();
    }, 1000);
}

function displayTraitementsInTable() {
    const tbody = document.getElementById('traitementsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    if (traitements.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                <i class="fas fa-cogs" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                Aucun traitement trouvé
            </td>
        `;
        tbody.appendChild(row);
        return;
    }
    
    traitements.forEach(traitement => {
        const row = createTraitementRow(traitement);
        tbody.appendChild(row);
    });
}

function createTraitementRow(traitement) {
    const row = document.createElement('tr');
    
    const statusBadge = traitement.actif ? 
        '<span class="badge badge-success">Actif</span>' : 
        '<span class="badge badge-secondary">Inactif</span>';
    
    row.innerHTML = `
        <td>${traitement.id_trait}</td>
        <td><strong>${traitement.lib_trait}</strong></td>
        <td>${traitement.description || '-'}</td>
        <td>${statusBadge}</td>
        <td>
            <button class="action-btn btn-edit" onclick="editTraitement(${traitement.id_trait})" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn btn-delete" onclick="deleteTraitement(${traitement.id_trait})" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

function submitAddTraitement() {
    const form = document.getElementById('addTraitementForm');
    const formData = new FormData(form);
    
    const lib_trait = formData.get('lib_trait');

    // Validation
    if (!lib_trait || lib_trait.trim() === '') {
        showNotification('Veuillez remplir le libellé du traitement', 'error');
        return;
    }

    // Simuler l'ajout - à remplacer par un appel AJAX
    const newTraitement = {
        id_trait: traitements.length + 1,
        lib_trait: lib_trait,
        description: formData.get('description_trait') || null,
        actif: formData.get('actif') ? 1 : 0
    };
    
    traitements.push(newTraitement);
    
    // Fermer le modal et réinitialiser le formulaire
    closeModal('addTraitementModal');
    form.reset();
    
    // Afficher le message de succès
    showNotification('Traitement créé avec succès', 'success');
    
    // Recharger le tableau
    displayTraitementsInTable();
}

function editTraitement(traitementId) {
    const traitement = traitements.find(t => t.id_trait === traitementId);
    if (traitement) {
        showNotification(`Édition du traitement: ${traitement.lib_trait}`, 'info');
        console.log('Edit traitement:', traitement);
        // Ici vous pourrez implémenter un modal d'édition
    }
}

function deleteTraitement(traitementId) {
    const traitement = traitements.find(t => t.id_trait === traitementId);
    if (traitement) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer le traitement "${traitement.lib_trait}" ?`)) {
            traitements = traitements.filter(t => t.id_trait !== traitementId);
            displayTraitementsInTable();
            showNotification('Traitement supprimé avec succès', 'success');
        }
    }
}
</script> 