<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/functions.php';

// Sécurité : Vérifier si l'utilisateur est admin (à implémenter correctement)
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
    // Si un ID est fourni, retourner un seul traitement (pour la modification)
    if (isset($_GET['id'])) {
        $id_trait = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id_trait) {
            json_response(false, 'ID de traitement invalide.');
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM traitement WHERE id_trait = :id_trait");
        $stmt->bindParam(':id_trait', $id_trait, PDO::PARAM_INT);
        $stmt->execute();
        $traitement = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($traitement) {
            // Assurer que les valeurs numériques sont bien des nombres et booléen pour est_actif
            $traitement['id_trait'] = (int)$traitement['id_trait'];
            $traitement['ordre_affichage'] = (int)$traitement['ordre_affichage'];
            $traitement['est_actif'] = (bool)$traitement['est_actif'];
            echo json_encode($traitement); // Retourne directement l'objet traitement
        } else {
            json_response(false, 'Traitement non trouvé.');
        }
    } else {
        // Sinon, retourner tous les traitements
        $stmt = $pdo->query("SELECT * FROM traitement ORDER BY ordre_affichage ASC, lib_trait ASC");
        $traitements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // S'assurer que les types sont corrects pour le JS
        $traitements_typed = array_map(function($trait) {
            $trait['id_trait'] = (int)$trait['id_trait'];
            $trait['ordre_affichage'] = (int)$trait['ordre_affichage'];
            $trait['est_actif'] = (bool)$trait['est_actif']; // ou (int) si vous préférez 0/1
            return $trait;
        }, $traitements);

        echo json_encode($traitements_typed);
    }

} catch (PDOException $e) {
    error_log("Erreur PDO : " . $e->getMessage()); // Log l'erreur côté serveur
    json_response(false, 'Erreur lors de la récupération des traitements. Veuillez consulter les logs du serveur.');
} catch (Exception $e) {
    error_log("Erreur : " . $e->getMessage());
    json_response(false, 'Une erreur inattendue est survenue.');
}

?>
