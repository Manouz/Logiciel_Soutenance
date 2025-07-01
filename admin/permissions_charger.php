<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/functions.php';

// Sécurité : Vérifier si l'utilisateur est admin
/*
if (!check_user_role(['admin'])) {
    json_response(false, 'Accès non autorisé.', [], 403);
    exit;
}
*/

$pdo = getPDOConnection();

if (!$pdo) {
    json_response(false, 'Erreur de connexion à la base de données.');
    exit;
}

header('Content-Type: application/json');

$id_gu = filter_input(INPUT_GET, 'id_gu', FILTER_VALIDATE_INT);

if (!$id_gu) {
    json_response(false, 'ID de groupe utilisateur manquant ou invalide.');
    exit;
}

try {
    // 1. Récupérer tous les traitements actifs (ou tous les traitements, selon la logique métier)
    // On prend tous les traitements pour pouvoir attribuer/retirer des droits même sur des traitements inactifs globalement.
    // Le statut 'est_actif' du traitement lui-même est une autre couche de contrôle.
    $stmt_traitements = $pdo->query("SELECT id_trait, lib_trait FROM traitement ORDER BY ordre_affichage ASC, lib_trait ASC");
    $traitements = $stmt_traitements->fetchAll(PDO::FETCH_ASSOC);

    // 2. Récupérer les permissions existantes pour ce groupe
    $stmt_permissions = $pdo->prepare("SELECT * FROM rattacher WHERE id_gu = :id_gu");
    $stmt_permissions->bindParam(':id_gu', $id_gu, PDO::PARAM_INT);
    $stmt_permissions->execute();
    $permissions_raw = $stmt_permissions->fetchAll(PDO::FETCH_ASSOC);

    // Transformer les permissions en un format plus facile à utiliser côté client: [id_trait => {permissions}]
    $permissions_map = [];
    foreach ($permissions_raw as $perm) {
        $id_traitement_key = (int)$perm['id_traitement'];
        $permissions_map[$id_traitement_key] = [
            'voir_dans_sidebar' => (bool)$perm['voir_dans_sidebar'],
            'peut_ajouter'      => (bool)$perm['peut_ajouter'],
            'peut_modifier'     => (bool)$perm['peut_modifier'],
            'peut_supprimer'    => (bool)$perm['peut_supprimer'],
            'peut_imprimer'     => (bool)$perm['peut_imprimer'],
            'peut_exporter'     => (bool)$perm['peut_exporter'],
            'peut_importer'     => (bool)$perm['peut_importer'],
        ];
    }

    // S'assurer que id_trait est un entier pour les traitements
    $traitements_typed = array_map(function($trait) {
        $trait['id_trait'] = (int)$trait['id_trait'];
        return $trait;
    }, $traitements);


    echo json_encode([
        'traitements' => $traitements_typed,
        'permissions' => $permissions_map
    ]);

} catch (PDOException $e) {
    error_log("Erreur PDO lors du chargement des permissions : " . $e->getMessage());
    json_response(false, 'Erreur lors du chargement des permissions pour le groupe.');
} catch (Exception $e) {
    error_log("Erreur générale : " . $e->getMessage());
    json_response(false, 'Une erreur inattendue est survenue.');
}

?>
