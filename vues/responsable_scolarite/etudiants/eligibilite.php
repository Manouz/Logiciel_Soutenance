<?php
/**
 * Éligibilité des Étudiants - Responsable Scolarité
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
                case 'calculer_eligibilite':
                    try {
                        // Récupérer tous les étudiants actifs
                        $etudiants = $db->fetchAll("
                            SELECT e.utilisateur_id, e.moyenne_generale,
                                   COUNT(r.rapport_id) as nb_rapports,
                                   COUNT(CASE WHEN r.statut_id = 11 THEN 1 END) as nb_rapports_valides
                            FROM etudiants e
                            LEFT JOIN rapports r ON e.utilisateur_id = r.etudiant_id
                            WHERE e.est_actif = 1
                            GROUP BY e.utilisateur_id
                        ");
                        
                        foreach ($etudiants as $etudiant) {
                            $eligible = 1; // NON_ELIGIBLE par défaut
                            
                            // Critères d'éligibilité
                            if ($etudiant['moyenne_generale'] >= 10 && 
                                $etudiant['nb_rapports_valides'] > 0) {
                                $eligible = 5; // ELIGIBLE
                            }
                            
                            $db->update('etudiants', ['statut_eligibilite' => $eligible], 'utilisateur_id = ?', [$etudiant['utilisateur_id']]);
                        }
                        
                        $success_message = "Éligibilité calculée pour tous les étudiants !";
                    } catch (Exception $e) {
                        $error_message = "Erreur lors du calcul : " . $e->getMessage();
                    }
                    break;
                    
                case 'modifier_eligibilite':
                    try {
                        $etudiant_id = $_POST['etudiant_id'];
                        $eligible = (int)$_POST['eligible'];
                        $commentaire = $_POST['commentaire'] ?? '';
                        
                        $db->update('etudiants', [
                            'statut_eligibilite' => $eligible,
                            'commentaire_eligibilite' => $commentaire
                        ], 'utilisateur_id = ?', [$etudiant_id]);
                        
                        $success_message = "Éligibilité modifiée avec succès !";
                    } catch (Exception $e) {
                        $error_message = "Erreur lors de la modification : " . $e->getMessage();
                    }
                    break;
            }
        }
    }
    
    // Récupérer les statistiques d'éligibilité
    $totalEtudiants = $db->count('etudiants', 'est_actif = 1');
    $etudiantsEligibles = $db->count('etudiants', 'statut_eligibilite = 5 AND est_actif = 1'); // ELIGIBLE
    $etudiantsNonEligibles = $db->count('etudiants', 'statut_eligibilite != 5 AND est_actif = 1');
    $tauxEligibilite = $totalEtudiants > 0 ? round(($etudiantsEligibles / $totalEtudiants) * 100, 1) : 0;
    
    // Étudiants avec détails d'éligibilité
    $etudiants = $db->fetchAll("
        SELECT e.*, ip.nom, ip.prenoms, niv.nom as niveau_nom, s.nom as specialite_nom,
               COUNT(r.rapport_id) as nb_rapports,
               COUNT(CASE WHEN r.statut_id = 11 THEN 1 END) as nb_rapports_valides,
               COUNT(CASE WHEN sout.statut_id = 15 THEN 1 END) as nb_soutenances
        FROM etudiants e
        JOIN informations_personnelles ip ON e.utilisateur_id = ip.utilisateur_id
        JOIN niveaux niv ON e.niveau_id = niv.niveau_id
        JOIN specialites s ON e.specialite_id = s.specialite_id
        LEFT JOIN rapports r ON e.utilisateur_id = r.etudiant_id
        LEFT JOIN soutenances sout ON e.utilisateur_id = sout.etudiant_id
        WHERE e.est_actif = 1
        GROUP BY e.utilisateur_id
        ORDER BY e.statut_eligibilite DESC, e.moyenne_generale DESC
    ");
    
    // Statistiques par niveau
    $statsNiveaux = $db->fetchAll("
        SELECT n.nom as niveau, 
               COUNT(e.utilisateur_id) as total,
               COUNT(CASE WHEN e.statut_eligibilite = 5 THEN 1 END) as eligibles
        FROM niveaux n
        LEFT JOIN etudiants e ON n.niveau_id = e.niveau_id AND e.est_actif = 1
        GROUP BY n.niveau_id, n.nom
        ORDER BY n.nom
    ");
    
} catch (Exception $e) {
    error_log("Erreur éligibilité: " . $e->getMessage());
    $etudiants = [];
    $statsNiveaux = [];
    $totalEtudiants = 0;
    $etudiantsEligibles = 0;
    $etudiantsNonEligibles = 0;
    $tauxEligibilite = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éligibilité des Étudiants - Responsable Scolarité</title>
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
            background: linear-gradient(135deg, var(--success-color), var(--accent-color));
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, var(--error-color), #f87171);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, var(--info-color), #60a5fa);
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

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
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

        .badge-eligible {
            background: #dcfce7;
            color: #166534;
        }

        .badge-non-eligible {
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
            max-width: 500px;
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
            
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
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
            <h1><i class="fas fa-check-circle"></i> Vérification d'Éligibilité</h1>
            <p>Gestion et vérification de l'éligibilité des étudiants à la soutenance</p>
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
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($totalEtudiants) ?></h3>
                    <p>Total étudiants</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($etudiantsEligibles) ?></h3>
                    <p>Étudiants éligibles</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-times"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($etudiantsNonEligibles) ?></h3>
                    <p>Non éligibles</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $tauxEligibilite ?>%</h3>
                    <p>Taux d'éligibilité</p>
                </div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar">
            <h3>Gestion de l'éligibilité</h3>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="calculer_eligibilite">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Recalculer l\'éligibilité pour tous les étudiants ?')">
                    <i class="fas fa-calculator"></i>
                    Recalculer l'éligibilité
                </button>
            </form>
        </div>

        <!-- Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3>Éligibilité par niveau</h3>
            </div>
            <canvas id="eligibiliteChart" width="400" height="200"></canvas>
        </div>

        <!-- Students Table -->
        <div class="data-table">
            <div class="table-header">
                <h3>Liste des étudiants</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Niveau</th>
                        <th>Moyenne</th>
                        <th>Rapports</th>
                        <th>Soutenances</th>
                        <th>Éligibilité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $etudiant): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenoms']) ?></strong><br>
                                <small><?= htmlspecialchars($etudiant['numero_etudiant']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($etudiant['niveau_nom']) ?></td>
                            <td>
                                <?php if ($etudiant['moyenne_generale']): ?>
                                    <strong><?= number_format($etudiant['moyenne_generale'], 2) ?>/20</strong>
                                <?php else: ?>
                                    <em>Non calculée</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $etudiant['nb_rapports_valides'] ?>/<?= $etudiant['nb_rapports'] ?>
                                <small>(validés/total)</small>
                            </td>
                            <td><?= $etudiant['nb_soutenances'] ?></td>
                            <td>
                                <span class="badge badge-<?= $etudiant['statut_eligibilite'] == 5 ? 'eligible' : 'non-eligible' ?>">
                                    <?= $etudiant['statut_eligibilite'] == 5 ? 'Éligible' : 'Non éligible' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-secondary" onclick="editEligibility(<?= $etudiant['utilisateur_id'] ?>)">
                                    <i class="fas fa-edit"></i>
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

    <!-- Edit Eligibility Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier l'éligibilité</h3>
                <button class="modal-close" onclick="closeModal('editModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="editForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="modifier_eligibilite">
                    <input type="hidden" name="etudiant_id" id="edit_etudiant_id">
                    
                    <div class="form-group">
                        <label for="edit_eligible">Statut d'éligibilité</label>
                        <select name="eligible" id="edit_eligible" required>
                            <option value="1">Non éligible</option>
                            <option value="5">Éligible</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_commentaire">Commentaire</label>
                        <textarea name="commentaire" id="edit_commentaire" placeholder="Raison de la modification..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Données des étudiants pour JavaScript
        const etudiants = <?= json_encode($etudiants) ?>;
        const statsNiveaux = <?= json_encode($statsNiveaux) ?>;

        // Graphique d'éligibilité par niveau
        const eligibiliteCtx = document.getElementById('eligibiliteChart').getContext('2d');
        new Chart(eligibiliteCtx, {
            type: 'bar',
            data: {
                labels: statsNiveaux.map(item => item.niveau),
                datasets: [
                    {
                        label: 'Total',
                        data: statsNiveaux.map(item => item.total),
                        backgroundColor: 'rgba(107, 114, 128, 0.8)',
                        borderColor: 'rgba(107, 114, 128, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Éligibles',
                        data: statsNiveaux.map(item => item.eligibles),
                        backgroundColor: 'rgba(0, 51, 41, 0.8)',
                        borderColor: 'rgba(0, 51, 41, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
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

        function editEligibility(etudiant_id) {
            const etudiant = etudiants.find(e => e.utilisateur_id == etudiant_id);
            if (etudiant) {
                document.getElementById('edit_etudiant_id').value = etudiant.utilisateur_id;
                document.getElementById('edit_eligible').value = etudiant.statut_eligibilite;
                document.getElementById('edit_commentaire').value = etudiant.commentaire_eligibilite || '';
                
                openModal('editModal');
            }
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
