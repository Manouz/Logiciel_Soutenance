<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Permissions</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #003329;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-200: #e5e7eb;
            --gray-700: #374151;
            --success-color: #10b981;
            --error-color: #ef4444;
            --border-radius: 8px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-700);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--white);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .permissions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            overflow-x: auto;
        }

        .permissions-table th, 
        .permissions-table td {
            border: 1px solid var(--gray-200);
            padding: 12px;
            text-align: center;
        }

        .permissions-table th {
            background-color: var(--primary-color);
            color: var(--white);
            font-weight: 600;
        }

        .permissions-table tr:nth-child(even) {
            background-color: var(--gray-50);
        }

        .permission-checkbox {
            display: inline-block;
            width: 20px;
            height: 20px;
            position: relative;
            cursor: pointer;
        }

        .permission-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 4px;
        }

        .permission-checkbox:hover input ~ .checkmark {
            background-color: #f0f0f0;
        }

        .permission-checkbox input:checked ~ .checkmark {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .permission-checkbox input:checked ~ .checkmark:after {
            display: block;
        }

        .permission-checkbox .checkmark:after {
            left: 7px;
            top: 3px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .btn {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #00251f;
        }

        .notification {
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: var(--border-radius);
            display: none;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            display: block;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-key"></i> Gestion des Permissions</h1>
        
        <div id="notification" class="notification"></div>
        
        <div class="table-responsive">
            <table class="permissions-table" id="permissionsTable">
                <thead>
                    <tr>
                        <th>Actions / Traitements</th>
                        <!-- Les traitements seront ajoutés dynamiquement ici -->
                    </tr>
                </thead>
                <tbody>
                    <!-- Les lignes d'actions seront ajoutées dynamiquement ici -->
                </tbody>
            </table>
        </div>
        
        <button id="saveBtn" class="btn">
            <i class="fas fa-save"></i> Enregistrer les permissions
        </button>
    </div>

    <script>
        // Données de démonstration (remplacez par vos appels API réels)
        const actions = [
            { id: 1, libelle: 'Ajouter' },
            { id: 2, libelle: 'Modifier' },
            { id: 3, libelle: 'Supprimer' },
            { id: 4, libelle: 'Imprimer' },
            { id: 5, libelle: 'Exporter' }
        ];

        // Simuler les données de la table traitement
        let traitements = [
            { id_traitement: 1, lib_trait: 'Gestion Utilisateurs' },
            { id_traitement: 2, lib_trait: 'Gestion Rapports' },
            { id_traitement: 3, lib_trait: 'Gestion Modules' },
            { id_traitement: 4, lib_trait: 'Gestion Commissions' }
        ];

        // Simuler les données existantes de la table rattacher
        let rattacherData = [
            { id_gu: 1, id_traitement: 1 }, // Exemple de permission existante
            { id_gu: 2, id_traitement: 1 }
        ];

        // Fonction pour afficher une notification
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = 'notification ' + type;
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        // Fonction pour vérifier si une permission existe
        function hasPermission(actionId, traitementId) {
            return rattacherData.some(item => 
                item.id_gu === actionId && item.id_traitement === traitementId
            );
        }

        // Fonction pour générer le tableau de permissions
        function generatePermissionsTable() {
            const table = document.getElementById('permissionsTable');
            const thead = table.querySelector('thead tr');
            const tbody = table.querySelector('tbody');
            
            // Vider le tableau
            thead.innerHTML = '<th>Actions / Traitements</th>';
            tbody.innerHTML = '';
            
            // Ajouter les en-têtes de colonnes (traitements)
            traitements.forEach(traitement => {
                const th = document.createElement('th');
                th.textContent = traitement.lib_trait;
                th.setAttribute('data-traitement-id', traitement.id_traitement);
                thead.appendChild(th);
            });
            
            // Ajouter les lignes d'actions
            actions.forEach(action => {
                const tr = document.createElement('tr');
                
                // Cellule d'action
                const tdAction = document.createElement('td');
                tdAction.textContent = action.libelle;
                tdAction.setAttribute('data-action-id', action.id);
                tr.appendChild(tdAction);
                
                // Cellules de permissions (cases à cocher)
                traitements.forEach(traitement => {
                    const td = document.createElement('td');
                    const label = document.createElement('label');
                    label.className = 'permission-checkbox';
                    
                    const input = document.createElement('input');
                    input.type = 'checkbox';
                    input.dataset.actionId = action.id;
                    input.dataset.traitementId = traitement.id_traitement;
                    
                    // Cocher la case si la permission existe
                    if (hasPermission(action.id, traitement.id_traitement)) {
                        input.checked = true;
                    }
                    
                    const span = document.createElement('span');
                    span.className = 'checkmark';
                    
                    label.appendChild(input);
                    label.appendChild(span);
                    td.appendChild(label);
                    tr.appendChild(td);
                });
                
                tbody.appendChild(tr);
            });
        }

        // Fonction pour sauvegarder les permissions
        async function savePermissions() {
            const checkboxes = document.querySelectorAll('#permissionsTable input[type="checkbox"]:checked');
            const newPermissions = [];
            
            checkboxes.forEach(checkbox => {
                newPermissions.push({
                    id_gu: parseInt(checkbox.dataset.actionId),
                    id_traitement: parseInt(checkbox.dataset.traitementId)
                });
            });
            
            try {
                // Ici, vous feriez normalement un appel API pour sauvegarder
                // Pour la démo, nous simulons juste la sauvegarde
                console.log('Permissions à sauvegarder:', newPermissions);
                
                // Simuler un délai d'appel API
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Mettre à jour les données locales (remplacer par la réponse réelle de l'API)
                rattacherData = newPermissions;
                
                showNotification('Permissions sauvegardées avec succès!', 'success');
            } catch (error) {
                console.error('Erreur lors de la sauvegarde:', error);
                showNotification('Erreur lors de la sauvegarde des permissions', 'error');
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            generatePermissionsTable();
            
            // Écouteur d'événement pour le bouton de sauvegarde
            document.getElementById('saveBtn').addEventListener('click', savePermissions);
        });

        // Fonction pour charger les traitements depuis l'API (exemple)
        async function loadTraitements() {
            try {
                // Remplacez par votre vrai appel API
                // const response = await fetch('/api/traitements');
                // traitements = await response.json();
                
                // Pour la démo, nous utilisons les données simulées
                console.log('Traitements chargés:', traitements);
                generatePermissionsTable();
            } catch (error) {
                console.error('Erreur lors du chargement des traitements:', error);
                showNotification('Erreur lors du chargement des traitements', 'error');
            }
        }

        // Charger les traitements au démarrage
        loadTraitements();
    </script>
</body>
</html>