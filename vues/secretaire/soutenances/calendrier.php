<?php
/**
 * Calendrier des Soutenances - Secrétaire
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
if ($userRole !== 'Secrétaire') {
    redirectTo('../../../login.php');
}

$userName = SessionManager::getUserName();

// Paramètres de navigation du calendrier
$month = intval($_GET['month'] ?? date('n'));
$year = intval($_GET['year'] ?? date('Y'));

// Validation des paramètres
if ($month < 1 || $month > 12) $month = date('n');
if ($year < 2020 || $year > 2030) $year = date('Y');

try {
    $db = Database::getInstance();
    
    // Récupérer les soutenances du mois
    $start_date = sprintf('%04d-%02d-01', $year, $month);
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $sql = "
        SELECT 
            s.soutenance_id,
            s.date_prevue,
            s.duree_prevue,
            r.titre as titre_rapport,
            e.numero_etudiant,
            CONCAT(ip.nom, ' ', ip.prenoms) as nom_etudiant,
            sp.libelle_specialite,
            sal.nom_salle,
            st.libelle_statut as statut_soutenance,
            st.couleur_affichage as couleur_statut
        FROM soutenances s
        JOIN rapports r ON s.rapport_id = r.rapport_id
        JOIN etudiants e ON r.etudiant_id = e.etudiant_id
        JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        LEFT JOIN salles sal ON s.salle_id = sal.salle_id
        JOIN statuts st ON s.statut_id = st.statut_id
        WHERE DATE(s.date_prevue) BETWEEN ? AND ?
        ORDER BY s.date_prevue ASC
    ";
    
    $soutenances = $db->fetchAll($sql, [$start_date, $end_date]);
    
    // Organiser les soutenances par jour
    $soutenances_par_jour = [];
    foreach ($soutenances as $soutenance) {
        $jour = date('j', strtotime($soutenance['date_prevue']));
        if (!isset($soutenances_par_jour[$jour])) {
            $soutenances_par_jour[$jour] = [];
        }
        $soutenances_par_jour[$jour][] = $soutenance;
    }
    
    // Informations du calendrier
    $premier_jour = date('w', strtotime($start_date)); // 0 = dimanche
    $nb_jours = date('t', strtotime($start_date));
    
    // Ajuster pour que lundi soit le premier jour (0)
    $premier_jour = ($premier_jour == 0) ? 6 : $premier_jour - 1;
    
    // Navigation
    $mois_precedent = $month == 1 ? 12 : $month - 1;
    $annee_precedente = $month == 1 ? $year - 1 : $year;
    $mois_suivant = $month == 12 ? 1 : $month + 1;
    $annee_suivante = $month == 12 ? $year + 1 : $year;
    
    $noms_mois = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];
    
} catch (Exception $e) {
    error_log("Erreur calendrier soutenances: " . $e->getMessage());
    $soutenances_par_jour = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Soutenances - Secrétaire</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 700;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .calendar-header {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            background: var(--primary-color);
            color: var(--white);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .month-year {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .view-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-secondary {
            background: var(--gray-500);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: var(--gray-600);
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .calendar-container {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
        }

        .calendar-table th {
            background: var(--primary-color);
            color: var(--white);
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .calendar-table td {
            height: 120px;
            vertical-align: top;
            border: 1px solid var(--gray-200);
            padding: 0.5rem;
            position: relative;
        }

        .calendar-table td.other-month {
            background: var(--gray-50);
            color: var(--gray-400);
        }

        .calendar-table td.today {
            background: var(--primary-light);
        }

        .day-number {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .today .day-number {
            color: var(--primary-color);
            font-weight: 700;
        }

        .soutenance-item {
            background: var(--accent-color);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
            cursor: pointer;
            transition: var(--transition);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .soutenance-item:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow);
        }

        .soutenance-count {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Modal pour les détails */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 2rem;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .modal-header h3 {
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .close {
            color: var(--gray-500);
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }

        .close:hover {
            color: var(--gray-700);
        }

        .soutenance-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-item i {
            color: var(--primary-color);
            width: 20px;
        }

        .legend {
            background: var(--white);
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-top: 1rem;
        }

        .legend h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .calendar-header {
                flex-direction: column;
                gap: 1rem;
            }

            .calendar-nav {
                flex-direction: column;
                text-align: center;
            }

            .calendar-table th,
            .calendar-table td {
                padding: 0.5rem 0.25rem;
            }

            .calendar-table td {
                height: 80px;
            }

            .soutenance-item {
                font-size: 0.6rem;
                padding: 0.125rem 0.25rem;
            }

            .modal-content {
                margin: 10% auto;
                padding: 1rem;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-calendar"></i> Calendrier des Soutenances</h1>
                <div class="breadcrumb">
                    <a href="../index.php">Tableau de bord</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="planning.php">Planning soutenances</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Calendrier</span>
                </div>
            </div>
        </div>

        <!-- En-tête du calendrier -->
        <div class="calendar-header">
            <div class="calendar-nav">
                <a href="?month=<?= $mois_precedent ?>&year=<?= $annee_precedente ?>" class="nav-btn">
                    <i class="fas fa-chevron-left"></i> Précédent
                </a>
                <div class="month-year">
                    <?= $noms_mois[$month] ?> <?= $year ?>
                </div>
                <a href="?month=<?= $mois_suivant ?>&year=<?= $annee_suivante ?>" class="nav-btn">
                    Suivant <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            
            <div class="view-actions">
                <a href="planning.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Vue Liste
                </a>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Imprimer
                </button>
                <a href="?month=<?= date('n') ?>&year=<?= date('Y') ?>" class="btn btn-primary">
                    <i class="fas fa-calendar-day"></i> Aujourd'hui
                </a>
            </div>
        </div>

        <!-- Calendrier -->
        <div class="calendar-container">
            <table class="calendar-table">
                <thead>
                    <tr>
                        <th>Lundi</th>
                        <th>Mardi</th>
                        <th>Mercredi</th>
                        <th>Jeudi</th>
                        <th>Vendredi</th>
                        <th>Samedi</th>
                        <th>Dimanche</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $jour_actuel = 1 - $premier_jour;
                    $aujourd_hui = date('j');
                    $mois_actuel = date('n');
                    $annee_actuelle = date('Y');
                    
                    for ($semaine = 0; $semaine < 6; $semaine++):
                        if ($jour_actuel > $nb_jours) break;
                    ?>
                        <tr>
                            <?php for ($jour_semaine = 0; $jour_semaine < 7; $jour_semaine++): ?>
                                <?php
                                $est_dans_mois = ($jour_actuel >= 1 && $jour_actuel <= $nb_jours);
                                $est_aujourd_hui = ($est_dans_mois && $jour_actuel == $aujourd_hui && $month == $mois_actuel && $year == $annee_actuelle);
                                $classe_td = '';
                                
                                if (!$est_dans_mois) {
                                    $classe_td = 'other-month';
                                } elseif ($est_aujourd_hui) {
                                    $classe_td = 'today';
                                }
                                ?>
                                <td class="<?= $classe_td ?>">
                                    <?php if ($est_dans_mois): ?>
                                        <div class="day-number"><?= $jour_actuel ?></div>
                                        
                                        <?php if (isset($soutenances_par_jour[$jour_actuel])): ?>
                                            <?php $soutenances_jour = $soutenances_par_jour[$jour_actuel]; ?>
                                            <?php if (count($soutenances_jour) > 3): ?>
                                                <div class="soutenance-count"><?= count($soutenances_jour) ?></div>
                                            <?php endif; ?>
                                            
                                            <?php foreach (array_slice($soutenances_jour, 0, 3) as $soutenance): ?>
                                                <div class="soutenance-item" 
                                                     onclick="showSoutenanceDetails(<?= htmlspecialchars(json_encode($soutenance)) ?>)"
                                                     style="background-color: <?= $soutenance['couleur_statut'] ?>">
                                                    <?= date('H:i', strtotime($soutenance['date_prevue'])) ?> - <?= htmlspecialchars(substr($soutenance['nom_etudiant'], 0, 15)) ?>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <?php if (count($soutenances_jour) > 3): ?>
                                                <div class="soutenance-item" 
                                                     onclick="showDayDetails(<?= $jour_actuel ?>, <?= $month ?>, <?= $year ?>)"
                                                     style="background-color: var(--gray-500)">
                                                    +<?= count($soutenances_jour) - 3 ?> autres...
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php
                                        if ($jour_actuel <= 0) {
                                            $jour_affiche = $nb_jours + $jour_actuel;
                                        } else {
                                            $jour_affiche = $jour_actuel - $nb_jours;
                                        }
                                        ?>
                                        <div class="day-number"><?= $jour_affiche ?></div>
                                    <?php endif; ?>
                                </td>
                                <?php $jour_actuel++; ?>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <!-- Légende -->
        <div class="legend">
            <h4>Légende des statuts</h4>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--accent-color)"></div>
                    <span>Programmée</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--success-color)"></div>
                    <span>Confirmée</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--warning-color)"></div>
                    <span>En attente</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--error-color)"></div>
                    <span>Annulée</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--primary-light)"></div>
                    <span>Aujourd'hui</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les détails -->
    <div id="soutenanceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Détails de la soutenance</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody" class="soutenance-details">
                <!-- Contenu dynamique -->
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        const soutenancesData = <?= json_encode($soutenances_par_jour) ?>;

        // Afficher les détails d'une soutenance
        function showSoutenanceDetails(soutenance) {
            const modal = document.getElementById('soutenanceModal');
            const title = document.getElementById('modalTitle');
            const body = document.getElementById('modalBody');
            
            title.textContent = `Soutenance - ${soutenance.nom_etudiant}`;
            
            const dateFormatted = new Date(soutenance.date_prevue).toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            const timeFormatted = new Date(soutenance.date_prevue).toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            body.innerHTML = `
                <div class="detail-item">
                    <i class="fas fa-user"></i>
                    <span><strong>Étudiant:</strong> ${soutenance.nom_etudiant}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-id-card"></i>
                    <span><strong>Numéro:</strong> ${soutenance.numero_etudiant}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-calendar"></i>
                    <span><strong>Date:</strong> ${dateFormatted}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span><strong>Heure:</strong> ${timeFormatted}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-hourglass-half"></i>
                    <span><strong>Durée:</strong> ${soutenance.duree_prevue} minutes</span>
                </div>
                ${soutenance.nom_salle ? `
                <div class="detail-item">
                    <i class="fas fa-door-open"></i>
                    <span><strong>Salle:</strong> ${soutenance.nom_salle}</span>
                </div>
                ` : ''}
                ${soutenance.libelle_specialite ? `
                <div class="detail-item">
                    <i class="fas fa-graduation-cap"></i>
                    <span><strong>Spécialité:</strong> ${soutenance.libelle_specialite}</span>
                </div>
                ` : ''}
                <div class="detail-item">
                    <i class="fas fa-file-alt"></i>
                    <span><strong>Titre:</strong> ${soutenance.titre_rapport}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-info-circle"></i>
                    <span><strong>Statut:</strong> <span style="color: ${soutenance.couleur_statut}">${soutenance.statut_soutenance}</span></span>
                </div>
            `;
            
            modal.style.display = 'block';
        }

        // Afficher toutes les soutenances d'un jour
        function showDayDetails(day, month, year) {
            const modal = document.getElementById('soutenanceModal');
            const title = document.getElementById('modalTitle');
            const body = document.getElementById('modalBody');
            
            const soutenances = soutenancesData[day] || [];
            
            title.textContent = `Soutenances du ${day}/${month}/${year}`;
            
            let html = '';
            soutenances.forEach(soutenance => {
                const timeFormatted = new Date(soutenance.date_prevue).toLocaleTimeString('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                html += `
                    <div style="border: 1px solid var(--gray-200); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <strong>${soutenance.nom_etudiant}</strong>
                            <span style="background-color: ${soutenance.couleur_statut}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                ${soutenance.statut_soutenance}
                            </span>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--gray-600);">
                            <div><i class="fas fa-clock"></i> ${timeFormatted} (${soutenance.duree_prevue} min)</div>
                            ${soutenance.nom_salle ? `<div><i class="fas fa-door-open"></i> ${soutenance.nom_salle}</div>` : ''}
                            <div><i class="fas fa-file-alt"></i> ${soutenance.titre_rapport.substring(0, 60)}${soutenance.titre_rapport.length > 60 ? '...' : ''}</div>
                        </div>
                    </div>
                `;
            });
            
            body.innerHTML = html;
            modal.style.display = 'block';
        }

        // Fermer la modal
        function closeModal() {
            document.getElementById('soutenanceModal').style.display = 'none';
        }

        // Fermer la modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modal = document.getElementById('soutenanceModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Styles d'impression
        const printStyles = `
            @media print {
                .header, .calendar-header .view-actions, .legend { display: none !important; }
                .calendar-container { box-shadow: none; }
                .calendar-table { font-size: 10px; }
                .calendar-table th, .calendar-table td { padding: 4px; }
                .soutenance-item { font-size: 8px; padding: 1px 2px; }
                .modal { display: none !important; }
            }
        `;
        
        const styleSheet = document.createElement("style");
        styleSheet.innerText = printStyles;
        document.head.appendChild(styleSheet);

        console.log('📅 Calendrier des soutenances - Ready!');
    </script>
</body>
</html>
