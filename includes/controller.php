<?php
require_once '../classes/Traitement.php';

class TraitementController {
    private $traitement;
    
    public function __construct() {
        $this->traitement = new Traitement();
    }
    
    /**
     * Traiter les requêtes POST (CRUD operations)
     */
    public function handlePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add':
                return $this->add();
            case 'edit':
                return $this->edit();
            case 'delete':
                return $this->delete();
            case 'delete_multiple':
                return $this->deleteMultiple();
            default:
                return ['success' => false, 'message' => 'Action non reconnue'];
        }
    }
    
    /**
     * Ajouter un traitement
     */
    private function add() {
        $lib_trait = $_POST['lib_trait'] ?? '';
        return $this->traitement->add($lib_trait);
    }
    
    /**
     * Modifier un traitement
     */
    private function edit() {
        $id_trait = $_POST['id_trait'] ?? '';
        $lib_trait = $_POST['lib_trait'] ?? '';
        return $this->traitement->update($id_trait, $lib_trait);
    }
    
    /**
     * Supprimer un traitement
     */
    private function delete() {
        $id_trait = $_POST['id_trait'] ?? '';
        return $this->traitement->delete($id_trait);
    }
    
    /**
     * Supprimer plusieurs traitements
     */
    private function deleteMultiple() {
        $ids = $_POST['ids'] ?? [];
        return $this->traitement->deleteMultiple($ids);
    }
    
    /**
     * Récupérer les données avec pagination
     */
    public function getData($page = 1, $per_page = 5, $search = '') {
        return $this->traitement->getAll($page, $per_page, $search);
    }
    
    /**
     * Récupérer toutes les données pour export
     */
    public function getAllData($search = '') {
        return $this->traitement->getAllForExport($search);
    }
    
    /**
     * Récupérer un traitement par ID
     */
    public function getTraitement($id) {
        return $this->traitement->getById($id);
    }
}
?>
