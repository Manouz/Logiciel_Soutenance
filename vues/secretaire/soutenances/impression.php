<?php
/**
 * Impression Planning - Secrétaire
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

// Récupérer les paramètres de filtrage depuis l'URL
$search = $_GET['search'] ?? '';
$date_debut = $_GET['date_debut'] ?? date('Y-m-d');
$date_fin = $_GET['date_fin'] ?? date('Y-m-d', strtotime('+1 month'));
$statut_filter = $_GET['statut'] ?? '';
$specialite_filter = $_GET['specialite'] ?? '';

try {
    $db = Database::getInstance();
    
    // Construction de la requête WHERE
    $where_conditions = ['1=1'];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(ip.nom LIKE ? OR ip.prenoms LIKE ? OR e.numero_etudiant LIKE ? OR r.titre LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if (!empty($date_debut)) {
        $where_conditions[] = "DATE(s.date_prevue) >= ?";
        $params[] = $date_debut;
    }
    
    if (!empty($date_fin)) {
        $where_conditions[] = "DATE(s.date_prevue) <= ?";
        $params[] = $date_fin;
    }
    
    if (!empty($statut_filter)) {
        $where_conditions[] = "s.statut_id = ?";
        $params[] = $statut_filter;
    }
    
    if (!empty($specialite_filter)) {
        $where_conditions[] = "e.specialite_id = ?";
        $params[] = $specialite_filter;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Requête pour récupérer les soutenances
    $sql = "
        SELECT 
            s.soutenance_id,
            s.date_prevue,
            s.duree_prevue,
            s.type_soutenance,
            r.titre as titre_rapport,
            e.numero_etudiant,
            CONCAT(ip.nom, ' ', ip.prenoms) as nom_etudiant,
            sp.libelle_specialite,
            sal.nom_salle,
            sal.batiment,
            sal.etage,
            st.libelle_statut as statut_soutenance,
            CONCAT(enc_ip.nom, ' ', enc_ip.prenoms) as nom_encadreur,
            s.est_publique
        FROM soutenances s
        JOIN rapports r ON s.rapport_id = r.rapport_id
        JOIN etudiants e ON r.etudiant_id = e.etudiant_id
        JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        LEFT JOIN salles sal ON s.salle_id = sal.salle_id
        JOIN statuts st ON s.statut_id = st.statut_id
        LEFT JOIN enseignants enc ON r.encadreur_id = enc.enseignant_id
        LEFT JOIN utilisateurs enc_u ON enc.utilisateur_id = enc_u.utilisateur_id
        LEFT JOIN informations_personnelles enc_ip ON enc_u.utilisateur_id = enc_ip.utilisateur_id
        WHERE $where_clause
        ORDER BY s.date_prevue ASC
    ";
    
    $soutenances = $db->fetchAll($sql, $params);
    
    // Récupérer les informations des jurys pour chaque soutenance
    foreach ($soutenances as &$soutenance) {
        $jury_sql = "
            SELECT 
                j.role_jury,
                CONCAT(jury_ip.nom, ' ', jury_ip.prenoms) as nom_jury,
                ga.libelle_grade
            FROM jurys j
            JOIN enseignants jury_ens ON j.enseignant_id = jury_ens.enseignant_id
            JOIN utilisateurs jury_u ON jury_ens.utilisateur_id = jury_u.utilisateur_id
            JOIN informations_personnelles jury_ip ON jury_u.utilisateur_id = jury_ip.utilisateur_id
            LEFT JOIN grades_academiques ga ON jury_ens.grade_id = ga.grade_id
            WHERE j.soutenance_id = ?
            ORDER BY 
                CASE j.role_jury 
                    WHEN 'Président' THEN 1 
                    WHEN 'Rapporteur' THEN 2 
                    WHEN 'Examinateur' THEN 3 
                    ELSE 4 
                END
        ";
        
        $soutenance['jury'] = $db->fetchAll($jury_sql, [$soutenance['soutenance_id']]);
    }
    
} catch (Exception $e) {
    error_log("Erreur impression planning: " . $e->getMessage());
    $soutenances = [];
}

// Grouper les soutenances par date
$soutenances_par_date = [];
foreach ($soutenances as $soutenance) {
    $date = date('Y-m-d', strtotime($soutenance['date_prevue']));
    if (!isset($soutenances_par_date[$date])) {
        $soutenances_par_date[$date] = [];
    }
    $soutenances_par_date[$date][] = $soutenance;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning des Soutenances - Impression</title>
    <style>
        :root {
            --primary-color: rgb(0, 51, 41);
            --primary-light: rgba(0, 51, 41, 0.1);
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-900: #111827;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: var(--gray-900);
            background: var(--white);
        }

        .print-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .print-header h1 {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 0.5rem;
        }

        .print-header .subtitle {
            font-size: 16px;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .print-header .date-range {
            font-size: 14px;
            color: var(--gray-600);
        }

        .print-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 10px;
            color: var(--gray-600);
        }

        .date-section {
            margin-bottom: 2rem;
            page-break-inside: avoid;
        }

        .date-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 0.5rem 1rem;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 1rem;
        }

        .soutenance-item {
            border: 1px solid var(--gray-200);
            margin-bottom: 1rem;
            page-break-inside: avoid;
        }

        .soutenance-header {
            background: var(--gray-100);
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1rem;
            align-items: center;
        }

        .time-badge {
            background: var(--primary-color);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            min-width: 60px;
        }

        .soutenance-title {
            font-weight: bold;
            font-size: 13px;
        }

        .soutenance-subtitle {
            color: var(--gray-600);
            font-size: 11px;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            background: var(--gray-200);
            color: var(--gray-900);
        }

        .soutenance-details {
            padding: 0.75rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .detail-section h4 {
            font-size: 11px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-200);
            padding-bottom: 0.25rem;
        }

        .detail-item {
            font-size: 10px;
            margin-bottom: 0.25rem;
            display: flex;
            gap: 0.5rem;
        }

        .detail-label {
            font-weight: bold;
            min-width: 60px;
        }

        .jury-list {
            list-style: none;
        }

        .jury-list li {
            font-size: 10px;
            padding: 0.25rem 0;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            justify-content: space-between;
        }

        .jury-list li:last-child {
            border-bottom: none;
        }

        .jury-role {
            font-weight: bold;
            color: var(--primary-color);
        }

        .summary {
            margin-top: 2rem;
            padding: 1rem;
            background: var(--gray-100);
            border-radius: 4px;
        }

        .summary h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 10px;
            color: var(--gray-600);
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: var(--gray-600);
        }

        /* Styles d'impression */
        @media print {
            body {
                font-size: 10px;
            }
            
            .print-header h1 {
                font-size: 20px;
            }
            
            .print-header .subtitle {
                font-size: 14px;
            }
            
            .date-header {
                font-size: 12px;
            }
            
            .soutenance-title {
                font-size: 11px;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            .no-print {
                display: none;
            }
        }

        @page {
            margin: 1cm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h1>UNIVERSITÉ FÉLIX HOUPHOUËT-BOIGNY</h1>
        <div class="subtitle">Planning des Soutenances</div>
        <div class="date-range">
            Période: <?= date('d/m/Y', strtotime($date_debut)) ?> - <?= date('d/m/Y', strtotime($date_fin)) ?>
        </div>
    </div>

    <div class="print-info">
        <div>Généré le: <?= date('d/m/Y à H:i') ?></div>
        <div>Total: <?= count($soutenances) ?> soutenance(s)</div>
        <div>Secrétariat</div>
    </div>

    <?php if (!empty($soutenances_par_date)): ?>
        <?php foreach ($soutenances_par_date as $date => $soutenances_jour): ?>
            <div class="date-section">
                <div class="date-header">
                    <?= date('l j F Y', strtotime($date)) ?> - <?= count($soutenances_jour) ?> soutenance(s)
                </div>

                <?php foreach ($soutenances_jour as $soutenance): ?>
                    <div class="soutenance-item">
                        <div class="soutenance-header">
                            <div class="time-badge">
                                <?= date('H:i', strtotime($soutenance['date_prevue'])) ?>
                            </div>
                            
                            <div>
                                <div class="soutenance-title">
                                    <?= htmlspecialchars($soutenance['nom_etudiant']) ?>
                                </div>
                                <div class="soutenance-subtitle">
                                    <?= htmlspecialchars($soutenance['numero_etudiant']) ?> - 
                                    <?= htmlspecialchars($soutenance['libelle_specialite'] ?? 'Spécialité non définie') ?>
                                </div>
                            </div>
                            
                            <div class="status-badge">
                                <?= htmlspecialchars($soutenance['statut_soutenance']) ?>
                            </div>
                        </div>
                        
                        <div class="soutenance-details">
                            <div class="detail-section">
                                <h4>Informations pratiques</h4>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Durée:</span>
                                    <span><?= $soutenance['duree_prevue'] ?> minutes</span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Type:</span>
                                    <span><?= htmlspecialchars($soutenance['type_soutenance']) ?></span>
                                </div>
                                
                                <?php if ($soutenance['nom_salle']): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Salle:</span>
                                        <span><?= htmlspecialchars($soutenance['nom_salle']) ?></span>
                                    </div>
                                    
                                    <?php if ($soutenance['batiment']): ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Lieu:</span>
                                            <span><?= htmlspecialchars($soutenance['batiment']) ?><?= $soutenance['etage'] ? ' - ' . $soutenance['etage'] : '' ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Salle:</span>
                                        <span style="color: #f59e0b;">Non définie</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($soutenance['nom_encadreur']): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Encadreur:</span>
                                        <span><?= htmlspecialchars($soutenance['nom_encadreur']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Accès:</span>
                                    <span><?= $soutenance['est_publique'] ? 'Public' : 'Privé' ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label">Titre:</span>
                                    <span><?= htmlspecialchars(substr($soutenance['titre_rapport'], 0, 60)) ?><?= strlen($soutenance['titre_rapport']) > 60 ? '...' : '' ?></span>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h4>Composition du jury</h4>
                                
                                <?php if (!empty($soutenance['jury'])): ?>
                                    <ul class="jury-list">
                                        <?php foreach ($soutenance['jury'] as $membre): ?>
                                            <li>
                                                <div>
                                                    <?= htmlspecialchars($membre['nom_jury']) ?>
                                                    <?php if ($membre['libelle_grade']): ?>
                                                        <br><small><?= htmlspecialchars($membre['libelle_grade']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="jury-role"><?= htmlspecialchars($membre['role_jury']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="detail-item">
                                        <span style="color: #f59e0b;">Jury non constitué</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <!-- Résumé statistique -->
        <div class="summary">
            <h3>Résumé</h3>
            <div class="summary-stats">
                <div class="stat-item">
                    <div class="stat-number"><?= count($soutenances) ?></div>
                    <div class="stat-label">Total soutenances</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count($soutenances_par_date) ?></div>
                    <div class="stat-label">Jours concernés</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= round(count($soutenances) / max(1, count($soutenances_par_date)), 1) ?></div>
                    <div class="stat-label">Moyenne par jour</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= array_sum(array_column($soutenances, 'duree_prevue')) ?></div>
                    <div class="stat-label">Total minutes</div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="no-data">
            <h3>Aucune soutenance trouvée</h3>
            <p>Aucune soutenance ne correspond aux critères sélectionnés pour la période du <?= date('d/m/Y', strtotime($date_debut)) ?> au <?= date('d/m/Y', strtotime($date_fin)) ?>.</p>
        </div>
    <?php endif; ?>

    <script>
        // Impression automatique au chargement de la page
        window.onload = function() {
            window.print();
        };

        // Fermer la fenêtre après impression ou annulation
        window.onafterprint = function() {
            window.close();
        };

        console.log('🖨️ Planning impression - Ready!');
    </script>
</body>
</html>
