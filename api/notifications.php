<?php
/**
 * API des notifications en temps réel
 * Système de Validation Académique - UFHB Cocody
 */

require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Headers pour API JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Gérer les requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Démarrer la session
SessionManager::start();

// Vérifier l'authentification
if (!SessionManager::isLoggedIn()) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$userId = SessionManager::getUserId();
$userRole = SessionManager::getUserRole();
$action = $_GET['action'] ?? '';

try {
    $db = Database::getInstance();
    
    switch ($action) {
        case 'get':
            getNotifications($db, $userId, $userRole);
            break;
            
        case 'mark_read':
            markAsRead($db, $userId);
            break;
            
        case 'mark_all_read':
            markAllAsRead($db, $userId);
            break;
            
        case 'count':
            getUnreadCount($db, $userId);
            break;
            
        default:
            jsonResponse(['error' => 'Action non supportée'], 400);
    }
    
} catch (Exception $e) {
    error_log("Erreur API notifications: " . $e->getMessage());
    jsonResponse(['error' => 'Erreur serveur'], 500);
}

/**
 * Récupérer les notifications de l'utilisateur
 */
function getNotifications($db, $userId, $userRole) {
    $limit = $_GET['limit'] ?? 20;
    $offset = $_GET['offset'] ?? 0;
    
    // Générer des notifications en temps réel basées sur les activités
    $notifications = generateRealTimeNotifications($db, $userId, $userRole);
    
    // Récupérer aussi les notifications stockées en base
    $sql = "SELECT 
                notification_id,
                type_notification,
                titre_notification,
                contenu_notification,
                lien_action,
                est_lue,
                date_creation,
                date_lecture
            FROM notifications 
            WHERE utilisateur_id = ? 
            ORDER BY date_creation DESC 
            LIMIT ? OFFSET ?";
    
    $storedNotifications = $db->fetchAll($sql, [$userId, $limit, $offset]);
    
    // Fusionner les notifications
    $allNotifications = array_merge($notifications, $storedNotifications);
    
    // Trier par date
    usort($allNotifications, function($a, $b) {
        $dateA = strtotime($a['date_creation'] ?? $a['time']);
        $dateB = strtotime($b['date_creation'] ?? $b['time']);
        return $dateB - $dateA;
    });
    
    // Limiter le résultat
    $allNotifications = array_slice($allNotifications, 0, $limit);
    
    jsonResponse([
        'notifications' => $allNotifications,
        'total' => count($allNotifications),
        'unread_count' => getUnreadCountValue($db, $userId)
    ]);
}

/**
 * Générer des notifications en temps réel
 */
function generateRealTimeNotifications($db, $userId, $userRole) {
    $notifications = [];
    $now = date('Y-m-d H:i:s');
    
    if ($userRole === 'Responsable Scolarité') {
        // Notifications pour responsable scolarité
        
        // Nouveaux étudiants inscrits aujourd'hui
        $nouveauxEtudiants = $db->fetchAll("
            SELECT COUNT(*) as count, MAX(e.date_creation) as derniere_inscription
            FROM etudiants e 
            WHERE DATE(e.date_creation) = CURDATE()
        ");
        
        if ($nouveauxEtudiants[0]['count'] > 0) {
            $notifications[] = [
                'id' => 'new_students_' . date('Y-m-d'),
                'type' => 'info',
                'title' => 'Nouveaux étudiants',
                'message' => $nouveauxEtudiants[0]['count'] . ' nouveau(x) étudiant(s) inscrit(s) aujourd\'hui',
                'time' => $nouveauxEtudiants[0]['derniere_inscription'],
                'icon' => 'fas fa-user-plus',
                'read' => false,
                'action_url' => 'etudiants/gestion.php'
            ];
        }
        
        // Rapports déposés récemment
        $rapportsRecents = $db->fetchAll("
            SELECT r.rapport_id, r.titre, ip.nom, ip.prenoms, r.date_depot
            FROM rapports r
            JOIN etudiants e ON r.etudiant_id = e.etudiant_id
            JOIN utilisateurs u ON e.utilisateur_id = u.utilisateur_id  
            JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
            WHERE r.statut_id = 9 AND DATE(r.date_depot) >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
            ORDER BY r.date_depot DESC
            LIMIT 5
        ");
        
        foreach ($rapportsRecents as $rapport) {
            $notifications[] = [
                'id' => 'rapport_' . $rapport['rapport_id'],
                'type' => 'success',
                'title' => 'Nouveau rapport déposé',
                'message' => $rapport['nom'] . ' ' . $rapport['prenoms'] . ' a déposé son rapport: ' . truncateText($rapport['titre'], 50),
                'time' => $rapport['date_depot'],
                'icon' => 'fas fa-file-upload',
                'read' => false,
                'action_url' => 'rapports/suivi.php?id=' . $rapport['rapport_id']
            ];
        }
        
        // Étudiants non éligibles
        $nonEligibles = $db->fetchAll("
            SELECT COUNT(*) as count
            FROM etudiants 
            WHERE statut_eligibilite != 5
        ");
        
        if ($nonEligibles[0]['count'] > 0) {
            $notifications[] = [
                'id' => 'non_eligible_alert',
                'type' => 'warning',
                'title' => 'Étudiants non éligibles',
                'message' => $nonEligibles[0]['count'] . ' étudiant(s) ne sont pas éligibles à la soutenance',
                'time' => $now,
                'icon' => 'fas fa-exclamation-triangle',
                'read' => false,
                'action_url' => 'etudiants/eligibilite.php'
            ];
        }
        
        // Notes en attente de validation
        $notesAttente = $db->fetchAll("
            SELECT COUNT(*) as count
            FROM evaluations 
            WHERE est_validee = 0 AND DATE(date_creation) >= DATE_SUB(CURDATE(), INTERVAL 3 DAYS)
        ");
        
        if ($notesAttente[0]['count'] > 0) {
            $notifications[] = [
                'id' => 'notes_pending',
                'type' => 'info',
                'title' => 'Notes en attente',
                'message' => $notesAttente[0]['count'] . ' note(s) en attente de validation',
                'time' => $now,
                'icon' => 'fas fa-clock',
                'read' => false,
                'action_url' => 'notes/validation.php'
            ];
        }
    }
    
    // Notifications système communes
    
    // Maintenance programmée (exemple)
    $maintenanceProgrammee = false; // Vous pouvez définir cela depuis la configuration
    if ($maintenanceProgrammee) {
        $notifications[] = [
            'id' => 'maintenance_alert',
            'type' => 'warning',
            'title' => 'Maintenance programmée',
            'message' => 'Une maintenance est programmée ce soir de 22h à 02h',
            'time' => $now,
            'icon' => 'fas fa-tools',
            'read' => false
        ];
    }
    
    return $notifications;
}

/**
 * Marquer une notification comme lue
 */
function markAsRead($db, $userId) {
    $notificationId = $_POST['notification_id'] ?? '';
    
    if (empty($notificationId)) {
        jsonResponse(['error' => 'ID de notification requis'], 400);
    }
    
    // Marquer comme lue si c'est une notification en base
    if (is_numeric($notificationId)) {
        $sql = "UPDATE notifications 
                SET est_lue = 1, date_lecture = NOW() 
                WHERE notification_id = ? AND utilisateur_id = ?";
        $db->query($sql, [$notificationId, $userId]);
    }
    
    jsonResponse(['success' => true]);
}

/**
 * Marquer toutes les notifications comme lues
 */
function markAllAsRead($db, $userId) {
    $sql = "UPDATE notifications 
            SET est_lue = 1, date_lecture = NOW() 
            WHERE utilisateur_id = ? AND est_lue = 0";
    $db->query($sql, [$userId]);
    
    jsonResponse(['success' => true]);
}

/**
 * Obtenir le nombre de notifications non lues
 */
function getUnreadCount($db, $userId) {
    $count = getUnreadCountValue($db, $userId);
    jsonResponse(['unread_count' => $count]);
}

/**
 * Calculer le nombre de notifications non lues
 */
function getUnreadCountValue($db, $userId) {
    $userRole = SessionManager::getUserRole();
    $count = 0;
    
    // Compter les notifications en base
    $storedUnread = $db->fetch("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE utilisateur_id = ? AND est_lue = 0
    ", [$userId]);
    
    $count += $storedUnread['count'] ?? 0;
    
    // Ajouter les notifications en temps réel selon le rôle
    if ($userRole === 'Responsable Scolarité') {
        // Nouveaux étudiants aujourd'hui
        $nouveauxEtudiants = $db->fetch("
            SELECT COUNT(*) as count 
            FROM etudiants 
            WHERE DATE(date_creation) = CURDATE()
        ");
        $count += $nouveauxEtudiants['count'] ?? 0;
        
        // Rapports déposés récemment
        $rapportsRecents = $db->fetch("
            SELECT COUNT(*) as count 
            FROM rapports 
            WHERE statut_id = 9 AND DATE(date_depot) >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        ");
        $count += $rapportsRecents['count'] ?? 0;
        
        // Étudiants non éligibles
        $nonEligibles = $db->fetch("
            SELECT COUNT(*) as count 
            FROM etudiants 
            WHERE statut_eligibilite != 5
        ");
        if (($nonEligibles['count'] ?? 0) > 0) {
            $count += 1; // Une seule notification pour tous les non éligibles
        }
    }
    
    return $count;
}

/**
 * Créer une nouvelle notification
 */
function createNotification($db, $userId, $type, $title, $message, $actionUrl = null) {
    $sql = "INSERT INTO notifications (
                utilisateur_id, 
                type_notification, 
                titre_notification, 
                contenu_notification, 
                lien_action,
                est_envoi_email
            ) VALUES (?, ?, ?, ?, ?, 0)";
    
    $db->query($sql, [$userId, $type, $title, $message, $actionUrl]);
    
    return $db->getLastInsertId();
}
?>