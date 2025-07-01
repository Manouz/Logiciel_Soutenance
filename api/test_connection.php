<?php
// Inclure la configuration de la base de données
require_once '../config/database.php';

// Headers pour CORS et JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Test de connexion
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Test de la table traitement
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM traitement");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Connexion à la base de données réussie',
        'database_info' => [
            'host' => 'localhost',
            'database' => 'validation_soutenance',
            'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'client_version' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION)
        ],
        'table_info' => [
            'traitement_count' => $result['count']
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données',
        'error' => $e->getMessage(),
        'database_info' => [
            'host' => 'localhost',
            'database' => 'validation_soutenance'
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur générale',
        'error' => $e->getMessage()
    ]);
}
?> 