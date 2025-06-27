<?php
/**
 * Fonctions utilitaires
 * Fichier: includes/functions.php
 */

/**
 * Fonction de redirection sécurisée
 * 
 * @param string $url URL de destination
 */
if (!function_exists('redirectTo')) {
    function redirectTo($url) {
        // Vérifier si les en-têtes n'ont pas encore été envoyés
        if (!headers_sent()) {
            header('Location: ' . $url);
            exit();
        } else {
            // Si les en-têtes ont été envoyés, utiliser JavaScript
            echo "<script>window.location.href = '" . addslashes($url) . "';</script>";
            exit();
        }
    }
}

/**
 * Fonction pour formater les dates - uniquement si elle n'existe pas déjà
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd/m/Y H:i') {
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        
        try {
            $dateObj = new DateTime($date);
            return $dateObj->format($format);
        } catch (Exception $e) {
            return $date;
        }
    }
}

/**
 * Fonction pour calculer le temps écoulé
 */
if (!function_exists('timeAgo')) {
    function timeAgo($date) {
        if (empty($date)) return '-';
        
        try {
            $time = time() - strtotime($date);
            
            if ($time < 60) return 'à l\'instant';
            if ($time < 3600) return floor($time/60) . ' min';
            if ($time < 86400) return floor($time/3600) . ' h';
            if ($time < 2592000) return floor($time/86400) . ' j';
            if ($time < 31536000) return floor($time/2592000) . ' mois';
            
            return floor($time/31536000) . ' an' . (floor($time/31536000) > 1 ? 's' : '');
        } catch (Exception $e) {
            return $date;
        }
    }
}

/**
 * Fonctions utilitaires pour les activités (pour le dashboard admin)
 */
if (!function_exists('getActivityIcon')) {
    function getActivityIcon($action) {
        $icons = [
            'CREATE' => 'plus-circle',
            'UPDATE' => 'edit',
            'DELETE' => 'trash',
            'LOGIN' => 'sign-in-alt',
            'LOGOUT' => 'sign-out-alt',
            'BLOCK' => 'lock',
            'UNBLOCK' => 'unlock',
            'ERROR' => 'exclamation-triangle',
            'WARNING' => 'exclamation-circle'
        ];
        return $icons[$action] ?? 'info-circle';
    }
}

if (!function_exists('getActivityIconClass')) {
    function getActivityIconClass($action) {
        $classes = [
            'CREATE' => 'success',
            'UPDATE' => 'primary',
            'DELETE' => 'danger',
            'LOGIN' => 'info',
            'LOGOUT' => 'secondary',
            'BLOCK' => 'warning',
            'UNBLOCK' => 'success',
            'ERROR' => 'danger',
            'WARNING' => 'warning'
        ];
        return $classes[$action] ?? 'info';
    }
}

if (!function_exists('getActivityText')) {
    function getActivityText($action, $table) {
        $actions = [
            'CREATE' => 'a créé un enregistrement dans',
            'UPDATE' => 'a modifié un enregistrement dans',
            'DELETE' => 'a supprimé un enregistrement de',
            'LOGIN' => 's\'est connecté au système',
            'LOGOUT' => 's\'est déconnecté du système',
            'BLOCK' => 'a bloqué un utilisateur',
            'UNBLOCK' => 'a débloqué un utilisateur'
        ];
        
        $text = $actions[$action] ?? 'a effectué une action sur';
        return in_array($action, ['LOGIN', 'LOGOUT', 'BLOCK', 'UNBLOCK']) ? $text : $text . ' ' . $table;
    }
}
?>