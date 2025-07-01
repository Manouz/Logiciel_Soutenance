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

// Récupération et validation des données du formulaire
$id_trait = filter_input(INPUT_POST, 'id_trait', FILTER_VALIDATE_INT); // 'id_trait' vient du champ hidden
$lib_trait = sanitize_input($_POST['lib_trait'] ?? null);
$description = sanitize_input($_POST['description'] ?? null);
$url_traitement = sanitize_input($_POST['url_traitement'] ?? null);
$icone = sanitize_input($_POST['icone'] ?? 'fas fa-file');
$ordre_affichage = filter_input(INPUT_POST, 'ordre_affichage', FILTER_VALIDATE_INT);
$est_actif = isset($_POST['est_actif']) ? (int)$_POST['est_actif'] : 0;
if ($est_actif !== 0 && $est_actif !== 1) {
    $est_actif = 0;
}


// Validation
if (!$id_trait) {
    json_response(false, 'ID de traitement invalide ou manquant.');
    exit;
}
if (empty($lib_trait)) {
    json_response(false, 'Le libellé du traitement est requis.');
    exit;
}
if ($ordre_affichage === false || $ordre_affichage < 0) {
    $ordre_affichage = 0; // Valeur par défaut
}


try {
    // Vérifier si le traitement existe avant de le modifier
    $stmt_check = $pdo->prepare("SELECT id_trait FROM traitement WHERE id_trait = :id_trait");
    $stmt_check->bindParam(':id_trait', $id_trait, PDO::PARAM_INT);
    $stmt_check->execute();
    if ($stmt_check->fetchColumn() === false) {
        json_response(false, "Le traitement avec l'ID $id_trait n'existe pas.");
        exit;
    }

    $sql = "UPDATE traitement SET
                lib_trait = :lib_trait,
                description = :description,
                url_traitement = :url_traitement,
                icone = :icone,
                ordre_affichage = :ordre_affichage,
                est_actif = :est_actif
            WHERE id_trait = :id_trait";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':id_trait', $id_trait, PDO::PARAM_INT);
    $stmt->bindParam(':lib_trait', $lib_trait, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':url_traitement', $url_traitement, PDO::PARAM_STR);
    $stmt->bindParam(':icone', $icone, PDO::PARAM_STR);
    $stmt->bindParam(':ordre_affichage', $ordre_affichage, PDO::PARAM_INT);
    $stmt->bindParam(':est_actif', $est_actif, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            json_response(true, 'Traitement modifié avec succès.');
        } else {
            // Aucune ligne affectée, soit les données étaient identiques, soit l'ID n'existait pas (déjà vérifié)
            json_response(true, 'Aucune modification détectée pour le traitement.');
        }
    } else {
        json_response(false, 'Erreur lors de la modification du traitement.');
    }

} catch (PDOException $e) {
    error_log("Erreur PDO lors de la modification : " . $e->getMessage());
    if ($e->getCode() == '23000') {
        json_response(false, 'Un traitement avec ce libellé existe peut-être déjà (autre que celui-ci).');
    } else {
        json_response(false, 'Erreur de base de données lors de la modification. Message: ' . $e->getMessage());
    }
} catch (Exception $e) {
    error_log("Erreur générale lors de la modification : " . $e->getMessage());
    json_response(false, 'Une erreur inattendue est survenue lors de la modification.');
}

?>
