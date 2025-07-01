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
$lib_trait = sanitize_input($_POST['lib_trait'] ?? null);
$description = sanitize_input($_POST['description'] ?? null); // Peut être vide
$url_traitement = sanitize_input($_POST['url_traitement'] ?? null); // Peut être vide
$icone = sanitize_input($_POST['icone'] ?? 'fas fa-file');
$ordre_affichage = filter_input(INPUT_POST, 'ordre_affichage', FILTER_VALIDATE_INT);
// Pour la checkbox 'est_actif', si elle n'est pas cochée, elle n'est pas envoyée.
// Le JS s'assure d'envoyer '0' ou '1'. Sinon, on peut faire :
$est_actif = isset($_POST['est_actif']) ? (int)$_POST['est_actif'] : 0;
if ($est_actif !== 0 && $est_actif !== 1) { // S'assurer que c'est bien 0 ou 1
    $est_actif = 0;
}


// Validation simple
if (empty($lib_trait)) {
    json_response(false, 'Le libellé du traitement est requis.');
    exit;
}
if ($ordre_affichage === false || $ordre_affichage < 0) { // false si non entier ou invalide
    $ordre_affichage = 0; // Valeur par défaut si invalide
}


try {
    $sql = "INSERT INTO traitement (lib_trait, description, url_traitement, icone, ordre_affichage, est_actif, date_creation)
            VALUES (:lib_trait, :description, :url_traitement, :icone, :ordre_affichage, :est_actif, CURRENT_TIMESTAMP)";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':lib_trait', $lib_trait, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':url_traitement', $url_traitement, PDO::PARAM_STR);
    $stmt->bindParam(':icone', $icone, PDO::PARAM_STR);
    $stmt->bindParam(':ordre_affichage', $ordre_affichage, PDO::PARAM_INT);
    $stmt->bindParam(':est_actif', $est_actif, PDO::PARAM_INT); // Stocké comme TINYINT(1)

    if ($stmt->execute()) {
        $new_id = $pdo->lastInsertId();
        json_response(true, 'Traitement ajouté avec succès.', ['id_trait' => $new_id]);
    } else {
        json_response(false, 'Erreur lors de l\'ajout du traitement.');
    }

} catch (PDOException $e) {
    error_log("Erreur PDO lors de l'ajout : " . $e->getMessage());
    // Vérifier les erreurs de contrainte unique, etc.
    if ($e->getCode() == '23000') { // Code d'erreur SQL pour violation de contrainte (ex: libellé unique)
        json_response(false, 'Un traitement avec ce libellé existe peut-être déjà.');
    } else {
        json_response(false, 'Erreur de base de données lors de l\'ajout du traitement. Message: ' . $e->getMessage());
    }
} catch (Exception $e) {
    error_log("Erreur générale lors de l'ajout : " . $e->getMessage());
    json_response(false, 'Une erreur inattendue est survenue lors de l\'ajout.');
}

?>
