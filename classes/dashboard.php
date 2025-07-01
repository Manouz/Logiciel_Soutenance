<?php
require_once 'config/database.php';

class Dashboard {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getStatistiquesGenerales() {
        $stats = [];

        // Total étudiants
        $query = "SELECT COUNT(*) as total FROM etudiants";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_etudiants'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total enseignants
        $query = "SELECT COUNT(*) as total FROM enseignants";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_enseignants'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total personnel administratif
        $query = "SELECT COUNT(*) as total FROM personnel_administratif";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_personnel'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total rapports
        $query = "SELECT COUNT(*) as total FROM rapport_etudiant";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_rapports'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Règlements en cours
        $query = "SELECT COUNT(*) as total FROM reglement WHERE statut != 'Payé'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['reglements_en_cours'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Montant total des règlements
        $query = "SELECT SUM(montant_total) as total FROM reglement";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['montant_total_reglements'] = $result['total'] ?? 0;

        return $stats;
    }

    public function getActivitesRecentes() {
        $query = "SELECT 
                    'Nouvel étudiant' as type,
                    CONCAT(nom_etd, ' ', prenom_etd) as description,
                    'Ajouté au système' as action,
                    NOW() as date_action
                  FROM etudiants 
                  ORDER BY num_etd DESC 
                  LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatutEtudiants() {
        $query = "SELECT 
                    statut_eligibilite,
                    COUNT(*) as nombre
                  FROM etudiants 
                  GROUP BY statut_eligibilite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
