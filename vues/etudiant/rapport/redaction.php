<?php
/**
 * Éditeur de Rapport Étudiant
 * Système de Validation Académique - Université Félix Houphouët-Boigny
 */

require_once '../../../config/database.php';
require_once '../../../config/session.php';
require_once '../../../includes/functions.php';
require_once '../../../classes/User.php';

/**
 * Fonctions utilitaires manquantes
 */

// Fonction pour vérifier l'authentification
if (!function_exists('requireAuth')) {
    function requireAuth() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !getCurrentUser()) {
            header('Location: ../../../vues/login.php?error=auth_required');
            exit();
        }
    }
}

// Fonction pour vérifier les permissions
if (!function_exists('requirePermission')) {
    function requirePermission($level) {
        $currentUser = getCurrentUser();
        if (!$currentUser || ($currentUser['niveau_acces'] ?? 0) < $level) {
            header('Location: ../../../vues/unauthorized.php');
            exit();
        }
    }
}

// Fonction pour vérifier un rôle spécifique
if (!function_exists('hasRole')) {
    function hasRole($role) {
        $currentUser = getCurrentUser();
        return $currentUser && ($currentUser['nom_role'] ?? $currentUser['role'] ?? '') === $role;
    }
}

// Fonction de redirection
if (!function_exists('redirect')) {
    function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
}

// Définir les constantes manquantes
if (!defined('ACCESS_LEVEL_STUDENT')) define('ACCESS_LEVEL_STUDENT', 1);
if (!defined('RAPPORT_BROUILLON')) define('RAPPORT_BROUILLON', 1);

// Vérifier l'authentification et les permissions
requireAuth();
requirePermission(ACCESS_LEVEL_STUDENT);

if (!hasRole('Étudiant')) {
    redirect('../../../vues/unauthorized.php');
}

$currentUser = getCurrentUser();
$userId = $currentUser['utilisateur_id'] ?? $currentUser['id'];

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Récupérer les informations de l'étudiant
    $sql = "SELECT 
                e.*,
                ip.nom,
                ip.prenoms,
                sp.libelle_specialite
            FROM etudiants e
            INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
            INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
            LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
            WHERE u.utilisateur_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $etudiant = $stmt->fetch();
    
    if (!$etudiant) {
        throw new Exception("Informations étudiant non trouvées");
    }
    
    // Récupérer ou créer le rapport de l'étudiant
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
    
    // Si aucun rapport n'existe, on initialise les données pour en créer un
    if (!$rapport) {
        $rapport = [
            'rapport_id' => null,
            'titre' => '',
            'type_rapport' => 'Mémoire',
            'resume' => '',
            'mots_cles' => '',
            'cadre_reference_texte' => '',
            'introduction_texte' => '',
            'problematique_texte' => '',
            'objectif_general_texte' => '',
            'objectifs_specifiques_texte' => '',
            'methodologie_texte' => '',
            'entreprise_stage' => '',
            'maitre_stage_nom' => '',
            'maitre_stage_email' => '',
            'maitre_stage_poste' => '',
            'lieu_stage' => '',
            'date_debut_stage' => '',
            'date_fin_stage' => '',
            'nombre_mots' => 0,
            'version_document' => '1.0',
            'statut_id' => RAPPORT_BROUILLON,
            'statut_rapport' => 'Brouillon',
            'statut_couleur' => '#6c757d',
            'encadreur_id' => null,
            'date_creation' => date('Y-m-d H:i:s'),
            'date_modification' => date('Y-m-d H:i:s')
        ];
    }
    
    // Récupérer les enseignants disponibles pour l'encadrement
    $sql = "SELECT 
                ens.enseignant_id,
                ens.numero_enseignant,
                CONCAT(ip.prenoms, ' ', ip.nom) as nom_complet,
                ga.libelle_grade,
                f.libelle_fonction
            FROM enseignants ens
            INNER JOIN utilisateurs u ON ens.utilisateur_id = u.utilisateur_id
            INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
            LEFT JOIN grades_academiques ga ON ens.grade_id = ga.grade_id
            LEFT JOIN fonctions f ON ens.fonction_id = f.fonction_id
            WHERE u.est_actif = 1
            ORDER BY ip.nom, ip.prenoms";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $enseignants = $stmt->fetchAll();
    
    // Récupérer les versions du rapport
    $versions = [];
    if ($rapport['rapport_id']) {
        $sql = "SELECT 
                    rv.*,
                    CONCAT(ip.prenoms, ' ', ip.nom) as cree_par_nom
                FROM versions_rapports rv
                LEFT JOIN utilisateurs u ON rv.cree_par_id = u.utilisateur_id
                LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                WHERE rv.rapport_id = ?
                ORDER BY rv.created_at DESC
                LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rapport['rapport_id']]);
        $versions = $stmt->fetchAll();
    }
    
    // Statistiques du rapport
    $stats = [
        'mots_total' => $rapport['nombre_mots'] ?? 0,
        'objectif_mots' => 15000,
        'progression' => min(100, (($rapport['nombre_mots'] ?? 0) / 15000) * 100),
        'derniere_modification' => $rapport['date_modification'] ?? $rapport['date_creation'] ?? date('Y-m-d H:i:s'),
        'version_actuelle' => $rapport['version_document'] ?? '1.0'
    ];
    
} catch (Exception $e) {
    error_log("Erreur éditeur rapport: " . $e->getMessage());
    $error_message = "Erreur lors du chargement du rapport: " . $e->getMessage();
}

// Configuration de la page
$pageTitle = "Rédaction de Rapport";
$pageDescription = "Éditeur de rapport avec sauvegarde automatique";
$additionalCSS = ['etudiant/rapport-editor.css', 'etudiant/etudiant-style.css'];
$additionalJS = ['etudiant/rapport-editor.js', 'common/tinymce/tinymce.min.js'];

$breadcrumb = [
    ['title' => 'Dashboard', 'url' => '../index.php'],
    ['title' => 'Mon Rapport', 'url' => '#'],
    ['title' => 'Rédaction', 'url' => '#']
];

$pageHeader = [
    'title' => 'Rédaction de Mon Rapport',
    'subtitle' => 'Éditeur avancé avec sauvegarde automatique',
    'actions' => '
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" id="previewBtn">
                <i class="fas fa-eye me-2"></i>Prévisualiser
            </button>
            <button class="btn btn-success" id="saveBtn">
                <i class="fas fa-save me-2"></i>Sauvegarder
            </button>
        </div>'
];
?>

<?php include '../../../includes/header.php'; ?>

<div class="container-fluid rapport-editor">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= escape($error_message) ?>
        </div>
    <?php endif; ?>
    
    <!-- Barre d'outils principale -->
    <div class="editor-toolbar">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="rapport-info">
                    <h5 class="mb-1"><?= escape($rapport['titre'] ?: 'Nouveau Rapport') ?></h5>
                    <div class="text-muted d-flex align-items-center gap-3">
                        <span class="badge" style="background-color: <?= escape($rapport['statut_couleur']) ?>">
                            <?= escape($rapport['statut_rapport']) ?>
                        </span>
                        <span>Version <?= escape($stats['version_actuelle']) ?></span>
                        <span><?= number_format($stats['mots_total']) ?> mots</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="progress-info text-end">
                    <div class="progress-label mb-1">
                        Progression: <?= number_format($stats['progression'], 1) ?>%
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" 
                             style="width: <?= $stats['progression'] ?>%"
                             aria-valuenow="<?= $stats['progression'] ?>" 
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted">
                        Objectif: <?= number_format($stats['objectif_mots']) ?> mots
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Panel de navigation des sections -->
        <div class="col-lg-3">
            <div class="sticky-top" style="top: 100px;">
                <div class="card sections-nav">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>Sections du Rapport
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <nav class="nav nav-pills flex-column sections-list">
                            <a class="nav-link active" href="#section-metadata" data-section="metadata">
                                <i class="fas fa-info-circle me-2"></i>Métadonnées
                            </a>
                            <a class="nav-link" href="#section-resume" data-section="resume">
                                <i class="fas fa-file-text me-2"></i>Résumé
                            </a>
                            <a class="nav-link" href="#section-cadre" data-section="cadre">
                                <i class="fas fa-book me-2"></i>Cadre de Référence
                            </a>
                            <a class="nav-link" href="#section-introduction" data-section="introduction">
                                <i class="fas fa-play me-2"></i>Introduction
                            </a>
                            <a class="nav-link" href="#section-problematique" data-section="problematique">
                                <i class="fas fa-question-circle me-2"></i>Problématique
                            </a>
                            <a class="nav-link" href="#section-objectifs" data-section="objectifs">
                                <i class="fas fa-bullseye me-2"></i>Objectifs
                            </a>
                            <a class="nav-link" href="#section-methodologie" data-section="methodologie">
                                <i class="fas fa-cogs me-2"></i>Méthodologie
                            </a>
                            <a class="nav-link" href="#section-stage" data-section="stage">
                                <i class="fas fa-building me-2"></i>Informations Stage
                            </a>
                        </nav>
                    </div>
                </div>
                
                <!-- Panel de versions -->
                <div class="card versions-panel mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i>Versions
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($versions)): ?>
                            <div class="versions-list">
                                <?php foreach ($versions as $version): ?>
                                    <div class="version-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="version-number">v<?= escape($version['numero_version'] ?? '1.0') ?></div>
                                                <small class="text-muted">
                                                    <?= formatDateFR($version['created_at'] ?? date('Y-m-d H:i:s'), 'd/m à H:i') ?>
                                                </small>
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="loadVersion('<?= escape($version['version_id'] ?? '') ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <?php if (!empty($version['changes_summary'])): ?>
                                            <small class="text-muted d-block mt-1">
                                                <?= escape($version['changes_summary']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">Aucune version sauvegardée</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Zone d'édition principale -->
        <div class="col-lg-9">
            <form id="rapportForm" class="rapport-form">
                <input type="hidden" id="rapportId" value="<?= escape($rapport['rapport_id'] ?? '') ?>">
                <input type="hidden" id="etudiantId" value="<?= escape($etudiant['etudiant_id'] ?? '') ?>">
                
                <!-- Section Métadonnées -->
                <div class="section-card active" id="section-metadata">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Métadonnées du Rapport</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="titre" class="form-label">Titre du rapport *</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="titre" 
                                               name="titre" 
                                               value="<?= escape($rapport['titre']) ?>"
                                               placeholder="Saisissez le titre de votre rapport"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="type_rapport" class="form-label">Type de rapport *</label>
                                        <select class="form-select" id="type_rapport" name="type_rapport" required>
                                            <option value="Mémoire" <?= $rapport['type_rapport'] === 'Mémoire' ? 'selected' : '' ?>>Mémoire</option>
                                            <option value="Stage" <?= $rapport['type_rapport'] === 'Stage' ? 'selected' : '' ?>>Rapport de Stage</option>
                                            <option value="Projet" <?= $rapport['type_rapport'] === 'Projet' ? 'selected' : '' ?>>Projet</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="encadreur_id" class="form-label">Encadreur *</label>
                                        <select class="form-select" id="encadreur_id" name="encadreur_id" required>
                                            <option value="">Sélectionnez un encadreur</option>
                                            <?php foreach ($enseignants as $enseignant): ?>
                                                <option value="<?= $enseignant['enseignant_id'] ?>"
                                                        <?= $rapport['encadreur_id'] == $enseignant['enseignant_id'] ? 'selected' : '' ?>>
                                                    <?= escape($enseignant['nom_complet']) ?> 
                                                    <?php if (!empty($enseignant['libelle_grade'])): ?>
                                                        (<?= escape($enseignant['libelle_grade']) ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mots_cles" class="form-label">Mots-clés</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="mots_cles" 
                                               name="mots_cles" 
                                               value="<?= escape($rapport['mots_cles']) ?>"
                                               placeholder="Séparez les mots-clés par des virgules">
                                        <small class="form-text text-muted">
                                            Ex: machine learning, intelligence artificielle, réseaux
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Résumé -->
                <div class="section-card" id="section-resume">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Résumé</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="resume" class="form-label">Résumé du rapport</label>
                                <textarea class="form-control editor-textarea" 
                                          id="resume" 
                                          name="resume" 
                                          rows="8"
                                          placeholder="Rédigez un résumé concis de votre rapport (300-500 mots)"><?= escape($rapport['resume']) ?></textarea>
                                <small class="form-text text-muted">
                                    Le résumé doit présenter brièvement le contexte, les objectifs, la méthode et les résultats.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Cadre de référence -->
                <div class="section-card" id="section-cadre">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Cadre de Référence Théorique</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <textarea class="form-control wysiwyg-editor" 
                                          id="cadre_reference_texte" 
                                          name="cadre_reference_texte"
                                          placeholder="Développez le cadre théorique de votre recherche..."><?= $rapport['cadre_reference_texte'] ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Introduction -->
                <div class="section-card" id="section-introduction">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Introduction</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <textarea class="form-control wysiwyg-editor" 
                                          id="introduction_texte" 
                                          name="introduction_texte"
                                          placeholder="Rédigez l'introduction de votre rapport..."><?= $rapport['introduction_texte'] ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Problématique -->
                <div class="section-card" id="section-problematique">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Problématique</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <textarea class="form-control wysiwyg-editor" 
                                          id="problematique_texte" 
                                          name="problematique_texte"
                                          placeholder="Exposez la problématique de votre recherche..."><?= $rapport['problematique_texte'] ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Objectifs -->
                <div class="section-card" id="section-objectifs">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Objectifs</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="objectif_general_texte" class="form-label">Objectif général</label>
                                        <textarea class="form-control wysiwyg-editor" 
                                                  id="objectif_general_texte" 
                                                  name="objectif_general_texte"
                                                  placeholder="Décrivez l'objectif principal de votre recherche..."><?= $rapport['objectif_general_texte'] ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="objectifs_specifiques_texte" class="form-label">Objectifs spécifiques</label>
                                        <textarea class="form-control wysiwyg-editor" 
                                                  id="objectifs_specifiques_texte" 
                                                  name="objectifs_specifiques_texte"
                                                  placeholder="Listez les objectifs spécifiques..."><?= $rapport['objectifs_specifiques_texte'] ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Méthodologie -->
                <div class="section-card" id="section-methodologie">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Méthodologie</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <textarea class="form-control wysiwyg-editor" 
                                          id="methodologie_texte" 
                                          name="methodologie_texte"
                                          placeholder="Décrivez la méthodologie utilisée..."><?= $rapport['methodologie_texte'] ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Informations Stage -->
                <div class="section-card" id="section-stage">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Informations de Stage</h5>
                            <small class="text-muted">Pour les rapports de stage uniquement</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="entreprise_stage" class="form-label">Entreprise/Organisation</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="entreprise_stage" 
                                               name="entreprise_stage" 
                                               value="<?= escape($rapport['entreprise_stage']) ?>"
                                               placeholder="Nom de l'entreprise">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lieu_stage" class="form-label">Lieu du stage</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="lieu_stage" 
                                               name="lieu_stage" 
                                               value="<?= escape($rapport['lieu_stage']) ?>"
                                               placeholder="Ville, Pays">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="maitre_stage_nom" class="form-label">Maître de stage</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="maitre_stage_nom" 
                                               name="maitre_stage_nom" 
                                               value="<?= escape($rapport['maitre_stage_nom']) ?>"
                                               placeholder="Nom du maître de stage">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="maitre_stage_email" class="form-label">Email du maître de stage</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="maitre_stage_email" 
                                               name="maitre_stage_email" 
                                               value="<?= escape($rapport['maitre_stage_email']) ?>"
                                               placeholder="email@entreprise.com">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="maitre_stage_poste" class="form-label">Poste</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="maitre_stage_poste" 
                                               name="maitre_stage_poste" 
                                               value="<?= escape($rapport['maitre_stage_poste']) ?>"
                                               placeholder="Poste du maître de stage">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_debut_stage" class="form-label">Date de début</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="date_debut_stage" 
                                               name="date_debut_stage" 
                                               value="<?= escape($rapport['date_debut_stage']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_fin_stage" class="form-label">Date de fin</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="date_fin_stage" 
                                               name="date_fin_stage" 
                                               value="<?= escape($rapport['date_fin_stage']) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de prévisualisation -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-body">
                <div id="previewContent" class="preview-content">
                    <!-- Le contenu sera généré par JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="exportPdfBtn">
                    <i class="fas fa-file-pdf me-2"></i>Exporter en PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Indicateur de sauvegarde -->
<div class="save-indicator" id="saveIndicator">
    <i class="fas fa-check-circle"></i>
    <span>Sauvegardé automatiquement</span>
</div>

<script>
// Configuration pour l'éditeur
window.RAPPORT_CONFIG = {
    rapportId: <?= json_encode($rapport['rapport_id']) ?>,
    etudiantId: <?= json_encode($etudiant['etudiant_id'] ?? '') ?>,
    autoSaveInterval: 30000, // 30 secondes
    wordCountTarget: 15000
};

// Fonction pour charger une version spécifique
function loadVersion(versionId) {
    if (!versionId) {
        console.error('ID de version manquant');
        return;
    }
    
    showLoader();
    
    fetch('api/rapport-versions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.APP_CONFIG?.csrfToken || ''
        },
        body: JSON.stringify({
            action: 'load_version',
            version_id: versionId
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        
        if (data.success) {
            // Charger les données de la version dans le formulaire
            const formData = data.version_data;
            
            // Remplir tous les champs du formulaire
            Object.keys(formData).forEach(key => {
                const field = document.getElementById(key);
                if (field) {
                    if (field.tagName.toLowerCase() === 'textarea' && field.classList.contains('wysiwyg-editor')) {
                        // Pour les éditeurs WYSIWYG (TinyMCE)
                        if (window.tinymce && tinymce.get(key)) {
                            tinymce.get(key).setContent(formData[key] || '');
                        } else {
                            field.value = formData[key] || '';
                        }
                    } else {
                        field.value = formData[key] || '';
                    }
                }
            });
            
            showToast('Version chargée avec succès', 'success');
        } else {
            showToast(data.message || 'Erreur lors du chargement de la version', 'error');
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Erreur:', error);
        showToast('Erreur lors du chargement de la version', 'error');
    });
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les éditeurs WYSIWYG si TinyMCE est disponible
    if (window.tinymce) {
        const wysiwygEditors = document.querySelectorAll('.wysiwyg-editor');
        wysiwygEditors.forEach(function(editor) {
            tinymce.init({
                target: editor,
                height: 300,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
                setup: function(editor) {
                    editor.on('change', function() {
                        // Déclencher la sauvegarde automatique lors des changements
                        triggerAutoSave();
                    });
                }
            });
        });
    }
    
    // Navigation entre les sections
    const sectionLinks = document.querySelectorAll('.sections-list .nav-link');
    const sectionCards = document.querySelectorAll('.section-card');
    
    sectionLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Retirer la classe active de tous les liens et sections
            sectionLinks.forEach(l => l.classList.remove('active'));
            sectionCards.forEach(s => s.classList.remove('active'));
            
            // Ajouter la classe active au lien cliqué
            this.classList.add('active');
            
            // Afficher la section correspondante
            const sectionId = this.getAttribute('href');
            const targetSection = document.querySelector(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    
    // Sauvegarde automatique
    let autoSaveTimer;
    
    function triggerAutoSave() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            saveRapport(true); // true pour sauvegarde automatique
        }, window.RAPPORT_CONFIG.autoSaveInterval);
    }
    
    // Écouter les changements dans le formulaire
    const formElements = document.querySelectorAll('#rapportForm input, #rapportForm select, #rapportForm textarea');
    formElements.forEach(function(element) {
        element.addEventListener('input', triggerAutoSave);
        element.addEventListener('change', triggerAutoSave);
    });
    
    // Bouton de sauvegarde manuelle
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            saveRapport(false); // false pour sauvegarde manuelle
        });
    }
    
    // Bouton de prévisualisation
    const previewBtn = document.getElementById('previewBtn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            generatePreview();
        });
    }
    
    // Fonction de sauvegarde
    function saveRapport(isAutoSave = false) {
        const formData = new FormData(document.getElementById('rapportForm'));
        
        // Ajouter le contenu des éditeurs WYSIWYG
        if (window.tinymce) {
            tinymce.get().forEach(function(editor) {
                formData.set(editor.id, editor.getContent());
            });
        }
        
        formData.append('action', 'save_rapport');
        formData.append('is_auto_save', isAutoSave ? '1' : '0');
        
        if (!isAutoSave) {
            showLoader();
        }
        
        fetch('api/rapport-save.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.APP_CONFIG?.csrfToken || ''
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!isAutoSave) {
                hideLoader();
            }
            
            if (data.success) {
                // Mettre à jour l'ID du rapport si c'est un nouveau rapport
                if (data.rapport_id) {
                    document.getElementById('rapportId').value = data.rapport_id;
                    window.RAPPORT_CONFIG.rapportId = data.rapport_id;
                }
                
                // Mettre à jour les statistiques
                if (data.stats) {
                    updateStats(data.stats);
                }
                
                // Afficher l'indicateur de sauvegarde
                showSaveIndicator(isAutoSave);
                
                if (!isAutoSave) {
                    showToast('Rapport sauvegardé avec succès', 'success');
                }
            } else {
                if (!isAutoSave) {
                    showToast(data.message || 'Erreur lors de la sauvegarde', 'error');
                }
            }
        })
        .catch(error => {
            if (!isAutoSave) {
                hideLoader();
                showToast('Erreur lors de la sauvegarde', 'error');
            }
            console.error('Erreur:', error);
        });
    }
    
    // Fonction pour mettre à jour les statistiques
    function updateStats(stats) {
        // Mettre à jour le nombre de mots
        const motsElement = document.querySelector('.rapport-info .text-muted span:nth-child(3)');
        if (motsElement) {
            motsElement.textContent = new Intl.NumberFormat('fr-FR').format(stats.mots_total) + ' mots';
        }
        
        // Mettre à jour la barre de progression
        const progressBar = document.querySelector('.progress-bar');
        const progressLabel = document.querySelector('.progress-label');
        if (progressBar && progressLabel) {
            progressBar.style.width = stats.progression + '%';
            progressBar.setAttribute('aria-valuenow', stats.progression);
            progressLabel.textContent = `Progression: ${stats.progression.toFixed(1)}%`;
        }
    }
    
    // Fonction pour afficher l'indicateur de sauvegarde
    function showSaveIndicator(isAutoSave) {
        const indicator = document.getElementById('saveIndicator');
        const icon = indicator.querySelector('i');
        const text = indicator.querySelector('span');
        
        if (isAutoSave) {
            icon.className = 'fas fa-check-circle';
            text.textContent = 'Sauvegardé automatiquement';
        } else {
            icon.className = 'fas fa-save';
            text.textContent = 'Sauvegardé manuellement';
        }
        
        indicator.classList.add('show');
        
        setTimeout(function() {
            indicator.classList.remove('show');
        }, 3000);
    }
    
    // Fonction de prévisualisation
    function generatePreview() {
        const formData = new FormData(document.getElementById('rapportForm'));
        
        // Ajouter le contenu des éditeurs WYSIWYG
        if (window.tinymce) {
            tinymce.get().forEach(function(editor) {
                formData.set(editor.id, editor.getContent());
            });
        }
        
        formData.append('action', 'preview_rapport');
        
        showLoader();
        
        fetch('api/rapport-preview.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.APP_CONFIG?.csrfToken || ''
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            
            if (data.success) {
                document.getElementById('previewContent').innerHTML = data.html;
                const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                modal.show();
            } else {
                showToast(data.message || 'Erreur lors de la génération de la prévisualisation', 'error');
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Erreur:', error);
            showToast('Erreur lors de la génération de la prévisualisation', 'error');
        });
    }
    
    // Export PDF
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', function() {
            const rapportId = window.RAPPORT_CONFIG.rapportId;
            if (rapportId) {
                window.open(`api/rapport-export-pdf.php?rapport_id=${rapportId}`, '_blank');
            } else {
                showToast('Veuillez d\'abord sauvegarder le rapport', 'warning');
            }
        });
    }
    
    // Avertir avant de quitter la page s'il y a des modifications non sauvées
    let hasUnsavedChanges = false;
    
    formElements.forEach(function(element) {
        element.addEventListener('input', function() {
            hasUnsavedChanges = true;
        });
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter cette page ?';
        }
    });
    
    // Marquer comme sauvé après une sauvegarde réussie
    document.addEventListener('rapportSaved', function() {
        hasUnsavedChanges = false;
    });
});
</script>

<style>
/* Styles pour l'éditeur de rapport */
.rapport-editor {
    padding: 0;
}

.editor-toolbar {
    background: white;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sections-nav .nav-link {
    color: #6c757d;
    border: none;
    border-radius: 0;
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
    transition: all 0.3s ease;
}

.sections-nav .nav-link:hover {
    background-color: #f8f9fa;
    color: var(--primary-color);
}

.sections-nav .nav-link.active {
    background-color: var(--primary-color);
    color: white;
}

.section-card {
    display: none;
    margin-bottom: 1.5rem;
}

.section-card.active {
    display: block;
}

.versions-panel .version-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.versions-panel .version-item:last-child {
    border-bottom: none;
}

.version-number {
    font-weight: 600;
    color: var(--primary-color);
}

.save-indicator {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: #28a745;
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 1050;
}

.save-indicator.show {
    transform: translateY(0);
    opacity: 1;
}

.save-indicator i {
    margin-right: 0.5rem;
}

.wysiwyg-editor {
    min-height: 300px;
}

.editor-textarea {
    min-height: 200px;
    resize: vertical;
}

.progress {
    height: 8px !important;
}

.preview-content {
    font-family: 'Times New Roman', serif;
    line-height: 1.6;
    color: #333;
}

.preview-content h1, .preview-content h2, .preview-content h3 {
    color: var(--primary-color);
    margin-top: 2rem;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .editor-toolbar .row {
        flex-direction: column;
    }
    
    .editor-toolbar .col-md-6:last-child {
        margin-top: 1rem;
        text-align: left !important;
    }
    
    .save-indicator {
        bottom: 1rem;
        right: 1rem;
        left: 1rem;
        text-align: center;
    }
}
</style>

<?php include '../../../includes/footer.php'; ?>-header">
                <h5 class="modal-title">Prévisualisation du Rapport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal