<?php
// Vérification de l'authentification et des permissions
/*session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}*/
?>

<div class="page-header">
    <h1><i class="fas fa-layer-group"></i> Gestion des Niveaux d'Approbation</h1>
    <p>Définir et gérer les niveaux d'approbation pour les processus de validation</p>
</div>

<div class="page-content">
    <div class="config-section">
        <div class="config-header">
            <div class="config-info">
                <h3>Niveaux d'Approbation</h3>
                <p>Configurer les étapes d'approbation et leurs autorités</p>
            </div>
            <button class="btn btn-success" onclick="openModal('addNiveauModal')">
                <i class="fas fa-plus"></i> Créer un niveau
            </button>
        </div>
        
        <div class="config-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Libellé</th>
                        <th>Niveau</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="niveauxTableBody">
                    <!-- Les données seront chargées dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un niveau d'approbation -->
<div id="addNiveauModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Créer un Niveau d'Approbation</h3>
            <span class="close" onclick="closeModal('addNiveauModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addNiveauForm">
                <div class="form-group">
                    <label for="code_niveau">Code du niveau *</label>
                    <input type="text" id="code_niveau" name="code_niveau" required 
                           placeholder="Ex: NIV1" maxlength="10">
                </div>
                
                <div class="form-group">
                    <label for="libelle_niveau">Libellé *</label>
                    <input type="text" id="libelle_niveau" name="libelle_niveau" required 
                           placeholder="Ex: Niveau 1 - Validation initiale">
                </div>
                
                <div class="form-group">
                    <label for="niveau_hierarchique">Niveau hiérarchique *</label>
                    <input type="number" id="niveau_hierarchique" name="niveau_hierarchique" 
                           required min="1" max="10" placeholder="1-10">
                </div>
                
                <div class="form-group">
                    <label for="description_niveau">Description</label>
                    <textarea id="description_niveau" name="description_niveau" rows="3" 
                              placeholder="Description du niveau d'approbation"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="actif" name="actif" value="1" checked>
                        <span class="checkmark"></span>
                        Niveau actif
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('addNiveauModal')">
                Annuler
            </button>
            <button type="button" class="btn btn-success" onclick="submitAddNiveau()">
                <i class="fas fa-save"></i> Créer
            </button>
        </div>
    </div>
</div>

<script>
// Variables globales
let niveaux = [];

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadNiveauxData();
});

function loadNiveauxData() {
    const tbody = document.getElementById('niveauxTableBody');
    if (!tbody) return;

    // Afficher un loader pendant le chargement
    tbody.innerHTML = `
        <tr>
            <td colspan="7" style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                <br>Chargement des niveaux d'approbation...
            </td>
        </tr>
    `;

    // Simuler le chargement depuis la base de données
    setTimeout(() => {
        // Données simulées - à remplacer par un appel AJAX vers la base
        niveaux = [
            { id_niveau: 1, code_niveau: 'NIV1', libelle_niveau: 'Validation initiale', 
              niveau_hierarchique: 1, description: 'Première validation par l\'encadrant', actif: 1 },
            { id_niveau: 2, code_niveau: 'NIV2', libelle_niveau: 'Validation départementale', 
              niveau_hierarchique: 2, description: 'Validation par le responsable de département', actif: 1 },
            { id_niveau: 3, code_niveau: 'NIV3', libelle_niveau: 'Validation finale', 
              niveau_hierarchique: 3, description: 'Validation finale par la commission', actif: 1 }
        ];
        
        displayNiveauxInTable();
    }, 1000);
}

function displayNiveauxInTable() {
    const tbody = document.getElementById('niveauxTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    if (niveaux.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                <i class="fas fa-layer-group" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                Aucun niveau d'approbation trouvé
            </td>
        `;
        tbody.appendChild(row);
        return;
    }
    
    niveaux.forEach(niveau => {
        const row = createNiveauRow(niveau);
        tbody.appendChild(row);
    });
}

function createNiveauRow(niveau) {
    const row = document.createElement('tr');
    
    const statusBadge = niveau.actif ? 
        '<span class="badge badge-success">Actif</span>' : 
        '<span class="badge badge-secondary">Inactif</span>';
    
    row.innerHTML = `
        <td>${niveau.id_niveau}</td>
        <td><strong>${niveau.code_niveau}</strong></td>
        <td>${niveau.libelle_niveau}</td>
        <td><span class="badge badge-info">Niveau ${niveau.niveau_hierarchique}</span></td>
        <td>${niveau.description || '-'}</td>
        <td>${statusBadge}</td>
        <td>
            <button class="action-btn btn-edit" onclick="editNiveau(${niveau.id_niveau})" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn btn-delete" onclick="deleteNiveau(${niveau.id_niveau})" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

function submitAddNiveau() {
    const form = document.getElementById('addNiveauForm');
    const formData = new FormData(form);
    
    const code_niveau = formData.get('code_niveau');
    const libelle_niveau = formData.get('libelle_niveau');
    const niveau_hierarchique = formData.get('niveau_hierarchique');

    // Validation
    if (!code_niveau || !libelle_niveau || !niveau_hierarchique) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    // Simuler l'ajout - à remplacer par un appel AJAX
    const newNiveau = {
        id_niveau: niveaux.length + 1,
        code_niveau: code_niveau,
        libelle_niveau: libelle_niveau,
        niveau_hierarchique: parseInt(niveau_hierarchique),
        description: formData.get('description_niveau') || null,
        actif: formData.get('actif') ? 1 : 0
    };
    
    niveaux.push(newNiveau);
    
    // Fermer le modal et réinitialiser le formulaire
    closeModal('addNiveauModal');
    form.reset();
    
    // Afficher le message de succès
    showNotification('Niveau d\'approbation créé avec succès', 'success');
    
    // Recharger le tableau
    displayNiveauxInTable();
}

function editNiveau(niveauId) {
    const niveau = niveaux.find(n => n.id_niveau === niveauId);
    if (niveau) {
        showNotification(`Édition du niveau: ${niveau.libelle_niveau}`, 'info');
        console.log('Edit niveau:', niveau);
        // Ici vous pourrez implémenter un modal d'édition
    }
}

function deleteNiveau(niveauId) {
    const niveau = niveaux.find(n => n.id_niveau === niveauId);
    if (niveau) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer le niveau "${niveau.libelle_niveau}" ?`)) {
            niveaux = niveaux.filter(n => n.id_niveau !== niveauId);
            displayNiveauxInTable();
            showNotification('Niveau d\'approbation supprimé avec succès', 'success');
        }
    }
}
</script> 