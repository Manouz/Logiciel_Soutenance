<?php
/**
 * Gestion des Étudiants - Responsable Scolarité
 * Système de Validation Académique - UFHB Cocody
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';

SessionManager::start();

if (!SessionManager::isLoggedIn()) {
    redirectTo('../../../login.php');
}

$userRole = SessionManager::getUserRole();
if ($userRole !== 'Responsable Scolarité') {
    redirectTo('../../../login.php');
}

$userId = SessionManager::getUserId();
$userName = SessionManager::getUserName();

try {
    $db = Database::getInstance();
    
    // Traitement des actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'ajouter_etudiant':
                    try {
                        $db->beginTransaction();
                        
                        // Créer l'utilisateur
                        $userData = [
                            'nom_utilisateur' => $_POST['numero_etudiant'],
                            'mot_de_passe' => password_hash($_POST['numero_etudiant'], PASSWORD_DEFAULT),
                            'role_id' => 3, // Étudiant
                            'est_actif' => 1,
                            'date_creation' => date('Y-m-d H:i:s')
                        ];
                        $utilisateur_id = $db->insert('utilisateurs', $userData);
                        
                        // Créer les informations personnelles
                        $infoData = [
                            'utilisateur_id' => $utilisateur_id,
                            'nom' => $_POST['nom'],
                            'prenoms' => $_POST['prenom'],
                            'email' => $_POST['email'],
                            'telephone' => $_POST['telephone'],
                            'date_naissance' => $_POST['date_naissance'],
                            'adresse' => $_POST['adresse']
                        ];
                        $db->insert('informations_personnelles', $infoData);
                        
                        // Créer l'étudiant
                        $etudiantData = [
                            'utilisateur_id' => $utilisateur_id,
                            'numero_etudiant' => $_POST['numero_etudiant'],
                            'niveau_id' => $_POST['niveau_id'],
                            'specialite_id' => $_POST['specialite_id'],
                            'est_actif' => 1,
                            'statut_eligibilite' => 1, // NON_ELIGIBLE par défaut
                            'date_inscription' => date('Y-m-d')
                        ];
                        $db->insert('etudiants', $etudiantData);
                        
                        $db->commit();
                        $success_message = "Étudiant ajouté avec succès !";
                    } catch (Exception $e) {
                        $db->rollback();
                        $error_message = "Erreur lors de l'ajout : " . $e->getMessage();
                    }
                    break;
                    
                case 'modifier_etudiant':
                    try {
                        $db->beginTransaction();
                        
                        $utilisateur_id = $_POST['utilisateur_id'];
                        
                        // Mettre à jour les informations personnelles
                        $infoData = [
                            'nom' => $_POST['nom'],
                            'prenoms' => $_POST['prenom'],
                            'email' => $_POST['email'],
                            'telephone' => $_POST['telephone'],
                            'date_naissance' => $_POST['date_naissance'],
                            'adresse' => $_POST['adresse']
                        ];
                        $db->update('informations_personnelles', $infoData, 'utilisateur_id = ?', [$utilisateur_id]);
                        
                        // Mettre à jour l'étudiant
                        $etudiantData = [
                            'niveau_id' => $_POST['niveau_id'],
                            'specialite_id' => $_POST['specialite_id']
                        ];
                        $db->update('etudiants', $etudiantData, 'utilisateur_id = ?', [$utilisateur_id]);
                        
                        $db->commit();
                        $success_message = "Étudiant modifié avec succès !";
                    } catch (Exception $e) {
                        $db->rollback();
                        $error_message = "Erreur lors de la modification : " . $e->getMessage();
                    }
                    break;
                    
                case 'supprimer_etudiant':
                    try {
                        $utilisateur_id = $_POST['utilisateur_id'];
                        
                        $db->update('etudiants', ['est_actif' => 0], 'utilisateur_id = ?', [$utilisateur_id]);
                        $db->update('utilisateurs', ['est_actif' => 0], 'utilisateur_id = ?', [$utilisateur_id]);
                        
                        $success_message = "Étudiant désactivé avec succès !";
                    } catch (Exception $e) {
                        $error_message = "Erreur lors de la suppression : " . $e->getMessage();
                    }
                    break;
            }
        }
    }
    
    // Récupérer les statistiques
    $totalEtudiants = $db->count('etudiants', 'est_actif = 1');
    $etudiantsActifs = $totalEtudiants;
    $etudiantsEligibles = $db->count('etudiants', 'statut_eligibilite = 5 AND est_actif = 1'); // ELIGIBLE
    
    $moyenne_result = $db->fetch("SELECT AVG(moyenne_generale) as moyenne FROM etudiants WHERE moyenne_generale IS NOT NULL AND est_actif = 1");
    $moyenneGenerale = round($moyenne_result['moyenne'] ?? 0, 1);
    
    // Liste des étudiants avec informations complètes
    $etudiants = $db->fetchAll("
        SELECT e.*, ip.nom, ip.prenoms, ip.email, ip.telephone, ip.date_naissance, ip.adresse,
               n.nom as niveau_nom, s.nom as specialite_nom
        FROM etudiants e
        JOIN informations_personnelles ip ON e.utilisateur_id = ip.utilisateur_id
        JOIN niveaux n ON e.niveau_id = n.niveau_id
        JOIN specialites s ON e.specialite_id = s.specialite_id
        WHERE e.est_actif = 1
        ORDER BY ip.nom, ip.prenoms
    ");
    
    // Données pour les formulaires
    $niveaux = $db->fetchAll("SELECT * FROM niveaux ORDER BY nom");
    $specialites = $db->fetchAll("SELECT * FROM specialites ORDER BY nom");
    
} catch (Exception $e) {
    error_log("Erreur gestion étudiants: " . $e->getMessage());
    $etudiants = [];
    $niveaux = [];
    $specialites = [];
    $totalEtudiants = 0;
    $etudiantsActifs = 0;
    $etudiantsEligibles = 0;
    $moyenneGenerale = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants - Responsable Scolarité</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: rgb(0, 51, 41);
            --primary-light: rgba(0, 51, 41, 0.1);
            --primary-dark: rgb(0, 35, 28);
            --secondary-color: #10b981;
            --accent-color: #34d399;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }

        .data-table {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
        }

        .data-table tr:hover {
            background: var(--gray-50);
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-actif {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inactif {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
        }

        .btn-danger {
            background: var(--error-color);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--gray-500);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .modal-close:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: none;
            }
            
            .data-table {
                overflow-x: auto;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Gestion des Étudiants</h1>
            <p>Administration et suivi des étudiants</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= number_format($totalEtudiants) ?></h3>
                <p>Étudiants inscrits</p>
            </div>
            <div class="stat-card">
                <h3><?= number_format($etudiantsActifs) ?></h3>
                <p>Étudiants actifs</p>
            </div>
            <div class="stat-card">
                <h3><?= number_format($etudiantsEligibles) ?></h3>
                <p>Étudiants éligibles</p>
            </div>
            <div class="stat-card">
                <h3><?= $moyenneGenerale ?></h3>
                <p>Moyenne générale</p>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher un étudiant..." onkeyup="filterStudents()">
            </div>
            <button class="btn btn-primary" onclick="openModal('addModal')">
                <i class="fas fa-user-plus"></i>
                Ajouter un étudiant
            </button>
        </div>

        <!-- Students Table -->
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Niveau</th>
                        <th>Spécialité</th>
                        <th>Moyenne</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    <?php foreach ($etudiants as $etudiant): ?>
                        <tr>
                            <td><?= htmlspecialchars($etudiant['numero_etudiant']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenoms']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($etudiant['email']) ?></td>
                            <td><?= htmlspecialchars($etudiant['telephone'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($etudiant['niveau_nom']) ?></td>
                            <td><?= htmlspecialchars($etudiant['specialite_nom']) ?></td>
                            <td>
                                <?php if ($etudiant['moyenne_generale']): ?>
                                    <strong><?= number_format($etudiant['moyenne_generale'], 2) ?>/20</strong>
                                <?php else: ?>
                                    <em>Non calculée</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $etudiant['est_actif'] ? 'actif' : 'inactif' ?>">
                                    <?= $etudiant['est_actif'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-secondary" onclick="editStudent(<?= $etudiant['utilisateur_id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger" onclick="deleteStudent(<?= $etudiant['utilisateur_id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="../index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Retour au tableau de bord
            </a>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un étudiant</h3>
                <button class="modal-close" onclick="closeModal('addModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="ajouter_etudiant">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="numero_etudiant">Numéro étudiant *</label>
                            <input type="text" name="numero_etudiant" required>
                        </div>
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" name="nom" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" name="prenom" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" name="telephone">
                        </div>
                        <div class="form-group">
                            <label for="date_naissance">Date de naissance</label>
                            <input type="date" name="date_naissance">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="niveau_id">Niveau *</label>
                            <select name="niveau_id" required>
                                <option value="">Sélectionner un niveau</option>
                                <?php foreach ($niveaux as $niveau): ?>
                                    <option value="<?= $niveau['niveau_id'] ?>"><?= htmlspecialchars($niveau['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="specialite_id">Spécialité *</label>
                            <select name="specialite_id" required>
                                <option value="">Sélectionner une spécialité</option>
                                <?php foreach ($specialites as $specialite): ?>
                                    <option value="<?= $specialite['specialite_id'] ?>"><?= htmlspecialchars($specialite['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <textarea name="adresse" placeholder="Adresse complète"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier l'étudiant</h3>
                <button class="modal-close" onclick="closeModal('editModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="editForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="modifier_etudiant">
                    <input type="hidden" name="utilisateur_id" id="edit_utilisateur_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_nom">Nom *</label>
                            <input type="text" name="nom" id="edit_nom" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_prenom">Prénom *</label>
                            <input type="text" name="prenom" id="edit_prenom" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_email">Email *</label>
                            <input type="email" name="email" id="edit_email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_telephone">Téléphone</label>
                            <input type="tel" name="telephone" id="edit_telephone">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_date_naissance">Date de naissance</label>
                            <input type="date" name="date_naissance" id="edit_date_naissance">
                        </div>
                        <div class="form-group">
                            <label for="edit_niveau_id">Niveau *</label>
                            <select name="niveau_id" id="edit_niveau_id" required>
                                <option value="">Sélectionner un niveau</option>
                                <?php foreach ($niveaux as $niveau): ?>
                                    <option value="<?= $niveau['niveau_id'] ?>"><?= htmlspecialchars($niveau['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_specialite_id">Spécialité *</label>
                            <select name="specialite_id" id="edit_specialite_id" required>
                                <option value="">Sélectionner une spécialité</option>
                                <?php foreach ($specialites as $specialite): ?>
                                    <option value="<?= $specialite['specialite_id'] ?>"><?= htmlspecialchars($specialite['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_adresse">Adresse</label>
                        <textarea name="adresse" id="edit_adresse" placeholder="Adresse complète"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Données des étudiants pour JavaScript
        const etudiants = <?= json_encode($etudiants) ?>;

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function filterStudents() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function editStudent(utilisateur_id) {
            const etudiant = etudiants.find(e => e.utilisateur_id == utilisateur_id);
            if (etudiant) {
                document.getElementById('edit_utilisateur_id').value = etudiant.utilisateur_id;
                document.getElementById('edit_nom').value = etudiant.nom;
                document.getElementById('edit_prenom').value = etudiant.prenoms;
                document.getElementById('edit_email').value = etudiant.email;
                document.getElementById('edit_telephone').value = etudiant.telephone || '';
                document.getElementById('edit_date_naissance').value = etudiant.date_naissance || '';
                document.getElementById('edit_niveau_id').value = etudiant.niveau_id;
                document.getElementById('edit_specialite_id').value = etudiant.specialite_id;
                document.getElementById('edit_adresse').value = etudiant.adresse || '';
                
                openModal('editModal');
            }
        }

        function deleteStudent(utilisateur_id) {
            if (confirm('Êtes-vous sûr de vouloir désactiver cet étudiant ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.name = 'action';
                actionInput.value = 'supprimer_etudiant';
                form.appendChild(actionInput);
                
                const idInput = document.createElement('input');
                idInput.name = 'utilisateur_id';
                idInput.value = utilisateur_id;
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Fermer modal en cliquant à l'extérieur
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });
    </script>
</body>
</html>
