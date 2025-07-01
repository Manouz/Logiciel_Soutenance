<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/functions.php'; // Sera créé plus tard

// TODO: Vérifier si l'utilisateur est admin et authentifié

$page_title = "Gestion des Permissions par Groupe";
// Inclure l'en-tête
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5 pt-4"> <!-- mt-5 et pt-4 pour espacement avec navbar fixe -->
    <h1 class="mb-4"><?php echo htmlspecialchars($page_title); ?></h1>

    <p>Interface pour attribuer les permissions sur les traitements aux groupes d'utilisateurs.</p>

    <!-- Zone pour les messages (succès, erreurs) -->
    <div id="messagesPermissions" class="my-3"></div>

    <div class="row mb-3">
        <div class="col-md-6 col-lg-4">
            <label for="selectGroupeUtilisateur" class="form-label">Choisir un Groupe d'Utilisateurs :</label>
            <select class="form-select" id="selectGroupeUtilisateur">
                <option selected disabled value="">Sélectionnez un groupe...</option>
                <!-- Les groupes seront chargés ici par JavaScript -->
            </select>
        </div>
    </div>

    <div id="permissionsContainer" class="mt-4" style="display: none;">
        <h2>Permissions pour le groupe : <span id="nomGroupeSelectionne"></span></h2>
        <form id="formPermissions">
            <input type="hidden" id="selectedGroupeId" name="id_gu">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Traitement (Page/Fonctionnalité)</th>
                        <th>Voir dans Sidebar</th>
                        <th>Ajouter</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                        <th>Imprimer</th>
                        <th>Exporter</th>
                        <th>Importer</th>
                    </tr>
                </thead>
                <tbody id="listePermissionsBody">
                    <!-- Les traitements et leurs permissions seront chargés ici -->
                </tbody>
            </table>
            <button type="submit" class="btn btn-success" id="btnSauvegarderPermissions">Sauvegarder les Permissions</button>
        </form>
    </div>

</div>

<?php
// Inclure le pied de page
require_once __DIR__ . '/../includes/footer.php';
// Le script JS spécifique gestion_permissions.js devrait être inclus par footer.php
// s'il détecte que nous sommes sur la page gestion_permissions.php.
// Si cette logique n'est pas dans footer.php, décommentez la ligne ci-dessous :
// echo '<script src="../assets/js/gestion_permissions.js"></script>';
?>
