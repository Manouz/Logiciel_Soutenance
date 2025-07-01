<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/functions.php';

// Sécurité : Vérifier si l'utilisateur est admin et si la requête est POST
/*
if (!check_user_role(['admin'])) {
    json_response(false, 'Accès non autorisé.', [], 403);
    exit;
}
*/
if (!is_post_request()) {
    json_response(false, 'Méthode non autorisée.', [], 405);
    exit;
}

$pdo = getPDOConnection();
if (!$pdo) {
    json_response(false, 'Erreur de connexion à la base de données.');
    exit;
}

// Récupération des données
$id_gu = filter_input(INPUT_POST, 'id_gu', FILTER_VALIDATE_INT);
// Les permissions sont attendues sous la forme d'un tableau: $_POST['permissions'][id_trait][nom_permission]
$permissions_post = $_POST['permissions'] ?? [];

if (!$id_gu) {
    json_response(false, 'ID de groupe utilisateur manquant ou invalide.');
    exit;
}

// Valider que $permissions_post est bien un tableau
if (!is_array($permissions_post)) {
    json_response(false, 'Format de données de permissions incorrect.');
    exit;
}

// Liste des clés de permission attendues pour chaque traitement
$permission_keys = [
    'voir_dans_sidebar', 'peut_ajouter', 'peut_modifier', 'peut_supprimer',
    'peut_imprimer', 'peut_exporter', 'peut_importer'
];

try {
    $pdo->beginTransaction();

    // 1. Supprimer toutes les permissions existantes pour ce groupe
    $stmt_delete = $pdo->prepare("DELETE FROM rattacher WHERE id_gu = :id_gu");
    $stmt_delete->bindParam(':id_gu', $id_gu, PDO::PARAM_INT);
    $stmt_delete->execute();

    // 2. Préparer la requête d'insertion pour les nouvelles permissions
    $sql_insert = "INSERT INTO rattacher (id_gu, id_traitement, voir_dans_sidebar, peut_ajouter, peut_modifier, peut_supprimer, peut_imprimer, peut_exporter, peut_importer)
                   VALUES (:id_gu, :id_traitement, :voir_dans_sidebar, :peut_ajouter, :peut_modifier, :peut_supprimer, :peut_imprimer, :peut_exporter, :peut_importer)";
    $stmt_insert = $pdo->prepare($sql_insert);

    // 3. Parcourir les permissions envoyées et les insérer
    // $permissions_post est de la forme [id_trait => [nom_perm => 'on', ...]] si checkbox cochée
    // ou [id_trait => []] si aucune checkbox n'est cochée pour ce traitement.
    // Le JS devrait envoyer les clés même si non cochées (avec une valeur falsy), ou alors il faut ajuster ici.
    // Le JS actuel envoie seulement les 'on' si cochées.
    // On va donc récupérer la liste de tous les traitements pour s'assurer de créer une entrée pour chacun, même si toutes les perms sont à false.

    $stmt_all_traitements = $pdo->query("SELECT id_trait FROM traitement");
    $all_traitements_ids = $stmt_all_traitements->fetchAll(PDO::FETCH_COLUMN);

    foreach ($all_traitements_ids as $id_trait_from_db) {
        $id_trait = (int)$id_trait_from_db; // S'assurer que c'est un entier
        $perms_for_trait = $permissions_post[$id_trait] ?? []; // Permissions envoyées pour ce traitement

        $params = [
            'id_gu' => $id_gu,
            'id_traitement' => $id_trait
        ];

        $has_any_permission = false; // Pour savoir si on doit insérer une ligne

        foreach ($permission_keys as $key) {
            // Si la clé existe dans $perms_for_trait (c'est-à-dire checkbox cochée et envoyée), alors TRUE (1)
            // Sinon, FALSE (0)
            $params[$key] = isset($perms_for_trait[$key]) ? 1 : 0;
            if ($params[$key] == 1) {
                $has_any_permission = true;
            }
        }

        // On insère une ligne dans rattacher seulement si au moins une permission est accordée
        // OU si on veut explicitement une ligne "tout à false" pour chaque traitement pour un groupe.
        // La logique actuelle du JS (gestion_permissions.js) construit le tableau des permissions
        // dynamiquement, donc si un traitement n'a aucune case cochée, il n'apparaîtra pas dans $_POST['permissions'][$id_trait].
        // Pour simplifier, on insère une ligne pour chaque traitement qui a au moins une permission cochée.
        // Si aucune permission n'est cochée pour un traitement, aucune ligne ne sera insérée pour ce id_trait/id_gu.
        // C'est équivalent à "pas de droit".

        // Ajustement: le JS envoie `permissions[ID_TRAIT][NOM_PERM]` qui sera 'on' si coché.
        // Donc, $perms_for_trait[$key] sera 'on'. On convertit en booléen (0 ou 1).

        if ($has_any_permission) { // Ou si on veut toujours une ligne, on retire cette condition
             $stmt_insert->execute($params);
        }
    }

    $pdo->commit();
    json_response(true, 'Permissions sauvegardées avec succès.');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erreur PDO lors de la sauvegarde des permissions : " . $e->getMessage());
    json_response(false, 'Erreur de base de données lors de la sauvegarde des permissions. ' . $e->getMessage());
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erreur générale lors de la sauvegarde des permissions : " . $e->getMessage());
    json_response(false, 'Une erreur inattendue est survenue lors de la sauvegarde des permissions.');
}

?>
