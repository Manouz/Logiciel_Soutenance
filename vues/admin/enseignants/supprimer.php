<?php
/**
 * Supprimer un Enseignant - Administration
 * Fichier: vues/admin/enseignants/supprimer.php
 */

require_once '../../../config/database.php';
require_once '../../../includes/auth.php';

requireAuth(ROLE_ADMIN);

$db = Database::getInstance();

$enseignant_id = $_GET['id'] ?? null;
if (!$enseignant_id) {
    header('Location: liste.php');
    exit;
}

// Charger les données de l'enseignant
$enseignant = $db->fetch("
    SELECT ens.enseignant_id, ip.nom, ip.prenoms
    FROM enseignants ens
    INNER JOIN utilisateurs u ON ens.utilisateur_id = u.utilisateur_id
    INNER JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
    WHERE ens.enseignant_id = ?
", [$enseignant_id]);

if (!$enseignant) {
    header('Location: liste.php');
    exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $db->beginTransaction();
        // Suppression de l'enseignant (et éventuellement de l'utilisateur associé)
        $utilisateur_id = $db->fetch("SELECT utilisateur_id FROM enseignants WHERE enseignant_id = ?", [$enseignant_id])['utilisateur_id'];
        $db->delete("DELETE FROM enseignants WHERE enseignant_id = ?", [$enseignant_id]);
        $db->delete("DELETE FROM informations_personnelles WHERE utilisateur_id = ?", [$utilisateur_id]);
        $db->delete("DELETE FROM utilisateurs WHERE utilisateur_id = ?", [$utilisateur_id]);
        $db->commit();
        $success = true;
    } catch (Exception $e) {
        $db->rollback();
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

$page_title = "Supprimer un Enseignant";
$custom_css = ['admin/admin-crud.css'];
include '../../../includes/header.php';
?>
<div class="admin-crud-page">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card mt-5">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-user-times"></i> Supprimer un Enseignant</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">Enseignant supprimé avec succès. <a href="liste.php">Retour à la liste</a></div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <strong>Attention !</strong> Vous êtes sur le point de supprimer l'enseignant <strong><?= htmlspecialchars($enseignant['prenoms'] . ' ' . $enseignant['nom']) ?></strong>.<br>
                                Cette action est irréversible. Voulez-vous continuer ?
                            </div>
                            <form method="post" action="">
                                <div class="mt-3 text-end">
                                    <button type="submit" name="confirm" class="btn btn-danger">Oui, supprimer</button>
                                    <a href="liste.php" class="btn btn-secondary">Annuler</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../../../includes/footer.php'; ?>
