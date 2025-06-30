<?php
/**
 * Export des données - Secrétaire
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

// Traitement de l'export
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    $type = $_GET['type'] ?? 'etudiants';
    
    try {
        $db = Database::getInstance();
        
        if ($type === 'etudiants') {
            exportEtudiants($db);
        } elseif ($type === 'soutenances') {
            exportSoutenances($db);
        }
    } catch (Exception $e) {
        error_log("Erreur export: " . $e->getMessage());
        header('Location: export.php?error=1');
        exit;
    }
}

function exportEtudiants($db) {
    // Paramètres de filtrage
    $search = $_GET['search'] ?? '';
    $specialite_filter = $_GET['specialite'] ?? '';
    $statut_filter = $_GET['statut'] ?? '';
    
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
    
    $sql = "
        SELECT 
            e.numero_etudiant as 'Numéro Étudiant',
            e.numero_carte_etudiant as 'Numéro Carte',
            ip.nom as 'Nom',
            ip.prenoms as 'Prénoms',
            u.email as 'Email',
            ip.telephone as 'Téléphone',
            ne.libelle_niveau as 'Niveau',
            sp.libelle_specialite as 'Spécialité',
            e.annee_inscription as 'Année Inscription',
            e.moyenne_generale as 'Moyenne Générale',
            e.nombre_credits_valides as 'Crédits Validés',
            e.nombre_credits_requis as 'Crédits Requis',
            e.taux_progression as 'Taux Progression (%)',
            st.libelle_statut as 'Statut Éligibilité',
            r.titre as 'Titre Rapport',
            r.date_depot as 'Date Dépôt Rapport',
            CONCAT(enc_ip.nom, ' ', enc_ip.prenoms) as 'Encadreur'
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
    ";
    
    $etudiants = $db->fetchAll($sql, $params);
    
    // Génération du fichier CSV
    $filename = 'liste_etudiants_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // BOM pour UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // En-têtes
    if (!empty($etudiants)) {
        fputcsv($output, array_keys($etudiants[0]), ';');
        
        // Données
        foreach ($etudiants as $etudiant) {
            fputcsv($output, $etudiant, ';');
        }
    }
    
    fclose($output);
    exit;
}

function exportSoutenances($db) {
    // Paramètres de filtrage
    $date_debut = $_GET['date_debut'] ?? '';
    $date_fin = $_GET['date_fin'] ?? '';
    $statut_filter = $_GET['statut'] ?? '';
    
    // Construction de la requête WHERE
    $where_conditions = ['1=1'];
    $params = [];
    
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
    
    $where_clause = implode(' AND ', $where_conditions);
    
    $sql = "
        SELECT 
            DATE(s.date_prevue) as 'Date',
            TIME(s.date_prevue) as 'Heure Début',
            TIME(DATE_ADD(s.date_prevue, INTERVAL s.duree_prevue MINUTE)) as 'Heure Fin',
            e.numero_etudiant as 'Numéro Étudiant',
            CONCAT(ip.nom, ' ', ip.prenoms) as 'Nom Étudiant',
            sp.libelle_specialite as 'Spécialité',
            r.titre as 'Titre Rapport',
            sal.nom_salle as 'Salle',
            sal.batiment as 'Bâtiment',
            st.libelle_statut as 'Statut Soutenance',
            s.duree_prevue as 'Durée (min)',
            GROUP_CONCAT(
                CONCAT(jury_ip.nom, ' ', jury_ip.prenoms, ' (', j.role_jury, ')')
                SEPARATOR '; '
            ) as 'Composition Jury'
        FROM soutenances s
        JOIN rapports r ON s.rapport_id = r.rapport_id
        JOIN etudiants e ON r.etudiant_id = e.etudiant_id
        JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        LEFT JOIN salles sal ON s.salle_id = sal.salle_id
        JOIN statuts st ON s.statut_id = st.statut_id
        LEFT JOIN jurys j ON s.soutenance_id = j.soutenance_id
        LEFT JOIN enseignants jury_ens ON j.enseignant_id = jury_ens.enseignant_id
        LEFT JOIN utilisateurs jury_u ON jury_ens.utilisateur_id = jury_u.utilisateur_id
        LEFT JOIN informations_personnelles jury_ip ON jury_u.utilisateur_id = jury_ip.utilisateur_id
        WHERE $where_clause
        GROUP BY s.soutenance_id
        ORDER BY s.date_prevue
    ";
    
    $soutenances = $db->fetchAll($sql, $params);
    
    // Génération du fichier CSV
    $filename = 'planning_soutenances_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // BOM pour UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // En-têtes
    if (!empty($soutenances)) {
        fputcsv($output, array_keys($soutenances[0]), ';');
        
        // Données
        foreach ($soutenances as $soutenance) {
            fputcsv($output, $soutenance, ';');
        }
    }
    
    fclose($output);
    exit;
}

$userName = SessionManager::getUserName();

try {
    $db = Database::getInstance();
    
    // Récupérer les spécialités pour les filtres
    $specialites = $db->fetchAll("SELECT specialite_id, libelle_specialite FROM specialites WHERE est_actif = 1 ORDER BY libelle_specialite");
    
    // Récupérer les statuts pour les filtres
    $statuts_etudiant = $db->fetchAll("SELECT statut_id, libelle_statut FROM statuts WHERE type_statut = 'Etudiant' ORDER BY ordre_affichage");
    $statuts_soutenance = $db->fetchAll("SELECT statut_id, libelle_statut FROM statuts WHERE type_statut = 'Soutenance' ORDER BY ordre_affichage");
    
} catch (Exception $e) {
    error_log("Erreur page export: " . $e->getMessage());
    $specialites = [];
    $statuts_etudiant = [];
    $statuts_soutenance = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export des Données - Secrétaire</title>
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
            max-width: 1200px;
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

        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }

        .export-card {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .export-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .export-card h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .export-card p {
            color: var(--gray-600);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
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
            font-size: 1rem;
            width: 100%;
            justify-content: center;
        }

        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-info {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .features-list {
            list-style: none;
            margin: 1rem 0;
        }

        .features-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
        }

        .features-list li i {
            color: var(--success-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .export-grid {
                grid-template-columns: 1fr;
            }

            .export-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-download"></i> Export des Données</h1>
                <div class="breadcrumb">
                    <a href="../index.php">Tableau de bord</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Export des données</span>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                Une erreur s'est produite lors de l'export. Veuillez réessayer.
            </div>
        <?php endif; ?>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Les fichiers sont exportés au format CSV avec encodage UTF-8. Vous pouvez les ouvrir avec Excel, LibreOffice ou tout autre tableur.
        </div>

        <div class="export-grid">
            <!-- Export Liste des Étudiants -->
            <div class="export-card">
                <h3><i class="fas fa-users"></i> Liste des Étudiants</h3>
                <p>Exportez la liste complète des étudiants avec leurs informations personnelles, académiques et de progression.</p>
                
                <ul class="features-list">
                    <li><i class="fas fa-check"></i> Informations personnelles et contact</li>
                    <li><i class="fas fa-check"></i> Progression académique et notes</li>
                    <li><i class="fas fa-check"></i> Statut d'éligibilité</li>
                    <li><i class="fas fa-check"></i> Informations sur les rapports</li>
                    <li><i class="fas fa-check"></i> Encadreurs assignés</li>
                </ul>

                <form method="GET" action="">
                    <input type="hidden" name="action" value="export">
                    <input type="hidden" name="type" value="etudiants">
                    
                    <div class="form-group">
                        <label for="search_etudiant">Recherche (optionnel)</label>
                        <input type="text" 
                               id="search_etudiant" 
                               name="search" 
                               class="form-control" 
                               placeholder="Nom, prénom, numéro étudiant...">
                    </div>
                    
                    <div class="form-group">
                        <label for="specialite_etudiant">Spécialité</label>
                        <select id="specialite_etudiant" name="specialite" class="form-control">
                            <option value="">Toutes les spécialités</option>
                            <?php foreach ($specialites as $specialite): ?>
                                <option value="<?= $specialite['specialite_id'] ?>">
                                    <?= htmlspecialchars($specialite['libelle_specialite']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="statut_etudiant">Statut d'éligibilité</label>
                        <select id="statut_etudiant" name="statut" class="form-control">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statuts_etudiant as $statut): ?>
                                <option value="<?= $statut['statut_id'] ?>">
                                    <?= htmlspecialchars($statut['libelle_statut']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download"></i> Exporter la liste des étudiants
                    </button>
                </form>
            </div>

            <!-- Export Planning des Soutenances -->
            <div class="export-card">
                <h3><i class="fas fa-calendar-alt"></i> Planning des Soutenances</h3>
                <p>Exportez le planning complet des soutenances avec toutes les informations détaillées.</p>
                
                <ul class="features-list">
                    <li><i class="fas fa-check"></i> Dates et horaires des soutenances</li>
                    <li><i class="fas fa-check"></i> Informations des étudiants</li>
                    <li><i class="fas fa-check"></i> Salles et équipements</li>
                    <li><i class="fas fa-check"></i> Composition des jurys</li>
                    <li><i class="fas fa-check"></i> Statuts des soutenances</li>
                </ul>

                <form method="GET" action="">
                    <input type="hidden" name="action" value="export">
                    <input type="hidden" name="type" value="soutenances">
                    
                    <div class="form-group">
                        <label for="date_debut">Date de début</label>
                        <input type="date" 
                               id="date_debut" 
                               name="date_debut" 
                               class="form-control"
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_fin">Date de fin</label>
                        <input type="date" 
                               id="date_fin" 
                               name="date_fin" 
                               class="form-control"
                               value="<?= date('Y-m-d', strtotime('+3 months')) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="statut_soutenance">Statut</label>
                        <select id="statut_soutenance" name="statut" class="form-control">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statuts_soutenance as $statut): ?>
                                <option value="<?= $statut['statut_id'] ?>">
                                    <?= htmlspecialchars($statut['libelle_statut']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download"></i> Exporter le planning
                    </button>
                </form>
            </div>
        </div>

        <!-- Actions rapides -->
        <div style="margin-top: 2rem; text-align: center;">
            <a href="../index.php" class="btn btn-primary" style="width: auto; margin-right: 1rem;">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
            <a href="liste.php" class="btn btn-primary" style="width: auto;">
                <i class="fas fa-users"></i> Voir la liste des étudiants
            </a>
        </div>
    </div>

    <script>
        // Validation des dates
        document.getElementById('date_debut').addEventListener('change', function() {
            const dateDebut = new Date(this.value);
            const dateFin = document.getElementById('date_fin');
            const dateFinValue = new Date(dateFin.value);
            
            if (dateFinValue < dateDebut) {
                dateFin.value = this.value;
            }
        });

        console.log('📊 Export des données - Ready!');
    </script>
</body>
</html>
