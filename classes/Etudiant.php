<?php
class Etudiant {
    private $conn;
    private $table_name = "etudiants";

    public $num_etd;
    public $nom_etd;
    public $prenom_etd;
    public $num_carte_etd;
    public $date_naiss;
    public $email_etd;
    public $photo_etd;
    public $statut_eligibilite;
    public $id_niv_etd;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lire tous les étudiants
    public function lire() {
        $query = "SELECT e.*, ne.lib_niv_etd, 
                         COALESCE(r.statut, 'Non payé') as statut_reglement,
                         COALESCE(r.montant_paye, 0) as montant_paye,
                         COALESCE(r.montant_total, 0) as montant_total
                  FROM " . $this->table_name . " e
                  LEFT JOIN niveau_etude ne ON e.id_niv_etd = ne.id_niv_etd
                  LEFT JOIN reglement r ON e.num_etd = r.num_etd
                  ORDER BY e.nom_etd ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Créer un étudiant
    public function creer() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nom_etd=:nom_etd, prenom_etd=:prenom_etd, num_carte_etd=:num_carte_etd,
                      date_naiss=:date_naiss, email_etd=:email_etd, photo_etd=:photo_etd,
                      mdp_etd=:mdp_etd, statut_eligibilite=:statut_eligibilite, id_niv_etd=:id_niv_etd";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->nom_etd = htmlspecialchars(strip_tags($this->nom_etd));
        $this->prenom_etd = htmlspecialchars(strip_tags($this->prenom_etd));
        $this->num_carte_etd = htmlspecialchars(strip_tags($this->num_carte_etd));
        $this->email_etd = htmlspecialchars(strip_tags($this->email_etd));

        // Hash du mot de passe
        $password_hash = password_hash($this->mdp_etd, PASSWORD_DEFAULT);

        // Liaison des valeurs
        $stmt->bindParam(":nom_etd", $this->nom_etd);
        $stmt->bindParam(":prenom_etd", $this->prenom_etd);
        $stmt->bindParam(":num_carte_etd", $this->num_carte_etd);
        $stmt->bindParam(":date_naiss", $this->date_naiss);
        $stmt->bindParam(":email_etd", $this->email_etd);
        $stmt->bindParam(":photo_etd", $this->photo_etd);
        $stmt->bindParam(":mdp_etd", $password_hash);
        $stmt->bindParam(":statut_eligibilite", $this->statut_eligibilite);
        $stmt->bindParam(":id_niv_etd", $this->id_niv_etd);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Mettre à jour un étudiant
    public function mettreAJour() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nom_etd=:nom_etd, prenom_etd=:prenom_etd, num_carte_etd=:num_carte_etd,
                      date_naiss=:date_naiss, email_etd=:email_etd, 
                      statut_eligibilite=:statut_eligibilite, id_niv_etd=:id_niv_etd
                  WHERE num_etd=:num_etd";

        $stmt = $this->conn->prepare($query);

        $this->nom_etd = htmlspecialchars(strip_tags($this->nom_etd));
        $this->prenom_etd = htmlspecialchars(strip_tags($this->prenom_etd));
        $this->num_carte_etd = htmlspecialchars(strip_tags($this->num_carte_etd));
        $this->email_etd = htmlspecialchars(strip_tags($this->email_etd));

        $stmt->bindParam(":nom_etd", $this->nom_etd);
        $stmt->bindParam(":prenom_etd", $this->prenom_etd);
        $stmt->bindParam(":num_carte_etd", $this->num_carte_etd);
        $stmt->bindParam(":date_naiss", $this->date_naiss);
        $stmt->bindParam(":email_etd", $this->email_etd);
        $stmt->bindParam(":statut_eligibilite", $this->statut_eligibilite);
        $stmt->bindParam(":id_niv_etd", $this->id_niv_etd);
        $stmt->bindParam(":num_etd", $this->num_etd);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer un étudiant
    public function supprimer() {
        $query = "DELETE FROM " . $this->table_name . " WHERE num_etd = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->num_etd);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtenir les statistiques
    public function getStatistiques() {
        $stats = [];
        
        // Total étudiants
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Étudiants éligibles
        $query = "SELECT COUNT(*) as eligible FROM " . $this->table_name . " WHERE statut_eligibilite = 'Éligible'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['eligible'] = $stmt->fetch(PDO::FETCH_ASSOC)['eligible'];

        // En attente
        $query = "SELECT COUNT(*) as attente FROM " . $this->table_name . " WHERE statut_eligibilite = 'En attente de confirmation'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['attente'] = $stmt->fetch(PDO::FETCH_ASSOC)['attente'];

        // Non éligibles
        $query = "SELECT COUNT(*) as non_eligible FROM " . $this->table_name . " WHERE statut_eligibilite = 'Non éligible'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['non_eligible'] = $stmt->fetch(PDO::FETCH_ASSOC)['non_eligible'];

        return $stats;
    }
}
?>
