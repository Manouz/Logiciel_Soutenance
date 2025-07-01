<?php
// Configuration de la base de données
define('DB_HOST', 'localhost'); // ou votre hôte
define('DB_NAME', 'votre_base_de_donnees'); // à remplacer
define('DB_USER', 'votre_utilisateur_db'); // à remplacer
define('DB_PASS', 'votre_mot_de_passe_db'); // à remplacer
define('DB_CHARSET', 'utf8mb4');

// Options PDO
$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Configuration du site (optionnel)
define('SITE_URL', 'http://localhost/votre_projet/'); // à remplacer
define('SITE_NAME', 'Gestion des Menus');

// Activer l'affichage des erreurs pour le développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
