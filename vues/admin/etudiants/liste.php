<?php
/**
 * Liste des Étudiants - Administration
 * Fichier: pages/admin/etudiants/liste.php
 */

require_once '../../../config/database.php';
require_once '../../../includes/auth.php';

// Vérification des permissions
requireAuth(ROLE_ADMIN);

$db = Database::getInstance();

try {
    // Récupération de la liste complète des étudiants avec toutes les informations
    $etudiants = $db->fetchAll("
        SELECT 
            e.etudiant_id,
            e.numero_etudiant,
            e.numero_carte_etudiant,
            e.moyenne_generale,
            e.nombre_credits_valides,
            e.nombre_credits_requis,
            e.taux_progression,
            e.annee_inscription,
            e.date_inscription,
            ip.nom,
            ip.prenoms,
            ip.date_naissance,
            ip.telephone,
            ip.genre,
            ip.nationalite,
            u.email,
            u.est_actif,
            u.derniere_connexion,
            u.date_creation,
            ne.libelle_niveau,
            sp.libelle_specialite,
            st.libelle_statut as statut_eligibilite,
            st.couleur_affichage,
            reg.montant_total as frais_scolarite,
            reg.montant_paye,
            reg.montant_restant,
            reg_stat.libelle_statut as statut_reglement,
            COUNT(r.rapport_id) as nombre_rapports
        FROM etudiants e
        INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        INNER JOIN niveaux_etude ne ON e.niveau_id = ne.niveau_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        INNER JOIN statuts st ON e.statut_eligibilite = st.statut_id
        LEFT JOIN reglements reg ON e.etudiant_id = reg.etudiant_id
        LEFT JOIN statuts reg_stat ON reg.statut_id = reg_stat.statut_id
        LEFT JOIN rapports r ON e.etudiant_id = r.etudiant_id
        GROUP BY e.etudiant_id
        ORDER BY ip.nom, ip.prenoms
    ");
    
    // Statistiques globales
    $stats = [
        'total' => count($etudiants),
        'actifs' => count(array_filter($etudiants, fn($e) => $e['est_actif'] == 1)),
        'eligibles' => count(array_filter($etudiants, fn($e) => $e['statut_eligibilite'] === 'Éligible')),
        'avec_rapports' => count(array_filter($etudiants, fn($e) => $e['nombre_rapports'] > 0))
    ];
    
    // Spécialités pour le filtre
    $specialites = $db->fetchAll("
        SELECT specialite_id, libelle_specialite 
        FROM specialites 
        WHERE est_actif = 1 
        ORDER BY libelle_specialite
    ");
    
} catch (Exception $e) {
    error_log("Erreur liste étudiants: " . $e->getMessage());
    $etudiants = [];
    $stats = ['total' => 0, 'actifs' => 0, 'eligibles' => 0, 'avec_rapports' => 0];
    $specialites = [];
}

$page_title = "Gestion des Étudiants";
$custom_css = ['admin/admin-crud.css'];
$custom_js = ['admin/crud-operations.js'];

include '../../../includes/header.php';
?>

<div class="admin-crud-page">
    <!-- Header de la page -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">
                        <i class="fas fa-graduation-cap"></i>
                        Gestion des Étudiants
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Étudiants</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 text-end">
                    <div class="page-actions">
                        <button class="btn btn-success" onclick="window.location.href='ajouter.php'">
                            <i class="fas fa-plus"></i>
                            Nouvel Étudiant
                        </button>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-download"></i>
                                Exporter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportData('excel')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportData('csv')">
                                    <i class="fas fa-file-csv"></i> CSV
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Cartes de statistiques -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card primary">
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['total'] ?></div>
                        <div class="stat-label">Total Étudiants</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card success">
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['actifs'] ?></div>
                        <div class="stat-label">Étudiants Actifs</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card warning">
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['eligibles'] ?></div>
                        <div class="stat-label">Éligibles Soutenance</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card info">
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['avec_rapports'] ?></div>
                        <div class="stat-label">Avec Rapports</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-card">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Recherche</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="searchInput" 
                                           placeholder="Nom, prénom, numéro...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Spécialité</label>
                                <select class="form-select" id="specialiteFilter">
                                    <option value="">Toutes</option>
                                    <?php foreach ($specialites as $specialite): ?>
                                    <option value="<?= $specialite['specialite_id'] ?>">
                                        <?= htmlspecialchars($specialite['libelle_specialite']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Statut</label>
                                <select class="form-select" id="statutFilter">
                                    <option value="">Tous</option>
                                    <option value="1">Actifs</option>
                                    <option value="0">Inactifs</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Éligibilité</label>
                                <select class="form-select" id="eligibiliteFilter">
                                    <option value="">Tous</option>
                                    <option value="Éligible">Éligibles</option>
                                    <option value="Non éligible">Non éligibles</option>
                                    <option value="En attente de vérification">En attente</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Année</label>
                                <select class="form-select" id="anneeFilter">
                                    <option value="">Toutes</option>
                                    <?php 
                                    $annees = array_unique(array_column($etudiants, 'annee_inscription'));
                                    rsort($annees);
                                    foreach ($annees as $annee): ?>
                                    <option value="<?= $annee ?>"><?= $annee ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des étudiants -->
        <div class="row">
            <div class="col-12">
                <div class="data-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-list"></i>
                            Liste des Étudiants
                        </h5>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="etudiantsTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th>Photo</th>
                                        <th>Informations</th>
                                        <th>Numéros</th>
                                        <th>Contact</th>
                                        <th>Académique</th>
                                        <th>Progression</th>
                                        <th>Statuts</th>
                                        <th>Dernière Connexion</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($etudiants as $etudiant): ?>
                                    <tr data-etudiant-id="<?= $etudiant['etudiant_id'] ?>">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input row-select" type="checkbox" 
                                                       value="<?= $etudiant['etudiant_id'] ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="student-avatar">
                                                <img src="<?= ASSETS_URL ?>images/avatars/default.png" 
                                                     alt="Avatar" class="rounded-circle" width="40" height="40">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="student-info">
                                                <div class="student-name">
                                                    <strong><?= htmlspecialchars($etudiant['prenoms'] . ' ' . $etudiant['nom']) ?></strong>
                                                </div>
                                                <div class="student-details">
                                                    <?php if ($etudiant['genre']): ?>
                                                    <span class="badge bg-light text-dark">
                                                        <?= $etudiant['genre'] == 'M' ? 'Homme' : 'Femme' ?>
                                                    </span>
                                                    <?php endif; ?>
                                                    <?php if ($etudiant['date_naissance']): ?>
                                                    <small class="text-muted">
                                                        <?= calculateAge($etudiant['date_naissance']) ?> ans
                                                    </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="student-numbers">
                                                <div class="numero-etudiant">
                                                    <strong><?= htmlspecialchars($etudiant['numero_etudiant']) ?></strong>
                                                </div>
                                                <?php if ($etudiant['numero_carte_etudiant']): ?>
                                                <div class="numero-carte">
                                                    <small class="text-muted">
                                                        Carte: <?= htmlspecialchars($etudiant['numero_carte_etudiant']) ?>
                                                    </small>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <div class="email">
                                                    <i class="fas fa-envelope text-muted"></i>
                                                    <small><?= htmlspecialchars($etudiant['email']) ?></small>
                                                </div>
                                                <?php if ($etudiant['telephone']): ?>
                                                <div class="telephone">
                                                    <i class="fas fa-phone text-muted"></i>
                                                    <small><?= htmlspecialchars($etudiant['telephone']) ?></small>
                                                </div>
                                                <?php endif; ?>
                                                <?php if ($etudiant['nationalite'] && $etudiant['nationalite'] !== 'Côte d\'Ivoire'): ?>
                                                <div class="nationalite">
                                                    <i class="fas fa-globe text-muted"></i>
                                                    <small><?= htmlspecialchars($etudiant['nationalite']) ?></small>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="academic-info">
                                                <div class="niveau">
                                                    <strong><?= htmlspecialchars($etudiant['libelle_niveau']) ?></strong>
                                                </div>
                                                <?php if ($etudiant['libelle_specialite']): ?>
                                                <div class="specialite">
                                                    <span class="badge bg-primary">
                                                        <?= htmlspecialchars($etudiant['libelle_specialite']) ?>
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                                <div class="annee-inscription">
                                                    <small class="text-muted">
                                                        Promotion <?= $etudiant['annee_inscription'] ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="progression-info">
                                                <div class="moyenne">
                                                    <?php if ($etudiant['moyenne_generale']): ?>
                                                    <span class="badge bg-<?= $etudiant['moyenne_generale'] >= 10 ? 'success' : 'danger' ?>">
                                                        <?= number_format($etudiant['moyenne_generale'], 2) ?>/20
                                                    </span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary">N/A</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="credits">
                                                    <small class="text-muted">
                                                        <?= $etudiant['nombre_credits_valides'] ?>/<?= $etudiant['nombre_credits_requis'] ?> crédits
                                                    </small>
                                                </div>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-info" 
                                                         style="width: <?= $etudiant['taux_progression'] ?>%">
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?= number_format($etudiant['taux_progression'], 1) ?>%</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="status-info">
                                                <div class="statut-compte">
                                                    <?php if ($etudiant['est_actif']): ?>
                                                    <span class="badge bg-success">Actif</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-danger">Inactif</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="statut-eligibilite">
                                                    <span class="badge" style="background-color: <?= $etudiant['couleur_affichage'] ?>">
                                                        <?= htmlspecialchars($etudiant['statut_eligibilite']) ?>
                                                    </span>
                                                </div>
                                                <?php if ($etudiant['statut_reglement']): ?>
                                                <div class="statut-reglement">
                                                    <small class="text-muted">
                                                        Règlement: <?= htmlspecialchars($etudiant['statut_reglement']) ?>
                                                    </small>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($etudiant['derniere_connexion']): ?>
                                            <small class="text-muted">
                                                <?= timeAgo($etudiant['derniere_connexion']) ?>
                                            </small>
                                            <?php else: ?>
                                            <small class="text-danger">Jamais connecté</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewStudent(<?= $etudiant['etudiant_id'] ?>)"
                                                            data-bs-toggle="tooltip" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="editStudent(<?= $etudiant['etudiant_id'] ?>)"
                                                            data-bs-toggle="tooltip" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-<?= $etudiant['est_actif'] ? 'warning' : 'info' ?>" 
                                                            onclick="toggleStudent(<?= $etudiant['etudiant_id'] ?>, <?= $etudiant['est_actif'] ? 'false' : 'true' ?>)"
                                                            data-bs-toggle="tooltip" title="<?= $etudiant['est_actif'] ? 'Désactiver' : 'Activer' ?>">
                                                        <i class="fas fa-<?= $etudiant['est_actif'] ? 'user-slash' : 'user-check' ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteStudent(<?= $etudiant['etudiant_id'] ?>)"
                                                            data-bs-toggle="tooltip" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions groupées -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="bulk-actions" id="bulkActions" style="display: none;">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <span id="selectedCount">0</span> étudiant(s) sélectionné(s)
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-success" onclick="bulkActivate()">
                                            <i class="fas fa-user-check"></i> Activer
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="bulkDeactivate()">
                                            <i class="fas fa-user-slash"></i> Désactiver
                                        </button>
                                        <button class="btn btn-outline-primary" onclick="bulkExport()">
                                            <i class="fas fa-download"></i> Exporter
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="bulkDelete()">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de détails étudiant -->
<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de l'Étudiant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="studentModalBody">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>

<?php include '../../../includes/footer.php'; ?>