<?php
/**
 * Liste des Étudiants - Secrétaire
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
    
    // Paramètres de recherche et filtrage
    $search = $_GET['search'] ?? '';
    $specialite_filter = $_GET['specialite'] ?? '';
    $statut_filter = $_GET['statut'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    // Construction de la requête WHERE
    $where_conditions = ['u.est_actif = 1'];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(ip.nom LIKE ? OR ip.prenoms LIKE ? OR e.numero_etudiant LIKE ? OR u.email LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if (!empty($specialite_filter)) {
        $where_conditions[] = "e.specialite_id = ?";
        $params[] = $specialite_filter;
    }
    
    if (!empty($statut_filter)) {
        $where_conditions[] = "e.statut_eligibilite = ?";
        $params[] = $statut_filter;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Requête principale pour récupérer les étudiants
    $sql = "
        SELECT 
            e.etudiant_id,
            e.numero_etudiant,
            e.numero_carte_etudiant,
            ip.nom,
            ip.prenoms,
            u.email,
            ip.telephone,
            ne.libelle_niveau,
            sp.libelle_specialite,
            e.annee_inscription,
            e.moyenne_generale,
            e.nombre_credits_valides,
            e.nombre_credits_requis,
            e.taux_progression,
            st.libelle_statut as statut_eligibilite,
            st.couleur_affichage as couleur_statut,
            r.titre as titre_rapport,
            r.date_depot,
            enc_ip.nom as encadreur_nom,
            enc_ip.prenoms as encadreur_prenoms
        FROM etudiants e
        JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        JOIN niveaux_etude ne ON e.niveau_id = ne.niveau_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        JOIN statuts st ON e.statut_eligibilite = st.statut_id
        LEFT JOIN rapports r ON e.etudiant_id = r.etudiant_id
        LEFT JOIN enseignants enc ON r.encadreur_id = enc.enseignant_id
        LEFT JOIN utilisateurs enc_u ON enc.utilisateur_id = enc_u.utilisateur_id
        LEFT JOIN informations_personnelles enc_ip ON enc_u.utilisateur_id = enc_ip.utilisateur_id
        WHERE $where_clause
        ORDER BY ip.nom, ip.prenoms
        LIMIT $limit OFFSET $offset
    ";
    
    $etudiants = $db->fetchAll($sql, $params);
    
    // Compter le total pour la pagination
    $count_sql = "
        SELECT COUNT(DISTINCT e.etudiant_id) as total
        FROM etudiants e
        JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        WHERE $where_clause
    ";
    
    $total_result = $db->fetch($count_sql, $params);
    $total_etudiants = $total_result['total'];
    $total_pages = ceil($total_etudiants / $limit);
    
    // Récupérer les spécialités pour le filtre
    $specialites = $db->fetchAll("SELECT specialite_id, libelle_specialite FROM specialites WHERE est_actif = 1 ORDER BY libelle_specialite");
    
    // Récupérer les statuts d'éligibilité pour le filtre
    $statuts = $db->fetchAll("SELECT statut_id, libelle_statut FROM statuts WHERE type_statut = 'Etudiant' ORDER BY ordre_affichage");
    
} catch (Exception $e) {
    error_log("Erreur liste étudiants: " . $e->getMessage());
    $etudiants = [];
    $total_etudiants = 0;
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
    <title>Liste des Étudiants - Secrétaire</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            grid-template-columns: 2fr 1fr 1fr auto;
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

        .table-container {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: var(--primary-color);
            color: var(--white);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            font-size: 0.9rem;
        }

        .table tbody tr:hover {
            background: var(--gray-50);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--white);
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--success-color);
            transition: width 0.3s ease;
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

            .table-container {
                overflow-x: auto;
            }

            .table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-users"></i> Liste des Étudiants</h1>
                <div class="breadcrumb">
                    <a href="../index.php">Tableau de bord</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Liste des étudiants</span>
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
                               placeholder="Nom, prénom, numéro étudiant ou email..."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="specialite">Spécialité</label>
                        <select id="specialite" name="specialite" class="form-control">
                            <option value="">Toutes les spécialités</option>
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
                            <option value="">Tous les statuts</option>
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
                <strong><?= number_format($total_etudiants) ?></strong> étudiant(s) trouvé(s)
                <?php if ($page > 1 || $total_pages > 1): ?>
                    - Page <?= $page ?> sur <?= $total_pages ?>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <a href="../export.php?type=etudiants<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?>" 
                   class="btn btn-success">
                    <i class="fas fa-download"></i> Exporter CSV
                </a>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Imprimer
                </button>
            </div>
        </div>

        <!-- Tableau des étudiants -->
        <div class="table-container">
            <?php if (!empty($etudiants)): ?>
                <table class="table" id="etudiantsTable">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Contact</th>
                            <th>Formation</th>
                            <th>Progression</th>
                            <th>Rapport</th>
                            <th>Encadreur</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenoms']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($etudiant['numero_etudiant']) ?></small>
                                        <?php if ($etudiant['numero_carte_etudiant']): ?>
                                            <br><small class="text-muted">Carte: <?= htmlspecialchars($etudiant['numero_carte_etudiant']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($etudiant['email']) ?><br>
                                        <?php if ($etudiant['telephone']): ?>
                                            <i class="fas fa-phone"></i> <?= htmlspecialchars($etudiant['telephone']) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($etudiant['libelle_niveau']) ?></strong><br>
                                        <?php if ($etudiant['libelle_specialite']): ?>
                                            <small><?= htmlspecialchars($etudiant['libelle_specialite']) ?></small><br>
                                        <?php endif; ?>
                                        <small class="text-muted">Promotion <?= $etudiant['annee_inscription'] ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?php if ($etudiant['moyenne_generale']): ?>
                                            <strong>Moyenne: <?= number_format($etudiant['moyenne_generale'], 2) ?>/20</strong><br>
                                        <?php endif; ?>
                                        <small>Crédits: <?= $etudiant['nombre_credits_valides'] ?>/<?= $etudiant['nombre_credits_requis'] ?></small>
                                        <div class="progress-bar" style="margin-top: 0.25rem;">
                                            <div class="progress-fill" style="width: <?= $etudiant['taux_progression'] ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= number_format($etudiant['taux_progression'], 1) ?>%</small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($etudiant['titre_rapport']): ?>
                                        <div>
                                            <strong><?= htmlspecialchars(substr($etudiant['titre_rapport'], 0, 50)) ?><?= strlen($etudiant['titre_rapport']) > 50 ? '...' : '' ?></strong>
                                            <?php if ($etudiant['date_depot']): ?>
                                                <br><small class="text-muted">Déposé le <?= date('d/m/Y', strtotime($etudiant['date_depot'])) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted">Aucun rapport</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($etudiant['encadreur_nom']): ?>
                                        <?= htmlspecialchars($etudiant['encadreur_nom'] . ' ' . $etudiant['encadreur_prenoms']) ?>
                                    <?php else: ?>
                                        <small class="text-muted">Non assigné</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge" style="background-color: <?= $etudiant['couleur_statut'] ?>">
                                        <?= htmlspecialchars($etudiant['statut_eligibilite']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-users-slash"></i>
                    <h3>Aucun étudiant trouvé</h3>
                    <p>Aucun étudiant ne correspond aux critères de recherche.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?>">
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
                        <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($specialite_filter) ? '&specialite=' . $specialite_filter : '' ?><?= !empty($statut_filter) ? '&statut=' . $statut_filter : '' ?>">
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

        // Print styles
        const printStyles = `
            @media print {
                body * { visibility: hidden; }
                .table-container, .table-container * { visibility: visible; }
                .table-container { position: absolute; left: 0; top: 0; width: 100%; }
                .btn, .pagination, .filters-section, .actions-bar { display: none !important; }
                .table { font-size: 12px; }
                .table th, .table td { padding: 8px; }
            }
        `;
        
        const styleSheet = document.createElement("style");
        styleSheet.innerText = printStyles;
        document.head.appendChild(styleSheet);

        console.log('📋 Liste des étudiants - Ready!');
    </script>
</body>
</html>
