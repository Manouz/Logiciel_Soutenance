<?php
// Vérification de l'authentification et des permissions
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
?>

<div class="page-header">
    <h1><i class="fas fa-book"></i> Gestion des ECUE</h1>
    <p>Gérer les Éléments Constitutifs des Unités d'Enseignement</p>
</div>

<div class="page-content">
    <div class="config-section">
        <div class="config-header">
            <div class="config-info">
                <h3>Éléments Constitutifs (ECUE)</h3>
                <p>Créer et gérer les modules d'enseignement</p>
            </div>
            <button class="btn btn-success" onclick="openModal('addEcueModal')">
                <i class="fas fa-plus"></i> Créer un ECUE
            </button>
        </div>
        
        <div class="config-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code ECUE</th>
                        <th>Libellé</th>
                        <th>UE</th>
                        <th>Crédits</th>
                        <th>Coefficient</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ecueTableBody">
                    <!-- Les données seront chargées dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un ECUE -->
<div id="addEcueModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Créer un ECUE</h3>
            <span class="close" onclick="closeModal('addEcueModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addEcueForm">
                <div class="form-group">
                    <label for="code_ecue">Code ECUE *</label>
                    <input type="text" id="code_ecue" name="code_ecue" required 
                           placeholder="Ex: INFO501" maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="libelle_ecue">Libellé *</label>
                    <input type="text" id="libelle_ecue" name="libelle_ecue" required 
                           placeholder="Ex: Développement Web Avancé">
                </div>
                
                <div class="form-group">
                    <label for="ue_id">Unité d'Enseignement *</label>
                    <select id="ue_id" name="ue_id" required>
                        <option value="">Sélectionner une UE</option>
                        <option value="1">UE501 - Informatique</option>
                        <option value="2">UE502 - Mathématiques</option>
                        <option value="3">UE503 - Langues</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_credits">Nombre de crédits *</label>
                        <input type="number" id="nombre_credits" name="nombre_credits" 
                               required min="1" max="10" placeholder="1-10">
                    </div>
                    
                    <div class="form-group">
                        <label for="coefficient_evaluation">Coefficient</label>
                        <input type="number" id="coefficient_evaluation" name="coefficient_evaluation" 
                               step="0.01" min="0.1" max="5" value="1.00">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description_ecue">Description</label>
                    <textarea id="description_ecue" name="description_ecue" rows="3" 
                              placeholder="Description du contenu de l'ECUE"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="est_actif" name="est_actif" value="1" checked>
                        <span class="checkmark"></span>
                        ECUE actif
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('addEcueModal')">
                Annuler
            </button>
            <button type="button" class="btn btn-success" onclick="submitAddEcue()">
                <i class="fas fa-save"></i> Créer
            </button>
        </div>
    </div>
</div>

<script>
// Variables globales
let ecues = [];

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadEcuesData();
});

function loadEcuesData() {
    const tbody = document.getElementById('ecueTableBody');
    if (!tbody) return;

    // Afficher un loader pendant le chargement
    tbody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                <br>Chargement des ECUE...
            </td>
        </tr>
    `;

    // Simuler le chargement depuis la base de données
    setTimeout(() => {
        // Données simulées - à remplacer par un appel AJAX vers la base
        ecues = [
            { ecue_id: 1, code_ecue: 'INFO501', libelle_ecue: 'Développement Web Avancé', 
              ue_id: 1, ue_libelle: 'UE501 - Informatique', nombre_credits: 3, coefficient_evaluation: 1.00, est_actif: 1 },
            { ecue_id: 2, code_ecue: 'INFO502', libelle_ecue: 'Bases de Données', 
              ue_id: 1, ue_libelle: 'UE501 - Informatique', nombre_credits: 4, coefficient_evaluation: 1.50, est_actif: 1 },
            { ecue_id: 3, code_ecue: 'MATH501', libelle_ecue: 'Statistiques', 
              ue_id: 2, ue_libelle: 'UE502 - Mathématiques', nombre_credits: 2, coefficient_evaluation: 1.00, est_actif: 1 }
        ];
        
        displayEcuesInTable();
    }, 1000);
}

function displayEcuesInTable() {
    const tbody = document.getElementById('ecueTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    if (ecues.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                <i class="fas fa-book" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                Aucun ECUE trouvé
            </td>
        `;
        tbody.appendChild(row);
        return;
    }
    
    ecues.forEach(ecue => {
        const row = createEcueRow(ecue);
        tbody.appendChild(row);
    });
}

function createEcueRow(ecue) {
    const row = document.createElement('tr');
    
    const statusBadge = ecue.est_actif ? 
        '<span class="badge badge-success">Actif</span>' : 
        '<span class="badge badge-secondary">Inactif</span>';
    
    row.innerHTML = `
        <td>${ecue.ecue_id}</td>
        <td><strong>${ecue.code_ecue}</strong></td>
        <td>${ecue.libelle_ecue}</td>
        <td>${ecue.ue_libelle}</td>
        <td><span class="badge badge-info">${ecue.nombre_credits} crédits</span></td>
        <td>${ecue.coefficient_evaluation}</td>
        <td>${statusBadge}</td>
        <td>
            <button class="action-btn btn-edit" onclick="editEcue(${ecue.ecue_id})" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn btn-delete" onclick="deleteEcue(${ecue.ecue_id})" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

function submitAddEcue() {
    const form = document.getElementById('addEcueForm');
    const formData = new FormData(form);
    
    const code_ecue = formData.get('code_ecue');
    const libelle_ecue = formData.get('libelle_ecue');
    const ue_id = formData.get('ue_id');
    const nombre_credits = formData.get('nombre_credits');

    // Validation
    if (!code_ecue || !libelle_ecue || !ue_id || !nombre_credits) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    // Simuler l'ajout - à remplacer par un appel AJAX
    const newEcue = {
        ecue_id: ecues.length + 1,
        code_ecue: code_ecue,
        libelle_ecue: libelle_ecue,
        ue_id: parseInt(ue_id),
        ue_libelle: document.getElementById('ue_id').options[document.getElementById('ue_id').selectedIndex].text,
        nombre_credits: parseInt(nombre_credits),
        coefficient_evaluation: parseFloat(formData.get('coefficient_evaluation')) || 1.00,
        est_actif: formData.get('est_actif') ? 1 : 0
    };
    
    ecues.push(newEcue);
    
    // Fermer le modal et réinitialiser le formulaire
    closeModal('addEcueModal');
    form.reset();
    
    // Afficher le message de succès
    showNotification('ECUE créé avec succès', 'success');
    
    // Recharger le tableau
    displayEcuesInTable();
}

function editEcue(ecueId) {
    const ecue = ecues.find(e => e.ecue_id === ecueId);
    if (ecue) {
        showNotification(`Édition de l'ECUE: ${ecue.libelle_ecue}`, 'info');
        console.log('Edit ecue:', ecue);
        // Ici vous pourrez implémenter un modal d'édition
    }
}

function deleteEcue(ecueId) {
    const ecue = ecues.find(e => e.ecue_id === ecueId);
    if (ecue) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'ECUE "${ecue.libelle_ecue}" ?`)) {
            ecues = ecues.filter(e => e.ecue_id !== ecueId);
            displayEcuesInTable();
            showNotification('ECUE supprimé avec succès', 'success');
        }
    }
}
</script> 