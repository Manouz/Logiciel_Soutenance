<?php
/**
 * Classe User - Gestion des utilisateurs
 * Système de Validation Académique - Université Félix Houphouët-Boigny
 */

class User {
    
    private $pdo;
    private $userData;
    
    public function __construct($userId = null) {
        $this->pdo = Database::getInstance()->getConnection();
        
        if ($userId) {
            $this->loadUser($userId);
        }
    }
    
    /**
     * Charger les données d'un utilisateur
     */
    private function loadUser($userId) {
        $sql = "SELECT 
                    u.utilisateur_id,
                    u.code_utilisateur,
                    u.email,
                    u.role_id,
                    u.statut_id,
                    u.est_actif,
                    u.derniere_connexion,
                    u.date_creation,
                    r.nom_role,
                    r.niveau_acces,
                    s.libelle_statut,
                    ip.nom,
                    ip.prenoms,
                    ip.date_naissance,
                    ip.genre,
                    ip.telephone,
                    ip.adresse,
                    ip.ville,
                    ip.pays
                FROM utilisateurs u
                INNER JOIN roles r ON u.role_id = r.role_id
                INNER JOIN statuts s ON u.statut_id = s.statut_id
                LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                WHERE u.utilisateur_id = ? AND u.est_actif = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $this->userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$this->userData) {
            throw new Exception("Utilisateur non trouvé");
        }
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function create($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Validation des données
            $this->validateUserData($data);
            
            // Générer le code utilisateur
            $rolePrefix = $this->getRolePrefix($data['role_id']);
            $codeUtilisateur = generateUniqueNumber($rolePrefix);
            
            // Hasher le mot de passe
            $passwordData = hashPassword($data['password']);
            
            // Insérer l'utilisateur principal
            $sql = "INSERT INTO utilisateurs 
                    (code_utilisateur, email, role_id, statut_id, 
                     mot_de_passe_hash, salt) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $codeUtilisateur,
                $data['email'],
                $data['role_id'],
                $data['statut_id'] ?? 1, // Actif par défaut
                $passwordData['hash'],
                $passwordData['salt']
            ]);
            
            $userId = $this->pdo->lastInsertId();
            
            // Insérer les informations personnelles
            $this->insertPersonalInfo($userId, $data);
            
            // Insérer les informations spécifiques selon le rôle
            $this->insertRoleSpecificData($userId, $data);
            
            $this->pdo->commit();
            
            // Logger la création
            Logger::log('USER_CREATED', 'utilisateurs', $userId, 
                       null, json_encode(['email' => $data['email'], 'role' => $data['role_id']]));
            
            return $userId;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update($userId, $data) {
        try {
            $this->pdo->beginTransaction();
            
            // Charger les anciennes données pour le log
            $oldData = $this->getUserById($userId);
            
            // Mettre à jour les informations utilisateur
            if (isset($data['email']) || isset($data['role_id']) || isset($data['statut_id'])) {
                $this->updateUserBasicInfo($userId, $data);
            }
            
            // Mettre à jour les informations personnelles
            if (isset($data['nom']) || isset($data['prenoms']) || isset($data['telephone'])) {
                $this->updatePersonalInfo($userId, $data);
            }
            
            // Mettre à jour le mot de passe si fourni
            if (!empty($data['password'])) {
                $this->updatePassword($userId, $data['password']);
            }
            
            $this->pdo->commit();
            
            // Logger la modification
            Logger::log('USER_UPDATED', 'utilisateurs', $userId, 
                       json_encode($oldData), json_encode($data));
            
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Supprimer un utilisateur (soft delete)
     */
    public function delete($userId) {
        try {
            // Vérifier que l'utilisateur existe
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception("Utilisateur non trouvé");
            }
            
            // Soft delete
            $sql = "UPDATE utilisateurs 
                    SET est_actif = 0, 
                        date_modification = NOW() 
                    WHERE utilisateur_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$userId]);
            
            if ($result) {
                // Logger la suppression
                Logger::log('USER_DELETED', 'utilisateurs', $userId, 
                           json_encode($user), null);
            }
            
            return $result;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Obtenir un utilisateur par ID
     */
    public function getUserById($userId) {
        $sql = "SELECT 
                    u.*,
                    r.nom_role,
                    r.niveau_acces,
                    s.libelle_statut,
                    ip.nom,
                    ip.prenoms,
                    ip.telephone,
                    ip.adresse
                FROM utilisateurs u
                INNER JOIN roles r ON u.role_id = r.role_id
                INNER JOIN statuts s ON u.statut_id = s.statut_id
                LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                WHERE u.utilisateur_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir un utilisateur par email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT 
                    u.*,
                    r.nom_role,
                    r.niveau_acces,
                    s.libelle_statut,
                    ip.nom,
                    ip.prenoms
                FROM utilisateurs u
                INNER JOIN roles r ON u.role_id = r.role_id
                INNER JOIN statuts s ON u.statut_id = s.statut_id
                LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                WHERE u.email = ? AND u.est_actif = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lister les utilisateurs avec pagination
     */
    public function getUsers($filters = [], $page = 1, $limit = 20) {
        $where = ["u.est_actif = 1"];
        $params = [];
        
        // Filtres
        if (!empty($filters['role_id'])) {
            $where[] = "u.role_id = ?";
            $params[] = $filters['role_id'];
        }
        
        if (!empty($filters['statut_id'])) {
            $where[] = "u.statut_id = ?";
            $params[] = $filters['statut_id'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(ip.nom LIKE ? OR ip.prenoms LIKE ? OR u.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Compter le total
        $countSql = "SELECT COUNT(*) as total 
                     FROM utilisateurs u
                     LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                     WHERE $whereClause";
        
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Récupérer les données avec pagination
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT 
                    u.utilisateur_id,
                    u.code_utilisateur,
                    u.email,
                    u.derniere_connexion,
                    u.date_creation,
                    r.nom_role,
                    s.libelle_statut,
                    s.couleur_affichage,
                    ip.nom,
                    ip.prenoms,
                    ip.telephone
                FROM utilisateurs u
                INNER JOIN roles r ON u.role_id = r.role_id
                INNER JOIN statuts s ON u.statut_id = s.statut_id
                LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                WHERE $whereClause
                ORDER BY u.date_creation DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'users' => $users,
            'pagination' => paginate($total, $limit, $page)
        ];
    }
    
    /**
     * Obtenir les rôles disponibles
     */
    public function getRoles() {
        $sql = "SELECT * FROM roles WHERE est_actif = 1 ORDER BY niveau_acces DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir les statuts disponibles
     */
    public function getStatuts($type = 'Utilisateur') {
        $sql = "SELECT * FROM statuts WHERE type_statut = ? AND est_actif = 1 ORDER BY ordre_affichage";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Valider les données utilisateur
     */
    private function validateUserData($data) {
        $errors = [];
        
        // Email requis et valide
        if (empty($data['email'])) {
            $errors[] = "L'email est requis";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format d'email invalide";
        } elseif ($this->emailExists($data['email'])) {
            $errors[] = "Cet email est déjà utilisé";
        }
        
        // Mot de passe requis
        if (empty($data['password'])) {
            $errors[] = "Le mot de passe est requis";
        } elseif (strlen($data['password']) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
        }
        
        // Rôle requis
        if (empty($data['role_id'])) {
            $errors[] = "Le rôle est requis";
        }
        
        // Nom et prénom requis
        if (empty($data['nom'])) {
            $errors[] = "Le nom est requis";
        }
        
        if (empty($data['prenoms'])) {
            $errors[] = "Le prénom est requis";
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
    }
    
    /**
     * Vérifier si un email existe déjà
     */
    private function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as count FROM utilisateurs WHERE email = ? AND est_actif = 1";
        $params = [$email];
        
        if ($excludeUserId) {
            $sql .= " AND utilisateur_id != ?";
            $params[] = $excludeUserId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Obtenir le préfixe de rôle pour le code utilisateur
     */
    private function getRolePrefix($roleId) {
        $prefixes = [
            1 => 'ADM',  // Administrateur
            2 => 'RSC',  // Responsable Scolarité
            3 => 'CHC',  // Chargé Communication
            4 => 'COM',  // Commission
            5 => 'SEC',  // Secrétaire
            6 => 'ENS',  // Enseignant
            7 => 'PER',  // Personnel Administratif
            8 => 'ETD'   // Étudiant
        ];
        
        return $prefixes[$roleId] ?? 'USR';
    }
    
    /**
     * Insérer les informations personnelles
     */
    private function insertPersonalInfo($userId, $data) {
        $sql = "INSERT INTO informations_personnelles 
                (utilisateur_id, nom, prenoms, date_naissance, lieu_naissance, 
                 genre, nationalite, situation_matrimoniale, numero_identite, 
                 telephone, telephone_urgence, adresse, ville, code_postal, pays) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $data['nom'],
            $data['prenoms'],
            $data['date_naissance'] ?? null,
            $data['lieu_naissance'] ?? null,
            $data['genre'] ?? null,
            $data['nationalite'] ?? 'Ivoirienne',
            $data['situation_matrimoniale'] ?? null,
            $data['numero_identite'] ?? null,
            $data['telephone'] ?? null,
            $data['telephone_urgence'] ?? null,
            $data['adresse'] ?? null,
            $data['ville'] ?? null,
            $data['code_postal'] ?? null,
            $data['pays'] ?? 'Côte d\'Ivoire'
        ]);
    }
    
    /**
     * Insérer les données spécifiques selon le rôle
     */
    private function insertRoleSpecificData($userId, $data) {
        switch ($data['role_id']) {
            case 6: // Enseignant
                $this->insertEnseignantData($userId, $data);
                break;
            case 8: // Étudiant
                $this->insertEtudiantData($userId, $data);
                break;
            case 7: // Personnel Administratif
                $this->insertPersonnelData($userId, $data);
                break;
        }
    }
    
    /**
     * Insérer les données d'un enseignant
     */
    private function insertEnseignantData($userId, $data) {
        $numeroEnseignant = generateUniqueNumber('ENS');
        
        $sql = "INSERT INTO enseignants 
                (utilisateur_id, numero_enseignant, grade_id, fonction_id, 
                 specialite_id, date_recrutement, est_vacataire, nombre_heures_max, 
                 taux_horaire) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $numeroEnseignant,
            $data['grade_id'] ?? 6, // Vacataire par défaut
            $data['fonction_id'] ?? 8, // Enseignant par défaut
            $data['specialite_id'] ?? null,
            $data['date_recrutement'] ?? date('Y-m-d'),
            $data['est_vacataire'] ?? 0,
            $data['nombre_heures_max'] ?? 192,
            $data['taux_horaire'] ?? null
        ]);
    }
    
    /**
     * Insérer les données d'un étudiant
     */
    private function insertEtudiantData($userId, $data) {
        $numeroEtudiant = generateUniqueNumber('ETD');
        $numeroCarteEtudiant = 'CE' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO etudiants 
                (utilisateur_id, numero_etudiant, numero_carte_etudiant, 
                 niveau_id, specialite_id, annee_inscription, statut_eligibilite) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $numeroEtudiant,
            $numeroCarteEtudiant,
            $data['niveau_id'] ?? 5, // M2 par défaut
            $data['specialite_id'] ?? null,
            $data['annee_inscription'] ?? date('Y'),
            7 // Statut "En attente de vérification"
        ]);
    }
    
    /**
     * Insérer les données du personnel administratif
     */
    private function insertPersonnelData($userId, $data) {
        $numeroPersonnel = generateUniqueNumber('PER');
        
        $sql = "INSERT INTO personnel_administratif 
                (utilisateur_id, numero_personnel, fonction_id, 
                 service_rattachement, date_recrutement, salaire) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $numeroPersonnel,
            $data['fonction_id'] ?? 9, // Secrétaire par défaut
            $data['service_rattachement'] ?? 'Administration',
            $data['date_recrutement'] ?? date('Y-m-d'),
            $data['salaire'] ?? null
        ]);
    }
    
    /**
     * Mettre à jour les informations de base de l'utilisateur
     */
    private function updateUserBasicInfo($userId, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['email'])) {
            if ($this->emailExists($data['email'], $userId)) {
                throw new Exception("Cet email est déjà utilisé");
            }
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['role_id'])) {
            $fields[] = "role_id = ?";
            $params[] = $data['role_id'];
        }
        
        if (isset($data['statut_id'])) {
            $fields[] = "statut_id = ?";
            $params[] = $data['statut_id'];
        }
        
        if (!empty($fields)) {
            $sql = "UPDATE utilisateurs SET " . implode(', ', $fields) . 
                   ", date_modification = NOW() WHERE utilisateur_id = ?";
            $params[] = $userId;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }
    }
    
    /**
     * Mettre à jour les informations personnelles
     */
    private function updatePersonalInfo($userId, $data) {
        $fields = [];
        $params = [];
        
        $personalFields = [
            'nom', 'prenoms', 'date_naissance', 'lieu_naissance', 'genre',
            'nationalite', 'situation_matrimoniale', 'numero_identite',
            'telephone', 'telephone_urgence', 'adresse', 'ville', 'code_postal', 'pays'
        ];
        
        foreach ($personalFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (!empty($fields)) {
            // Vérifier si l'enregistrement existe
            $checkSql = "SELECT COUNT(*) as count FROM informations_personnelles WHERE utilisateur_id = ?";
            $stmt = $this->pdo->prepare($checkSql);
            $stmt->execute([$userId]);
            $exists = $stmt->fetch()['count'] > 0;
            
            if ($exists) {
                // Mise à jour
                $sql = "UPDATE informations_personnelles SET " . implode(', ', $fields) . 
                       " WHERE utilisateur_id = ?";
                $params[] = $userId;
            } else {
                // Insertion
                $fieldNames = implode(', ', array_keys($data));
                $placeholders = str_repeat('?,', count($data) - 1) . '?';
                $sql = "INSERT INTO informations_personnelles (utilisateur_id, $fieldNames) 
                        VALUES (?, $placeholders)";
                array_unshift($params, $userId);
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }
    }
    
    /**
     * Mettre à jour le mot de passe
     */
    private function updatePassword($userId, $newPassword) {
        $passwordData = hashPassword($newPassword);
        
        $sql = "UPDATE utilisateurs 
                SET mot_de_passe_hash = ?, salt = ?, date_modification = NOW() 
                WHERE utilisateur_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $passwordData['hash'],
            $passwordData['salt'],
            $userId
        ]);
        
        // Invalider toutes les sessions actives de cet utilisateur
        $this->invalidateUserSessions($userId);
    }
    
    /**
     * Invalider toutes les sessions d'un utilisateur
     */
    private function invalidateUserSessions($userId) {
        $sql = "UPDATE sessions_utilisateurs 
                SET est_active = 0 
                WHERE utilisateur_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
    }
    
    /**
     * Obtenir les statistiques des utilisateurs
     */
    public function getStatistics() {
        $stats = [];
        
        // Total utilisateurs actifs
        $sql = "SELECT COUNT(*) as total FROM utilisateurs WHERE est_actif = 1";
        $stmt = $this->pdo->query($sql);
        $stats['total_users'] = $stmt->fetch()['total'];
        
        // Utilisateurs par rôle
        $sql = "SELECT r.nom_role, COUNT(*) as count 
                FROM utilisateurs u 
                INNER JOIN roles r ON u.role_id = r.role_id 
                WHERE u.est_actif = 1 
                GROUP BY r.nom_role 
                ORDER BY count DESC";
        $stmt = $this->pdo->query($sql);
        $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Connexions récentes (7 derniers jours)
        $sql = "SELECT COUNT(*) as count 
                FROM utilisateurs 
                WHERE derniere_connexion >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                AND est_actif = 1";
        $stmt = $this->pdo->query($sql);
        $stats['recent_logins'] = $stmt->fetch()['count'];
        
        // Nouveaux utilisateurs ce mois
        $sql = "SELECT COUNT(*) as count 
                FROM utilisateurs 
                WHERE MONTH(date_creation) = MONTH(NOW()) 
                AND YEAR(date_creation) = YEAR(NOW()) 
                AND est_actif = 1";
        $stmt = $this->pdo->query($sql);
        $stats['new_this_month'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Rechercher des utilisateurs
     */
    public function search($query, $roleId = null, $limit = 10) {
        $where = ["u.est_actif = 1"];
        $params = [];
        
        if (!empty($query)) {
            $where[] = "(ip.nom LIKE ? OR ip.prenoms LIKE ? OR u.email LIKE ? OR u.code_utilisateur LIKE ?)";
            $searchTerm = '%' . $query . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($roleId) {
            $where[] = "u.role_id = ?";
            $params[] = $roleId;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                    u.utilisateur_id,
                    u.code_utilisateur,
                    u.email,
                    r.nom_role,
                    CONCAT(ip.prenoms, ' ', ip.nom) as nom_complet
                FROM utilisateurs u
                INNER JOIN roles r ON u.role_id = r.role_id
                LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                WHERE $whereClause
                ORDER BY ip.nom, ip.prenoms
                LIMIT ?";
        
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir les données de l'utilisateur actuel
     */
    public function getCurrentUserData() {
        return $this->userData;
    }
    
    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($role) {
        return $this->userData && $this->userData['nom_role'] === $role;
    }
    
    /**
     * Vérifier si l'utilisateur a un niveau d'accès suffisant
     */
    public function hasAccessLevel($level) {
        return $this->userData && $this->userData['niveau_acces'] >= $level;
    }
}
?>