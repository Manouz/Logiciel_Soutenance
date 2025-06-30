<?php
/**
 * Consultation des Notes - Responsable Scolarité
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
    
    // Statistiques générales
    $totalEtudiants = $db->count('notes_evaluations', 'DISTINCT etudiant_id');
    
    $moyenne_result = $db->fetch("SELECT AVG(note) as moyenne FROM notes_evaluations WHERE note IS NOT NULL");
    $moyenneGenerale = round($moyenne_result['moyenne'] ?? 0, 1);
    
    $notesReussies = $db->count('notes_evaluations', 'note >= 10');
    $totalNotes = $db->count('notes_evaluations');
    $tauxReussite = $totalNotes > 0 ? round(($notesReussies / $totalNotes) * 100, 1) : 0;
    
    $mentionsBien = $db->count('notes_evaluations', 'note >= 14');
    $echecs = $db->count('notes_evaluations', 'note < 10');
    
    // Notes détaillées avec informations étudiants
    $notes = $db->fetchAll("
        SELECT ne.*, ip.nom, ip.prenoms, e_etud.numero_etudiant, 
               ue.nom as ue_nom, ue.code as ue_code,
               niv.nom as niveau_nom, s.nom as specialite_nom,
               ev.type_evaluation, ev.coefficient, ev.date_evaluation
        FROM notes_evaluations ne
        JOIN etudiants e_etud ON ne.etudiant_id = e_etud.utilisateur_id
        JOIN informations_personnelles ip ON e_etud.utilisateur_id = ip.utilisateur_id
        JOIN evaluations ev ON ne.evaluation_id = ev.evaluation_id
        JOIN ues ue ON ev.ue_id = ue.ue_id
        JOIN niveaux niv ON e_etud.niveau_id = niv.niveau_id
        JOIN specialites s ON e_etud.specialite_id = s.specialite_id
        ORDER BY ne.date_saisie DESC
        LIMIT 100
    ");
    
    // Données pour les graphiques
    $moyennesNiveaux = $db->fetchAll("
        SELECT niv.nom as niveau, AVG(ne.note) as moyenne
        FROM notes_evaluations ne
        JOIN etudiants e ON ne.etudiant_id = e.utilisateur_id
        JOIN niveaux niv ON e.niveau_id = niv.niveau_id
        WHERE ne.note IS NOT NULL
        GROUP BY niv.niveau_id, niv.nom
        ORDER BY niv.nom
    ");
    
    $moyennesSpecialites = $db->fetchAll("
        SELECT s.nom as specialite, AVG(ne.note) as moyenne
        FROM notes_evaluations ne
        JOIN etudiants e ON ne.etudiant_id = e.utilisateur_id
        JOIN specialites s ON e.specialite_id = s.specialite_id
        WHERE ne.note IS NOT NULL
        GROUP BY s.specialite_id, s.nom
        ORDER BY s.nom
    ");
    
} catch (Exception $e) {
    error_log("Erreur consultation notes: " . $e->getMessage());
    $totalEtudiants = 0;
    $moyenneGenerale = 0;
    $tauxReussite = 0;
    $mentionsBien = 0;
    $echecs = 0;
    $notes = [];
    $moyennesNiveaux = [];
    $moyennesSpecialites = [];
}

function getGradeClass($note) {
    if ($note >= 16) return 'grade-excellent';
    if ($note >= 14) return 'grade-good';
    if ($note >= 10) return 'grade-average';
    return 'grade-poor';
}

function getGradeMention($note) {
    if ($note >= 16) return 'Très Bien';
    if ($note >= 14) return 'Bien';
    if ($note >= 12) return 'Assez Bien';
    if ($note >= 10) return 'Passable';
    return 'Insuffisant';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation des Notes - Responsable Scolarité</title>
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
            background: linear-gradient(135deg, var(--success-color), var(--accent-color));
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, var(--info-color), #60a5fa);
        }

        .stat-card:nth-child(5) .stat-icon {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
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

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
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

        .grade-excellent {
            background: #dcfce7;
            color: #166534;
        }

        .grade-good {
            background: #dbeafe;
            color: #1e40af;
        }

        .grade-average {
            background: #fef3c7;
            color: #92400e;
        }

        .grade-poor {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
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
            <h1><i class="fas fa-chart-line"></i> Consultation des Notes</h1>
            <p>Analyse et consultation des résultats académiques</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($totalEtudiants) ?></h3>
                    <p>Étudiants évalués</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $moyenneGenerale ?></h3>
                    <p>Moyenne générale</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $tauxReussite ?>%</h3>
                    <p>Taux de réussite</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($mentionsBien) ?></h3>
                    <p>Mentions B/TB</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($echecs) ?></h3>
                    <p>Échecs (&lt;10)</p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Moyennes par niveau</h3>
                </div>
                <canvas id="niveauxChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Moyennes par spécialité</h3>
                </div>
                <canvas id="specialitesChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Notes Table -->
        <div class="data-table">
            <div class="table-header">
                <h3>Notes récentes</h3>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-secondary" onclick="exportNotes()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>UE</th>
                        <th>Type</th>
                        <th>Note</th>
                        <th>Coefficient</th>
                        <th>Date de saisie</th>
                        <th>Mention</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notes as $note): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($note['nom'] . ' ' . $note['prenoms']) ?></strong><br>
                                <small><?= htmlspecialchars($note['numero_etudiant']) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($note['ue_nom']) ?></strong><br>
                                <small><?= htmlspecialchars($note['ue_code']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($note['type_evaluation']) ?></td>
                            <td>
                                <strong class="<?= getGradeClass($note['note']) ?>">
                                    <?= number_format($note['note'], 2) ?>/20
                                </strong>
                            </td>
                            <td><?= $note['coefficient'] ?></td>
                            <td><?= formatDateTime($note['date_saisie']) ?></td>
                            <td>
                                <span class="badge <?= getGradeClass($note['note']) ?>">
                                    <?= getGradeMention($note['note']) ?>
                                </span>
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

    <script>
        // Données pour les graphiques
        const niveauxData = <?= json_encode($moyennesNiveaux) ?>;
        const specialitesData = <?= json_encode($moyennesSpecialites) ?>;

        // Graphique des moyennes par niveau
        const niveauxCtx = document.getElementById('niveauxChart').getContext('2d');
        new Chart(niveauxCtx, {
            type: 'bar',
            data: {
                labels: niveauxData.map(item => item.niveau),
                datasets: [{
                    label: 'Moyenne',
                    data: niveauxData.map(item => parseFloat(item.moyenne).toFixed(2)),
                    backgroundColor: 'rgba(0, 51, 41, 0.8)',
                    borderColor: 'rgba(0, 51, 41, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 20
                    }
                }
            }
        });

        // Graphique des moyennes par spécialité
        const specialitesCtx = document.getElementById('specialitesChart').getContext('2d');
        new Chart(specialitesCtx, {
            type: 'radar',
            data: {
                labels: specialitesData.map(item => item.specialite),
                datasets: [{
                    label: 'Moyenne',
                    data: specialitesData.map(item => parseFloat(item.moyenne).toFixed(2)),
                    borderColor: 'rgba(0, 51, 41, 1)',
                    backgroundColor: 'rgba(0, 51, 41, 0.2)',
                    pointBackgroundColor: 'rgba(0, 51, 41, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 20
                    }
                }
            }
        });

        function exportNotes() {
            alert('Fonctionnalité d\'export en cours de développement');
        }
    </script>
</body>
</html>
