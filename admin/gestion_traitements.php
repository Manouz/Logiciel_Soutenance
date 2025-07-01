<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/functions.php'; // Sera créé plus tard

// TODO: Vérifier si l'utilisateur est admin et authentifié

$page_title = "Gestion des Traitements";
// Inclure l'en-tête
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5 pt-4"> <!-- mt-5 et pt-4 pour espacement avec navbar fixe -->
    <h1 class="mb-4"><?php echo htmlspecialchars($page_title); ?></h1>

    <p>Interface pour ajouter, modifier, lister et supprimer les traitements (pages/fonctionnalités) de l'application.</p>

    <!-- Zone pour les messages (succès, erreurs) -->
    <div id="messages" class="my-3"></div>

    <!-- Bouton pour ajouter un nouveau traitement (ouvre un modal par exemple) -->
    <button class="btn btn-primary mb-3" id="btnAjouterTraitement" data-bs-toggle="modal" data-bs-target="#traitementModal">
        <i class="fas fa-plus"></i> Ajouter un Traitement
    </button>

    <h2 class="mt-4">Liste des Traitements</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Libellé</th>
                <th>Description</th>
                <th>URL</th>
                <th>Icône</th>
                <th>Ordre</th>
                <th>Actif</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="listeTraitementsBody">
            <!-- Les traitements seront chargés ici par JavaScript -->
            <tr>
                <td colspan="8" class="text-center">Chargement des traitements...</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal pour Ajouter/Modifier un traitement (exemple avec Bootstrap) -->
<div class="modal fade" id="traitementModal" tabindex="-1" aria-labelledby="traitementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="traitementModalLabel">Ajouter/Modifier un Traitement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTraitement">
                    <input type="hidden" id="traitementId" name="id_trait">
                    <div class="mb-3">
                        <label for="lib_trait" class="form-label">Libellé du traitement</label>
                        <input type="text" class="form-control" id="lib_trait" name="lib_trait" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="url_traitement" class="form-label">URL</label>
                        <input type="text" class="form-control" id="url_traitement" name="url_traitement">
                    </div>
                    <div class="mb-3">
                        <label for="icone" class="form-label">Icône (ex: fas fa-home)</label>
                        <input type="text" class="form-control" id="icone" name="icone" value="fas fa-file">
                    </div>
                    <div class="mb-3">
                        <label for="ordre_affichage" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="ordre_affichage" name="ordre_affichage" value="0">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="est_actif" name="est_actif" value="1" checked>
                        <label class="form-check-label" for="est_actif">Actif</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="submit" class="btn btn-primary" form="formTraitement" id="btnSaveTraitement">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
require_once __DIR__ . '/../includes/footer.php';
// Le script JS spécifique gestion_traitements.js devrait être inclus par footer.php
// s'il détecte que nous sommes sur la page gestion_traitements.php (basé sur $current_script_name).
// Si cette logique n'est pas dans footer.php, décommentez la ligne ci-dessous :
// echo '<script src="../assets/js/gestion_traitements.js"></script>';
?>
