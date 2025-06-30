<?php
/**
 * Planning des Soutenances - Secrétaire
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

try {
    $db = Database::getInstance();
    
    // Paramètres de filtrage
    $search = $_GET['search'] ?? '';
    $date_debut = $_GET['date_debut'] ?? date('Y-m-d');
    $date_fin = $_GET['date_fin'] ?? date('Y-m-d', strtotime('+3 months'));
    $statut_filter = $_GET['statut'] ?? '';
    $specialite_filter = $_GET['specialite'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 15;
    $offset = ($page - 1) * $limit;
    
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
    
    // Requête principale pour récupérer les soutenances
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
            sal.capacite,
            st.libelle_statut as statut_soutenance,
            st.couleur_affichage as couleur_statut,
            CONCAT(enc_ip.nom, ' ', enc_ip.prenoms) as nom_encadreur,
            s.est_publique,
            s.lien_visioconference
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
        LIMIT $limit OFFSET $offset
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
    
    // Compter le total pour la pagination
    $count_sql = "
        SELECT COUNT(DISTINCT s.soutenance_id) as total
        FROM soutenances s
        JOIN rapports r ON s.rapport_id = r.rapport_id
        JOIN etudiants e ON r.etudiant_id = e.etudiant_id
        JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        WHERE $where_clause
    ";
    
    $total_result = $db->fetch($count_sql, $params);
    $total_soutenances = $total_result['total'];
    $total_pages = ceil($total_soutenances / $limit);
    
    // Récupérer les données pour les filtres
    $specialites = $db->fetchAll("SELECT specialite_id, libelle_specialite FROM specialites WHERE est_actif = 1 ORDER BY libelle_specialite");
    $statuts = $db->fetchAll("SELECT statut_id, libelle_statut FROM statuts WHERE type_statut = 'Soutenance' ORDER BY ordre_affichage");
    
} catch (Exception $e) {
    error_log("Erreur planning soutenances: " . $e->getMessage());
    $soutenances = [];
    $total_soutenances = 0;
    $total_pages = 0;
    $specialites = [];
    $statuts = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning des Soutenances - Secrétaire</title>
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

        .filters-section {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn {
            padding: 0.75rem 1.5rem;
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

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--gray-500);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: var(--gray-600);
        }

        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background: #059669;
        }

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .results-info {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .soutenances-container {
            display: grid;
            gap: 1.5rem;
        }

        .soutenance-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .soutenance-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .soutenance-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1rem;
            align-items: center;
        }

        .date-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            background: var(--primary-color);
            color: var(--white);
            border-radius: 8px;
            min-width: 80px;
        }

        .date-badge .day {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .date-badge .month {
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .date-badge .time {
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .soutenance-info h3 {
            color: var(--gray-900);
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .soutenance-info p {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--white);
        }

        .soutenance-details {
            padding: 1.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .detail-section h4 {
            color: var(--primary-color);
            font-size: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .detail-item i {
            color: var(--gray-500);
            width: 16px;
        }

        .jury-list {
            list-style: none;
        }

        .jury-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .jury-list li:last-child {
            border-bottom: none;
        }

        .jury-role {
            background: var(--gray-100);
            color: var(--gray-700);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            text-decoration: none;
            color: var(--gray-700);
            transition: var(--transition);
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }

        .pagination .current {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--gray-500);
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-300);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .actions-bar {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .action-buttons {
                justify-content: center;
            }

            .soutenance-header {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .soutenance-details {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-calendar-alt"></i> Planning des Soutenances</h1>
                <div class="breadcrumb">
                    <a href="../index.php">Tableau de bord</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Planning soutenances</span>
                </div>
            </div>
        </div>

        <!-- Filtres et Recherche -->
        <div class="filters-section">
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="search">Rechercher</label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               class="form-control" 
                               placeholder="Nom étudiant, numéro, titre rapport..."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_debut">Date début</label>
                        <input type="date" 
                               id="date_debut" 
                               name="date_debut" 
                               class="form-control"
                               value="<?= htmlspecialchars($date_debut) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_fin">Date fin</label>
                        <input type="date" 
                               id="date_fin" 
                               name="date_fin" 
                               class="form-control"
                               value="<?= htmlspecialchars($date_fin) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="specialite">Spécialité</label>
                        <select id="specialite" name="specialite" class="form-control">
                            <option value="">Toutes</option>
                            <?php foreach ($specialites as $specialite): ?>
                                <option value="<?= $specialite['specialite_id'] ?>" 
                                        <?= $specialite_filter == $specialite['specialite_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($specialite['libelle_specialite']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="">Tous</option>
                            <?php foreach ($statuts as $statut): ?>
                                <option value="<?= $statut['statut_id'] ?>" 
                                        <?= $statut_filter == $statut['statut_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($statut['libelle_statut']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Barre d'actions -->
        <div class="actions-bar">
            <div class="results-info">
                <strong><?= number_format($total_soutenances) ?></strong> soutenance(s) trouvée(s)
                <?php if ($page > 1 || $total_pages > 1): ?>
                    - Page <?= $page ?> sur <?= $total_pages ?>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <a href="../etudiants/export.php?type=soutenances<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($date_debut) ? '&date_debut=' . $date_debut : '' ?><?= !empty($date_fin) ? '&date_fin=' . $date_fin : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?>" 
                   class="btn btn-success">
                    <i class="fas fa-download"></i> Exporter
                </a>
                <a href="impression.php<?= $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
                   class="btn btn-secondary" target="_blank">
                    <i class="fas fa-print"></i> Imprimer
                </a>
                <a href="calendrier.php" class="btn btn-primary">
                    <i class="fas fa-calendar"></i> Vue Calendrier
                </a>
            </div>
        </div>

        <!-- Liste des soutenances -->
        <div class="soutenances-container">
            <?php if (!empty($soutenances)): ?>
                <?php foreach ($soutenances as $soutenance): ?>
                    <div class="soutenance-card">
                        <div class="soutenance-header">
                            <div class="date-badge">
                                <div class="day"><?= date('d', strtotime($soutenance['date_prevue'])) ?></div>
                                <div class="month"><?= date('M', strtotime($soutenance['date_prevue'])) ?></div>
                                <div class="time"><?= date('H:i', strtotime($soutenance['date_prevue'])) ?></div>
                            </div>
                            
                            <div class="soutenance-info">
                                <h3><?= htmlspecialchars($soutenance['nom_etudiant']) ?></h3>
                                <p><strong><?= htmlspecialchars($soutenance['numero_etudiant']) ?></strong> - <?= htmlspecialchars($soutenance['libelle_specialite'] ?? 'Spécialité non définie') ?></p>
                                <p><?= htmlspecialchars(substr($soutenance['titre_rapport'], 0, 80)) ?><?= strlen($soutenance['titre_rapport']) > 80 ? '...' : '' ?></p>
                            </div>
                            
                            <div>
                                <span class="status-badge" style="background-color: <?= $soutenance['couleur_statut'] ?>">
                                    <?= htmlspecialchars($soutenance['statut_soutenance']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="soutenance-details">
                            <div class="detail-section">
                                <h4><i class="fas fa-info-circle"></i> Informations pratiques</h4>
                                
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Durée: <?= $soutenance['duree_prevue'] ?> minutes</span>
                                </div>
                                
                                <div class="detail-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span>Type: <?= htmlspecialchars($soutenance['type_soutenance']) ?></span>
                                </div>
                                
                                <?php if ($soutenance['nom_salle']): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-door-open"></i>
                                        <span><?= htmlspecialchars($soutenance['nom_salle']) ?></span>
                                    </div>
                                    
                                    <?php if ($soutenance['batiment']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-building"></i>
                                            <span><?= htmlspecialchars($soutenance['batiment']) ?><?= $soutenance['etage'] ? ' - ' . $soutenance['etage'] : '' ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($soutenance['capacite']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-users"></i>
                                            <span>Capacité: <?= $soutenance['capacite'] ?> personnes</span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="detail-item">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span style="color: var(--warning-color);">Salle non définie</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($soutenance['nom_encadreur']): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Encadreur: <?= htmlspecialchars($soutenance['nom_encadreur']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($soutenance['lien_visioconference']): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-video"></i>
                                        <span>Visioconférence disponible</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="detail-item">
                                    <i class="fas fa-eye"></i>
                                    <span><?= $soutenance['est_publique'] ? 'Soutenance publique' : 'Soutenance privée' ?></span>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h4><i class="fas fa-gavel"></i> Composition du jury</h4>
                                
                                <?php if (!empty($soutenance['jury'])): ?>
                                    <ul class="jury-list">
                                        <?php foreach ($soutenance['jury'] as $membre): ?>
                                            <li>
                                                <div>
                                                    <strong><?= htmlspecialchars($membre['nom_jury']) ?></strong>
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
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span style="color: var(--warning-color);">Jury non constitué</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucune soutenance trouvée</h3>
                    <p>Aucune soutenance ne correspond aux critères de recherche pour la période sélectionnée.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($date_debut) ? '&date_debut=' . $date_debut : '' ?><?= !empty($date_fin) ? '&date_fin=' . $date_fin : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($date_debut) ? '&date_debut=' . $date_debut : '' ?><?= !empty($date_fin) ? '&date_fin=' . $date_fin : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($date_debut) ? '&date_debut=' . $date_debut : '' ?><?= !empty($date_fin) ? '&date_fin=' . $date_fin : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($date_debut) ? '&date_debut=' . $date_debut : '' ?><?= !empty($date_fin) ? '&date_fin=' . $date_fin : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($date_debut) ? '&date_debut=' . $date_debut : '' ?><?= !empty($date_fin) ? '&date_fin=' . $date_fin : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-submit form on filter change
        document.getElementById('specialite').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('statut').addEventListener('change', function() {
            this.form.submit();
        });

        // Validation des dates
        document.getElementById('date_debut').addEventListener('change', function() {
            const dateDebut = new Date(this.value);
            const dateFin = document.getElementById('date_fin');
            const dateFinValue = new Date(dateFin.value);
            
            if (dateFinValue < dateDebut) {
                dateFin.value = this.value;
            }
        });

        console.log('📅 Planning des soutenances - Ready!');
    </script>
</body>
</html>
