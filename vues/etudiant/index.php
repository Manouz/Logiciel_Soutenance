<?php
/**
 * Dashboard Étudiant - Page Principale
 * Système de Validation Académique - Université Félix Houphouët-Boigny
 */

// Configuration et sécurité
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/functions.php';
require_once '../../classes/User.php';

// Vérifier l'authentification et les permissions
requireAuth();
requirePermission(ACCESS_LEVEL_STUDENT);

// Vérifier que l'utilisateur est bien un étudiant
if (!hasRole('Étudiant')) {
    redirect('pages/unauthorized.php');
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Récupérer les informations détaillées de l'étudiant
    $sql = "SELECT 
                e.*,
                ip.nom,
                ip.prenoms,
                ip.telephone,
                ip.date_naissance,
                ne.libelle_niveau,
                sp.libelle_specialite,
                s.libelle_statut as statut_eligibilite_libelle,
                s.couleur_affichage as statut_couleur
            FROM etudiants e
            INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
            INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
            INNER JOIN niveaux_etude ne ON e.niveau_id = ne.niveau_id
            LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
            INNER JOIN statuts s ON e.statut_eligibilite = s.statut_id
            WHERE u.utilisateur_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $etudiant = $stmt->fetch();
    
    if (!$etudiant) {
        throw new Exception("Informations étudiant non trouvées");
    }
    
    // Récupérer le rapport de l'étudiant (s'il existe)
    $sql = "SELECT 
                r.*,
                s.libelle_statut as statut_rapport,
                s.couleur_affichage as statut_couleur,
                CONCAT(ip_enc.prenoms, ' ', ip_enc.nom) as nom_encadreur,
                enc.numero_enseignant
            FROM rapports r
            INNER JOIN statuts s ON r.statut_id = s.statut_id
            LEFT JOIN enseignants enc ON r.encadreur_id = enc.enseignant_id
            LEFT JOIN utilisateurs u_enc ON enc.utilisateur_id = u_enc.utilisateur_id
            LEFT JOIN informations_personnelles ip_enc ON u_enc.utilisateur_id = ip_enc.utilisateur_id
            WHERE r.etudiant_id = ?
            ORDER BY r.date_creation DESC
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$etudiant['etudiant_id']]);
    $rapport = $stmt->fetch();
    
    // Statistiques de progression
    $stats = [
        'progression_rapport' => 0,
        'mots_rediges' => 0,
        'objectif_mots' => 15000,
        'jours_restants' => 0,
        'moyenne_generale' => $etudiant['moyenne_generale'] ?? 0,
        'credits_valides' => $etudiant['nombre_credits_valides'] ?? 0,
        'credits_requis' => $etudiant['nombre_credits_requis'] ?? 60
    ];
    
    if ($rapport) {
        $stats['progression_rapport'] = min(100, ($rapport['nombre_mots'] / 15000) * 100);
        $stats['mots_rediges'] = $rapport['nombre_mots'] ?? 0;
        
        // Calculer les jours restants jusqu'à la date limite
        if ($rapport['date_limite_depot']) {
            $dateLimite = new DateTime($rapport['date_limite_depot']);
            $maintenant = new DateTime();
            $diff = $maintenant->diff($dateLimite);
            $stats['jours_restants'] = $diff->invert ? 0 : $diff->days;
        }
    }
    
    // Récupérer les notifications récentes
    $sql = "SELECT 
                n.*,
                DATE(n.date_creation) as date_creation_formatted
            FROM notifications n
            WHERE n.utilisateur_id = ?
            ORDER BY n.date_creation DESC
            LIMIT 5";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();
    
    // Récupérer les événements à venir
    $sql = "SELECT 
                ce.*,
                DATE(ce.start_date) as date_formatted,
                TIME(ce.start_date) as heure_formatted
            FROM calendar_events ce
            WHERE ce.student_id = ?
            AND ce.start_date >= NOW()
            ORDER BY ce.start_date ASC
            LIMIT 5";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $evenements = $stmt->fetchAll();
    
    // Récupérer les réclamations en cours
    $sql = "SELECT 
                rec.*,
                s.libelle_statut as statut_libelle,
                s.couleur_affichage as statut_couleur
            FROM reclamations rec
            INNER JOIN statuts s ON rec.statut_id = s.statut_id
            WHERE rec.etudiant_id = ?
            AND rec.statut_id NOT IN (25, 26) -- Pas résolue ou fermée
            ORDER BY rec.date_creation DESC
            LIMIT 3";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$etudiant['etudiant_id']]);
    $reclamations = $stmt->fetchAll();
    
    // Récupérer les feedbacks récents
    $sql = "SELECT 
                f.*,
                CONCAT(ip_eval.prenoms, ' ', ip_eval.nom) as nom_evaluateur,
                DATE(f.date_creation) as date_feedback
            FROM feedbacks f
            LEFT JOIN enseignants ens ON f.evaluator_id = ens.enseignant_id
            LEFT JOIN utilisateurs u_eval ON ens.utilisateur_id = u_eval.utilisateur_id
            LEFT JOIN informations_personnelles ip_eval ON u_eval.utilisateur_id = ip_eval.utilisateur_id
            WHERE f.report_id = ?
            ORDER BY f.date_creation DESC
            LIMIT 3";
    
    $feedbacks = [];
    if ($rapport) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rapport['rapport_id']]);
        $feedbacks = $stmt->fetchAll();
    }
    
} catch (Exception $e) {
    error_log("Erreur dashboard étudiant: " . $e->getMessage());
    $error_message = "Erreur lors du chargement des données";
}

// Configuration de la page
$pageTitle = "Dashboard Étudiant";
$pageDescription = "Tableau de bord personnel de l'étudiant";
$additionalCSS = ['etudiant/dashboard-etudiant.css', 'etudiant/etudiant-style.css'];
$additionalJS = ['etudiant/dashboard-etudiant.js'];

$breadcrumb = [
    ['title' => 'Dashboard', 'url' => '#']
];

$pageHeader = [
    'title' => 'Tableau de Bord',
    'subtitle' => 'Bienvenue ' . ($etudiant['prenoms'] ?? '') . ', suivez votre progression académique'
];
?>

<?php include '../../includes/header.php'; ?>

<div class="container-fluid">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= escape($error_message) ?>
        </div>
    <?php endif; ?>
    
    <!-- Cartes statistiques principales -->
    <div class="row mb-4">
        <!-- Progression du rapport -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card rapport-progress h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stat-label">Progression Rapport</div>
                            <div class="stat-value"><?= number_format($stats['progression_rapport'], 1) ?>%</div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-primary" 
                                     style="width: <?= $stats['progression_rapport'] ?>%"
                                     aria-valuenow="<?= $stats['progression_rapport'] ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mots rédigés -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card mots-rediges h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-pen"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stat-label">Mots Rédigés</div>
                            <div class="stat-value"><?= number_format($stats['mots_rediges']) ?></div>
                            <div class="stat-subtitle text-muted">
                                Objectif: <?= number_format($stats['objectif_mots']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Moyenne générale -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card moyenne-generale h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stat-label">Moyenne Générale</div>
                            <div class="stat-value"><?= number_format($stats['moyenne_generale'], 2) ?>/20</div>
                            <div class="stat-subtitle text-muted">
                                <?= $stats['credits_valides'] ?>/<?= $stats['credits_requis'] ?> crédits
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Jours restants -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card jours-restants h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon <?= $stats['jours_restants'] <= 7 ? 'bg-danger' : 'bg-info' ?>">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stat-label">Jours Restants</div>
                            <div class="stat-value"><?= $stats['jours_restants'] ?></div>
                            <div class="stat-subtitle text-muted">
                                <?php if ($stats['jours_restants'] <= 7): ?>
                                    <span class="text-danger">Échéance proche!</span>
                                <?php else: ?>
                                    Jusqu'à la soutenance
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ligne principale avec timeline et activités -->
    <div class="row mb-4">
        <!-- Timeline du projet -->
        <div class="col-lg-8">
            <div class="card timeline-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-timeline me-2"></i>
                        Timeline de Mon Projet
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php
                        $etapes = [
                            ['title' => 'Inscription et Choix du Sujet', 'status' => 'completed', 'date' => 'Sept 2024'],
                            ['title' => 'Recherche et Planification', 'status' => 'completed', 'date' => 'Oct 2024'],
                            ['title' => 'Rédaction du Rapport', 'status' => $rapport ? 'current' : 'pending', 'date' => 'Nov-Mai 2025'],
                            ['title' => 'Soumission du Rapport', 'status' => $rapport && $rapport['statut_id'] >= RAPPORT_DEPOSE ? 'completed' : 'pending', 'date' => 'Juin 2025'],
                            ['title' => 'Validation par la Commission', 'status' => $rapport && $rapport['statut_id'] >= RAPPORT_VALIDE ? 'completed' : 'pending', 'date' => 'Juillet 2025'],
                            ['title' => 'Soutenance', 'status' => 'pending', 'date' => 'Sept 2025']
                        ];
                        
                        foreach ($etapes as $index => $etape):
                        ?>
                            <div class="timeline-item <?= $etape['status'] ?>">
                                <div class="timeline-marker">
                                    <?php if ($etape['status'] === 'completed'): ?>
                                        <i class="fas fa-check"></i>
                                    <?php elseif ($etape['status'] === 'current'): ?>
                                        <i class="fas fa-clock"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content">
                                    <h6><?= escape($etape['title']) ?></h6>
                                    <small class="text-muted"><?= escape($etape['date']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activités récentes -->
        <div class="col-lg-4">
            <div class="card activity-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Activités Récentes
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($notifications)): ?>
                        <div class="activity-list">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-<?= getNotificationIcon($notification['type_notification']) ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title"><?= escape($notification['titre_notification']) ?></div>
                                        <div class="activity-time text-muted">
                                            <?= formatDateFR($notification['date_creation'], 'd/m à H:i') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Aucune activité récente</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ligne avec statut rapport et feedbacks -->
    <div class="row mb-4">
        <!-- Statut du rapport -->
        <div class="col-lg-6">
            <div class="card rapport-status-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Mon Rapport de Stage/Mémoire
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($rapport): ?>
                        <div class="rapport-info">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="rapport-title"><?= escape($rapport['titre']) ?></h6>
                                    <p class="text-muted mb-2">
                                        Type: <?= escape($rapport['type_rapport']) ?>
                                    </p>
                                    <?php if ($rapport['nom_encadreur']): ?>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-user-tie me-1"></i>
                                            Encadreur: <?= escape($rapport['nom_encadreur']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-clock me-1"></i>
                                        Dernière modification: <?= formatDateFR($rapport['date_modification']) ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge badge-lg" style="background-color: <?= $rapport['statut_couleur'] ?>">
                                        <?= escape($rapport['statut_rapport']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="rapport-actions mt-3">
                                <a href="rapport/redaction.php" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Continuer la rédaction
                                </a>
                                <?php if ($rapport['fichier_rapport']): ?>
                                    <a href="<?= $rapport['fichier_rapport'] ?>" target="_blank" class="btn btn-outline-secondary">
                                        <i class="fas fa-download me-2"></i>Télécharger
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                            <h6>Aucun rapport créé</h6>
                            <p class="text-muted">Commencez par créer votre rapport de stage ou mémoire</p>
                            <a href="rapport/redaction.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer mon rapport
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Feedbacks récents -->
        <div class="col-lg-6">
            <div class="card feedbacks-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments me-2"></i>
                        Feedbacks Récents
                    </h5>
                    <a href="feedbacks/" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($feedbacks)): ?>
                        <div class="feedbacks-list">
                            <?php foreach ($feedbacks as $feedback): ?>
                                <div class="feedback-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="feedback-evaluator">
                                                <?= escape($feedback['nom_evaluateur'] ?: 'Évaluateur') ?>
                                            </div>
                                            <div class="feedback-content">
                                                <?= escape(substr($feedback['content'], 0, 100)) ?>...
                                            </div>
                                            <div class="feedback-date text-muted">
                                                <?= formatDateFR($feedback['date_feedback']) ?>
                                            </div>
                                        </div>
                                        <?php if ($feedback['overall_rating']): ?>
                                            <div class="feedback-rating">
                                                <span class="badge bg-primary"><?= $feedback['overall_rating'] ?>/20</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-comment-slash fa-2x mb-2"></i>
                            <p>Aucun feedback reçu</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ligne avec calendrier et réclamations -->
    <div class="row mb-4">
        <!-- Prochains événements -->
        <div class="col-lg-6">
            <div class="card calendar-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Prochains Événements
                    </h5>
                    <a href="calendrier.php" class="btn btn-sm btn-outline-primary">Voir calendrier</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($evenements)): ?>
                        <div class="events-list">
                            <?php foreach ($evenements as $event): ?>
                                <div class="event-item">
                                    <div class="event-date">
                                        <div class="event-day"><?= date('d', strtotime($event['start_date'])) ?></div>
                                        <div class="event-month"><?= formatDateFR($event['date_formatted'], 'M') ?></div>
                                    </div>
                                    <div class="event-info">
                                        <div class="event-title"><?= escape($event['title']) ?></div>
                                        <div class="event-time text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('H:i', strtotime($event['start_date'])) ?>
                                        </div>
                                        <?php if ($event['location']): ?>
                                            <div class="event-location text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?= escape($event['location']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p>Aucun événement à venir</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Réclamations en cours -->
        <div class="col-lg-6">
            <div class="card reclamations-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Réclamations en Cours
                    </h5>
                    <a href="reclamations/" class="btn btn-sm btn-outline-primary">Gérer</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($reclamations)): ?>
                        <div class="reclamations-list">
                            <?php foreach ($reclamations as $reclamation): ?>
                                <div class="reclamation-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="reclamation-subject">
                                                <?= escape($reclamation['sujet']) ?>
                                            </div>
                                            <div class="reclamation-date text-muted">
                                                <?= formatDateFR($reclamation['date_creation']) ?>
                                            </div>
                                        </div>
                                        <span class="badge" style="background-color: <?= $reclamation['statut_couleur'] ?>">
                                            <?= escape($reclamation['statut_libelle']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p>Aucune réclamation en cours</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statut d'éligibilité -->
    <div class="row">
        <div class="col-12">
            <div class="card eligibilite-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Statut d'Éligibilité à la Soutenance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="eligibilite-status">
                                <span class="badge badge-lg fs-6" style="background-color: <?= $etudiant['statut_couleur'] ?>">
                                    <?= escape($etudiant['statut_eligibilite_libelle']) ?>
                                </span>
                            </div>
                            
                            <div class="eligibilite-details mt-3">
                                <div class="detail-item">
                                    <i class="fas fa-check-circle text-<?= $stats['moyenne_generale'] >= 10 ? 'success' : 'danger' ?> me-2"></i>
                                    Moyenne générale: <?= number_format($stats['moyenne_generale'], 2) ?>/20
                                    <?= $stats['moyenne_generale'] >= 10 ? '(Validé)' : '(Non validé)' ?>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-check-circle text-<?= $stats['credits_valides'] >= $stats['credits_requis'] ? 'success' : 'danger' ?> me-2"></i>
                                    Crédits: <?= $stats['credits_valides'] ?>/<?= $stats['credits_requis'] ?>
                                    <?= $stats['credits_valides'] >= $stats['credits_requis'] ? '(Validé)' : '(En cours)' ?>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-check-circle text-<?= $rapport && $rapport['statut_id'] >= RAPPORT_DEPOSE ? 'success' : 'warning' ?> me-2"></i>
                                    Rapport: <?= $rapport ? $rapport['statut_rapport'] : 'Non créé' ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="eligibilite-progress">
                                <h6>Progression vers l'éligibilité</h6>
                                <?php
                                $progressItems = [
                                    ['label' => 'Moyenne ≥ 10/20', 'completed' => $stats['moyenne_generale'] >= 10],
                                    ['label' => 'Tous les crédits validés', 'completed' => $stats['credits_valides'] >= $stats['credits_requis']],
                                    ['label' => 'Rapport soumis', 'completed' => $rapport && $rapport['statut_id'] >= RAPPORT_DEPOSE],
                                    ['label' => 'Rapport validé', 'completed' => $rapport && $rapport['statut_id'] >= RAPPORT_VALIDE]
                                ];
                                
                                $completedCount = array_reduce($progressItems, function($count, $item) {
                                    return $count + ($item['completed'] ? 1 : 0);
                                }, 0);
                                $progressPercent = ($completedCount / count($progressItems)) * 100;
                                ?>
                                
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-success" 
                                         style="width: <?= $progressPercent ?>%"
                                         aria-valuenow="<?= $progressPercent ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= round($progressPercent) ?>%
                                    </div>
                                </div>
                                
                                <div class="progress-details">
                                    <?php foreach ($progressItems as $item): ?>
                                        <div class="progress-item">
                                            <i class="fas fa-<?= $item['completed'] ? 'check-circle text-success' : 'circle text-muted' ?> me-2"></i>
                                            <?= escape($item['label']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Fonction utilitaire pour obtenir l'icône des notifications
 */
function getNotificationIcon($type) {
    $icons = [
        'feedback' => 'comment',
        'reminder' => 'bell',
        'system' => 'cog',
        'complaint' => 'exclamation-triangle',
        'calendar' => 'calendar',
        'rapport' => 'file-alt',
        'soutenance' => 'graduation-cap'
    ];
    
    return $icons[$type] ?? 'info-circle';
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des cartes statistiques
    animateStatCards();
    
    // Actualisation automatique des données
    setInterval(refreshDashboardData, 5 * 60 * 1000); // Toutes les 5 minutes
    
    // Initialiser les tooltips
    initializeTooltips();
    
    // Gestion des notifications en temps réel
    setupNotificationUpdates();
});

function animateStatCards() {
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

function refreshDashboardData() {
    fetch('api/dashboard-data.php', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': window.APP_CONFIG.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatistics(data.stats);
            updateNotifications(data.notifications);
            updateEvents(data.events);
        }
    })
    .catch(error => {
        console.error('Erreur lors de la mise à jour:', error);
    });
}

function updateStatistics(stats) {
    // Mettre à jour la progression du rapport
    const progressBar = document.querySelector('.rapport-progress .progress-bar');
    const progressValue = document.querySelector('.rapport-progress .stat-value');
    
    if (progressBar && progressValue) {
        progressBar.style.width = stats.progression_rapport + '%';
        progressValue.textContent = stats.progression_rapport.toFixed(1) + '%';
    }
    
    // Mettre à jour le nombre de mots
    const motsValue = document.querySelector('.mots-rediges .stat-value');
    if (motsValue) {
        motsValue.textContent = new Intl.NumberFormat('fr-FR').format(stats.mots_rediges);
    }
    
    // Mettre à jour la moyenne
    const moyenneValue = document.querySelector('.moyenne-generale .stat-value');
    if (moyenneValue) {
        moyenneValue.textContent = stats.moyenne_generale.toFixed(2) + '/20';
    }
    
    // Mettre à jour les jours restants
    const joursValue = document.querySelector('.jours-restants .stat-value');
    const joursIcon = document.querySelector('.jours-restants .stat-icon');
    
    if (joursValue && joursIcon) {
        joursValue.textContent = stats.jours_restants;
        
        // Changer la couleur selon l'urgence
        joursIcon.className = 'stat-icon ' + (stats.jours_restants <= 7 ? 'bg-danger' : 'bg-info');
    }
}

function updateNotifications(notifications) {
    const activityList = document.querySelector('.activity-list');
    if (!activityList) return;
    
    activityList.innerHTML = '';
    
    if (notifications.length === 0) {
        activityList.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>Aucune activité récente</p>
            </div>
        `;
        return;
    }
    
    notifications.forEach(notification => {
        const item = document.createElement('div');
        item.className = 'activity-item';
        item.innerHTML = `
            <div class="activity-icon">
                <i class="fas fa-${getNotificationIcon(notification.type_notification)}"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">${notification.titre_notification}</div>
                <div class="activity-time text-muted">
                    ${formatDate(notification.date_creation)}
                </div>
            </div>
        `;
        activityList.appendChild(item);
    });
}

function updateEvents(events) {
    const eventsList = document.querySelector('.events-list');
    if (!eventsList) return;
    
    eventsList.innerHTML = '';
    
    if (events.length === 0) {
        eventsList.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-calendar-times fa-2x mb-2"></i>
                <p>Aucun événement à venir</p>
            </div>
        `;
        return;
    }
    
    events.forEach(event => {
        const item = document.createElement('div');
        item.className = 'event-item';
        
        const eventDate = new Date(event.start_date);
        const day = eventDate.getDate().toString().padStart(2, '0');
        const month = eventDate.toLocaleDateString('fr-FR', { month: 'short' });
        const time = eventDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        
        item.innerHTML = `
            <div class="event-date">
                <div class="event-day">${day}</div>
                <div class="event-month">${month}</div>
            </div>
            <div class="event-info">
                <div class="event-title">${event.title}</div>
                <div class="event-time text-muted">
                    <i class="fas fa-clock me-1"></i>
                    ${time}
                </div>
                ${event.location ? `
                    <div class="event-location text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        ${event.location}
                    </div>
                ` : ''}
            </div>
        `;
        eventsList.appendChild(item);
    });
}

function initializeTooltips() {
    // Initialiser les tooltips Bootstrap pour les éléments avec des informations supplémentaires
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function setupNotificationUpdates() {
    // Vérifier les nouvelles notifications toutes les 30 secondes
    setInterval(() => {
        fetch('api/notifications-count.php')
            .then(response => response.json())
            .then(data => {
                if (data.hasNew) {
                    // Afficher un indicateur de nouvelles notifications
                    showNewNotificationIndicator();
                }
            })
            .catch(console.error);
    }, 30000);
}

function showNewNotificationIndicator() {
    // Afficher une notification discrète
    showToast('Vous avez de nouvelles notifications', 'info', 3000);
    
    // Ajouter un effet visuel sur l'icône de notification dans la navbar
    const notificationBell = document.querySelector('#notificationsDropdown i');
    if (notificationBell) {
        notificationBell.classList.add('pulse');
        setTimeout(() => {
            notificationBell.classList.remove('pulse');
        }, 2000);
    }
}

// Fonctions utilitaires
function getNotificationIcon(type) {
    const icons = {
        'feedback': 'comment',
        'reminder': 'bell',
        'system': 'cog',
        'complaint': 'exclamation-triangle',
        'calendar': 'calendar',
        'rapport': 'file-alt',
        'soutenance': 'graduation-cap'
    };
    
    return icons[type] || 'info-circle';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) {
        return 'Hier';
    } else if (diffDays < 7) {
        return `Il y a ${diffDays} jours`;
    } else {
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
}

// Gestion des actions rapides
function quickActions() {
    // Action rapide pour continuer la rédaction
    const continueBtn = document.querySelector('.btn[href*="redaction"]');
    if (continueBtn) {
        continueBtn.addEventListener('click', function(e) {
            // Sauvegarder l'état actuel avant de naviguer
            localStorage.setItem('lastDashboardView', Date.now());
        });
    }
    
    // Action rapide pour créer un événement
    window.createQuickEvent = function() {
        // Ouvrir une modal de création rapide d'événement
        const modal = new bootstrap.Modal(document.getElementById('quickEventModal'));
        modal.show();
    };
    
    // Action rapide pour voir les feedbacks
    window.viewFeedbacks = function() {
        window.location.href = 'feedbacks/';
    };
}

// Initialiser les actions rapides
quickActions();
</script>

<?php include '../../includes/footer.php'; ?>