<?php
// Vérification de l'authentification et des permissions
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
?>

<div class="page-header">
    <h1><i class="fas fa-user-tie"></i> Gestion des Fonctions</h1>
    <p>Définir et gérer les fonctions et postes du personnel</p>
</div>

<div class="page-content">
    <div class="config-section">
        <div class="config-header">
            <div class="config-info">
                <h3>Fonctions du Personnel</h3>
                <p>Gérer les fonctions et postes administratifs et académiques</p>
            </div>
            <button class="btn btn-success" onclick="openModal('addFonctionModal')">
                <i class="fas fa-plus"></i> Créer une fonction
            </button>
        </div>
        
        <div class="config-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Libellé</th>
                        <th>Niveau Responsabilité</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fonctionsTableBody">
                    <!-- Les données seront chargées dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une fonction -->
<div id="addFonctionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Créer une Fonction</h3>
            <span class="close" onclick="closeModal('addFonctionModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addFonctionForm">
                <div class="form-group">
                    <label for="code_fonction">Code de la fonction *</label>
                    <input type="text" id="code_fonction" name="code_fonction" required 
                           placeholder="Ex: DIR_FILIERE" maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="libelle_fonction">Libellé *</label>
                    <input type="text" id="libelle_fonction" name="libelle_fonction" required 
                           placeholder="Ex: Directeur de Filière">
                </div>
                
                <div class="form-group">
                    <label for="niveau_responsabilite">Niveau de responsabilité *</label>
                    <select id="niveau_responsabilite" name="niveau_responsabilite" required>
                        <option value="">Sélectionner un niveau</option>
                        <option value="1">Niveau 1 - Basique</option>
                        <option value="2">Niveau 2 - Intermédiaire</option>
                        <option value="3">Niveau 3 - Avancé</option>
                        <option value="4">Niveau 4 - Supérieur</option>
                        <option value="5">Niveau 5 - Direction</option>
                        <option value="6">Niveau 6 - Management</option>
                        <option value="7">Niveau 7 - Coordination</option>
                        <option value="8">Niveau 8 - Responsabilité</option>
                        <option value="9">Niveau 9 - Direction Supérieure</option>
                        <option value="10">Niveau 10 - Direction Générale</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description_fonction">Description</label>
                    <textarea id="description_fonction" name="description_fonction" rows="3" 
                              placeholder="Description détaillée de la fonction"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="est_actif" name="est_actif" value="1" checked>
                        <span class="checkmark"></span>
                        Fonction active
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('addFonctionModal')">
                Annuler
            </button>
            <button type="button" class="btn btn-success" onclick="submitAddFonction()">
                <i class="fas fa-save"></i> Créer
            </button>
        </div>
    </div>
</div>

<script>
// Variables globales
let fonctions = [];

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadFonctionsData();
});

function loadFonctionsData() {
    const tbody = document.getElementById('fonctionsTableBody');
    if (!tbody) return;

    // Afficher un loader pendant le chargement
    tbody.innerHTML = `
        <tr>
            <td colspan="7" style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                <br>Chargement des fonctions...
            </td>
        </tr>
    `;

    // Simuler le chargement depuis la base de données
    setTimeout(() => {
        // Données simulées - à remplacer par un appel AJAX vers la base
        fonctions = [
            { fonction_id: 1, code_fonction: 'DIR_FILIERE', libelle_fonction: 'Directeur de Filière', 
              niveau_responsabilite: 10, description: 'Direction d\'une filière d\'enseignement', est_actif: 1 },
            { fonction_id: 2, code_fonction: 'RESP_MASTER', libelle_fonction: 'Responsable Master', 
              niveau_responsabilite: 9, description: 'Responsabilité du cycle Master', est_actif: 1 },
            { fonction_id: 3, code_fonction: 'RESP_LICENCE', libelle_fonction: 'Responsable Licence', 
              niveau_responsabilite: 8, description: 'Responsabilité du cycle Licence', est_actif: 1 },
            { fonction_id: 4, code_fonction: 'COORD_PEDAGO', libelle_fonction: 'Coordinateur Pédagogique', 
              niveau_responsabilite: 7, description: 'Coordination pédagogique', est_actif: 1 },
            { fonction_id: 5, code_fonction: 'ADMIN_SCOL', libelle_fonction: 'Administrateur Scolarité', 
              niveau_responsabilite: 6, description: 'Administration de la scolarité', est_actif: 1 }
        ];
        
        displayFonctionsInTable();
    }, 1000);
}

function displayFonctionsInTable() {
    const tbody = document.getElementById('fonctionsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    if (fonctions.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                <i class="fas fa-user-tie" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                Aucune fonction trouvée
            </td>
        `;
        tbody.appendChild(row);
        return;
    }
    
    fonctions.forEach(fonction => {
        const row = createFonctionRow(fonction);
        tbody.appendChild(row);
    });
}

function createFonctionRow(fonction) {
    const row = document.createElement('tr');
    
    const statusBadge = fonction.est_actif ? 
        '<span class="badge badge-success">Active</span>' : 
        '<span class="badge badge-secondary">Inactive</span>';
    
    const niveauBadge = `<span class="badge badge-info">Niveau ${fonction.niveau_responsabilite}</span>`;
    
    row.innerHTML = `
        <td>${fonction.fonction_id}</td>
        <td><strong>${fonction.code_fonction}</strong></td>
        <td>${fonction.libelle_fonction}</td>
        <td>${niveauBadge}</td>
        <td>${fonction.description || '-'}</td>
        <td>${statusBadge}</td>
        <td>
            <button class="action-btn btn-edit" onclick="editFonction(${fonction.fonction_id})" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn btn-delete" onclick="deleteFonction(${fonction.fonction_id})" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

function submitAddFonction() {
    const form = document.getElementById('addFonctionForm');
    const formData = new FormData(form);
    
    const code_fonction = formData.get('code_fonction');
    const libelle_fonction = formData.get('libelle_fonction');
    const niveau_responsabilite = formData.get('niveau_responsabilite');

    // Validation
    if (!code_fonction || !libelle_fonction || !niveau_responsabilite) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    // Simuler l'ajout - à remplacer par un appel AJAX
    const newFonction = {
        fonction_id: fonctions.length + 1,
        code_fonction: code_fonction,
        libelle_fonction: libelle_fonction,
        niveau_responsabilite: parseInt(niveau_responsabilite),
        description: formData.get('description_fonction') || null,
        est_actif: formData.get('est_actif') ? 1 : 0
    };
    
    fonctions.push(newFonction);
    
    // Fermer le modal et réinitialiser le formulaire
    closeModal('addFonctionModal');
    form.reset();
    
    // Afficher le message de succès
    showNotification('Fonction créée avec succès', 'success');
    
    // Recharger le tableau
    displayFonctionsInTable();
}

function editFonction(fonctionId) {
    const fonction = fonctions.find(f => f.fonction_id === fonctionId);
    if (fonction) {
        showNotification(`Édition de la fonction: ${fonction.libelle_fonction}`, 'info');
        console.log('Edit fonction:', fonction);
        // Ici vous pourrez implémenter un modal d'édition
    }
}

function deleteFonction(fonctionId) {
    const fonction = fonctions.find(f => f.fonction_id === fonctionId);
    if (fonction) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer la fonction "${fonction.libelle_fonction}" ?`)) {
            fonctions = fonctions.filter(f => f.fonction_id !== fonctionId);
            displayFonctionsInTable();
            showNotification('Fonction supprimée avec succès', 'success');
        }
    }
}
</script> 