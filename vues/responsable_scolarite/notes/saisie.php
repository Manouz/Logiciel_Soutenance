<?php

session_start();
require_once '../../../config/database.php';
require_once '../../../config/session.php';

// Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'responsable_scolarite') {
    header('Location: ../../../login.php');
    exit();
}

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'saisie_individuelle':
                // Traitement saisie individuelle
                $etudiant_id = $_POST['etudiant_id'];
                $ue_id = $_POST['ue_id'];
                $type_note = $_POST['type_note'];
                $note = $_POST['note'];
                $coefficient = $_POST['coefficient'];
                
                $stmt = $pdo->prepare("INSERT INTO notes (etudiant_id, ue_id, type_note, note, coefficient, date_saisie, saisi_par) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
                $stmt->execute([$etudiant_id, $ue_id, $type_note, $note, $coefficient, $_SESSION['user_id']]);
                
                $success_message = "Note saisie avec succès !";
                break;
                
            case 'saisie_masse':
                // Traitement saisie en masse
                $notes_data = json_decode($_POST['notes_data'], true);
                
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("INSERT INTO notes (etudiant_id, ue_id, type_note, note, coefficient, date_saisie, saisi_par) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
                    
                    foreach ($notes_data as $note_data) {
                        $stmt->execute([
                            $note_data['etudiant_id'],
                            $note_data['ue_id'],
                            $note_data['type_note'],
                            $note_data['note'],
                            $note_data['coefficient'],
                            $_SESSION['user_id']
                        ]);
                    }
                    
                    $pdo->commit();
                    $success_message = "Notes saisies en masse avec succès !";
                } catch (Exception $e) {
                    $pdo->rollback();
                    $error_message = "Erreur lors de la saisie en masse : " . $e->getMessage();
                }
                break;
        }
    }
}

// Récupération des données pour les formulaires
$niveaux = $pdo->query("SELECT * FROM niveaux ORDER BY nom")->fetchAll();
$specialites = $pdo->query("SELECT * FROM specialites ORDER BY nom")->fetchAll();
$ues = $pdo->query("SELECT * FROM ues ORDER BY nom")->fetchAll();
$etudiants = $pdo->query("SELECT e.*, n.nom as niveau_nom, s.nom as specialite_nom 
                         FROM etudiants e 
                         JOIN niveaux n ON e.niveau_id = n.id 
                         JOIN specialites s ON e.specialite_id = s.id 
                         ORDER BY e.nom, e.prenom")->fetchAll();
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
            justify-content: between;
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

        .btn-warning {
            background: var(--warning-color);
            color: var(--white);
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-info {
            background: var(--info-color);
            color: var(--white);
        }

        .btn-info:hover {
            background: #2563eb;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: flex-end;
            margin-top: 2rem;
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

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
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
            backdrop-filter: blur(4px);
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 2rem;
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-500);
            padding: 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .close-modal:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .file-upload {
            border: 2px dashed var(--gray-300);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
        }

        .file-upload:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }

        .file-upload.dragover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            transition: width 0.3s ease;
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
            border-left: 4px solid var(--primary-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray-600);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
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
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
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
            <button class="tab" onclick="showTab('import-excel')">
                <i class="fas fa-file-excel"></i>
                Import Excel
            </button>
            <button class="tab" onclick="showTab('validation')">
                <i class="fas fa-check-double"></i>
                Validation
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
                                    <option value="<?php echo $etudiant['id']; ?>">
                                        <?php echo $etudiant['nom'] . ' ' . $etudiant['prenom'] . ' - ' . $etudiant['niveau_nom'] . ' ' . $etudiant['specialite_nom']; ?>
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
                                    <option value="<?php echo $ue['id']; ?>">
                                        <?php echo $ue['code'] . ' - ' . $ue['nom']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-clipboard-list"></i>
                                Type de Note
                            </label>
                            <select name="type_note" class="form-select" required>
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
                                <option value="<?php echo $niveau['id']; ?>"><?php echo $niveau['nom']; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select class="form-select" id="filterSpecialite" onchange="filterStudents()">
                            <option value="">Toutes les spécialités</option>
                            <?php foreach ($specialites as $specialite): ?>
                                <option value="<?php echo $specialite['id']; ?>"><?php echo $specialite['nom']; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select class="form-select" id="bulkUE" required>
                            <option value="">Sélectionner une UE</option>
                            <?php foreach ($ues as $ue): ?>
                                <option value="<?php echo $ue['id']; ?>"><?php echo $ue['code'] . ' - ' . $ue['nom']; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select class="form-select" id="bulkTypeNote" required>
                            <option value="">Type de note</option>
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
                                    data-niveau="<?php echo $etudiant['niveau_id']; ?>"
                                    data-specialite="<?php echo $etudiant['specialite_id']; ?>"
                                    data-nom="<?php echo strtolower($etudiant['nom'] . ' ' . $etudiant['prenom']); ?>">
                                    <td>
                                        <input type="checkbox" class="student-checkbox" 
                                               value="<?php echo $etudiant['id']; ?>">
                                    </td>
                                    <td>
                                        <strong><?php echo $etudiant['nom'] . ' ' . $etudiant['prenom']; ?></strong><br>
                                        <small class="text-muted"><?php echo $etudiant['numero_etudiant']; ?></small>
                                    </td>
                                    <td><?php echo $etudiant['niveau_nom']; ?></td>
                                    <td><?php echo $etudiant['specialite_nom']; ?></td>
                                    <td>
                                        <input type="number" class="form-input note-input" 
                                               min="0" max="20" step="0.25"
                                               data-student="<?php echo $etudiant['id']; ?>"
                                               onchange="validateBulkNote(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-input" style="width: 80px;"
                                               min="1" max="10" value="1"
                                               data-student="<?php echo $etudiant['id']; ?>">
                                    </td>
                                    <td>
                                        <span class="badge badge-info" id="status-<?php echo $etudiant['id']; ?>">
                                            En attente
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-warning" onclick="validateAllNotes()">
                        <i class="fas fa-check-double"></i>
                        Valider Toutes les Notes
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveBulkNotes()">
                        <i class="fas fa-save"></i>
                        Enregistrer les Notes Sélectionnées
                    </button>
                </div>
            </div>
        </div>

        <!-- Import Excel -->
        <div id="import-excel" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-file-import"></i>
                        Import depuis Excel
                    </h2>
                </div>

                <div class="file-upload" id="fileUpload" onclick="document.getElementById('excelFile').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                    <h3>Glissez-déposez votre fichier Excel ici</h3>
                    <p>ou cliquez pour sélectionner un fichier</p>
                    <small>Formats acceptés: .xlsx, .xls, .csv</small>
                    <input type="file" id="excelFile" accept=".xlsx,.xls,.csv" style="display: none;" onchange="handleFileSelect(this)">
                </div>

                <div id="uploadProgress" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <p id="progressText">Téléchargement en cours...</p>
                </div>

                <div id="previewSection" style="display: none;">
                    <h3>Aperçu des données</h3>
                    <div class="table-container">
                        <table class="table" id="previewTable">
                            <thead id="previewHeader"></thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                    
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" onclick="cancelImport()">
                            <i class="fas fa-times"></i>
                            Annuler
                        </button>
                        <button type="button" class="btn btn-primary" onclick="confirmImport()">
                            <i class="fas fa-check"></i>
                            Confirmer l'Import
                        </button>
                    </div>
                </div>

                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Format du fichier Excel
                        </h3>
                    </div>
                    <p>Votre fichier Excel doit contenir les colonnes suivantes :</p>
                    <ul style="margin: 1rem 0; padding-left: 2rem;">
                        <li><strong>numero_etudiant</strong> : Numéro d'étudiant</li>
                        <li><strong>nom</strong> : Nom de l'étudiant</li>
                        <li><strong>prenom</strong> : Prénom de l'étudiant</li>
                        <li><strong>ue_code</strong> : Code de l'UE</li>
                        <li><strong>type_note</strong> : Type de note (CC, TP, Examen, etc.)</li>
                        <li><strong>note</strong> : Note sur 20</li>
                        <li><strong>coefficient</strong> : Coefficient de la note</li>
                    </ul>
                    
                    <div class="btn-group">
                        <a href="#" class="btn btn-info" onclick="downloadTemplate()">
                            <i class="fas fa-download"></i>
                            Télécharger le Modèle Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Validation -->
        <div id="validation" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-clipboard-check"></i>
                        Validation des Notes
                    </h2>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number" id="notesEnAttente">0</div>
                        <div class="stat-label">Notes en Attente</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="notesValidees">0</div>
                        <div class="stat-label">Notes Validées</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="notesRejetees">0</div>
                        <div class="stat-label">Notes Rejetées</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="totalNotes">0</div>
                        <div class="stat-label">Total Notes</div>
                    </div>
                </div>

                <div class="bulk-actions">
                    <div class="filter-group">
                        <select class="form-select" id="filterStatut">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente">En attente</option>
                            <option value="validee">Validée</option>
                            <option value="rejetee">Rejetée</option>
                        </select>

                        <select class="form-select" id="filterUEValidation">
                            <option value="">Toutes les UE</option>
                            <?php foreach ($ues as $ue): ?>
                                <option value="<?php echo $ue['id']; ?>"><?php echo $ue['code'] . ' - ' . $ue['nom']; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button class="btn btn-success" onclick="validateSelectedNotes()">
                            <i class="fas fa-check"></i>
                            Valider Sélectionnées
                        </button>

                        <button class="btn btn-warning" onclick="rejectSelectedNotes()">
                            <i class="fas fa-times"></i>
                            Rejeter Sélectionnées
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="table" id="validationTable">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAllValidation" onchange="toggleSelectAllValidation()">
                                </th>
                                <th>Date Saisie</th>
                                <th>Étudiant</th>
                                <th>UE</th>
                                <th>Type</th>
                                <th>Note</th>
                                <th>Coefficient</th>
                                <th>Saisi par</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="validationTableBody">
                            <!-- Les données seront chargées via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmation -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="confirmTitle">Confirmation</h3>
                <button class="close-modal" onclick="closeModal('confirmModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="confirmMessage"></div>
            <div class="btn-group">
                <button class="btn btn-secondary" onclick="closeModal('confirmModal')">
                    Annuler
                </button>
                <button class="btn btn-primary" id="confirmButton">
                    Confirmer
                </button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentTab = 'saisie-individuelle';
        let importData = [];
        let validationData = [];

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
            
            // Charger les données spécifiques à l'onglet
            if (tabName === 'validation') {
                loadValidationData();
            }
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

        function toggleSelectAllValidation() {
            const selectAll = document.getElementById('selectAllValidation');
            const checkboxes = document.querySelectorAll('.validation-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        // Validation de toutes les notes
        function validateAllNotes() {
            const noteInputs = document.querySelectorAll('.note-input');
            let allValid = true;
            
            noteInputs.forEach(input => {
                if (input.value !== '' && !validateBulkNote(input)) {
                    allValid = false;
                }
            });
            
            if (allValid) {
                showAlert('success', 'Toutes les notes saisies sont valides !');
            } else {
                showAlert('error', 'Certaines notes ne sont pas valides. Veuillez les corriger.');
            }
        }

        // Sauvegarde en masse
        function saveBulkNotes() {
            const ueId = document.getElementById('bulkUE').value;
            const typeNote = document.getElementById('bulkTypeNote').value;
            
            if (!ueId || !typeNote) {
                showAlert('error', 'Veuillez sélectionner une UE et un type de note.');
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
                        type_note: typeNote,
                        note: noteInput.value,
                        coefficient: coeffInput.value
                    });
                }
            });
            
            if (notesData.length === 0) {
                showAlert('error', 'Aucune note valide sélectionnée.');
                return;
            }
            
            // Confirmation
            showConfirmModal(
                'Confirmer la sauvegarde',
                `Êtes-vous sûr de vouloir enregistrer ${notesData.length} note(s) ?`,
                () => {
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
            );
        }

        // Gestion des fichiers
        function handleFileSelect(input) {
            const file = input.files[0];
            if (!file) return;
            
            const allowedTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
                'text/csv'
            ];
            
            if (!allowedTypes.includes(file.type)) {
                showAlert('error', 'Format de fichier non supporté. Utilisez .xlsx, .xls ou .csv');
                return;
            }
            
            // Simuler le téléchargement
            showUploadProgress();
            
            // Ici, vous ajouteriez la logique pour traiter le fichier Excel
            setTimeout(() => {
                hideUploadProgress();
                showPreview();
            }, 2000);
        }

        function showUploadProgress() {
            document.getElementById('uploadProgress').style.display = 'block';
            let progress = 0;
            
            const interval = setInterval(() => {
                progress += 10;
                document.getElementById('progressFill').style.width = progress + '%';
                document.getElementById('progressText').textContent = `Téléchargement en cours... ${progress}%`;
                
                if (progress >= 100) {
                    clearInterval(interval);
                }
            }, 200);
        }

        function hideUploadProgress() {
            document.getElementById('uploadProgress').style.display = 'none';
        }

        function showPreview() {
            // Données d'exemple pour la prévisualisation
            const sampleData = [
                ['12345', 'Dupont', 'Jean', 'INF101', 'CC', '15.5', '2'],
                ['12346', 'Martin', 'Marie', 'INF101', 'CC', '17.0', '2'],
                ['12347', 'Bernard', 'Paul', 'INF101', 'CC', '12.5', '2']
            ];
            
            const headers = ['Numéro', 'Nom', 'Prénom', 'UE', 'Type', 'Note', 'Coeff'];
            
            // Construire le tableau de prévisualisation
            const headerRow = document.getElementById('previewHeader');
            headerRow.innerHTML = headers.map(h => `<th>${h}</th>`).join('');
            
            const bodyRows = document.getElementById('previewBody');
            bodyRows.innerHTML = sampleData.map(row => 
                `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`
            ).join('');
            
            document.getElementById('previewSection').style.display = 'block';
        }

        function cancelImport() {
            document.getElementById('previewSection').style.display = 'none';
            document.getElementById('excelFile').value = '';
        }

        function confirmImport() {
            showAlert('success', 'Import réalisé avec succès !');
            cancelImport();
        }

        function downloadTemplate() {
            // Ici, vous ajouteriez la logique pour générer et télécharger le modèle Excel
            showAlert('info', 'Téléchargement du modèle Excel...');
        }

        // Chargement des données de validation
        function loadValidationData() {
            // Ici, vous ajouteriez un appel AJAX pour charger les données
            // Pour la démo, on utilise des données fictives
            const sampleValidationData = [
                {
                    id: 1,
                    date_saisie: '2024-01-15 10:30:00',
                    etudiant: 'Dupont Jean',
                    ue: 'INF101 - Programmation',
                    type: 'CC',
                    note: 15.5,
                    coefficient: 2,
                    saisi_par: 'Prof. Martin',
                    statut: 'en_attente'
                },
                // ... plus de données
            ];
            
            updateValidationTable(sampleValidationData);
            updateValidationStats(sampleValidationData);
        }

        function updateValidationTable(data) {
            const tbody = document.getElementById('validationTableBody');
            tbody.innerHTML = data.map(item => `
                <tr>
                    <td><input type="checkbox" class="validation-checkbox" value="${item.id}"></td>
                    <td>${new Date(item.date_saisie).toLocaleString('fr-FR')}</td>
                    <td>${item.etudiant}</td>
                    <td>${item.ue}</td>
                    <td>${item.type}</td>
                    <td>${item.note}/20</td>
                    <td>${item.coefficient}</td>
                    <td>${item.saisi_par}</td>
                    <td>
                        <span class="badge badge-${getStatusClass(item.statut)}">
                            ${getStatusText(item.statut)}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="validateNote(${item.id})">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="rejectNote(${item.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function updateValidationStats(data) {
            const stats = data.reduce((acc, item) => {
                acc.total++;
                acc[item.statut]++;
                return acc;
            }, { total: 0, en_attente: 0, validee: 0, rejetee: 0 });
            
            document.getElementById('notesEnAttente').textContent = stats.en_attente || 0;
            document.getElementById('notesValidees').textContent = stats.validee || 0;
            document.getElementById('notesRejetees').textContent = stats.rejetee || 0;
            document.getElementById('totalNotes').textContent = stats.total;
        }

        function getStatusClass(status) {
            const classes = {
                'en_attente': 'info',
                'validee': 'success',
                'rejetee': 'error'
            };
            return classes[status] || 'info';
        }

        function getStatusText(status) {
            const texts = {
                'en_attente': 'En attente',
                'validee': 'Validée',
                'rejetee': 'Rejetée'
            };
            return texts[status] || 'Inconnu';
        }

        function validateNote(id) {
            showConfirmModal(
                'Valider la note',
                'Êtes-vous sûr de vouloir valider cette note ?',
                () => {
                    // Ici, vous ajouteriez l'appel AJAX pour valider la note
                    showAlert('success', 'Note validée avec succès !');
                    loadValidationData();
                }
            );
        }

        function rejectNote(id) {
            showConfirmModal(
                'Rejeter la note',
                'Êtes-vous sûr de vouloir rejeter cette note ?',
                () => {
                    // Ici, vous ajouteriez l'appel AJAX pour rejeter la note
                    showAlert('warning', 'Note rejetée.');
                    loadValidationData();
                }
            );
        }

        function validateSelectedNotes() {
            const selected = document.querySelectorAll('.validation-checkbox:checked');
            if (selected.length === 0) {
                showAlert('error', 'Aucune note sélectionnée.');
                return;
            }
            
            showConfirmModal(
                'Valider les notes sélectionnées',
                `Êtes-vous sûr de vouloir valider ${selected.length} note(s) ?`,
                () => {
                    showAlert('success', `${selected.length} note(s) validée(s) avec succès !`);
                    loadValidationData();
                }
            );
        }

        function rejectSelectedNotes() {
            const selected = document.querySelectorAll('.validation-checkbox:checked');
            if (selected.length === 0) {
                showAlert('error', 'Aucune note sélectionnée.');
                return;
            }
            
            showConfirmModal(
                'Rejeter les notes sélectionnées',
                `Êtes-vous sûr de vouloir rejeter ${selected.length} note(s) ?`,
                () => {
                    showAlert('warning', `${selected.length} note(s) rejetée(s).`);
                    loadValidationData();
                }
            );
        }

        // Utilitaires
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                ${message}
            `;
            
            const container = document.querySelector('.container');
            container.insertBefore(alertDiv, container.firstChild.nextSibling);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        function showConfirmModal(title, message, onConfirm) {
            document.getElementById('confirmTitle').textContent = title;
            document.getElementById('confirmMessage').innerHTML = `<p>${message}</p>`;
            document.getElementById('confirmButton').onclick = () => {
                closeModal('confirmModal');
                onConfirm();
            };
            document.getElementById('confirmModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Gestion du drag & drop
        const fileUpload = document.getElementById('fileUpload');
        
        fileUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUpload.classList.add('dragover');
        });
        
        fileUpload.addEventListener('dragleave', () => {
            fileUpload.classList.remove('dragover');
        });
        
        fileUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUpload.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('excelFile').files = files;
                handleFileSelect(document.getElementById('excelFile'));
            }
        });

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Charger les données initiales si nécessaire
            if (currentTab === 'validation') {
                loadValidationData();
            }
        });
    </script>
</body>
</html>
