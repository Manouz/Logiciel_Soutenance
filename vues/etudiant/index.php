<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Vérification des permissions
requireAuth(ROLE_ETUDIANT);

$db = Database::getInstance();
$user_id = SessionManager::getUserId();

try {
    // Récupération des informations de l'étudiant
    $etudiant = $db->fetch("
        SELECT 
            e.etudiant_id,
            e.numero_etudiant,
            e.numero_carte_etudiant,
            e.moyenne_generale,
            e.nombre_credits_valides,
            e.nombre_credits_requis,
            e.taux_progression,
            ip.nom,
            ip.prenoms,
            ip.telephone,
            u.email,
            ne.libelle_niveau,
            sp.libelle_specialite,
            s.libelle_statut as statut_eligibilite,
            s.couleur_affichage
        FROM etudiants e
        INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        INNER JOIN niveaux_etude ne ON e.niveau_id = ne.niveau_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        INNER JOIN statuts s ON e.statut_eligibilite = s.statut_id
        WHERE u.utilisateur_id = ?
    ", [$user_id]);
    
    if (!$etudiant) {
        throw new Exception("Étudiant non trouvé");
    }
    
    // Récupération du rapport de l'étudiant
    $rapport = $db->fetch("
        SELECT 
            r.rapport_id,
            r.titre,
            r.type_rapport,
            r.date_depot,
            r.date_limite_depot,
            r.nombre_pages,
            r.nombre_mots,
            r.version_document,
            s.libelle_statut as statut_rapport,
            s.couleur_affichage as couleur_statut,
            CONCAT(ip_enc.nom, ' ', ip_enc.prenoms) as encadreur_nom
        FROM rapports r
        LEFT JOIN enseignants ens ON r.encadreur_id = ens.enseignant_id
        LEFT JOIN utilisateurs u_enc ON ens.utilisateur_id = u_enc.utilisateur_id
        LEFT JOIN informations_personnelles ip_enc ON u_enc.utilisateur_id = ip_enc.utilisateur_id
        INNER JOIN statuts s ON r.statut_id = s.statut_id
        WHERE r.etudiant_id = ?
        ORDER BY r.date_creation DESC
        LIMIT 1
    ", [$etudiant['etudiant_id']]);
    
    // Récupération des notes de l'étudiant
    $notes = $db->fetchAll("
        SELECT 
            ec.libelle_ecue,
            ue.libelle_ue,
            ev.note,
            ev.note_sur,
            ev.coefficient,
            ev.type_evaluation,
            ev.session_evaluation,
            ev.date_evaluation,
            ev.commentaire,
            CONCAT(ip_ens.nom, ' ', ip_ens.prenoms) as enseignant_nom
        FROM evaluations ev
        INNER JOIN elements_constitutifs ec ON ev.ecue_id = ec.ecue_id
        INNER JOIN unites_enseignement ue ON ec.ue_id = ue.ue_id
        INNER JOIN enseignants ens ON ev.enseignant_id = ens.enseignant_id
        INNER JOIN utilisateurs u_ens ON ens.utilisateur_id = u_ens.utilisateur_id
        INNER JOIN informations_personnelles ip_ens ON u_ens.utilisateur_id = ip_ens.utilisateur_id
        WHERE ev.etudiant_id = ? AND ev.est_validee = 1
        ORDER BY ev.date_evaluation DESC
        LIMIT 10
    ", [$etudiant['etudiant_id']]);
    
    // Récupération des réclamations
    $reclamations = $db->fetchAll("
        SELECT 
            r.reclamation_id,
            r.numero_reclamation,
            r.sujet,
            r.date_creation,
            s.libelle_statut,
            s.couleur_affichage,
            r.reponse
        FROM reclamations r
        INNER JOIN statuts s ON r.statut_id = s.statut_id
        WHERE r.etudiant_id = ?
        ORDER BY r.date_creation DESC
        LIMIT 5
    ", [$etudiant['etudiant_id']]);
    
    // Calcul des statistiques
    $stats = [
        'progression' => $etudiant['taux_progression'] ?? 0,
        'credits_valides' => $etudiant['nombre_credits_valides'] ?? 0,
        'credits_requis' => $etudiant['nombre_credits_requis'] ?? 60,
        'moyenne_generale' => $etudiant['moyenne_generale'] ?? 0,
        'notes_count' => count($notes),
        'reclamations_ouvertes' => count(array_filter($reclamations, function($r) {
            return in_array($r['libelle_statut'], ['Ouverte', 'En cours']);
        }))
    ];
    
    // Prochaines échéances
    $echeances = [];
    if ($rapport && $rapport['date_limite_depot']) {
        $jours_restants = floor((strtotime($rapport['date_limite_depot']) - time()) / (24 * 3600));
        if ($jours_restants >= 0) {
            $echeances[] = [
                'titre' => 'Dépôt du rapport',
                'date' => $rapport['date_limite_depot'],
                'jours_restants' => $jours_restants,
                'type' => 'rapport'
            ];
        }
    }
    
} catch (Exception $e) {
    error_log("Erreur dashboard étudiant: " . $e->getMessage());
    $etudiant = null;
    $rapport = null;
    $notes = [];
    $reclamations = [];
    $stats = array_fill_keys(['progression', 'credits_valides', 'credits_requis', 'moyenne_generale', 'notes_count', 'reclamations_ouvertes'], 0);
    $echeances = [];
}

$page_title = "Dashboard Étudiant";
include '../../includes/header.php';
?>

<div class="etudiant-dashboard">
    <!-- Header personnalisé -->
    <div class="dashboard-header etudiant-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="student-welcome">
                        <div class="student-avatar-large">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="student-info-header">
                            <h1 class="dashboard-title">
                                Bonjour, <?= htmlspecialchars($etudiant['prenoms'] ?? 'Étudiant') ?> !
                            </h1>
                            <p class="dashboard-subtitle">
                                <?= htmlspecialchars($etudiant['numero_etudiant'] ?? '') ?> - 
                                <?= htmlspecialchars($etudiant['libelle_specialite'] ?? 'Spécialité non définie') ?>
                            </p>
                            <div class="student-status">
                                <span class="status-badge" style="background-color: <?= $etudiant['couleur_affichage'] ?? '#6c757d' ?>">
                                    <?= htmlspecialchars($etudiant['statut_eligibilite'] ?? 'Non défini') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-actions">
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#profilModal">
                            <i class="fas fa-user-cog"></i>
                            Mon Profil
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i>
                            Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Progression et statistiques principales -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="progression-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Ma Progression Académique
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="progress-circle-container">
                                    <div class="progress-circle" data-percentage="<?= $stats['progression'] ?>">
                                        <div class="progress-circle-inner">
                                            <span class="progress-percentage"><?= number_format($stats['progression'], 1) ?>%</span>
                                            <span class="progress-label">Progression</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="progress-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Crédits validés</span>
                                        <span class="detail-value">
                                            <?= $stats['credits_valides'] ?> / <?= $stats['credits_requis'] ?>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Moyenne générale</span>
                                        <span class="detail-value moyenne-value">
                                            <?= $stats['moyenne_generale'] ? number_format($stats['moyenne_generale'], 2) : 'N/A' ?>/20
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Évaluations</span>
                                        <span class="detail-value"><?= $stats['notes_count'] ?> notes</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Statut</span>
                                        <span class="detail-value" style="color: <?= $etudiant['couleur_affichage'] ?? '#6c757d' ?>">
                                            <?= htmlspecialchars($etudiant['statut_eligibilite'] ?? 'Non défini') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="echeances-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-calendar-exclamation"></i>
                            Prochaines Échéances
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($echeances)): ?>
                            <?php foreach ($echeances as $echeance): ?>
                            <div class="echeance-item">
                                <div class="echeance-icon">
                                    <i class="fas fa-<?= $echeance['type'] == 'rapport' ? 'file-alt' : 'calendar' ?>"></i>
                                </div>
                                <div class="echeance-content">
                                    <div class="echeance-title"><?= htmlspecialchars($echeance['titre']) ?></div>
                                    <div class="echeance-date"><?= formatDate($echeance['date']) ?></div>
                                    <div class="echeance-countdown">
                                        <?php if ($echeance['jours_restants'] <= 7): ?>
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?= $echeance['jours_restants'] ?> jour(s) restant(s)
                                            </span>
                                        <?php else: ?>
                                            <span class="text-success">
                                                <?= $echeance['jours_restants'] ?> jour(s) restant(s)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-echeances">
                                <i class="fas fa-calendar-check text-success"></i>
                                <p>Aucune échéance proche</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="quick-actions-card etudiant-actions">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-rocket"></i>
                            Actions Rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="rapport/redaction.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-primary">
                                        <i class="fas fa-pen"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Rédiger Rapport</div>
                                        <div class="quick-action-desc">Éditeur en ligne</div>
                                        <?php if ($rapport): ?>
                                        <div class="quick-action-status">
                                            <span style="color: <?= $rapport['couleur_statut'] ?>">
                                                <?= htmlspecialchars($rapport['statut_rapport']) ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="rapport/soumission.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-success">
                                        <i class="fas fa-upload"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Soumettre Rapport</div>
                                        <div class="quick-action-desc">Dépôt final</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="reclamations/soumettre.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-warning">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Nouvelle Réclamation</div>
                                        <div class="quick-action-desc">Support étudiant</div>
                                        <?php if ($stats['reclamations_ouvertes'] > 0): ?>
                                        <div class="quick-action-badge">
                                            <?= $stats['reclamations_ouvertes'] ?> en cours
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="calendrier.php" class="quick-action-btn">
                                    <div class="quick-action-icon bg-info">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="quick-action-text">
                                        <div class="quick-action-title">Mon Calendrier</div>
                                        <div class="quick-action-desc">Planning personnel</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal - 3 sections -->
        <div class="row">
            <!-- Mon Rapport -->
            <div class="col-lg-4 mb-4">
                <div class="content-card rapport-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="fas fa-file-alt"></i>
                            Mon Rapport de Stage
                        </h5>
                        <?php if ($rapport): ?>
                        <span class="badge" style="background-color: <?= $rapport['couleur_statut'] ?>">
                            <?= htmlspecialchars($rapport['statut_rapport']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($rapport): ?>
                            <div class="rapport-info">
                                <div class="rapport-title">
                                    <?= htmlspecialchars($rapport['titre']) ?>
                                </div>
                                <div class="rapport-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Type:</span>
                                        <span class="detail-value"><?= htmlspecialchars($rapport['type_rapport']) ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Encadreur:</span>
                                        <span class="detail-value"><?= htmlspecialchars($rapport['encadreur_nom']) ?></span>
                                    </div>
                                    <?php if ($rapport['nombre_pages']): ?>
                                    <div class="detail-row">
                                        <span class="detail-label">Pages:</span>
                                        <span class="detail-value"><?= $rapport['nombre_pages'] ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($rapport['nombre_mots']): ?>
                                    <div class="detail-row">
                                        <span class="detail-label">Mots:</span>
                                        <span class="detail-value"><?= number_format($rapport['nombre_mots']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="detail-row">
                                        <span class="detail-label">Version:</span>
                                        <span class="detail-value"><?= htmlspecialchars($rapport['version_document']) ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($rapport['date_depot']): ?>
                                <div class="rapport-dates">
                                    <div class="date-item">
                                        <i class="fas fa-upload text-success"></i>
                                        Déposé le <?= formatDate($rapport['date_depot']) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($rapport['date_limite_depot']): ?>
                                <div class="date-item">
                                    <i class="fas fa-clock <?= strtotime($rapport['date_limite_depot']) < time() ? 'text-danger' : 'text-warning' ?>"></i>
                                    Limite: <?= formatDate($rapport['date_limite_depot']) ?>
                                    <?php if (strtotime($rapport['date_limite_depot']) < time()): ?>
                                        <span class="text-danger">(Dépassée)</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="rapport-actions mt-3">
                                <a href="rapport/redaction.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="rapport/historique.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-history"></i> Historique
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="no-rapport">
                                <div class="no-rapport-icon">
                                    <i class="fas fa-file-plus"></i>
                                </div>
                                <h6>Aucun rapport créé</h6>
                                <p class="text-muted">Commencez la rédaction de votre rapport de stage</p>
                                <a href="rapport/redaction.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Créer mon rapport
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Mes Notes -->
            <div class="col-lg-4 mb-4">
                <div class="content-card notes-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="fas fa-star"></i>
                            Mes Dernières Notes
                        </h5>
                        <span class="badge bg-info"><?= count($notes) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="notes-summary">
                            <div class="moyenne-display">
                                <span class="moyenne-label">Moyenne générale</span>
                                <span class="moyenne-number">
                                    <?= $stats['moyenne_generale'] ? number_format($stats['moyenne_generale'], 2) : 'N/A' ?>
                                    <small>/20</small>
                                </span>
                            </div>
                        </div>
                        
                        <div class="notes-list">
                            <?php foreach (array_slice($notes, 0, 5) as $note): ?>
                            <div class="note-item">
                                <div class="note-header">
                                    <span class="note-matiere">
                                        <?= htmlspecialchars($note['libelle_ecue']) ?>
                                    </span>
                                    <span class="note-value">
                                        <?= $note['note'] ?>/<?= $note['note_sur'] ?>
                                    </span>
                                </div>
                                <div class="note-details">
                                    <span class="note-ue"><?= htmlspecialchars($note['libelle_ue']) ?></span>
                                    <span class="note-type"><?= htmlspecialchars($note['type_evaluation']) ?></span>
                                </div>
                                <div class="note-date">
                                    <i class="fas fa-calendar"></i>
                                    <?= formatDate($note['date_evaluation']) ?>
                                </div>
                                <?php if ($note['commentaire']): ?>
                                <div class="note-comment">
                                    <i class="fas fa-comment"></i>
                                    <?= htmlspecialchars(substr($note['commentaire'], 0, 100)) ?>
                                    <?= strlen($note['commentaire']) > 100 ? '...' : '' ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (empty($notes)): ?>
                        <div class="no-notes">
                            <i class="fas fa-clipboard-list text-muted"></i>
                            <p class="text-muted">Aucune note disponible</p>
                        </div>
                        <?php else: ?>
                        <div class="text-center mt-3">
                            <a href="notes/consultation.php" class="btn btn-sm btn-outline-info">
                                Voir toutes mes notes
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Mes Réclamations -->
            <div class="col-lg-4 mb-4">
                <div class="content-card reclamations-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="fas fa-headset"></i>
                            Mes Réclamations
                        </h5>
                        <?php if ($stats['reclamations_ouvertes'] > 0): ?>
                        <span class="badge bg-warning"><?= $stats['reclamations_ouvertes'] ?> en cours</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="reclamations-list">
                            <?php foreach ($reclamations as $reclamation): ?>
                            <div class="reclamation-item">
                                <div class="reclamation-header">
                                    <span class="reclamation-numero">
                                        #<?= htmlspecialchars($reclamation['numero_reclamation']) ?>
                                    </span>
                                    <span class="reclamation-status" style="background-color: <?= $reclamation['couleur_affichage'] ?>">
                                        <?= htmlspecialchars($reclamation['libelle_statut']) ?>
                                    </span>
                                </div>
                                <div class="reclamation-content">
                                    <div class="reclamation-sujet">
                                        <?= htmlspecialchars($reclamation['sujet']) ?>
                                    </div>
                                    <div class="reclamation-date">
                                        <i class="fas fa-clock"></i>
                                        <?= timeAgo($reclamation['date_creation']) ?>
                                    </div>
                                </div>
                                <?php if ($reclamation['reponse']): ?>
                                <div class="reclamation-reponse">
                                    <i class="fas fa-reply text-success"></i>
                                    <small class="text-success">Réponse reçue</small>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (empty($reclamations)): ?>
                        <div class="no-reclamations">
                            <i class="fas fa-smile text-success"></i>
                            <p class="text-muted">Aucune réclamation</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="reclamations-actions mt-3">
                            <a href="reclamations/soumettre.php" class="btn btn-sm btn-warning">
                                <i class="fas fa-plus"></i> Nouvelle réclamation
                            </a>
                            <?php if (!empty($reclamations)): ?>
                            <a href="reclamations/suivi.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-list"></i> Voir tout
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section informative -->
        <div class="row">
            <div class="col-12">
                <div class="info-section">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Informations Importantes</h6>
                                    <p>Consultez régulièrement vos emails et ce dashboard pour les mises à jour importantes concernant votre parcours.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Besoin d'Aide ?</h6>
                                    <p>En cas de problème, n'hésitez pas à soumettre une réclamation ou à contacter votre encadreur.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Délais Importants</h6>
                                    <p>Respectez les échéances de dépôt de rapport et de soumission des documents requis.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Profil -->
<div class="modal fade" id="profilModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mon Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h6><?= htmlspecialchars($etudiant['prenoms'] . ' ' . $etudiant['nom']) ?></h6>
                        <p class="text-muted"><?= htmlspecialchars($etudiant['numero_etudiant']) ?></p>
                    </div>
                    <div class="col-md-8">
                        <div class="profile-info">
                            <div class="info-group">
                                <label>Email:</label>
                                <span><?= htmlspecialchars($etudiant['email']) ?></span>
                            </div>
                            <div class="info-group">
                                <label>Téléphone:</label>
                                <span><?= htmlspecialchars($etudiant['telephone'] ?? 'Non renseigné') ?></span>
                            </div>
                            <div class="info-group">
                                <label>Niveau:</label>
                                <span><?= htmlspecialchars($etudiant['libelle_niveau']) ?></span>
                            </div>
                            <div class="info-group">
                                <label>Spécialité:</label>
                                <span><?= htmlspecialchars($etudiant['libelle_specialite'] ?? 'Non définie') ?></span>
                            </div>
                            <div class="info-group">
                                <label>Numéro de carte:</label>
                                <span><?= htmlspecialchars($etudiant['numero_carte_etudiant'] ?? 'Non attribué') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary">Modifier mes informations</button>
            </div>
        </div>
    </div>
</div>

<?php
include '../../includes/footer.php';

// Fonctions utilitaires
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
document.addEventListener('DOMContentLoaded', function() {
    // Animation du cercle de progression
    const progressCircle = document.querySelector('.progress-circle');
    const percentage = progressCircle.getAttribute('data-percentage');
    
    setTimeout(() => {
        progressCircle.style.setProperty('--progress', percentage + '%');
        progressCircle.classList.add('animated');
    }, 500);
    
    // Auto-refresh des données importantes
    setInterval(function() {
        fetch('api/dashboard_refresh.php')
            .then(response => response.json())
            .then(data => {
                if (data.new_notification) {
                    showNotification('Nouvelles informations disponibles', 'info');
                }
            })
            .catch(error => console.error('Erreur refresh:', error));
    }, 60000); // Refresh toutes les minutes
});

// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
    // Implémentation d'un système de notification toast
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} toast-notification`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'info' ? 'info-circle' : 'check-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Animation des cartes au scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

document.querySelectorAll('.content-card, .quick-action-btn').forEach(el => {
    observer.observe(el);
});
</script>