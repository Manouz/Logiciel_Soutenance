<?php
require_once 'Database.php';

class Traitement {
    private $conn;
    private $table_name = "traitement";

    public $id_trait;
    public $lib_trait;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lire avec pagination
    public function lire($page = 1, $par_page = 5) {
        $offset = ($page - 1) * $par_page;
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id_trait ASC LIMIT :par_page OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':par_page', $par_page, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Créer un traitement
    public function creer() {
        $query = "INSERT INTO " . $this->table_name . " (lib_trait) VALUES (:lib_trait)";
        $stmt = $this->conn->prepare($query);
        $this->lib_trait = htmlspecialchars(strip_tags($this->lib_trait));
        $stmt->bindParam(":lib_trait", $this->lib_trait);
        if($stmt->execute()) {
            $this->id_trait = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Mettre à jour un traitement
    public function mettreAJour() {
        $query = "UPDATE " . $this->table_name . " SET lib_trait=:lib_trait WHERE id_trait=:id_trait";
        $stmt = $this->conn->prepare($query);
        $this->lib_trait = htmlspecialchars(strip_tags($this->lib_trait));
        $stmt->bindParam(":lib_trait", $this->lib_trait);
        $stmt->bindParam(":id_trait", $this->id_trait);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer un traitement
    public function supprimer() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_trait = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_trait);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer plusieurs traitements
    public function supprimerMultiple($ids) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $query = "DELETE FROM " . $this->table_name . " WHERE id_trait IN ($placeholders)";
        $stmt = $this->conn->prepare($query);
        if($stmt->execute($ids)) {
            return true;
        }
        return false;
    }

    // Obtenir le nombre total de traitements
    public function getCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    }
}
?>
