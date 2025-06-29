<?php
/**
 * Page de déconnexion
 * Système de Validation Académique - UFHB Cocody
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Démarrer la session
SessionManager::start();

// Vérifier si l'utilisateur est connecté
if (!SessionManager::isLoggedIn()) {
    redirectTo('login.php');
}

// Logger l'action de déconnexion
logAction('LOGOUT', 'Déconnexion de l\'utilisateur');

// Détruire la session
SessionManager::destroy();

// Supprimer le cookie "Se souvenir de moi" s'il existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Rediriger vers la page de connexion avec un message
redirectTo('login.php?message=logged_out');
?>