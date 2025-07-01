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

try {
    $stmt = $pdo->query("SELECT id_gu, lib_gu FROM groupe_utilisateur ORDER BY lib_gu ASC");
    $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // S'assurer que id_gu est un entier si nécessaire pour JS, bien que pour un select ce ne soit pas critique.
    $groupes_typed = array_map(function($groupe) {
        $groupe['id_gu'] = (int)$groupe['id_gu'];
        return $groupe;
    }, $groupes);

    echo json_encode($groupes_typed);

} catch (PDOException $e) {
    error_log("Erreur PDO lors de la récupération des groupes : " . $e->getMessage());
    json_response(false, 'Erreur lors de la récupération des groupes d\'utilisateurs.');
} catch (Exception $e) {
    error_log("Erreur générale : " . $e->getMessage());
    json_response(false, 'Une erreur inattendue est survenue.');
}

?>
