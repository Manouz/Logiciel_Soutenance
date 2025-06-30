<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../assets/css/admin/admin-style.css">
    <title>Gestion des Traitements</title>
</head>
<style>
.data-table { width: 100%; margin-top: 20px; }
.data-table table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 12px; text-align: left; border: 1px solid #ddd; }
.data-table th { background: #f5f5f5; }
.data-table tr:hover { background: #f9f9f9; }

.bulk-actions {
    margin: 20px 0;
    display: none;
}

.bulk-actions button {
    margin-right: 10px;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-delete { background: #dc3545; color: white; }
.btn-print { background: #6c757d; color: white; }
.btn-export { background: #28a745; color: white; }

.pagination {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 5px;
}

.pagination button {
    padding: 5px 10px;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
}

.pagination button.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination-ellipsis {
    padding: 5px 10px;
    color: #6c757d;
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 10px;
    background: #f8f9fa;
}

.total-count {
    font-size: 14px;
    color: #6c757d;
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px;
    border-radius: 4px;
    color: white;
    z-index: 1000;
}

.notification-success { background: #28a745; }
.notification-error { background: #dc3545; }
</style>
<body>
    <div class="config-header">
        <button class="back-btn" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="config-title">
            <h2>Gestion des Traitements</h2>
            <p>Gérer les traitements du système</p>
        </div>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i>
            Ajouter un traitement
        </button>
    </div>

    <!-- Actions groupées -->
    <div class="bulk-actions">
        <button class="btn-delete" onclick="deleteSelected()">
            <i class="fas fa-trash"></i> Supprimer
        </button>
        <button class="btn-print" onclick="printSelected()">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button class="btn-export" onclick="exportSelected()">
            <i class="fas fa-file-export"></i> Exporter
        </button>
    </div>

    <!-- Tableau des traitements -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                    </th>
                    <!--<th>ID</th>-->
                    <th>Libellé</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="traitementTableBody">
                <!-- Les données seront chargées dynamiquement -->
            </tbody>
        </table>
        
        <div class="table-footer">
            <div class="total-count">
                Total : <span id="totalCount">0</span> traitement(s)
            </div>
            <div class="pagination" id="pagination">
                <!-- La pagination sera générée dynamiquement -->
            </div>
        </div>
    </div>

    <!-- Modal pour ajouter/modifier -->
    <div class="modal" id="traitementModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Ajouter un traitement</h3>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="traitementForm">
                    <input type="hidden" id="traitementId" name="id_trait">
                    <div class="form-group">
                        <label for="lib_trait">Libellé *</label>
                        <input type="text" id="lib_trait" name="lib_trait" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                <button class="btn btn-primary" onclick="saveTraitement()">Enregistrer</button>
            </div>
        </div>
    </div>

<script>
let currentPage = 1;
let selectedIds = new Set();

// Charger les traitements
function loadTraitements(page = 1) {
    currentPage = page;
    fetch(`../../../includes/traitement_actions.php?action=read&page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTraitementTable(data.data);
                updatePagination(data.total);
                document.getElementById('totalCount').textContent = data.total;
            } else {
                showNotification('Erreur lors du chargement', 'error');
            }
        })
        .catch(() => showNotification('Erreur de connexion au serveur', 'error'));
}

// Afficher les traitements
function renderTraitementTable(data) {
    const tbody = document.getElementById('traitementTableBody');
    tbody.innerHTML = '';
    
    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Aucun traitement trouvé</td></tr>';
        return;
    }
    
    data.forEach(traitement => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="checkbox" class="row-checkbox" 
                       value="${traitement.id_trait}"
                       onchange="toggleRow(this)"
                       ${selectedIds.has(parseInt(traitement.id_trait)) ? 'checked' : ''}>
            </td>
            <!--<td>${traitement.id_trait}</td>-->
            <td>${traitement.lib_trait}</td>
            <td>
                <button class="btn-icon" onclick="editTraitement(${traitement.id_trait}, '${traitement.lib_trait}')">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon" onclick="deleteTraitement(${traitement.id_trait})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
    updateBulkActionsVisibility();
}

// Mettre à jour la pagination
function updatePagination(total) {
    const totalPages = Math.ceil(total / 5);
    const pagination = document.getElementById('pagination');
    let html = '';

    // Bouton précédent
    html += `<button onclick="loadTraitements(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
        <i class="fas fa-chevron-left"></i>
    </button>`;

    // Afficher les numéros de page avec ellipsis
    const range = 2; // Nombre de pages à afficher de chaque côté de la page courante
    let start = Math.max(1, currentPage - range);
    let end = Math.min(totalPages, currentPage + range);

    // Ajuster start et end pour toujours afficher 5 pages si possible
    if (end - start < 4) {
        if (start === 1) {
            end = Math.min(5, totalPages);
        } else if (end === totalPages) {
            start = Math.max(1, totalPages - 4);
        }
    }

    // Première page
    if (start > 1) {
        html += `<button onclick="loadTraitements(1)">1</button>`;
        if (start > 2) html += `<span class="pagination-ellipsis">...</span>`;
    }

    // Pages numérotées
    for (let i = start; i <= end; i++) {
        html += `<button class="${currentPage === i ? 'active' : ''}" 
                        onclick="loadTraitements(${i})">${i}</button>`;
    }

    // Dernière page
    if (end < totalPages) {
        if (end < totalPages - 1) html += `<span class="pagination-ellipsis">...</span>`;
        html += `<button onclick="loadTraitements(${totalPages})">${totalPages}</button>`;
    }

    // Bouton suivant
    html += `<button onclick="loadTraitements(${currentPage + 1})" 
                     ${currentPage === totalPages ? 'disabled' : ''}>
        <i class="fas fa-chevron-right"></i>
    </button>`;

    pagination.innerHTML = html;
    
    // Mettre à jour le compteur total
    document.getElementById('totalCount').textContent = total;
}

// Gestion des sélections
function toggleSelectAll(checkbox) {
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    rowCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        toggleRow(cb);
    });
}

function toggleRow(checkbox) {
    const id = parseInt(checkbox.value);
    if (checkbox.checked) {
        selectedIds.add(id);
    } else {
        selectedIds.delete(id);
    }
    updateBulkActionsVisibility();
}

function updateBulkActionsVisibility() {
    const bulkActions = document.querySelector('.bulk-actions');
    bulkActions.style.display = selectedIds.size > 0 ? 'block' : 'none';
    
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    selectAll.checked = rowCheckboxes.length > 0 && 
                       Array.from(rowCheckboxes).every(cb => cb.checked);
}

// Actions groupées
function deleteSelected() {
    if (!confirm(`Voulez-vous vraiment supprimer ${selectedIds.size} traitement(s) ?`)) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_multiple');
    formData.append('ids', Array.from(selectedIds).join(','));

    fetch('../../../includes/traitement_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Traitements supprimés avec succès', 'success');
            selectedIds.clear();
            loadTraitements(currentPage);
        } else {
            showNotification('Erreur lors de la suppression', 'error');
        }
    })
    .catch(() => showNotification('Erreur de connexion', 'error'));
}

function printSelected() {
    const selectedData = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => {
        const row = cb.closest('tr');
        return {
            // id: row.cells[1].textContent, // supprimé
            libelle: row.cells[1].textContent
        };
    });

    const printWindow = window.open('', '', 'height=500,width=800');
    printWindow.document.write(`
        <html>
        <head>
            <title>Traitements sélectionnés</title>
            <style>
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; }
            </style>
        </head>
        <body>
            <h2>Traitements sélectionnés</h2>
            <table>
                <thead>
                    <tr>
                        <!--<th>ID</th>-->
                        <th>Libellé</th>
                    </tr>
                </thead>
                <tbody>
                    ${selectedData.map(item => `
                        <tr>
                            <!--<td>${item.id}</td>-->
                            <td>${item.libelle}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function exportSelected() {
    const selectedData = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => {
        const row = cb.closest('tr');
        return {
            // ID: row.cells[1].textContent, // supprimé
            Libelle: row.cells[1].textContent
        };
    });

    const csv = [
        ['Libellé'],
        ...selectedData.map(item => [item.Libelle])
    ].map(row => row.join(',')).join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', 'traitements.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Gestion du modal
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Ajouter un traitement';
    document.getElementById('traitementForm').reset();
    document.getElementById('traitementId').value = '';
    document.getElementById('traitementModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('traitementModal').style.display = 'none';
}

function editTraitement(id, libelle) {
    document.getElementById('modalTitle').textContent = 'Modifier le traitement';
    document.getElementById('traitementId').value = id;
    document.getElementById('lib_trait').value = libelle;
    document.getElementById('traitementModal').style.display = 'block';
}

function saveTraitement() {
    const form = document.getElementById('traitementForm');
    const formData = new FormData(form);
    formData.append('action', form.traitementId.value ? 'update' : 'create');

    fetch('../../../includes/traitement_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal();
            loadTraitements(currentPage);
        } else {
            showNotification(data.message || 'Erreur lors de l\'enregistrement', 'error');
        }
    })
    .catch(() => showNotification('Erreur de connexion au serveur', 'error'));
}

// Notifications
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialisation
window.addEventListener('DOMContentLoaded', () => {
    loadTraitements(1);
});

// Fermer le modal si on clique en dehors
window.onclick = function(event) {
    const modal = document.getElementById('traitementModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>
</body>
</html>



