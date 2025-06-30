<?php
/**
 * Statistiques - Responsable Scolarité
 * Système de Validation Académique - UFHB Cocody
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

SessionManager::start();

if (!SessionManager::isLoggedIn()) {
    redirectTo('../../login.php');
}

$userRole = SessionManager::getUserRole();
if ($userRole !== 'Responsable Scolarité') {
    redirectTo('../../login.php');
}

$userId = SessionManager::getUserId();
$userName = SessionManager::getUserName();

try {
    $db = Database::getInstance();
    
    // Statistiques générales
    $totalEtudiants = $db->count('etudiants', 'est_actif = 1');
    
    $moyenne_result = $db->fetch("SELECT AVG(moyenne_generale) as moyenne FROM etudiants WHERE moyenne_generale IS NOT NULL");
    $moyenneGenerale = round($moyenne_result['moyenne'] ?? 0, 1);
    
    $etudiantsReussis = $db->count('etudiants', 'moyenne_generale >= 10 AND est_actif = 1');
    $tauxReussite = $totalEtudiants > 0 ? round(($etudiantsReussis / $totalEtudiants) * 100, 1) : 0;
    
    $rapportsDeposes = $db->count('rapports', 'statut_id IN (9, 10)'); // DEPOSE, EN_VERIFICATION
    $soutenancesTerminees = $db->count('soutenances', 'statut_id = 15'); // TERMINEE
    
    // Statistiques par niveau
    $statsNiveaux = $db->fetchAll("
        SELECT n.nom as niveau, COUNT(e.utilisateur_id) as nombre 
        FROM niveaux n 
        LEFT JOIN etudiants e ON n.niveau_id = e.niveau_id AND e.est_actif = 1
        GROUP BY n.niveau_id, n.nom 
        ORDER BY n.nom
    ");
    
    // Statistiques par spécialité
    $statsSpecialites = $db->fetchAll("
        SELECT s.nom as specialite, COUNT(e.utilisateur_id) as nombre 
        FROM specialites s 
        LEFT JOIN etudiants e ON s.specialite_id = e.specialite_id AND e.est_actif = 1
        GROUP BY s.specialite_id, s.nom 
        ORDER BY s.nom
    ");
    
} catch (Exception $e) {
    error_log("Erreur statistiques: " . $e->getMessage());
    $totalEtudiants = 0;
    $moyenneGenerale = 0;
    $tauxReussite = 0;
    $rapportsDeposes = 0;
    $soutenancesTerminees = 0;
    $statsNiveaux = [];
    $statsSpecialites = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Responsable Scolarité</title>
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
            max-width: 1200px;
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-bar"></i> Statistiques et Rapports</h1>
            <p>Vue d'ensemble des performances académiques</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($totalEtudiants) ?></h3>
                    <p>Étudiants inscrits</p>
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
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($rapportsDeposes) ?></h3>
                    <p>Rapports déposés</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($soutenancesTerminees) ?></h3>
                    <p>Soutenances terminées</p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Répartition par niveau</h3>
                </div>
                <canvas id="niveauxChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Répartition par spécialité</h3>
                </div>
                <canvas id="specialitesChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Retour au tableau de bord
            </a>
        </div>
    </div>

    <script>
        // Données pour les graphiques
        const niveauxData = <?= json_encode($statsNiveaux) ?>;
        const specialitesData = <?= json_encode($statsSpecialites) ?>;

        // Graphique des niveaux
        const niveauxCtx = document.getElementById('niveauxChart').getContext('2d');
        new Chart(niveauxCtx, {
            type: 'doughnut',
            data: {
                labels: niveauxData.map(item => item.niveau),
                datasets: [{
                    data: niveauxData.map(item => item.nombre),
                    backgroundColor: [
                        'rgba(0, 51, 41, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(52, 211, 153, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ]
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

        // Graphique des spécialités
        const specialitesCtx = document.getElementById('specialitesChart').getContext('2d');
        new Chart(specialitesCtx, {
            type: 'bar',
            data: {
                labels: specialitesData.map(item => item.specialite),
                datasets: [{
                    label: 'Nombre d\'étudiants',
                    data: specialitesData.map(item => item.nombre),
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
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
