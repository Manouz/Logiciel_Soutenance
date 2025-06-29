<?php
/*require_once '../../config/database.php';

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
}*/
?>

                <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3>1,247</h3>
                                <p>Utilisateurs </p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3>89</h3>
                                <p>Rapports en attente</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3>156</h3>
                                <p>Rapports validés</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stat-info">
                                <h3>12</h3>
                                <p>Commissions planifiées</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-widgets">
                        <div class="widget">
                            <h3>Activité récente</h3>
                            <div class="activity-list">
                                <div class="activity-item">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Nouvel utilisateur inscrit: Marie Dupont</span>
                                    <small>Il y a 2 heures</small>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-file-upload"></i>
                                    <span>Rapport déposé par Jean Martin</span>
                                    <small>Il y a 4 heures</small>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-check"></i>
                                    <span>Rapport validé par Commission A</span>
                                    <small>Il y a 6 heures</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu supplémentaire pour tester le scroll -->
                    <div-- class="widget">
                        <h3>Contenu supplémentaire pour tester le scroll</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                        <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
                        <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
                        <p>Totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt.</p>
                        <p>Explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.</p>
                        <p>Sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>
                        <p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.</p>
                        <p>Sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</p>
                    </-div>