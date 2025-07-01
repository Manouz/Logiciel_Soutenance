<?php
// Ce fichier est destiné à être inclus dans la structure principale de la page.

// Assurer la connexion à la BD et la disponibilité du générateur
// Ces require_once sont importants si sidebar.php est inclus
// dans un contexte où ils ne sont pas déjà chargés.
// Cependant, si header.php les charge déjà, ils pourraient être redondants (mais inoffensifs).
require_once __DIR__ . '/../config/config.php'; // Pour SITE_URL, et potentiellement la session
require_once __DIR__ . '/../core/db_connect.php';   // Pour getPDOConnection()
require_once __DIR__ . '/sidebar_generateur.php'; // Pour generer_menu_sidebar()

$pdo_sidebar = getPDOConnection(); // Obtenir une instance PDO

if (!$pdo_sidebar) {
    // Gérer l'erreur de connexion à la BD si elle n'a pas pu être établie
    // Peut-être afficher un message d'erreur discret ou rien du tout.
    // echo "<div class='alert alert-danger'>Erreur de connexion BD pour la sidebar.</div>";
    // Normalement, db_connect.php gère déjà le logging ou die() en cas d'échec critique.
} else {
    // Appeler la fonction pour générer le menu de la sidebar
    // La fonction generer_menu_sidebar() est dans sidebar_generateur.php
    // Elle utilise $_SESSION['id_gu'] pour identifier le groupe de l'utilisateur.
    // Assurez-vous que la session est démarrée (normalement fait dans config.php ou header.php)
    // et que $_SESSION['id_gu'] est défini après la connexion de l'utilisateur.

    $sidebar_html = generer_menu_sidebar($pdo_sidebar);

    // Afficher le HTML de la sidebar
    // Vous pouvez entourer cela d'une structure de sidebar (ex: <nav id="sidebar">)
    // La fonction generer_menu_sidebar retourne déjà un <ul>.

    // Exemple de structure de base pour une sidebar Bootstrap 5
    // Cette structure serait typiquement dans le fichier qui inclut sidebar.php (ex: header.php ou un template de page)
    // Pour l'instant, on affiche directement le contenu généré.
    // Si la sidebar est une <nav> dans une colonne Bootstrap :
    // <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" id="sidebarMenu">
    //   <div class="position-sticky pt-3">
    //      <?php echo $sidebar_html; (le ul généré) >
    //   </div>
    // </div>

    echo $sidebar_html;
}
?>
