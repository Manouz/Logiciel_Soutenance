<?php
// Inclure la configuration de la base de données
require_once '../config/database.php';

// Headers pour CORS et JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Vérifier que c'est une requête GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // Connexion à la base de données
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Récupérer tous les traitements
    $stmt = $pdo->prepare("SELECT id_trait, lib_trait FROM traitement ORDER BY id_trait ASC");
    $stmt->execute();
    
    $traitements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retourner la réponse de succès
    echo json_encode([
        'success' => true,
        'message' => 'Traitements récupérés avec succès',
        'traitements' => $traitements,
        'count' => count($traitements)
    ]);
    
} catch (PDOException $e) {
    // Erreur de base de données
    error_log("Erreur PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // Erreur générale
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 