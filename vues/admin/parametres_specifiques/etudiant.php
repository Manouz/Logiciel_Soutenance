<?php

$database = new Database();
$pdo = $database->getConnection();

// Traitement des actions
$message = '';
$message_type = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    $pdo->beginTransaction();
                    
                    // Générer un salt unique
                    $salt = bin2hex(random_bytes(32));
                    $password_hash = hash('sha256', $_POST['mot_de_passe'] . $salt);
                    
                    // 1. Créer l'utilisateur
                    $stmt_user = $pdo->prepare("INSERT INTO utilisateurs (code_utilisateur, email, role_id, statut_id, mot_de_passe_hash, salt) VALUES (?, ?, ?, ?, ?, ?)");
                    $code_utilisateur = 'ETD' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    $stmt_user->execute([
                        $code_utilisateur,
                        $_POST['email'],
                        8, // Role étudiant
                        1, // Statut actif
                        $password_hash,
                        $salt
                    ]);
                    $utilisateur_id = $pdo->lastInsertId();
                    
                    // 2. Créer les informations personnelles
                    $stmt_info = $pdo->prepare("INSERT INTO informations_personnelles (utilisateur_id, nom, prenoms, date_naissance, genre, telephone, adresse, ville, pays) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_info->execute([
                        $utilisateur_id,
                        $_POST['nom'],
                        $_POST['prenoms'],
                        $_POST['date_naissance'],
                        $_POST['genre'] ?? 'M',
                        $_POST['telephone'] ?? '',
                        $_POST['adresse'] ?? '',
                        $_POST['ville'] ?? '',
                        $_POST['pays'] ?? 'Côte d\'Ivoire'
                    ]);
                    
                    // 3. Créer l'étudiant
                    $stmt_etudiant = $pdo->prepare("INSERT INTO etudiants (utilisateur_id, numero_etudiant, numero_carte_etudiant, niveau_id, specialite_id, annee_inscription, statut_eligibilite) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $numero_etudiant = 'E' . date('Y') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                    $stmt_etudiant->execute([
                        $utilisateur_id,
                        $numero_etudiant,
                        $_POST['numero_carte_etudiant'],
                        $_POST['niveau_id'],
                        $_POST['specialite_id'] ?? null,
                        $_POST['annee_inscription'] ?? date('Y'),
                        $_POST['statut_eligibilite']
                    ]);
                    
                    $pdo->commit();
                    $message = "Étudiant créé avec succès.";
                    $message_type = "success";
                } catch(PDOException $e) {
                    $pdo->rollback();
                    $message = "Erreur lors de la création : " . $e->getMessage();
                    $message_type = "error";
                }
                break;

            case 'update':
                try {
                    $pdo->beginTransaction();
                    
                    // 1. Mettre à jour l'utilisateur
                    $stmt_user = $pdo->prepare("UPDATE utilisateurs SET email = ? WHERE utilisateur_id = ?");
                    $stmt_user->execute([$_POST['email'], $_POST['utilisateur_id']]);
                    
                    // 2. Mettre à jour les informations personnelles
                    $stmt_info = $pdo->prepare("UPDATE informations_personnelles SET nom = ?, prenoms = ?, date_naissance = ?, genre = ?, telephone = ?, adresse = ?, ville = ?, pays = ? WHERE utilisateur_id = ?");
                    $stmt_info->execute([
                        $_POST['nom'],
                        $_POST['prenoms'],
                        $_POST['date_naissance'],
                        $_POST['genre'],
                        $_POST['telephone'],
                        $_POST['adresse'],
                        $_POST['ville'],
                        $_POST['pays'],
                        $_POST['utilisateur_id']
                    ]);
                    
                    // 3. Mettre à jour l'étudiant
                    $stmt_etudiant = $pdo->prepare("UPDATE etudiants SET numero_carte_etudiant = ?, niveau_id = ?, specialite_id = ?, annee_inscription = ?, statut_eligibilite = ? WHERE etudiant_id = ?");
                    $stmt_etudiant->execute([
                        $_POST['numero_carte_etudiant'],
                        $_POST['niveau_id'],
                        $_POST['specialite_id'],
                        $_POST['annee_inscription'],
                        $_POST['statut_eligibilite'],
                        $_POST['etudiant_id']
                    ]);
                    
                    $pdo->commit();
                    $message = "Étudiant mis à jour avec succès.";
                    $message_type = "success";
                } catch(PDOException $e) {
                    $pdo->rollback();
                    $message = "Erreur lors de la mise à jour : " . $e->getMessage();
                    $message_type = "error";
                }
                break;

            case 'delete':
                try {
                    // La suppression en cascade est gérée par les contraintes FK
                    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE utilisateur_id = (SELECT utilisateur_id FROM etudiants WHERE etudiant_id = ?)");
                    $stmt->execute([$_POST['etudiant_id']]);
                    $message = "Étudiant supprimé avec succès.";
                    $message_type = "success";
                } catch(PDOException $e) {
                    $message = "Erreur lors de la suppression : " . $e->getMessage();
                    $message_type = "error";
                }
                break;

            case 'delete_multiple':
                if (isset($_POST['selected_students']) && !empty($_POST['selected_students'])) {
                    try {
                        $pdo->beginTransaction();
                        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE utilisateur_id = (SELECT utilisateur_id FROM etudiants WHERE etudiant_id = ?)");
                        foreach ($_POST['selected_students'] as $etudiant_id) {
                            $stmt->execute([$etudiant_id]);
                        }
                        $pdo->commit();
                        $count = count($_POST['selected_students']);
                        $message = "$count étudiant(s) supprimé(s) avec succès.";
                        $message_type = "success";
                    } catch(PDOException $e) {
                        $pdo->rollback();
                        $message = "Erreur lors de la suppression multiple : " . $e->getMessage();
                        $message_type = "error";
                    }
                } else {
                    $message = "Aucun étudiant sélectionné.";
                    $message_type = "warning";
                }
                break;
        }
    }
}

// Récupération des étudiants avec toutes les informations via la vue
$query = "SELECT 
    e.etudiant_id,
    e.utilisateur_id,
    e.numero_etudiant,
    e.numero_carte_etudiant,
    ip.nom,
    ip.prenoms,
    ip.date_naissance,
    ip.genre,
    ip.telephone,
    ip.adresse,
    ip.ville,
    ip.pays,
    u.email,
    ne.libelle_niveau,
    sp.libelle_specialite,
    e.annee_inscription,
    e.moyenne_generale,
    e.nombre_credits_valides,
    e.nombre_credits_requis,
    e.taux_progression,
    s.libelle_statut as statut_eligibilite,
    s.couleur_affichage,
    u.est_actif,
    u.derniere_connexion,
    COALESCE(r.montant_total, 0) as montant_total_reglement,
    COALESCE(r.montant_paye, 0) as montant_paye_reglement,
    COALESCE(sr.libelle_statut, 'Non défini') as statut_reglement
FROM etudiants e
INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
INNER JOIN niveaux_etude ne ON e.niveau_id = ne.niveau_id
LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
INNER JOIN statuts s ON e.statut_eligibilite = s.statut_id
LEFT JOIN reglements r ON e.etudiant_id = r.etudiant_id
LEFT JOIN statuts sr ON r.statut_id = sr.statut_id
WHERE u.est_actif = 1
ORDER BY ip.nom ASC, ip.prenoms ASC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN s.code_statut = 'ELIGIBLE' THEN 1 ELSE 0 END) as eligible,
    SUM(CASE WHEN s.code_statut = 'EN_ATTENTE' THEN 1 ELSE 0 END) as attente,
    SUM(CASE WHEN s.code_statut = 'NON_ELIGIBLE' THEN 1 ELSE 0 END) as non_eligible
FROM etudiants e
INNER JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
INNER JOIN statuts s ON e.statut_eligibilite = s.statut_id
WHERE u.est_actif = 1";
$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des données pour les formulaires
$niveaux_query = "SELECT niveau_id, libelle_niveau FROM niveaux_etude WHERE est_actif = 1 ORDER BY libelle_niveau";
$niveaux_stmt = $pdo->prepare($niveaux_query);
$niveaux_stmt->execute();
$niveaux = $niveaux_stmt->fetchAll(PDO::FETCH_ASSOC);

$specialites_query = "SELECT specialite_id, libelle_specialite FROM specialites WHERE est_actif = 1 ORDER BY libelle_specialite";
$specialites_stmt = $pdo->prepare($specialites_query);
$specialites_stmt->execute();
$specialites = $specialites_stmt->fetchAll(PDO::FETCH_ASSOC);

$statuts_eligibilite_query = "SELECT statut_id, libelle_statut FROM statuts WHERE type_statut = 'Etudiant' AND est_actif = 1 ORDER BY ordre_affichage";
$statuts_stmt = $pdo->prepare($statuts_eligibilite_query);
$statuts_stmt->execute();
$statuts_eligibilite = $statuts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* Styles spécifiques pour la gestion des étudiants */
.toolbar {
    background: var(--white);
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.toolbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.toolbar-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.export-dropdown {
    position: relative;
    display: inline-block;
}

.export-btn {
    background: var(--secondary-color);
    color: var(--white);
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    transition: var(--transition);
}

.export-btn:hover {
    background: #059669;
    transform: translateY(-1px);
}

.export-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--white);
    border-radius: 8px;
    box-shadow: var(--shadow-lg);
    min-width: 150px;
    z-index: 1000;
    margin-top: 0.5rem;
}

.export-menu.show {
    display: block;
}

.export-menu a {
    display: block;
    padding: 0.75rem 1rem;
    color: var(--gray-700);
    text-decoration: none;
    transition: var(--transition);
    border-bottom: 1px solid var(--gray-200);
}

.export-menu a:last-child {
    border-bottom: none;
}

.export-menu a:hover {
    background: var(--gray-50);
    color: var(--primary-color);
}

.export-menu i {
    margin-right: 0.5rem;
    width: 16px;
}

.btn-danger {
    background: var(--error-color);
    color: var(--white);
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-1px);
}

.btn-print {
    background: var(--warning-color);
    color: var(--white);
}

.btn-print:hover {
    background: #d97706;
    transform: translateY(-1px);
}

.selected-actions {
    display: none;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 1rem;
    background: var(--primary-light);
    border-radius: 8px;
    border: 1px solid var(--primary-color);
}

.selected-actions.show {
    display: flex;
}

.selected-count {
    font-weight: 500;
    color: var(--primary-color);
}

/* Table scrollable horizontalement */
.table-container {
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.table-wrapper {
    overflow-x: auto;
    overflow-y: visible;
}

.table-wrapper::-webkit-scrollbar {
    height: 8px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: var(--gray-100);
    border-radius: 4px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: var(--gray-300);
    border-radius: 4px;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: var(--gray-400);
}

.students-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1400px; /* Force le scroll horizontal si nécessaire */
}

.students-table th,
.students-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
    white-space: nowrap;
}

.students-table th {
    background: var(--gray-50);
    font-weight: 600;
    color: var(--gray-700);
    position: sticky;
    top: 0;
    z-index: 10;
}

.students-table tr:hover {
    background: var(--gray-50);
}

.checkbox-cell {
    width: 50px;
    text-align: center;
}

.actions-cell {
    width: 120px;
    text-align: center;
}

.student-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.select-all-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}

.btn-sm {
    padding: 0.375rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 4px;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.progress-bar {
    width: 100px;
    height: 8px;
    background: var(--gray-200);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--error-color), var(--warning-color), var(--success-color));
    transition: width 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .toolbar-left,
    .toolbar-right {
        justify-content: center;
    }
    
    .students-table th,
    .students-table td {
        padding: 0.5rem;
        font-size: 0.875rem;
    }
}

/* Print styles */
@media print {
    .toolbar,
    .filters-bar,
    .selected-actions,
    .action-buttons,
    .sidebar,
    .content-header {
        display: none !important;
    }
    
    .main-content {
        margin-left: 0 !important;
    }
    
    .content-body {
        padding: 0 !important;
    }
    
    .table-container {
        box-shadow: none;
        border: 1px solid #000;
    }
    
    .students-table {
        min-width: auto;
    }
    
    .students-table th,
    .students-table td {
        border: 1px solid #000;
        padding: 0.5rem;
        font-size: 12px;
    }
    
    .checkbox-cell {
        display: none;
    }
}

/* Variables CSS pour les couleurs */
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
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --border-radius: 12px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--white);
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.stat-info h3 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.25rem;
}

.stat-info p {
    color: var(--gray-600);
    font-size: 0.9rem;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--primary-color);
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
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
    background: var(--gray-200);
    color: var(--gray-700);
}

.btn-secondary:hover {
    background: var(--gray-300);
}

.filters-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: center;
}

.filter-select,
.search-input {
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: 8px;
    font-size: 0.9rem;
    transition: var(--transition);
}

.filter-select:focus,
.search-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.search-input {
    flex: 1;
    max-width: 300px;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.btn-icon {
    padding: 0.5rem;
    border: none;
    background: none;
    cursor: pointer;
    border-radius: 6px;
    transition: var(--transition);
    color: var(--gray-600);
}

.btn-icon:hover {
    background: var(--gray-100);
    color: var(--primary-color);
}

.btn-icon.btn-danger:hover {
    background: var(--error-color);
    color: var(--white);
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-xl);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--primary-color);
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: var(--gray-500);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: var(--transition);
}

.modal-close:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: 8px;
    font-size: 0.9rem;
    transition: var(--transition);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-width: 300px;
    max-width: 500px;
    z-index: 3000;
    animation: slideInRight 0.3s ease-out;
    border-left: 4px solid var(--primary-color);
}

.notification-success {
    border-left-color: var(--success-color);
}

.notification-error {
    border-left-color: var(--error-color);
}

.notification-warning {
    border-left-color: var(--warning-color);
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.notification-success .notification-content i {
    color: var(--success-color);
}

.notification-error .notification-content i {
    color: var(--error-color);
}

.notification-warning .notification-content i {
    color: var(--warning-color);
}

.notification-close {
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: var(--transition);
}

.notification-close:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<?php if ($message): ?>
<div class="notification notification-<?php echo $message_type; ?>" id="notification">
    <div class="notification-content">
        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : ($message_type == 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
        <span><?php echo $message; ?></span>
    </div>
    <button class="notification-close" onclick="closeNotification()">
        <i class="fas fa-times"></i>
    </button>
</div>
<?php endif; ?>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['total']; ?></h3>
            <p>Total Étudiants</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['eligible']; ?></h3>
            <p>Éligibles</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['attente']; ?></h3>
            <p>En Attente</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['non_eligible']; ?></h3>
            <p>Non Éligibles</p>
        </div>
    </div>
</div>

<!-- Barre d'outils -->
<div class="toolbar">
    <div class="toolbar-left">
        <button class="btn btn-primary" onclick="openModal('createModal')">
            <i class="fas fa-plus"></i>
            Nouvel Étudiant
        </button>
        
        <div class="selected-actions" id="selectedActions">
            <span class="selected-count" id="selectedCount">0 sélectionné(s)</span>
            <button class="btn btn-danger btn-sm" onclick="deleteSelected()">
                <i class="fas fa-trash"></i>
                Supprimer
            </button>
        </div>
    </div>
    
    <div class="toolbar-right">
        <button class="btn btn-print" onclick="printTable()">
            <i class="fas fa-print"></i>
            Imprimer
        </button>
        
        <div class="export-dropdown">
            <button class="export-btn" onclick="toggleExportMenu()">
                <i class="fas fa-download"></i>
                Exporter
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="export-menu" id="exportMenu">
                <a href="#" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf"></i>
                    Export PDF
                </a>
                <a href="#" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i>
                    Export Excel
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filters-bar">
    <select class="filter-select" id="statusFilter" onchange="filterTable()">
        <option value="">Tous les statuts</option>
        <?php foreach($statuts_eligibilite as $statut): ?>
            <option value="<?php echo htmlspecialchars($statut['libelle_statut']); ?>">
                <?php echo htmlspecialchars($statut['libelle_statut']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="text" class="search-input" id="searchInput" placeholder="Rechercher un étudiant..." onkeyup="filterTable()">
</div>

<!-- Table des étudiants -->
<div class="table-container">
    <div class="table-wrapper">
        <table class="students-table" id="studentsTable">
            <thead>
                <tr>
                    <th class="checkbox-cell">
                        <input type="checkbox" class="select-all-checkbox" id="selectAll" onchange="toggleSelectAll()">
                    </th>
                    <th>N° Étudiant</th>
                    <th>N° Carte</th>
                    <th>Nom</th>
                    <th>Prénoms</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Date Naissance</th>
                    <th>Genre</th>
                    <th>Niveau</th>
                    <th>Spécialité</th>
                    <th>Année Inscription</th>
                    <th>Statut Éligibilité</th>
                    <th>Progression</th>
                    <th>Dernière Connexion</th>
                    <th class="actions-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $etd): ?>
                <tr>
                    <td class="checkbox-cell">
                        <input type="checkbox" class="student-checkbox" value="<?php echo $etd['etudiant_id']; ?>" onchange="updateSelectedCount()">
                    </td>
                    <td><?php echo htmlspecialchars($etd['numero_etudiant']); ?></td>
                    <td><?php echo htmlspecialchars($etd['numero_carte_etudiant']); ?></td>
                    <td><?php echo htmlspecialchars($etd['nom']); ?></td>
                    <td><?php echo htmlspecialchars($etd['prenoms']); ?></td>
                    <td><?php echo htmlspecialchars($etd['email']); ?></td>
                    <td><?php echo htmlspecialchars($etd['telephone']); ?></td>
                    <td><?php echo $etd['date_naissance'] ? date('d/m/Y', strtotime($etd['date_naissance'])) : 'N/A'; ?></td>
                    <td><?php echo $etd['genre'] == 'M' ? 'Masculin' : 'Féminin'; ?></td>
                    <td><?php echo htmlspecialchars($etd['libelle_niveau']); ?></td>
                    <td><?php echo htmlspecialchars($etd['libelle_specialite'] ?? 'Non définie'); ?></td>
                    <td><?php echo $etd['annee_inscription']; ?></td>
                    <td>
                        <span class="status-badge" style="background-color: <?php echo $etd['couleur_affichage']; ?>20; color: <?php echo $etd['couleur_affichage']; ?>;">
                            <?php echo htmlspecialchars($etd['statut_eligibilite']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $etd['taux_progression']; ?>%"></div>
                        </div>
                        <small><?php echo number_format($etd['taux_progression'], 1); ?>%</small>
                    </td>
                    <td><?php echo $etd['derniere_connexion'] ? date('d/m/Y H:i', strtotime($etd['derniere_connexion'])) : 'Jamais'; ?></td>
                    <td class="actions-cell">
                        <div class="action-buttons">
                            <button class="btn-icon btn-sm" onclick="editStudent(<?php echo $etd['etudiant_id']; ?>)" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-sm btn-danger" onclick="deleteStudent(<?php echo $etd['etudiant_id']; ?>)" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn-icon btn-sm" onclick="viewStudent(<?php echo $etd['etudiant_id']; ?>)" title="Voir détails">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Création -->
<div class="modal" id="createModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nouvel Étudiant</h3>
            <button class="modal-close" onclick="closeModal('createModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="create">
                
                <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Informations personnelles</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenoms">Prénoms *</label>
                        <input type="text" id="prenoms" name="prenoms" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_naissance">Date de Naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance">
                    </div>
                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre">
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <textarea id="adresse" name="adresse" rows="2"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville">
                    </div>
                    <div class="form-group">
                        <label for="pays">Pays</label>
                        <input type="text" id="pays" name="pays" value="Côte d'Ivoire">
                    </div>
                </div>

                <h4 style="margin: 1.5rem 0 1rem; color: var(--primary-color);">Informations académiques</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="numero_carte_etudiant">Numéro de Carte Étudiant *</label>
                        <input type="text" id="numero_carte_etudiant" name="numero_carte_etudiant" required>
                    </div>
                    <div class="form-group">
                        <label for="annee_inscription">Année d'Inscription</label>
                        <input type="number" id="annee_inscription" name="annee_inscription" value="<?php echo date('Y'); ?>" min="2020" max="<?php echo date('Y') + 1; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="niveau_id">Niveau d'Étude *</label>
                        <select id="niveau_id" name="niveau_id" required>
                            <option value="">Sélectionner un niveau</option>
                            <?php foreach($niveaux as $niveau): ?>
                                <option value="<?php echo $niveau['niveau_id']; ?>">
                                    <?php echo htmlspecialchars($niveau['libelle_niveau']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="specialite_id">Spécialité</label>
                        <select id="specialite_id" name="specialite_id">
                            <option value="">Sélectionner une spécialité</option>
                            <?php foreach($specialites as $specialite): ?>
                                <option value="<?php echo $specialite['specialite_id']; ?>">
                                    <?php echo htmlspecialchars($specialite['libelle_specialite']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="statut_eligibilite">Statut d'Éligibilité</label>
                        <select id="statut_eligibilite" name="statut_eligibilite">
                            <?php foreach($statuts_eligibilite as $statut): ?>
                                <option value="<?php echo $statut['statut_id']; ?>" <?php echo $statut['libelle_statut'] == 'En attente de vérification' ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($statut['libelle_statut']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mot_de_passe">Mot de Passe *</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Édition -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier Étudiant</h3>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="editForm">
            <div class="modal-body">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="etudiant_id" id="edit_etudiant_id">
                <input type="hidden" name="utilisateur_id" id="edit_utilisateur_id">
                
                <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Informations personnelles</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_nom">Nom *</label>
                        <input type="text" id="edit_nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_prenoms">Prénoms *</label>
                        <input type="text" id="edit_prenoms" name="prenoms" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email *</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_telephone">Téléphone</label>
                        <input type="tel" id="edit_telephone" name="telephone">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_date_naissance">Date de Naissance</label>
                        <input type="date" id="edit_date_naissance" name="date_naissance">
                    </div>
                    <div class="form-group">
                        <label for="edit_genre">Genre</label>
                        <select id="edit_genre" name="genre">
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_adresse">Adresse</label>
                    <textarea id="edit_adresse" name="adresse" rows="2"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_ville">Ville</label>
                        <input type="text" id="edit_ville" name="ville">
                    </div>
                    <div class="form-group">
                        <label for="edit_pays">Pays</label>
                        <input type="text" id="edit_pays" name="pays">
                    </div>
                </div>

                <h4 style="margin: 1.5rem 0 1rem; color: var(--primary-color);">Informations académiques</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_numero_carte_etudiant">Numéro de Carte Étudiant *</label>
                        <input type="text" id="edit_numero_carte_etudiant" name="numero_carte_etudiant" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_annee_inscription">Année d'Inscription</label>
                        <input type="number" id="edit_annee_inscription" name="annee_inscription" min="2020" max="<?php echo date('Y') + 1; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_niveau_id">Niveau d'Étude *</label>
                        <select id="edit_niveau_id" name="niveau_id" required>
                            <?php foreach($niveaux as $niveau): ?>
                                <option value="<?php echo $niveau['niveau_id']; ?>">
                                    <?php echo htmlspecialchars($niveau['libelle_niveau']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_specialite_id">Spécialité</label>
                        <select id="edit_specialite_id" name="specialite_id">
                            <option value="">Sélectionner une spécialité</option>
                            <?php foreach($specialites as $specialite): ?>
                                <option value="<?php echo $specialite['specialite_id']; ?>">
                                    <?php echo htmlspecialchars($specialite['libelle_specialite']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_statut_eligibilite">Statut d'Éligibilité</label>
                    <select id="edit_statut_eligibilite" name="statut_eligibilite">
                        <?php foreach($statuts_eligibilite as $statut): ?>
                            <option value="<?php echo $statut['statut_id']; ?>">
                                <?php echo htmlspecialchars($statut['libelle_statut']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
// Variables globales pour stocker les données des étudiants
const studentsData = <?php echo json_encode($etudiants); ?>;

// Gestion des modals
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Fermer les notifications
function closeNotification() {
    const notification = document.getElementById('notification');
    if (notification) {
        notification.style.display = 'none';
    }
}

// Auto-fermeture des notifications après 5 secondes
setTimeout(function() {
    closeNotification();
}, 5000);

// Gestion de la sélection multiple
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    
    studentCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
    const count = selectedCheckboxes.length;
    const selectedActions = document.getElementById('selectedActions');
    const selectedCount = document.getElementById('selectedCount');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    selectedCount.textContent = count + ' sélectionné(s)';
    
    if (count > 0) {
        selectedActions.classList.add('show');
    } else {
        selectedActions.classList.remove('show');
    }
    
    // Mettre à jour la checkbox "Tout sélectionner"
    const totalCheckboxes = document.querySelectorAll('.student-checkbox').length;
    selectAllCheckbox.indeterminate = count > 0 && count < totalCheckboxes;
    selectAllCheckbox.checked = count === totalCheckboxes;
}

// Suppression multiple
function deleteSelected() {
    const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert('Aucun étudiant sélectionné.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedCheckboxes.length} étudiant(s) ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete_multiple">';
        
        selectedCheckboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_students[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Édition d'un étudiant
function editStudent(etudiantId) {
    const student = studentsData.find(s => s.etudiant_id == etudiantId);
    if (student) {
        document.getElementById('edit_etudiant_id').value = student.etudiant_id;
        document.getElementById('edit_utilisateur_id').value = student.utilisateur_id;
        document.getElementById('edit_nom').value = student.nom;
        document.getElementById('edit_prenoms').value = student.prenoms;
        document.getElementById('edit_email').value = student.email;
        document.getElementById('edit_telephone').value = student.telephone || '';
        document.getElementById('edit_date_naissance').value = student.date_naissance;
        document.getElementById('edit_genre').value = student.genre;
        document.getElementById('edit_adresse').value = student.adresse || '';
        document.getElementById('edit_ville').value = student.ville || '';
        document.getElementById('edit_pays').value = student.pays || '';
        document.getElementById('edit_numero_carte_etudiant').value = student.numero_carte_etudiant;
        document.getElementById('edit_annee_inscription').value = student.annee_inscription;
        
        // Pour les selects, on doit trouver les bonnes valeurs
        const niveauSelect = document.getElementById('edit_niveau_id');
        for (let option of niveauSelect.options) {
            if (option.text === student.libelle_niveau) {
                option.selected = true;
                break;
            }
        }
        
        const specialiteSelect = document.getElementById('edit_specialite_id');
        for (let option of specialiteSelect.options) {
            if (option.text === student.libelle_specialite) {
                option.selected = true;
                break;
            }
        }
        
        const statutSelect = document.getElementById('edit_statut_eligibilite');
        for (let option of statutSelect.options) {
            if (option.text === student.statut_eligibilite) {
                option.selected = true;
                break;
            }
        }
        
        openModal('editModal');
    }
}

// Suppression d'un étudiant
function deleteStudent(etudiantId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="etudiant_id" value="${etudiantId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Voir les détails d'un étudiant
function viewStudent(etudiantId) {
    const student = studentsData.find(s => s.etudiant_id == etudiantId);
    if (student) {
        const details = `
Détails de l'étudiant:

Informations personnelles:
- Nom: ${student.nom}
- Prénoms: ${student.prenoms}
- Email: ${student.email}
- Téléphone: ${student.telephone || 'Non renseigné'}
- Date de naissance: ${student.date_naissance || 'Non renseignée'}
- Genre: ${student.genre === 'M' ? 'Masculin' : 'Féminin'}
- Adresse: ${student.adresse || 'Non renseignée'}
- Ville: ${student.ville || 'Non renseignée'}

Informations académiques:
- N° Étudiant: ${student.numero_etudiant}
- N° Carte: ${student.numero_carte_etudiant}
- Niveau: ${student.libelle_niveau}
- Spécialité: ${student.libelle_specialite || 'Non définie'}
- Année d'inscription: ${student.annee_inscription}
- Statut: ${student.statut_eligibilite}
- Progression: ${student.taux_progression}%
- Crédits validés: ${student.nombre_credits_valides}/${student.nombre_credits_requis}
        `;
        alert(details);
    }
}

// Filtrage du tableau
function filterTable() {
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('studentsTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        
        const nom = cells[3].textContent.toLowerCase();
        const prenoms = cells[4].textContent.toLowerCase();
        const email = cells[5].textContent.toLowerCase();
        const numeroEtudiant = cells[1].textContent.toLowerCase();
        const statut = cells[12].textContent.toLowerCase();
        
        const matchesSearch = nom.includes(searchInput) || 
                            prenoms.includes(searchInput) || 
                            email.includes(searchInput) ||
                            numeroEtudiant.includes(searchInput);
        
        const matchesStatus = statusFilter === '' || statut.includes(statusFilter);
        
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

// Gestion du menu d'export
function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    menu.classList.toggle('show');
}
    const menu = document.getElementById('exportMenu');
    menu.classList.toggle('show');
}

// Fermer le menu d'export en cliquant ailleurs
document.addEventListener('click', function(event) {
    const exportDropdown = document.querySelector('.export-dropdown');
    if (!exportDropdown.contains(event.target)) {
        document.getElementById('exportMenu').classList.remove('show');
    }
});

// Impression
function printTable() {
    window.print();
}

// Export PDF (simulation)
function exportToPDF() {
    // Récupérer les données visibles du tableau
    const table = document.getElementById('studentsTable');
    const rows = table.querySelectorAll('tr:not([style*="display: none"])');
    
    let csvContent = "N° Étudiant,N° Carte,Nom,Prénoms,Email,Téléphone,Date Naissance,Genre,Niveau,Spécialité,Année Inscription,Statut Éligibilité,Progression\n";
    
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].querySelectorAll('td');
        const rowData = [
            cells[1].textContent, // N° Étudiant
            cells[2].textContent, // N° Carte
            cells[3].textContent, // Nom
            cells[4].textContent, // Prénoms
            cells[5].textContent, // Email
            cells[6].textContent, // Téléphone
            cells[7].textContent, // Date Naissance
            cells[8].textContent, // Genre
            cells[9].textContent, // Niveau
            cells[10].textContent, // Spécialité
            cells[11].textContent, // Année Inscription
            cells[12].textContent.replace(/\s+/g, ' ').trim(), // Statut Éligibilité
            cells[13].textContent.replace(/\s+/g, ' ').trim()  // Progression
        ];
        csvContent += rowData.map(field => `"${field}"`).join(',') + '\n';
    }
    
    // Créer un blob et télécharger
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'etudiants_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    document.getElementById('exportMenu').classList.remove('show');
    
    // Notification
    showNotification('Export PDF simulé (fichier CSV généré)', 'info');
}

// Export Excel (simulation)
function exportToExcel() {
    // Récupérer les données du tableau
    const table = document.getElementById('studentsTable');
    const rows = table.querySelectorAll('tr:not([style*="display: none"])');
    
    let htmlTable = '<table border="1">';
    
    // En-têtes
    htmlTable += '<tr>';
    const headers = ['N° Étudiant', 'N° Carte', 'Nom', 'Prénoms', 'Email', 'Téléphone', 'Date Naissance', 'Genre', 'Niveau', 'Spécialité', 'Année Inscription', 'Statut Éligibilité', 'Progression'];
    headers.forEach(header => {
        htmlTable += `<th>${header}</th>`;
    });
    htmlTable += '</tr>';
    
    // Données
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].querySelectorAll('td');
        htmlTable += '<tr>';
        for (let j = 1; j <= 13; j++) {
            htmlTable += `<td>${cells[j].textContent}</td>`;
        }
        htmlTable += '</tr>';
    }
    
    htmlTable += '</table>';
    
    // Créer un blob Excel
    const blob = new Blob([htmlTable], { type: 'application/vnd.ms-excel' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'etudiants_' + new Date().toISOString().split('T')[0] + '.xls');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    document.getElementById('exportMenu').classList.remove('show');
    
    // Notification
    showNotification('Export Excel généré avec succès', 'success');
}

// Fonction pour afficher les notifications
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-suppression après 3 secondes
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

// Fermer les modals en cliquant à l'extérieur
window.onclick = function(event) {
    const modals = document.getElementsByClassName('modal');
    for (let i = 0; i < modals.length; i++) {
        if (event.target === modals[i]) {
            modals[i].classList.remove('active');
        }
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});
</script>





























