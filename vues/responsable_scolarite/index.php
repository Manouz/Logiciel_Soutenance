<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $traitements = $_POST['traitements'] ?? [];

    // Simuler l'enregistrement
    echo "<h2>✅ Traitements enregistrés :</h2>";
    echo "<ul>";
    foreach ($traitements as $tr) {
        echo "<li>$tr</li>";
    }
    echo "</ul>";
    echo "<a href='attribution.php'>⬅ Retour</a>";
}


// Exemple : chargement des traitements depuis un tableau simulé
$traitements = [
    ['code' => 'TR001', 'libelle' => 'Ajouter un étudiant', 'categorie' => 'Étudiants'],
    ['code' => 'TR002', 'libelle' => 'Calcul automatique des moyennes', 'categorie' => 'Notes'],
    ['code' => 'TR020', 'libelle' => 'Déposer un rapport', 'categorie' => 'Rapports'],
    ['code' => 'TR030', 'libelle' => 'Transmettre à la commission', 'categorie' => 'Commission'],
    ['code' => 'TR040', 'libelle' => 'Planifier une soutenance', 'categorie' => 'Soutenances'],
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Attribution des traitements</title>
  <link rel="stylesheet" href="style.css">
</head>
<style>
body {
  font-family: Arial, sans-serif;
  margin: 2rem;
  background: #f4f4f4;
}

h2 {
  color: #004466;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
  background: #fff;
  box-shadow: 0 0 5px rgba(0,0,0,0.1);
}

th, td {
  border: 1px solid #ccc;
  padding: 0.6rem;
  text-align: left;
}

th {
  background: #e0f7fa;
}

button {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
  background-color: #00796b;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

</style>
<body>

<h2>🛠️ Attribution des traitements à un utilisateur</h2>

<label for="filtre">🔍 Filtrer par catégorie :</label>
<select id="filtre" onchange="filtrerCategorie()">
  <option value="tous">Tous</option>
  <?php foreach(array_unique(array_column($traitements, 'categorie')) as $cat): ?>
    <option value="<?= $cat ?>"><?= $cat ?></option>
  <?php endforeach; ?>
</select>

<form method="post" action="save_permissions.php">
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Libellé</th>
        <th>Catégorie</th>
        <th>Autorisé</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($traitements as $tr): ?>
        <tr data-categorie="<?= $tr['categorie'] ?>">
          <td><?= $tr['code'] ?></td>
          <td title="<?= $tr['libelle'] ?>"><?= $tr['libelle'] ?></td>
          <td><?= $tr['categorie'] ?></td>
          <td>
            <input type="checkbox" name="traitements[]" value="<?= $tr['code'] ?>">
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <button type="submit">💾 Enregistrer les permissions</button>
</form>

<script>
function filtrerCategorie() {
  let filtre = document.getElementById('filtre').value;
  let lignes = document.querySelectorAll("tbody tr");
  lignes.forEach(row => {
    if (filtre === "tous" || row.dataset.categorie === filtre) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}
</script>

</body>
</html>
