<?php
/**
 * Header commun pour toutes les pages
 * Système de Validation Académique - Université Félix Houphouët-Boigny
 */

// Vérifier que les constantes sont définies
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/constants.php';
}

// Obtenir les informations de l'utilisateur connecté
$currentUser = getCurrentUser();
$pageTitle = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription ?? 'Système de gestion des validations académiques';
$additionalCSS = $additionalCSS ?? [];
$additionalJS = $additionalJS ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= escape($pageDescription) ?>">
    <meta name="author" content="<?= UNIVERSITY_NAME ?>">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
    
    <title><?= escape($pageTitle) ?> - <?= UNIVERSITY_SHORT_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('images/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>">
    
    <!-- CSS Framework et Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Global -->
    <link href="<?= asset('css/common/global-style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/common/responsive.css') ?>" rel="stylesheet">
    
    <!-- CSS spécifiques additionnels -->
    <?php foreach ($additionalCSS as $cssFile): ?>
        <link href="<?= asset('css/' . $cssFile) ?>" rel="stylesheet">
    <?php endforeach; ?>
    
    <!-- CSS Variables et Themes -->
    <style>
        :root {
            --primary-color: #1a5490;
            --secondary-color: #f8b500;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --dark-color: #2c3e50;
            --light-bg: #f8f9fa;
            --sidebar-width: 280px;
            --navbar-height: 70px;
            --border-radius: 10px;
            --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --box-shadow-lg: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-color);
        }
        
        .main-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .content-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 2rem;
            transition: var(--transition);
        }
        
        .content-wrapper.sidebar-collapsed {
            margin-left: 80px;
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding: 1rem;
            }
        }
        
        /* Loading spinner global */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .loading-spinner.show {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Toast notifications */
        .toast-container {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 1055;
        }
        
        /* Scrollbar custom */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
    </style>
</head>
<body>
    <?php if (isMaintenanceMode()): ?>
        <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
            <i class="fas fa-tools me-2"></i>
            <strong>Mode maintenance :</strong> <?= MAINTENANCE_MESSAGE ?>
        </div>
    <?php endif; ?>
    
    <!-- Loading Spinner Global -->
    <div class="loading-spinner" id="globalLoader">
        <div class="spinner"></div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container"></div>
    
    <div class="main-wrapper">
        <?php 
        // Inclure la navbar si l'utilisateur est connecté
        if (SessionManager::isLoggedIn()) {
            include __DIR__ . '/navbar.php';
        }
        ?>
        
        <!-- Début du contenu principal -->
        <main class="content-wrapper <?= !SessionManager::isLoggedIn() ? 'no-sidebar' : '' ?>" id="mainContent">
            
            <?php 
            // Afficher les messages flash s'il y en a
            if (isset($_SESSION['flash_messages'])) {
                foreach ($_SESSION['flash_messages'] as $type => $messages) {
                    foreach ($messages as $message) {
                        $alertClass = match($type) {
                            'success' => 'alert-success',
                            'error' => 'alert-danger', 
                            'warning' => 'alert-warning',
                            'info' => 'alert-info',
                            default => 'alert-info'
                        };
                        
                        $icon = match($type) {
                            'success' => 'fas fa-check-circle',
                            'error' => 'fas fa-exclamation-circle',
                            'warning' => 'fas fa-exclamation-triangle',
                            'info' => 'fas fa-info-circle',
                            default => 'fas fa-info-circle'
                        };
                        
                        echo "<div class='alert {$alertClass} alert-dismissible fade show' role='alert'>
                                <i class='{$icon} me-2'></i>
                                {$message}
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                              </div>";
                    }
                }
                unset($_SESSION['flash_messages']);
            }
            ?>
            
            <!-- Breadcrumb si défini -->
            <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= page('dashboard.php') ?>">
                                <i class="fas fa-home"></i> Accueil
                            </a>
                        </li>
                        <?php foreach ($breadcrumb as $index => $item): ?>
                            <?php if ($index === count($breadcrumb) - 1): ?>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?= escape($item['title']) ?>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item">
                                    <a href="<?= $item['url'] ?>"><?= escape($item['title']) ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>
            
            <!-- En-tête de page si défini -->
            <?php if (isset($pageHeader)): ?>
                <div class="page-header mb-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="page-title"><?= escape($pageHeader['title']) ?></h1>
                            <?php if (isset($pageHeader['subtitle'])): ?>
                                <p class="page-subtitle text-muted"><?= escape($pageHeader['subtitle']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($pageHeader['actions'])): ?>
                            <div class="col-auto">
                                <div class="page-actions">
                                    <?= $pageHeader['actions'] ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

<script>
// Variables JavaScript globales
window.APP_CONFIG = {
    name: '<?= APP_NAME ?>',
    version: '<?= APP_VERSION ?>',
    baseUrl: '<?= $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) ?>',
    csrfToken: '<?= generateCSRFToken() ?>',
    user: <?= $currentUser ? json_encode([
        'id' => $currentUser['id'],
        'name' => $currentUser['name'],
        'role' => $currentUser['role'],
        'level' => $currentUser['niveau_acces']
    ]) : 'null' ?>,
    sessionTimeout: <?= SESSION_TIMEOUT ?>,
    maxFileSize: <?= MAX_FILE_SIZE ?>,
    allowedTypes: <?= json_encode(ALLOWED_EXTENSIONS['all']) ?>
};

// Fonction pour afficher les notifications toast
function showToast(message, type = 'info', duration = 5000) {
    const toastContainer = document.querySelector('.toast-container');
    const toastId = 'toast-' + Date.now();
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const colors = {
        success: 'text-bg-success',
        error: 'text-bg-danger',
        warning: 'text-bg-warning',
        info: 'text-bg-info'
    };
    
    const toastHTML = `
        <div class="toast ${colors[type] || colors.info}" role="alert" id="${toastId}">
            <div class="toast-header">
                <i class="fas ${icons[type] || icons.info} me-2"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: duration });
    toast.show();
    
    // Supprimer l'élément après fermeture
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Fonction pour afficher/masquer le loader global
function showLoader() {
    document.getElementById('globalLoader').classList.add('show');
}

function hideLoader() {
    document.getElementById('globalLoader').classList.remove('show');
}

// Gestion des requêtes AJAX avec CSRF token
function ajaxRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.APP_CONFIG.csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };
    
    return fetch(url, mergedOptions);
}

// Auto-refresh du token CSRF
setInterval(() => {
    fetch('api/refresh-csrf.php')
        .then(response => response.json())
        .then(data => {
            if (data.token) {
                window.APP_CONFIG.csrfToken = data.token;
                document.querySelector('meta[name="csrf-token"]').content = data.token;
            }
        })
        .catch(console.error);
}, 30 * 60 * 1000); // Toutes les 30 minutes

// Confirmer avant suppression
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// Format de nombres
function formatNumber(number, decimals = 2) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

// Format de dates
function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    
    return new Date(date).toLocaleDateString('fr-FR', {...defaultOptions, ...options});
}
</script>