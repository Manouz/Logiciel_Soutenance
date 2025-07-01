<?php
// Vérification de l'authentification et des permissions
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
?>

<div class="page-header">
    <h1><i class="fas fa-graduation-cap"></i> Gestion des Unités d'Enseignement</h1>
    <p>Créer et gérer les unités d'enseignement et leurs composantes</p>
</div>

<div class="page-content">
    <div class="config-section">
        <div class="config-header">
            <div class="config-info">
                <h3>Unités d'Enseignement (UE)</h3>
                <p>Gérer les unités d'enseignement et leurs paramètres</p>
            </div>
            <button class="btn btn-success" onclick="openModal('addUeModal')">
                <i class="fas fa-plus"></i> Créer une UE
            </button>
        </div>
        
        <div class="config-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code UE</th>
                        <th>Libellé</th>
                        <th>Niveau</th>
                        <th>Spécialité</th>
                        <th>Crédits</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ueTableBody">
                    <!-- Les données seront chargées dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une UE -->
<div id="addUeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Créer une Unité d'Enseignement</h3>
            <span class="close" onclick="closeModal('addUeModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addUeForm">
                <div class="form-group">
                    <label for="code_ue">Code UE *</label>
                    <input type="text" id="code_ue" name="code_ue" required 
                           placeholder="Ex: UE501" maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="libelle_ue">Libellé *</label>
                    <input type="text" id="libelle_ue" name="libelle_ue" required 
                           placeholder="Ex: Informatique Fondamentale">
                </div>
                
                <div class="form-group">
                    <label for="niveau_id">Niveau d'étude *</label>
                    <select id="niveau_id" name="niveau_id" required>
                        <option value="">Sélectionner un niveau</option>
                        <option value="1">Licence 1</option>
                        <option value="2">Licence 2</option>
                        <option value="3">Licence 3</option>
                        <option value="4">Master 1</option>
                        <option value="5">Master 2</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="specialite_id">Spécialité</label>
                    <select id="specialite_id" name="specialite_id">
                        <option value="">Aucune spécialité</option>
                        <option value="1">Génie Logiciel</option>
                        <option value="2">Intelligence Artificielle</option>
                        <option value="3">Informatique</option>
                        <option value="4">Réseaux et Sécurité</option>
                        <option value="5">Télécommunications</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nombre_credits">Nombre de crédits *</label>
                    <input type="number" id="nombre_credits" name="nombre_credits" 
                           required min="1" max="30" placeholder="1-30">
                </div>
                
                <div class="form-group">
                    <label for="description_ue">Description</label>
                    <textarea id="description_ue" name="description_ue" rows="3" 
                              placeholder="Description de l'unité d'enseignement"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="est_obligatoire" name="est_obligatoire" value="1" checked>
                        <span class="checkmark"></span>
                        UE obligatoire
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="est_actif" name="est_actif" value="1" checked>
                        <span class="checkmark"></span>
                        UE active
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('addUeModal')">
                Annuler
            </button>
            <button type="button" class="btn btn-success" onclick="submitAddUe()">
                <i class="fas fa-save"></i> Créer
            </button>
        </div>
    </div>
</div>

<script>
// Variables globales
let ues = [];

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadUesData();
});

function loadUesData() {
    const tbody = document.getElementById('ueTableBody');
    if (!tbody) return;

    // Afficher un loader pendant le chargement
    tbody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                <br>Chargement des UE...
            </td>
        </tr>
    `;

    // Simuler le chargement depuis la base de données
    setTimeout(() => {
        // Données simulées - à remplacer par un appel AJAX vers la base
        ues = [
            { ue_id: 1, code_ue: 'UE501', libelle_ue: 'Informatique Fondamentale', 
              niveau_id: 5, niveau_libelle: 'Master 2', specialite_id: 1, specialite_libelle: 'Génie Logiciel', 
              nombre_credits: 6, est_obligatoire: 1, est_actif: 1 },
            { ue_id: 2, code_ue: 'UE502', libelle_ue: 'Mathématiques Appliquées', 
              niveau_id: 5, niveau_libelle: 'Master 2', specialite_id: null, specialite_libelle: null, 
              nombre_credits: 4, est_obligatoire: 1, est_actif: 1 },
            { ue_id: 3, code_ue: 'UE503', libelle_ue: 'Langues Étrangères', 
              niveau_id: 5, niveau_libelle: 'Master 2', specialite_id: null, specialite_libelle: null, 
              nombre_credits: 2, est_obligatoire: 0, est_actif: 1 }
        ];
        
        displayUesInTable();
    }, 1000);
}

function displayUesInTable() {
    const tbody = document.getElementById('ueTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    if (ues.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                <i class="fas fa-graduation-cap" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                Aucune UE trouvée
            </td>
        `;
        tbody.appendChild(row);
        return;
    }
    
    ues.forEach(ue => {
        const row = createUeRow(ue);
        tbody.appendChild(row);
    });
}

function createUeRow(ue) {
    const row = document.createElement('tr');
    
    const statusBadge = ue.est_actif ? 
        '<span class="badge badge-success">Active</span>' : 
        '<span class="badge badge-secondary">Inactive</span>';
    
    const obligatoireBadge = ue.est_obligatoire ? 
        '<span class="badge badge-primary">Obligatoire</span>' : 
        '<span class="badge badge-info">Optionnelle</span>';
    
    row.innerHTML = `
        <td>${ue.ue_id}</td>
        <td><strong>${ue.code_ue}</strong></td>
        <td>${ue.libelle_ue}</td>
        <td>${ue.niveau_libelle}</td>
        <td>${ue.specialite_libelle || '-'}</td>
        <td><span class="badge badge-info">${ue.nombre_credits} crédits</span></td>
        <td>${statusBadge} ${obligatoireBadge}</td>
        <td>
            <button class="action-btn btn-edit" onclick="editUe(${ue.ue_id})" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn btn-delete" onclick="deleteUe(${ue.ue_id})" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

function submitAddUe() {
    const form = document.getElementById('addUeForm');
    const formData = new FormData(form);
    
    const code_ue = formData.get('code_ue');
    const libelle_ue = formData.get('libelle_ue');
    const niveau_id = formData.get('niveau_id');
    const nombre_credits = formData.get('nombre_credits');

    // Validation
    if (!code_ue || !libelle_ue || !niveau_id || !nombre_credits) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    // Simuler l'ajout - à remplacer par un appel AJAX
    const newUe = {
        ue_id: ues.length + 1,
        code_ue: code_ue,
        libelle_ue: libelle_ue,
        niveau_id: parseInt(niveau_id),
        niveau_libelle: document.getElementById('niveau_id').options[document.getElementById('niveau_id').selectedIndex].text,
        specialite_id: formData.get('specialite_id') ? parseInt(formData.get('specialite_id')) : null,
        specialite_libelle: formData.get('specialite_id') ? document.getElementById('specialite_id').options[document.getElementById('specialite_id').selectedIndex].text : null,
        nombre_credits: parseInt(nombre_credits),
        est_obligatoire: formData.get('est_obligatoire') ? 1 : 0,
        est_actif: formData.get('est_actif') ? 1 : 0
    };
    
    ues.push(newUe);
    
    // Fermer le modal et réinitialiser le formulaire
    closeModal('addUeModal');
    form.reset();
    
    // Afficher le message de succès
    showNotification('UE créée avec succès', 'success');
    
    // Recharger le tableau
    displayUesInTable();
}

function editUe(ueId) {
    const ue = ues.find(u => u.ue_id === ueId);
    if (ue) {
        showNotification(`Édition de l'UE: ${ue.libelle_ue}`, 'info');
        console.log('Edit ue:', ue);
        // Ici vous pourrez implémenter un modal d'édition
    }
}

function deleteUe(ueId) {
    const ue = ues.find(u => u.ue_id === ueId);
    if (ue) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'UE "${ue.libelle_ue}" ?`)) {
            ues = ues.filter(u => u.ue_id !== ueId);
            displayUesInTable();
            showNotification('UE supprimée avec succès', 'success');
        }
    }
}
</script> 