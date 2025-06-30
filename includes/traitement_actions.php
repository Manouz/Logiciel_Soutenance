<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../classes/Traitement.php';

$database = new Database();
$db = $database->getConnection();
$traitement = new Traitement($db);

$response = array();

if(isset($_GET['action']) && $_GET['action'] == 'read') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $par_page = 5;
    
    $stmt = $traitement->lire($page, $par_page);
    $num = $stmt->rowCount();
    $total = $traitement->getCount();
    
    if($num > 0) {
        $traitements_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $traitement_item = array(
                "id_trait" => $id_trait,
                "lib_trait" => $lib_trait
            );
            array_push($traitements_arr, $traitement_item);
        }
        $response = array(
            "success" => true,
            "data" => $traitements_arr,
            "total" => $total,
            "page" => $page,
            "par_page" => $par_page,
            "total_pages" => ceil($total / $par_page)
        );
    } else {
        $response = array(
            "success" => true,
            "data" => array(),
            "total" => 0,
            "message" => "Aucun traitement trouvé."
        );
    }
} elseif(isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'create':
            $traitement->lib_trait = $_POST['lib_trait'];
            if($traitement->creer()) {
                $response = array(
                    "success" => true,
                    "message" => "Traitement créé avec succès.",
                    "id" => $traitement->id_trait
                );
            } else {
                $response = array(
                    "success" => false,
                    "message" => "Impossible de créer le traitement."
                );
            }
            break;

        case 'update':
            $traitement->id_trait = $_POST['id_trait'];
            $traitement->lib_trait = $_POST['lib_trait'];
            if($traitement->mettreAJour()) {
                $response = array(
                    "success" => true,
                    "message" => "Traitement mis à jour avec succès."
                );
            } else {
                $response = array(
                    "success" => false,
                    "message" => "Impossible de mettre à jour le traitement."
                );
            }
            break;

        case 'delete':
            $traitement->id_trait = $_POST['id_trait'];
            if($traitement->supprimer()) {
                $response = array(
                    "success" => true,
                    "message" => "Traitement supprimé avec succès."
                );
            } else {
                $response = array(
                    "success" => false,
                    "message" => "Impossible de supprimer le traitement."
                );
            }
            break;

        case 'delete_multiple':
            $ids = explode(',', $_POST['ids']);
            $ids = array_map('intval', $ids);
            if($traitement->supprimerMultiple($ids)) {
                $response = array(
                    "success" => true,
                    "message" => "Traitements supprimés avec succès."
                );
            } else {
                $response = array(
                    "success" => false,
                    "message" => "Impossible de supprimer les traitements."
                );
            }
            break;
    }
}

echo json_encode($response);
?>
