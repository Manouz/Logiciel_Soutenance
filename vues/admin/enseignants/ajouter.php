<?php
/**
 * Ajouter un Enseignant - Administration
 * Fichier: vues/admin/enseignants/ajouter.php
 */

require_once '../../../config/database.php';
require_once '../../../includes/auth.php';

requireAuth(ROLE_ADMIN);

$db = Database::getInstance();

// Récupération des grades et spécialités pour les listes déroulantes
$grades = $db->fetchAll("SELECT DISTINCT grade FROM enseignants WHERE grade IS NOT NULL AND grade <> '' ORDER BY grade");
$specialites = $db->fetchAll("SELECT DISTINCT specialite FROM enseignants WHERE specialite IS NOT NULL AND specialite <> '' ORDER BY specialite");

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenoms = trim($_POST['prenoms'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $genre = $_POST['genre'] ?? '';
    $date_naissance = $_POST['date_naissance'] ?? '';
    $nationalite = trim($_POST['nationalite'] ?? '');
    $grade = $_POST['grade'] ?? '';
    $specialite = $_POST['specialite'] ?? '';
    $date_recrutement = $_POST['date_recrutement'] ?? '';
    $statut = isset($_POST['statut']) ? 1 : 0;
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Validation basique
    if (!$nom || !$prenoms || !$email || !$grade || !$specialite || !$mot_de_passe) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } else {
        try {
            $db->beginTransaction();
            // Création utilisateur
            $auth = new AuthManager();
            $hashData = $auth->hashPassword($mot_de_passe);
            $userId = $db->insert(
                "INSERT INTO utilisateurs (email, mot_de_passe_hash, salt, role_id, est_actif, date_creation) VALUES (?, ?, ?, (SELECT role_id FROM roles WHERE nom_role = ? LIMIT 1), ?, NOW())",
                [$email, $hashData['hash'], $hashData['salt'], ROLE_ENSEIGNANT, $statut]
            );
            // Infos personnelles
            $db->insert(
                "INSERT INTO informations_personnelles (utilisateur_id, nom, prenoms, telephone, genre, date_naissance, nationalite) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, $nom, $prenoms, $telephone, $genre, $date_naissance, $nationalite]
            );
            // Enseignant
            $db->insert(
                "INSERT INTO enseignants (utilisateur_id, grade, specialite, statut, date_recrutement) VALUES (?, ?, ?, ?, ?)",
                [$userId, $grade, $specialite, $statut, $date_recrutement]
            );
            $db->commit();
            $success = true;
        } catch (Exception $e) {
            $db->rollback();
            $error = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}

$page_title = "Ajouter un Enseignant";
$custom_css = ['admin/admin-crud.css'];
include '../../../includes/header.php';
?>
<div class="admin-crud-page">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-plus"></i> Ajouter un Enseignant</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">Enseignant ajouté avec succès ! <a href="liste.php">Retour à la liste</a></div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom *</label>
                                    <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prénoms *</label>
                                    <input type="text" name="prenoms" class="form-control" required value="<?= htmlspecialchars($_POST['prenoms'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Genre</label>
                                    <select name="genre" class="form-select">
                                        <option value="">--</option>
                                        <option value="M" <?= (($_POST['genre'] ?? '') == 'M') ? 'selected' : '' ?>>Homme</option>
                                        <option value="F" <?= (($_POST['genre'] ?? '') == 'F') ? 'selected' : '' ?>>Femme</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Date de naissance</label>
                                    <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nationalité</label>
                                    <input type="text" name="nationalite" class="form-control" value="<?= htmlspecialchars($_POST['nationalite'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Grade *</label>
                                    <select name="grade" class="form-select" required>
                                        <option value="">--</option>
                                        <?php foreach ($grades as $g): ?>
                                            <option value="<?= htmlspecialchars($g['grade']) ?>" <?= (($_POST['grade'] ?? '') == $g['grade']) ? 'selected' : '' ?>><?= htmlspecialchars($g['grade']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Spécialité *</label>
                                    <select name="specialite" class="form-select" required>
                                        <option value="">--</option>
                                        <?php foreach ($specialites as $s): ?>
                                            <option value="<?= htmlspecialchars($s['specialite']) ?>" <?= (($_POST['specialite'] ?? '') == $s['specialite']) ? 'selected' : '' ?>><?= htmlspecialchars($s['specialite']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date de recrutement</label>
                                    <input type="date" name="date_recrutement" class="form-control" value="<?= htmlspecialchars($_POST['date_recrutement'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mot de passe *</label>
                                    <input type="password" name="mot_de_passe" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="statut" class="form-check-input" id="statut" <?= isset($_POST['statut']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="statut">Compte actif</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                                <a href="liste.php" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../../../includes/footer.php'; ?>
