<?php
// gestion_menus.php
/*require_once '../../../config/database.php';*/

// Gestion des actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save_permissions':
            $groupe_id = $_POST['groupe_id'] ?? '';
            $traitements = $_POST['traitements'] ?? [];
            
            if (!empty($groupe_id)) {
                try {
                    // Commencer une transaction
                    $pdo->beginTransaction();
                    
                    // Supprimer les anciennes associations pour ce groupe
                    $stmt = $pdo->prepare("DELETE FROM rattacher WHERE id_gu = ?");
                    $stmt->execute([$groupe_id]);
                    
                    // Insérer les nouvelles associations
                    if (!empty($traitements)) {
                        $stmt = $pdo->prepare("INSERT INTO rattacher (id_gu, id_trait) VALUES (?, ?)");
                        foreach ($traitements as $traitement_id) {
                            $stmt->execute([$groupe_id, $traitement_id]);
                        }
                    }
                    
                    $pdo->commit();
                    $response = ['success' => true, 'message' => 'Permissions mises à jour avec succès'];
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $response = ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
                }
            } else {
                $response = ['success' => false, 'message' => 'Veuillez sélectionner un groupe'];
            }
            break;
            
        case 'get_permissions':
            $groupe_id = $_POST['groupe_id'] ?? '';
            if (!empty($groupe_id)) {
                try {
                    $stmt = $pdo->prepare("SELECT id_trait FROM rattacher WHERE id_groupe = ?");
                    $stmt->execute([$groupe_id]);
                    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $response = ['success' => true, 'permissions' => $permissions];
                } catch (PDOException $e) {
                    $response = ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
                }
            } else {
                $response = ['success' => false, 'message' => 'Groupe non spécifié'];
            }
            break;
    }
    
    if (isset($response)) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Récupérer les groupes d'utilisateurs
try {
    $stmt = $pdo->query("SELECT * FROM groupe_utilisateur ORDER BY lib_gu");
    $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $groupes = [];
    $error_message = "Erreur lors du chargement des groupes : " . $e->getMessage();
}

// Récupérer tous les traitements
try {
    $stmt = $pdo->query("SELECT * FROM traitement ORDER BY lib_trait");
    $traitements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $traitements = [];
    $error_message = "Erreur lors du chargement des traitements : " . $e->getMessage();
}

// Récupérer les associations existantes pour affichage
try {
    $stmt = $pdo->query("
        SELECT 
            g.id_groupe,
            g.lib_groupe,
            GROUP_CONCAT(t.lib_trait SEPARATOR ', ') as traitements_associes,
            COUNT(r.id_trait) as nb_traitements
        FROM groupe_utilisateur g
        LEFT JOIN rattacher r ON g.id_groupe = r.id_groupe
        LEFT JOIN traitement t ON r.id_trait = t.id_trait
        GROUP BY g.id_groupe, g.lib_groupe
        ORDER BY g.lib_groupe
    ");
    $associations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $associations = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Menus</title>
    <link rel="stylesheet" href="../../../assets/css/admin/admin-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<style>
/* Styles spécifiques à la gestion des menus */
.permission-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.permission-form {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
}

.permission-form h3 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.groupe-selection {
    margin-bottom: 2rem;
}

.traitements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    max-height: 400px;
    overflow-y: auto;
    padding: 1rem;
    border: 2px dashed var(--gray-200);
    border-radius: 8px;
    background: var(--gray-50);
}

.traitement-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--white);
    border-radius: 8px;
    border: 1px solid var(--gray-200);
    transition: var(--transition);
    cursor: pointer;
    user-select: none;
}

.traitement-item:hover {
    background: var(--gray-50);
    border-color: var(--primary-color);
}

.traitement-item.selected {
    background: var(--primary-light);
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.traitement-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.traitement-item label {
    cursor: pointer;
    flex: 1;
    font-weight: 500;
}

.associations-overview {
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
}

.associations-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-50);
}

.associations-header h3 {
    color: var(--primary-color);
    margin: 0;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.association-item {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-100);
    transition: var(--transition);
}

.association-item:last-child {
    border-bottom: none;
}

.association-item:hover {
    background: var(--gray-50);
}

.association-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.groupe-name {
    font-weight: 600;
    color: var(--gray-900);
    font-size: 1.1rem;
}

.traitements-count {
    background: var(--primary-color);
    color: var(--white);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.traitements-list {
    color: var(--gray-600);
    font-size: 0.9rem;
    line-height: 1.4;
}

.no-permissions {
    color: var(--gray-400);
    font-style: italic;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
}

.select-all-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: var(--gray-50);
    border-radius: 8px;
    border: 1px solid var(--gray-200);
}

.loading-state {
    display: none;
    text-align: center;
    padding: 2rem;
    color: var(--gray-500);
}

.loading-state i {
    font-size: 2rem;
    margin-bottom: 1rem;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .permission-container {
        grid-template-columns: 1fr;
    }
    
    .traitements-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<body>
    <!-- Header de configuration -->
    <div class="config-header">
        <div class="config-header-left">
            <button class="back-btn" onclick="history.back()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="config-title">
                <h2>Gestion des Menus</h2>
                <p>Configuration des permissions et accès par groupe d'utilisateurs</p>
            </div>
        </div>
        <button class="btn btn-info" onclick="refreshAssociations()">
            <i class="fas fa-sync-alt"></i>
            Actualiser
        </button>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?= $error_message ?>
        </div>
    <?php endif; ?>

    <div class="permission-container">
        <!-- Formulaire d'attribution des permissions -->
        <div class="permission-form">
            <h3>
                <i class="fas fa-users-cog"></i>
                Attribution des Permissions
            </h3>
            
            <form id="permissionForm">
                <div class="groupe-selection">
                    <label for="groupeSelect" class="required">Groupe d'utilisateurs</label>
                    <select id="groupeSelect" name="groupe_id" required onchange="loadGroupePermissions()">
                        <option value="">Sélectionner un groupe</option>
                        <?php foreach ($groupes as $groupe): ?>
                            <option value="<?= $groupe['id_groupe'] ?>">
                                <?= htmlspecialchars($groupe['lib_groupe']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="loading-state" id="loadingState">
                    <i class="fas fa-spinner"></i>
                    <p>Chargement des permissions...</p>
                </div>

                <div id="traitementsSection" style="display: none;">
                    <h4>Traitements disponibles</h4>
                    <div class="select-all-actions">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="selectAllTraitements()">
                            <i class="fas fa-check-square"></i>
                            Tout sélectionner
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAllTraitements()">
                            <i class="fas fa-square"></i>
                            Tout désélectionner
                        </button>
                    </div>
                    
                    <div class="traitements-grid" id="traitementsGrid">
                        <?php foreach ($traitements as $traitement): ?>
                            <div class="traitement-item" onclick="toggleTraitement(<?= $traitement['id_trait'] ?>)">
                                <input type="checkbox" 
                                       id="trait_<?= $traitement['id_trait'] ?>" 
                                       name="traitements[]" 
                                       value="<?= $traitement['id_trait'] ?>"
                                       onchange="updateTraitementStyle(this)">
                                <label for="trait_<?= $traitement['id_trait'] ?>">
                                    <?= htmlspecialchars($traitement['lib_trait']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-undo"></i>
                        Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Enregistrer les permissions
                    </button>
                </div>
            </form>
        </div>

        <!-- Aperçu des associations -->
        <div class="associations-overview">
            <div class="associations-header">
                <h3>
                    <i class="fas fa-list-alt"></i>
                    Associations Actuelles
                </h3>
            </div>
            <div id="associationsList">
                <?php if (empty($associations)): ?>
                    <div class="association-item">
                        <div class="no-permissions">Aucune association configurée</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($associations as $association): ?>
                        <div class="association-item">
                            <div class="association-header">
                                <span class="groupe-name"><?= htmlspecialchars($association['lib_groupe']) ?></span>
                                <span class="traitements-count"><?= $association['nb_traitements'] ?> traitement(s)</span>
                            </div>
                            <div class="traitements-list">
                                <?php if ($association['traitements_associes']): ?>
                                    <?= htmlspecialchars($association['traitements_associes']) ?>
                                <?php else: ?>
                                    <span class="no-permissions">Aucun traitement associé</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentGroupeId = null;
        let isLoading = false;

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
        });

        // Configuration des événements
        function setupEventListeners() {
            const form = document.getElementById('permissionForm');
            form.addEventListener('submit', handleFormSubmit);
        }

        // Charger les permissions d'un groupe
        function loadGroupePermissions() {
            const groupeSelect = document.getElementById('groupeSelect');
            const groupeId = groupeSelect.value;
            
            if (!groupeId) {
                hideTraitementsSection();
                return;
            }

            currentGroupeId = groupeId;
            showLoadingState();
            
            const formData = new FormData();
            formData.append('action', 'get_permissions');
            formData.append('groupe_id', groupeId);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoadingState();
                
                if (data.success) {
                    showTraitementsSection();
                    updateTraitementsSelection(data.permissions);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                hideLoadingState();
                showNotification('Erreur de chargement', 'error');
                console.error('Erreur:', error);
            });
        }

        // Afficher la section des traitements
        function showTraitementsSection() {
            document.getElementById('traitementsSection').style.display = 'block';
        }

        // Masquer la section des traitements
        function hideTraitementsSection() {
            document.getElementById('traitementsSection').style.display = 'none';
            clearAllSelections();
        }

        // Afficher l'état de chargement
        function showLoadingState() {
            isLoading = true;
            document.getElementById('loadingState').style.display = 'block';
        }

        // Masquer l'état de chargement
        function hideLoadingState() {
            isLoading = false;
            document.getElementById('loadingState').style.display = 'none';
        }

        // Mettre à jour la sélection des traitements
        function updateTraitementsSelection(permissions) {
            // Décocher toutes les cases
            const checkboxes = document.querySelectorAll('input[name="traitements[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                updateTraitementStyle(checkbox);
            });

            // Cocher les traitements associés
            permissions.forEach(traitementId => {
                const checkbox = document.getElementById(`trait_${traitementId}`);
                if (checkbox) {
                    checkbox.checked = true;
                    updateTraitementStyle(checkbox);
                }
            });
        }

        // Basculer la sélection d'un traitement
        function toggleTraitement(traitementId) {
            const checkbox = document.getElementById(`trait_${traitementId}`);
            checkbox.checked = !checkbox.checked;
            updateTraitementStyle(checkbox);
        }

        // Mettre à jour le style d'un traitement
        function updateTraitementStyle(checkbox) {
            const item = checkbox.closest('.traitement-item');
            if (checkbox.checked) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        }

        // Sélectionner tous les traitements
        function selectAllTraitements() {
            const checkboxes = document.querySelectorAll('input[name="traitements[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                updateTraitementStyle(checkbox);
            });
        }

        // Désélectionner tous les traitements
        function deselectAllTraitements() {
            const checkboxes = document.querySelectorAll('input[name="traitements[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                updateTraitementStyle(checkbox);
            });
        }

        // Effacer toutes les sélections
        function clearAllSelections() {
            const checkboxes = document.querySelectorAll('input[name="traitements[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                updateTraitementStyle(checkbox);
            });
        }

        // Réinitialiser le formulaire
        function resetForm() {
            document.getElementById('groupeSelect').value = '';
            hideTraitementsSection();
            currentGroupeId = null;
        }

        // Gérer la soumission du formulaire
        function handleFormSubmit(e) {
            e.preventDefault();
            
            if (isLoading) return;
            
            const formData = new FormData(e.target);
            formData.append('action', 'save_permissions');
            
            // Validation
            const groupeId = formData.get('groupe_id');
            if (!groupeId) {
                showNotification('Veuillez sélectionner un groupe', 'warning');
                return;
            }

            const selectedTraitements = formData.getAll('traitements[]');
            
            if (selectedTraitements.length === 0) {
                if (!confirm('Aucun traitement sélectionné. Voulez-vous supprimer toutes les permissions pour ce groupe ?')) {
                    return;
                }
            }

            // Envoi des données
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    refreshAssociations();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Erreur lors de la sauvegarde', 'error');
                console.error('Erreur:', error);
            });
        }

        // Actualiser les associations
        function refreshAssociations() {
            window.location.reload();
        }

        // Fonction de notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 
                                      type === 'error' ? 'exclamation-circle' : 
                                      type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
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

        // Gestion du clavier
        document.addEventListener('keydown', function(e) {
            // Ctrl+S pour sauvegarder
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const form = document.getElementById('permissionForm');
                form.dispatchEvent(new Event('submit'));
            }
            
            // Escape pour réinitialiser
            if (e.key === 'Escape') {
                resetForm();
            }
        });
    </script>
</body>
</html>
 