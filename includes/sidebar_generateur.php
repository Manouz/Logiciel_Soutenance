<?php
// Ce fichier ne devrait pas être accessible directement via URL.
// Il est destiné à être inclus par d'autres scripts PHP.

/**
 * Génère le HTML pour la sidebar de l'utilisateur connecté.
 *
 * @param PDO $pdo L'objet de connexion PDO.
 * @return string Le HTML de la sidebar, ou une chaîne vide si aucune permission ou erreur.
 */
function generer_menu_sidebar(PDO $pdo): string {
    $html_sidebar = "";

    // 1. Récupérer l'ID du groupe de l'utilisateur connecté (simulation)
    // En réalité, cela viendrait d'une session après authentification.
    // Exemple: $id_gu_utilisateur = $_SESSION['id_gu'] ?? null;

    // Pour les tests, on peut forcer un id_gu. Supprimez ou commentez en production.
    if (isset($_SESSION['id_gu'])) {
        $id_gu_utilisateur = (int)$_SESSION['id_gu'];
    } else {
        // Si vous voulez tester sans session, décommentez la ligne suivante avec un ID de groupe valide.
        // $id_gu_utilisateur = 1; // Exemple: ID du groupe admin ou un autre groupe de test
        // return "<ul class='nav flex-column'><li class='nav-item'><span class='nav-link text-warning'>ID Groupe Utilisateur non défini en session.</span></li></ul>";
        return ""; // Pas de sidebar si pas d'ID de groupe
    }

    if (!$id_gu_utilisateur) {
        // Pas d'utilisateur connecté ou pas de groupe associé.
        // Vous pourriez retourner un menu par défaut ou un message.
        return "<ul class='nav flex-column'><li class='nav-item'><span class='nav-link text-muted'>Non connecté</span></li></ul>";
    }

    try {
        // 2. Interroger la base de données
        $sql = "SELECT t.lib_trait, t.url_traitement, t.icone
                FROM traitement t
                JOIN rattacher r ON t.id_trait = r.id_traitement
                WHERE r.id_gu = :id_gu_utilisateur
                  AND r.voir_dans_sidebar = TRUE
                  AND t.est_actif = TRUE  -- On ne montre que les traitements globalement actifs
                ORDER BY t.ordre_affichage ASC, t.lib_trait ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_gu_utilisateur', $id_gu_utilisateur, PDO::PARAM_INT);
        $stmt->execute();

        $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Générer le HTML
        // Utilisation de classes Bootstrap pour la sidebar (exemple)
        $html_sidebar .= "<ul class='nav flex-column'>"; // Classe 'sidebar-nav' ou autre de votre CSS

        if (empty($menu_items)) {
            // $html_sidebar .= "<li class='nav-item'><span class='nav-link text-muted'>Aucun menu disponible.</span></li>";
            // Ou ne rien afficher si aucun item
        } else {
            $site_url_base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';

            foreach ($menu_items as $item) {
                $url = !empty($item['url_traitement']) ? htmlspecialchars($item['url_traitement']) : '#';
                // S'assurer que l'URL est correctement formée (absolue ou relative à la racine du site)
                if ($url !== '#' && !filter_var($url, FILTER_VALIDATE_URL) && strpos($url, '/') !== 0) {
                    // Si ce n'est pas une URL absolue et ne commence pas par '/', la préfixer avec SITE_URL
                    $url = $site_url_base . '/' . ltrim($url, '/');
                } else if ($url !== '#' && strpos($url, '/') === 0 && defined('SITE_URL')) {
                    // Si commence par / mais n'est pas une URL complète (ex: /admin/page.php)
                    // On la combine avec la partie host de SITE_URL
                    $parsed_site_url = parse_url(SITE_URL);
                    $base_for_relative = $parsed_site_url['scheme'] . '://' . $parsed_site_url['host'];
                    if(isset($parsed_site_url['port'])) $base_for_relative .= ':' . $parsed_site_url['port'];
                    // $url = $base_for_relative . $url; // Ceci peut être trop si SITE_URL contient déjà un path.
                    // Plus simple: si url commence par /, on suppose qu'elle est relative à la racine du domaine.
                    // Si SITE_URL a un sous-dossier, il faut être prudent.
                    // Pour l'instant, on assume que les URL commençant par / sont correctes ou que SITE_URL est la racine du domaine.
                    // Si SITE_URL est http://localhost/monprojet/, et url est /page.php, on veut http://localhost/monprojet/page.php
                    // Si url est page.php, on veut http://localhost/monprojet/page.php
                    // Si url est http://example.com/page.php, on la laisse telle quelle.
                    if (strpos($url, '/') === 0 && $site_url_base !== '') { // url = /admin/users.php, site_url_base = http://localhost/myapp
                         $url = $site_url_base . $url; // -> http://localhost/myapp/admin/users.php
                    }

                }


                $icone_html = !empty($item['icone']) ? "<i class='" . htmlspecialchars($item['icone']) . " me-2'></i>" : "";

                $html_sidebar .= "<li class='nav-item'>";
                $html_sidebar .= "<a class='nav-link' href='" . $url . "'>";
                $html_sidebar .= $icone_html . htmlspecialchars($item['lib_trait']);
                $html_sidebar .= "</a></li>";
            }
        }
        $html_sidebar .= "</ul>";

    } catch (PDOException $e) {
        error_log("Erreur PDO Sidebar: " . $e->getMessage());
        // En production, ne pas afficher l'erreur SQL directement.
        $html_sidebar = "<ul class='nav flex-column'><li class='nav-item'><span class='nav-link text-danger'>Erreur chargement menu.</span></li></ul>";
    } catch (Exception $e) {
        error_log("Erreur Générale Sidebar: " . $e->getMessage());
        $html_sidebar = "<ul class='nav flex-column'><li class='nav-item'><span class='nav-link text-danger'>Erreur interne menu.</span></li></ul>";
    }

    return $html_sidebar;
}

// Pour tester cette fonction directement (à commenter/supprimer en production)
/*
if (php_sapi_name() === 'cli' || (isset($_GET['test_sidebar']) && $_GET['test_sidebar'] == '1')) {
    require_once __DIR__ . '/../config/config.php'; // Pour SITE_URL etc.
    require_once __DIR__ . '/../core/db_connect.php'; // Pour getPDOConnection

    $pdo_test = getPDOConnection();
    if ($pdo_test) {
        // Simuler une session pour le test
        $_SESSION['id_gu'] = 1; // Remplacer par un ID de groupe valide dans votre BD

        echo "<h3>Test de la Sidebar Générée :</h3>";
        echo generer_menu_sidebar($pdo_test);
    } else {
        echo "Impossible de se connecter à la base de données pour le test.";
    }
}
*/
?>
