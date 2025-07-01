<?php
// Vérification de l'authentification et des permissions
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
?>

<div class="page-header">
    <h1><i class="fas fa-flask"></i> Page de Test</h1>
    <p>Page de test pour vérifier le système de chargement dynamique</p>
</div>

<div class="page-content">
    <div class="config-section">
        <div class="config-header">
            <div class="config-info">
                <h3>Test de Chargement</h3>
                <p>Cette page permet de tester le système de navigation dynamique</p>
            </div>
            <button class="btn btn-primary" onclick="testFunction()">
                <i class="fas fa-play"></i> Tester
            </button>
        </div>
        
        <div class="config-table">
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h3>Informations de Test</h3>
                <p>Cette page a été chargée dynamiquement avec succès !</p>
                
                <div style="margin-top: 1rem;">
                    <h4>Fonctionnalités testées :</h4>
                    <ul style="margin-left: 1rem; margin-top: 0.5rem;">
                        <li>✅ Chargement dynamique de page</li>
                        <li>✅ Mise à jour du titre dans le header</li>
                        <li>✅ Système de notifications</li>
                        <li>✅ Gestion des modals</li>
                        <li>✅ Navigation entre les pages</li>
                    </ul>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <button class="btn btn-success" onclick="showNotification('Test de notification réussie !', 'success')">
                        <i class="fas fa-bell"></i> Tester Notification
                    </button>
                    <button class="btn btn-warning" onclick="showNotification('Attention, ceci est un avertissement', 'warning')">
                        <i class="fas fa-exclamation-triangle"></i> Tester Avertissement
                    </button>
                    <button class="btn btn-danger" onclick="showNotification('Erreur de test', 'error')">
                        <i class="fas fa-times-circle"></i> Tester Erreur
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testFunction() {
    showNotification('Fonction de test exécutée avec succès !', 'success');
    console.log('Test function executed');
}

// Test d'initialisation de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page de test chargée avec succès');
    showNotification('Page de test chargée dynamiquement', 'info');
});
</script> 