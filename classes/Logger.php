<?php
/**
 * Classe Logger - Gestion des logs d'audit
 * Système de Validation Académique - Université Félix Houphouët-Boigny
 * Basée sur la table logs_audit de votre base de données
 */

class Logger {
    
    private static $pdo = null;
    
    /**
     * Initialiser la connexion PDO
     */
    private static function initPDO() {
        if (self::$pdo === null) {
            self::$pdo = Database::getInstance()->getConnection();
        }
    }
    
    /**
     * Enregistrer un log d'audit
     * 
     * @param string $typeAction Type d'action (CREATE, UPDATE, DELETE, LOGIN, etc.)
     * @param string|null $tableCible Table concernée par l'action
     * @param int|null $enregistrementId ID de l'enregistrement concerné
     * @param string|null $anciennesValeurs Anciennes valeurs (JSON)
     * @param string|null $nouvellesValeurs Nouvelles valeurs (JSON)
     * @param string|null $commentaire Commentaire additionnel
     * @return bool|int ID du log créé ou false en cas d'erreur
     */
    public static function log(
        $typeAction, 
        $tableCible = null, 
        $enregistrementId = null, 
        $anciennesValeurs = null, 
        $nouvellesValeurs = null, 
        $commentaire = null
    ) {
        try {
            self::initPDO();
            
            // Obtenir l'utilisateur actuel
            $utilisateurId = self::getCurrentUserId();
            
            // Obtenir les informations de la requête
            $adresseIp = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            // Compter les modifications si c'est un UPDATE
            $nombreModifications = 1;
            if ($typeAction === 'UPDATE' && $anciennesValeurs && $nouvellesValeurs) {
                $nombreModifications = self::countChanges($anciennesValeurs, $nouvellesValeurs);
            }
            
            $sql = "INSERT INTO logs_audit 
                    (utilisateur_id, type_action, table_cible, enregistrement_id, 
                     anciennes_valeurs, nouvelles_valeurs, nombre_modifications, 
                     adresse_ip, user_agent, commentaire) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = self::$pdo->prepare($sql);
            $result = $stmt->execute([
                $utilisateurId,
                $typeAction,
                $tableCible,
                $enregistrementId,
                $anciennesValeurs,
                $nouvellesValeurs,
                $nombreModifications,
                $adresseIp,
                $userAgent,
                $commentaire
            ]);
            
            if ($result) {
                return self::$pdo->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du log: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log spécifique pour les connexions réussies
     */
    public static function logLogin($userId, $email) {
        return self::log(
            'LOGIN_SUCCESS',
            'utilisateurs',
            $userId,
            null,
            json_encode([
                'email' => $email,
                'session_id' => session_id(),
                'timestamp' => date('Y-m-d H:i:s')
            ]),
            'Connexion utilisateur réussie'
        );
    }
    
    /**
     * Log spécifique pour les échecs de connexion
     */
    public static function logLoginFailure($email, $reason) {
        return self::log(
            'LOGIN_FAILURE',
            'utilisateurs',
            null,
            null,
            json_encode([
                'email' => $email,
                'reason' => $reason,
                'timestamp' => date('Y-m-d H:i:s')
            ]),
            'Échec de connexion: ' . $reason
        );
    }
    
    /**
     * Log spécifique pour les déconnexions
     */
    public static function logLogout($userId) {
        return self::log(
            'LOGOUT',
            'utilisateurs',
            $userId,
            null,
            json_encode([
                'session_id' => session_id(),
                'timestamp' => date('Y-m-d H:i:s')
            ]),
            'Déconnexion utilisateur'
        );
    }
    
    /**
     * Log spécifique pour la création d'utilisateurs
     */
    public static function logUserCreation($userId, $userData) {
        return self::log(
            'USER_CREATED',
            'utilisateurs',
            $userId,
            null,
            json_encode($userData),
            'Création d\'un nouvel utilisateur'
        );
    }
    
    /**
     * Log spécifique pour la modification d'utilisateurs
     */
    public static function logUserUpdate($userId, $oldData, $newData) {
        return self::log(
            'USER_UPDATED',
            'utilisateurs',
            $userId,
            json_encode($oldData),
            json_encode($newData),
            'Modification des données utilisateur'
        );
    }
    
    /**
     * Log spécifique pour la suppression d'utilisateurs
     */
    public static function logUserDeletion($userId, $userData) {
        return self::log(
            'USER_DELETED',
            'utilisateurs',
            $userId,
            json_encode($userData),
            null,
            'Suppression d\'utilisateur (soft delete)'
        );
    }
    
    /**
     * Log spécifique pour les rapports
     */
    public static function logRapportAction($action, $rapportId, $oldData = null, $newData = null) {
        $commentaires = [
            'RAPPORT_CREATED' => 'Création d\'un nouveau rapport',
            'RAPPORT_SUBMITTED' => 'Soumission de rapport pour validation',
            'RAPPORT_VALIDATED' => 'Validation de rapport par la commission',
            'RAPPORT_REJECTED' => 'Rejet de rapport par la commission',
            'RAPPORT_UPDATED' => 'Modification de rapport'
        ];
        
        return self::log(
            $action,
            'rapports',
            $rapportId,
            $oldData ? json_encode($oldData) : null,
            $newData ? json_encode($newData) : null,
            $commentaires[$action] ?? 'Action sur rapport'
        );
    }
    
    /**
     * Log spécifique pour les soutenances
     */
    public static function logSoutenanceAction($action, $soutenanceId, $data = null) {
        $commentaires = [
            'SOUTENANCE_SCHEDULED' => 'Programmation de soutenance',
            'SOUTENANCE_UPDATED' => 'Modification de soutenance',
            'SOUTENANCE_COMPLETED' => 'Soutenance terminée',
            'SOUTENANCE_CANCELLED' => 'Annulation de soutenance'
        ];
        
        return self::log(
            $action,
            'soutenances',
            $soutenanceId,
            null,
            $data ? json_encode($data) : null,
            $commentaires[$action] ?? 'Action sur soutenance'
        );
    }
    
    /**
     * Log spécifique pour les notes
     */
    public static function logNoteAction($action, $evaluationId, $oldData = null, $newData = null) {
        $commentaires = [
            'NOTE_ADDED' => 'Saisie d\'une nouvelle note',
            'NOTE_UPDATED' => 'Modification d\'une note',
            'NOTE_VALIDATED' => 'Validation d\'une note',
            'NOTE_DELETED' => 'Suppression d\'une note'
        ];
        
        return self::log(
            $action,
            'evaluations',
            $evaluationId,
            $oldData ? json_encode($oldData) : null,
            $newData ? json_encode($newData) : null,
            $commentaires[$action] ?? 'Action sur note'
        );
    }
    
    /**
     * Obtenir les logs avec filtres et pagination
     */
    public static function getLogs($filters = [], $page = 1, $limit = 50) {
        try {
            self::initPDO();
            
            $where = [];
            $params = [];
            
            // Filtres
            if (!empty($filters['type_action'])) {
                $where[] = "l.type_action = ?";
                $params[] = $filters['type_action'];
            }
            
            if (!empty($filters['table_cible'])) {
                $where[] = "l.table_cible = ?";
                $params[] = $filters['table_cible'];
            }
            
            if (!empty($filters['utilisateur_id'])) {
                $where[] = "l.utilisateur_id = ?";
                $params[] = $filters['utilisateur_id'];
            }
            
            if (!empty($filters['date_debut'])) {
                $where[] = "DATE(l.date_action) >= ?";
                $params[] = $filters['date_debut'];
            }
            
            if (!empty($filters['date_fin'])) {
                $where[] = "DATE(l.date_action) <= ?";
                $params[] = $filters['date_fin'];
            }
            
            if (!empty($filters['ip_address'])) {
                $where[] = "l.adresse_ip = ?";
                $params[] = $filters['ip_address'];
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Compter le total
            $countSql = "SELECT COUNT(*) as total FROM logs_audit l $whereClause";
            $stmt = self::$pdo->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];
            
            // Récupérer les logs avec pagination
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT 
                        l.*,
                        CONCAT(ip.prenoms, ' ', ip.nom) as nom_utilisateur,
                        u.email as email_utilisateur
                    FROM logs_audit l
                    LEFT JOIN utilisateurs u ON l.utilisateur_id = u.utilisateur_id
                    LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    $whereClause
                    ORDER BY l.date_action DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'logs' => $logs,
                'pagination' => paginate($total, $limit, $page)
            ];
            
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des logs: " . $e->getMessage());
            return [
                'logs' => [],
                'pagination' => paginate(0, $limit, $page)
            ];
        }
    }
    
    /**
     * Obtenir les statistiques des logs
     */
    public static function getStatistics($period = '30 days') {
        try {
            self::initPDO();
            
            $stats = [];
            
            // Total des actions sur la période
            $sql = "SELECT COUNT(*) as total 
                    FROM logs_audit 
                    WHERE date_action >= DATE_SUB(NOW(), INTERVAL $period)";
            $stmt = self::$pdo->query($sql);
            $stats['total_actions'] = $stmt->fetch()['total'];
            
            // Actions par type
            $sql = "SELECT type_action, COUNT(*) as count 
                    FROM logs_audit 
                    WHERE date_action >= DATE_SUB(NOW(), INTERVAL $period)
                    GROUP BY type_action 
                    ORDER BY count DESC 
                    LIMIT 10";
            $stmt = self::$pdo->query($sql);
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Actions par utilisateur (top 10)
            $sql = "SELECT 
                        l.utilisateur_id,
                        CONCAT(ip.prenoms, ' ', ip.nom) as nom_utilisateur,
                        COUNT(*) as count 
                    FROM logs_audit l
                    LEFT JOIN utilisateurs u ON l.utilisateur_id = u.utilisateur_id
                    LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    WHERE l.date_action >= DATE_SUB(NOW(), INTERVAL $period)
                    AND l.utilisateur_id IS NOT NULL
                    GROUP BY l.utilisateur_id 
                    ORDER BY count DESC 
                    LIMIT 10";
            $stmt = self::$pdo->query($sql);
            $stats['by_user'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Actions par jour (7 derniers jours)
            $sql = "SELECT 
                        DATE(date_action) as date,
                        COUNT(*) as count 
                    FROM logs_audit 
                    WHERE date_action >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(date_action) 
                    ORDER BY date DESC";
            $stmt = self::$pdo->query($sql);
            $stats['by_day'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Erreur lors du calcul des statistiques: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Nettoyer les anciens logs
     */
    public static function cleanOldLogs($retentionDays = 365) {
        try {
            self::initPDO();
            
            $sql = "DELETE FROM logs_audit 
                    WHERE date_action < DATE_SUB(NOW(), INTERVAL ? DAY)";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$retentionDays]);
            
            $deletedRows = $stmt->rowCount();
            
            if ($deletedRows > 0) {
                self::log(
                    'SYSTEM_MAINTENANCE',
                    'logs_audit',
                    null,
                    null,
                    json_encode(['deleted_rows' => $deletedRows, 'retention_days' => $retentionDays]),
                    "Nettoyage automatique des logs anciens"
                );
            }
            
            return $deletedRows;
            
        } catch (Exception $e) {
            error_log("Erreur lors du nettoyage des logs: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exporter les logs en CSV
     */
    public static function exportToCsv($filters = [], $filename = null) {
        $data = self::getLogs($filters, 1, 10000); // Limite élevée pour export
        
        if (empty($data['logs'])) {
            return false;
        }
        
        $filename = $filename ?: 'logs_audit_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Date/Heure',
            'Utilisateur',
            'Action',
            'Table',
            'ID Enregistrement',
            'Adresse IP',
            'Commentaire'
        ];
        
        $csvData = [];
        foreach ($data['logs'] as $log) {
            $csvData[] = [
                $log['date_action'],
                $log['nom_utilisateur'] ?: 'Système',
                $log['type_action'],
                $log['table_cible'] ?: '',
                $log['enregistrement_id'] ?: '',
                $log['adresse_ip'],
                $log['commentaire'] ?: ''
            ];
        }
        
        exportToCSV($csvData, $filename, $headers);
        
        // Log de l'export
        self::log(
            'EXPORT_LOGS',
            'logs_audit',
            null,
            null,
            json_encode(['filename' => $filename, 'count' => count($csvData)]),
            'Export des logs d\'audit en CSV'
        );
        
        return true;
    }
    
    /**
     * Obtenir l'ID de l'utilisateur actuel
     */
    private static function getCurrentUserId() {
        // Vérifier si on est dans une session
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        
        // Pour les actions système ou automatiques
        return null;
    }
    
    /**
     * Compter les changements entre anciennes et nouvelles valeurs
     */
    private static function countChanges($oldData, $newData) {
        if (!$oldData || !$newData) {
            return 1;
        }
        
        $oldArray = json_decode($oldData, true);
        $newArray = json_decode($newData, true);
        
        if (!is_array($oldArray) || !is_array($newArray)) {
            return 1;
        }
        
        $changes = 0;
        foreach ($newArray as $key => $value) {
            if (!isset($oldArray[$key]) || $oldArray[$key] !== $value) {
                $changes++;
            }
        }
        
        return max(1, $changes);
    }
    
    /**
     * Obtenir l'historique des modifications d'un enregistrement
     */
    public static function getRecordHistory($table, $recordId) {
        try {
            self::initPDO();
            
            $sql = "SELECT 
                        l.*,
                        CONCAT(ip.prenoms, ' ', ip.nom) as nom_utilisateur,
                        u.email as email_utilisateur
                    FROM logs_audit l
                    LEFT JOIN utilisateurs u ON l.utilisateur_id = u.utilisateur_id
                    LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    WHERE l.table_cible = ? AND l.enregistrement_id = ?
                    ORDER BY l.date_action DESC";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$table, $recordId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération de l'historique: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Rechercher dans les logs
     */
    public static function search($query, $limit = 100) {
        try {
            self::initPDO();
            
            $sql = "SELECT 
                        l.*,
                        CONCAT(ip.prenoms, ' ', ip.nom) as nom_utilisateur,
                        u.email as email_utilisateur
                    FROM logs_audit l
                    LEFT JOIN utilisateurs u ON l.utilisateur_id = u.utilisateur_id
                    LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    WHERE l.type_action LIKE ? 
                    OR l.table_cible LIKE ? 
                    OR l.commentaire LIKE ?
                    OR l.adresse_ip LIKE ?
                    OR ip.nom LIKE ?
                    OR ip.prenoms LIKE ?
                    ORDER BY l.date_action DESC
                    LIMIT ?";
            
            $searchTerm = '%' . $query . '%';
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([
                $searchTerm, $searchTerm, $searchTerm, 
                $searchTerm, $searchTerm, $searchTerm, 
                $limit
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur lors de la recherche dans les logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les actions suspectes
     */
    public static function getSuspiciousActivity($hours = 24) {
        try {
            self::initPDO();
            
            $suspicious = [];
            
            // Tentatives de connexion multiples échouées
            $sql = "SELECT 
                        adresse_ip,
                        COUNT(*) as tentatives,
                        MAX(date_action) as derniere_tentative
                    FROM logs_audit 
                    WHERE type_action = 'LOGIN_FAILURE' 
                    AND date_action >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                    GROUP BY adresse_ip 
                    HAVING tentatives >= 5
                    ORDER BY tentatives DESC";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$hours]);
            $suspicious['failed_logins'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Suppressions multiples
            $sql = "SELECT 
                        l.utilisateur_id,
                        CONCAT(ip.prenoms, ' ', ip.nom) as nom_utilisateur,
                        COUNT(*) as suppressions
                    FROM logs_audit l
                    LEFT JOIN utilisateurs u ON l.utilisateur_id = u.utilisateur_id
                    LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    WHERE l.type_action LIKE '%DELETE%' 
                    AND l.date_action >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                    AND l.utilisateur_id IS NOT NULL
                    GROUP BY l.utilisateur_id 
                    HAVING suppressions >= 10
                    ORDER BY suppressions DESC";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$hours]);
            $suspicious['mass_deletions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Connexions depuis des IP inhabituelles
            $sql = "SELECT DISTINCT
                        l.adresse_ip,
                        l.utilisateur_id,
                        CONCAT(ip.prenoms, ' ', ip.nom) as nom_utilisateur,
                        l.date_action
                    FROM logs_audit l
                    LEFT JOIN utilisateurs u ON l.utilisateur_id = u.utilisateur_id
                    LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    WHERE l.type_action = 'LOGIN_SUCCESS' 
                    AND l.date_action >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                    AND l.utilisateur_id IS NOT NULL
                    AND l.adresse_ip NOT IN (
                        SELECT DISTINCT adresse_ip 
                        FROM logs_audit 
                        WHERE type_action = 'LOGIN_SUCCESS' 
                        AND utilisateur_id = l.utilisateur_id
                        AND date_action < DATE_SUB(NOW(), INTERVAL 7 DAY)
                    )
                    ORDER BY l.date_action DESC";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$hours]);
            $suspicious['unusual_ips'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $suspicious;
            
        } catch (Exception $e) {
            error_log("Erreur lors de la détection d'activités suspectes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Générer un rapport d'audit
     */
    public static function generateAuditReport($dateDebut, $dateFin) {
        try {
            self::initPDO();
            
            $report = [
                'periode' => [
                    'debut' => $dateDebut,
                    'fin' => $dateFin
                ],
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            // Statistiques générales
            $sql = "SELECT 
                        COUNT(*) as total_actions,
                        COUNT(DISTINCT utilisateur_id) as utilisateurs_actifs,
                        COUNT(DISTINCT adresse_ip) as ips_uniques,
                        COUNT(DISTINCT DATE(date_action)) as jours_activite
                    FROM logs_audit 
                    WHERE DATE(date_action) BETWEEN ? AND ?";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            $report['statistiques'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Actions par type
            $sql = "SELECT 
                        type_action,
                        COUNT(*) as count,
                        COUNT(DISTINCT utilisateur_id) as utilisateurs
                    FROM logs_audit 
                    WHERE DATE(date_action) BETWEEN ? AND ?
                    GROUP BY type_action 
                    ORDER BY count DESC";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            $report['actions_par_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Utilisateurs les plus actifs
            $sql = "SELECT 
                        l.utilisateur_id,
                        CONCAT(ip.prenoms, ' ', ip.nom) as nom_utilisateur,
                        u.email,
                        r.nom_role,
                        COUNT(*) as actions_count
                    FROM logs_audit l
                    LEFT JOIN utilisateurs u ON l.utilisateur_id = u.utilisateur_id
                    LEFT JOIN informations_personnelles ip ON u.utilisateur_id = ip.utilisateur_id
                    LEFT JOIN roles r ON u.role_id = r.role_id
                    WHERE DATE(l.date_action) BETWEEN ? AND ?
                    AND l.utilisateur_id IS NOT NULL
                    GROUP BY l.utilisateur_id 
                    ORDER BY actions_count DESC 
                    LIMIT 20";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            $report['utilisateurs_actifs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Activité par jour
            $sql = "SELECT 
                        DATE(date_action) as date,
                        COUNT(*) as actions,
                        COUNT(DISTINCT utilisateur_id) as utilisateurs
                    FROM logs_audit 
                    WHERE DATE(date_action) BETWEEN ? AND ?
                    GROUP BY DATE(date_action) 
                    ORDER BY date";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            $report['activite_quotidienne'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Erreurs et échecs
            $sql = "SELECT 
                        type_action,
                        COUNT(*) as count
                    FROM logs_audit 
                    WHERE DATE(date_action) BETWEEN ? AND ?
                    AND (type_action LIKE '%FAILURE%' OR type_action LIKE '%ERROR%')
                    GROUP BY type_action 
                    ORDER BY count DESC";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$dateDebut, $dateFin]);
            $report['erreurs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $report;
            
        } catch (Exception $e) {
            error_log("Erreur lors de la génération du rapport d'audit: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Archiver les logs anciens
     */
    public static function archiveLogs($archiveBeforeDate) {
        try {
            self::initPDO();
            
            // Créer une table d'archive si elle n'existe pas
            $createArchiveSql = "CREATE TABLE IF NOT EXISTS logs_audit_archive LIKE logs_audit";
            self::$pdo->exec($createArchiveSql);
            
            // Copier les logs à archiver
            $sql = "INSERT INTO logs_audit_archive 
                    SELECT * FROM logs_audit 
                    WHERE DATE(date_action) < ?";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$archiveBeforeDate]);
            $archivedCount = $stmt->rowCount();
            
            // Supprimer les logs archivés de la table principale
            if ($archivedCount > 0) {
                $deleteSql = "DELETE FROM logs_audit WHERE DATE(date_action) < ?";
                $stmt = self::$pdo->prepare($deleteSql);
                $stmt->execute([$archiveBeforeDate]);
                
                // Log de l'archivage
                self::log(
                    'SYSTEM_ARCHIVE',
                    'logs_audit',
                    null,
                    null,
                    json_encode([
                        'archived_count' => $archivedCount,
                        'archive_before' => $archiveBeforeDate
                    ]),
                    "Archivage automatique des logs"
                );
            }
            
            return $archivedCount;
            
        } catch (Exception $e) {
            error_log("Erreur lors de l'archivage des logs: " . $e->getMessage());
            return false;
        }
    }
}
?>