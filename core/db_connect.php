<?php
// Inclure la configuration
require_once __DIR__ . '/../config/config.php';

/**
 * Fonction pour établir une connexion à la base de données.
 * @return PDO|null L'objet PDO en cas de succès, null sinon.
 */
function getPDOConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $GLOBALS['pdo_options']);
        return $pdo;
    } catch (PDOException $e) {
        // En production, logguer l'erreur plutôt que de l'afficher directement
        error_log("Erreur de connexion à la base de données : " . $e->getMessage());
        // Pour le développement, on peut afficher l'erreur
        // die("Erreur de connexion à la base de données : " . $e->getMessage());
        // Il serait préférable d'afficher une page d'erreur générique en production
        // ou de gérer l'erreur d'une manière plus élégante.
        return null;
    }
}

// Exemple d'utilisation (peut être commenté ou supprimé après test)
/*
$pdo = getPDOConnection();
if ($pdo) {
    echo "Connexion à la base de données établie avec succès.";
} else {
    echo "Échec de la connexion à la base de données.";
}
*/
?>
