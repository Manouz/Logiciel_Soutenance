<?php
/**
 * Fonctions utilitaires
 * Système de Validation Académique - UFHB Cocody
 * Fichier: includes/functions.php
 */

/**
 * Nettoyer et sécuriser les entrées utilisateur
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Valider une adresse email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valider un numéro de téléphone
 */
function validatePhone($phone) {
    // Pattern pour les numéros ivoiriens
    $pattern = '/^(\+225|225)?[0-9]{8,10}$/';
    return preg_match($pattern, $phone);
}

/**
 * Générer un token sécurisé
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Générer un mot de passe aléatoire
 */
function generateRandomPassword($length = 12) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $max)];
    }
    
    return $password;
}

/**
 * Formater une date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * Formater une date et heure
 */
function formatDateTime($datetime, $format = 'd/m/Y à H:i') {
    return formatDate($datetime, $format);
}

/**
 * Calculer l'âge à partir d'une date de naissance
 */
function calculateAge($birthDate) {
    if (empty($birthDate) || $birthDate === '0000-00-00') {
        return null;
    }
    
    try {
        $birth = new DateTime($birthDate);
        $today = new DateTime();
        return $today->diff($birth)->y;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Formater une taille de fichier
 */
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

/**
 * Générer un slug à partir d'une chaîne
 */
function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[àáâãäå]/', 'a', $string);
    $string = preg_replace('/[èéêë]/', 'e', $string);
    $string = preg_replace('/[ìíîï]/', 'i', $string);
    $string = preg_replace('/[òóôõö]/', 'o', $string);
    $string = preg_replace('/[ùúûü]/', 'u', $string);
    $string = preg_replace('/[ñ]/', 'n', $string);
    $string = preg_replace('/[ç]/', 'c', $string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

/**
 * Redirection sécurisée
 */
function redirectTo($url, $permanent = false) {
    $status = $permanent ? 301 : 302;
    
    // Nettoyer l'URL
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    // Si c'est une URL relative, ajouter le chemin de base
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $baseUrl = getBaseUrl();
        $url = rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }
    
    header("Location: $url", true, $status);
    exit();
}

/**
 * Obtenir l'URL de base du site
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    
    return $protocol . '://' . $host . ($path === '/' ? '' : $path);
}

/**
 * Réponse JSON
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Vérifier si c'est une requête AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Obtenir l'adresse IP du client
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
               'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Hasher un mot de passe avec un salt
 */
function hashPassword($password, $salt = null) {
    if ($salt === null) {
        $salt = bin2hex(random_bytes(16));
    }
    
    $hash = hash('sha256', $password . $salt);
    
    return [
        'hash' => $hash,
        'salt' => $salt
    ];
}

/**
 * Vérifier un mot de passe
 */
function verifyPassword($password, $hash, $salt) {
    return hash('sha256', $password . $salt) === $hash;
}

/**
 * Générer un numéro unique
 */
function generateUniqueNumber($prefix = '', $length = 6) {
    $number = str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    return $prefix . $number;
}

/**
 * Valider une extension de fichier
 */
function validateFileExtension($filename, $allowedExtensions = ['pdf', 'doc', 'docx']) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowedExtensions);
}

/**
 * Sécuriser un nom de fichier
 */
function sanitizeFilename($filename) {
    // Conserver l'extension
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    
    // Nettoyer le nom
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
    $name = preg_replace('/_+/', '_', $name);
    $name = trim($name, '_');
    
    return $name . '.' . $extension;
}

/**
 * Créer un dossier s'il n'existe pas
 */
function createDirectoryIfNotExists($path, $permissions = 0755) {
    if (!is_dir($path)) {
        return mkdir($path, $permissions, true);
    }
    return true;
}

/**
 * Obtenir l'extension d'un fichier
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Vérifier si un fichier est une image
 */
function isImageFile($filename) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    return in_array(getFileExtension($filename), $imageExtensions);
}

/**
 * Générer une couleur aléatoire
 */
function generateRandomColor() {
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

/**
 * Tronquer un texte
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Nettoyer une chaîne pour la recherche
 */
function cleanSearchString($string) {
    $string = trim($string);
    $string = preg_replace('/\s+/', ' ', $string);
    return $string;
}

/**
 * Convertir les accents
 */
function removeAccents($string) {
    $accents = [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ñ' => 'n', 'ç' => 'c'
    ];
    
    return strtr(strtolower($string), $accents);
}

/**
 * Vérifier les permissions d'accès
 */
function checkPermission($requiredRole, $userRole) {
    $roleHierarchy = [
        'Étudiant' => 1,
        'Personnel Administratif' => 2,
        'Enseignant' => 3,
        'Secrétaire' => 4,
        'Commission' => 5,
        'Chargé Communication' => 6,
        'Responsable Scolarité' => 7,
        'Administrateur' => 8
    ];
    
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
    $userLevel = $roleHierarchy[$userRole] ?? 0;
    
    return $userLevel >= $requiredLevel;
}

/**
 * Logger une action
 */
function logAction($action, $details = '', $userId = null) {
    try {
        if ($userId === null) {
            $userId = SessionManager::getUserId();
        }
        
        $db = Database::getInstance();
        $sql = "INSERT INTO logs_audit (utilisateur_id, type_action, commentaire, adresse_ip, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $userId,
            $action,
            $details,
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        $db->query($sql, $params);
    } catch (Exception $e) {
        error_log("Erreur lors du logging: " . $e->getMessage());
    }
}

/**
 * Obtenir la configuration du système
 */
function getSystemConfig($key, $default = null) {
    try {
        $db = Database::getInstance();
        $config = $db->fetch("SELECT valeur_configuration FROM configuration_systeme WHERE cle_configuration = ?", [$key]);
        return $config ? $config['valeur_configuration'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Définir une configuration du système
 */
function setSystemConfig($key, $value, $userId = null) {
    try {
        if ($userId === null) {
            $userId = SessionManager::getUserId();
        }
        
        $db = Database::getInstance();
        
        // Vérifier si la configuration existe
        $exists = $db->exists('configuration_systeme', 'cle_configuration = ?', [$key]);
        
        if ($exists) {
            $sql = "UPDATE configuration_systeme SET valeur_configuration = ?, modifie_par = ? WHERE cle_configuration = ?";
            $db->query($sql, [$value, $userId, $key]);
        } else {
            $sql = "INSERT INTO configuration_systeme (cle_configuration, valeur_configuration, modifie_par) VALUES (?, ?, ?)";
            $db->query($sql, [$key, $value, $userId]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Erreur configuration: " . $e->getMessage());
        return false;
    }
}

/**
 * Détecter le type de fichier par son contenu
 */
function detectMimeType($file) {
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $mimeType;
    } elseif (function_exists('mime_content_type')) {
        return mime_content_type($file);
    } else {
        return false;
    }
}

/**
 * Créer un breadcrumb
 */
function createBreadcrumb($items) {
    $breadcrumb = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    $count = count($items);
    foreach ($items as $index => $item) {
        if ($index === $count - 1) {
            $breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $url = isset($item['url']) ? htmlspecialchars($item['url']) : '#';
            $breadcrumb .= '<li class="breadcrumb-item"><a href="' . $url . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }
    
    $breadcrumb .= '</ol></nav>';
    return $breadcrumb;
}
?>