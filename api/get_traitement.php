<?php
/**
 * API pour récupérer tous les traitements
 * Fichier: api/get_traitements.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Inclure les fichiers nécessaires
require_once '../classes/Traitement.php';

try {
    // Créer une instance de Traitement
    $traitement = new Traitement();
    
    // Lire tous les traitements
    $stmt = $traitement->read();
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        $traitements_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $traitement_item = array(
                'id_trait' => $row['id_trait'],
                'lib_trait' => $row['lib_trait']
            );
            
            array_push($traitements_arr, $traitement_item);
        }
        
        echo json_encode([
            'success' => true,
            'count' => $num,
            'traitements' => $traitements_arr
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'count' => 0,
            'traitements' => [],
            'message' => 'Aucun traitement trouvé'
        ]);
    }

} catch (Exception $e) {
    error_log("Erreur API get_traitements: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des traitements'
    ]);
}
?>