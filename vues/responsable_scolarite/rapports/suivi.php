<?php
/**
 * Suivi des Rapports - Responsable Scolarité
 * Système de Validation Académique - UFHB Cocody
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';

SessionManager::start();

if (!SessionManager::isLoggedIn()) {
    redirectTo('../../../login.php');
}

$userRole = SessionManager::getUserRole();
if ($userRole !== 'Responsable Scolarité') {
    redirectTo('../../../login.php');
}

$userId = SessionManager::getUserId();
$userName = SessionManager::getUserName();

try {
    $db = Database::getInstance();
    
    // Traitement des actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'affecter_encadrant':
                    try {
                        $rapport_id = $_POST['rapport_id'];
                        $encadrant_id = $_POST['encadrant_id'];
                        
                        $db->update('rapports', [
                            'encadrant_id' => $encadrant_id,
                            'statut_id' => 10 // EN_VERIFICATION
                        ], 'rapport_id = ?', [$rapport_id]);
                        
                        $success_message = "Encadrant affecté avec succès !";
                    } catch (Exception $e) {
                        $error_message = "Erreur lors de l'affectation : " . $e->getMessage();
                    }
                    break;
                    
                case 'modifier_statut':
                    try {
                        $rapport_id = $_POST['rapport_id'];
                        $nouveau_statut = $_POST['nouveau_statut'];
                        $commentaire = $_POST['commentaire'] ?? '';
                        
                        $db->update('rapports', [
                            'statut_id' => $nouveau_statut,
                            'commentaire_validation' => $commentaire
                        ], 'rapport_id = ?', [$rapport_id]);
                        
                        $success_message = "Statut modifié avec succès !";
                    } catch (Exception $e) {
                        $error_message = "Erreur lors de la modification : " . $e->getMessage();
                    }
                    break;
            }
        }
    }
    
    // Statistiques des rapports
    $totalRapports = $db->count('rapports');
    $rapportsDeposes = $db->count('rapports', 'statut_id = 9'); // DEPOSE
    $rapportsEnVerification = $db->count('rapports', 'statut_id = 10'); // EN_VERIFICATION
    $rapportsValides = $db->count('rapports', 'statut_id = 11'); // VALIDE
    $rapportsRejetes = $db->count('rapports', 'statut_id = 12'); // REJETE
    
    // Rapports avec détails complets
    $rapports = $db->fetchAll("
        SELECT r.*, ip.nom, ip.prenoms, e.numero_etudiant,
               niv.nom as niveau_nom, s.nom as specialite_nom,
               st.nom as statut_nom,
               enc_ip.nom as encadrant_nom, enc_ip.prenoms as encadrant_prenoms
        FROM rapports r
        JOIN etudiants et ON r.etudiant_id = et.utilisateur_id
        JOIN informations_personnelles ip ON et.utilisateur_id = ip.utilisateur_id
        JOIN niveaux niv ON et.niveau_id = niv.niveau_id
        JOIN specialites s ON et.specialite_id = s.specialite_id
        JOIN statuts st ON r.statut_id = st.statut_id
        LEFT JOIN utilisateurs enc_u ON r.encadrant_id = enc_u.utilisateur_id
        LEFT JOIN informations_personnelles enc_ip ON enc_u.utilisateur_id = enc_ip.utilisateur_id
        ORDER BY r.date_depot DESC
    ");
    
    // Encadrants disponibles
    $encadrants = $db->fetchAll("
        SELECT u.utilisateur_id, ip.nom, ip.prenoms
        FROM utilisateurs u
        JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        JOIN roles r ON u.role_id = r.role_id
        WHERE r.nom = 'Enseignant' AND u.est_actif = 1
        ORDER BY ip.nom, ip.prenoms
    ");
    
    // Statuts disponibles
    $statuts = $db->fetchAll("
        SELECT * FROM statuts 
        WHERE statut_id IN (9, 10, 11, 12) 
        ORDER BY statut_id
    ");
    
} catch (Exception $e) {
    error_log("Erreur suivi rapports: " . $e->getMessage());
    $rapports = [];
    $encadrants = [];
    $statuts = [];
    $totalRapports = 0;
    $rapportsDeposes = 0;
    $rapportsEnVerification = 0;
    $rapportsValides = 0;
    $rapportsRejetes = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Rapports - Responsable Scolarité</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: rgb(0, 51, 41);
            --primary-light: rgba(0, 51, 41, 0.1);
            --primary-dark: rgb(0, 35, 28);
            --secondary-color: #10b981;
            --accent-color: #34d399;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, var(--warning-color), #fbbf24);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, var(--info-color), #60a5fa);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, var(--success-color), var(--accent-color));
        }

        .stat-card:nth-child(5) .stat-icon {
            background: linear-gradient(135deg, var(--error-color), #f87171);
        }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .chart-container {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .chart-header {
            margin-bottom: 1.5rem;
        }

        .chart-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .data-table {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
        }

        .data-table tr:hover {
            background: var(--gray-50);
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-depose {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-verification {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-valide {
            background: #dcfce7;
            color: #166534;
        }

        .badge-rejete {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--gray-500);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .modal-close:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Suivi des Rapports</h1>
            <p>Gestion et suivi des rapports de mémoire et de stage</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($totalRapports) ?></h3>
                    <p>Total rapports</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-upload"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($rapportsDeposes) ?></h3>
                    <p>Rapports déposés</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($rapportsEnVerification) ?></h3>
                    <p>En vérification</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($rapportsValides) ?></h3>
                    <p>Rapports validés</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-times"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($rapportsRejetes) ?></h3>
                    <p>Rapports rejetés</p>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3>Répartition des statuts</h3>
            </div>
            <canvas id="statutsChart" width="400" height="200"></canvas>
        </div>

        <!-- Reports Table -->
        <div class="data-table">
            <div class="table-header">
                <h3>Liste des rapports</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Date de dépôt</th>
                        <th>Encadrant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rapports as $rapport): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($rapport['nom'] . ' ' . $rapport['prenoms']) ?></strong><br>
                                <small><?= htmlspecialchars($rapport['numero_etudiant']) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($rapport['titre']) ?></strong><br>
                                <small><?= htmlspecialchars($rapport['niveau_nom'] . ' - ' . $rapport['specialite_nom']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($rapport['type_rapport']) ?></td>
                            <td><?= formatDateTime($rapport['date_depot']) ?></td>
                            <td>
                                <?php if ($rapport['encadrant_nom']): ?>
                                    <?= htmlspecialchars($rapport['encadrant_nom'] . ' ' . $rapport['encadrant_prenoms']) ?>
                                <?php else: ?>
                                    <em>Non affecté</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $rapport['statut_nom'])) ?>">
                                    <?= htmlspecialchars($rapport['statut_nom']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!$rapport['encadrant_id']): ?>
                                    <button class="btn btn-primary" onclick="affecterEncadrant(<?= $rapport['rapport_id'] ?>)">
                                        <i class="fas fa-user-plus"></i>
                                        Affecter
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-secondary" onclick="modifierStatut(<?= $rapport['rapport_id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                    Statut
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="../index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Retour au tableau de bord
            </a>
        </div>
    </div>

    <!-- Affecter Encadrant Modal -->
    <div id="affecterModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Affecter un encadrant</h3>
                <button class="modal-close" onclick="closeModal('affecterModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="affecterForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="affecter_encadrant">
                    <input type="hidden" name="rapport_id" id="affecter_rapport_id">
                    
                    <div class="form-group">
                        <label for="encadrant_id">Encadrant</label>
                        <select name="encadrant_id" id="encadrant_id" required>
                            <option value="">Sélectionner un encadrant</option>
                            <?php foreach ($encadrants as $encadrant): ?>
                                <option value="<?= $encadrant['utilisateur_id'] ?>">
                                    <?= htmlspecialchars($encadrant['nom'] . ' ' . $encadrant['prenoms']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('affecterModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Affecter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modifier Statut Modal -->
    <div id="statutModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier le statut</h3>
                <button class="modal-close" onclick="closeModal('statutModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="statutForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="modifier_statut">
                    <input type="hidden" name="rapport_id" id="statut_rapport_id">
                    
                    <div class="form-group">
                        <label for="nouveau_statut">Nouveau statut</label>
                        <select name="nouveau_statut" id="nouveau_statut" required>
                            <option value="">Sélectionner un statut</option>
                            <?php foreach ($statuts as $statut): ?>
                                <option value="<?= $statut['statut_id'] ?>">
                                    <?= htmlspecialchars($statut['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="commentaire">Commentaire</label>
                        <textarea name="commentaire" id="commentaire" placeholder="Commentaire sur le changement de statut..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('statutModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Données pour le graphique
        const statutsData = [
            { label: 'Déposés', value: <?= $rapportsDeposes ?>, color: '#f59e0b' },
            { label: 'En vérification', value: <?= $rapportsEnVerification ?>, color: '#3b82f6' },
            { label: 'Validés', value: <?= $rapportsValides ?>, color: '#10b981' },
            { label: 'Rejetés', value: <?= $rapportsRejetes ?>, color: '#ef4444' }
        ];

        // Graphique des statuts
        const statutsCtx = document.getElementById('statutsChart').getContext('2d');
        new Chart(statutsCtx, {
            type: 'doughnut',
            data: {
                labels: statutsData.map(item => item.label),
                datasets: [{
                    data: statutsData.map(item => item.value),
                    backgroundColor: statutsData.map(item => item.color)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function affecterEncadrant(rapport_id) {
            document.getElementById('affecter_rapport_id').value = rapport_id;
            openModal('affecterModal');
        }

        function modifierStatut(rapport_id) {
            document.getElementById('statut_rapport_id').value = rapport_id;
            openModal('statutModal');
        }

        // Fermer modal en cliquant à l'extérieur
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });
    </script>
</body>
</html>
