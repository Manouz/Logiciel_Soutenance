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

// Récupération et validation de l'ID du traitement à supprimer
$id_trait = filter_input(INPUT_POST, 'id_trait', FILTER_VALIDATE_INT);

if (!$id_trait) {
    json_response(false, 'ID de traitement invalide ou manquant.');
    exit;
}

try {
    // Avant de supprimer, on pourrait vérifier si le traitement existe,
    // mais DELETE ne lèvera pas d'erreur si l'ID n'existe pas, il affectera juste 0 lignes.
    // Cependant, cela peut être utile pour un message plus précis.
    $stmt_check = $pdo->prepare("SELECT id_trait FROM traitement WHERE id_trait = :id_trait");
    $stmt_check->bindParam(':id_trait', $id_trait, PDO::PARAM_INT);
    $stmt_check->execute();
    if ($stmt_check->fetchColumn() === false) {
        json_response(false, "Le traitement avec l'ID $id_trait n'existe pas et ne peut être supprimé.");
        exit;
    }

    // La suppression des entrées correspondantes dans la table `rattacher`
    // devrait être gérée par la contrainte FOREIGN KEY `ON DELETE CASCADE`
    // si elle a été définie lors de la création de la table `rattacher`.
    // Si ce n'est pas le cas, il faudrait d'abord supprimer manuellement les entrées dans `rattacher`.
    // Exemple de suppression manuelle (si pas de CASCADE):
    /*
    $stmt_rattacher = $pdo->prepare("DELETE FROM rattacher WHERE id_traitement = :id_traitement");
    $stmt_rattacher->bindParam(':id_traitement', $id_trait, PDO::PARAM_INT);
    $stmt_rattacher->execute();
    */

    $sql = "DELETE FROM traitement WHERE id_trait = :id_trait";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_trait', $id_trait, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            json_response(true, 'Traitement supprimé avec succès.');
        } else {
            // Cela ne devrait pas arriver si la vérification précédente a été faite.
            json_response(false, 'Aucun traitement trouvé avec cet ID pour la suppression (ou déjà supprimé).');
        }
    } else {
        json_response(false, 'Erreur lors de la suppression du traitement.');
    }

} catch (PDOException $e) {
    error_log("Erreur PDO lors de la suppression : " . $e->getMessage());
    // Si une contrainte de clé étrangère empêche la suppression (par exemple, si ON DELETE CASCADE n'est pas défini
    // et qu'il y a des références dans `rattacher` ou ailleurs).
    if ($e->getCode() == '23000') {
        json_response(false, 'Impossible de supprimer ce traitement car il est référencé ailleurs (par exemple, dans des permissions de groupe). Veuillez d\'abord supprimer ces références.');
    } else {
        json_response(false, 'Erreur de base de données lors de la suppression. Message: ' . $e->getMessage());
    }
} catch (Exception $e) {
    error_log("Erreur générale lors de la suppression : " . $e->getMessage());
    json_response(false, 'Une erreur inattendue est survenue lors de la suppression.');
}

?>
