
<?php
// Simulation de groupes utilisateurs
$groupes = ["admin", "enseignant", "etudiant", "personnel_n1"];
$actions = ["exporter", "importer", "modifier", "imprimer"];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Actions</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>🧩 Gestion des Actions par Groupe Utilisateur</h2>
    <form method="POST" action="save_menu.php">
        <label for="groupe">Choisir un groupe utilisateur :</label>
        <select name="groupe" id="groupe" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach($groupes as $g): ?>
                <option value="<?= $g ?>"><?= ucfirst($g) ?></option>
            <?php endforeach; ?>
        </select>

        <div id="actions">
            <p><strong>Actions disponibles :</strong></p>
            <?php foreach($actions as $act): ?>
                <label>
                    <input type="checkbox" name="actions[]" value="<?= $act ?>">
                    <?= ucfirst($act) ?>
                </label><br>
            <?php endforeach; ?>
        </div>

        <button type="submit">💾 Enregistrer les autorisations</button>
    </form>
    
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $groupe = $_POST["groupe"] ?? "non défini";
    $actions = $_POST["actions"] ?? [];

    echo "<h2>✅ Actions enregistrées pour le groupe : <em>$groupe</em></h2>";
    echo "<ul>";
    foreach ($actions as $act) {
        echo "<li>$act</li>";
    }
    echo "</ul>";
    echo "<p><a href='menu_gestion.php'>⬅ Retour à la gestion</a></p>";

    // Ici tu pourrais insérer les données dans une table `rattacher(groupe, action)`
}
?>
</body>
</html>
