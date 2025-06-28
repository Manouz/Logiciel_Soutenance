<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Vérification des permissions
requireAuth(ROLE_RESPONSABLE_SCOLARITE);

$db = Database::getInstance();

try {
    // Statistiques spécifiques au responsable scolarité
    $stats = [
        'total_etudiants_m2' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM etudiants e 
            INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id 
            INNER JOIN niveaux_etude ne ON e.niveau_id = ne.niveau_id
            WHERE u.est_actif = 1 AND ne.code_niveau = 'M2'
        ")['count'],
        
        'etudiants_eligibles' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM etudiants e 
            INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
            WHERE u.est_actif = 1 AND e.statut_eligibilite = 5
        ")['count'],
        
        'rapports_a_evaluer' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM rapports r
            WHERE r.statut_id IN (9, 10)
        ")['count'],
        
        'soutenances_planifiees' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM soutenances s
            WHERE s.statut_id = 14 AND s.date_prevue >= CURDATE()
        ")['count'],
        
        'notes_en_attente' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM evaluations e
            WHERE e.est_validee = 0
        ")['count']
    ];
    
    // Étudiants récemment ajoutés
    $nouveaux_etudiants = $db->fetchAll("
        SELECT 
            e.numero_etudiant,
            ip.nom,
            ip.prenoms,
            sp.libelle_specialite,
            u.date_creation
        FROM etudiants e
        INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        WHERE u.est_actif = 1
        ORDER BY u.date_creation DESC
        LIMIT 5
    ");
    
    // Rapports nécessitant attention
    $rapports_attention = $db->fetchAll("
        SELECT 
            r.rapport_id,
            r.titre,
            ip.nom,
            ip.prenoms,
            s.libelle_statut,
            s.couleur_affichage,
            r.date_depot,
            r.date_limite_depot
        FROM rapports r
        INNER JOIN etudiants et ON r.etudiant_id = et.etudiant_id
        INNER JOIN utilisateurs u ON et.utilisateur_id = u.utilisateur_id
        INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        INNER JOIN statuts s ON r.statut_id = s.statut_id
        WHERE r.statut_id IN (9, 10, 12)
        ORDER BY r.date_depot DESC
        LIMIT 8
    ");
    
    // Progression des étudiants par spécialité
    $progression_specialites = $db->fetchAll("
        SELECT 
            sp.libelle_specialite,
            COUNT(e.etudiant_id) as total_etudiants,
            SUM(CASE WHEN e.statut_eligibilite = 5 THEN 1 ELSE 0 END) as eligibles,
            AVG(e.moyenne_generale) as moyenne_generale,
            AVG(e.taux_progression) as taux_progression_moyen
        FROM etudiants e
        INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        WHERE u.est_actif = 1
        GROUP BY e.specialite_id, sp.libelle_specialite
        ORDER BY sp.libelle_specialite
    ");
    
} catch (Exception $e) {
    error_log("Erreur dashboard responsable: " . $e->getMessage());
    $stats = array_fill_keys(['total_etudiants_m2', 'etudiants_eligibles', 'rapports_a_evaluer', 'soutenances_planifiees', 'notes_en_attente'], 0);
    $nouveaux_etudiants = [];
    $rapports_attention = [];
    $progression_specialites = [];
}

$page_title = "Dashboard Responsable Scolarité";
include '../../includes/header.php';
?>

<div class="responsable-dashboard">
    <!-- Header personnalisé -->
    <div class="dashboard-header responsable-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="dashboard-title">
                        <i class="fas fa-user-graduate"></i>
                        Responsable Scolarité Master 2
                    </h1>
                    <p class="dashboard-subtitle">
                        Gestion académique et suivi des étudiants
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-actions">
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#calculMoyennesModal">
                            <i class="fas fa-calculator"></i>
                            Calculer Moyennes
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="exporterDonnees()">
                            <i class="fas fa-download"></i>
                            Exporter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Cartes de statistiques spécialisées -->
        <div class="row mb-4">
            <div class="col-xl-2-4 col-md-6 mb-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-card-body">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $stats['total_etudiants_m2'] ?></div>
                            <div class="stat-label">Étudiants M2</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up text-success"></i>
                                <span class="text-success">+5.2%</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-footer">
                        <a href="etudiants/gestion.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-cog"></i> Gérer
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-2-4 col-md-6 mb-4">
                <div class="stat-card stat-card-success">
                    <div class="stat-card-body">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $stats['etudiants_eligibles'] ?></div>
                            <div class="stat-label">Éligibles</div>
                            <div class="stat-percentage">
                                <?= $stats['total_etudiants_m2'] > 0 ? round(($stats['etudiants_eligibles'] / $stats['total_etudiants_m2']) * 100, 1) : 0 ?>%
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-footer">
                        <a href="etudiants/eligibilite.php" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-eye"></i> Vérifier
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-2-4 col-md-6 mb-4">
                <div class="stat-card stat-card-warning">
                    <div class="stat-card-body">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $stats['rapports_a_evaluer'] ?></div>
                            <div class="stat-label">Rapports à Évaluer</div>
                            <div class="stat-alert">
                                <?php if ($stats['rapports_a_evaluer'] > 5): ?>
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <span class="text-warning">Urgent</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-footer">
                        <a href="rapports/suivi.php" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-tasks"></i> Traiter
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-2-4 col-md-6 mb-4">
                <div class="stat-card stat-card-info">
                    <div class="stat-card-body">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $stats['soutenances_planifiees'] ?></div>
                            <div class="stat-label">Soutenances Prévues</div>
                            <div class="stat-period">Ce mois</div>
                        </div>
                    </div>
                    <div class="stat-card-footer">
                        <a href="rapports/planification.php" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-calendar"></i> Planning
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-2-4 col-md-6 mb-4">
                <div class="stat-card stat-card-danger">
                    <div class="stat-card-body">
                        <div class="stat-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $stats['notes_en_attente'] ?></div>
                            <div class="stat-label">Notes en Attente</div>
                            <div class="stat-action">
                                <small class="text-muted">À valider</small>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-footer">
                        <a href="notes/validation.php" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-check"></i> Valider
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides spécialisées -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="quick-actions-card responsable-actions">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-bolt"></i>
                            Actions Responsable Scolarité
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="notes/saisie.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-primary">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Saisir Notes</div>
                                        <div class="quick-action-desc">Ajouter évaluations</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="etudiants/verification.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-success">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Vérifier Éligibilité</div>
                                        <div class="quick-action-desc">Contrôles automatiques</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="rapports/evaluation.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-warning">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Évaluer Rapports</div>
                                        <div class="quick-action-desc">Notation et feedback</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="statistiques.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-info">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Statistiques</div>
                                        <div class="quick-action-desc">Analyses détaillées</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal - 3 colonnes -->
        <div class="row">
            <!-- Nouveaux étudiants -->
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="fas fa-user-plus"></i>
                            Nouveaux Étudiants
                        </h5>
                        <span class="badge bg-primary"><?= count($nouveaux_etudiants) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="student-list">
                            <?php foreach ($nouveaux_etudiants as $etudiant): ?>
                            <div class="student-item">
                                <div class="student-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="student-info">
                                    <div class="student-name">
                                        <?= htmlspecialchars($etudiant['prenoms'] . ' ' . $etudiant['nom']) ?>
                                    </div>
                                    <div class="student-details">
                                        <span class="student-number"><?= htmlspecialchars($etudiant['numero_etudiant']) ?></span>
                                        <span class="student-speciality"><?= htmlspecialchars($etudiant['libelle_specialite'] ?? 'Non définie') ?></span>
                                    </div>
                                    <div class="student-date">
                                        <i class="fas fa-clock"></i>
                                        <?= timeAgo($etudiant['date_creation']) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="etudiants/gestion.php" class="btn btn-sm btn-outline-primary">
                                Voir tous les étudiants
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rapports nécessitant attention -->
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Rapports Attention
                        </h5>
                        <span class="badge bg-warning"><?= count($rapports_attention) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="rapport-list">
                            <?php foreach ($rapports_attention as $rapport): ?>
                            <div class="rapport-item">
                                <div class="rapport-status">
                                    <span class="status-badge" style="background-color: <?= $rapport['couleur_affichage'] ?>">
                                        <?= htmlspecialchars($rapport['libelle_statut']) ?>
                                    </span>
                                </div>
                                <div class="rapport-info">
                                    <div class="rapport-title">
                                        <?= htmlspecialchars(substr($rapport['titre'], 0, 40) . '...') ?>
                                    </div>
                                    <div class="rapport-student">
                                        <i class="fas fa-user"></i>
                                        <?= htmlspecialchars($rapport['prenoms'] . ' ' . $rapport['nom']) ?>
                                    </div>
                                    <div class="rapport-dates">
                                        <?php if ($rapport['date_depot']): ?>
                                        <span class="depot-date">
                                            <i class="fas fa-upload"></i>
                                            Déposé: <?= formatDate($rapport['date_depot']) ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($rapport['date_limite_depot'] && strtotime($rapport['date_limite_depot']) < time()): ?>
                                        <span class="limite-date text-danger">
                                            <i class="fas fa-clock"></i>
                                            Limite dépassée
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="rapports/suivi.php" class="btn btn-sm btn-outline-warning">
                                Gérer tous les rapports
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progression par spécialité -->
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Progression par Spécialité
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="speciality-progress">
                            <?php foreach ($progression_specialites as $spec): ?>
                            <div class="speciality-item">
                                <div class="speciality-header">
                                    <span class="speciality-name">
                                        <?= htmlspecialchars($spec['libelle_specialite'] ?? 'Non définie') ?>
                                    </span>
                                    <span class="speciality-count">
                                        <?= $spec['total_etudiants'] ?> étudiants
                                    </span>
                                </div>
                                <div class="speciality-stats">
                                    <div class="progress-item">
                                        <label>Taux d'éligibilité</label>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" 
                                                 style="width: <?= $spec['total_etudiants'] > 0 ? ($spec['eligibles'] / $spec['total_etudiants'] * 100) : 0 ?>%">
                                            </div>
                                        </div>
                                        <span class="progress-text">
                                            <?= $spec['eligibles'] ?>/<?= $spec['total_etudiants'] ?> 
                                            (<?= $spec['total_etudiants'] > 0 ? round(($spec['eligibles'] / $spec['total_etudiants']) * 100, 1) : 0 ?>%)
                                        </span>
                                    </div>
                                    <div class="speciality-metrics">
                                        <div class="metric">
                                            <span class="metric-label">Moyenne générale</span>
                                            <span class="metric-value">
                                                <?= $spec['moyenne_generale'] ? number_format($spec['moyenne_generale'], 2) : 'N/A' ?>/20
                                            </span>
                                        </div>
                                        <div class="metric">
                                            <span class="metric-label">Progression moyenne</span>
                                            <span class="metric-value">
                                                <?= $spec['taux_progression_moyen'] ? number_format($spec['taux_progression_moyen'], 1) : 'N/A' ?>%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques détaillés -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Évolution des Performances
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartPerformances" height="100"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-pie-chart"></i>
                            Répartition Éligibilité
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartEligibilite" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de calcul des moyennes -->
<div class="modal fade" id="calculMoyennesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Calculer les Moyennes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="calculMoyennesForm">
                    <div class="mb-3">
                        <label class="form-label">Spécialité</label>
                        <select class="form-select" name="specialite_id">
                            <option value="">Toutes les spécialités</option>
                            <?php foreach ($progression_specialites as $spec): ?>
                            <option value="<?= $spec['specialite_id'] ?? '' ?>">
                                <?= htmlspecialchars($spec['libelle_specialite'] ?? 'Non définie') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="recalculer_tout" checked>
                            <label class="form-check-label">
                                Recalculer toutes les moyennes
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="lancerCalcul()">
                    <i class="fas fa-calculator"></i> Calculer
                </button>
            </div>
        </div>
    </div>
</div>

<?php
include '../../includes/footer.php';

// Fonctions utilitaires (reprises de admin)
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'À l\'instant';
    if ($time < 3600) return floor($time/60) . ' min';
    if ($time < 86400) return floor($time/3600) . ' h';
    if ($time < 2592000) return floor($time/86400) . ' j';
    return date('d/m/Y', strtotime($datetime));
}

function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}
?>

<script>
// Initialisation des graphiques
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des performances
    const ctxPerf = document.getElementById('chartPerformances').getContext('2d');
    new Chart(ctxPerf, {
        type: 'line',
        data: {
            labels: ['Sept', 'Oct', 'Nov', 'Déc', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [{
                label: 'Moyenne Générale',
                data: [12.5, 13.2, 13.8, 14.1, 14.5, 14.3, 14.8, 15.1, 15.3, 15.6],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4
            }, {
                label: 'Taux d\'Éligibilité (%)',
                data: [45, 52, 58, 62, 68, 65, 72, 75, 78, 82],
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Graphique d'éligibilité
    const ctxElig = document.getElementById('chartEligibilite').getContext('2d');
    new Chart(ctxElig, {
        type: 'doughnut',
        data: {
            labels: ['Éligibles', 'Non Éligibles', 'En Attente'],
            datasets: [{
                data: [<?= $stats['etudiants_eligibles'] ?>, 
                       <?= $stats['total_etudiants_m2'] - $stats['etudiants_eligibles'] ?>, 
                       5],
                backgroundColor: ['#27ae60', '#e74c3c', '#f39c12']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});

// Fonction de calcul des moyennes
function lancerCalcul() {
    const form = document.getElementById('calculMoyennesForm');
    const formData = new FormData(form);
    
    // Affichage du loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calcul en cours...';
    btn.disabled = true;
    
    fetch('api/calculer_moyennes.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Moyennes calculées avec succès !');
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur lors du calcul');
        console.error(error);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        bootstrap.Modal.getInstance(document.getElementById('calculMoyennesModal')).hide();
    });
}

// Fonction d'export
function exporterDonnees() {
    window.open('api/export_donnees.php', '_blank');
}
</script>