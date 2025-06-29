<?php
/**
 * Fichier des fonctions utilitaires du système de validation académique
 * Université Félix Houphouët-Boigny de Cocody
 * 
 * @author Système de Gestion Académique
 * @version 1.0
 * @date 2025
 */

// Empêcher l'accès direct
if (!defined('INCLUDES_PATH')) {
    die('Accès direct interdit');
}

// ========================================================================================
// FONCTIONS DE SÉCURITÉ ET AUTHENTIFICATION
// ========================================================================================

/**
 * Hacher un mot de passe avec salt
 */
function hasherMotDePasse($motDePasse, $salt = null) {
    if ($salt === null) {
        $salt = bin2hex(random_bytes(32));
    }
    return [
        'hash' => password_hash($motDePasse . $salt, PASSWORD_ARGON2ID),
        'salt' => $salt
    ];
}

/**
 * Vérifier un mot de passe
 */
function verifierMotDePasse($motDePasse, $hash, $salt) {
    return password_verify($motDePasse . $salt, $hash);
}

/**
 * Générer un token sécurisé
 */
function genererToken($longueur = 32) {
    return bin2hex(random_bytes($longueur));
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function estConnecte() {
    return isset($_SESSION['utilisateur_id']) && !empty($_SESSION['utilisateur_id']);
}

/**
 * Vérifier le rôle de l'utilisateur
 */
function verifierRole($roles_autorises) {
    if (!estConnecte()) {
        return false;
    }
    
    if (is_string($roles_autorises)) {
        $roles_autorises = [$roles_autorises];
    }
    
    return in_array($_SESSION['role_nom'], $roles_autorises);
}

/**
 * Redirecter si non autorisé
 */
function verifierAutorisation($roles_autorises) {
    if (!verifierRole($roles_autorises)) {
        header('Location: /pages/erreur/403.php');
        exit;
    }
}

/**
 * Déconnexion sécurisée
 */
function deconnecter() {
    global $conn;
    
    if (estConnecte()) {
        // Invalider la session en base
        $stmt = $conn->prepare("UPDATE sessions_utilisateurs SET est_active = 0 WHERE utilisateur_id = ? AND est_active = 1");
        $stmt->execute([$_SESSION['utilisateur_id']]);
        
        // Log de déconnexion
        ajouterLogAudit($_SESSION['utilisateur_id'], 'LOGOUT', 'sessions_utilisateurs', $_SESSION['utilisateur_id']);
    }
    
    session_destroy();
    header('Location: /login.php');
    exit;
}

// ========================================================================================
// FONCTIONS DE BASE DE DONNÉES
// ========================================================================================

/**
 * Exécuter une requête sécurisée
 */
function executerRequete($sql, $params = []) {
    global $conn;
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erreur SQL: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupérer un enregistrement
 */
function obtenirEnregistrement($sql, $params = []) {
    $stmt = executerRequete($sql, $params);
    return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
}

/**
 * Récupérer plusieurs enregistrements
 */
function obtenirEnregistrements($sql, $params = []) {
    $stmt = executerRequete($sql, $params);
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

/**
 * Compter les enregistrements
 */
function compterEnregistrements($table, $conditions = '', $params = []) {
    $sql = "SELECT COUNT(*) as total FROM $table";
    if (!empty($conditions)) {
        $sql .= " WHERE $conditions";
    }
    
    $result = obtenirEnregistrement($sql, $params);
    return $result ? $result['total'] : 0;
}

/**
 * Insérer un enregistrement
 */
function insererEnregistrement($table, $donnees) {
    global $conn;
    
    $colonnes = implode(',', array_keys($donnees));
    $placeholders = ':' . implode(', :', array_keys($donnees));
    
    $sql = "INSERT INTO $table ($colonnes) VALUES ($placeholders)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($donnees);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Erreur insertion: " . $e->getMessage());
        return false;
    }
}

/**
 * Mettre à jour un enregistrement
 */
function mettreAJourEnregistrement($table, $donnees, $conditions, $params_conditions = []) {
    global $conn;
    
    $set_clause = [];
    foreach (array_keys($donnees) as $colonne) {
        $set_clause[] = "$colonne = :$colonne";
    }
    
    $sql = "UPDATE $table SET " . implode(', ', $set_clause) . " WHERE $conditions";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_merge($donnees, $params_conditions));
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Erreur mise à jour: " . $e->getMessage());
        return false;
    }
}

/**
 * Supprimer un enregistrement
 */
function supprimerEnregistrement($table, $conditions, $params = []) {
    global $conn;
    
    $sql = "DELETE FROM $table WHERE $conditions";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Erreur suppression: " . $e->getMessage());
        return false;
    }
}

// ========================================================================================
// FONCTIONS MÉTIER SPÉCIFIQUES
// ========================================================================================

/**
 * Obtenir les informations complètes d'un utilisateur
 */
function obtenirUtilisateurComplet($utilisateur_id) {
    $sql = "
        SELECT u.*, ip.*, r.nom_role, s.libelle_statut, s.couleur_affichage
        FROM utilisateurs u
        LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        LEFT JOIN roles r ON u.role_id = r.role_id
        LEFT JOIN statuts s ON u.statut_id = s.statut_id
        WHERE u.utilisateur_id = ?
    ";
    
    return obtenirEnregistrement($sql, [$utilisateur_id]);
}

/**
 * Obtenir les informations d'un étudiant
 */
function obtenirEtudiantComplet($etudiant_id) {
    $sql = "
        SELECT e.*, u.email, ip.nom, ip.prenoms, ip.telephone,
               ne.libelle_niveau, sp.libelle_specialite,
               s.libelle_statut as statut_eligibilite_libelle,
               s.couleur_affichage
        FROM etudiants e
        JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id
        JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
        JOIN niveaux_etude ne ON e.niveau_id = ne.niveau_id
        LEFT JOIN specialites sp ON e.specialite_id = sp.specialite_id
        JOIN statuts s ON e.statut_eligibilite = s.statut_id
        WHERE e.etudiant_id = ?
    ";
    
    return obtenirEnregistrement($sql, [$etudiant_id]);
}

/**
 * Vérifier l'éligibilité d'un étudiant
 */
function verifierEligibiliteEtudiant($etudiant_id) {
    // Vérifier le règlement des frais
    $reglements_sql = "
        SELECT SUM(montant_restant) as solde_restant
        FROM reglements r
        JOIN statuts s ON r.statut_id = s.statut_id
        WHERE r.etudiant_id = ? AND s.code_statut != 'PAYE'
    ";
    
    $reglement = obtenirEnregistrement($reglements_sql, [$etudiant_id]);
    $frais_regles = ($reglement['solde_restant'] <= 0);
    
    // Vérifier la moyenne générale
    $etudiant = obtenirEtudiantComplet($etudiant_id);
    $moyenne_suffisante = ($etudiant['moyenne_generale'] >= 10.0);
    
    // Vérifier les crédits
    $credits_valides = ($etudiant['nombre_credits_valides'] >= $etudiant['nombre_credits_requis']);
    
    $eligible = $frais_regles && $moyenne_suffisante && $credits_valides;
    
    // Mettre à jour le statut d'éligibilité
    $nouveau_statut = $eligible ? 5 : 6; // ELIGIBLE : NON_ELIGIBLE
    mettreAJourEnregistrement('etudiants', 
        ['statut_eligibilite' => $nouveau_statut], 
        'etudiant_id = ?', 
        [$etudiant_id]
    );
    
    return [
        'eligible' => $eligible,
        'frais_regles' => $frais_regles,
        'moyenne_suffisante' => $moyenne_suffisante,
        'credits_valides' => $credits_valides,
        'details' => [
            'solde_restant' => $reglement['solde_restant'],
            'moyenne' => $etudiant['moyenne_generale'],
            'credits' => $etudiant['nombre_credits_valides'] . '/' . $etudiant['nombre_credits_requis']
        ]
    ];
}

/**
 * Calculer la moyenne d'un étudiant
 */
function calculerMoyenneEtudiant($etudiant_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("CALL sp_calculer_moyenne_etudiant(?, @moyenne)");
        $stmt->execute([$etudiant_id]);
        
        $result = $conn->query("SELECT @moyenne as moyenne")->fetch(PDO::FETCH_ASSOC);
        return $result['moyenne'];
    } catch (PDOException $e) {
        error_log("Erreur calcul moyenne: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtenir les statistiques du dashboard
 */
function obtenirStatistiquesDashboard($role_nom) {
    $stats = [];
    
    switch ($role_nom) {
        case 'Administrateur':
            $stats = [
                'total_utilisateurs' => compterEnregistrements('utilisateurs', 'est_actif = 1'),
                'total_etudiants' => compterEnregistrements('etudiants'),
                'total_enseignants' => compterEnregistrements('enseignants'),
                'total_personnel' => compterEnregistrements('personnel_administratif'),
                'rapports_en_cours' => compterEnregistrements('rapports r JOIN statuts s ON r.statut_id = s.statut_id', 's.code_statut IN ("DEPOSE", "EN_VERIFICATION")'),
                'soutenances_ce_mois' => compterEnregistrements('soutenances', 'MONTH(date_prevue) = MONTH(CURDATE()) AND YEAR(date_prevue) = YEAR(CURDATE())')
            ];
            break;
            
        case 'Responsable Scolarité':
            $stats = [
                'total_etudiants' => compterEnregistrements('etudiants'),
                'etudiants_eligibles' => compterEnregistrements('etudiants e JOIN statuts s ON e.statut_eligibilite = s.statut_id', 's.code_statut = "ELIGIBLE"'),
                'notes_a_saisir' => compterEnregistrements('evaluations', 'est_validee = 0'),
                'rapports_deposes' => compterEnregistrements('rapports r JOIN statuts s ON r.statut_id = s.statut_id', 's.code_statut = "DEPOSE"'),
                'moyenne_generale' => obtenirEnregistrement("SELECT AVG(moyenne_generale) as moyenne FROM etudiants WHERE moyenne_generale IS NOT NULL")['moyenne']
            ];
            break;
            
        case 'Chargé Communication':
            $stats = [
                'rapports_a_verifier' => compterEnregistrements('rapports r JOIN statuts s ON r.statut_id = s.statut_id', 's.code_statut = "DEPOSE"'),
                'rapports_en_verification' => compterEnregistrements('rapports r JOIN statuts s ON r.statut_id = s.statut_id', 's.code_statut = "EN_VERIFICATION"'),
                'comptes_rendus_recus' => compterEnregistrements('evaluationsrapport', 'DATE(date_evaluation) = CURDATE()'),
                'validations_en_attente' => compterEnregistrements('rapports r JOIN statuts s ON r.statut_id = s.statut_id', 's.code_statut = "EN_VERIFICATION"')
            ];
            break;
            
        case 'Commission':
            $stats = [
                'rapports_a_evaluer' => compterEnregistrements('rapports r JOIN statuts s ON r.statut_id = s.statut_id', 's.code_statut = "EN_VERIFICATION"'),
                'evaluations_terminees' => compterEnregistrements('evaluationsrapport', 'DATE(date_evaluation) = CURDATE()'),
                'jurys_a_constituer' => compterEnregistrements('rapports r JOIN statuts s ON r.statut_id = s.statut_id', 's.code_statut = "VALIDE" AND r.rapport_id NOT IN (SELECT id_rapport FROM jurys)')
            ];
            break;
            
        case 'Secrétaire':
            $stats = [
                'total_etudiants' => compterEnregistrements('etudiants'),
                'soutenances_programmees' => compterEnregistrements('soutenances s JOIN statuts st ON s.statut_id = st.statut_id', 'st.code_statut = "PROGRAMMEE"'),
                'soutenances_ce_mois' => compterEnregistrements('soutenances', 'MONTH(date_prevue) = MONTH(CURDATE()) AND YEAR(date_prevue) = YEAR(CURDATE())'),
                'salles_disponibles' => compterEnregistrements('salles', 'est_disponible = 1 AND est_actif = 1')
            ];
            break;
            
        case 'Étudiant':
            $etudiant_id = $_SESSION['etudiant_id'] ?? null;
            if ($etudiant_id) {
                $rapport = obtenirEnregistrement("SELECT * FROM rapports WHERE etudiant_id = ?", [$etudiant_id]);
                $stats = [
                    'progression_rapport' => $rapport ? calculerProgressionRapport($rapport['rapport_id']) : 0,
                    'nombre_mots' => $rapport['nombre_mots'] ?? 0,
                    'statut_rapport' => $rapport ? obtenirEnregistrement("SELECT libelle_statut FROM statuts WHERE statut_id = ?", [$rapport['statut_id']])['libelle_statut'] : 'Aucun rapport',
                    'notifications_non_lues' => compterEnregistrements('notifications', 'utilisateur_id = ? AND est_lue = 0', [$_SESSION['utilisateur_id']])
                ];
            }
            break;
    }
    
    return $stats;
}

/**
 * Calculer la progression d'un rapport
 */
function calculerProgressionRapport($rapport_id) {
    $rapport = obtenirEnregistrement("SELECT * FROM rapports WHERE rapport_id = ?", [$rapport_id]);
    
    if (!$rapport) return 0;
    
    $progression = 0;
    
    // Critères de progression
    if (!empty($rapport['titre'])) $progression += 10;
    if (!empty($rapport['resume'])) $progression += 15;
    if (!empty($rapport['introduction_texte'])) $progression += 15;
    if (!empty($rapport['problematique_texte'])) $progression += 15;
    if (!empty($rapport['methodologie_texte'])) $progression += 15;
    if (!empty($rapport['fichier_rapport'])) $progression += 20;
    if ($rapport['nombre_pages'] >= 30) $progression += 10;
    
    return min($progression, 100);
}

// ========================================================================================
// FONCTIONS D'UPLOAD ET FICHIERS
// ========================================================================================

/**
 * Uploader un fichier
 */
function uploaderFichier($fichier, $dossier_destination, $types_autorises = ['pdf', 'doc', 'docx']) {
    if (!isset($fichier['tmp_name']) || empty($fichier['tmp_name'])) {
        return ['success' => false, 'message' => 'Aucun fichier sélectionné'];
    }
    
    $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $types_autorises)) {
        return ['success' => false, 'message' => 'Type de fichier non autorisé'];
    }
    
    $taille_max = 50 * 1024 * 1024; // 50MB
    if ($fichier['size'] > $taille_max) {
        return ['success' => false, 'message' => 'Fichier trop volumineux (max 50MB)'];
    }
    
    $nom_fichier = uniqid() . '_' . time() . '.' . $extension;
    $chemin_complet = $dossier_destination . '/' . $nom_fichier;
    
    if (!is_dir($dossier_destination)) {
        mkdir($dossier_destination, 0755, true);
    }
    
    if (move_uploaded_file($fichier['tmp_name'], $chemin_complet)) {
        return [
            'success' => true,
            'nom_fichier' => $nom_fichier,
            'chemin' => $chemin_complet,
            'taille' => $fichier['size']
        ];
    }
    
    return ['success' => false, 'message' => 'Erreur lors de l\'upload'];
}

// ========================================================================================
// FONCTIONS DE NOTIFICATION ET COMMUNICATION
// ========================================================================================

/**
 * Créer une notification
 */
function creerNotification($utilisateur_id, $type, $titre, $contenu, $lien_action = null, $priorite = 'NORMALE') {
    global $conn;
    
    // Obtenir l'ID de priorité
    $priorite_obj = obtenirEnregistrement("SELECT statut_id FROM statuts WHERE code_statut = ? AND type_statut = 'Priorite'", [$priorite]);
    $priorite_id = $priorite_obj['statut_id'];
    
    $donnees = [
        'utilisateur_id' => $utilisateur_id,
        'type_notification' => $type,
        'titre_notification' => $titre,
        'contenu_notification' => $contenu,
        'lien_action' => $lien_action,
        'priorite_id' => $priorite_id
    ];
    
    return insererEnregistrement('notifications', $donnees);
}

/**
 * Envoyer un email
 */
function envoyerEmail($destinataire, $sujet, $contenu, $copie = null) {
    // Configuration email (à adapter selon votre serveur)
    $headers = [
        'From: noreply@ufhb.edu.ci',
        'Reply-To: noreply@ufhb.edu.ci',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    if ($copie) {
        $headers[] = "Cc: $copie";
    }
    
    return mail($destinataire, $sujet, $contenu, implode("\r\n", $headers));
}

/**
 * Marquer une notification comme lue
 */
function marquerNotificationLue($notification_id, $utilisateur_id) {
    return mettreAJourEnregistrement(
        'notifications',
        ['est_lue' => 1, 'date_lecture' => date('Y-m-d H:i:s')],
        'notification_id = ? AND utilisateur_id = ?',
        [$notification_id, $utilisateur_id]
    );
}

// ========================================================================================
// FONCTIONS D'AUDIT ET LOGS
// ========================================================================================

/**
 * Ajouter un log d'audit
 */
function ajouterLogAudit($utilisateur_id, $type_action, $table_cible, $enregistrement_id, $anciennes_valeurs = null, $nouvelles_valeurs = null, $commentaire = null) {
    $donnees = [
        'utilisateur_id' => $utilisateur_id,
        'type_action' => $type_action,
        'table_cible' => $table_cible,
        'enregistrement_id' => $enregistrement_id,
        'anciennes_valeurs' => $anciennes_valeurs ? json_encode($anciennes_valeurs) : null,
        'nouvelles_valeurs' => $nouvelles_valeurs ? json_encode($nouvelles_valeurs) : null,
        'adresse_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'commentaire' => $commentaire
    ];
    
    return insererEnregistrement('logs_audit', $donnees);
}

// ========================================================================================
// FONCTIONS UTILITAIRES
// ========================================================================================

/**
 * Formater une date
 */
function formaterDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Formater une date avec heure
 */
function formaterDateHeure($date, $format = 'd/m/Y H:i') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Générer un numéro unique
 */
function genererNumeroUnique($prefixe, $table, $colonne, $longueur = 8) {
    do {
        $numero = $prefixe . str_pad(random_int(1, pow(10, $longueur) - 1), $longueur, '0', STR_PAD_LEFT);
        $existe = obtenirEnregistrement("SELECT $colonne FROM $table WHERE $colonne = ?", [$numero]);
    } while ($existe);
    
    return $numero;
}

/**
 * Nettoyer et valider les données d'entrée
 */
function nettoyerEntree($data) {
    if (is_array($data)) {
        return array_map('nettoyerEntree', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valider un email
 */
function validerEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valider un numéro de téléphone
 */
function validerTelephone($telephone) {
    return preg_match('/^[0-9+\-\s\(\)]{8,15}$/', $telephone);
}

/**
 * Obtenir les options pour un select
 */
function obtenirOptionsSelect($table, $colonne_valeur, $colonne_libelle, $condition = '', $params = []) {
    $sql = "SELECT $colonne_valeur as valeur, $colonne_libelle as libelle FROM $table";
    if (!empty($condition)) {
        $sql .= " WHERE $condition";
    }
    $sql .= " ORDER BY $colonne_libelle";
    
    return obtenirEnregistrements($sql, $params);
}

/**
 * Obtenir les statuts par type
 */
function obtenirStatutsParType($type_statut) {
    return obtenirOptionsSelect('statuts', 'statut_id', 'libelle_statut', 'type_statut = ? AND est_actif = 1', [$type_statut]);
}

/**
 * Pagination
 */
function genererPagination($page_actuelle, $total_enregistrements, $par_page, $url_base) {
    $total_pages = ceil($total_enregistrements / $par_page);
    
    if ($total_pages <= 1) return '';
    
    $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';
    
    // Bouton précédent
    if ($page_actuelle > 1) {
        $prev = $page_actuelle - 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$url_base}?page={$prev}'>Précédent</a></li>";
    }
    
    // Numéros de page
    $debut = max(1, $page_actuelle - 2);
    $fin = min($total_pages, $page_actuelle + 2);
    
    for ($i = $debut; $i <= $fin; $i++) {
        $active = ($i == $page_actuelle) ? 'active' : '';
        $html .= "<li class='page-item {$active}'><a class='page-link' href='{$url_base}?page={$i}'>{$i}</a></li>";
    }
    
    // Bouton suivant
    if ($page_actuelle < $total_pages) {
        $next = $page_actuelle + 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$url_base}?page={$next}'>Suivant</a></li>";
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Convertir la taille de fichier en format lisible
 */
function formaterTailleFichier($taille) {
    $unites = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($taille >= 1024 && $i < count($unites) - 1) {
        $taille /= 1024;
        $i++;
    }
    
    return round($taille, 2) . ' ' . $unites[$i];
}

/**
 * Générer un badge de statut
 */
function genererBadgeStatut($statut, $couleur = null) {
    $couleur = $couleur ?: '#6c757d';
    return "<span class='badge' style='background-color: {$couleur}; color: white;'>{$statut}</span>";
}

/**
 * Vérifier les permissions d'accès aux fichiers
 */
function verifierAccesFichier($chemin_fichier, $utilisateur_id) {
    // Logique de vérification des permissions
    // À implémenter selon vos règles métier
    return true;
}

/**
 * Obtenir l'année académique actuelle
 */
function obtenirAnneeAcademique() {
    $annee_actuelle = date('Y');
    $mois_actuel = date('n');
    
    // L'année académique commence en octobre
    if ($mois_actuel >= 10) {
        return $annee_actuelle . '-' . ($annee_actuelle + 1);
    } else {
        return ($annee_actuelle - 1) . '-' . $annee_actuelle;
    }
}

// ========================================================================================
// FONCTIONS DE GÉNÉRATION DE RAPPORTS
// ========================================================================================

/**
 * Exporter des données en CSV
 */
function exporterCSV($donnees, $nom_fichier, $en_tetes = []) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nom_fichier . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($en_tetes)) {
        fputcsv($output, $en_tetes);
    }
    
    foreach ($donnees as $ligne) {
        fputcsv($output, $ligne);
    }
    
    fclose($output);
    exit;
}

/**
 * Générer un rapport d'activité
 */
function genererRapportActivite($utilisateur_id, $date_debut, $date_fin) {
    $sql = "
        SELECT la.*, u.email
        FROM logs_audit la
        LEFT JOIN utilisateurs u ON la.utilisateur_id = u.utilisateur_id
        WHERE la.utilisateur_id = ? 
        AND la.date_action BETWEEN ? AND ?
        ORDER BY la.date_action DESC
    ";
    
    return obtenirEnregistrements($sql, [$utilisateur_id, $date_debut, $date_fin]);
}

// Définir la constante pour indiquer que les fonctions sont chargées
define('FUNCTIONS_LOADED', true);

?>