<?php
/**
 * Configuration de la base de données
 * Fichier: config/database.php
 */

class Database {
    private $host = "localhost";
    private $db_name = "validation_soutenance"; // Ajustez selon votre BDD
    private $username = "root"; // Ajustez selon vos paramètres
    private $password = ""; // Ajustez selon vos paramètres
    private $charset = "utf8mb4";
    public $conn;

    /**
     * Connexion à la base de données
     */
    public function getConnection(): PDO {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $exception) {
            error_log("Erreur de connexion: " . $exception->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }

        return $this->conn;
    }

    /**
     * Fermer la connexion
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>