<?php
/**
 * Saisie des Notes - Responsable Scolarité
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
    
    // Traitement des formulaires
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'saisie_individuelle':
                    try {
                        $etudiant_id = $_POST['etudiant_id'];
                        $ue_id = $_POST['ue_id'];
                        $type_evaluation = $_POST['type_evaluation'];
                        $note = (float)$_POST['note'];
                        $coefficient = (int)$_POST['coefficient'];
                        $commentaire = $_POST['commentaire'] ?? '';
                        
                        // Créer l'évaluation
                        $evaluationData = [
                            'ue_id' => $ue_id,
                            'type_evaluation' => $type_evaluation,
                            'coefficient' => $coefficient,
                            'date_evaluation' => date('Y-m-d'),
                            'date_creation' => date('Y-m-d H:i:s'),
                            'cree_par' => $userId
                        ];
                        $evaluation_id = $db->insert('evaluations', $evaluationData);
                        
                        // Créer la note
                        $noteData = [
                            'evaluation_id' => $evaluation_id,
                            'etudiant_id' => $etudiant_id,
                            'note' => $note,
                            'commentaire' => $commentaire,
                            'date_saisie' => date('Y-m-d H:i:s'),
                            'saisi_par' => $userId
                        ];
                        $db->insert('notes_evaluations', $noteData);
                        
                        // Mettre à jour la moyenne de l'étudiant
                        updateStudentAverage($db, $etudiant_id);
                        
                        $success_message = "Note saisie avec succès !";
                    } catch (Exception $e) {
                        $error_message = "Erreur lors de la saisie : " . $e->getMessage();
                    }
                    break;
                    
                case 'saisie_masse':
                    try {
                        $notes_data = json_decode($_POST['notes_data'], true);
                        
                        $db->beginTransaction();
                        
                        $etudiants_updated = [];
                        
                        foreach ($notes_data as $note_data) {
                            // Créer l'évaluation
                            $evaluationData = [
                                'ue_id' => $note_data['ue_id'],
                                'type_evaluation' => $note_data['type_evaluation'],
                                'coefficient' => $note_data['coefficient'],
                                'date_evaluation' => date('Y-m-d'),
                                'date_creation' => date('Y-m-d H:i:s'),
                                'cree_par' => $userId
                            ];
                            $evaluation_id = $db->insert('evaluations', $evaluationData);
                            
                            // Créer la note
                            $noteDataInsert = [
                                'evaluation_id' => $evaluation_id,
                                'etudiant_id' => $note_data['etudiant_id'],
                                'note' => $note_data['note'],
                                'date_saisie' => date('Y-m-d H:i:s'),
                                'saisi_par' => $userId
                            ];
                            $db->insert('notes_evaluations', $noteDataInsert);
                            
                            $etudiants_updated[] = $note_data['etudiant_id'];
                        }
                        
                        // Mettre à jour les moyennes des étudiants concernés
                        foreach (array_unique($etudiants_updated) as $etudiant_id) {
                            updateStudentAverage($db, $etudiant_id);
                        }
                        
                        $db->commit();
                        $success_message = "Notes saisies en masse avec succès !";
                    } catch (Exception $e) {
                        $db->rollback();
                        $error_message = "Erreur lors de la saisie en masse : " . $e->getMessage();
                    }
                    break;
            }
        }
    }
    
    // Récupération des données pour les formulaires
    $niveaux = $db->fetchAll("SELECT * FROM niveaux ORDER BY nom");
    $specialites = $db->fetchAll("SELECT * FROM specialites ORDER BY nom");
    $ues = $db->fetchAll("SELECT * FROM ues ORDER BY nom");
    
    $etudiants = $db->fetchAll("
        SELECT e.*, ip.nom, ip.prenoms, n.nom as niveau_nom, s.nom as specialite_nom 
        FROM etudiants e 
        JOIN informations_personnelles ip ON e.utilisateur_id = ip.utilisateur_id
        JOIN niveaux n ON e.niveau_id = n.niveau_id 
        JOIN specialites s ON e.specialite_id = s.specialite_id 
        WHERE e.est_actif = 1
        ORDER BY ip.nom, ip.prenoms
    ");
    
} catch (Exception $e) {
    error_log("Erreur récupération données: " . $e->getMessage());
    $niveaux = [];
    $specialites = [];
    $ues = [];
    $etudiants = [];
}

// Fonction pour mettre à jour la moyenne d'un étudiant
function updateStudentAverage($db, $etudiant_id) {
    $result = $db->fetch("
        SELECT AVG(ne.note * e.coefficient) / AVG(e.coefficient) as moyenne 
        FROM notes_evaluations ne
        JOIN evaluations e ON ne.evaluation_id = e.evaluation_id
        WHERE ne.etudiant_id = ? AND ne.note IS NOT NULL
    ", [$etudiant_id]);
    
    if ($result && $result['moyenne'] !== null) {
        $db->update('etudiants', ['moyenne_generale' => round($result['moyenne'], 2)], 'utilisateur_id = ?', [$etudiant_id]);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie des Notes - Responsable Scolarité</title>
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
            --info-color: #3b82f6;
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
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
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
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
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

        .tabs {
            display: flex;
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        .tab {
            flex: 1;
            padding: 1rem 1.5rem;
            background: transparent;
            border: none;
            border-radius: calc(var(--border-radius) - 2px);
            cursor: pointer;
            font-weight: 600;
            color: var(--gray-600);
            transition: var(--transition);
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        .tab.active {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .tab:hover:not(.active) {
            background: var(--gray-100);
            color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-input, .form-select, .form-textarea {
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 1rem;
            justify-content: center;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--gray-500);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: var(--gray-600);
        }

        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .validation-error {
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .note-valid {
            border-color: var(--success-color);
        }

        .note-invalid {
            border-color: var(--error-color);
        }

        .bulk-actions {
            background: var(--gray-50);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-box input {
            width: 100%;
            padding-left: 2.5rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }

        .filter-group {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-200);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
        }

        .table th {
            background: var(--gray-50);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            border-bottom: 2px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: var(--gray-50);
        }

        .note-input {
            width: 80px;
            text-align: center;
        }

        .student-row {
            transition: var(--transition);
        }

        .student-row:hover {
            background: var(--primary-light);
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header {
                padding: 1.5rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .btn-group {
                justify-content: stretch;
            }

            .btn-group .btn {
                flex: 1;
            }

            .tabs {
                flex-direction: column;
            }

            .tab {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Saisie des Notes</h1>
            <p>Interface de saisie et gestion des notes des étudiants</p>
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

        <div class="tabs">
            <button class="tab active" onclick="showTab('saisie-individuelle')">
                <i class="fas fa-user"></i>
                Saisie Individuelle
            </button>
            <button class="tab" onclick="showTab('saisie-masse')">
                <i class="fas fa-users"></i>
                Saisie en Masse
            </button>
        </div>

        <!-- Saisie Individuelle -->
        <div id="saisie-individuelle" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-user-edit"></i>
                        Saisie Individuelle
                    </h2>
                </div>

                <form method="POST" id="saisieIndividuelleForm">
                    <input type="hidden" name="action" value="saisie_individuelle">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-graduate"></i>
                                Étudiant
                            </label>
                            <select name="etudiant_id" class="form-select" required>
                                <option value="">Sélectionner un étudiant</option>
                                <?php foreach ($etudiants as $etudiant): ?>
                                    <option value="<?= $etudiant['utilisateur_id'] ?>">
                                        <?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenoms'] . ' - ' . $etudiant['niveau_nom'] . ' ' . $etudiant['specialite_nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-book"></i>
                                Unité d'Enseignement
                            </label>
                            <select name="ue_id" class="form-select" required>
                                <option value="">Sélectionner une UE</option>
                                <?php foreach ($ues as $ue): ?>
                                    <option value="<?= $ue['ue_id'] ?>">
                                        <?= htmlspecialchars($ue['code'] . ' - ' . $ue['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-clipboard-list"></i>
                                Type d'Évaluation
                            </label>
                            <select name="type_evaluation" class="form-select" required>
                                <option value="">Sélectionner le type</option>
                                <option value="CC">Contrôle Continu</option>
                                <option value="TP">Travaux Pratiques</option>
                                <option value="Examen">Examen</option>
                                <option value="Projet">Projet</option>
                                <option value="Oral">Oral</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-star"></i>
                                Note (/20)
                            </label>
                            <input type="number" name="note" class="form-input note-input" 
                                   min="0" max="20" step="0.25" required
                                   onchange="validateNote(this)">
                            <div class="validation-error" id="note-error"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-weight-hanging"></i>
                                Coefficient
                            </label>
                            <input type="number" name="coefficient" class="form-input" 
                                   min="1" max="10" value="1" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-comment"></i>
                                Commentaire (optionnel)
                            </label>
                            <textarea name="commentaire" class="form-textarea" 
                                      placeholder="Commentaire sur la note..."></textarea>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i>
                            Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Enregistrer la Note
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Saisie en Masse -->
        <div id="saisie-masse" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-users-cog"></i>
                        Saisie en Masse
                    </h2>
                </div>

                <div class="bulk-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-input" placeholder="Rechercher un étudiant..." 
                               id="searchStudent" onkeyup="filterStudents()">
                    </div>
                    
                    <div class="filter-group">
                        <select class="form-select" id="filterNiveau" onchange="filterStudents()">
                            <option value="">Tous les niveaux</option>
                            <?php foreach ($niveaux as $niveau): ?>
                                <option value="<?= $niveau['niveau_id'] ?>"><?= htmlspecialchars($niveau['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select class="form-select" id="filterSpecialite" onchange="filterStudents()">
                            <option value="">Toutes les spécialités</option>
                            <?php foreach ($specialites as $specialite): ?>
                                <option value="<?= $specialite['specialite_id'] ?>"><?= htmlspecialchars($specialite['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select class="form-select" id="bulkUE" required>
                            <option value="">Sélectionner une UE</option>
                            <?php foreach ($ues as $ue): ?>
                                <option value="<?= $ue['ue_id'] ?>"><?= htmlspecialchars($ue['code'] . ' - ' . $ue['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select class="form-select" id="bulkTypeEvaluation" required>
                            <option value="">Type d'évaluation</option>
                            <option value="CC">Contrôle Continu</option>
                            <option value="TP">Travaux Pratiques</option>
                            <option value="Examen">Examen</option>
                            <option value="Projet">Projet</option>
                            <option value="Oral">Oral</option>
                        </select>
                    </div>
                </div>

                <div class="table-container">
                    <table class="table" id="studentsTable">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Étudiant</th>
                                <th>Niveau</th>
                                <th>Spécialité</th>
                                <th>Note (/20)</th>
                                <th>Coefficient</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            <?php foreach ($etudiants as $etudiant): ?>
                                <tr class="student-row" 
                                    data-niveau="<?= $etudiant['niveau_id'] ?>"
                                    data-specialite="<?= $etudiant['specialite_id'] ?>"
                                    data-nom="<?= strtolower($etudiant['nom'] . ' ' . $etudiant['prenoms']) ?>">
                                    <td>
                                        <input type="checkbox" class="student-checkbox" 
                                               value="<?= $etudiant['utilisateur_id'] ?>">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenoms']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($etudiant['numero_etudiant']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($etudiant['niveau_nom']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['specialite_nom']) ?></td>
                                    <td>
                                        <input type="number" class="form-input note-input" 
                                               min="0" max="20" step="0.25"
                                               data-student="<?= $etudiant['utilisateur_id'] ?>"
                                               onchange="validateBulkNote(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input" style="width: 80px;"
                                               min="1" max="10" value="1"
                                               data-student="<?= $etudiant['utilisateur_id'] ?>">
                                    </td>
                                    <td>
                                        <span class="badge badge-info" id="status-<?= $etudiant['utilisateur_id'] ?>">
                                            En attente
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-success" onclick="saveBulkNotes()">
                        <i class="fas fa-save"></i>
                        Enregistrer les Notes Sélectionnées
                    </button>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="../index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Retour au tableau de bord
            </a>
        </div>
    </div>

    <script>
        // Variables globales
        let currentTab = 'saisie-individuelle';

        // Gestion des onglets
        function showTab(tabName) {
            // Masquer tous les contenus d'onglets
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Désactiver tous les onglets
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activer l'onglet et le contenu sélectionnés
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            currentTab = tabName;
        }

        // Validation des notes
        function validateNote(input) {
            const value = parseFloat(input.value);
            const errorDiv = document.getElementById('note-error');
            
            if (isNaN(value) || value < 0 || value > 20) {
                input.classList.add('note-invalid');
                input.classList.remove('note-valid');
                errorDiv.textContent = 'La note doit être comprise entre 0 et 20';
                return false;
            } else {
                input.classList.add('note-valid');
                input.classList.remove('note-invalid');
                errorDiv.textContent = '';
                return true;
            }
        }

        function validateBulkNote(input) {
            const value = parseFloat(input.value);
            const studentId = input.dataset.student;
            const statusElement = document.getElementById(`status-${studentId}`);
            
            if (input.value === '') {
                statusElement.textContent = 'En attente';
                statusElement.className = 'badge badge-info';
                input.classList.remove('note-valid', 'note-invalid');
                return;
            }
            
            if (isNaN(value) || value < 0 || value > 20) {
                input.classList.add('note-invalid');
                input.classList.remove('note-valid');
                statusElement.textContent = 'Invalide';
                statusElement.className = 'badge badge-error';
                return false;
            } else {
                input.classList.add('note-valid');
                input.classList.remove('note-invalid');
                statusElement.textContent = 'Prêt';
                statusElement.className = 'badge badge-success';
                return true;
            }
        }

        // Filtrage des étudiants
        function filterStudents() {
            const searchTerm = document.getElementById('searchStudent').value.toLowerCase();
            const niveauFilter = document.getElementById('filterNiveau').value;
            const specialiteFilter = document.getElementById('filterSpecialite').value;
            
            const rows = document.querySelectorAll('.student-row');
            
            rows.forEach(row => {
                const nom = row.dataset.nom;
                const niveau = row.dataset.niveau;
                const specialite = row.dataset.specialite;
                
                const matchSearch = nom.includes(searchTerm);
                const matchNiveau = !niveauFilter || niveau === niveauFilter;
                const matchSpecialite = !specialiteFilter || specialite === specialiteFilter;
                
                if (matchSearch && matchNiveau && matchSpecialite) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Sélection multiple
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.student-checkbox');
            
            checkboxes.forEach(checkbox => {
                if (checkbox.closest('.student-row').style.display !== 'none') {
                    checkbox.checked = selectAll.checked;
                }
            });
        }

        // Sauvegarde en masse
        function saveBulkNotes() {
            const ueId = document.getElementById('bulkUE').value;
            const typeEvaluation = document.getElementById('bulkTypeEvaluation').value;
            
            if (!ueId || !typeEvaluation) {
                alert('Veuillez sélectionner une UE et un type d\'évaluation.');
                return;
            }
            
            const selectedStudents = document.querySelectorAll('.student-checkbox:checked');
            const notesData = [];
            
            selectedStudents.forEach(checkbox => {
                const studentId = checkbox.value;
                const row = checkbox.closest('.student-row');
                const noteInput = row.querySelector('.note-input');
                const coeffInput = row.querySelector('input[type="number"]:not(.note-input)');
                
                if (noteInput.value !== '' && validateBulkNote(noteInput)) {
                    notesData.push({
                        etudiant_id: studentId,
                        ue_id: ueId,
                        type_evaluation: typeEvaluation,
                        note: noteInput.value,
                        coefficient: coeffInput.value
                    });
                }
            });
            
            if (notesData.length === 0) {
                alert('Aucune note valide sélectionnée.');
                return;
            }
            
            // Confirmation
            if (confirm(`Êtes-vous sûr de vouloir enregistrer ${notesData.length} note(s) ?`)) {
                // Créer un formulaire caché pour envoyer les données
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.name = 'action';
                actionInput.value = 'saisie_masse';
                form.appendChild(actionInput);
                
                const dataInput = document.createElement('input');
                dataInput.name = 'notes_data';
                dataInput.value = JSON.stringify(notesData);
                form.appendChild(dataInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
